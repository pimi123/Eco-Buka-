<?php

namespace App\Services\Payments\NestPay;

use App\Mail\OrderConfirmationMail;
use App\Models\NestPayCheckoutSession;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Orders\CreateOrderService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ProcessNestPayResultService
{
    public function __construct(
        private readonly NestPayHashService $hashes,
        private readonly CreateOrderService $orders,
    ) {
    }

    public function process(array $payload, string $endpoint): PaymentResult
    {
        if (! $this->hashes->validateResponseHash($payload)) {
            Log::warning('NestPay response hash validation failed.', [
                'endpoint' => $endpoint,
                'return_oid' => $this->value($payload, 'ReturnOid'),
                'trans_id' => $this->value($payload, 'TransId'),
                'response' => $this->value($payload, 'Response'),
                'proc_return_code' => $this->value($payload, 'ProcReturnCode'),
            ]);

            return new PaymentResult(false, 'invalid_hash', null, 'Payment verification failed.');
        }

        return DB::transaction(function () use ($payload, $endpoint): PaymentResult {
            $returnOid = $this->value($payload, 'ReturnOid') ?: $this->value($payload, 'oid');

            if (! $returnOid) {
                return new PaymentResult(false, 'missing_return_oid', null, 'Missing payment reference.');
            }

            $payment = Payment::query()
                ->where('provider', Payment::PROVIDER_NESTPAY)
                ->where('provider_order_id', $returnOid)
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return $this->processCheckoutSession($payload, $endpoint, $returnOid);
            }

            $payment->load('order');

            if ($payment->processed_at) {
                return new PaymentResult(
                    $payment->status === Payment::STATUS_APPROVED,
                    'duplicate',
                    $payment,
                    'Payment response was already processed.'
                );
            }

            $amount = $this->value($payload, 'amount') ?: $this->value($payload, 'Amount');
            $currency = $this->value($payload, 'currency') ?: $this->value($payload, 'Currency');

            if ($amount !== null && number_format((float) $amount, 2, '.', '') !== number_format((float) $payment->amount, 2, '.', '')) {
                return $this->markError($payment, $payload, 'amount_mismatch', 'Returned payment amount does not match the expected amount.');
            }

            if ($currency !== null && (string) $currency !== (string) $payment->currency_code) {
                return $this->markError($payment, $payload, 'currency_mismatch', 'Returned payment currency does not match the expected currency.');
            }

            $response = $this->value($payload, 'Response');
            $procReturnCode = $this->value($payload, 'ProcReturnCode');
            $approved = $response === 'Approved' && $procReturnCode === '00';

            $payment->fill([
                'status' => $approved ? Payment::STATUS_APPROVED : $this->failureStatus($response, $procReturnCode),
                'response' => $response,
                'proc_return_code' => $procReturnCode,
                'auth_code' => $this->value($payload, 'AuthCode'),
                'host_ref_num' => $this->value($payload, 'HostRefNum'),
                'trans_id' => $this->value($payload, 'TransId'),
                'md_status' => $this->value($payload, 'mdStatus') ?: $this->value($payload, 'mdstatus'),
                'masked_pan' => $this->value($payload, 'MaskedPan'),
                'payment_method' => $this->value($payload, 'PaymentMethod') ?: $this->value($payload, 'EXTRA.CARDBRAND'),
                'installment_count' => $this->installmentCount($payment, $payload),
                'error_message' => $this->value($payload, 'ErrMsg'),
                'response_metadata' => array_merge(
                    $this->safeMetadata($payload),
                    [
                        'callback_endpoint' => $endpoint,
                    ]
                ),
                'paid_at' => $approved ? now() : null,
                'processed_at' => now(),
            ]);
            $payment->save();

            if ($payment->order) {
                if ($approved) {
                    if ($payment->order->status === Order::STATUS_PENDING) {
                        $payment->order->setStatus(Order::STATUS_CONFIRMED);
                        $payment->order->save();
                    }

                    $this->sendOrderConfirmationAfterCommit($payment->order);
                } else {
                    $this->markOrderPaymentFailed($payment);
                }
            }

            return new PaymentResult($approved, $approved ? 'approved' : 'declined', $payment, $approved ? 'Payment approved.' : 'Payment declined.');
        });
    }

    private function processCheckoutSession(array $payload, string $endpoint, string $returnOid): PaymentResult
    {
        $session = NestPayCheckoutSession::query()
            ->where('provider', Payment::PROVIDER_NESTPAY)
            ->where('provider_order_id', $returnOid)
            ->lockForUpdate()
            ->first();

        if (! $session) {
            Log::warning('NestPay response ReturnOid does not match an expected payment or checkout session.', [
                'return_oid' => $returnOid,
                'trans_id' => $this->value($payload, 'TransId'),
            ]);

            return new PaymentResult(false, 'incorrect_return_oid', null, 'Payment reference was not found.');
        }

        $session->load('order');

        if ($session->processed_at) {
            $payment = $session->order?->latestPayment()->first();

            return new PaymentResult(
                $session->status === NestPayCheckoutSession::STATUS_APPROVED,
                'duplicate',
                $payment,
                'Payment response was already processed.'
            );
        }

        $amount = $this->value($payload, 'amount') ?: $this->value($payload, 'Amount');
        $currency = $this->value($payload, 'currency') ?: $this->value($payload, 'Currency');

        if ($amount !== null && number_format((float) $amount, 2, '.', '') !== number_format((float) $session->amount, 2, '.', '')) {
            $this->markCheckoutSessionError($session, $payload, $endpoint, 'Returned payment amount does not match the expected amount.');

            return new PaymentResult(false, 'amount_mismatch', null, 'Returned payment amount does not match the expected amount.');
        }

        if ($currency !== null && (string) $currency !== (string) $session->currency_code) {
            $this->markCheckoutSessionError($session, $payload, $endpoint, 'Returned payment currency does not match the expected currency.');

            return new PaymentResult(false, 'currency_mismatch', null, 'Returned payment currency does not match the expected currency.');
        }

        $response = $this->value($payload, 'Response');
        $procReturnCode = $this->value($payload, 'ProcReturnCode');
        $approved = $response === 'Approved' && $procReturnCode === '00';

        $session->fill([
            'status' => $approved ? NestPayCheckoutSession::STATUS_APPROVED : $this->checkoutFailureStatus($response, $procReturnCode),
            'response' => $response,
            'proc_return_code' => $procReturnCode,
            'auth_code' => $this->value($payload, 'AuthCode'),
            'host_ref_num' => $this->value($payload, 'HostRefNum'),
            'trans_id' => $this->value($payload, 'TransId'),
            'md_status' => $this->value($payload, 'mdStatus') ?: $this->value($payload, 'mdstatus'),
            'masked_pan' => $this->value($payload, 'MaskedPan'),
            'payment_method' => $this->value($payload, 'PaymentMethod') ?: $this->value($payload, 'EXTRA.CARDBRAND'),
            'installment_count' => $this->installmentCountFromPayload($session->installment_count, $payload),
            'error_message' => $this->value($payload, 'ErrMsg'),
            'response_metadata' => array_merge($this->safeMetadata($payload), ['callback_endpoint' => $endpoint]),
            'paid_at' => $approved ? now() : null,
            'processed_at' => now(),
        ]);
        $session->save();

        if (! $approved) {
            return new PaymentResult(false, 'declined', null, 'Payment declined.');
        }

        $order = $this->orders->create($session->order_payload);
        $order->setStatus(Order::STATUS_CONFIRMED);
        $order->save();

        $payment = $order->payments()->create([
            'provider' => Payment::PROVIDER_NESTPAY,
            'provider_order_id' => $session->provider_order_id,
            'idempotency_key' => $session->idempotency_key,
            'amount' => $session->amount,
            'currency' => $session->currency,
            'currency_code' => $session->currency_code,
            'status' => Payment::STATUS_APPROVED,
            'response' => $session->response,
            'proc_return_code' => $session->proc_return_code,
            'auth_code' => $session->auth_code,
            'host_ref_num' => $session->host_ref_num,
            'trans_id' => $session->trans_id,
            'md_status' => $session->md_status,
            'masked_pan' => $session->masked_pan,
            'payment_method' => $session->payment_method,
            'installment_count' => $session->installment_count,
            'error_message' => $session->error_message,
            'request_metadata' => $session->request_metadata,
            'response_metadata' => $session->response_metadata,
            'paid_at' => $session->paid_at,
            'processed_at' => $session->processed_at,
        ]);

        $session->order_id = $order->id;
        $session->save();

        $this->sendOrderConfirmationAfterCommit($order);

        return new PaymentResult(true, 'approved', $payment, 'Payment approved.');
    }

    private function markError(Payment $payment, array $payload, string $code, string $message): PaymentResult
    {
        $payment->fill([
            'status' => Payment::STATUS_ERROR,
            'response' => $this->value($payload, 'Response'),
            'proc_return_code' => $this->value($payload, 'ProcReturnCode'),
            'error_message' => $message,
            'response_metadata' => $this->safeMetadata($payload),
            'processed_at' => now(),
        ]);
        $payment->save();
        $this->markOrderPaymentFailed($payment);

        Log::warning('NestPay response validation failed after hash verification.', [
            'reason' => $code,
            'payment_id' => $payment->id,
            'return_oid' => $this->value($payload, 'ReturnOid'),
        ]);

        return new PaymentResult(false, $code, $payment, $message);
    }

    private function markOrderPaymentFailed(Payment $payment): void
    {
        if (! $payment->relationLoaded('order')) {
            $payment->load('order');
        }

        if ($payment->order && $payment->order->status === Order::STATUS_PENDING) {
            $payment->order->setStatus(Order::STATUS_PAYMENT_FAILED);
            $payment->order->save();
        }
    }

    private function failureStatus(?string $response, ?string $procReturnCode): string
    {
        if ($response === 'Error' || $procReturnCode === '99') {
            return Payment::STATUS_ERROR;
        }

        return Payment::STATUS_DECLINED;
    }

    private function checkoutFailureStatus(?string $response, ?string $procReturnCode): string
    {
        if ($response === 'Error' || $procReturnCode === '99') {
            return NestPayCheckoutSession::STATUS_ERROR;
        }

        return NestPayCheckoutSession::STATUS_DECLINED;
    }

    private function markCheckoutSessionError(NestPayCheckoutSession $session, array $payload, string $endpoint, string $message): void
    {
        $session->fill([
            'status' => NestPayCheckoutSession::STATUS_ERROR,
            'response' => $this->value($payload, 'Response'),
            'proc_return_code' => $this->value($payload, 'ProcReturnCode'),
            'error_message' => $message,
            'response_metadata' => array_merge($this->safeMetadata($payload), ['callback_endpoint' => $endpoint]),
            'processed_at' => now(),
        ]);
        $session->save();
    }

    private function installmentCount(Payment $payment, array $payload): ?int
    {
        $returned = $this->value($payload, 'Instalment') ?: $this->value($payload, 'instalment');
        if ($returned !== null && $returned !== '') {
            return max(1, (int) $returned);
        }

        return $payment->installment_count;
    }

    private function installmentCountFromPayload(?int $current, array $payload): ?int
    {
        $returned = $this->value($payload, 'Instalment') ?: $this->value($payload, 'instalment');
        if ($returned !== null && $returned !== '') {
            return max(1, (int) $returned);
        }

        return $current;
    }

    private function safeMetadata(array $payload): array
    {
        return Arr::except($payload, ['HASH', 'hash', 'storekey', 'StoreKey', 'password', 'Password', 'cvv', 'CVV']);
    }

    private function sendOrderConfirmationAfterCommit(Order $order): void
    {
        if (! $order->customer_email) {
            return;
        }

        DB::afterCommit(function () use ($order): void {
            try {
                $order->loadMissing('items.product', 'latestPayment');
                Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
            } catch (Throwable $exception) {
                Log::warning('Order confirmation email could not be sent after NestPay approval.', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }

    private function value(array $payload, string $key): ?string
    {
        foreach ($payload as $name => $value) {
            if (strcasecmp((string) $name, $key) === 0) {
                return is_scalar($value) ? Str::of((string) $value)->trim()->toString() : null;
            }
        }

        return null;
    }
}

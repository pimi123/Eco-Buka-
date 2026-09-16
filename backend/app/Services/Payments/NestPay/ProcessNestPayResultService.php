<?php

namespace App\Services\Payments\NestPay;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessNestPayResultService
{
    public function __construct(
        private readonly NestPayHashService $hashes,
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

        return DB::transaction(function () use ($payload): PaymentResult {
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
                Log::warning('NestPay response ReturnOid does not match an expected payment.', [
                    'return_oid' => $returnOid,
                    'trans_id' => $this->value($payload, 'TransId'),
                ]);

                return new PaymentResult(false, 'incorrect_return_oid', null, 'Payment reference was not found.');
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
                'error_message' => $this->value($payload, 'ErrMsg'),
                'response_metadata' => $this->safeMetadata($payload),
                'paid_at' => $approved ? now() : null,
                'processed_at' => now(),
            ]);
            $payment->save();

            if ($approved && $payment->order && $payment->order->status === Order::STATUS_PENDING) {
                $payment->order->setStatus(Order::STATUS_CONFIRMED);
                $payment->order->save();
            }

            return new PaymentResult($approved, $approved ? 'approved' : 'declined', $payment, $approved ? 'Payment approved.' : 'Payment declined.');
        });
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

        Log::warning('NestPay response validation failed after hash verification.', [
            'reason' => $code,
            'payment_id' => $payment->id,
            'return_oid' => $this->value($payload, 'ReturnOid'),
        ]);

        return new PaymentResult(false, $code, $payment, $message);
    }

    private function failureStatus(?string $response, ?string $procReturnCode): string
    {
        if ($response === 'Error' || $procReturnCode === '99') {
            return Payment::STATUS_ERROR;
        }

        return Payment::STATUS_DECLINED;
    }

    private function safeMetadata(array $payload): array
    {
        return Arr::except($payload, ['HASH', 'hash', 'storekey', 'StoreKey', 'password', 'Password', 'cvv', 'CVV']);
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

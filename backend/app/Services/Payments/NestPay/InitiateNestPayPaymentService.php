<?php

namespace App\Services\Payments\NestPay;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class InitiateNestPayPaymentService
{
    public function __construct(
        private readonly NestPayHashService $hashes,
    ) {
    }

    public function initiate(Order $order, array $options = []): array
    {
        return DB::transaction(function () use ($order, $options): array {
            $order = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertOrderCanBePaid($order);

            $amount = $this->formatAmount($order->total);
            $oid = $this->generateOid($order);
            $rnd = Str::random(20);

            $payment = $this->pendingPayment($order);
            $payment->fill([
                'provider' => Payment::PROVIDER_NESTPAY,
                'provider_order_id' => $oid,
                'idempotency_key' => Payment::PROVIDER_NESTPAY.':'.$oid,
                'amount' => $amount,
                'currency' => $order->currency ?: 'EUR',
                'currency_code' => $this->config('currency'),
                'status' => Payment::STATUS_PENDING,
                'request_metadata' => [
                    'oid' => $oid,
                    'rnd' => $rnd,
                    'initiated_at' => now()->toISOString(),
                ],
                'response_metadata' => null,
                'response' => null,
                'proc_return_code' => null,
                'auth_code' => null,
                'host_ref_num' => null,
                'trans_id' => null,
                'md_status' => null,
                'masked_pan' => null,
                'payment_method' => null,
                'error_message' => null,
                'paid_at' => null,
                'processed_at' => null,
            ]);
            $payment->save();

            $parameters = $this->parameters($order, $amount, $oid, $rnd, $options);
            $parameters['hash'] = $this->hashes->generateRequestHash($parameters);

            return [
                'gateway_url' => $this->config('gateway_url'),
                'parameters' => $parameters,
            ];
        });
    }

    private function assertOrderCanBePaid(Order $order): void
    {
        if (in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED], true)) {
            throw ValidationException::withMessages([
                'order' => 'This order cannot be paid.',
            ]);
        }

        if ((float) $order->total <= 0) {
            throw ValidationException::withMessages([
                'order' => 'This order does not have a payable total.',
            ]);
        }

        if (($order->currency ?: 'EUR') !== 'EUR') {
            throw ValidationException::withMessages([
                'order' => 'This order currency is not supported for NestPay.',
            ]);
        }

        if ($order->payments()->where('provider', Payment::PROVIDER_NESTPAY)->where('status', Payment::STATUS_APPROVED)->exists()) {
            throw ValidationException::withMessages([
                'order' => 'This order has already been paid.',
            ]);
        }
    }

    private function pendingPayment(Order $order): Payment
    {
        return $order->payments()
            ->where('provider', Payment::PROVIDER_NESTPAY)
            ->where('status', Payment::STATUS_PENDING)
            ->latest('id')
            ->first() ?? new Payment(['order_id' => $order->id]);
    }

    private function parameters(Order $order, string $amount, string $oid, string $rnd, array $options): array
    {
        $parameters = [
            'clientid' => $this->config('client_id'),
            'storetype' => $this->config('store_type'),
            'trantype' => $this->config('transaction_type'),
            'amount' => $amount,
            'currency' => $this->config('currency'),
            'oid' => $oid,
            'okUrl' => $this->url('ok_url', '/order-success?payment=approved&order='.$order->order_number),
            'failUrl' => $this->url('fail_url', '/checkout?payment=failed&order='.$order->order_number),
            'lang' => $this->config('language'),
            'rnd' => $rnd,
            'hashAlgorithm' => $this->config('hash_algorithm'),
            'encoding' => 'utf-8',
        ];

        $shopUrl = $options['shopurl'] ?? $this->url('shop_url', '/checkout');
        if ($shopUrl) {
            $parameters['shopurl'] = $shopUrl;
        }

        return $parameters;
    }

    private function generateOid(Order $order): string
    {
        return Str::limit(
            'EB-'.$order->id.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(12)),
            64,
            ''
        );
    }

    private function formatAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function url(string $configKey, string $fallbackPath): string
    {
        $configured = config("nestpay.{$configKey}");
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return rtrim($this->config('frontend_url'), '/').$fallbackPath;
    }

    private function config(string $key): string
    {
        $value = config("nestpay.{$key}");

        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException("NestPay configuration [{$key}] is missing.");
        }

        return $value;
    }
}

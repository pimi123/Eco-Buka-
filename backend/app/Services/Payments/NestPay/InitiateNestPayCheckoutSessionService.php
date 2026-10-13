<?php

namespace App\Services\Payments\NestPay;

use App\Models\NestPayCheckoutSession;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class InitiateNestPayCheckoutSessionService
{
    public function __construct(
        private readonly NestPayHashService $hashes,
    ) {
    }

    public function initiate(array $checkoutData): array
    {
        return DB::transaction(function () use ($checkoutData): array {
            [$amount, $itemsSnapshot] = $this->checkoutTotal($checkoutData);
            $installmentCount = $this->validatedInstallmentCount($amount, $checkoutData);
            $oid = $this->generateOid();
            $rnd = Str::random(20);

            $session = NestPayCheckoutSession::create([
                'provider' => Payment::PROVIDER_NESTPAY,
                'provider_order_id' => $oid,
                'idempotency_key' => Payment::PROVIDER_NESTPAY.':checkout:'.$oid,
                'amount' => $amount,
                'currency' => 'EUR',
                'currency_code' => $this->config('currency'),
                'status' => NestPayCheckoutSession::STATUS_PENDING,
                'order_payload' => $checkoutData,
                'items_snapshot' => $itemsSnapshot,
                'installment_count' => $installmentCount,
                'request_metadata' => [
                    'oid' => $oid,
                    'rnd' => $rnd,
                    'installment_count' => $installmentCount,
                    'initiated_at' => now()->toISOString(),
                ],
            ]);

            $parameters = $this->parameters($session, $rnd, $checkoutData);
            $parameters['hash'] = $this->hashes->generateRequestHash($parameters);

            return [
                'gateway_url' => $this->config('gateway_url'),
                'parameters' => $parameters,
            ];
        });
    }

    private function checkoutTotal(array $checkoutData): array
    {
        $subtotalCents = 0;
        $itemsSnapshot = [];
        $productIds = collect($checkoutData['items'])->pluck('product_id')->unique()->values();
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('active', true)
            ->where('in_stock', true)
            ->with('category:id,name,slug')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($checkoutData['items'] as $index => $item) {
            $product = $products->get((int) $item['product_id']);
            if (! $product) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'This product is currently out of stock.',
                ]);
            }

            $quantity = (int) $item['quantity'];
            $unitCents = (int) round(((float) ($product->price ?? 0)) * 100);
            $lineCents = $unitCents * $quantity;
            $subtotalCents += $lineCents;

            $itemsSnapshot[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'quantity' => $quantity,
                'unit_price' => $unitCents / 100,
                'line_total' => $lineCents / 100,
                'category' => $product->category?->name,
            ];
        }

        $amount = $subtotalCents / 100;
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'items' => 'This checkout does not have a payable total.',
            ]);
        }

        return [$amount, $itemsSnapshot];
    }

    private function parameters(NestPayCheckoutSession $session, string $rnd, array $options): array
    {
        $parameters = [
            'clientid' => $this->config('client_id'),
            'storetype' => $this->config('store_type'),
            'trantype' => $this->config('transaction_type'),
            'amount' => $this->formatAmount($session->amount),
            'currency' => $this->config('currency'),
            'oid' => $session->provider_order_id,
            'okUrl' => $this->url('ok_url', '/order-success?payment=approved'),
            'failUrl' => $this->url('fail_url', '/payment-failed?payment=failed'),
            'lang' => $this->config('language'),
            'rnd' => $rnd,
            'hashAlgorithm' => $this->config('hash_algorithm'),
            'refreshtime' => $this->config('refresh_time'),
            'encoding' => 'utf-8',
        ];

        $shopUrl = $options['shopurl'] ?? $this->url('shop_url', '/checkout');
        if ($shopUrl) {
            $parameters['shopurl'] = $shopUrl;
        }

        if ($session->installment_count !== null) {
            $parameters[$this->installmentParameterName()] = (string) $session->installment_count;
        }

        return $parameters;
    }

    private function validatedInstallmentCount(float $amount, array $options): ?int
    {
        $requested = $options['installment_count'] ?? null;
        if ($requested === null || $requested === '') {
            return null;
        }

        $requested = (int) $requested;
        if (! $this->installmentsEnabled()) {
            throw ValidationException::withMessages([
                'installment_count' => 'Installment payments are not enabled.',
            ]);
        }

        if (! in_array($requested, $this->allowedInstallments(), true)) {
            throw ValidationException::withMessages([
                'installment_count' => 'The selected installment option is not available.',
            ]);
        }

        if ($amount < $this->minimumInstallmentAmount()) {
            throw ValidationException::withMessages([
                'installment_count' => 'This order total is below the minimum amount for installment payments.',
            ]);
        }

        return $requested;
    }

    private function generateOid(): string
    {
        return Str::limit(
            'EBC-'.now()->format('YmdHis').'-'.Str::upper(Str::random(16)),
            64,
            ''
        );
    }

    private function formatAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function installmentsEnabled(): bool
    {
        return filter_var(config('nestpay.installments_enabled'), FILTER_VALIDATE_BOOL);
    }

    private function allowedInstallments(): array
    {
        $configured = config('nestpay.allowed_installments', '');
        $values = is_array($configured) ? $configured : explode(',', (string) $configured);

        return collect($values)
            ->map(fn (mixed $value): int => (int) trim((string) $value))
            ->filter(fn (int $value): bool => $value >= 2)
            ->unique()
            ->values()
            ->all();
    }

    private function minimumInstallmentAmount(): float
    {
        return max(0, (float) config('nestpay.minimum_installment_amount', 0));
    }

    private function installmentParameterName(): string
    {
        $name = config('nestpay.installment_parameter', 'Instalment');

        return is_string($name) && trim($name) !== '' ? trim($name) : 'Instalment';
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

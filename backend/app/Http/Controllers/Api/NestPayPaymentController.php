<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiateNestPayCheckoutRequest;
use App\Http\Requests\InitiateNestPayPaymentRequest;
use App\Models\Order;
use App\Services\Payments\NestPay\InitiateNestPayCheckoutSessionService;
use App\Services\Payments\NestPay\InitiateNestPayPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class NestPayPaymentController extends Controller
{
    public function options(): JsonResponse
    {
        return response()->json([
            'installments_enabled' => filter_var(config('nestpay.installments_enabled'), FILTER_VALIDATE_BOOL),
            'allowed_installments' => $this->allowedInstallments(),
            'minimum_installment_amount' => max(0, (float) config('nestpay.minimum_installment_amount', 0)),
        ]);
    }

    public function store(
        InitiateNestPayPaymentRequest $request,
        Order $order,
        InitiateNestPayPaymentService $payments,
    ): JsonResponse {
        $payload = $payments->initiate($order, $request->validated());

        return response()->json($payload);
    }

    public function checkout(
        InitiateNestPayCheckoutRequest $request,
        InitiateNestPayCheckoutSessionService $payments,
    ): JsonResponse {
        $payload = $payments->initiate($request->validated());

        return response()->json($payload);
    }

    private function allowedInstallments(): array
    {
        $configured = config('nestpay.allowed_installments', '');
        $values = is_array($configured) ? $configured : explode(',', (string) $configured);

        return Collection::make($values)
            ->map(fn (mixed $value): int => (int) trim((string) $value))
            ->filter(fn (int $value): bool => $value >= 2)
            ->unique()
            ->values()
            ->all();
    }
}

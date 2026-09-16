<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiateNestPayPaymentRequest;
use App\Models\Order;
use App\Services\Payments\NestPay\InitiateNestPayPaymentService;
use Illuminate\Http\JsonResponse;

class NestPayPaymentController extends Controller
{
    public function store(
        InitiateNestPayPaymentRequest $request,
        Order $order,
        InitiateNestPayPaymentService $payments,
    ): JsonResponse {
        $payload = $payments->initiate($order, $request->validated());

        return response()->json($payload);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\Orders\CreateOrderService;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, CreateOrderService $orders)
    {
        $order = $orders->create($request->validated());

        return response()->json([
            'message' => 'Your order has been placed successfully. Our team will contact you shortly to confirm the details.',
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total' => $order->total,
            'currency' => $order->currency,
        ], 201);
    }
}

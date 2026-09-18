<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\Orders\CreateOrderService;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, CreateOrderService $orders)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $order = $orders->create($data);

        return response()->json([
            'message' => 'Your order has been placed successfully. Our team will contact you shortly to confirm the details.',
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total' => $order->total,
            'currency' => $order->currency,
        ], 201);
    }
}

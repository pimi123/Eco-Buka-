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
            'message' => $order->payment_method === 'cash'
                ? 'Porosia u regjistrua me sukses. Pagesa bëhet në dorëzim.'
                : 'Porosia u regjistrua me sukses. Ju lutemi vazhdoni te pagesa.',
            'payment_method' => $order->payment_method,
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total' => $order->total,
            'currency' => $order->currency,
        ], 201);
    }
}

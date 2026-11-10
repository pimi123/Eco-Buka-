<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\Orders\CreateOrderService;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function purchaseConfirmation(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:255'],
            'token' => ['required', 'string', 'max:255'],
        ]);
        $order = Order::where('order_number', $data['order_number'])
            ->where('tracking_token', $data['token'])->firstOrFail();
        abort_unless($order->payment_method === Order::PAYMENT_CARD
            && $order->status !== Order::STATUS_CANCELLED
            && $order->payments()->where('status', Payment::STATUS_APPROVED)->whereNotNull('paid_at')->exists(), 409);

        return response()->json([
            'id' => $order->id,
            'total' => $order->total,
            'currency' => $order->currency,
        ])->header('Cache-Control', 'no-store');
    }

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

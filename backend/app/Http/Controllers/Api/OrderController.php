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
        $payment = $order->payments()->where('status', Payment::STATUS_APPROVED)->whereNotNull('paid_at')->firstOrFail();
        abort_unless($payment->currency === $order->currency && (int) round((float) $payment->amount * 100) === (int) round((float) $order->total * 100), 409);

        return response()->json([
            'id' => $order->id,
            'total' => $order->total,
            'currency' => $order->currency,
        ])->header('Cache-Control', 'no-store');
    }

    public function store(StoreOrderRequest $request, CreateOrderService $orders)
    {
        $data = $request->validated();
        $data['tracking_context'] = app(\App\Services\Tracking\MetaConversions::class)->context($request);
        $order = $orders->create($data);

        return response()->json([
            'message' => $order->payment_method === 'cash'
                ? 'Porosia u regjistrua me sukses. Pagesa bëhet në dorëzim.'
                : 'Porosia u regjistrua me sukses. Ju lutemi vazhdoni te pagesa.',
            'payment_method' => $order->payment_method,
            'id' => $order->id,
            'order_number' => $order->order_number,
            'tracking_token' => $order->tracking_token,
            'submission_event_id' => 'order_submitted_'.$order->id,
            'status' => $order->status,
            'total' => $order->total,
            'currency' => $order->currency,
        ], 201);
    }
}

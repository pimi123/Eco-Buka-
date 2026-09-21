<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Services\Orders\CreateOrderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, CreateOrderService $orders)
    {
        $order = $orders->create($request->validated());
        $this->sendOrderConfirmation($order);

        return response()->json([
            'message' => 'Porosia u regjistrua me sukses. Një email konfirmimi u dërgua me detajet e porosisë.',
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total' => $order->total,
            'currency' => $order->currency,
        ], 201);
    }

    private function sendOrderConfirmation(Order $order): void
    {
        if (! $order->customer_email) {
            return;
        }

        try {
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
        } catch (Throwable $exception) {
            Log::warning('Order confirmation email could not be sent.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

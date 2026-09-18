<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['items', 'latestPayment'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Order $order): array => $this->orderPayload($order))
            ->values();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    private function orderPayload(Order $order): array
    {
        $payment = $order->latestPayment;

        return [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $this->orderStatusLabel($order->status),
            'payment_status' => $payment?->status,
            'payment_status_label' => $this->paymentStatusLabel($payment?->status),
            'total' => $order->total,
            'currency' => $order->currency,
            'created_at' => $order->created_at?->toISOString(),
            'customer_name' => $order->customer_name,
            'delivery_area' => implode(', ', array_filter([
                $order->municipality,
                $order->city,
                $order->country,
            ])),
            'items' => $order->items->map(fn ($item): array => [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
                'image_url' => $item->product_snapshot['image_url'] ?? null,
                'category' => $item->product_snapshot['category'] ?? null,
                'selected_options' => $item->selected_options,
            ])->values(),
        ];
    }

    private function orderStatusLabel(string $status): string
    {
        return [
            Order::STATUS_PENDING => 'Në pritje',
            Order::STATUS_CONFIRMED => 'E konfirmuar',
            Order::STATUS_PROCESSING => 'Në përgatitje',
            Order::STATUS_COMPLETED => 'E përfunduar',
            Order::STATUS_CANCELLED => 'E anuluar',
        ][$status] ?? $status;
    }

    private function paymentStatusLabel(?string $status): string
    {
        return [
            'pending' => 'Në pritje',
            'approved' => 'E aprovuar',
            'declined' => 'E refuzuar',
            'error' => 'Gabim',
            'cancelled' => 'E anuluar',
        ][$status ?? ''] ?? 'Pa pagesë';
    }
}

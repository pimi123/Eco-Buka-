<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicOrderController extends Controller
{
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:120'],
        ]);

        $order = Order::query()
            ->where('order_number', $orderNumber)
            ->where('tracking_token', $data['token'])
            ->with(['items', 'latestPayment'])
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Porosia nuk u gjet me këto të dhëna.',
            ], 404);
        }

        return response()->json($this->orderPayload($order));
    }

    public function track(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:80'],
            'contact' => ['required', 'string', 'max:255'],
        ]);

        $order = Order::query()
            ->where('order_number', $data['order_number'])
            ->with(['items', 'latestPayment'])
            ->first();

        if (! $order || ! $this->contactMatches($order, $data['contact'])) {
            return response()->json([
                'message' => 'Porosia nuk u gjet me këto të dhëna.',
            ], 404);
        }

        return response()->json($this->orderPayload($order));
    }

    private function contactMatches(Order $order, string $contact): bool
    {
        $contact = trim($contact);

        if ($order->customer_email && Str::lower($order->customer_email) === Str::lower($contact)) {
            return true;
        }

        return $this->normalizePhone($order->customer_phone) === $this->normalizePhone($contact);
    }

    private function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?? '';
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
            Order::STATUS_PAYMENT_FAILED => 'Pagesa dështoi',
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

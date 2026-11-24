<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Tracking\MetaConversions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class ConfirmCashPayment
{
    public function confirm(Order $order, array $data, int $adminId): Payment
    {
        return DB::transaction(function () use ($order, $data, $adminId) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->payment_method !== Order::PAYMENT_CASH || $order->status === Order::STATUS_CANCELLED
                || $order->currency !== 'EUR' || ($data['currency'] ?? null) !== 'EUR' || (float) $data['amount'] <= 0
                || (int) round((float) $data['amount'] * 100) !== (int) round((float) $order->total * 100)) {
                throw ValidationException::withMessages(['amount' => 'Only the full received EUR amount of an eligible cash order can be confirmed.']);
            }
            $receivedAt = Carbon::parse($data['received_at']);
            if ($receivedAt->isFuture() || $receivedAt->lt($order->created_at->startOfSecond())) {
                throw ValidationException::withMessages(['received_at' => 'Use the actual collection time between order creation and now.']);
            }
            $existing = $order->payments()->where('status', Payment::STATUS_APPROVED)->first();
            if ($existing) {
                \App\Models\MetaConversionEvent::where('order_id', $order->id)->where('event_name', 'Purchase')->increment('duplicate_attempts');
                return $existing;
            }
            $payment = $order->payments()->create([
                'provider' => 'manual_cash', 'provider_order_id' => $data['reference'],
                'idempotency_key' => 'manual_cash:'.$order->id,
                'amount' => $order->total, 'currency' => 'EUR', 'currency_code' => '978',
                'status' => Payment::STATUS_APPROVED, 'paid_at' => $receivedAt, 'processed_at' => now(),
                'confirmed_by' => $adminId, 'action_source' => $data['action_source'],
            ]);
            app(MetaConversions::class)->purchased($payment);
            return $payment;
        });
    }
}

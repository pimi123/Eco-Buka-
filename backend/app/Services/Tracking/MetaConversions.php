<?php

namespace App\Services\Tracking;

use App\Models\MetaConversionEvent;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class MetaConversions
{
    public const CONSENT_VERSION = '2026-11-20';

    public function withdraw(Order $order): void
    {
        $order->update(['tracking_context' => ['marketing' => false]]);
        MetaConversionEvent::where('order_id', $order->id)->where('status', '!=', 'delivered')->update(['status' => 'revoked', 'payload' => null]);
        MetaConversionEvent::where('order_id', $order->id)->where('status', 'delivered')->update(['payload' => null]);
    }

    public function context(Request $request): array
    {
        if (! config('meta.enabled')) return ['marketing' => false];
        $input = $request->validated('tracking', []);
        if (($input['consent_version'] ?? null) !== self::CONSENT_VERSION || ! filter_var($input['marketing'] ?? false, FILTER_VALIDATE_BOOL)) {
            return ['marketing' => false];
        }
        $context = [
            'marketing' => true, 'consent_version' => self::CONSENT_VERSION,
            'consented_at' => now()->toISOString(),
            'client_ip_address' => $request->ip(),
            'client_user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ];
        foreach (['fbp', 'fbc'] as $field) {
            $value = $input[$field] ?? null;
            if (is_string($value) && preg_match('/^fb\.\d+\.\d{13}\.[A-Za-z0-9_\-\.]+$/', $value)) {
                $context[$field] = $value;
            }
        }

        return $context;
    }

    public function submitted(Order $order): MetaConversionEvent
    {
        return $this->record($order, 'OrderSubmitted', $order->created_at, (float) $order->total, 'website');
    }

    public function purchased(Payment $payment): ?MetaConversionEvent
    {
        $order = $payment->order;
        if (! $order || $order->status === Order::STATUS_CANCELLED || $payment->status !== Payment::STATUS_APPROVED || ! $payment->paid_at
            || $order->currency !== 'EUR' || $payment->currency !== 'EUR' || (int) round((float) $payment->amount * 100) !== (int) round((float) $order->total * 100)
            || (float) $payment->amount <= 0) {
            return null;
        }

        return $this->record($order, 'Purchase', $payment->paid_at, (float) $payment->amount, $payment->action_source ?: 'website', $payment);
    }

    private function record(Order $order, string $name, $time, float $value, string $source, ?Payment $payment = null): MetaConversionEvent
    {
        $context = $order->tracking_context ?? [];
        $enabled = (bool) config('meta.enabled');
        $eligible = $enabled && ($context['marketing'] ?? false) === true && ($context['consent_version'] ?? null) === self::CONSENT_VERSION;
        $id = ($name === 'Purchase' ? 'purchase_' : 'order_submitted_').$order->id;
        $user = [];
        if ($eligible) {
            $email = mb_strtolower(trim((string) $order->customer_email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) $user['em'] = [hash('sha256', $email)];
            $rawPhone = trim((string) $order->customer_phone);
            $phone = preg_replace('/\D/', '', $rawPhone);
            if (str_starts_with($rawPhone, '00')) $phone = substr($phone, 2);
            // Do not infer international country codes from an ambiguous local number.
            $international = str_starts_with($rawPhone, '+') || str_starts_with($rawPhone, '00') || preg_match('/^(383|355|389)/', $phone);
            if ($international && preg_match('/^[1-9]\d{6,14}$/', $phone)) $user['ph'] = [hash('sha256', $phone)];
            foreach (['client_ip_address', 'client_user_agent', 'fbp', 'fbc'] as $field) {
                if (! empty($context[$field])) $user[$field] = $context[$field];
            }
        }
        $data = ['currency' => 'EUR', 'value' => $value, 'order_id' => (string) $order->order_number];
        if (config('meta.catalog_ids_verified')) {
            $items = $order->items()->get();
            $data['content_type'] = 'product';
            $data['contents'] = $items->map(fn ($item) => ['id' => (string) ($item->product_snapshot['id'] ?? $item->product_id), 'quantity' => $item->quantity, 'item_price' => (float) $item->unit_price])->all();
            $data['content_ids'] = array_column($data['contents'], 'id');
        }
        $payload = ['event_name' => $name, 'event_id' => $id, 'event_time' => $time->timestamp, 'action_source' => $source, 'user_data' => $user, 'custom_data' => $data];
        if ($source === 'website') $payload['event_source_url'] = rtrim(config('meta.site_url'), '/').'/checkout';
        $validUser = ! empty($user['em']) || ! empty($user['ph']) || ! empty($user['fbc']) || ! empty($user['fbp']);

        $event = MetaConversionEvent::firstOrCreate(['order_id' => $order->id, 'event_name' => $name], [
            'payment_id' => $payment?->id, 'event_id' => $id, 'dataset_id' => (string) config('meta.pixel_id'),
            'occurred_at' => $time, 'value' => $value, 'currency' => 'EUR',
            'status' => ! $enabled ? 'disabled' : ($eligible && $validUser ? 'pending' : 'ineligible'),
            'payload' => $eligible && $validUser ? $payload : null,
        ]);
        if (! $event->wasRecentlyCreated) $event->increment('duplicate_attempts');
        return $event;
    }
}

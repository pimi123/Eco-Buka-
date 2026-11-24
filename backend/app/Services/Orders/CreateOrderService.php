<?php

namespace App\Services\Orders;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\CheckoutAttempt;
use App\Services\Tracking\MetaConversions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateOrderService
{
    public function create(array $data, ?array $paidSnapshot = null): Order
    {
        return DB::transaction(function () use ($data, $paidSnapshot): Order {
            $attempt = null;
            if (! empty($data['checkout_key'])) {
                $hash = hash('sha256', json_encode(Arr::sortRecursive(Arr::except($data, ['checkout_key', 'tracking', 'tracking_context'])), JSON_THROW_ON_ERROR));
                CheckoutAttempt::firstOrCreate(['key' => $data['checkout_key']], ['payload_hash' => $hash]);
                $attempt = CheckoutAttempt::whereKey($data['checkout_key'])->lockForUpdate()->firstOrFail();
                if (! hash_equals($attempt->payload_hash, $hash)) throw ValidationException::withMessages(['checkout_key' => 'This checkout attempt has already been used with different data.']);
                if ($attempt->order_id) {
                    $existing = Order::whereKey($attempt->order_id)->lockForUpdate()->firstOrFail();
                    // A retry must not retain old marketing permission after a refusal.
                    if (($data['tracking_context']['marketing'] ?? false) !== true) app(MetaConversions::class)->withdraw($existing);
                    return $existing->load('items');
                }
            }
            $order = Order::create([
                'tracking_context' => $data['tracking_context'] ?? ['marketing' => false],
                'payment_method' => $data['payment_method'] ?? Order::PAYMENT_CARD,
                'order_number' => $this->nextOrderNumber(),
                'tracking_token' => $this->trackingToken(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'country' => $data['country'],
                'municipality' => $data['municipality'],
                'delivery_address' => $data['delivery_address'],
                'city' => $data['city'],
                'postal_code' => $data['postal_code'] ?? null,
                'delivery_details' => $data['delivery_details'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'policy_accepted_at' => now(),
                'status' => Order::STATUS_PENDING,
                'delivery_fee' => 0,
                'currency' => 'EUR',
            ]);

            $subtotalCents = 0;
            $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->when($paidSnapshot === null, fn ($query) => $query->where('active', true)->where('in_stock', true))
                ->with('category:id,name,slug')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($data['items'] as $index => $item) {
                $product = $products->get((int) $item['product_id']);
                $frozen = $paidSnapshot !== null ? collect($paidSnapshot)->firstWhere('product_id', (int) $item['product_id']) : null;
                if ((! $product && $paidSnapshot === null) || ($paidSnapshot !== null && ! $frozen)) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => 'This product is currently out of stock.',
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $unitCents = (int) round(((float) ($frozen['unit_price'] ?? $product?->price ?? 0)) * 100);
                $lineCents = $unitCents * $quantity;
                $subtotalCents += $lineCents;

                $order->items()->create([
                    'product_id' => $product?->id,
                    'product_name' => $frozen['name'] ?? $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitCents / 100,
                    'line_total' => $lineCents / 100,
                    'selected_options' => $item['selected_options'] ?? null,
                    'product_snapshot' => [
                        'id' => $item['product_id'],
                        'name' => $frozen['name'] ?? $product?->name,
                        'slug' => $frozen['slug'] ?? $product?->slug,
                        'price' => $unitCents / 100,
                        'old_price' => $paidSnapshot === null ? $product?->old_price : null,
                        'image_url' => $product?->main_image_url,
                        'category' => $frozen['category'] ?? $product?->category?->name,
                        'specs' => $product?->specs,
                    ],
                ]);
            }

            $order->subtotal = $subtotalCents / 100;
            $order->total = ($subtotalCents / 100) + (float) $order->delivery_fee;
            $order->save();
            $attempt?->update(['order_id' => $order->id]);
            app(MetaConversions::class)->submitted($order);

            if ($order->payment_method === Order::PAYMENT_CASH) {
                DB::afterCommit(function () use ($order): void {
                    try {
                        Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
                    } catch (\Throwable $exception) {
                        Log::warning('Cash order confirmation email could not be sent.', [
                            'order_id' => $order->id,
                            'error' => 'mail_delivery_failed',
                        ]);
                    }
                });
            }

            return $order->load('items');
        });
    }

    private function nextOrderNumber(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';
        $latest = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('order_number');

        $next = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        do {
            $orderNumber = $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    private function trackingToken(): string
    {
        do {
            $token = 'ord_'.Str::lower(Str::random(48));
        } while (Order::query()->where('tracking_token', $token)->exists());

        return $token;
    }
}

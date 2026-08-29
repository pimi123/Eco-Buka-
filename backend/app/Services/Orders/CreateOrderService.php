<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrderService
{
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'delivery_address' => $data['delivery_address'],
                'city' => $data['city'],
                'delivery_details' => $data['delivery_details'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'status' => Order::STATUS_PENDING,
                'delivery_fee' => 0,
                'currency' => 'EUR',
            ]);

            $subtotalCents = 0;
            $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->where('active', true)
                ->where('in_stock', true)
                ->with('category:id,name,slug')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($data['items'] as $index => $item) {
                $product = $products->get((int) $item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages([
                        "items.{$index}.product_id" => 'This product is currently out of stock.',
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $unitCents = (int) round(((float) ($product->price ?? 0)) * 100);
                $lineCents = $unitCents * $quantity;
                $subtotalCents += $lineCents;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitCents / 100,
                    'line_total' => $lineCents / 100,
                    'selected_options' => $item['selected_options'] ?? null,
                    'product_snapshot' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => $product->price,
                        'old_price' => $product->old_price,
                        'image_url' => $product->main_image_url,
                        'category' => $product->category?->name,
                        'specs' => $product->specs,
                    ],
                ]);
            }

            $order->subtotal = $subtotalCents / 100;
            $order->total = ($subtotalCents / 100) + (float) $order->delivery_fee;
            $order->save();

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
}

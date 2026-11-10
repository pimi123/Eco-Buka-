<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_verified_card_payments_return_purchase_data(): void
    {
        $order = Order::create([
            'order_number' => 'PIXEL-TEST', 'tracking_token' => 'private-token',
            'payment_method' => 'card', 'status' => 'pending',
            'customer_name' => 'Test', 'customer_phone' => '123456789',
            'delivery_address' => 'Test street', 'city' => 'Prishtina',
            'subtotal' => 25, 'delivery_fee' => 0, 'total' => 25, 'currency' => 'EUR',
        ]);
        $payload = ['order_number' => $order->order_number, 'token' => 'private-token'];
        $this->postJson('/api/orders/purchase-confirmation', [...$payload, 'token' => 'wrong'])->assertNotFound();
        $this->postJson('/api/orders/purchase-confirmation', $payload)->assertStatus(409);
        $order->payments()->create([
            'provider' => 'nestpay', 'provider_order_id' => 'PIXEL-TEST',
            'amount' => 25, 'currency' => 'EUR', 'currency_code' => '978',
            'status' => Payment::STATUS_APPROVED, 'paid_at' => now(),
        ]);
        $this->postJson('/api/orders/purchase-confirmation', $payload)
            ->assertOk()->assertExactJson(['id' => $order->id, 'total' => '25.00', 'currency' => 'EUR']);
        $order->update(['status' => Order::STATUS_CANCELLED]);
        $this->postJson('/api/orders/purchase-confirmation', $payload)->assertStatus(409);
    }
}

<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CashOrderTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        $product = Product::create([
            'name' => 'Cash test product', 'slug' => 'cash-test',
            'price' => 100, 'active' => true, 'in_stock' => true,
        ]);

        return [
            'customer_name' => 'Test Customer',
            'customer_phone' => '+38344111222',
            'customer_email' => 'customer@example.test',
            'country' => 'Kosove', 'municipality' => 'Prishtina',
            'delivery_address' => 'Test Street 1', 'city' => 'Prishtina',
            'policy_accepted' => true, 'payment_method' => 'cash',
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ];
    }

    public function test_cash_order_is_saved_unpaid_and_emailed_after_commit(): void
    {
        Mail::fake();
        $this->postJson('/api/orders', $this->payload())
            ->assertCreated()->assertJsonPath('payment_method', 'cash')
            ->assertJsonPath('status', 'pending')->assertJsonPath('total', '200.00');

        $order = Order::sole();
        $this->assertDatabaseCount('payments', 0);
        Mail::assertSent(OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id
                && $mail->hasTo('customer@example.test');
        });
        Mail::assertSentCount(1);
        $this->assertStringContainsString('E papaguar', (new OrderConfirmationMail($order))->render());
        $this->postJson("/api/orders/{$order->id}/payments/nestpay")
            ->assertUnprocessable()->assertJsonValidationErrors('order');
    }

    public function test_card_order_does_not_send_email_before_payment(): void
    {
        Mail::fake();
        $payload = $this->payload();
        $payload['payment_method'] = 'card';
        config(['nestpay.gateway_url' => 'https://bank.example.test/pay',
            'nestpay.client_id' => 'test-client', 'nestpay.store_key' => 'test-key']);
        $this->postJson('/api/orders', $payload)->assertCreated()
            ->assertJsonPath('status', 'pending_payment');
        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
    }

    public function test_invalid_method_does_not_create_order_or_send_email(): void
    {
        Mail::fake();
        $payload = $this->payload();
        $payload['payment_method'] = 'invalid';
        $this->postJson('/api/orders', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
        Mail::assertNothingSent();
    }
}

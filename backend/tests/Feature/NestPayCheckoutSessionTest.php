<?php

namespace Tests\Feature;

use App\Models\NestPayCheckoutSession;
use App\Models\Order;
use App\Models\Product;
use App\Services\Payments\NestPay\NestPayHashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NestPayCheckoutSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config([
            'nestpay.gateway_url' => 'https://bank.example.test/pay',
            'nestpay.client_id' => 'test-client',
            'nestpay.store_key' => 'test-key',
            'nestpay.frontend_url' => 'https://shop.example.test',
            'nestpay.installments_enabled' => false,
            'nestpay.ok_url' => null,
            'nestpay.fail_url' => null,
        ]);
    }

    private function initiate(): Product
    {
        $product = Product::create([
            'name' => 'Session product', 'slug' => 'session-product',
            'price' => 100, 'active' => true, 'in_stock' => true,
        ]);
        $this->postJson('/api/checkout/nestpay', [
            'payment_method' => 'card', 'customer_name' => 'Test Customer',
            'customer_phone' => '+38344111222', 'customer_email' => 'test@example.test',
            'country' => 'Kosove', 'municipality' => 'Prishtina',
            'delivery_address' => 'Test Street', 'city' => 'Prishtina',
            'policy_accepted' => true,
            'items' => [['product_id' => $product->id, 'quantity' => 2,
                'selected_options' => ['color' => 'black']]],
        ])->assertOk()->assertJsonPath('parameters.amount', '200.00')
            ->assertJsonPath('parameters.okUrl', config('nestpay.ok_url') ?: url('/api/payments/nestpay/success'))
            ->assertJsonPath('parameters.failUrl', config('nestpay.fail_url') ?: url('/api/payments/nestpay/failure'));
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('nestpay_checkout_sessions', 1);
        Mail::assertNothingSent();
        return $product;
    }

    public function test_configured_public_callback_urls_are_preserved(): void
    {
        config([
            'nestpay.ok_url' => 'https://dev.example.test/api/payments/nestpay/success',
            'nestpay.fail_url' => 'https://dev.example.test/api/payments/nestpay/failure',
        ]);
        $this->initiate();
    }

    public function test_old_two_request_checkout_continues_to_bank_without_creating_an_order(): void
    {
        $this->initiate();
        $payload = NestPayCheckoutSession::sole()->order_payload;
        $response = $this->postJson('/api/orders', $payload)->assertCreated();
        $reference = $response->json('id');
        $this->assertStringStartsWith('checkout-', $reference);
        $this->assertDatabaseCount('orders', 0);

        $payment = $this->postJson("/api/orders/{$reference}/payments/nestpay", [
            'shopurl' => 'https://shop.example.test/checkout',
        ])->assertOk()->assertJsonPath('gateway_url', 'https://bank.example.test/pay')
            ->assertJsonPath('parameters.amount', '200.00')
            ->assertJsonPath('parameters.shopurl', 'https://shop.example.test/checkout');
        $this->assertSame(substr($reference, strlen('checkout-')), $payment->json('parameters.oid'));
        $this->assertNotEmpty($payment->json('parameters.hash'));
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('nestpay_checkout_sessions', 2);

        $bankPayload = [
            'ReturnOid' => $payment->json('parameters.oid'),
            'Response' => 'Approved', 'ProcReturnCode' => '00',
            'amount' => '200.00', 'currency' => '978',
        ];
        $bankPayload['HASH'] = app(NestPayHashService::class)->generateRequestHash($bankPayload, 'test-key');
        $this->post('/api/payments/nestpay/success', $bankPayload)->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->postJson("/api/orders/{$reference}/payments/nestpay", [])
            ->assertUnprocessable()->assertJsonValidationErrors('payment');
        $this->assertDatabaseCount('orders', 1);
    }

    private function signedResult(array $overrides = []): array
    {
        $payload = array_merge([
            'ReturnOid' => NestPayCheckoutSession::sole()->provider_order_id,
            'Response' => 'Approved', 'ProcReturnCode' => '00',
            'amount' => '200.00', 'currency' => '978', 'TransId' => 'test-transaction',
        ], $overrides);
        $payload['HASH'] = app(NestPayHashService::class)->generateRequestHash($payload, 'test-key');
        return $payload;
    }

    public function test_approved_payment_creates_one_order_at_paid_price_even_after_product_changes(): void
    {
        $product = $this->initiate();
        $product->update(['price' => 150, 'in_stock' => false]);
        $payload = $this->signedResult();
        $this->post('/api/payments/nestpay/success', $payload)->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
        $order = Order::sole();
        $this->assertSame('200.00', $order->total);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame(['color' => 'black'], $order->items()->first()->selected_options);
        $this->post('/api/payments/nestpay/success', $payload)->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_declined_payment_creates_no_order(): void
    {
        $this->initiate();
        $this->post('/api/payments/nestpay/failure', $this->signedResult([
            'Response' => 'Declined', 'ProcReturnCode' => '05',
        ]))->assertRedirect();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
        Mail::assertNothingSent();
    }

    public function test_invalid_hash_and_mismatched_amount_create_no_order(): void
    {
        $this->initiate();
        $payload = $this->signedResult();
        $payload['HASH'] = 'invalid';
        $this->post('/api/payments/nestpay/success', $payload)->assertRedirect();
        $this->assertNull(NestPayCheckoutSession::sole()->processed_at);
        $this->post('/api/payments/nestpay/success', $this->signedResult(['amount' => '1.00']))->assertRedirect();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
    }
}

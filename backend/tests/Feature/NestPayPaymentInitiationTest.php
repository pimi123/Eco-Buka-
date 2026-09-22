<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NestPayPaymentInitiationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'nestpay.gateway_url' => 'https://bank.example.test/fim/est3dgate',
            'nestpay.client_id' => '290300000',
            'nestpay.store_key' => 'test-store-key',
            'nestpay.store_type' => '3d_pay_hosting',
            'nestpay.transaction_type' => 'Auth',
            'nestpay.currency' => '978',
            'nestpay.language' => 'en',
            'nestpay.hash_algorithm' => 'ver3',
            'nestpay.refresh_time' => '5',
            'nestpay.installments_enabled' => false,
            'nestpay.allowed_installments' => '',
            'nestpay.minimum_installment_amount' => '0',
            'nestpay.installment_parameter' => 'Instalment',
            'nestpay.frontend_url' => 'https://shop.example.test',
            'nestpay.ok_url' => null,
            'nestpay.fail_url' => null,
            'nestpay.shop_url' => null,
        ]);
    }

    public function test_it_initiates_nestpay_payment_from_authoritative_order_total(): void
    {
        $order = $this->order(['total' => 549.90]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/nestpay", [
            'amount' => '1.00',
        ]);

        $response->assertOk()
            ->assertJsonPath('gateway_url', 'https://bank.example.test/fim/est3dgate')
            ->assertJsonPath('parameters.clientid', '290300000')
            ->assertJsonPath('parameters.storetype', '3d_pay_hosting')
            ->assertJsonPath('parameters.trantype', 'Auth')
            ->assertJsonPath('parameters.amount', '549.90')
            ->assertJsonPath('parameters.currency', '978')
            ->assertJsonPath('parameters.lang', 'en')
            ->assertJsonPath('parameters.hashAlgorithm', 'ver3')
            ->assertJsonPath('parameters.refreshtime', '5')
            ->assertJsonPath('parameters.encoding', 'utf-8');

        $parameters = $response->json('parameters');

        $this->assertArrayNotHasKey('Instalment', $parameters);
        $this->assertArrayHasKey('hash', $parameters);
        $this->assertArrayHasKey('oid', $parameters);
        $this->assertArrayHasKey('rnd', $parameters);
        $this->assertLessThanOrEqual(64, strlen($parameters['oid']));
        $this->assertSame(20, strlen($parameters['rnd']));
        $this->assertStringNotContainsString('test-store-key', $response->getContent());
        $this->assertStringNotContainsString('store_key', $response->getContent());

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => Payment::PROVIDER_NESTPAY,
            'provider_order_id' => $parameters['oid'],
            'amount' => 549.90,
            'currency' => 'EUR',
            'currency_code' => '978',
            'status' => Payment::STATUS_PENDING,
        ]);
    }

    public function test_it_exposes_safe_installment_options(): void
    {
        config([
            'nestpay.installments_enabled' => true,
            'nestpay.allowed_installments' => '3,6,12',
            'nestpay.minimum_installment_amount' => '100',
        ]);

        $this->getJson('/api/payments/nestpay/options')
            ->assertOk()
            ->assertJson([
                'installments_enabled' => true,
                'allowed_installments' => [3, 6, 12],
                'minimum_installment_amount' => 100,
            ])
            ->assertJsonMissing(['store_key' => 'test-store-key']);
    }

    public function test_it_initiates_nestpay_payment_with_allowed_installment_count(): void
    {
        config([
            'nestpay.installments_enabled' => true,
            'nestpay.allowed_installments' => '3,6,12',
            'nestpay.minimum_installment_amount' => '100',
        ]);

        $order = $this->order(['total' => 549.90]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/nestpay", [
            'installment_count' => 6,
        ]);

        $response->assertOk()
            ->assertJsonPath('parameters.Instalment', '6');

        $payment = $order->payments()->first();
        $this->assertSame(6, $payment->installment_count);
        $this->assertSame(6, $payment->request_metadata['installment_count']);
    }

    public function test_it_rejects_installment_count_when_installments_are_disabled(): void
    {
        $order = $this->order(['total' => 549.90]);

        $this->postJson("/api/orders/{$order->id}/payments/nestpay", [
            'installment_count' => 6,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['installment_count']);
    }

    public function test_it_rejects_installment_count_that_is_not_allowed(): void
    {
        config([
            'nestpay.installments_enabled' => true,
            'nestpay.allowed_installments' => '3,6',
            'nestpay.minimum_installment_amount' => '100',
        ]);

        $order = $this->order(['total' => 549.90]);

        $this->postJson("/api/orders/{$order->id}/payments/nestpay", [
            'installment_count' => 12,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['installment_count']);
    }

    public function test_it_rejects_installments_below_minimum_order_total(): void
    {
        config([
            'nestpay.installments_enabled' => true,
            'nestpay.allowed_installments' => '3,6',
            'nestpay.minimum_installment_amount' => '100',
        ]);

        $order = $this->order(['subtotal' => 80.00, 'total' => 80.00]);

        $this->postJson("/api/orders/{$order->id}/payments/nestpay", [
            'installment_count' => 3,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['installment_count']);
    }

    public function test_it_updates_existing_pending_nestpay_payment_for_the_order(): void
    {
        $order = $this->order(['total' => 120.00]);
        $payment = $order->payments()->create([
            'provider' => Payment::PROVIDER_NESTPAY,
            'provider_order_id' => 'OLD-OID',
            'amount' => 120.00,
            'currency' => 'EUR',
            'currency_code' => '978',
            'status' => Payment::STATUS_PENDING,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/payments/nestpay");

        $response->assertOk();

        $this->assertSame(1, $order->payments()->count());
        $this->assertSame($payment->id, $order->payments()->first()->id);
        $this->assertSame($response->json('parameters.oid'), $payment->fresh()->provider_order_id);
    }

    public function test_it_rejects_orders_that_cannot_be_paid(): void
    {
        $order = $this->order([
            'status' => Order::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        $this->postJson("/api/orders/{$order->id}/payments/nestpay")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order']);
    }

    public function test_it_rejects_already_approved_nestpay_payment(): void
    {
        $order = $this->order();
        $order->payments()->create([
            'provider' => Payment::PROVIDER_NESTPAY,
            'provider_order_id' => 'APPROVED-OID',
            'amount' => $order->total,
            'currency' => 'EUR',
            'currency_code' => '978',
            'status' => Payment::STATUS_APPROVED,
        ]);

        $this->postJson("/api/orders/{$order->id}/payments/nestpay")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order']);
    }

    private function order(array $overrides = []): Order
    {
        return Order::create([
            'order_number' => 'ORD-20260916-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_name' => 'Eco Buka Customer',
            'customer_phone' => '+38345977007',
            'customer_email' => 'customer@example.test',
            'country' => 'Kosove',
            'municipality' => 'Prishtine',
            'delivery_address' => 'Rr. Test 1',
            'city' => 'Prishtine',
            'status' => Order::STATUS_PENDING,
            'subtotal' => 549.90,
            'delivery_fee' => 0,
            'total' => 549.90,
            'currency' => 'EUR',
            'policy_accepted_at' => now(),
            ...$overrides,
        ]);
    }
}

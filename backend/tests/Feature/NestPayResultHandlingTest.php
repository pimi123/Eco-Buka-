<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\NestPay\NestPayHashService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NestPayResultHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'nestpay.store_key' => 'test-store-key',
            'nestpay.frontend_url' => 'https://shop.example.test',
        ]);
    }

    public function test_it_processes_valid_approved_response(): void
    {
        [$order, $payment] = $this->pendingPayment();

        $response = $this->post('/api/payments/nestpay/success', $this->signedPayload([
            'ReturnOid' => $payment->provider_order_id,
            'Response' => 'Approved',
            'ProcReturnCode' => '00',
            'AuthCode' => 'AUTH123',
            'HostRefNum' => 'HOST123',
            'TransId' => 'TRANS123',
            'mdStatus' => '1',
            'MaskedPan' => '545616******1234',
            'PaymentMethod' => 'MASTERCARD',
            'EXTRA.CARDBRAND' => 'MASTERCARD',
            'amount' => '549.90',
            'currency' => '978',
        ]));

        $response->assertRedirect('https://shop.example.test/order-success?payment=approved');

        $payment->refresh();
        $this->assertSame(Payment::STATUS_APPROVED, $payment->status);
        $this->assertSame('Approved', $payment->response);
        $this->assertSame('00', $payment->proc_return_code);
        $this->assertSame('1', $payment->md_status);
        $this->assertSame('MASTERCARD', $payment->payment_method);
        $this->assertNotNull($payment->paid_at);
        $this->assertNotNull($payment->processed_at);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
    }

    public function test_it_rejects_invalid_response_hash(): void
    {
        [, $payment] = $this->pendingPayment();

        $response = $this->post('/api/payments/nestpay/success', [
            'ReturnOid' => $payment->provider_order_id,
            'Response' => 'Approved',
            'ProcReturnCode' => '00',
            'HASH' => 'invalid-hash',
        ]);

        $response->assertRedirect('https://shop.example.test/checkout?payment=invalid_hash');

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertNull($payment->processed_at);
    }

    public function test_it_processes_declined_response(): void
    {
        [, $payment] = $this->pendingPayment();

        $this->post('/api/payments/nestpay/failure', $this->signedPayload([
            'ReturnOid' => $payment->provider_order_id,
            'Response' => 'Declined',
            'ProcReturnCode' => '05',
            'ErrMsg' => 'Declined by issuer',
            'amount' => '549.90',
            'currency' => '978',
        ]))->assertRedirect('https://shop.example.test/checkout?payment=declined');

        $payment->refresh();
        $this->assertSame(Payment::STATUS_DECLINED, $payment->status);
        $this->assertSame('Declined by issuer', $payment->error_message);
        $this->assertNotNull($payment->processed_at);
        $this->assertNull($payment->paid_at);
    }

    public function test_it_processes_gateway_error_proc_return_code_99(): void
    {
        [, $payment] = $this->pendingPayment();

        $this->post('/api/payments/nestpay/failure', $this->signedPayload([
            'ReturnOid' => $payment->provider_order_id,
            'Response' => 'Error',
            'ProcReturnCode' => '99',
            'ErrMsg' => 'Gateway error',
            'amount' => '549.90',
            'currency' => '978',
        ]))->assertRedirect('https://shop.example.test/checkout?payment=declined');

        $payment->refresh();
        $this->assertSame(Payment::STATUS_ERROR, $payment->status);
        $this->assertSame('99', $payment->proc_return_code);
        $this->assertSame('Gateway error', $payment->error_message);
    }

    public function test_it_rejects_incorrect_return_oid(): void
    {
        [, $payment] = $this->pendingPayment();

        $this->post('/api/payments/nestpay/success', $this->signedPayload([
            'ReturnOid' => 'UNKNOWN-OID',
            'Response' => 'Approved',
            'ProcReturnCode' => '00',
            'amount' => '549.90',
            'currency' => '978',
        ]))->assertRedirect('https://shop.example.test/checkout?payment=incorrect_return_oid');

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_duplicate_response_is_idempotent(): void
    {
        [, $payment] = $this->pendingPayment();
        $payload = $this->signedPayload([
            'ReturnOid' => $payment->provider_order_id,
            'Response' => 'Approved',
            'ProcReturnCode' => '00',
            'TransId' => 'TRANS-DUPLICATE',
            'amount' => '549.90',
            'currency' => '978',
        ]);

        $this->post('/api/payments/nestpay/success', $payload)
            ->assertRedirect('https://shop.example.test/order-success?payment=approved');
        $firstProcessedAt = $payment->fresh()->processed_at;

        $this->post('/api/payments/nestpay/success', $payload)
            ->assertRedirect('https://shop.example.test/order-success?payment=duplicate');

        $this->assertEquals($firstProcessedAt, $payment->fresh()->processed_at);
        $this->assertSame(1, Payment::query()->count());
    }

    private function signedPayload(array $payload): array
    {
        $payload['HASH'] = app(NestPayHashService::class)->generateRequestHash($payload, 'test-store-key');

        return $payload;
    }

    private function pendingPayment(): array
    {
        $order = Order::create([
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
        ]);

        $payment = $order->payments()->create([
            'provider' => Payment::PROVIDER_NESTPAY,
            'provider_order_id' => 'EB-'.$order->id.'-TEST',
            'idempotency_key' => 'nestpay:EB-'.$order->id.'-TEST',
            'amount' => 549.90,
            'currency' => 'EUR',
            'currency_code' => '978',
            'status' => Payment::STATUS_PENDING,
        ]);

        return [$order, $payment];
    }
}

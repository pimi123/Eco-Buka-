<?php

namespace Tests\Feature;

use App\Models\MetaConversionEvent;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\Orders\CreateOrderService;
use App\Services\Payments\NestPay\NestPayHashService;
use App\Services\Tracking\DeliverMetaEvent;
use App\Services\Tracking\MetaConversions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class MetaTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['meta.enabled' => true, 'meta.access_token' => 'test-only-token', 'meta.site_url' => 'https://shop.example.test', 'nestpay.store_key' => 'test-only-key']);
        Mail::fake();
        Http::preventStrayRequests();
    }

    private function payload(string $method = 'cash'): array
    {
        $product = Product::create(['name' => 'Test battery', 'slug' => 'battery-'.Str::random(8), 'price' => 249.90, 'old_price' => 399, 'active' => true, 'in_stock' => true]);
        return ['checkout_key' => (string) Str::uuid(), 'payment_method' => $method, 'customer_name' => 'Test Customer', 'customer_email' => 'TEST@example.test',
            'customer_phone' => '+383 44 111 222', 'country' => 'Kosove', 'municipality' => 'Prishtina', 'city' => 'Prishtina', 'delivery_address' => 'Test address',
            'policy_accepted' => true, 'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'tracking' => ['marketing' => true, 'consent_version' => MetaConversions::CONSENT_VERSION, 'fbp' => 'fb.1.1700000000000.12345']];
    }

    private function order(string $method = 'card', bool $consent = true): Order
    {
        $data = $this->payload($method);
        $data['tracking_context'] = ['marketing' => $consent, 'consent_version' => MetaConversions::CONSENT_VERSION, 'client_user_agent' => 'Test browser', 'client_ip_address' => '192.0.2.1'];
        return app(CreateOrderService::class)->create($data);
    }

    private function payment(Order $order): Payment
    {
        return $order->payments()->create(['provider' => 'nestpay', 'provider_order_id' => 'oid-'.$order->id, 'amount' => $order->total,
            'currency' => 'EUR', 'currency_code' => '978', 'status' => 'pending']);
    }

    private function approveCallback(Payment $payment, array $override = [])
    {
        $data = array_merge(['ReturnOid' => $payment->provider_order_id, 'Response' => 'Approved', 'ProcReturnCode' => '00', 'amount' => (string) $payment->amount, 'currency' => '978'], $override);
        $data['HASH'] = app(NestPayHashService::class)->generateRequestHash($data);
        return $this->post('/api/payments/nestpay/success', $data);
    }

    public function test_cash_submission_is_idempotent_and_never_a_purchase(): void
    {
        $data = $this->payload();
        $first = $this->postJson('/api/orders', $data)->assertCreated();
        $this->postJson('/api/orders', $data)->assertCreated()->assertJsonPath('id', $first->json('id'));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('meta_conversion_events', 1);
        $this->assertSame('OrderSubmitted', MetaConversionEvent::sole()->event_name);
        $data['customer_name'] = 'Changed';
        $this->postJson('/api/orders', $data)->assertUnprocessable()->assertJsonValidationErrors('checkout_key');
    }

    public function test_validation_failure_does_not_create_events(): void
    {
        $data = $this->payload();
        unset($data['customer_email']);
        $this->postJson('/api/orders', $data)->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('meta_conversion_events', 0);
    }

    public function test_signed_payment_creates_one_immutable_discounted_purchase(): void
    {
        $order = $this->order(); $payment = $this->payment($order);
        $this->approveCallback($payment)->assertRedirect();
        $this->approveCallback($payment)->assertRedirect();
        $this->assertSame('approved', $payment->fresh()->status);
        $event = MetaConversionEvent::where('event_name', 'Purchase')->sole();
        $this->assertSame('249.90', $event->value);
        $this->assertSame('EUR', $event->currency);
        $this->assertSame('purchase_'.$order->id, $event->event_id);
        $this->assertSame(hash('sha256', 'test@example.test'), $event->payload['user_data']['em'][0]);
        $this->assertSame(hash('sha256', '38344111222'), $event->payload['user_data']['ph'][0]);
        $this->assertSame('website', $event->payload['action_source']);
        $this->assertStringNotContainsString('user_data', DB::table('meta_conversion_events')->where('id', $event->id)->value('payload'));
        $this->assertStringNotContainsString('Test browser', DB::table('orders')->where('id', $order->id)->value('tracking_context'));
    }

    public function test_failed_invalid_or_sparse_callbacks_cannot_create_purchase(): void
    {
        $order = $this->order(); $payment = $this->payment($order);
        $this->post('/api/payments/nestpay/success', ['ReturnOid' => $payment->provider_order_id, 'Response' => 'Approved', 'ProcReturnCode' => '00', 'HASH' => 'forged'])->assertRedirect();
        $this->assertSame('pending', $payment->fresh()->status);
        $this->approveCallback($payment, ['Response' => 'Declined', 'ProcReturnCode' => '05'])->assertRedirect();
        $this->assertSame(0, MetaConversionEvent::where('event_name', 'Purchase')->count());
        $other = $this->order(); $otherPayment = $this->payment($other);
        $this->approveCallback($otherPayment, ['amount' => null])->assertRedirect();
        $this->assertSame('error', $otherPayment->fresh()->status);
        $this->assertSame(0, MetaConversionEvent::where('event_name', 'Purchase')->count());
    }

    public function test_delivery_outage_retries_without_altering_payment_or_event_time(): void
    {
        $order = $this->order(); $payment = $this->payment($order); $this->approveCallback($payment);
        $event = MetaConversionEvent::where('event_name', 'Purchase')->sole();
        $payload = $event->payload;
        Http::fake(['graph.facebook.com/*' => Http::sequence()->push([], 503)->push(['events_received' => 1])]);
        app(DeliverMetaEvent::class)->send($event->id);
        $this->assertSame('retry', $event->fresh()->status);
        $this->assertSame('approved', $payment->fresh()->status);
        $this->travel(3)->minutes();
        app(DeliverMetaEvent::class)->send($event->id);
        $this->assertSame('delivered', $event->fresh()->status);
        $this->assertSame($payload, $event->fresh()->payload);
        $sent = count(Http::recorded());
        app(DeliverMetaEvent::class)->send($event->id);
        $this->assertCount($sent, Http::recorded());
    }

    public function test_cash_collection_not_fulfillment_is_the_conversion_and_requires_admin(): void
    {
        $order = $this->order('cash');
        $order->update(['status' => 'completed']);
        $this->assertSame(0, MetaConversionEvent::where('event_name', 'Purchase')->count());
        $data = ['amount' => $order->total, 'currency' => 'EUR', 'reference' => 'TEST-RECEIPT', 'action_source' => 'other', 'received_at' => now()->toISOString(), 'funds_received' => true];
        $this->post('/admin/orders/'.$order->id.'/confirm-payment', $data)->assertRedirect('/login');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post('/admin/orders/'.$order->id.'/confirm-payment', $data)->assertRedirect();
        $this->post('/admin/orders/'.$order->id.'/confirm-payment', $data)->assertRedirect();
        $this->assertSame(1, $order->payments()->count());
        $event = MetaConversionEvent::where('event_name', 'Purchase')->sole();
        $this->assertSame('other', $event->payload['action_source']);
        $this->assertSame($admin->id, $order->payments()->sole()->confirmed_by);
        $this->assertArrayNotHasKey('event_source_url', $event->payload);
    }

    public function test_revocation_and_missing_consent_prevent_delivery(): void
    {
        $order = $this->order('card', false); $payment = $this->payment($order); $this->approveCallback($payment);
        $event = MetaConversionEvent::where('event_name', 'Purchase')->sole();
        $this->assertSame('ineligible', $event->status);
        $this->assertNull($event->payload);
        app(DeliverMetaEvent::class)->send($event->id); Http::assertNothingSent();
        $other = $this->order(); $otherPayment = $this->payment($other); $this->approveCallback($otherPayment);
        $this->postJson('/api/tracking/meta/revoke', ['orders' => [['id' => $other->id, 'token' => 'wrong']]])->assertOk();
        $this->assertTrue($other->fresh()->tracking_context['marketing']);
        $this->postJson('/api/tracking/meta/revoke', ['orders' => [['id' => $other->id, 'token' => $other->tracking_token]]])->assertOk();
        $otherEvent = MetaConversionEvent::where('order_id', $other->id)->where('event_name', 'Purchase')->sole();
        $this->assertSame('revoked', $otherEvent->status);
        app(DeliverMetaEvent::class)->send($otherEvent->id); Http::assertNothingSent();
    }

    public function test_expired_events_are_not_restamped_and_tokens_are_not_public(): void
    {
        $order = $this->order(); $payment = $this->payment($order); $this->approveCallback($payment);
        $event = MetaConversionEvent::where('event_name', 'Purchase')->sole();
        $event->update(['occurred_at' => now()->subDays(8)]);
        app(DeliverMetaEvent::class)->send($event->id);
        $this->assertSame('expired', $event->fresh()->status); Http::assertNothingSent();
        $response = $this->getJson('/api/tracking/meta/config')->assertOk();
        $this->assertStringNotContainsString('test-only-token', $response->getContent());
    }

    public function test_distinct_paid_orders_each_have_one_purchase_and_use_payment_time(): void
    {
        foreach ([1, 2] as $index) {
            $order = $this->order();
            $order->forceFill(['created_at' => now()->subDays(3)])->save();
            $payment = $this->payment($order);
            $this->approveCallback($payment);
            $event = MetaConversionEvent::where('order_id', $order->id)->where('event_name', 'Purchase')->sole();
            $this->assertSame($payment->fresh()->paid_at->timestamp, $event->payload['event_time']);
            $this->assertGreaterThan($order->created_at->timestamp, $event->payload['event_time']);
        }
        $this->assertSame(2, MetaConversionEvent::where('event_name', 'Purchase')->count());
    }

    public function test_amount_currency_mismatch_and_cancelled_orders_never_become_conversions(): void
    {
        foreach ([['amount' => '1.00'], ['currency' => '840']] as $override) {
            $order = $this->order(); $payment = $this->payment($order);
            $this->approveCallback($payment, $override);
            $this->assertSame('error', $payment->fresh()->status);
        }
        $cancelled = $this->order(); $cancelled->update(['status' => 'cancelled']);
        $this->approveCallback($this->payment($cancelled));
        $this->assertSame(0, MetaConversionEvent::where('event_name', 'Purchase')->count());
    }

    public function test_payment_token_is_required_and_retry_preserves_gateway_reference(): void
    {
        config(['nestpay.gateway_url' => 'https://bank.example.test/gateway', 'nestpay.client_id' => 'test-client']);
        $order = $this->order();
        $url = '/api/orders/'.$order->id.'/payments/nestpay';
        $this->postJson($url, ['token' => 'wrong'])->assertForbidden();
        $first = $this->postJson($url, ['token' => $order->tracking_token])->assertOk();
        $second = $this->postJson($url, ['token' => $order->tracking_token])->assertOk();
        $this->assertSame($first->json('parameters'), $second->json('parameters'));
        $this->assertSame(1, $order->payments()->count());
    }

    public function test_session_callback_uses_frozen_black_friday_prices_and_survives_stock_change(): void
    {
        config(['nestpay.gateway_url' => 'https://bank.example.test/gateway', 'nestpay.client_id' => 'test-client']);
        $data = $this->payload('card');
        $first = $this->postJson('/api/checkout/nestpay', $data)->assertOk();
        $this->postJson('/api/checkout/nestpay', $data)->assertOk()->assertJsonPath('parameters.oid', $first->json('parameters.oid'));
        $this->assertDatabaseCount('nestpay_checkout_sessions', 1);
        Product::whereKey($data['items'][0]['product_id'])->update(['price' => 399, 'in_stock' => false]);
        $payload = ['ReturnOid' => $first->json('parameters.oid'), 'Response' => 'Approved', 'ProcReturnCode' => '00', 'amount' => '249.90', 'currency' => '978'];
        $payload['HASH'] = app(NestPayHashService::class)->generateRequestHash($payload);
        $this->post('/api/payments/nestpay/success', $payload)->assertRedirect();
        $this->post('/api/payments/nestpay/success', $payload)->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame('249.90', Order::sole()->total);
        $this->assertSame('249.90', Order::sole()->items()->sole()->unit_price);
        $this->assertSame('249.90', MetaConversionEvent::where('event_name', 'Purchase')->sole()->value);
    }

    public function test_partial_cash_and_non_admin_confirmation_are_rejected(): void
    {
        $order = $this->order('cash');
        $data = ['amount' => 1, 'currency' => 'EUR', 'reference' => 'TEST', 'action_source' => 'other', 'received_at' => now()->toISOString(), 'funds_received' => true];
        $this->actingAs(User::factory()->create(['is_admin' => false]))->postJson('/admin/orders/'.$order->id.'/confirm-payment', $data)->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post('/admin/orders/'.$order->id.'/confirm-payment', $data)->assertRedirect();
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(0, MetaConversionEvent::where('event_name', 'Purchase')->count());
    }

    public function test_permanent_errors_do_not_loop_and_disabled_mode_does_not_backfill(): void
    {
        $order = $this->order(); $this->approveCallback($this->payment($order));
        $event = MetaConversionEvent::where('event_name', 'Purchase')->sole();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['code' => 190]], 400)]);
        app(DeliverMetaEvent::class)->send($event->id);
        $this->assertSame('failed', $event->fresh()->status);
        $this->assertSame('meta_http_400_code_190', $event->fresh()->last_error);
        config(['meta.enabled' => false]);
        $another = $this->order(); $this->approveCallback($this->payment($another));
        $disabled = MetaConversionEvent::where('order_id', $another->id)->where('event_name', 'Purchase')->sole();
        $this->assertSame('disabled', $disabled->status);
        $this->assertNull($disabled->payload);
    }

    public function test_recovery_dispatch_and_reconciliation_are_available_without_network(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        $order = $this->order(); $this->approveCallback($this->payment($order));
        $this->artisan('meta:dispatch')->assertSuccessful();
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendMetaEvent::class, 2);
        $this->artisan('meta:report')->expectsOutputToContain('fully paid orders: 1')->assertSuccessful();
        $this->artisan('meta:health')->assertSuccessful();
        MetaConversionEvent::where('event_name', 'Purchase')->update(['status' => 'failed']);
        $this->artisan('meta:health')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_idempotent_retry_honors_new_refusal_without_recreating_order(): void
    {
        $data = $this->payload();
        $this->postJson('/api/orders', $data)->assertCreated();
        $data['tracking'] = ['marketing' => false, 'consent_version' => MetaConversions::CONSENT_VERSION];
        $this->postJson('/api/orders', $data)->assertCreated();
        $this->assertDatabaseCount('orders', 1);
        $this->assertFalse(Order::sole()->tracking_context['marketing']);
        $this->assertSame('revoked', MetaConversionEvent::sole()->status);
        $this->assertNull(MetaConversionEvent::sole()->payload);
    }
}

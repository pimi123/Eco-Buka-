<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_place_order(): void
    {
        $product = $this->product();

        $this->postJson('/api/orders', [
            'customer_name' => 'Guest Customer',
            'customer_phone' => '+38344111222',
            'customer_email' => 'guest@example.com',
            'country' => 'Kosove',
            'municipality' => 'Prishtine',
            'delivery_address' => 'Main Street 12',
            'city' => 'Prishtine',
            'policy_accepted' => true,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertUnauthorized();
    }

    public function test_authenticated_customer_can_place_order_without_payment(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product = $this->product(['price' => 249.99]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Eco Customer',
            'customer_phone' => '+38344111222',
            'customer_email' => 'customer@example.com',
            'country' => 'Kosove',
            'municipality' => 'Prishtine',
            'delivery_address' => 'Main Street 12',
            'city' => 'Prishtina',
            'policy_accepted' => true,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', Order::STATUS_PENDING)
            ->assertJsonPath('total', '499.98');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_name' => 'Eco Customer',
            'status' => Order::STATUS_PENDING,
            'subtotal' => 499.98,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 249.99,
            'line_total' => 499.98,
        ]);
    }

    public function test_backend_uses_database_price_not_frontend_price(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = $this->product(['price' => 100]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Price Test',
            'customer_phone' => '+38344111222',
            'country' => 'Kosove',
            'municipality' => 'Prishtine',
            'delivery_address' => 'Main Street 12',
            'city' => 'Prishtina',
            'policy_accepted' => true,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1],
            ],
        ]);

        $response->assertCreated()->assertJsonPath('total', '300.00');
        $this->assertDatabaseHas('order_items', ['unit_price' => 100, 'line_total' => 300]);
    }

    public function test_validation_rejects_missing_name_and_invalid_quantity(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = $this->product();

        $this->postJson('/api/orders', [
            'customer_phone' => '+38344111222',
            'country' => 'Kosove',
            'municipality' => 'Prishtine',
            'delivery_address' => 'Main Street 12',
            'city' => 'Prishtina',
            'policy_accepted' => true,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 0],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_name', 'items.0.quantity']);
    }

    public function test_inactive_products_cannot_be_ordered(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = $this->product(['active' => false]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Inactive Test',
            'customer_phone' => '+38344111222',
            'country' => 'Kosove',
            'municipality' => 'Prishtine',
            'delivery_address' => 'Main Street 12',
            'city' => 'Prishtina',
            'policy_accepted' => true,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    private function product(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Power Stations',
            'slug' => 'power-stations',
            'active' => true,
            'sort_order' => 1,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Test Power Station',
            'slug' => 'test-power-station-'.uniqid(),
            'short_description' => 'Portable power station.',
            'description' => 'Portable power station.',
            'price' => 200,
            'active' => true,
            'featured' => false,
            'sort_order' => 1,
            ...$overrides,
        ]);
    }
}

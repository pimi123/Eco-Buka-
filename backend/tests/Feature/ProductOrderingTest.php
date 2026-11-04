<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_priority_applies_before_pagination_and_preserves_group_order(): void
    {
        $normal = Product::create(['name' => 'Regular', 'price' => 100, 'sort_order' => 1, 'active' => true]);
        $sale = Product::create(['name' => 'Sale', 'price' => 80, 'old_price' => 100, 'sort_order' => 8, 'active' => true]);
        $invalid = Product::create(['name' => 'Not a sale', 'price' => 100, 'old_price' => 90, 'sort_order' => 2, 'active' => true]);
        $this->getJson('/api/products?per_page=1')->assertOk()->assertJsonPath('data.0.id', $sale->id);
        $group = Collection::create(['name' => 'Offers', 'type' => 'featured', 'active' => true]);
        $group->products()->attach($normal, ['sort_order' => 1, 'active' => true]);
        $group->products()->attach($invalid, ['sort_order' => 2, 'active' => true]);
        $group->products()->attach($sale, ['sort_order' => 3, 'active' => true]);
        $this->getJson('/api/collections/'.$group->slug.'/products')->assertOk()
            ->assertJsonPath('0.id', $sale->id)->assertJsonPath('1.id', $normal->id)->assertJsonPath('2.id', $invalid->id);
    }

    public function test_new_products_append_without_manual_order_including_legacy_zero(): void
    {
        $this->withoutMiddleware();
        Product::create(['name' => 'Existing', 'sort_order' => 12]);
        foreach ([[], ['sort_order' => ''], ['sort_order' => 0]] as $index => $order) {
            $this->post('/admin/products', ['name' => 'New '.$index, ...$order])->assertRedirect();
            $this->assertSame(13 + $index, Product::where('name', 'New '.$index)->firstOrFail()->sort_order);
        }
        $this->assertSame(12, Product::where('name', 'Existing')->firstOrFail()->sort_order);
    }

    public function test_edit_preserves_order_and_new_group_membership_appends(): void
    {
        $this->withoutMiddleware();
        $category = Category::create(['name' => 'Category']);
        $collection = Collection::create(['name' => 'Collection', 'type' => 'featured']);
        $newCollection = Collection::create(['name' => 'New collection', 'type' => 'featured']);
        $existing = Product::create(['name' => 'Existing', 'sort_order' => 7]);
        $product = Product::create(['name' => 'Edited', 'sort_order' => 9]);
        $category->assignedProducts()->attach($product, ['sort_order' => 15, 'active' => false]);
        $collection->products()->attach($product, ['sort_order' => 20, 'active' => true]);
        $newCollection->products()->attach($existing, ['sort_order' => 30, 'active' => true]);

        $this->put('/admin/products/'.$product->id, [
            'name' => 'Edited', 'category_id' => $category->id,
            'collection_ids' => [$collection->id, $newCollection->id], 'sort_order' => '',
        ])->assertRedirect();

        $this->assertSame(9, $product->fresh()->sort_order);
        $this->assertDatabaseHas('category_product', ['product_id' => $product->id, 'category_id' => $category->id, 'sort_order' => 15, 'active' => false]);
        $this->assertDatabaseHas('collection_product', ['product_id' => $product->id, 'collection_id' => $collection->id, 'sort_order' => 20]);
        $this->assertDatabaseHas('collection_product', ['product_id' => $product->id, 'collection_id' => $newCollection->id, 'sort_order' => 31]);
    }
}

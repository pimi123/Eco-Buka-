<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_out_of_stock_products_are_last_in_every_sort_and_group(): void
    {
        $available = $this->product('Available', ['price' => 100, 'created_at' => now()->subDay()]);
        $cheap = $this->product('Unavailable cheap sale', ['price' => 1, 'old_price' => 200, 'in_stock' => false]);
        $expensive = $this->product('Unavailable expensive', ['price' => 1000, 'in_stock' => false]);
        $group = Collection::create(['name' => 'Stock group', 'type' => 'featured', 'active' => true]);
        foreach ([$cheap, $expensive, $available] as $i => $product) $group->products()->attach($product, ['sort_order' => $i + 1, 'active' => true]);
        foreach (['recommended', 'price_asc', 'price_desc', 'newest'] as $sort) {
            $this->getJson('/api/catalog?sort='.$sort)->assertOk()->assertJsonPath('data.0.id', $available->id);
            $this->getJson('/api/catalog?collection='.$group->slug.'&sort='.$sort)->assertOk()->assertJsonPath('data.0.id', $available->id);
        }
        $this->getJson('/api/products')->assertOk()->assertJsonPath('0.id', $available->id);
        $this->getJson('/api/collections/'.$group->slug.'/products')->assertOk()->assertJsonPath('0.id', $available->id);
    }

    private function product(string $name, array $extra = []): Product
    {
        return Product::create(['name' => $name, 'price' => 100, 'active' => true, 'in_stock' => true, ...$extra]);
    }

    public function test_combined_filters_and_explicit_price_sort(): void
    {
        $category = Category::create(['name' => 'Stations', 'active' => true]);
        $a = $this->product('DELTA offer', ['price' => 80, 'old_price' => 100, 'category_id' => $category->id]);
        $this->product('DELTA unavailable', ['price' => 60, 'old_price' => 100, 'category_id' => $category->id, 'in_stock' => false]);
        $this->product('RIVER regular', ['price' => 20, 'category_id' => $category->id]);
        $this->getJson('/api/catalog?category='.$category->slug.'&series[]=DELTA&sale=1&min=70&max=90&facets=1')
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('data.0.id', $a->id)->assertJsonPath('facets.categories.0.count', 3);
        $this->getJson('/api/catalog?sort=price_asc')->assertOk()->assertJsonPath('data.0.name', 'RIVER regular');
        $this->getJson('/api/catalog?series[]=invalid')->assertUnprocessable();
    }

    public function test_secondary_category_collection_scope_and_literal_search(): void
    {
        $category = Category::create(['name' => 'Extra', 'active' => true]);
        $group = Collection::create(['name' => 'Group', 'type' => 'featured', 'active' => true]);
        $p = $this->product('DELTA 100%');
        $this->product('Other');
        $category->assignedProducts()->attach($p, ['active' => true, 'sort_order' => 1]);
        $group->products()->attach($p, ['active' => true, 'sort_order' => 1]);
        $this->getJson('/api/catalog?collection='.$group->slug.'&facets=1')->assertOk()->assertJsonPath('total', 1)->assertJsonPath('facets.categories.0.count', 1);
        $this->getJson('/api/catalog?category='.$category->slug)->assertOk()->assertJsonPath('data.0.id', $p->id);
        $this->getJson('/api/catalog?q=%25')->assertOk()->assertJsonPath('total', 1);
    }

    public function test_bounded_queries_and_pagination(): void
    {
        for ($i = 0; $i < 30; $i++) $this->product('DELTA '.$i);
        DB::enableQueryLog();
        $this->getJson('/api/catalog?facets=1')->assertOk()->assertJsonCount(24, 'data')->assertJsonPath('total', 30)->assertJsonPath('last_page', 2);
        $this->assertLessThanOrEqual(8, count(DB::getQueryLog()), 'Catalogue must not issue a query per product or per facet option.');
        DB::disableQueryLog();
        $this->getJson('/api/catalog?page=2')->assertOk()->assertJsonCount(6, 'data');
    }
}

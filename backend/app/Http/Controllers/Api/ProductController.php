<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = trim((string) $request->query('category', ''));

        if ($categorySlug !== '') {
            return $this->productsForCategorySlug($categorySlug)->get();
        }

        return $this->activeProducts()->get();
    }

    public function featured()
    {
        return $this->activeProducts()->where('featured', true)->get();
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->query('query', ''));

        return $this->activeProducts()
            ->when($query, function ($builder) use ($query) {
                $builder->where(function ($inner) use ($query): void {
                    $inner->where('name', 'like', "%{$query}%")
                        ->orWhere('short_description', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            })
            ->get();
    }

    public function byCategory(string $slug)
    {
        return $this->productsForCategorySlug($slug)->get();
    }

    public function show(string $slug)
    {
        return $this->activeProducts()->where('slug', $slug)->firstOrFail();
    }

    private function activeProducts()
    {
        return Product::query()
            ->where('active', true)
            ->with('category:id,name,slug')
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    private function productsForCategorySlug(string $slug)
    {
        $category = Category::query()->where('active', true)->where('slug', $slug)->firstOrFail();

        return $this->activeProducts()->where('category_id', $category->id);
    }
}

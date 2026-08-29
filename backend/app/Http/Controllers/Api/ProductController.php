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
            return $this->productListResponse($request, $this->productsForCategorySlug($categorySlug));
        }

        return $this->productListResponse($request, $this->activeProducts());
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

    public function byCategory(Request $request, string $slug)
    {
        return $this->productListResponse($request, $this->productsForCategorySlug($slug));
    }

    public function show(string $slug)
    {
        return $this->activeProducts()->where('slug', $slug)->firstOrFail();
    }

    private function activeProducts()
    {
        return Product::query()
            ->where('active', true)
            ->with(['category:id,name,slug', 'categories:id,name,slug', 'collections:id,name,slug,type'])
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    private function productsForCategorySlug(string $slug)
    {
        $category = Category::query()->where('active', true)->where('slug', $slug)->firstOrFail();

        return $this->activeProducts()
            ->where(function ($query) use ($category): void {
                $query->where('category_id', $category->id)
                    ->orWhereHas('categories', fn ($inner) => $inner
                        ->where('categories.id', $category->id)
                        ->where('category_product.active', true));
            });
    }

    private function productListResponse(Request $request, $query)
    {
        if (!$request->has('per_page')) {
            return $query->get();
        }

        $perPage = min(max((int) $request->query('per_page', 24), 1), 60);

        return $query->paginate($perPage);
    }
}

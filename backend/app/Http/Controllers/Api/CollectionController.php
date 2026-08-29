<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index()
    {
        return Collection::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function show(string $slug)
    {
        return Collection::query()
            ->where('active', true)
            ->where('slug', $slug)
            ->with(['products' => fn ($query) => $query->where('products.active', true)->wherePivot('active', true)])
            ->firstOrFail();
    }

    public function products(Request $request, string $slug)
    {
        $collection = Collection::query()->where('active', true)->where('slug', $slug)->firstOrFail();

        $query = $collection->products()
            ->where('products.active', true)
            ->wherePivot('active', true)
            ->with(['category:id,name,slug', 'categories:id,name,slug', 'collections:id,name,slug,type']);

        if (!$request->has('per_page')) {
            return $query->get();
        }

        $perPage = min(max((int) $request->query('per_page', 24), 1), 60);

        return $query->paginate($perPage);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function show(string $slug)
    {
        return Category::query()
            ->where('active', true)
            ->where('slug', $slug)
            ->with(['products' => fn ($query) => $query->where('active', true)->orderBy('sort_order')])
            ->firstOrFail();
    }
}

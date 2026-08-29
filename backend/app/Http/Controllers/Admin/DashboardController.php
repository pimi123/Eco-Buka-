<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\HeroBanner;
use App\Models\Product;
use App\Models\PromoCard;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'totalProducts' => Product::count(),
            'activeProducts' => Product::where('active', true)->count(),
            'totalCategories' => Category::count(),
            'activeCategories' => Category::where('active', true)->count(),
            'totalCollections' => Collection::count(),
            'heroBanners' => HeroBanner::count(),
            'promoCards' => PromoCard::count(),
            'recentProducts' => Product::with('category')->latest()->limit(8)->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FeatureBanner;
use App\Models\HeroBanner;
use App\Models\NavigationCard;
use App\Models\PromoCard;
use App\Models\ShowcaseSection;

class HomeController extends Controller
{
    public function heroBanners()
    {
        return HeroBanner::query()->where('active', true)->orderBy('sort_order')->get();
    }

    public function promoCards(string $sectionKey = 'new_products')
    {
        return PromoCard::query()
            ->where('active', true)
            ->where('section_key', $sectionKey)
            ->orderBy('sort_order')
            ->get();
    }

    public function showcase(string $sectionKey)
    {
        $section = ShowcaseSection::query()
            ->where('section_key', $sectionKey)
            ->where('active', true)
            ->firstOrFail();

        $products = $section->products()
            ->where('products.active', true)
            ->wherePivot('active', true)
            ->with('category:id,name,slug')
            ->get();

        return [
            'section' => $section,
            'products' => $products,
        ];
    }

    public function navigationCards(string $sectionKey)
    {
        return NavigationCard::query()
            ->where('active', true)
            ->where('section_key', $sectionKey)
            ->orderBy('sort_order')
            ->get();
    }

    public function featureBanners(string $sectionKey)
    {
        return FeatureBanner::query()
            ->where('active', true)
            ->where('section_key', $sectionKey)
            ->orderBy('sort_order')
            ->get();
    }

    public function homepage()
    {
        $showcaseSections = ShowcaseSection::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ShowcaseSection $section) => [
                'section' => $section,
                'products' => $section->products()
                    ->where('products.active', true)
                    ->wherePivot('active', true)
                    ->with('category:id,name,slug')
                    ->get(),
                'navigation_cards' => NavigationCard::query()
                    ->where('active', true)
                    ->where('section_key', $section->section_key)
                    ->orderBy('sort_order')
                    ->get(),
                'feature_banners' => FeatureBanner::query()
                    ->where('active', true)
                    ->where('section_key', $section->section_key)
                    ->orderBy('sort_order')
                    ->get(),
            ]);

        return [
            'hero_banners' => HeroBanner::query()->where('active', true)->orderBy('sort_order')->get(),
            'categories' => Category::query()->where('active', true)->orderBy('sort_order')->get(),
            'new_products_promo_cards' => PromoCard::query()
                ->where('active', true)
                ->where('section_key', 'new_products')
                ->orderBy('sort_order')
                ->get(),
            'showcase_sections' => $showcaseSections,
            'feature_banners' => FeatureBanner::query()
                ->where('active', true)
                ->orderBy('sort_order')
                ->get(),
        ];
    }
}

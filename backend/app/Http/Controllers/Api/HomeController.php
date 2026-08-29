<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
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
        return $this->promoCardsForSection($sectionKey);
    }

    public function promoCardSection(string $sectionKey)
    {
        $section = ShowcaseSection::query()
            ->where('active', true)
            ->where('section_key', $sectionKey)
            ->first();

        return [
            'section' => $section,
            'cards' => $this->promoCardsForSection($sectionKey, $section),
        ];
    }

    private function promoCardsForSection(string $sectionKey, ?ShowcaseSection $section = null)
    {
        $section ??= ShowcaseSection::query()
            ->where('active', true)
            ->where('section_key', $sectionKey)
            ->first();

        return PromoCard::query()
            ->where('active', true)
            ->where(function ($query) use ($sectionKey, $section): void {
                $query->where('section_key', $sectionKey);

                if ($section) {
                    $query->orWhere('homepage_section_id', $section->id);
                }
            })
            ->orderBy('sort_order')
            ->get();
    }

    public function showcase(string $sectionKey)
    {
        $section = ShowcaseSection::query()
            ->where('section_key', $sectionKey)
            ->where('active', true)
            ->firstOrFail();

        $products = $this->productsForSection($section);

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
                'products' => $this->productsForSection($section),
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
            'new_products_promo_cards' => $this->promoCardsForSection('new_products'),
            'showcase_sections' => $showcaseSections,
            'feature_banners' => FeatureBanner::query()
                ->where('active', true)
                ->orderBy('sort_order')
                ->get(),
        ];
    }

    private function productsForSection(ShowcaseSection $section)
    {
        $limit = max(1, (int) ($section->display_limit ?: 4));

        if ($section->source_type === 'category') {
            $category = null;
            if ($section->source_id || $section->source_slug) {
                $category = Category::query()
                    ->where('active', true)
                    ->where(function ($query) use ($section): void {
                        $query->when($section->source_id, fn ($inner) => $inner->orWhere('id', $section->source_id))
                            ->when($section->source_slug, fn ($inner) => $inner->orWhere('slug', $section->source_slug));
                    })
                    ->first();
            }

            if ($category) {
                return \App\Models\Product::query()
                    ->where('active', true)
                    ->where(function ($query) use ($category): void {
                        $query->where('category_id', $category->id)
                            ->orWhereHas('categories', fn ($inner) => $inner
                                ->where('categories.id', $category->id)
                                ->where('category_product.active', true));
                    })
                    ->with(['category:id,name,slug', 'categories:id,name,slug', 'collections:id,name,slug,type'])
                    ->orderBy('sort_order')
                    ->limit($limit)
                    ->get();
            }
        }

        if ($section->source_type === 'collection') {
            $collection = null;
            if ($section->source_id || $section->source_slug) {
                $collection = Collection::query()
                    ->where('active', true)
                    ->where(function ($query) use ($section): void {
                        $query->when($section->source_id, fn ($inner) => $inner->orWhere('id', $section->source_id))
                            ->when($section->source_slug, fn ($inner) => $inner->orWhere('slug', $section->source_slug));
                    })
                    ->first();
            }

            if ($collection) {
                return $collection->products()
                    ->where('products.active', true)
                    ->wherePivot('active', true)
                    ->with(['category:id,name,slug', 'categories:id,name,slug', 'collections:id,name,slug,type'])
                    ->limit($limit)
                    ->get();
            }
        }

        return $section->products()
            ->where('products.active', true)
            ->wherePivot('active', true)
            ->with(['category:id,name,slug', 'categories:id,name,slug', 'collections:id,name,slug,type'])
            ->limit($limit)
            ->get();
    }
}

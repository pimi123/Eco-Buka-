<?php

use App\Models\Collection;
use App\Models\ShowcaseSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $popularCollection = Collection::query()
            ->where('slug', 'popular-eco-buka-solutions')
            ->orWhere('name', 'Popular Eco Buka Solutions')
            ->first();

        $sections = [
            'power_stations_featured' => [
                'title' => 'Power Stations',
                'subtitle' => 'Explore reliable portable power solutions for home, outdoor, and backup energy.',
                'section_type' => 'featured_category',
                'source_type' => 'category',
                'source_slug' => 'power-stations',
                'display_limit' => 8,
                'layout_variant' => 'featured_banner_grid',
                'button_text' => 'View All',
                'button_link' => '/category/power-stations',
                'sort_order' => 3,
            ],
            'solar_system_showcase' => [
                'title' => 'STREAM Solar Plant',
                'subtitle' => 'Featured plug-and-play solar products selected for realistic homepage testing.',
                'section_type' => 'mixed_showcase',
                'source_type' => 'manual_products',
                'source_slug' => 'new-products',
                'display_limit' => 4,
                'layout_variant' => 'product_showcase_with_cards',
                'sort_order' => 5,
            ],
            'popular_eco_buka' => [
                'title' => 'Popular Eco Buka solutions',
                'subtitle' => 'Selected Eco Buka products and solution bundles for everyday power needs.',
                'section_type' => 'product_grid',
                'source_type' => $popularCollection ? 'collection' : 'manual_products',
                'source_id' => $popularCollection?->id,
                'source_slug' => $popularCollection?->slug,
                'display_limit' => 8,
                'layout_variant' => 'standard_grid',
                'sort_order' => 6,
            ],
            'featured_video_promo' => [
                'title' => 'Featured Video Promo',
                'subtitle' => 'Wide promotional video or image banner shown before the footer.',
                'section_type' => 'video_banner',
                'source_type' => 'manual_cards',
                'display_limit' => 1,
                'layout_variant' => 'wide_video_banner',
                'sort_order' => 8,
            ],
        ];

        foreach ($sections as $sectionKey => $attributes) {
            ShowcaseSection::query()->updateOrCreate(
                ['section_key' => $sectionKey],
                [...$attributes, 'active' => true],
            );
        }
    }

    public function down(): void
    {
        // Keep user-managed homepage content on rollback.
    }
};

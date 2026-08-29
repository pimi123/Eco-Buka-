<?php

use App\Models\PromoCard;
use App\Models\ShowcaseSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $sections = [
            'new_products' => [
                'title' => 'New Products',
                'subtitle' => 'Fresh energy launches, seasonal offers, and smart power picks for Eco Buka customers.',
                'section_type' => 'promo_cards',
                'source_type' => 'manual_cards',
                'display_limit' => 6,
                'sort_order' => 2,
            ],
            'promotional_category_cards' => [
                'title' => 'Promotional Category Cards',
                'subtitle' => 'Visual campaign cards that guide customers into offers, categories, and solutions.',
                'section_type' => 'promo_cards',
                'source_type' => 'manual_cards',
                'display_limit' => 2,
                'sort_order' => 7,
            ],
        ];

        foreach ($sections as $sectionKey => $attributes) {
            $section = ShowcaseSection::query()->updateOrCreate(
                ['section_key' => $sectionKey],
                [...$attributes, 'active' => true],
            );

            PromoCard::query()
                ->where('section_key', $sectionKey)
                ->whereNull('homepage_section_id')
                ->update(['homepage_section_id' => $section->id]);
        }
    }

    public function down(): void
    {
        // Keep CMS content in place when rolling back; the schema migration removes the relation.
    }
};

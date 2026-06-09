<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\FeatureBanner;
use App\Models\HeroBanner;
use App\Models\NavigationCard;
use App\Models\Product;
use App\Models\PromoCard;
use App\Models\ShowcaseSection;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@ecobuka.test'],
            [
                'name' => 'Eco Buka Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );

        $categories = collect([
            ['name' => 'Power Stations', 'slug' => 'power-stations'],
            ['name' => 'Solar Panels', 'slug' => 'solar-panels'],
            ['name' => 'Solar Generators', 'slug' => 'solar-generators'],
            ['name' => 'Smart Devices', 'slug' => 'smart-devices'],
            ['name' => 'Accessories', 'slug' => 'accessories'],
        ])->map(fn (array $category, int $index) => Category::query()->updateOrCreate(
            ['slug' => $category['slug']],
            [
                ...$category,
                'description' => 'Eco Buka '.$category['name'].' category.',
                'active' => true,
                'sort_order' => $index + 1,
            ],
        ));

        $products = collect([
            ['name' => 'DELTA 3 Max Series', 'slug' => 'delta-3-max-series', 'category' => 'power-stations', 'price' => 849, 'old_price' => 999, 'badge' => 'New Release'],
            ['name' => 'Eco Buka Station 1200', 'slug' => 'eco-buka-station-1200', 'category' => 'power-stations', 'price' => 1190, 'old_price' => 1390, 'badge' => 'Best Seller'],
            ['name' => 'Eco Buka River Mini', 'slug' => 'eco-buka-river-mini', 'category' => 'power-stations', 'price' => 249, 'old_price' => 299, 'badge' => 'New'],
            ['name' => 'Solar Panel 220W', 'slug' => 'solar-panel-220w', 'category' => 'solar-panels', 'price' => 319, 'old_price' => null, 'badge' => 'New'],
            ['name' => 'Solar Generator Home Kit', 'slug' => 'solar-generator-home-kit', 'category' => 'solar-generators', 'price' => 1490, 'old_price' => 1690, 'badge' => 'Sale'],
            ['name' => 'Smart Home Battery Monitor', 'slug' => 'smart-home-battery-monitor', 'category' => 'smart-devices', 'price' => 129, 'old_price' => null, 'badge' => 'New'],
            ['name' => 'Rapid Power Bank Pro', 'slug' => 'rapid-power-bank-pro', 'category' => 'accessories', 'price' => 99, 'old_price' => 129, 'badge' => 'New'],
            ['name' => 'Portable Solar Cable Kit', 'slug' => 'portable-solar-cable-kit', 'category' => 'accessories', 'price' => 49, 'old_price' => null, 'badge' => null],
        ])->map(function (array $product, int $index) use ($categories) {
            $category = $categories->firstWhere('slug', $product['category']);

            return Product::query()->updateOrCreate(
                ['slug' => $product['slug']],
                [
                    'category_id' => $category?->id,
                    'name' => $product['name'],
                    'short_description' => 'Premium Eco Buka energy solution for modern power needs.',
                    'description' => 'A presentation-ready product entry for the Eco Buka Laravel CMS.',
                    'price' => $product['price'],
                    'old_price' => $product['old_price'],
                    'badge' => $product['badge'],
                    'specs' => ['Capacity' => '2kWh', 'Output' => '3000W'],
                    'featured' => $index < 4,
                    'active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        });

        foreach ([
            ['New Release', 'DELTA 3 Max Series Available Now!', '2kWh Capacity | 3000W Max Output'],
            ['Smart Solar Solutions', 'Power Your Home with Clean Energy', 'Reliable backup, solar charging, and energy independence.'],
            ['Portable Energy', 'Power Anywhere You Go', 'Compact, powerful, and ready for home, travel, and outdoor use.'],
        ] as $index => [$eyebrow, $title, $subtitle]) {
            HeroBanner::query()->updateOrCreate(
                ['title' => $title],
                [
                    'eyebrow' => $eyebrow,
                    'subtitle' => $subtitle,
                    'button_text' => 'Learn More',
                    'button_link' => '/products',
                    'second_button_text' => 'Shop Now',
                    'second_button_link' => '/products',
                    'text_color' => 'light',
                    'text_alignment' => 'left',
                    'active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        PromoCard::query()
            ->where('section_key', 'new_products')
            ->where('title', 'like', 'New Product Promo %')
            ->update(['active' => false]);

        foreach ([
            [
                'label' => 'New',
                'title' => 'Summer Sale',
                'subtitle' => 'Earn up to 2,500 EcoCredits, plus a chance to win DELTA 3 Classic',
                'button_text' => 'Shop Now',
                'button_link' => '/products',
                'background_image' => '/promo/summer-sale.png',
                'text_color' => 'dark',
            ],
            [
                'label' => 'New | 1024Wh | 3600W Surge',
                'title' => 'DELTA 3 Classic',
                'subtitle' => 'Eco Buka\'s best-value 1 kWh portable power station',
                'button_text' => 'Learn More',
                'button_link' => '/products/delta-3-max-series',
                'background_image' => '/promo/delta-classic.png',
                'text_color' => 'light',
            ],
            [
                'label' => 'New',
                'title' => 'Eco Buka Power Bank',
                'subtitle' => 'Power your life, anywhere',
                'button_text' => 'Buy Now',
                'button_link' => '/products/rapid-power-bank-pro',
                'background_image' => '/promo/power-bank.png',
                'text_color' => 'light',
            ],
            [
                'label' => 'New Release',
                'title' => 'DELTA 3 Max Series',
                'subtitle' => '2kWh Capacity | 3000W Max Output',
                'button_text' => 'Learn More',
                'button_link' => '/products/delta-3-max-series',
                'background_image' => '/promo/delta-max-series.png',
                'text_color' => 'light',
            ],
        ] as $index => $card) {
            PromoCard::query()->updateOrCreate(
                ['section_key' => 'new_products', 'title' => $card['title']],
                [
                    ...$card,
                    'active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        foreach ([
            [
                'title' => 'Promotional Offers',
                'subtitle' => 'Shop a great selection of deals, sale & clearance items.',
                'button_text' => 'Buy Now',
                'button_link' => '/categories/accessories',
                'category_slug' => 'accessories',
                'background_image' => '/promo/delta-classic.png',
            ],
            [
                'title' => 'Home Battery',
                'subtitle' => 'One Powers All',
                'button_text' => 'Contact Us',
                'button_link' => '/categories/solar-generators',
                'category_slug' => 'solar-generators',
                'background_image' => '/promo/delta-max-series.png',
            ],
        ] as $index => $card) {
            PromoCard::query()->updateOrCreate(
                ['section_key' => 'promotional_category_cards', 'title' => $card['title']],
                [
                    ...$card,
                    'text_color' => 'light',
                    'active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        $showcase = ShowcaseSection::query()->updateOrCreate(
            ['section_key' => 'solar_system_showcase'],
            [
                'title' => 'New Products',
                'subtitle' => 'Fresh energy solutions selected for homes, businesses, and outdoor power.',
                'active' => true,
                'sort_order' => 1,
            ],
        );

        $sync = [];
        foreach ($products->take(4)->values() as $index => $product) {
            $sync[$product->id] = ['sort_order' => $index + 1, 'active' => true];
        }
        $showcase->products()->sync($sync);

        NavigationCard::query()->updateOrCreate(
            ['section_key' => 'solar_system_showcase', 'title' => 'View All Accessories'],
            ['link' => '/categories/accessories', 'active' => true, 'sort_order' => 1],
        );
        NavigationCard::query()->updateOrCreate(
            ['section_key' => 'solar_system_showcase', 'title' => 'Explore Solar Kits'],
            ['link' => '/categories/solar-panels', 'active' => true, 'sort_order' => 2],
        );

        FeatureBanner::query()->updateOrCreate(
            ['section_key' => 'solar_system_showcase', 'title' => 'Eco Buka DELTA Pro Ultra Power Station'],
            [
                'section_heading' => 'Eco Buka DELTA Pro Ultra Power Station',
                'eyebrow' => 'New',
                'subtitle' => '6kWh-30kWh Capacity | 6900W Output',
                'button_text' => 'Buy Now',
                'button_link' => '/products/delta-3-max-series',
                'text_color' => 'light',
                'text_alignment' => 'left',
                'active' => true,
                'sort_order' => 1,
            ],
        );

        FeatureBanner::query()->updateOrCreate(
            ['section_key' => 'featured_video_promo', 'title' => 'Eco Buka STREAM Series Plug & Play Solar Plant'],
            [
                'section_heading' => 'Eco Buka STREAM Series Plug & Play Solar Plant',
                'eyebrow' => 'FLASH SALE & Up to 43% OFF!',
                'subtitle' => 'Run high-wattage appliances with solar-ready portable energy.',
                'price_text' => 'Starting at €1199',
                'button_text' => 'Buy Now',
                'button_link' => '/products',
                'background_image' => '/promo/delta-classic.png',
                'text_color' => 'light',
                'text_alignment' => 'left',
                'active' => true,
                'sort_order' => 1,
            ],
        );
    }
}

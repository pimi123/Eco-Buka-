<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
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

        $categoryData = [
            ['name' => 'STREAM Series Solar Plants', 'slug' => 'stream-series', 'description' => 'Plug-and-play solar plants for balcony, rooftop, and smart home energy savings.', 'image' => '/promo/delta-max-series.png'],
            ['name' => 'Power Stations', 'slug' => 'power-stations', 'description' => 'Portable power stations for backup power, work sites, travel, and outdoor use.', 'image' => '/promo/delta-classic.png'],
            ['name' => 'Solar Panels', 'slug' => 'solar-panels', 'description' => 'Rigid, portable, and folding solar panels for charging stations and solar kits.', 'image' => '/promo/summer-sale.png'],
            ['name' => 'Solar Generators', 'slug' => 'solar-generators', 'description' => 'Bundled power station and solar panel kits for homes, cabins, and mobile work.', 'image' => '/promo/summer-sale.png'],
            ['name' => 'Smart Devices', 'slug' => 'smart-devices', 'description' => 'Smart batteries, monitors, chargers, and connected energy accessories.', 'image' => '/promo/power-bank.png'],
            ['name' => 'Home Battery', 'slug' => 'home-battery', 'description' => 'Scalable home backup batteries and inverter-ready energy storage systems.', 'image' => '/promo/delta-max-series.png'],
            ['name' => 'Power Banks', 'slug' => 'power-banks', 'description' => 'Compact power banks and USB-C charging solutions for phones, laptops, and travel.', 'image' => '/promo/power-bank.png'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Cables, chargers, extra batteries, cases, mounts, and installation essentials.', 'image' => '/promo/delta-classic.png'],
            ['name' => 'Solutions', 'slug' => 'solutions', 'description' => 'Curated energy solutions for home backup, camping, business, and solar independence.', 'image' => '/promo/delta-max-series.png'],
            ['name' => 'Refurbished', 'slug' => 'refurbished', 'description' => 'Checked and refreshed Eco Buka products for value-focused buyers.', 'image' => '/promo/summer-sale.png'],
        ];

        Category::query()
            ->whereNotIn('slug', collect($categoryData)->pluck('slug'))
            ->update(['active' => false]);

        $categories = collect($categoryData)->map(fn (array $category, int $index) => Category::query()->updateOrCreate(
            ['slug' => $category['slug']],
            [
                ...$category,
                'active' => true,
                'sort_order' => $index + 1,
            ],
        ));

        $productsData = [
            ['STREAM Ultra X', 'stream-ultra-x', 'stream-series', 1427, 1799, 'New', '4 x MPPT, 3.84kWh storage, and 2300W max AC output for high-demand home loads.', ['Capacity' => '3.84kWh', 'Output' => '2300W', 'Solar input' => '4 MPPT'], '/promo/delta-max-series.png', true],
            ['STREAM Ultra', 'stream-ultra', 'stream-series', 713, 1299, 'New', 'Expandable plug-and-play solar storage for apartments and compact homes.', ['Capacity' => '1.92-11.52kWh', 'MPPT' => 'Up to 4', 'Install' => 'DIY'], '/promo/delta-max-series.png', true],
            ['STREAM AC Pro', 'stream-ac-pro', 'stream-series', 799, 999, 'New', 'Microinverter-compatible AC storage for smarter solar self-consumption.', ['Compatibility' => 'Microinverters', 'Mode' => 'AC coupled', 'App' => 'Smart control'], '/promo/delta-max-series.png', true],
            ['All-in-One Home Solar Kit', 'all-in-one-home-solar-kit', 'stream-series', 1427, 1799, 'Solar Kit', 'A complete solar and storage bundle for daily energy coverage.', ['Capacity' => 'Expandable', 'Use' => 'Home', 'Solar' => 'Ready'], '/promo/summer-sale.png', true],

            ['DELTA 3 Classic 1024Wh', 'delta-3-classic-1024wh', 'power-stations', 549, 699, 'New', 'Best-value 1kWh portable station with fast charging for home and travel.', ['Capacity' => '1024Wh', 'Output' => '1800W', 'Surge' => '3600W'], '/promo/delta-classic.png', true],
            ['DELTA 3 Max Series', 'delta-3-max-series', 'power-stations', 849, 999, 'New Release', '2kWh class station with 3000W output for backup, camping, and work tools.', ['Capacity' => '2kWh', 'Output' => '3000W', 'Battery' => 'LFP'], '/promo/delta-max-series.png', true],
            ['DELTA Pro 3', 'delta-pro-3', 'power-stations', 3299, 3998, 'Pro', 'High-output backup power for demanding appliances and family energy needs.', ['Capacity' => '4kWh', 'Output' => '4000W', 'Expandable' => '12kWh'], '/promo/delta-max-series.png', true],
            ['DELTA 2 Max', 'delta-2-max', 'power-stations', 999, 1799, 'Best Seller', 'Expandable LFP power station for home backup, caravans, and outdoor work.', ['Capacity' => '2-6kWh', 'Output' => '2400W', 'Noise' => '30dB'], '/promo/delta-classic.png', true],
            ['RIVER 3 Plus', 'river-3-plus', 'power-stations', 299, 399, 'Compact', 'Lightweight backup power with fast charging for small devices and weekends away.', ['Capacity' => '286-858Wh', 'Output' => '600W', 'Solar' => '220W'], '/promo/delta-classic.png', false],
            ['RIVER 3 Series', 'river-3-series', 'power-stations', 249, 299, 'Portable', 'Ultra-compact power station for laptops, lights, routers, and short trips.', ['Capacity' => '245Wh', 'Output' => '300W', 'UPS' => 'Under 20ms'], '/promo/delta-classic.png', false],

            ['220W Bifacial Portable Solar Panel', '220w-bifacial-portable-solar-panel', 'solar-panels', 319, 399, 'New', 'Portable bifacial panel for camping, backup kits, and power stations.', ['Power' => '220W', 'Type' => 'Bifacial', 'Use' => 'Portable'], '/promo/summer-sale.png', true],
            ['400W Portable Solar Panel', '400w-portable-solar-panel', 'solar-panels', 599, 699, 'Popular', 'High-output folding panel for faster charging in the field.', ['Power' => '400W', 'Foldable' => 'Yes', 'Connector' => 'MC4'], '/promo/summer-sale.png', false],
            ['100W Rigid Solar Panel', '100w-rigid-solar-panel', 'solar-panels', 119, 149, null, 'Slim rigid panel for vans, sheds, and small permanent solar setups.', ['Power' => '100W', 'Type' => 'Rigid', 'Rating' => 'IP68'], '/promo/summer-sale.png', false],
            ['130W RVMax Rigid Solar Panel', '130w-rvmax-rigid-solar-panel', 'solar-panels', 359, null, 'IP68', 'Rigid panel set built for RV roofs and long-term outdoor use.', ['Power' => '130W x 2', 'Rate' => '25%', 'Rating' => 'IP68'], '/promo/summer-sale.png', false],

            ['Solar Generator Home Kit', 'solar-generator-home-kit', 'solar-generators', 1490, 1690, 'Bundle', 'Power station and panel bundle for clean home backup energy.', ['Station' => '2kWh', 'Panel' => '220W', 'Use' => 'Home'], '/promo/summer-sale.png', true],
            ['Balcony Solar Starter Kit', 'balcony-solar-starter-kit', 'solar-generators', 499, 649, 'Starter', 'Easy balcony solar kit for apartments and small homes.', ['Install' => 'Plug and play', 'Use' => 'Balcony', 'Solar' => 'Included'], '/promo/delta-max-series.png', false],
            ['Camping Solar Generator Kit', 'camping-solar-generator-kit', 'solar-generators', 799, 999, 'Outdoor', 'Portable station and foldable panel kit for camping and events.', ['Use' => 'Camping', 'Panel' => 'Portable', 'Runtime' => 'Weekend'], '/promo/delta-classic.png', false],
            ['Business Backup Solar Kit', 'business-backup-solar-kit', 'solar-generators', 2490, 2890, 'Business', 'Scalable solar backup package for shops, offices, and field teams.', ['Use' => 'Business', 'Backup' => 'Yes', 'Solar' => 'Expandable'], '/promo/delta-max-series.png', false],

            ['Smart Home Battery Monitor', 'smart-home-battery-monitor', 'smart-devices', 129, null, 'New', 'Real-time monitoring for home energy use, charging, and backup status.', ['Connectivity' => 'Wi-Fi', 'App' => 'Yes', 'Use' => 'Monitoring'], '/promo/power-bank.png', false],
            ['800W Alternator Charger', '800w-alternator-charger', 'smart-devices', 249, 299, 'Travel', 'Charge portable stations while driving and stay powered on the road.', ['Power' => '800W', 'Use' => 'Vehicle', 'Mode' => 'Fast charge'], '/promo/power-bank.png', false],
            ['Smart Generator Adapter', 'smart-generator-adapter', 'smart-devices', 89, 119, null, 'Adapter for hybrid backup setups and smarter energy switching.', ['Use' => 'Backup', 'Type' => 'Adapter', 'Install' => 'Easy'], '/promo/power-bank.png', false],
            ['Eco Buka Smart Plug', 'eco-buka-smart-plug', 'smart-devices', 39, 49, 'Smart', 'Control appliances, schedules, and energy use from the app.', ['Control' => 'App', 'Use' => 'Appliance', 'Mode' => 'Schedule'], '/promo/power-bank.png', false],

            ['OCEAN 2 Home Battery', 'ocean-2-home-battery', 'home-battery', 5990, null, 'Home', 'Three-phase home energy storage concept for larger backup scenarios.', ['Capacity' => 'Up to 60kWh', 'Output' => '12kW', 'Phase' => 'Three-phase'], '/promo/delta-max-series.png', true],
            ['Eco Buka Home Backup Solution', 'eco-buka-home-backup-solution', 'home-battery', 1490, 1790, 'Solution', 'A complete package for backup power, solar charging, and daily resilience.', ['Use' => 'Home', 'Backup' => 'Yes', 'Solar' => 'Ready'], '/promo/delta-max-series.png', false],
            ['Stackable Extra Battery 2kWh', 'stackable-extra-battery-2kwh', 'home-battery', 799, 999, 'Expandable', 'Add-on battery module for longer runtime and larger backup coverage.', ['Capacity' => '2kWh', 'Type' => 'Extra battery', 'Battery' => 'LFP'], '/promo/delta-classic.png', false],

            ['RAPID Pro 20k Power Bank', 'rapid-pro-20k-power-bank', 'power-banks', 99, 129, 'New', 'Pocket-friendly high-speed charging for phones, tablets, and laptops.', ['Capacity' => '20,000mAh', 'USB-C' => 'Yes', 'Output' => 'High speed'], '/promo/power-bank.png', true],
            ['RAPID Magnetic Power Bank', 'rapid-magnetic-power-bank', 'power-banks', 69, 89, 'Travel', 'Compact magnetic battery for daily carry and quick phone top-ups.', ['Capacity' => '10,000mAh', 'Wireless' => 'Magnetic', 'Use' => 'Daily'], '/promo/power-bank.png', false],

            ['Portable Solar Cable Kit', 'portable-solar-cable-kit', 'accessories', 49, null, null, 'Cable set for connecting portable panels to compatible stations.', ['Length' => '3m', 'Connector' => 'MC4', 'Use' => 'Solar'], '/promo/summer-sale.png', false],
            ['Power Station Carry Case', 'power-station-carry-case', 'accessories', 79, 99, 'Accessory', 'Protective carry case for travel, storage, and worksite use.', ['Use' => 'Travel', 'Protection' => 'Padded', 'Fit' => 'Portable'], '/promo/delta-classic.png', false],
            ['Extra Battery Cable', 'extra-battery-cable', 'accessories', 59, null, null, 'Heavy-duty cable for connecting extra batteries and expansion modules.', ['Use' => 'Expansion', 'Connector' => 'Battery', 'Build' => 'Heavy duty'], '/promo/delta-classic.png', false],
            ['Solar Panel Mounting Kit', 'solar-panel-mounting-kit', 'accessories', 89, 119, 'Install', 'Mounting hardware for rigid panels, vans, sheds, and small roofs.', ['Use' => 'Mounting', 'Panel' => 'Rigid', 'Install' => 'Outdoor'], '/promo/summer-sale.png', false],

            ['Apartment Solar Solution', 'apartment-solar-solution', 'solutions', 1199, 1499, 'Apartment', 'Curated balcony solar and battery setup for apartment energy savings.', ['Use' => 'Apartment', 'Install' => 'Plug and play', 'Solar' => 'Included'], '/promo/delta-max-series.png', false],
            ['Outdoor Event Power Solution', 'outdoor-event-power-solution', 'solutions', 899, 1099, 'Events', 'Portable power package for events, pop-ups, and field teams.', ['Use' => 'Events', 'Runtime' => 'All day', 'Output' => 'High'], '/promo/delta-classic.png', false],
            ['Refurbished DELTA 2 Max', 'refurbished-delta-2-max', 'refurbished', 799, 999, 'Refurbished', 'Checked and refreshed portable station for value-focused buyers.', ['Capacity' => '2kWh', 'Output' => '2400W', 'Warranty' => 'Limited'], '/promo/delta-classic.png', false],
        ];

        Product::query()
            ->whereNotIn('slug', collect($productsData)->pluck(1))
            ->update(['active' => false, 'featured' => false]);

        $products = collect($productsData)->map(function (array $product, int $index) use ($categories) {
            [$name, $slug, $categorySlug, $price, $oldPrice, $badge, $shortDescription, $specs, $image, $featured] = $product;
            $category = $categories->firstWhere('slug', $categorySlug);

            $productModel = Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category?->id,
                    'name' => $name,
                    'short_description' => $shortDescription,
                    'description' => $shortDescription.' Eco Buka can prepare this product with local delivery, consultation, and after-sales support for Albania and regional projects.',
                    'price' => $price,
                    'old_price' => $oldPrice,
                    'badge' => $badge,
                    'main_image' => $image,
                    'gallery_images' => array_values(array_unique([$image, '/promo/delta-classic.png', '/promo/summer-sale.png'])),
                    'specs' => $specs,
                    'featured' => $featured,
                    'active' => true,
                    'sort_order' => $index + 1,
                ],
            );

            if ($category) {
                $productModel->categories()->syncWithoutDetaching([
                    $category->id => ['sort_order' => 1, 'active' => true],
                ]);
            }

            return $productModel;
        });

        $collectionData = [
            ['name' => 'New Products', 'slug' => 'new-products', 'type' => 'merchandising', 'description' => 'Fresh launches and recently promoted products.'],
            ['name' => 'Popular Eco Buka Solutions', 'slug' => 'popular-eco-buka-solutions', 'type' => 'featured', 'description' => 'Curated products that should be promoted as popular Eco Buka solutions.'],
            ['name' => 'Summer Sale', 'slug' => 'summer-sale', 'type' => 'campaign', 'description' => 'Seasonal campaign products, bundles, and offers.'],
            ['name' => 'Home Backup', 'slug' => 'home-backup', 'type' => 'solution', 'description' => 'Products useful for home backup during outages and daily resilience.'],
            ['name' => 'Solar Solutions', 'slug' => 'solar-solutions', 'type' => 'solution', 'description' => 'Solar panels, solar generators, and clean charging products.'],
            ['name' => 'Outdoor Power', 'slug' => 'outdoor-power', 'type' => 'solution', 'description' => 'Portable products for camping, travel, events, and field work.'],
            ['name' => 'Business Solutions', 'slug' => 'business-solutions', 'type' => 'solution', 'description' => 'Backup and mobile energy solutions for shops, offices, and teams.'],
        ];

        $collections = collect($collectionData)->map(fn (array $collection, int $index) => Collection::query()->updateOrCreate(
            ['slug' => $collection['slug']],
            [
                ...$collection,
                'active' => true,
                'sort_order' => $index + 1,
            ],
        ));

        $collectionProductSlugs = [
            'new-products' => ['stream-ultra-x', 'stream-ultra', 'delta-3-classic-1024wh', 'delta-3-max-series', 'rapid-pro-20k-power-bank'],
            'popular-eco-buka-solutions' => $products->where('featured', true)->pluck('slug')->all(),
            'summer-sale' => ['delta-3-classic-1024wh', 'delta-3-max-series', '220w-bifacial-portable-solar-panel', 'solar-generator-home-kit', 'balcony-solar-starter-kit'],
            'home-backup' => ['delta-pro-3', 'delta-2-max', 'ocean-2-home-battery', 'eco-buka-home-backup-solution', 'business-backup-solar-kit'],
            'solar-solutions' => ['220w-bifacial-portable-solar-panel', '400w-portable-solar-panel', 'solar-generator-home-kit', 'balcony-solar-starter-kit', 'all-in-one-home-solar-kit'],
            'outdoor-power' => ['river-3-plus', 'river-3-series', 'camping-solar-generator-kit', 'rapid-pro-20k-power-bank', 'portable-solar-cable-kit'],
            'business-solutions' => ['delta-pro-3', 'business-backup-solar-kit', '800w-alternator-charger', 'smart-home-battery-monitor'],
        ];

        foreach ($collectionProductSlugs as $collectionSlug => $productSlugs) {
            $collection = $collections->firstWhere('slug', $collectionSlug);
            if (! $collection) {
                continue;
            }

            $sync = [];
            foreach (array_values($productSlugs) as $index => $slug) {
                $product = $products->firstWhere('slug', $slug);
                if ($product) {
                    $sync[$product->id] = ['sort_order' => $index + 1, 'active' => true];
                }
            }
            $collection->products()->sync($sync);
        }

        HeroBanner::query()->update(['active' => false]);

        foreach ([
            ['New Release', 'DELTA 3 Max Series Available Now!', '2kWh capacity with 3000W max output for serious backup power.', '/products/delta-3-max-series', '/promo/delta-max-series.png', 'light'],
            ['Smart Solar Solutions', 'Power Your Home with Clean Energy', 'Reliable backup, solar charging, and energy independence for daily life.', '/category/home-battery', '/promo/summer-sale.png', 'light'],
            ['Portable Energy', 'Power Anywhere You Go', 'Compact power stations and solar kits for travel, work, and outdoor use.', '/category/power-stations', '/promo/delta-classic.png', 'light'],
            ['Summer Sale', 'Solar Kits Ready for the Season', 'Portable solar, balcony kits, and bundles selected for fast deployment.', '/category/solar-generators', '/promo/summer-sale.png', 'dark'],
        ] as $index => [$eyebrow, $title, $subtitle, $link, $image, $textColor]) {
            HeroBanner::query()->updateOrCreate(
                ['title' => $title],
                [
                    'eyebrow' => $eyebrow,
                    'subtitle' => $subtitle,
                    'button_text' => 'Learn More',
                    'button_link' => $link,
                    'second_button_text' => 'View Products',
                    'second_button_link' => '/products',
                    'background_image' => $image,
                    'mobile_background_image' => $image,
                    'text_color' => $textColor,
                    'text_alignment' => 'left',
                    'active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        $newProductsSection = ShowcaseSection::query()->updateOrCreate(
            ['section_key' => 'new_products'],
            [
                'title' => 'New Products',
                'subtitle' => 'Fresh energy launches, seasonal offers, and smart power picks for Eco Buka customers.',
                'section_type' => 'promo_cards',
                'source_type' => 'manual_cards',
                'layout_variant' => 'carousel',
                'display_limit' => 6,
                'active' => true,
                'sort_order' => 2,
            ],
        );

        $promotionalCardsSection = ShowcaseSection::query()->updateOrCreate(
            ['section_key' => 'promotional_category_cards'],
            [
                'title' => 'Promotional Category Cards',
                'subtitle' => 'Visual campaign cards that guide customers into offers, categories, and solutions.',
                'section_type' => 'promo_cards',
                'source_type' => 'manual_cards',
                'layout_variant' => 'two_cards',
                'display_limit' => 2,
                'active' => true,
                'sort_order' => 7,
            ],
        );

        PromoCard::query()->update(['active' => false]);

        foreach ([
            ['New', 'Summer Sale', 'Seasonal bundles for camping, backup, and solar savings.', 'Shop Now', '/products', null, '/promo/summer-sale.png', 'dark'],
            ['New | 1024Wh | 3600W Surge', 'DELTA 3 Classic', 'Best-value 1kWh portable station for everyday backup.', 'Learn More', '/products/delta-3-classic-1024wh', 'power-stations', '/promo/delta-classic.png', 'light'],
            ['New', 'Eco Buka Power Bank', 'Fast USB-C charging for phones, tablets, and laptops.', 'Buy Now', '/category/power-banks', 'power-banks', '/promo/power-bank.png', 'light'],
            ['New Release', 'DELTA 3 Max Series', '2kWh capacity and 3000W max output for larger appliances.', 'Learn More', '/products/delta-3-max-series', 'power-stations', '/promo/delta-max-series.png', 'light'],
            ['Solar Kit', 'Balcony Solar Starter Kit', 'Plug-and-play solar for apartments and compact homes.', 'Explore', '/products/balcony-solar-starter-kit', 'solar-generators', '/promo/summer-sale.png', 'dark'],
        ] as $index => [$label, $title, $subtitle, $button, $link, $categorySlug, $image, $textColor]) {
            PromoCard::query()->updateOrCreate(
                ['section_key' => 'new_products', 'title' => $title],
                [
                    'homepage_section_id' => $newProductsSection->id,
                    'label' => $label,
                    'subtitle' => $subtitle,
                    'button_text' => $button,
                    'button_link' => $link,
                    'category_slug' => $categorySlug,
                    'background_image' => $image,
                    'mobile_background_image' => $image,
                    'text_color' => $textColor,
                    'active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        foreach ([
            ['Promotional Offers', 'Deals, clearance items, and ready-to-ship accessories.', 'Buy Now', '/category/accessories', 'accessories', '/promo/delta-classic.png'],
            ['Home Battery', 'Scalable backup and solar storage for homes and businesses.', 'Contact Us', '/category/home-battery', 'home-battery', '/promo/delta-max-series.png'],
        ] as $index => [$title, $subtitle, $button, $link, $categorySlug, $image]) {
            PromoCard::query()->updateOrCreate(
                ['section_key' => 'promotional_category_cards', 'title' => $title],
                [
                    'homepage_section_id' => $promotionalCardsSection->id,
                    'subtitle' => $subtitle,
                    'button_text' => $button,
                    'button_link' => $link,
                    'category_slug' => $categorySlug,
                    'background_image' => $image,
                    'mobile_background_image' => $image,
                    'text_color' => 'light',
                    'active' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }

        $showcase = ShowcaseSection::query()->updateOrCreate(
            ['section_key' => 'solar_system_showcase'],
            [
                'title' => 'STREAM Solar Plant',
                'subtitle' => 'Featured plug-and-play solar products selected for realistic homepage testing.',
                'section_type' => 'mixed_showcase',
                'source_type' => 'manual_products',
                'source_slug' => 'new-products',
                'display_limit' => 4,
                'active' => true,
                'sort_order' => 1,
            ],
        );

        $showcaseSlugs = ['stream-ultra-x', 'stream-ultra', 'stream-ac-pro', 'all-in-one-home-solar-kit'];
        $sync = [];
        foreach ($showcaseSlugs as $index => $slug) {
            $product = $products->firstWhere('slug', $slug);
            if ($product) {
                $sync[$product->id] = ['sort_order' => $index + 1, 'active' => true];
            }
        }
        $showcase->products()->sync($sync);

        NavigationCard::query()->update(['active' => false]);

        NavigationCard::query()->updateOrCreate(
            ['section_key' => 'solar_system_showcase', 'title' => 'View all STREAM Series'],
            ['link' => '/category/stream-series', 'image' => '/promo/delta-max-series.png', 'active' => true, 'sort_order' => 1],
        );
        NavigationCard::query()->updateOrCreate(
            ['section_key' => 'solar_system_showcase', 'title' => 'View STREAM Series Accessories'],
            ['link' => '/category/accessories', 'image' => '/promo/summer-sale.png', 'active' => true, 'sort_order' => 2],
        );

        FeatureBanner::query()->update(['active' => false]);

        FeatureBanner::query()->updateOrCreate(
            ['section_key' => 'solar_system_showcase', 'title' => 'Eco Buka STREAM Series Plug & Play Solar Plant'],
            [
                'section_heading' => 'Eco Buka STREAM Series Plug & Play Solar Plant',
                'eyebrow' => 'FLASH SALE & Up to 43% OFF!',
                'subtitle' => 'Run high-wattage appliances with solar-ready storage.',
                'price_text' => 'Starting at EUR 1199',
                'button_text' => 'Buy Now',
                'button_link' => '/category/stream-series',
                'background_image' => '/promo/delta-max-series.png',
                'mobile_background_image' => '/promo/delta-max-series.png',
                'text_color' => 'light',
                'text_alignment' => 'left',
                'active' => true,
                'sort_order' => 1,
            ],
        );

        FeatureBanner::query()->updateOrCreate(
            ['section_key' => 'featured_video_promo', 'title' => 'Eco Buka Home Backup and Solar Storage'],
            [
                'section_heading' => 'Eco Buka Home Backup and Solar Storage',
                'eyebrow' => 'Home Energy Solution',
                'subtitle' => 'A premium backup and solar charging setup for homes, offices, and outdoor teams.',
                'price_text' => 'Configured from EUR 1490',
                'button_text' => 'Request Offer',
                'button_link' => '/contact',
                'background_image' => '/promo/summer-sale.png',
                'mobile_background_image' => '/promo/summer-sale.png',
                'text_color' => 'light',
                'text_alignment' => 'left',
                'active' => true,
                'sort_order' => 1,
            ],
        );
    }
}

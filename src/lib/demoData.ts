import type { Category } from '../types/category';
import type { Product } from '../types/product';
import deltaSeriesUrl from '../assets/categories/delta-series.png';
import ocean2Url from '../assets/categories/ocean-2.png';
import powerBanksUrl from '../assets/categories/power-banks.png';
import refurbishedUrl from '../assets/categories/refurbished.png';
import riverSeriesUrl from '../assets/categories/river-series.png';
import smartDevicesUrl from '../assets/categories/smart-devices.png';
import solarPanelsUrl from '../assets/categories/solar-panels.png';
import streamSeriesUrl from '../assets/categories/stream-series.png';
import streamSolarSystemUrl from '../assets/categories/stream-solar-system.png';
import showcaseBannerUrl from '../assets/heroes/hero-delta-max.png';

export const demoCategories: Category[] = [
  { id: 'stream-series', name: 'STREAM Series Plug & Play Solar Plant', slug: 'stream-series', description: 'Plug-and-play balcony solar solutions for simple home energy production.', image_url: streamSeriesUrl, is_new: true, active: true },
  { id: 'stream-solar-system', name: 'STREAM Plug & Play Solar System', slug: 'stream-solar-system', description: 'Compact solar systems designed for everyday home energy savings.', image_url: streamSolarSystemUrl, is_new: true, active: true },
  { id: 'delta-series', name: 'DELTA Series', slug: 'delta-series', description: 'High-capacity portable stations for home backup and professional power.', image_url: deltaSeriesUrl, is_new: false, active: true },
  { id: 'river-series', name: 'RIVER Series', slug: 'river-series', description: 'Light portable power for outdoor use, travel, and everyday essentials.', image_url: riverSeriesUrl, is_new: true, active: true },
  { id: 'solar-panels', name: 'Solar Panels', slug: 'solar-panels', description: 'Foldable and fixed solar panels for clean charging anywhere.', image_url: solarPanelsUrl, is_new: true, active: true },
  { id: 'smart-devices', name: 'Smart Devices', slug: 'smart-devices', description: 'Connected devices for monitoring and optimizing home energy use.', image_url: smartDevicesUrl, is_new: true, active: true },
  { id: 'ocean-2', name: 'OCEAN 2', slug: 'ocean-2', description: 'Dedicated energy equipment for pool, outdoor, and seasonal systems.', image_url: ocean2Url, is_new: false, active: true },
  { id: 'power-banks', name: 'Power Banks', slug: 'power-banks', description: 'Compact mobile charging for daily carry and travel backup.', image_url: powerBanksUrl, is_new: true, active: true },
  { id: 'refurbished', name: 'Refurbished', slug: 'refurbished', description: 'Professionally inspected value products for budget-friendly energy access.', image_url: refurbishedUrl, is_new: false, active: true },
];

export const demoProducts: Product[] = [
  {
    id: 'eb-500',
    name: 'Eco Buka Station 500',
    slug: 'eco-buka-station-500',
    category_id: 'delta-series',
    category: 'DELTA Series',
    short_description: 'Everyday portable power for phones, laptops, cameras, routers, and small appliances.',
    description: 'A compact, presentation-ready power station for home backup and outdoor use.',
    price: 499,
    old_price: 579,
    image_url: deltaSeriesUrl,
    specs: { Capacity: '512Wh', Output: '500W', 'Solar Input': '220W', Weight: '6.4kg' },
    badge: 'Best Seller',
    featured: true,
    active: true,
  },
  {
    id: 'eb-1200',
    name: 'Eco Buka Station 1200',
    slug: 'eco-buka-station-1200',
    category_id: 'delta-series',
    category: 'DELTA Series',
    short_description: 'More capacity for business continuity, refrigeration, tools, and home backup.',
    description: 'High-output backup power with expandable storage and smart monitoring.',
    price: 1190,
    old_price: 1390,
    image_url: deltaSeriesUrl,
    specs: { Capacity: '1.2kWh', Output: '1800W', 'Charge Time': '70 min', UPS: 'Yes' },
    badge: 'Sale',
    featured: true,
    active: true,
  },
  {
    id: 'solar-220',
    name: 'Eco Buka Solar Panel 220W',
    slug: 'eco-buka-solar-panel-220w',
    category_id: 'solar-panels',
    category: 'Solar Panels',
    short_description: 'Foldable solar panel with high conversion efficiency and weather-resistant finish.',
    description: 'Portable solar charging for stations, cabins, and field operations.',
    price: 319,
    image_url: solarPanelsUrl,
    specs: { Power: '220W', Cells: 'Monocrystalline', Rating: 'IP68', Weight: '7.2kg' },
    badge: 'New',
    featured: true,
    active: true,
  },
  {
    id: 'river-mini',
    name: 'Eco Buka River Mini',
    slug: 'eco-buka-river-mini',
    category_id: 'river-series',
    category: 'RIVER Series',
    short_description: 'Compact portable power for camping, travel, cameras, and daily backup.',
    description: 'A light mobile station made for quick charging and outdoor flexibility.',
    price: 249,
    old_price: 299,
    image_url: riverSeriesUrl,
    specs: { Capacity: '256Wh', Output: '300W', 'USB-C': '100W', Weight: '3.5kg' },
    badge: 'New',
    featured: true,
    active: true,
  },
];

export const demoHomepageShowcase = {
  section: {
    id: 'homepage-showcase',
    section_key: 'solar_system_showcase',
    title: 'New Products',
    subtitle: 'Fresh energy solutions selected for homes, businesses, and outdoor power.',
    layout_type: 'products_with_navigation_and_banner',
    active: true,
    sort_order: 10,
  },
  productLinks: demoProducts.slice(0, 4).map((product, index) => ({
    id: `showcase-product-${product.id}`,
    section_id: 'homepage-showcase',
    product_id: product.id,
    product,
    active: true,
    sort_order: index + 1,
  })),
  navigationCards: [
    {
      id: 'showcase-nav-accessories',
      section_id: 'homepage-showcase',
      title: 'View All Accessories',
      link: '/categories/power-banks',
      image_url: powerBanksUrl,
      active: true,
      sort_order: 1,
    },
    {
      id: 'showcase-nav-solar',
      section_id: 'homepage-showcase',
      title: 'Explore Solar Kits',
      link: '/categories/solar-panels',
      image_url: solarPanelsUrl,
      active: true,
      sort_order: 2,
    },
  ],
  banners: [
    {
      id: 'showcase-banner-delta',
      section_id: 'homepage-showcase',
      section_heading: 'Eco Buka DELTA Pro Ultra Power Station',
      eyebrow: 'New',
      title: 'Eco Buka DELTA Pro Ultra Power Station',
      subtitle: '6kWh-30kWh Capacity | 6900W Output',
      button_text: 'Buy Now',
      button_link: '/products/eco-buka-station-1200',
      background_image_url: showcaseBannerUrl,
      mobile_background_image_url: showcaseBannerUrl,
      text_color: 'light',
      text_alignment: 'left',
      active: true,
      sort_order: 1,
    },
  ],
};

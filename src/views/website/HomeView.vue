<script setup lang="ts">
import { computed, defineAsyncComponent, onMounted, ref } from 'vue';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import CategoryCarousel from '../../components/website/CategoryCarousel.vue';
import HeroSlider from '../../components/website/HeroSlider.vue';
import { apiGet, hasLaravelApiConfig } from '../../lib/api';
import { useSeo } from '../../lib/seo';
import { useCategoryStore } from '../../stores/categoryStore';
import type { HomepageSection } from '../../types/homepage';

const FeaturedCategoryProducts = defineAsyncComponent(() => import('../../components/website/FeaturedCategoryProducts.vue'));
const FeaturedVideoPromoSection = defineAsyncComponent(() => import('../../components/website/FeaturedVideoPromoSection.vue'));
const HomepageProductGridSection = defineAsyncComponent(() => import('../../components/website/HomepageProductGridSection.vue'));
const NewProductsCarousel = defineAsyncComponent(() => import('../../components/website/NewProductsCarousel.vue'));
const ProductShowcaseSection = defineAsyncComponent(() => import('../../components/website/ProductShowcaseSection.vue'));
const PromotionalCategoryCardsSection = defineAsyncComponent(() => import('../../components/website/PromotionalCategoryCardsSection.vue'));
const PromoBanner = defineAsyncComponent(() => import('../../components/website/PromoBanner.vue'));

const categoryStore = useCategoryStore();
const homepageSections = ref<HomepageSection[]>([]);

type HomepagePayload = {
  showcase_sections?: Array<HomepageSection | { section: HomepageSection }>;
};

const fallbackSections: HomepageSection[] = [
  {
    id: 'fallback-stream-showcase',
    section_key: 'solar_system_showcase',
    title: 'STREAM Solar Plant',
    section_type: 'mixed_showcase',
    active: true,
    sort_order: 1,
  },
  {
    id: 'fallback-new-products',
    section_key: 'new_products',
    title: 'New Products',
    subtitle: 'Fresh energy launches, seasonal offers, and smart power picks for Eco Buka customers.',
    section_type: 'promo_cards',
    active: true,
    sort_order: 2,
  },
  {
    id: 'fallback-power-stations',
    section_key: 'power_stations_featured',
    title: 'Power Stations',
    subtitle: 'Explore reliable portable power solutions for home, outdoor, and backup energy.',
    section_type: 'featured_category',
    source_type: 'category',
    source_slug: 'power-stations',
    active: true,
    sort_order: 3,
  },
  {
    id: 'fallback-popular',
    section_key: 'popular_eco_buka',
    title: 'Popular Eco Buka solutions',
    section_type: 'product_grid',
    active: true,
    sort_order: 6,
  },
  {
    id: 'fallback-promotional-cards',
    section_key: 'promotional_category_cards',
    title: 'Promotional Category Cards',
    section_type: 'promo_cards',
    active: true,
    sort_order: 7,
  },
  {
    id: 'fallback-video',
    section_key: 'featured_video_promo',
    title: 'Featured Video Promo',
    section_type: 'video_banner',
    active: true,
    sort_order: 8,
  },
];

const orderedSections = computed(() =>
  (homepageSections.value.length ? homepageSections.value : fallbackSections)
    .filter((section) => section.active !== false)
    .sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0) || String(a.title || a.section_key).localeCompare(String(b.title || b.section_key))),
);

useSeo({
  title: 'Portable Power and Solar Energy Solutions',
  description: 'Shop Eco Buka portable power stations, solar panels, solar generator kits, home battery storage, and clean backup energy solutions.',
  canonicalPath: '/',
});

onMounted(() => {
  categoryStore.fetchCategories();
  fetchHomepageSections();
});

function normalizeHomepageSection(entry: HomepageSection | { section: HomepageSection }) {
  return 'section' in entry ? entry.section : entry;
}

async function fetchHomepageSections() {
  if (!hasLaravelApiConfig) return;

  try {
    const data = await apiGet<HomepagePayload>('/homepage');
    homepageSections.value = (data.showcase_sections || []).map(normalizeHomepageSection);
  } catch {
    homepageSections.value = [];
  }
}
</script>

<template>
    <WebsiteLayout>
    <HeroSlider />
    <CategoryCarousel :categories="categoryStore.activeCategories" />

    <template v-for="section in orderedSections" :key="section.id || section.section_key">
      <NewProductsCarousel
        v-if="section.section_type === 'promo_cards' && section.section_key === 'new_products'"
        :section-key="section.section_key"
        :title="section.title || 'New Products'"
        :subtitle="section.subtitle || ''"
      />

      <PromotionalCategoryCardsSection
        v-else-if="section.section_type === 'promo_cards'"
        :section-key="section.section_key"
      />

      <FeaturedCategoryProducts
        v-else-if="section.section_type === 'featured_category'"
        :section-key="section.section_key"
        :category-slug="section.source_slug || 'power-stations'"
        :title="section.title || ''"
        :description="section.subtitle || ''"
      />

      <ProductShowcaseSection
        v-else-if="section.section_type === 'mixed_showcase'"
        :section-key="section.section_key"
      />

      <HomepageProductGridSection
        v-else-if="section.section_type === 'product_grid' || section.section_type === 'product_carousel'"
        :section-key="section.section_key"
      />

      <FeaturedVideoPromoSection
        v-else-if="section.section_type === 'video_banner'"
        :section-key="section.section_key"
      />
    </template>

    <PromoBanner />
  </WebsiteLayout>
</template>

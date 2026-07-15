<script setup lang="ts">
import { defineAsyncComponent, onMounted } from 'vue';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import CategoryCarousel from '../../components/website/CategoryCarousel.vue';
import HeroSlider from '../../components/website/HeroSlider.vue';
import { useSeo } from '../../lib/seo';
import { useCategoryStore } from '../../stores/categoryStore';
import { useProductStore } from '../../stores/productStore';

const FeaturedCategoryProducts = defineAsyncComponent(() => import('../../components/website/FeaturedCategoryProducts.vue'));
const FeaturedVideoPromoSection = defineAsyncComponent(() => import('../../components/website/FeaturedVideoPromoSection.vue'));
const NewProductsCarousel = defineAsyncComponent(() => import('../../components/website/NewProductsCarousel.vue'));
const ProductGrid = defineAsyncComponent(() => import('../../components/website/ProductGrid.vue'));
const ProductShowcaseSection = defineAsyncComponent(() => import('../../components/website/ProductShowcaseSection.vue'));
const PromotionalCategoryCardsSection = defineAsyncComponent(() => import('../../components/website/PromotionalCategoryCardsSection.vue'));
const PromoBanner = defineAsyncComponent(() => import('../../components/website/PromoBanner.vue'));

const categoryStore = useCategoryStore();
const productStore = useProductStore();

useSeo({
  title: 'Portable Power and Solar Energy Solutions',
  description: 'Shop Eco Buka portable power stations, solar panels, solar generator kits, home battery storage, and clean backup energy solutions.',
  canonicalPath: '/',
});

function runWhenIdle(callback: () => void) {
  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(callback, { timeout: 1800 });
    return;
  }

  globalThis.setTimeout(callback, 900);
}

onMounted(() => {
  categoryStore.fetchCategories();
  runWhenIdle(() => {
    productStore.fetchProducts();
  });
});
</script>

<template>
    <WebsiteLayout>
    <HeroSlider />
    <CategoryCarousel :categories="categoryStore.activeCategories" />
    <NewProductsCarousel subtitle="Fresh energy launches, seasonal offers, and smart power picks for Eco Buka customers." />
    <FeaturedCategoryProducts
      category-slug="power-stations"
      title="Power Stations"
      description="Explore reliable portable power solutions for home, outdoor, and backup energy."
    />
    <PromoBanner />
    <ProductShowcaseSection />
    <section class="container-shell py-10 sm:py-12 lg:py-14">
      <div>
        <p class="label">Featured catalogue</p>
        <h2 class="mt-2 text-2xl font-black sm:text-3xl">Popular Eco Buka solutions</h2>
      </div>
      <div class="mt-6 sm:mt-8">
        <ProductGrid :products="productStore.featuredProducts" />
      </div>
    </section>
    <PromotionalCategoryCardsSection />
    <FeaturedVideoPromoSection />
  </WebsiteLayout>
</template>

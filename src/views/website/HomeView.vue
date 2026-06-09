<script setup lang="ts">
import { onMounted } from 'vue';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import CategoryCarousel from '../../components/website/CategoryCarousel.vue';
import FeaturedCategoryProducts from '../../components/website/FeaturedCategoryProducts.vue';
import FeaturedVideoPromoSection from '../../components/website/FeaturedVideoPromoSection.vue';
import HeroSlider from '../../components/website/HeroSlider.vue';
import NewProductsCarousel from '../../components/website/NewProductsCarousel.vue';
import ProductGrid from '../../components/website/ProductGrid.vue';
import ProductShowcaseSection from '../../components/website/ProductShowcaseSection.vue';
import PromotionalCategoryCardsSection from '../../components/website/PromotionalCategoryCardsSection.vue';
import PromoBanner from '../../components/website/PromoBanner.vue';
import { useCategoryStore } from '../../stores/categoryStore';
import { useProductStore } from '../../stores/productStore';

const categoryStore = useCategoryStore();
const productStore = useProductStore();

onMounted(async () => {
  await Promise.all([categoryStore.fetchCategories(), productStore.fetchProducts()]);
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

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import CategoryNavigationCard from './CategoryNavigationCard.vue';
import FeatureBanner from './FeatureBanner.vue';
import ProductCard from './ProductCard.vue';
import { useHomepageStore } from '../../stores/homepageStore';
import { useProductStore } from '../../stores/productStore';

const props = withDefaults(
  defineProps<{
    sectionKey?: string;
  }>(),
  {
    sectionKey: 'solar_system_showcase',
  },
);

const homepageStore = useHomepageStore();
const productStore = useProductStore();

const sectionVisible = computed(() => homepageStore.section?.active !== false);
const displayLimit = computed(() => Number(homepageStore.section?.display_limit || 4));
const displayProducts = computed(() => {
  const selected = homepageStore.showcaseProducts.filter((product) => product.active);
  const fallback = productStore.featuredProducts.length ? productStore.featuredProducts : productStore.activeProducts;
  return (selected.length ? selected : fallback).slice(0, displayLimit.value);
});
const displayCards = computed(() => homepageStore.activeNavigationCards.slice(0, 2));
const banner = computed(() => homepageStore.primaryBanner);

onMounted(async () => {
  await Promise.all([productStore.fetchProducts(), homepageStore.fetchHomepageShowcase(props.sectionKey)]);
});
</script>

<template>
  <section v-if="sectionVisible" class="bg-mist py-8 sm:py-11 lg:py-12">
    <div class="container-shell">
      <div v-if="homepageStore.section?.title || homepageStore.section?.subtitle" class="mb-7 max-w-3xl">
        <h2 v-if="homepageStore.section?.title" class="text-2xl font-black leading-tight tracking-normal text-ink sm:text-3xl">
          {{ homepageStore.section.title }}
        </h2>
        <p v-if="homepageStore.section?.subtitle" class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">
          {{ homepageStore.section.subtitle }}
        </p>
      </div>

      <div v-if="homepageStore.loading" class="rounded-lg bg-white p-8 text-sm font-semibold text-slate-500 shadow-sm">
        Loading homepage showcase...
      </div>

      <template v-else>
        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_286px] xl:gap-5">
          <div
            v-if="displayProducts.length"
            class="flex min-w-0 snap-x gap-4 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] sm:grid sm:grid-cols-2 sm:overflow-visible sm:pb-0 xl:grid-cols-4 [&::-webkit-scrollbar]:hidden"
          >
            <ProductCard
              v-for="product in displayProducts"
              :key="product.id"
              :product="product"
              :show-specs="false"
              compact
              variant="showcase"
              class="w-[82vw] max-w-[300px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink"
            />
          </div>

          <div v-else class="rounded-lg bg-white p-8 text-sm font-semibold text-slate-500 shadow-sm xl:col-span-2">
            No showcase products selected yet.
          </div>

          <div v-if="displayCards.length" class="flex snap-x gap-4 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] sm:grid sm:grid-cols-2 sm:overflow-visible sm:pb-0 xl:grid-cols-1 [&::-webkit-scrollbar]:hidden">
            <CategoryNavigationCard
              v-for="card in displayCards"
              :key="card.id"
              :card="card"
              variant="showcase"
              class="w-[82vw] max-w-[300px] shrink-0 snap-start sm:w-auto sm:max-w-none sm:shrink"
            />
          </div>
        </div>

        <div v-if="banner" class="mt-10 sm:mt-12">
          <FeatureBanner :banner="banner" />
        </div>
      </template>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import ProductGallerySlider from '../../components/website/ProductGallerySlider.vue';
import { useProductStore } from '../../stores/productStore';

const route = useRoute();
const productStore = useProductStore();
const product = computed(() => productStore.activeProducts.find((item) => item.slug === route.params.slug));
const money = (value?: number | null) => (value ? new Intl.NumberFormat('en-EU', { style: 'currency', currency: 'EUR' }).format(value) : 'Request price');

onMounted(() => productStore.fetchProducts());
</script>

<template>
  <WebsiteLayout>
    <section v-if="product" class="container-shell grid gap-7 py-8 sm:gap-8 sm:py-12 lg:grid-cols-[1.05fr_0.95fr] lg:gap-10">
      <ProductGallerySlider :title="product.name" :main-image="product.image_url" :gallery="product.gallery" />
      <div class="min-w-0">
        <p class="label">{{ product.category }}</p>
        <h1 class="mt-2 text-3xl font-black leading-tight min-[390px]:text-4xl">{{ product.name }}</h1>
        <p class="mt-4 text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">{{ product.short_description }}</p>
        <div class="mt-6 flex flex-wrap items-end gap-3">
          <p class="text-2xl font-black sm:text-3xl">{{ money(product.price) }}</p>
          <p v-if="product.old_price" class="text-slate-400 line-through">{{ money(product.old_price) }}</p>
        </div>
        <div class="mt-7 flex flex-col gap-3 min-[420px]:flex-row min-[420px]:flex-wrap sm:mt-8">
          <RouterLink to="/contact" class="btn-primary w-full min-[420px]:w-auto">Request Offer</RouterLink>
          <RouterLink to="/contact" class="btn-secondary w-full min-[420px]:w-auto">Contact Us</RouterLink>
        </div>
        <div class="mt-7 grid gap-3 sm:mt-8 sm:grid-cols-2">
          <div v-for="(value, key) in product.specs" :key="key" class="min-w-0 rounded-lg border border-line p-4">
            <p class="text-xs font-bold uppercase text-slate-500">{{ key }}</p>
            <p class="mt-1 text-lg font-black">{{ value }}</p>
          </div>
        </div>
      </div>
    </section>
    <section v-if="product" class="container-shell pb-8 sm:pb-14">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
        <div class="rounded-lg border border-line bg-white p-4 sm:p-5"><h2 class="font-black">Overview</h2><p class="mt-3 text-sm leading-6 text-slate-600">{{ product.description }}</p></div>
        <div class="rounded-lg border border-line bg-white p-4 sm:p-5"><h2 class="font-black">Specifications</h2><p class="mt-3 text-sm leading-6 text-slate-600">Structured specs are ready for expanded Supabase fields.</p></div>
        <div class="rounded-lg border border-line bg-white p-4 sm:p-5"><h2 class="font-black">What is included</h2><p class="mt-3 text-sm leading-6 text-slate-600">Product unit, charging cable, documentation, and offer support.</p></div>
        <div class="rounded-lg border border-line bg-white p-4 sm:p-5"><h2 class="font-black">Downloads</h2><p class="mt-3 text-sm leading-6 text-slate-600">Datasheets and manuals can be added through Supabase Storage.</p></div>
      </div>
    </section>
  </WebsiteLayout>
</template>

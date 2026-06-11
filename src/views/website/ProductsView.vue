<script setup lang="ts">
import { onMounted } from 'vue';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import ProductGrid from '../../components/website/ProductGrid.vue';
import { useSeo } from '../../lib/seo';
import { useProductStore } from '../../stores/productStore';

const productStore = useProductStore();

useSeo({
  title: 'Products',
  description: 'Browse Eco Buka portable power stations, solar panels, solar generator kits, home battery systems, power banks, and accessories.',
  canonicalPath: '/products',
});

onMounted(() => productStore.fetchProducts());
</script>

<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell">
        <p class="label">Catalogue</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Products</h1>
        <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">Browse portable stations, solar kits, backup systems, and accessories prepared for presentation and Supabase data.</p>
      </div>
    </section>
    <section class="container-shell py-10 sm:py-12">
      <ProductGrid :products="productStore.activeProducts" />
    </section>
  </WebsiteLayout>
</template>

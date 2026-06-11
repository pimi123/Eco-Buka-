<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import ProductGrid from '../../components/website/ProductGrid.vue';
import { useSeo } from '../../lib/seo';
import { useProductStore } from '../../stores/productStore';

const query = ref('');
const productStore = useProductStore();
const results = computed(() => {
  const term = query.value.toLowerCase().trim();
  if (!term) return productStore.activeProducts;
  return productStore.activeProducts.filter((product) => `${product.name} ${product.short_description} ${product.description}`.toLowerCase().includes(term));
});

useSeo({
  title: 'Search Products',
  description: 'Search Eco Buka portable power stations, solar panels, batteries, smart devices, and clean energy accessories.',
  canonicalPath: '/search',
});

onMounted(() => productStore.fetchProducts());
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-10 sm:py-12">
      <h1 class="text-3xl font-black sm:text-4xl">Search products</h1>
      <input v-model="query" class="input-field mt-6 max-w-xl" placeholder="Search by name, capacity, category..." />
      <div class="mt-6 sm:mt-8">
        <ProductGrid :products="results" />
      </div>
    </section>
  </WebsiteLayout>
</template>

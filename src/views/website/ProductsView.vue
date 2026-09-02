<script setup lang="ts">
import { onMounted } from 'vue';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import ProductGrid from '../../components/website/ProductGrid.vue';
import { useSeo } from '../../lib/seo';
import { useProductStore } from '../../stores/productStore';

const productStore = useProductStore();

useSeo({
  title: 'Produktet',
  description: 'Shfletoni stacione portative energjie, panele solare, gjeneratorë solarë, bateri për shtëpi, power bank dhe aksesorë Eco Buka.',
  canonicalPath: '/products',
});

onMounted(() => productStore.fetchProducts());
</script>

<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell">
        <p class="label">Katalogu</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Produktet</h1>
        <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">Shfletoni stacione portative, sete solare, sisteme rezervë dhe aksesorë të përgatitur për zgjidhje energjie Eco Buka.</p>
      </div>
    </section>
    <section class="container-shell py-10 sm:py-12">
      <ProductGrid :products="productStore.activeProducts" />
    </section>
  </WebsiteLayout>
</template>

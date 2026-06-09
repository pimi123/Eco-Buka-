<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import ProductGrid from '../../components/website/ProductGrid.vue';
import { useCategoryStore } from '../../stores/categoryStore';
import { useProductStore } from '../../stores/productStore';

const route = useRoute();
const categoryStore = useCategoryStore();
const productStore = useProductStore();

const category = computed(() => categoryStore.categories.find((item) => item.slug === route.params.slug));
const products = computed(() => productStore.activeProducts.filter((product) => product.category_id === category.value?.id));

onMounted(async () => {
  await Promise.all([categoryStore.fetchCategories(), productStore.fetchProducts()]);
});
</script>

<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell">
        <p class="label">Category</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ category?.name || 'Products' }}</h1>
        <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">{{ category?.description }}</p>
      </div>
    </section>
    <section class="container-shell py-10 sm:py-12">
      <ProductGrid :products="products" />
    </section>
  </WebsiteLayout>
</template>

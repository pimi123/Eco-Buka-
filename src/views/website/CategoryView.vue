<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import ProductGrid from '../../components/website/ProductGrid.vue';
import { useSeo } from '../../lib/seo';
import { useCategoryStore } from '../../stores/categoryStore';
import { useProductStore } from '../../stores/productStore';
import type { Product } from '../../types/product';

const route = useRoute();
const categoryStore = useCategoryStore();
const productStore = useProductStore();

const products = ref<Product[]>([]);
const loading = ref(true);
const error = ref('');
const currentSlug = computed(() => String(route.params.slug || ''));
const category = computed(() => categoryStore.categories.find((item) => item.slug === currentSlug.value));

useSeo({
  title: computed(() => category.value?.name || 'Product Category'),
  description: computed(() => category.value?.description || `Browse Eco Buka products in the ${currentSlug.value.replace(/-/g, ' ')} category.`),
  canonicalPath: computed(() => `/category/${currentSlug.value}`),
});

async function loadCategory(slug: string) {
  loading.value = true;
  error.value = '';
  products.value = [];

  try {
    if (!categoryStore.categories.length) await categoryStore.fetchCategories();

    if (!category.value) {
      loading.value = false;
      return;
    }

    products.value = await productStore.fetchProductsByCategory(slug);
  } catch {
    error.value = 'We could not load this category right now.';
  } finally {
    loading.value = false;
  }
}

onMounted(() => loadCategory(currentSlug.value));
watch(currentSlug, (slug) => loadCategory(slug));
</script>

<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell">
        <p class="label">Category</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ category?.name || (loading ? 'Loading category' : 'Category not found') }}</h1>
        <p v-if="category?.description" class="mt-4 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">{{ category.description }}</p>
      </div>
    </section>
    <section class="container-shell py-10 sm:py-12">
      <div v-if="loading" class="rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Loading products...
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-100 bg-red-50 p-6 text-sm font-semibold text-red-700">
        {{ error }}
      </div>
      <div v-else-if="!category" class="rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Category not found.
      </div>
      <div v-else-if="!products.length" class="rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        No products found in this category.
      </div>
      <ProductGrid v-else :products="products" />
    </section>
  </WebsiteLayout>
</template>

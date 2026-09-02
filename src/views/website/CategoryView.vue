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
const loadingMore = ref(false);
const error = ref('');
const page = ref(1);
const hasMore = ref(false);
const perPage = 24;
const currentSlug = computed(() => String(route.params.slug || ''));
const category = computed(() => categoryStore.categories.find((item) => item.slug === currentSlug.value));

useSeo({
  title: computed(() => category.value?.name || 'Kategori produktesh'),
  description: computed(() => category.value?.description || `Shfletoni produktet Eco Buka në kategorinë ${currentSlug.value.replace(/-/g, ' ')}.`),
  canonicalPath: computed(() => `/category/${currentSlug.value}`),
});

async function loadCategory(slug: string) {
  loading.value = true;
  error.value = '';
  products.value = [];
  page.value = 1;
  hasMore.value = false;

  try {
    if (!categoryStore.categories.length) await categoryStore.fetchCategories();

    if (!category.value) {
      loading.value = false;
      return;
    }

    const result = await productStore.fetchProductsByCategory(slug, page.value, perPage);
    products.value = result.products;
    hasMore.value = result.hasMore;
  } catch {
    error.value = 'Kjo kategori nuk mund të ngarkohet për momentin.';
  } finally {
    loading.value = false;
  }
}

async function loadMoreProducts() {
  if (!hasMore.value || loadingMore.value) return;

  loadingMore.value = true;

  try {
    const nextPage = page.value + 1;
    const result = await productStore.fetchProductsByCategory(currentSlug.value, nextPage, perPage);
    products.value = [...products.value, ...result.products];
    page.value = result.currentPage;
    hasMore.value = result.hasMore;
  } catch {
    error.value = 'Produktet e tjera nuk mund të ngarkohen për momentin.';
  } finally {
    loadingMore.value = false;
  }
}

onMounted(() => loadCategory(currentSlug.value));
watch(currentSlug, (slug) => loadCategory(slug));
</script>

<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell">
        <p class="label">Kategoria</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ category?.name || (loading ? 'Duke ngarkuar kategorinë' : 'Kategoria nuk u gjet') }}</h1>
        <p v-if="category?.description" class="mt-4 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">{{ category.description }}</p>
      </div>
    </section>
    <section class="container-shell py-10 sm:py-12">
      <div v-if="loading" class="rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Duke ngarkuar produktet...
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-100 bg-red-50 p-6 text-sm font-semibold text-red-700">
        {{ error }}
      </div>
      <div v-else-if="!category" class="rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Kategoria nuk u gjet.
      </div>
      <div v-else-if="!products.length" class="rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Nuk u gjet asnjë produkt në këtë kategori.
      </div>
      <template v-else>
        <ProductGrid :products="products" />
        <div v-if="hasMore" class="mt-8 flex justify-center">
          <button class="rounded-full border border-line bg-white px-6 py-3 text-sm font-bold shadow-sm transition hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60" type="button" :disabled="loadingMore" @click="loadMoreProducts">
            {{ loadingMore ? 'Duke ngarkuar...' : 'Ngarko më shumë produkte' }}
          </button>
        </div>
      </template>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import ProductGrid from '../../components/website/ProductGrid.vue';
import { useSeo } from '../../lib/seo';
import { useCollectionStore } from '../../stores/collectionStore';
import { useProductStore } from '../../stores/productStore';
import type { Product } from '../../types/product';

const route = useRoute();
const collectionStore = useCollectionStore();
const productStore = useProductStore();

const products = ref<Product[]>([]);
const loading = ref(true);
const loadingMore = ref(false);
const error = ref('');
const page = ref(1);
const hasMore = ref(false);
const perPage = 24;
const currentSlug = computed(() => String(route.params.slug || ''));
const collection = computed(() => collectionStore.collections.find((item) => item.slug === currentSlug.value));

useSeo({
  title: computed(() => collection.value?.name || 'Eco Buka Collection'),
  description: computed(() => collection.value?.description || `Shfletoni produktet Eco Buka të zgjedhura për ${currentSlug.value.replace(/-/g, ' ')}.`),
  canonicalPath: computed(() => `/collections/${currentSlug.value}`),
});

async function loadCollection(slug: string) {
  loading.value = true;
  error.value = '';
  products.value = [];
  page.value = 1;
  hasMore.value = false;

  try {
    if (!collectionStore.collections.length) await collectionStore.fetchCollections();

    if (!collection.value) {
      loading.value = false;
      return;
    }

    const result = await productStore.fetchProductsByCollection(slug, page.value, perPage);
    products.value = result.products;
    hasMore.value = result.hasMore;
  } catch {
    error.value = 'Ky koleksion nuk mund të ngarkohet për momentin.';
  } finally {
    loading.value = false;
  }
}

async function loadMoreProducts() {
  if (!hasMore.value || loadingMore.value) return;

  loadingMore.value = true;

  try {
    const nextPage = page.value + 1;
    const result = await productStore.fetchProductsByCollection(currentSlug.value, nextPage, perPage);
    products.value = [...products.value, ...result.products];
    page.value = result.currentPage;
    hasMore.value = result.hasMore;
  } catch {
    error.value = 'Produktet e tjera nuk mund të ngarkohen për momentin.';
  } finally {
    loadingMore.value = false;
  }
}

onMounted(() => loadCollection(currentSlug.value));
watch(currentSlug, (slug) => loadCollection(slug));
</script>

<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell">
        <p class="label">{{ collection?.type || 'Koleksion' }}</p>
        <h1 class="mt-2 text-3xl font-black capitalize sm:text-4xl">{{ collection?.name || (loading ? 'Duke ngarkuar koleksionin' : 'Koleksioni nuk u gjet') }}</h1>
        <p v-if="collection?.description" class="mt-4 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">{{ collection.description }}</p>
      </div>
    </section>

    <section class="container-shell py-10 sm:py-12">
      <div v-if="loading" class="rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Duke ngarkuar produktet...
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-100 bg-red-50 p-6 text-sm font-semibold text-red-700">
        {{ error }}
      </div>
      <div v-else-if="!collection" class="rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Koleksioni nuk u gjet.
      </div>
      <div v-else-if="!products.length" class="rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Nuk u gjet asnjë produkt në këtë koleksion.
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

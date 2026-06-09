<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import fallbackUrl from '../../assets/eco-buka-hero.png';
import { useCategoryStore } from '../../stores/categoryStore';
import { useProductStore } from '../../stores/productStore';

const props = withDefaults(
  defineProps<{
    categorySlug: string;
    title?: string;
    description?: string;
    limit?: number;
  }>(),
  {
    title: '',
    description: '',
    limit: 8,
  },
);

const categoryStore = useCategoryStore();
const productStore = useProductStore();

const selectedCategory = computed(() => categoryStore.activeCategories.find((category) => category.slug === props.categorySlug));
const sectionTitle = computed(() => props.title || selectedCategory.value?.name || props.categorySlug.replace(/-/g, ' '));
const sectionDescription = computed(
  () =>
    props.description ||
    selectedCategory.value?.description ||
    'Explore reliable portable power solutions for home, outdoor, and backup energy.',
);

const products = computed(() => {
  const category = selectedCategory.value;
  if (!category) return [];

  return productStore.activeProducts
    .filter((product) => String(product.category_id) === String(category.id))
    .slice(0, props.limit);
});

const loading = computed(() => categoryStore.loading || productStore.loading);

const money = (value?: number | null) =>
  value ? new Intl.NumberFormat('en-EU', { style: 'currency', currency: 'EUR' }).format(value) : 'Request price';

onMounted(async () => {
  await Promise.all([categoryStore.fetchCategories(), productStore.fetchProducts()]);
});
</script>

<template>
  <section class="bg-white py-10 sm:py-12 lg:py-14">
    <div class="container-shell">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
          <p class="label">Featured category</p>
          <h2 class="mt-2 text-2xl font-black capitalize leading-tight text-ink sm:text-3xl">{{ sectionTitle }}</h2>
          <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">{{ sectionDescription }}</p>
        </div>
        <RouterLink
          :to="`/categories/${categorySlug}`"
          class="btn-secondary w-full sm:w-auto"
        >
          View All
        </RouterLink>
      </div>

      <div v-if="loading" class="mt-6 grid gap-4 sm:mt-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
        <div v-for="index in 4" :key="index" class="h-[390px] animate-pulse rounded-lg border border-line bg-mist" />
      </div>

      <div v-else-if="products.length" class="mt-6 grid gap-4 sm:mt-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
        <article
          v-for="product in products"
          :key="product.id"
          class="group flex min-w-0 flex-col overflow-hidden rounded-lg border border-line bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-panel"
        >
          <RouterLink :to="`/products/${product.slug}`" class="block aspect-[4/3] overflow-hidden bg-mist">
            <img
              :src="product.image_url || fallbackUrl"
              :alt="product.name"
              class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />
          </RouterLink>

          <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-5">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ product.category || sectionTitle }}</p>
            <h3 class="mt-2 text-base font-bold leading-6 text-ink sm:text-lg">{{ product.name }}</h3>
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ product.short_description || product.description }}</p>

            <div class="mt-auto flex items-end justify-between gap-3 pt-5">
              <div class="min-w-0">
                <p class="text-lg font-black text-ink sm:text-xl">{{ money(product.price) }}</p>
                <p v-if="product.old_price" class="text-sm text-slate-400 line-through">{{ money(product.old_price) }}</p>
              </div>
              <RouterLink
                :to="`/products/${product.slug}`"
                class="inline-flex min-h-10 shrink-0 items-center justify-center gap-2 rounded-md border border-line px-3 py-2 text-sm font-bold text-ink transition hover:border-ink"
              >
                View
                <ArrowRight class="h-4 w-4" />
              </RouterLink>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="mt-6 rounded-lg border border-line bg-mist p-8 text-center text-sm font-semibold text-slate-600 sm:mt-8">
        No products found in this category.
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../../lib/api';
import type { HomepageSection } from '../../types/homepage';
import type { Product } from '../../types/product';
import ProductGrid from './ProductGrid.vue';

const props = withDefaults(
  defineProps<{
    sectionKey: string;
    label?: string;
  }>(),
  {
    label: 'Katalog i veçuar',
  },
);

const section = ref<HomepageSection | null>(null);
const products = ref<Product[]>([]);
const loading = ref(false);
const error = ref('');

const visibleProducts = computed(() => products.value.filter((product) => product.active !== false));

onMounted(async () => {
  if (!hasLaravelApiConfig) return;

  loading.value = true;
  error.value = '';

  try {
    const data = await apiGet<{ section: HomepageSection; products: Product[] }>(`/home/showcase/${props.sectionKey}`);
    section.value = data.section;
    products.value = data.products.map((product) => ({
      ...product,
      id: String(product.id),
      image_url: (product as any).image_url || (product as any).main_image_url || null,
      category: typeof (product as any).category === 'object' ? (product as any).category.name : (product as any).category,
    }));
  } catch {
    error.value = 'Ky seksion i ballinës nuk mund të ngarkohet për momentin.';
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <section v-if="loading || error || visibleProducts.length" class="container-shell py-10 sm:py-12 lg:py-14">
    <div>
      <p class="label">{{ label }}</p>
      <h2 class="mt-2 text-2xl font-black sm:text-3xl">{{ section?.title || 'Produktet e ballinës' }}</h2>
      <p v-if="section?.subtitle" class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">{{ section.subtitle }}</p>
    </div>

    <div v-if="loading" class="mt-6 rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
      Duke ngarkuar produktet e seksionit...
    </div>
    <div v-else-if="error" class="mt-6 rounded-lg border border-red-100 bg-red-50 p-6 text-sm font-semibold text-red-700">
      {{ error }}
    </div>
    <div v-else class="mt-6 sm:mt-8">
      <ProductGrid :products="visibleProducts" />
    </div>
  </section>
</template>

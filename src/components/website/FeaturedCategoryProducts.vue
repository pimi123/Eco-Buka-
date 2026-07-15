<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';
import { optimizedImageUrl } from '../../lib/responsiveImages';
import { useCategoryStore } from '../../stores/categoryStore';
import { useProductStore } from '../../stores/productStore';

const fallbackUrl = '/promo/optimized/summer-sale-1280.jpg';

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
const featuredProduct = computed(() => products.value[0]);
const bannerImage = computed(() => optimizedImageUrl(selectedCategory.value?.image_url || featuredProduct.value?.image_url, 'desktop') || fallbackUrl);

const money = (value?: number | null) =>
  value ? new Intl.NumberFormat('en-EU', { style: 'currency', currency: 'EUR' }).format(value) : 'Request price';

onMounted(async () => {
  await Promise.all([categoryStore.fetchCategories(), productStore.fetchProducts()]);
});
</script>

<template>
  <section class="bg-white py-8 sm:py-12 lg:py-14">
    <div class="container-shell">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
          <p class="label">Featured category</p>
          <h2 class="mt-2 text-2xl font-black capitalize leading-tight text-ink sm:text-3xl">{{ sectionTitle }}</h2>
          <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">{{ sectionDescription }}</p>
        </div>
        <RouterLink
          :to="`/category/${categorySlug}`"
          class="btn-secondary w-full sm:w-auto"
        >
          View All
        </RouterLink>
      </div>

      <div
        class="relative mt-6 min-h-[280px] overflow-hidden rounded-xl bg-ink shadow-sm sm:mt-8 sm:min-h-[320px] lg:min-h-[360px]"
      >
        <img
          :src="bannerImage"
          :alt="sectionTitle"
          class="absolute inset-0 h-full w-full object-cover"
          loading="lazy"
          decoding="async"
          sizes="(max-width: 767px) 100vw, 1200px"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-black/86 via-black/52 to-black/18 sm:bg-gradient-to-r sm:from-black/82 sm:via-black/48 sm:to-black/10" />
        <div class="relative z-10 flex min-h-[280px] items-end p-4 text-white min-[390px]:p-5 sm:min-h-[320px] sm:items-center sm:p-8 lg:min-h-[360px] lg:p-10">
          <div class="max-w-xl">
            <p class="text-xs font-black uppercase tracking-wide text-orange-500 sm:text-sm">Featured series</p>
            <h3 class="mt-2 max-w-[16rem] text-2xl font-black leading-tight min-[390px]:text-3xl sm:max-w-xl sm:text-4xl">
              {{ sectionTitle }}
            </h3>
            <p class="mt-3 line-clamp-3 max-w-md text-sm font-semibold leading-6 text-white/90 sm:text-base">
              {{ sectionDescription }}
            </p>
            <div class="mt-5 flex flex-col gap-3 min-[390px]:flex-row min-[390px]:items-center">
              <RouterLink
                :to="`/category/${categorySlug}`"
                class="inline-flex min-h-11 w-full items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-black text-ink transition hover:bg-mist min-[390px]:w-auto"
              >
                Learn More
              </RouterLink>
              <p v-if="featuredProduct" class="text-sm font-black text-white sm:text-base">
                From {{ money(featuredProduct.price) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="loading" class="mt-3 grid grid-cols-1 gap-3 min-[340px]:grid-cols-2 md:grid-cols-3 lg:mt-5 lg:grid-cols-4 lg:gap-5">
        <div v-for="index in 4" :key="index" class="h-[270px] animate-pulse rounded-xl border border-line bg-mist sm:h-[330px]" />
      </div>

      <div v-else-if="products.length" class="mt-3 grid grid-cols-1 gap-3 min-[340px]:grid-cols-2 md:grid-cols-3 lg:mt-5 lg:grid-cols-4 lg:gap-5">
        <article
          v-for="product in products"
          :key="product.id"
          class="group flex min-w-0 flex-col overflow-hidden rounded-xl border border-line bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-panel"
        >
          <RouterLink :to="`/products/${product.slug}`" class="relative block aspect-square overflow-hidden bg-white sm:aspect-[4/3]">
            <img
              :src="optimizedImageUrl(product.image_url, 'mobile') || fallbackUrl"
              :alt="product.name"
              class="h-full w-full object-contain p-3 transition duration-500 group-hover:scale-105 sm:p-5"
              loading="lazy"
              decoding="async"
              sizes="(max-width: 340px) 100vw, (max-width: 767px) 50vw, (max-width: 1023px) 33vw, 25vw"
            />
            <span v-if="product.badge" class="absolute left-2 top-2 rounded-full bg-energy px-2.5 py-1 text-[11px] font-bold leading-none text-white sm:left-3 sm:top-3 sm:text-xs">
              {{ product.badge }}
            </span>
          </RouterLink>

          <div class="flex min-w-0 flex-1 flex-col p-3 min-[390px]:p-3.5 sm:p-5">
            <p v-if="product.badge" class="mb-2 text-xs font-medium leading-none text-red-600 sm:hidden">{{ product.badge }}</p>
            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500 sm:text-xs">{{ product.category || sectionTitle }}</p>
            <h3 class="mt-1.5 line-clamp-2 text-sm font-black leading-5 text-ink min-[390px]:text-[15px] sm:mt-2 sm:text-lg sm:leading-6">{{ product.name }}</h3>
            <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-600 min-[390px]:text-[13px] sm:mt-2 sm:text-sm sm:leading-6">{{ product.short_description || product.description }}</p>

            <div class="mt-auto flex items-end justify-between gap-2 pt-4 sm:gap-3 sm:pt-5">
              <div class="min-w-0">
                <p class="text-sm font-black text-ink min-[390px]:text-[15px] sm:text-xl">{{ money(product.price) }}</p>
                <p v-if="product.old_price" class="text-xs text-slate-400 line-through sm:text-sm">{{ money(product.old_price) }}</p>
              </div>
              <RouterLink
                :to="`/products/${product.slug}`"
                class="grid h-9 w-9 shrink-0 place-items-center rounded-md border border-line text-ink transition hover:border-ink sm:h-10 sm:w-10"
                aria-label="View product"
              >
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

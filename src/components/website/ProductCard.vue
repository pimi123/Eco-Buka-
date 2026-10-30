<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
import ProductPrice from './ProductPrice.vue';
import { discountPercent } from '../../lib/discount';
import type { Product } from '../../types/product';

const fallbackUrl = '/promo/optimized/summer-sale-1280.jpg';

const {
  product,
  showSpecs = true,
  compact = false,
  variant = 'default',
} = defineProps<{
  product: Product;
  showSpecs?: boolean;
  compact?: boolean;
  variant?: 'default' | 'showcase';
}>();

const discount = computed(() => discountPercent(product.price, product.old_price));
const imageUrl = computed(() => product.image_url || product.main_image_url || fallbackUrl);
const isInStock = computed(() => {
  const stockValue = product.in_stock as unknown;
  return stockValue !== false && stockValue !== 0 && stockValue !== '0';
});
</script>

<template>
  <RouterLink
    :to="`/products/${product.slug}`"
    class="group relative flex h-full min-w-0 cursor-pointer flex-col overflow-hidden rounded-xl bg-white transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-energy/25 sm:rounded-lg"
    :class="[
      variant === 'showcase' ? 'border border-transparent shadow-sm hover:shadow-panel' : 'border border-line shadow-sm hover:-translate-y-1 hover:shadow-panel',
      !isInStock ? 'grayscale-[35%]' : '',
    ]"
    :aria-label="`Shiko ${product.name}`"
  >
    <div
      class="relative block overflow-hidden"
      :class="variant === 'showcase' ? 'h-44 bg-white sm:h-52 lg:h-56' : compact ? 'aspect-[5/4] bg-mist' : 'aspect-[4/3] bg-mist'"
    >
      <img
        :src="imageUrl"
        :alt="product.name"
        class="h-full w-full transition duration-500 group-hover:scale-105"
        :class="variant === 'showcase' ? 'object-contain p-5 sm:p-7' : 'object-contain p-3 sm:p-5'"
        loading="lazy"
        decoding="async"
        sizes="(max-width: 340px) 100vw, (max-width: 767px) 50vw, (max-width: 1023px) 33vw, 25vw"
      />
      <span v-if="discount && isInStock" class="absolute right-2 top-2 rounded-full bg-red-700 px-2.5 py-1.5 text-xs font-black leading-none text-white shadow-sm sm:right-3 sm:top-3" :aria-label="`Zbritje ${discount}%`">−{{ discount }}%</span>
      <span v-if="product.badge && variant !== 'showcase' && isInStock" class="absolute left-2 top-2 rounded-full bg-energy px-2.5 py-1 text-[11px] font-bold leading-none text-white sm:left-3 sm:top-3 sm:px-3 sm:text-xs">{{ product.badge }}</span>
      <div v-if="!isInStock" class="absolute inset-0 bg-slate-950/25" aria-hidden="true"></div>
      <span v-if="!isInStock" class="absolute left-2 top-2 rounded-full bg-slate-950 px-3 py-1 text-[11px] font-black uppercase tracking-wide text-white shadow-sm sm:left-3 sm:top-3">
        Nuk është në stok
      </span>
    </div>
    <div class="flex min-w-0 flex-1 flex-col p-3 min-[390px]:p-3.5 sm:p-5" :class="variant === 'showcase' ? 'pt-0 sm:pt-0' : ''">
      <p v-if="product.badge && variant === 'showcase' && isInStock" class="mb-3 text-sm font-medium leading-none text-red-700">{{ product.badge }}</p>
      <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500 sm:text-xs">{{ product.category || 'Zgjidhje energjie' }}</p>
      <h3 class="mt-1.5 line-clamp-2 font-bold text-ink" :class="variant === 'showcase' ? 'text-base leading-6 sm:min-h-12 sm:text-lg' : 'text-sm leading-5 min-[390px]:text-[15px] sm:mt-2 sm:text-lg sm:leading-6'">{{ product.name }}</h3>
      <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-600 min-[390px]:text-[13px] sm:mt-2 sm:text-sm sm:leading-6">{{ product.short_description }}</p>
      <div v-if="showSpecs" class="mt-3 hidden flex-wrap gap-2 sm:flex">
        <span v-for="(value, key) in product.specs" :key="key" class="rounded-md bg-mist px-2 py-1 text-xs font-semibold text-slate-600">{{ key }}: {{ value }}</span>
      </div>
      <div class="mt-auto flex items-end justify-between gap-2 pt-4 sm:gap-3 sm:pt-5">
        <div class="min-w-0">
          <ProductPrice :price="product.price" :old-price="product.old_price" :in-stock="isInStock" />
          <p v-if="!isInStock" class="mt-1 text-xs font-bold text-slate-500">Për momentin i padisponueshëm</p>
        </div>
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md border border-line transition group-hover:border-ink sm:h-10 sm:w-10" aria-hidden="true">
          <ArrowRight class="h-4 w-4" />
        </span>
      </div>
    </div>
  </RouterLink>
</template>

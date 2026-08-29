<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed } from 'vue';
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

const money = (value?: number | null) => (value ? new Intl.NumberFormat('en-EU', { style: 'currency', currency: 'EUR' }).format(value) : 'Request price');
const imageUrl = computed(() => product.image_url || product.main_image_url || fallbackUrl);
const isInStock = computed(() => product.in_stock !== false);
</script>

<template>
  <RouterLink
    :to="`/products/${product.slug}`"
    class="group relative flex h-full min-w-0 cursor-pointer flex-col overflow-hidden rounded-xl bg-white transition focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-energy/25 sm:rounded-lg"
    :class="[
      variant === 'showcase' ? 'border border-transparent shadow-sm hover:shadow-panel' : 'border border-line shadow-sm hover:-translate-y-1 hover:shadow-panel',
      !isInStock ? 'grayscale-[35%]' : '',
    ]"
    :aria-label="`View ${product.name}`"
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
      <span v-if="product.badge && variant !== 'showcase' && isInStock" class="absolute left-2 top-2 rounded-full bg-energy px-2.5 py-1 text-[11px] font-bold leading-none text-white sm:left-3 sm:top-3 sm:px-3 sm:text-xs">{{ product.badge }}</span>
      <div v-if="!isInStock" class="absolute inset-0 bg-slate-950/25" aria-hidden="true"></div>
      <span v-if="!isInStock" class="absolute left-2 top-2 rounded-full bg-slate-950 px-3 py-1 text-[11px] font-black uppercase tracking-wide text-white shadow-sm sm:left-3 sm:top-3">
        Out of stock
      </span>
    </div>
    <div class="flex min-w-0 flex-1 flex-col p-3 min-[390px]:p-3.5 sm:p-5" :class="variant === 'showcase' ? 'pt-0 sm:pt-0' : ''">
      <p v-if="product.badge && variant === 'showcase' && isInStock" class="mb-3 text-sm font-medium leading-none text-red-600">{{ product.badge }}</p>
      <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500 sm:text-xs">{{ product.category || 'Energy solution' }}</p>
      <h3 class="mt-1.5 line-clamp-2 font-bold text-ink" :class="variant === 'showcase' ? 'text-base leading-6 sm:min-h-12 sm:text-lg' : 'text-sm leading-5 min-[390px]:text-[15px] sm:mt-2 sm:text-lg sm:leading-6'">{{ product.name }}</h3>
      <p class="mt-1.5 line-clamp-2 text-xs leading-5 text-slate-600 min-[390px]:text-[13px] sm:mt-2 sm:text-sm sm:leading-6">{{ product.short_description }}</p>
      <div v-if="showSpecs" class="mt-3 hidden flex-wrap gap-2 sm:flex">
        <span v-for="(value, key) in product.specs" :key="key" class="rounded-md bg-mist px-2 py-1 text-xs font-semibold text-slate-600">{{ key }}: {{ value }}</span>
      </div>
      <div class="mt-auto flex items-end justify-between gap-2 pt-4 sm:gap-3 sm:pt-5">
        <div class="min-w-0">
          <p class="font-black text-ink" :class="variant === 'showcase' ? 'text-xl sm:text-2xl' : 'text-sm min-[390px]:text-[15px] sm:text-xl'">{{ money(product.price) }}</p>
          <p v-if="product.old_price" class="text-xs text-slate-400 line-through sm:text-sm">{{ money(product.old_price) }}</p>
          <p v-if="!isInStock" class="mt-1 text-xs font-bold text-slate-500">Currently unavailable</p>
        </div>
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-md border border-line transition group-hover:border-ink sm:h-10 sm:w-10" aria-hidden="true">
          <ArrowRight class="h-4 w-4" />
        </span>
      </div>
    </div>
  </RouterLink>
</template>

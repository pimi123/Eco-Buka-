<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import type { Product } from '../../types/product';
import fallbackUrl from '../../assets/eco-buka-hero.png';

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
</script>

<template>
  <article
    class="group flex h-full min-w-0 flex-col overflow-hidden rounded-lg bg-white transition"
    :class="variant === 'showcase' ? 'border border-transparent shadow-sm hover:shadow-panel' : 'border border-line shadow-sm hover:-translate-y-1 hover:shadow-panel'"
  >
    <RouterLink
      :to="`/products/${product.slug}`"
      class="relative block overflow-hidden"
      :class="variant === 'showcase' ? 'h-48 bg-white sm:h-52 lg:h-56' : compact ? 'aspect-[5/4] bg-mist' : 'aspect-[4/3] bg-mist'"
    >
      <img
        :src="product.image_url || fallbackUrl"
        :alt="product.name"
        class="h-full w-full transition duration-500 group-hover:scale-105"
        :class="variant === 'showcase' ? 'object-contain p-6 sm:p-7' : 'object-cover'"
      />
      <span v-if="product.badge && variant !== 'showcase'" class="absolute left-3 top-3 rounded-full bg-energy px-3 py-1 text-xs font-bold text-white">{{ product.badge }}</span>
    </RouterLink>
    <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-5" :class="variant === 'showcase' ? 'pt-0' : ''">
      <p v-if="product.badge && variant === 'showcase'" class="mb-3 text-sm font-medium leading-none text-red-600">{{ product.badge }}</p>
      <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ product.category || 'Energy solution' }}</p>
      <h3 class="mt-2 font-bold text-ink" :class="variant === 'showcase' ? 'min-h-12 text-base leading-6 sm:text-lg' : 'text-base leading-6 sm:text-lg'">{{ product.name }}</h3>
      <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">{{ product.short_description }}</p>
      <div v-if="showSpecs" class="mt-4 flex flex-wrap gap-2">
        <span v-for="(value, key) in product.specs" :key="key" class="rounded-md bg-mist px-2 py-1 text-xs font-semibold text-slate-600">{{ key }}: {{ value }}</span>
      </div>
      <div class="mt-auto flex items-end justify-between gap-3 pt-5">
        <div class="min-w-0">
          <p class="font-black text-ink" :class="variant === 'showcase' ? 'text-xl sm:text-2xl' : 'text-lg sm:text-xl'">{{ money(product.price) }}</p>
          <p v-if="product.old_price" class="text-sm text-slate-400 line-through">{{ money(product.old_price) }}</p>
        </div>
        <RouterLink :to="`/products/${product.slug}`" class="grid h-9 w-9 shrink-0 place-items-center rounded-md border border-line transition hover:border-ink" aria-label="View details">
          <ArrowRight class="h-4 w-4" />
        </RouterLink>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import type { Category } from '../../types/category';
import fallbackUrl from '../../assets/eco-buka-hero.png';

defineProps<{ category: Category }>();
</script>

<template>
  <RouterLink :to="`/category/${category.slug}`" class="group min-w-0 overflow-hidden rounded-lg border border-line bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-panel">
    <div class="aspect-[4/3] overflow-hidden bg-mist">
      <img
        :src="category.image_url || fallbackUrl"
        :alt="category.name"
        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
        loading="lazy"
        decoding="async"
        sizes="(max-width: 767px) 100vw, (max-width: 1023px) 50vw, 33vw"
      />
    </div>
    <div class="p-4 sm:p-5">
      <h3 class="text-base font-bold leading-6 sm:text-lg">{{ category.name }}</h3>
      <p class="mt-2 text-sm leading-6 text-slate-600 sm:min-h-12">{{ category.description }}</p>
      <span class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-energy">View Products <ArrowRight class="h-4 w-4" /></span>
    </div>
  </RouterLink>
</template>

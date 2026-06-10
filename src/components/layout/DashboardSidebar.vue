<script setup lang="ts">
import { Boxes, GalleryVerticalEnd, Gauge, Globe2, Layers3, Settings } from 'lucide-vue-next';

const items = [
  { label: 'Overview', to: '/dashboard', icon: Gauge },
  { label: 'Homepage Showcase', to: '/dashboard/homepage-showcase', icon: GalleryVerticalEnd },
  { label: 'Products', to: '/dashboard/products', icon: Boxes },
  { label: 'Categories', to: '/dashboard/categories', icon: Layers3 },
  { label: 'Settings', to: '/dashboard/settings', icon: Settings },
  { label: 'Back to Website', to: '/', icon: Globe2 },
];
</script>

<template>
  <aside class="border-b border-line bg-white p-3 lg:min-h-screen lg:border-b-0 lg:border-r lg:p-4">
    <RouterLink to="/" class="block px-1 text-lg font-black sm:px-2 sm:text-xl">Eco Buka</RouterLink>
    <nav class="mt-3 flex snap-x gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:mt-8 lg:grid lg:gap-1 lg:overflow-visible lg:pb-0">
      <RouterLink v-for="item in items" :key="item.label" :to="item.to" class="flex min-h-10 shrink-0 snap-start items-center gap-2 rounded-md px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-mist hover:text-ink lg:gap-3">
        <component :is="item.icon" class="h-4 w-4 shrink-0" /> <span class="whitespace-nowrap">{{ item.label }}</span>
      </RouterLink>
    </nav>
  </aside>
</template>

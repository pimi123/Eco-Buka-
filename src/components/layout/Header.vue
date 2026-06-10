<script setup lang="ts">
import { Menu, Search, ShoppingBag, UserCog, X } from 'lucide-vue-next';
import { ref } from 'vue';

const open = ref(false);
const nav = [
  ['Power Stations', '/category/power-stations'],
  ['Solar Panels', '/category/solar-panels'],
  ['Solar Generators', '/category/solar-generators'],
  ['Smart Devices', '/category/smart-devices'],
  ['Accessories', '/category/accessories'],
  ['Solutions', '/category/solutions'],
];
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur">
    <div class="bg-ink px-4 py-1.5 text-center text-[11px] font-medium leading-4 text-white sm:py-2 sm:text-xs">
      Official energy solutions for homes, businesses and outdoor power
    </div>
    <div class="container-shell flex min-h-14 items-center justify-between gap-2 py-2.5 sm:min-h-16 sm:gap-4 sm:py-3">
      <RouterLink to="/" class="flex min-h-10 shrink-0 items-center text-lg font-black tracking-tight sm:text-xl" @click="open = false">Eco Buka</RouterLink>
      <nav class="hidden items-center gap-5 text-sm font-semibold xl:flex 2xl:gap-6">
        <RouterLink v-for="[label, to] in nav" :key="label" :to="to" class="hover:text-energy">{{ label }}</RouterLink>
      </nav>
      <div class="hidden min-w-0 flex-1 justify-end lg:flex">
        <RouterLink to="/search" class="flex h-10 w-full max-w-[220px] items-center gap-2 rounded-md border border-line px-3 text-sm text-slate-500 2xl:max-w-xs">
          <Search class="h-4 w-4" /> Search products
        </RouterLink>
      </div>
      <div class="flex items-center gap-2">
        <RouterLink to="/dashboard" class="grid h-10 w-10 place-items-center rounded-md hover:bg-mist" aria-label="Dashboard" @click="open = false"><UserCog class="h-5 w-5" /></RouterLink>
        <button class="grid h-10 w-10 place-items-center rounded-md hover:bg-mist" aria-label="Cart"><ShoppingBag class="h-5 w-5" /></button>
        <button class="grid h-10 w-10 place-items-center rounded-md hover:bg-mist xl:hidden" aria-label="Menu" @click="open = !open">
          <X v-if="open" class="h-5 w-5" /><Menu v-else class="h-5 w-5" />
        </button>
      </div>
    </div>
    <div v-if="open" class="absolute left-0 right-0 top-full border-t border-line bg-white shadow-panel xl:hidden">
      <div class="container-shell grid max-h-[calc(100vh-6.25rem)] gap-1 overflow-y-auto py-4">
        <RouterLink to="/search" class="mb-2 flex min-h-11 items-center gap-2 rounded-md border border-line px-3 text-sm text-slate-500" @click="open = false">
          <Search class="h-4 w-4" /> Search products
        </RouterLink>
        <RouterLink v-for="[label, to] in nav" :key="label" :to="to" class="rounded-md px-3 py-3 text-base font-semibold hover:bg-mist sm:text-sm" @click="open = false">{{ label }}</RouterLink>
      </div>
    </div>
  </header>
</template>

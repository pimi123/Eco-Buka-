<script setup lang="ts">
import { Menu, Search, ShoppingBag, UserCog, X } from 'lucide-vue-next';
import { ref } from 'vue';

const open = ref(false);
const nav = [
  ['Power Stations', '/categories/power-stations'],
  ['Solar Panels', '/categories/solar-panels'],
  ['Solar Generators', '/categories/solar-generators'],
  ['Smart Devices', '/categories/home-backup'],
  ['Accessories', '/categories/accessories'],
  ['Solutions', '/about'],
];
</script>

<template>
  <header class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur">
    <div class="bg-ink px-3 py-2 text-center text-[11px] font-medium leading-4 text-white sm:text-xs">
      Official energy solutions for homes, businesses and outdoor power
    </div>
    <div class="container-shell flex min-h-16 items-center justify-between gap-2 py-3 sm:gap-4">
      <RouterLink to="/" class="shrink-0 text-lg font-black tracking-tight sm:text-xl">Eco Buka</RouterLink>
      <nav class="hidden items-center gap-5 text-sm font-semibold xl:flex 2xl:gap-6">
        <RouterLink v-for="[label, to] in nav" :key="label" :to="to" class="hover:text-energy">{{ label }}</RouterLink>
      </nav>
      <div class="hidden min-w-0 flex-1 justify-end lg:flex">
        <RouterLink to="/search" class="flex h-10 w-full max-w-[220px] items-center gap-2 rounded-md border border-line px-3 text-sm text-slate-500 2xl:max-w-xs">
          <Search class="h-4 w-4" /> Search products
        </RouterLink>
      </div>
      <div class="flex items-center gap-2">
        <RouterLink to="/dashboard" class="rounded-md p-2 hover:bg-mist" aria-label="Dashboard"><UserCog class="h-5 w-5" /></RouterLink>
        <button class="rounded-md p-2 hover:bg-mist" aria-label="Cart"><ShoppingBag class="h-5 w-5" /></button>
        <button class="rounded-md p-2 hover:bg-mist xl:hidden" aria-label="Menu" @click="open = !open">
          <X v-if="open" class="h-5 w-5" /><Menu v-else class="h-5 w-5" />
        </button>
      </div>
    </div>
    <div v-if="open" class="border-t border-line bg-white xl:hidden">
      <div class="container-shell grid gap-1 py-4">
        <RouterLink to="/search" class="mb-2 flex h-10 items-center gap-2 rounded-md border border-line px-3 text-sm text-slate-500" @click="open = false">
          <Search class="h-4 w-4" /> Search products
        </RouterLink>
        <RouterLink v-for="[label, to] in nav" :key="label" :to="to" class="rounded-md px-2 py-3 text-sm font-semibold hover:bg-mist" @click="open = false">{{ label }}</RouterLink>
      </div>
    </div>
  </header>
</template>

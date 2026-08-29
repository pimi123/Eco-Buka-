<script setup lang="ts">
import { Menu, Search, ShoppingBag, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useCartStore } from '../../stores/cartStore';
import { useCollectionStore } from '../../stores/collectionStore';

const open = ref(false);
const headerRef = ref<HTMLElement | null>(null);
const cartStore = useCartStore();
const collectionStore = useCollectionStore();

const fallbackNav = [
  ['Power Stations', '/category/power-stations'],
  ['Solar Panels', '/category/solar-panels'],
  ['Solar Generators', '/category/solar-generators'],
  ['Smart Devices', '/category/smart-devices'],
  ['Accessories', '/category/accessories'],
  ['Solutions', '/category/solutions'],
];

const nav = computed(() => {
  const solutions = collectionStore.solutionCollections.slice(0, 4).map((collection) => [collection.name, `/collections/${collection.slug}`]);
  return solutions.length ? [...solutions, ['Power Stations', '/category/power-stations'], ['Accessories', '/category/accessories']] : fallbackNav;
});

const closeMenu = () => {
  open.value = false;
};

const handlePointerDown = (event: PointerEvent) => {
  if (!open.value || headerRef.value?.contains(event.target as Node)) {
    return;
  }

  closeMenu();
};

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape') {
    closeMenu();
  }
};

onMounted(() => {
  collectionStore.fetchCollections();
  document.addEventListener('pointerdown', handlePointerDown);
  document.addEventListener('keydown', handleKeydown);
  window.addEventListener('scroll', closeMenu, { passive: true });
  window.addEventListener('wheel', closeMenu, { capture: true, passive: true });
  window.addEventListener('touchmove', closeMenu, { capture: true, passive: true });
});

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', handlePointerDown);
  document.removeEventListener('keydown', handleKeydown);
  window.removeEventListener('scroll', closeMenu);
  window.removeEventListener('wheel', closeMenu, { capture: true });
  window.removeEventListener('touchmove', closeMenu, { capture: true });
});
</script>

<template>
  <header ref="headerRef" class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur">
    <div class="bg-ink px-4 py-1.5 text-center text-[11px] font-medium leading-4 text-white sm:py-2 sm:text-xs">
      Official energy solutions for homes, businesses and outdoor power
    </div>
    <div class="container-shell flex min-h-14 items-center justify-between gap-2 py-2.5 sm:min-h-16 sm:gap-4 sm:py-3">
      <RouterLink to="/" class="flex min-h-10 shrink-0 items-center text-lg font-black tracking-tight sm:text-xl" @click="open = false">Eco Buka</RouterLink>
      <nav class="hidden items-center gap-5 text-sm font-semibold xl:flex 2xl:gap-6">
        <RouterLink v-for="[label, to] in nav" :key="label" :to="to" class="hover:text-energy">{{ label }}</RouterLink>
      </nav>
      <div class="hidden min-w-0 flex-1 justify-end lg:flex">
        <RouterLink to="/search">
          <Search class="h-4 w-4" />
        </RouterLink>
      </div>
      <div class="flex items-center gap-2">
        <RouterLink to="/cart" class="relative grid h-10 w-10 place-items-center rounded-md hover:bg-mist" aria-label="Cart">
          <ShoppingBag class="h-5 w-5" />
          <span v-if="cartStore.count" class="absolute right-0 top-0 grid h-5 min-w-5 place-items-center rounded-full bg-energy px-1 text-[11px] font-black leading-none text-white">{{ cartStore.count }}</span>
        </RouterLink>
        <button class="grid h-10 w-10 place-items-center rounded-md hover:bg-mist xl:hidden" aria-label="Menu" @click="open = !open">
          <X v-if="open" class="h-5 w-5" /><Menu v-else class="h-5 w-5" />
        </button>
      </div>
    </div>
    <div v-if="open" class="absolute left-0 right-0 top-full border-t border-line bg-white shadow-panel xl:hidden">
      <div class="container-shell grid max-h-[calc(100vh-6.25rem)] gap-1 overflow-y-auto py-4">
        <RouterLink to="/search" class="mb-2 px-3 flex min-h-11 items-center gap-2 rounded-md text-slate-500" @click="open = false">
          <Search class="h-4 w-4" />
        </RouterLink>
        <RouterLink v-for="[label, to] in nav" :key="label" :to="to" class="rounded-md px-3 py-3 text-base font-semibold hover:bg-mist sm:text-sm" @click="open = false">{{ label }}</RouterLink>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { Category } from '../../types/category';

const props = defineProps<{ categories: Category[] }>();

const carouselRef = ref<HTMLElement | null>(null);

const carouselCategories = computed(() =>
  props.categories.map((category) => ({
    id: category.id,
    title: category.name,
    image: category.image_url,
    isNew: Boolean(category.is_new),
    slug: category.slug,
  })),
);

function scrollByDirection(direction: 'left' | 'right') {
  carouselRef.value?.scrollBy({
    left: direction === 'left' ? -360 : 360,
    behavior: 'smooth',
  });
}
</script>

<template>
  <section class="w-full border-y border-slate-100 bg-white">
    <div class="relative mx-auto flex h-[128px] w-full max-w-[1680px] items-center px-12 sm:h-[158px] sm:px-14 lg:h-[176px] lg:px-20">
      <button
        class="absolute left-2 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-[#f2f2f2] text-slate-400 transition hover:bg-slate-200 hover:text-slate-600 sm:left-3 sm:h-12 sm:w-12"
        type="button"
        aria-label="Scroll categories left"
        @click="scrollByDirection('left')"
      >
        <ChevronLeft class="h-5 w-5" />
      </button>

      <div
        ref="carouselRef"
        class="flex w-full snap-x items-center gap-3 overflow-x-auto scroll-smooth px-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden sm:gap-3 md:justify-start xl:justify-between"
      >
        <RouterLink
          v-for="category in carouselCategories"
          :key="category.id"
          :to="`/category/${category.slug}`"
          class="group flex min-w-[96px] snap-start flex-col items-center justify-start text-center outline-none min-[390px]:min-w-[108px] sm:min-w-[140px] lg:min-w-[154px]"
        >
          <div class="flex h-[52px] w-full items-end justify-center sm:h-[74px]">
            <img
              v-if="category.image"
              :src="category.image"
              :alt="category.title"
              class="max-h-[48px] w-auto max-w-[58px] object-contain transition duration-300 group-hover:scale-105 sm:max-h-[66px] sm:max-w-[78px]"
            />
          </div>
          <p class="mt-2 max-w-[108px] text-[11px] font-semibold leading-4 text-slate-800 transition group-hover:text-black sm:max-w-[145px] sm:text-[13px] sm:leading-[17px]">
            {{ category.title }}
          </p>
          <p v-if="category.isNew" class="mt-1 text-xs font-bold leading-none text-red-600">New</p>
        </RouterLink>
      </div>

      <button
        class="absolute right-2 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-[#d1d1d1] text-slate-700 transition hover:bg-slate-400 hover:text-white sm:right-3 sm:h-12 sm:w-12"
        type="button"
        aria-label="Scroll categories right"
        @click="scrollByDirection('right')"
      >
        <ChevronRight class="h-5 w-5" />
      </button>
    </div>
  </section>
</template>

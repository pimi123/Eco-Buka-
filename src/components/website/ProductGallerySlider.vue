<script setup lang="ts">
import { ChevronLeft, ChevronRight, ImageIcon } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import fallbackUrl from '../../assets/eco-buka-hero.png';

const props = defineProps<{
  title: string;
  mainImage?: string | null;
  gallery?: string[];
}>();

const activeIndex = ref(0);

const images = computed(() => {
  const sources = [props.mainImage, ...(props.gallery || [])].filter(Boolean) as string[];
  return Array.from(new Set(sources));
});

const activeImage = computed(() => images.value[activeIndex.value] || fallbackUrl);
const hasMultipleImages = computed(() => images.value.length > 1);

function showPrevious() {
  if (!hasMultipleImages.value) return;
  activeIndex.value = activeIndex.value === 0 ? images.value.length - 1 : activeIndex.value - 1;
}

function showNext() {
  if (!hasMultipleImages.value) return;
  activeIndex.value = activeIndex.value === images.value.length - 1 ? 0 : activeIndex.value + 1;
}

watch(images, () => {
  activeIndex.value = 0;
});
</script>

<template>
  <div class="min-w-0">
    <div class="group relative overflow-hidden rounded-lg border border-line bg-mist">
      <img :src="activeImage" :alt="title" class="aspect-[4/3] w-full object-contain p-3 sm:p-6" />

      <div v-if="!images.length" class="absolute inset-0 grid place-items-center text-slate-400">
        <ImageIcon class="h-10 w-10" />
      </div>

      <button
        v-if="hasMultipleImages"
        type="button"
        class="absolute left-2 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink shadow-sm transition hover:bg-white sm:left-3 sm:h-11 sm:w-11"
        aria-label="Previous product image"
        @click="showPrevious"
      >
        <ChevronLeft class="h-5 w-5" />
      </button>

      <button
        v-if="hasMultipleImages"
        type="button"
        class="absolute right-2 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-ink shadow-sm transition hover:bg-white sm:right-3 sm:h-11 sm:w-11"
        aria-label="Next product image"
        @click="showNext"
      >
        <ChevronRight class="h-5 w-5" />
      </button>

      <div
        v-if="hasMultipleImages"
        class="absolute bottom-3 left-1/2 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-slate-700 shadow-sm"
      >
        {{ activeIndex + 1 }} / {{ images.length }}
      </div>
    </div>

    <div
      v-if="hasMultipleImages"
      class="mt-3 flex gap-3 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
    >
      <button
        v-for="(image, index) in images"
        :key="image"
        type="button"
        class="h-16 w-20 shrink-0 overflow-hidden rounded-md border bg-mist transition sm:h-24 sm:w-28"
        :class="index === activeIndex ? 'border-ink ring-2 ring-ink/10' : 'border-line hover:border-slate-400'"
        :aria-label="`Show product image ${index + 1}`"
        @click="activeIndex = index"
      >
        <img :src="image" :alt="`${title} image ${index + 1}`" class="h-full w-full object-contain p-2" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ChevronLeft, ChevronRight, ImageIcon } from 'lucide-vue-next';
import { computed, nextTick, ref, watch } from 'vue';

const fallbackUrl = '/promo/optimized/summer-sale-1280.jpg';

const props = defineProps<{
  title: string;
  mainImage?: string | null;
  gallery?: string[];
}>();

const activeIndex = ref(0);
const mobileTrack = ref<HTMLElement | null>(null);
let scrollFrame = 0;

const images = computed(() => {
  const sources = [props.mainImage, ...(props.gallery || [])].filter(Boolean) as string[];
  return Array.from(new Set(sources));
});

const activeImage = computed(() => images.value[activeIndex.value] || fallbackUrl);
const hasMultipleImages = computed(() => images.value.length > 1);

function clampIndex(index: number) {
  if (!images.value.length) return 0;
  return Math.min(Math.max(index, 0), images.value.length - 1);
}

function updateActiveIndex(index: number, behavior: ScrollBehavior = 'smooth') {
  activeIndex.value = clampIndex(index);

  nextTick(() => {
    const track = mobileTrack.value;
    if (!track) return;

    track.scrollTo({
      left: activeIndex.value * track.clientWidth,
      behavior,
    });
  });
}

function showPrevious() {
  if (!hasMultipleImages.value) return;
  updateActiveIndex(activeIndex.value === 0 ? images.value.length - 1 : activeIndex.value - 1);
}

function showNext() {
  if (!hasMultipleImages.value) return;
  updateActiveIndex(activeIndex.value === images.value.length - 1 ? 0 : activeIndex.value + 1);
}

function syncActiveFromScroll() {
  const track = mobileTrack.value;
  if (!track || !track.clientWidth) return;

  if (scrollFrame) window.cancelAnimationFrame(scrollFrame);
  scrollFrame = window.requestAnimationFrame(() => {
    activeIndex.value = clampIndex(Math.round(track.scrollLeft / track.clientWidth));
  });
}

function selectImage(index: number) {
  updateActiveIndex(index);
}

function handleKeydown(event: KeyboardEvent) {
  if (!hasMultipleImages.value) return;
  if (event.key === 'ArrowLeft') {
    event.preventDefault();
    showPrevious();
  }
  if (event.key === 'ArrowRight') {
    event.preventDefault();
    showNext();
  }
}

function handleImageError(event: Event) {
  const image = event.currentTarget as HTMLImageElement;
  if (image.src.endsWith(fallbackUrl)) return;
  image.src = fallbackUrl;
}

watch(images, () => {
  updateActiveIndex(0, 'auto');
});
</script>

<template>
  <div class="min-w-0">
    <div
      class="group relative overflow-hidden rounded-lg border border-line bg-mist focus-within:ring-4 focus-within:ring-energy/20"
      tabindex="0"
      role="region"
      :aria-label="`${title} image gallery`"
      @keydown="handleKeydown"
    >
      <div
        ref="mobileTrack"
        class="flex aspect-[4/3] snap-x snap-mandatory overflow-x-auto scroll-smooth [-ms-overflow-style:none] [overscroll-behavior-x:contain] [scrollbar-width:none] sm:hidden [&::-webkit-scrollbar]:hidden"
        @scroll.passive="syncActiveFromScroll"
      >
        <div
          v-for="(image, index) in images.length ? images : [fallbackUrl]"
          :key="`${image}-${index}`"
          class="grid h-full w-full shrink-0 snap-center place-items-center bg-mist"
          :aria-hidden="index !== activeIndex"
        >
          <img
            :src="image"
            :alt="`${title} image ${index + 1}`"
            class="h-full w-full object-contain p-3"
            :loading="index === 0 ? 'eager' : 'lazy'"
            :fetchpriority="index === 0 ? 'high' : 'auto'"
            decoding="async"
            sizes="100vw"
            @error="handleImageError"
          />
        </div>
      </div>

      <img
        :src="activeImage"
        :alt="title"
        class="hidden aspect-[4/3] w-full object-contain p-3 sm:block sm:p-6"
        loading="eager"
        fetchpriority="high"
        decoding="async"
        sizes="(max-width: 1023px) 100vw, 52vw"
        @error="handleImageError"
      />

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
        class="absolute bottom-3 left-1/2 -translate-x-1/2 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-slate-700 shadow-sm"
        aria-live="polite"
      >
        {{ activeIndex + 1 }} / {{ images.length }}
      </div>

      <div v-if="hasMultipleImages" class="absolute bottom-3 right-3 flex gap-1.5 sm:hidden">
        <button
          v-for="(_, index) in images"
          :key="`dot-${index}`"
          type="button"
          class="h-2 rounded-full transition"
          :class="index === activeIndex ? 'w-5 bg-ink' : 'w-2 bg-white/80'"
          :aria-label="`Show product image ${index + 1}`"
          :aria-current="index === activeIndex ? 'true' : undefined"
          @click="selectImage(index)"
        />
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
        :aria-current="index === activeIndex ? 'true' : undefined"
        @click="selectImage(index)"
      >
        <img
          :src="image"
          :alt="`${title} image ${index + 1}`"
          class="h-full w-full object-contain p-2"
          loading="lazy"
          decoding="async"
          sizes="112px"
          @error="handleImageError"
        />
      </button>
    </div>
  </div>
</template>

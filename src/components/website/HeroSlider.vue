<script setup lang="ts">
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import deltaHeroUrl from '../../assets/heroes/hero-delta-max.png';
import portableHeroUrl from '../../assets/heroes/hero-portable-energy.png';
import solarHeroUrl from '../../assets/heroes/hero-solar-home.png';

interface HeroSlide {
  id: string;
  eyebrow: string;
  title: string;
  subtitle: string;
  primaryButtonText: string;
  primaryButtonLink: string;
  secondaryButtonText?: string;
  secondaryButtonLink?: string;
  image: string;
  imageAlt: string;
  overlayStyle: string;
  textColor: string;
  alignment: 'left' | 'center';
}

const slides: HeroSlide[] = [
  {
    id: 'delta-max',
    eyebrow: 'New Release',
    title: 'DELTA 3 Max Series Available Now!',
    subtitle: '2kWh Capacity | 3000W Max Output',
    primaryButtonText: 'Learn More',
    primaryButtonLink: '/products',
    secondaryButtonText: 'Shop Now',
    secondaryButtonLink: '/products',
    image: deltaHeroUrl,
    imageAlt: 'Premium portable power station for Eco Buka DELTA style hero banner',
    overlayStyle: 'bg-gradient-to-r from-black/78 via-black/40 to-black/10',
    textColor: 'text-white',
    alignment: 'left',
  },
  {
    id: 'smart-solar',
    eyebrow: 'Smart Solar Solutions',
    title: 'Power Your Home with Clean Energy',
    subtitle: 'Reliable backup, solar charging, and energy independence.',
    primaryButtonText: 'Explore Solutions',
    primaryButtonLink: '/about',
    secondaryButtonText: 'View Products',
    secondaryButtonLink: '/products',
    image: solarHeroUrl,
    imageAlt: 'Modern home solar energy system for Eco Buka hero banner',
    overlayStyle: 'bg-gradient-to-r from-ink/82 via-ink/45 to-ink/5',
    textColor: 'text-white',
    alignment: 'left',
  },
  {
    id: 'portable-energy',
    eyebrow: 'Portable Energy',
    title: 'Power Anywhere You Go',
    subtitle: 'Compact, powerful, and ready for home, travel, and outdoor use.',
    primaryButtonText: 'Discover More',
    primaryButtonLink: '/categories/river-series',
    secondaryButtonText: 'Browse Products',
    secondaryButtonLink: '/products',
    image: portableHeroUrl,
    imageAlt: 'Portable power and solar kit for Eco Buka outdoor energy banner',
    overlayStyle: 'bg-gradient-to-r from-black/72 via-black/38 to-black/5',
    textColor: 'text-white',
    alignment: 'left',
  },
];

const activeIndex = ref(0);
const isPaused = ref(false);
const touchStartX = ref<number | null>(null);
let autoplayId: number | undefined;

const activeSlide = computed(() => slides[activeIndex.value]);

function goToSlide(index: number) {
  activeIndex.value = (index + slides.length) % slides.length;
}

function nextSlide() {
  goToSlide(activeIndex.value + 1);
}

function previousSlide() {
  goToSlide(activeIndex.value - 1);
}

function startAutoplay() {
  stopAutoplay();
  autoplayId = window.setInterval(() => {
    if (!isPaused.value) nextSlide();
  }, 6500);
}

function stopAutoplay() {
  if (autoplayId) window.clearInterval(autoplayId);
}

function handleTouchStart(event: TouchEvent) {
  touchStartX.value = event.touches[0]?.clientX ?? null;
}

function handleTouchEnd(event: TouchEvent) {
  if (touchStartX.value === null) return;
  const endX = event.changedTouches[0]?.clientX ?? touchStartX.value;
  const delta = touchStartX.value - endX;

  if (Math.abs(delta) > 45) {
    if (delta > 0) nextSlide();
    else previousSlide();
  }

  touchStartX.value = null;
}

onMounted(startAutoplay);
onBeforeUnmount(stopAutoplay);
</script>

<template>
  <section
    class="relative isolate w-full overflow-hidden bg-ink"
    aria-label="Featured Eco Buka promotions"
    @mouseenter="isPaused = true"
    @mouseleave="isPaused = false"
    @touchstart.passive="handleTouchStart"
    @touchend.passive="handleTouchEnd"
  >
    <div class="relative h-[560px] min-h-[520px] sm:h-[620px] md:h-[68vh] md:min-h-[600px] lg:h-[78vh] lg:min-h-[660px] xl:h-[84vh] xl:min-h-[720px]">
      <div v-for="(slide, index) in slides" :key="slide.id" class="absolute inset-0 transition-opacity duration-700 ease-out" :class="index === activeIndex ? 'opacity-100' : 'pointer-events-none opacity-0'">
        <img
          :src="slide.image"
          :alt="slide.imageAlt"
          class="absolute inset-0 h-full w-full object-cover"
          :loading="index === 0 ? 'eager' : 'lazy'"
        />
        <div class="absolute inset-0" :class="slide.overlayStyle" />
      </div>

      <div class="container-shell relative z-10 flex h-full items-center pb-16 pt-12 sm:pb-20 lg:pb-24">
        <div :key="activeSlide.id" class="max-w-[680px] animate-[fadeIn_700ms_ease-out] pt-10 sm:pt-8 lg:pt-0" :class="activeSlide.textColor">
          <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/80 sm:text-sm">{{ activeSlide.eyebrow }}</p>
          <h1 class="mt-4 text-4xl font-black leading-[1.04] tracking-normal min-[420px]:text-5xl sm:mt-5 sm:text-6xl lg:text-7xl xl:text-8xl">
            {{ activeSlide.title }}
          </h1>
          <p class="mt-5 max-w-2xl text-base font-medium leading-7 text-white/86 sm:text-xl sm:leading-8 lg:text-2xl">
            {{ activeSlide.subtitle }}
          </p>
          <div class="mt-8 flex flex-col gap-3 min-[420px]:flex-row min-[420px]:flex-wrap sm:mt-10">
            <RouterLink :to="activeSlide.primaryButtonLink" class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-md bg-white px-6 py-3 text-sm font-bold text-ink transition hover:bg-mist min-[420px]:w-auto">
              {{ activeSlide.primaryButtonText }} <ArrowRight class="h-4 w-4 shrink-0" />
            </RouterLink>
            <RouterLink
              v-if="activeSlide.secondaryButtonText && activeSlide.secondaryButtonLink"
              :to="activeSlide.secondaryButtonLink"
              class="inline-flex min-h-12 w-full items-center justify-center rounded-md border border-white/55 bg-white/10 px-6 py-3 text-sm font-bold text-white backdrop-blur transition hover:bg-white/18 min-[420px]:w-auto"
            >
              {{ activeSlide.secondaryButtonText }}
            </RouterLink>
          </div>
        </div>
      </div>

      <button
        class="absolute left-3 top-1/2 z-20 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/18 text-white backdrop-blur transition hover:bg-white/28 md:flex"
        type="button"
        aria-label="Previous hero slide"
        @click="previousSlide"
      >
        <ChevronLeft class="h-6 w-6" />
      </button>
      <button
        class="absolute right-3 top-1/2 z-20 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/22 text-white backdrop-blur transition hover:bg-white/32 md:flex"
        type="button"
        aria-label="Next hero slide"
        @click="nextSlide"
      >
        <ChevronRight class="h-6 w-6" />
      </button>

      <div class="absolute bottom-6 left-1/2 z-20 flex -translate-x-1/2 items-center gap-3 sm:bottom-8">
        <button
          v-for="(slide, index) in slides"
          :key="slide.id"
          class="h-2.5 rounded-full transition-all"
          :class="index === activeIndex ? 'w-9 bg-white' : 'w-2.5 bg-white/45 hover:bg-white/70'"
          type="button"
          :aria-label="`Go to hero slide ${index + 1}`"
          :aria-current="index === activeIndex ? 'true' : undefined"
          @click="goToSlide(index)"
        />
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(14px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

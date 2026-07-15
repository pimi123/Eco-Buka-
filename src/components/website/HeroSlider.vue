<script setup lang="ts">
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../../lib/api';
import { optimizedImageUrl } from '../../lib/responsiveImages';
import type { HomepageBanner } from '../../types/homepage';

const deltaHeroUrl = '/promo/optimized/delta-max-series-1280.jpg';
const portableHeroUrl = '/promo/optimized/delta-classic-1280.jpg';
const solarHeroUrl = '/promo/optimized/summer-sale-1280.jpg';

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
  mobileImage?: string;
  imageAlt: string;
  overlayStyle: string;
  textColor: string;
  alignment: 'left' | 'center';
}

const fallbackSlides: HeroSlide[] = [
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
    primaryButtonLink: '/category/power-stations',
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
const isMobileViewport = ref(false);
const touchStartX = ref<number | null>(null);
const slides = ref<HeroSlide[]>(fallbackSlides);
let autoplayId: number | undefined;

const activeSlide = computed(() => slides.value[activeIndex.value] || fallbackSlides[0]);

function bannerOverlay(banner: HomepageBanner) {
  return banner.text_color === 'dark'
    ? 'bg-gradient-to-r from-white/82 via-white/42 to-white/8'
    : 'bg-gradient-to-r from-black/78 via-black/40 to-black/10';
}

function bannerTextClass(banner: HomepageBanner) {
  return banner.text_color === 'dark' ? 'text-ink' : 'text-white';
}

function mapBannerToSlide(banner: HomepageBanner): HeroSlide {
  return {
    id: String(banner.id),
    eyebrow: banner.eyebrow || '',
    title: banner.title,
    subtitle: banner.subtitle || '',
    primaryButtonText: banner.button_text || 'Learn More',
    primaryButtonLink: banner.button_link || '/products',
    secondaryButtonText: banner.second_button_text || 'View Products',
    secondaryButtonLink: banner.second_button_link || '/products',
    image: optimizedImageUrl(banner.background_image_url, 'desktop') || fallbackSlides[0].image,
    mobileImage: optimizedImageUrl(banner.mobile_background_image_url || banner.background_image_url, 'mobile') || fallbackSlides[0].image,
    imageAlt: banner.title,
    overlayStyle: bannerOverlay(banner),
    textColor: bannerTextClass(banner),
    alignment: banner.text_alignment === 'center' ? 'center' : 'left',
  };
}

function preloadFirstHeroImage() {
  if (typeof document === 'undefined') return;

  const firstImage = slides.value[0]?.image;
  if (!firstImage || document.head.querySelector(`link[rel="preload"][href="${firstImage}"]`)) return;

  const link = document.createElement('link');
  link.rel = 'preload';
  link.as = 'image';
  link.href = firstImage;
  link.setAttribute('fetchpriority', 'high');
  link.setAttribute('imagesizes', '100vw');
  document.head.appendChild(link);
}

async function fetchHeroBanners() {
  if (!hasLaravelApiConfig) return;

  try {
    const data = await apiGet<HomepageBanner[]>('/home/hero-banners');
    const activeBanners = data
      .filter((banner) => banner.active !== false)
      .sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0));

    if (activeBanners.length) {
      slides.value = activeBanners.map(mapBannerToSlide);
      activeIndex.value = 0;
      preloadFirstHeroImage();
    }
  } catch {
    slides.value = fallbackSlides;
  }
}

function goToSlide(index: number) {
  activeIndex.value = (index + slides.value.length) % slides.value.length;
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
    if (!isPaused.value && !isMobileViewport.value) nextSlide();
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

function updateViewportState() {
  isMobileViewport.value = window.matchMedia('(max-width: 767px)').matches;
  if (isMobileViewport.value) activeIndex.value = 0;
}

onMounted(() => {
  updateViewportState();
  window.addEventListener('resize', updateViewportState);
  preloadFirstHeroImage();
  fetchHeroBanners();
  startAutoplay();
});

onBeforeUnmount(() => {
  stopAutoplay();
  window.removeEventListener('resize', updateViewportState);
});
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
    <div class="relative h-[480px] min-h-[480px] sm:h-[600px] md:h-[68vh] md:min-h-[600px] lg:h-[78vh] lg:min-h-[660px] xl:h-[84vh] xl:min-h-[720px]">
      <div v-for="(slide, index) in slides" :key="slide.id" class="absolute inset-0 transition-opacity duration-700 ease-out" :class="index === activeIndex ? 'opacity-100' : 'pointer-events-none opacity-0'">
        <picture>
          <source v-if="slide.mobileImage" media="(max-width: 640px)" :srcset="slide.mobileImage" />
          <img
            :src="slide.image"
            :alt="slide.imageAlt"
            class="absolute inset-0 h-full w-full object-cover object-center"
            :loading="index === 0 ? 'eager' : 'lazy'"
            :fetchpriority="index === 0 ? 'high' : 'low'"
            decoding="async"
            sizes="100vw"
          />
        </picture>
        <div class="absolute inset-0" :class="slide.overlayStyle" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/78 via-black/34 to-black/12 md:hidden" />
        <div class="absolute inset-0 bg-gradient-to-r from-black/54 via-black/18 to-transparent md:hidden" />
      </div>

      <div class="container-shell relative z-10 flex h-full items-end pb-14 pt-10 sm:items-center sm:pb-20 lg:pb-24">
        <div :key="activeSlide.id" class="max-w-[680px] animate-[fadeIn_700ms_ease-out] pb-9 sm:pb-0 sm:pt-8 lg:pt-0" :class="activeSlide.textColor">
          <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/80 sm:text-sm">{{ activeSlide.eyebrow }}</p>
          <h1 class="mt-2 max-w-[12.5ch] text-[1.75rem] font-black leading-[1.05] tracking-normal min-[390px]:text-[2rem] min-[430px]:text-[2.15rem] sm:mt-5 sm:max-w-[14ch] sm:text-6xl lg:text-7xl xl:text-8xl">
            {{ activeSlide.title }}
          </h1>
          <p class="mt-3 max-w-[17.5rem] text-sm font-semibold leading-6 text-white/92 min-[390px]:text-[15px] sm:mt-5 sm:max-w-xl sm:text-xl sm:leading-8 lg:max-w-2xl lg:text-2xl">
            {{ activeSlide.subtitle }}
          </p>
          <div class="mt-5 flex max-w-[20rem] flex-row flex-wrap gap-2 sm:mt-10 sm:max-w-none">
            <RouterLink :to="activeSlide.primaryButtonLink" class="inline-flex min-h-11 flex-none items-center justify-center gap-2 rounded-md bg-white px-4 py-3 text-sm font-bold text-ink transition hover:bg-mist sm:min-h-12 sm:px-6">
              {{ activeSlide.primaryButtonText }} <ArrowRight class="h-4 w-4 shrink-0" />
            </RouterLink>
            <RouterLink
              v-if="activeSlide.secondaryButtonText && activeSlide.secondaryButtonLink"
              :to="activeSlide.secondaryButtonLink"
              class="inline-flex min-h-11 flex-none items-center justify-center rounded-md border border-white/55 bg-white/10 px-4 py-3 text-sm font-bold text-white backdrop-blur transition hover:bg-white/18 sm:min-h-12 sm:px-6"
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

      <div class="absolute bottom-5 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 sm:bottom-8 sm:gap-3">
        <button
          v-for="(slide, index) in slides"
          :key="slide.id"
          class="group grid h-8 place-items-center rounded-full px-0.5"
          type="button"
          :aria-label="`Go to hero slide ${index + 1}`"
          :aria-current="index === activeIndex ? 'true' : undefined"
          @click="goToSlide(index)"
        >
          <span
            class="h-2.5 rounded-full transition-all"
            :class="index === activeIndex ? 'w-9 bg-white' : 'w-2.5 bg-white/45 group-hover:bg-white/70'"
          />
        </button>
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

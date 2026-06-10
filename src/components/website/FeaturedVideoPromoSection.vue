<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import fallbackUrl from '../../assets/heroes/hero-solar-home.png';
import { apiGet, hasLaravelApiConfig } from '../../lib/api';
import { demoFeaturedVideoPromoBanners } from '../../lib/demoData';
import type { HomepageBanner } from '../../types/homepage';

const props = withDefaults(
  defineProps<{
    sectionKey?: string;
    title?: string;
    promoLabel?: string;
    heading?: string;
    description?: string;
    priceText?: string;
    buttonText?: string;
    buttonUrl?: string;
    backgroundVideoUrl?: string;
    fallbackImageUrl?: string;
  }>(),
  {
    sectionKey: 'featured_video_promo',
    title: '',
    promoLabel: '',
    heading: '',
    description: '',
    priceText: '',
    buttonText: '',
    buttonUrl: '',
    backgroundVideoUrl: '',
    fallbackImageUrl: '',
  },
);

const banner = ref<HomepageBanner | null>(null);
const loading = ref(false);
const videoFailed = ref(false);

const fallbackBanner = computed(() => demoFeaturedVideoPromoBanners[0] || null);
const activeBanner = computed(() => banner.value || fallbackBanner.value);

const content = computed(() => ({
  title: props.title || activeBanner.value?.section_heading || '',
  promoLabel: props.promoLabel || activeBanner.value?.eyebrow || '',
  heading: props.heading || activeBanner.value?.title || '',
  description: props.description || activeBanner.value?.subtitle || '',
  priceText: props.priceText || activeBanner.value?.price_text || '',
  buttonText: props.buttonText || activeBanner.value?.button_text || 'Buy Now',
  buttonUrl: props.buttonUrl || activeBanner.value?.button_link || '/products',
  backgroundVideoUrl: props.backgroundVideoUrl || activeBanner.value?.background_video_url || '',
  fallbackImageUrl: props.fallbackImageUrl || activeBanner.value?.background_image_url || activeBanner.value?.mobile_background_image_url || fallbackUrl,
}));

const sectionVisible = computed(() => loading.value || Boolean(content.value.heading));
const shouldShowVideo = computed(() => Boolean(content.value.backgroundVideoUrl && !videoFailed.value));
const isExternalButton = computed(() => /^https?:\/\//i.test(content.value.buttonUrl));

onMounted(async () => {
  if (props.heading || !hasLaravelApiConfig) return;

  loading.value = true;
  try {
    const data = await apiGet<HomepageBanner[]>(`/home/feature-banners/${props.sectionKey}`);
    banner.value = data[0] || null;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <section v-if="sectionVisible" class="bg-white py-8 sm:py-12 lg:py-14" aria-label="Featured video promotion">
    <div class="container-shell">
      <h2 v-if="content.title" class="mb-5 text-2xl font-black leading-tight text-ink sm:text-3xl">
        {{ content.title }}
      </h2>

      <div v-if="loading" class="h-[460px] animate-pulse rounded-lg bg-mist sm:h-[480px] lg:h-[540px]" />

      <div v-else class="relative min-h-[500px] overflow-hidden rounded-lg bg-ink shadow-panel sm:min-h-[420px] lg:min-h-[520px]">
        <img
          :src="content.fallbackImageUrl"
          :alt="content.heading"
          class="absolute inset-0 h-full w-full object-cover"
          loading="lazy"
        />

        <video
          v-if="shouldShowVideo"
          class="absolute inset-0 h-full w-full object-cover"
          :src="content.backgroundVideoUrl"
          autoplay
          muted
          loop
          playsinline
          preload="metadata"
          aria-hidden="true"
          @error="videoFailed = true"
        />

        <div class="absolute inset-0 bg-gradient-to-t from-black/86 via-black/52 to-black/18 sm:bg-gradient-to-r sm:from-black/82 sm:via-black/50 sm:to-black/12" />

        <div class="relative z-10 flex min-h-[500px] items-end px-5 py-9 sm:min-h-[420px] sm:items-center sm:px-10 lg:min-h-[520px] lg:px-14">
          <div class="max-w-xl text-white">
            <p v-if="content.promoLabel" class="text-sm font-black uppercase tracking-wide text-orange-400 sm:text-base">
              {{ content.promoLabel }}
            </p>
            <h3 class="mt-3 text-3xl font-black leading-tight min-[390px]:text-4xl lg:text-5xl">
              {{ content.heading }}
            </h3>
            <p v-if="content.description" class="mt-4 text-base font-semibold leading-7 text-white/90 sm:text-lg">
              {{ content.description }}
            </p>
            <p v-if="content.priceText" class="mt-5 text-xl font-black text-white sm:text-2xl">
              {{ content.priceText }}
            </p>

            <div v-if="content.buttonText && content.buttonUrl" class="mt-7">
              <a
                v-if="isExternalButton"
                :href="content.buttonUrl"
                target="_blank"
                rel="noreferrer"
                class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-white px-8 py-3 text-sm font-black text-ink transition hover:bg-mist sm:w-auto"
              >
                {{ content.buttonText }}
              </a>
              <RouterLink
                v-else
                :to="content.buttonUrl"
                class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-white px-8 py-3 text-sm font-black text-ink transition hover:bg-mist sm:w-auto"
              >
                {{ content.buttonText }}
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

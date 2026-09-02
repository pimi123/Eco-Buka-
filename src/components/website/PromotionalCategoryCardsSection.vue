<script setup lang="ts">
import { ArrowRight, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../../lib/api';
import { optimizedImageUrl } from '../../lib/responsiveImages';
import { demoPromotionalCategoryCards } from '../../lib/demoData';
import type { HomepagePromoCard, HomepageSection } from '../../types/homepage';

const fallbackUrl = '/promo/optimized/delta-max-series-1280.jpg';

const props = withDefaults(
  defineProps<{
    cards?: HomepagePromoCard[];
    sectionKey?: string;
    title?: string;
    subtitle?: string;
  }>(),
  {
    cards: undefined,
    sectionKey: 'promotional_category_cards',
    title: '',
    subtitle: '',
  },
);

const cardTrack = ref<HTMLElement | null>(null);
const loadedCards = ref<HomepagePromoCard[]>([]);
const section = ref<HomepageSection | null>(null);
const loading = ref(false);
const error = ref<string | null>(null);

const activeLoadedCards = computed(() =>
  loadedCards.value
    .filter((card) => card.active !== false)
    .sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0)),
);
const cardsToRender = computed(() =>
  [...(props.cards?.length ? props.cards : activeLoadedCards.value.length ? activeLoadedCards.value : demoPromotionalCategoryCards)]
    .filter((card) => card.active !== false)
    .sort((a, b) => Number(a.sort_order || 0) - Number(b.sort_order || 0))
    .slice(0, Number(section.value?.display_limit || 24)),
);
const sectionTitle = computed(() => props.title || section.value?.title || '');
const sectionSubtitle = computed(() => props.subtitle || section.value?.subtitle || '');
const layoutVariant = computed(() => {
  const variant = section.value?.layout_variant || (cardsToRender.value.length === 1 ? 'single_banner' : cardsToRender.value.length > 2 ? 'carousel' : 'two_cards');
  return ['single_banner', 'two_cards', 'grid', 'carousel'].includes(variant) ? variant : 'two_cards';
});
const effectiveLayout = computed(() => {
  if (cardsToRender.value.length <= 1 || layoutVariant.value === 'single_banner') return 'single_banner';
  if (layoutVariant.value === 'grid') return 'grid';
  if (layoutVariant.value === 'carousel' || cardsToRender.value.length > 2) return 'carousel';
  return 'two_cards';
});
const isSingleBanner = computed(() => effectiveLayout.value === 'single_banner');
const isCarousel = computed(() => effectiveLayout.value === 'carousel');
const isGrid = computed(() => effectiveLayout.value === 'grid');
const canScroll = computed(() => isCarousel.value && cardsToRender.value.length > 1);

function scrollCards(direction: -1 | 1) {
  const container = cardTrack.value;
  if (!container) return;

  container.scrollBy({
    left: direction * Math.min(container.clientWidth * 0.9, 900),
    behavior: 'smooth',
  });
}

function cardLink(card: HomepagePromoCard) {
  const link = card.button_link || (card.category_slug ? `/category/${card.category_slug}` : '/products');
  return link.replace(/^\/categories\//, '/category/');
}

function isExternal(link: string) {
  return /^https?:\/\//i.test(link);
}

function ariaLabel(card: HomepagePromoCard) {
  return card.category_slug ? `Shiko produktet në kategorinë ${card.title}` : `Shiko ${card.title}`;
}

function wrapperClass() {
  if (isCarousel.value) {
    return 'flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] sm:gap-5 lg:gap-6 [&::-webkit-scrollbar]:hidden';
  }

  if (isGrid.value) {
    return 'grid gap-4 sm:grid-cols-2 lg:grid-cols-3';
  }

  if (isSingleBanner.value) {
    return 'grid gap-5';
  }

  return 'grid gap-5 md:grid-cols-2 lg:gap-6';
}

function cardClass() {
  if (isCarousel.value) {
    return 'h-[280px] w-[84vw] max-w-[360px] shrink-0 snap-start sm:h-[330px] sm:w-[360px] lg:h-[356px] lg:w-[390px] xl:w-[410px]';
  }

  if (isSingleBanner.value) {
    return 'min-h-[320px] sm:min-h-[420px] lg:min-h-[460px]';
  }

  return 'min-h-[280px] sm:min-h-[340px] lg:min-h-[380px]';
}

function cardImageSizes() {
  if (isCarousel.value) return '(max-width: 640px) 84vw, (max-width: 1023px) 360px, 410px';
  if (isSingleBanner.value) return '(max-width: 1023px) 100vw, 1200px';
  return '(max-width: 767px) 100vw, 50vw';
}

onMounted(async () => {
  if (props.cards?.length || !hasLaravelApiConfig) return;

  loading.value = true;
  error.value = null;
  try {
    const data = await apiGet<{ section: HomepageSection | null; cards: HomepagePromoCard[] }>(`/home/promo-card-section/${props.sectionKey}`);
    section.value = data.section;
    loadedCards.value = data.cards;
  } catch (requestError) {
    error.value = requestError instanceof Error ? requestError.message : 'Kartelat promovuese nuk mund të ngarkohen.';
    loadedCards.value = demoPromotionalCategoryCards;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <section
    v-if="loading || cardsToRender.length"
    class="py-8 sm:py-11 lg:py-12"
    :class="isCarousel ? 'bg-mist' : 'bg-white'"
    :aria-label="sectionTitle || 'Kartela promovuese'"
  >
    <div class="container-shell">
      <div v-if="sectionTitle || sectionSubtitle || canScroll" class="mb-5 flex items-end justify-between gap-4 sm:mb-6">
        <div class="min-w-0 max-w-3xl">
          <h2 v-if="sectionTitle" class="text-2xl font-black leading-tight text-ink sm:text-3xl">
            {{ sectionTitle }}
          </h2>
          <p v-if="sectionSubtitle" class="mt-2 text-sm leading-6 text-slate-600 sm:text-base">
            {{ sectionSubtitle }}
          </p>
        </div>

        <div v-if="canScroll" class="hidden shrink-0 gap-2 lg:flex">
          <button
            type="button"
            class="grid h-10 w-10 place-items-center rounded-full border border-line bg-white text-ink shadow-sm transition hover:border-ink"
            aria-label="Lëviz kartelat majtas"
            @click="scrollCards(-1)"
          >
            <ChevronLeft class="h-5 w-5" />
          </button>
          <button
            type="button"
            class="grid h-10 w-10 place-items-center rounded-full border border-line bg-white text-ink shadow-sm transition hover:border-ink"
            aria-label="Lëviz kartelat djathtas"
            @click="scrollCards(1)"
          >
            <ChevronRight class="h-5 w-5" />
          </button>
        </div>
      </div>

      <div v-if="loading" :class="wrapperClass()">
        <div v-for="index in 2" :key="index" class="h-[320px] animate-pulse rounded-lg bg-mist sm:h-[360px]" />
      </div>

      <div v-else ref="cardTrack" :class="wrapperClass()">
        <component
          :is="isExternal(cardLink(card)) ? 'a' : 'RouterLink'"
          v-for="card in cardsToRender"
          :key="card.id"
          :href="isExternal(cardLink(card)) ? cardLink(card) : undefined"
          :to="!isExternal(cardLink(card)) ? cardLink(card) : undefined"
          :target="isExternal(cardLink(card)) ? '_blank' : undefined"
          :rel="isExternal(cardLink(card)) ? 'noreferrer' : undefined"
          :aria-label="ariaLabel(card)"
          class="group relative cursor-pointer overflow-hidden rounded-lg bg-ink shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-panel"
          :class="cardClass()"
        >
          <video
            v-if="card.background_video_url"
            class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
            :poster="optimizedImageUrl(card.background_image_url || card.mobile_background_image_url, 'desktop') || fallbackUrl"
            autoplay
            muted
            loop
            playsinline
            preload="metadata"
          >
            <source :src="card.background_video_url" />
          </video>
          <img
            v-else
            :src="optimizedImageUrl(card.background_image_url || card.mobile_background_image_url, 'desktop') || fallbackUrl"
            :alt="card.title"
            class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
            loading="lazy"
            decoding="async"
            :sizes="cardImageSizes()"
          />
          <div class="absolute inset-0 bg-gradient-to-r from-black/82 via-black/45 to-black/10" />
          <div class="relative z-10 flex h-full max-w-md flex-col items-start p-5 text-white sm:p-7">
            <h2 v-if="card.title" class="text-xl font-black leading-tight sm:text-2xl">{{ card.title }}</h2>
            <p v-if="card.subtitle" class="mt-3 text-sm font-bold leading-6 text-white/95 sm:text-base">{{ card.subtitle }}</p>
            <span class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-black text-ink shadow-sm transition duration-300 group-hover:gap-3 group-hover:bg-white/90">
              {{ card.button_text || 'Shiko produktet' }}
              <ArrowRight class="h-4 w-4" />
            </span>
          </div>
        </component>
      </div>

      <p v-if="error && !cardsToRender.length" class="mt-4 text-sm font-semibold text-red-600">{{ error }}</p>
    </div>
  </section>
</template>

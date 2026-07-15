<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../../lib/api';
import { optimizedImageUrl } from '../../lib/responsiveImages';
import { demoPromotionalCategoryCards } from '../../lib/demoData';
import type { HomepagePromoCard } from '../../types/homepage';

const fallbackUrl = '/promo/optimized/delta-max-series-1280.jpg';

const props = withDefaults(
  defineProps<{
    cards?: HomepagePromoCard[];
    sectionKey?: string;
  }>(),
  {
    cards: undefined,
    sectionKey: 'promotional_category_cards',
  },
);

const loadedCards = ref<HomepagePromoCard[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

const activeLoadedCards = computed(() => loadedCards.value.filter((card) => card.active !== false));
const cardsToRender = computed(() =>
  (props.cards?.length ? props.cards : activeLoadedCards.value.length ? activeLoadedCards.value : demoPromotionalCategoryCards).filter((card) => card.active !== false),
);

function cardLink(card: HomepagePromoCard) {
  const link = card.button_link || (card.category_slug ? `/category/${card.category_slug}` : '/products');
  return link.replace(/^\/categories\//, '/category/');
}

function isExternal(link: string) {
  return /^https?:\/\//i.test(link);
}

function ariaLabel(card: HomepagePromoCard) {
  return card.category_slug ? `View products in ${card.title} category` : `View ${card.title}`;
}

onMounted(async () => {
  if (props.cards?.length || !hasLaravelApiConfig) return;

  loading.value = true;
  error.value = null;
  try {
    loadedCards.value = await apiGet<HomepagePromoCard[]>(`/home/promo-cards/${props.sectionKey}`);
  } catch (requestError) {
    error.value = requestError instanceof Error ? requestError.message : 'Promotional cards could not be loaded.';
    loadedCards.value = demoPromotionalCategoryCards;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <section v-if="loading || cardsToRender.length" class="bg-white py-8 sm:py-12 lg:py-14" aria-label="Promotional category cards">
    <div class="container-shell">
      <div v-if="loading" class="grid gap-5 md:grid-cols-2 lg:gap-6">
        <div v-for="index in 2" :key="index" class="h-[320px] animate-pulse rounded-lg bg-mist sm:h-[360px]" />
      </div>

      <div v-else class="grid gap-5 md:grid-cols-2 lg:gap-6">
        <component
          :is="isExternal(cardLink(card)) ? 'a' : 'RouterLink'"
          v-for="card in cardsToRender"
          :key="card.id"
          :href="isExternal(cardLink(card)) ? cardLink(card) : undefined"
          :to="!isExternal(cardLink(card)) ? cardLink(card) : undefined"
          :target="isExternal(cardLink(card)) ? '_blank' : undefined"
          :rel="isExternal(cardLink(card)) ? 'noreferrer' : undefined"
          :aria-label="ariaLabel(card)"
          class="group relative min-h-[280px] overflow-hidden rounded-lg bg-ink shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-panel sm:min-h-[340px] lg:min-h-[380px]"
        >
          <img
            :src="optimizedImageUrl(card.background_image_url || card.mobile_background_image_url, 'desktop') || fallbackUrl"
            :alt="card.title"
            class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105"
            loading="lazy"
            decoding="async"
            sizes="(max-width: 767px) 100vw, 50vw"
          />
          <div class="absolute inset-0 bg-gradient-to-r from-black/82 via-black/45 to-black/10" />
          <div class="relative z-10 flex h-full max-w-md flex-col items-start p-5 text-white sm:p-7">
            <h2 v-if="card.title" class="text-xl font-black leading-tight sm:text-2xl">{{ card.title }}</h2>
            <p v-if="card.subtitle" class="mt-3 text-sm font-bold leading-6 text-white/95 sm:text-base">{{ card.subtitle }}</p>
            <span class="mt-4 inline-flex items-center gap-1 text-sm font-black text-white transition group-hover:gap-2">
              {{ card.button_text || 'View Products' }}
              <ArrowRight class="h-4 w-4" />
            </span>
          </div>
        </component>
      </div>

      <p v-if="error && !cardsToRender.length" class="mt-4 text-sm font-semibold text-red-600">{{ error }}</p>
    </div>
  </section>
</template>

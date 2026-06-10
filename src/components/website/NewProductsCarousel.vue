<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { useHomepageStore } from '../../stores/homepageStore';
import type { HomepagePromoCard } from '../../types/homepage';

const props = withDefaults(
  defineProps<{
    sectionKey?: string;
    title?: string;
    subtitle?: string;
  }>(),
  {
    sectionKey: 'new_products',
    title: 'New Products',
    subtitle: '',
  },
);

const homepageStore = useHomepageStore();
const carousel = ref<HTMLElement | null>(null);

function scrollCards(direction: -1 | 1) {
  const container = carousel.value;
  if (!container) return;

  container.scrollBy({
    left: direction * Math.min(container.clientWidth * 0.9, 980),
    behavior: 'smooth',
  });
}

function cardLink(card: HomepagePromoCard) {
  return card.button_link || '/products';
}

function isExternal(link?: string | null) {
  return Boolean(link && /^https?:\/\//i.test(link));
}

onMounted(() => {
  homepageStore.fetchPromoCards(props.sectionKey);
});
</script>

<template>
  <section
    v-if="homepageStore.promoCardsLoading || homepageStore.activePromoCards.length"
    class="bg-mist py-8 sm:py-11 lg:py-12"
    aria-label="New product promotions"
  >
    <div class="container-shell">
      <div class="mb-5 flex items-end justify-between gap-4 sm:mb-6">
        <div class="min-w-0">
          <h2 class="text-2xl font-black leading-tight text-ink sm:text-3xl">{{ title }}</h2>
          <p v-if="subtitle" class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 sm:text-base">{{ subtitle }}</p>
        </div>

        <div class="hidden shrink-0 gap-2 lg:flex">
          <button
            type="button"
            class="grid h-10 w-10 place-items-center rounded-full border border-line bg-white text-ink shadow-sm transition hover:border-ink"
            aria-label="Scroll promotions left"
            @click="scrollCards(-1)"
          >
            <ChevronLeft class="h-5 w-5" />
          </button>
          <button
            type="button"
            class="grid h-10 w-10 place-items-center rounded-full border border-line bg-white text-ink shadow-sm transition hover:border-ink"
            aria-label="Scroll promotions right"
            @click="scrollCards(1)"
          >
            <ChevronRight class="h-5 w-5" />
          </button>
        </div>
      </div>

      <div
        v-if="homepageStore.promoCardsLoading"
        class="flex gap-4 overflow-hidden"
      >
        <div
          v-for="index in 4"
          :key="index"
          class="h-[280px] w-[84vw] max-w-[360px] shrink-0 animate-pulse rounded-lg bg-white sm:h-[330px] sm:w-[360px] lg:h-[356px] lg:w-[390px]"
        />
      </div>

      <div
        v-else
        ref="carousel"
        class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-2 [-ms-overflow-style:none] [scrollbar-width:none] lg:gap-5 [&::-webkit-scrollbar]:hidden"
      >
        <article
          v-for="(card, index) in homepageStore.activePromoCards"
          :key="card.id"
          class="group relative h-[280px] w-[84vw] max-w-[360px] shrink-0 snap-start overflow-hidden rounded-lg bg-slate-200 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-panel sm:h-[330px] sm:w-[360px] lg:h-[356px] lg:w-[390px] xl:w-[410px]"
          :class="[
            card.text_color === 'dark'
              ? 'text-ink'
              : 'text-white',
            !card.background_image_url && !card.mobile_background_image_url
              ? ['bg-gradient-to-br', index % 4 === 0 ? 'from-sky-200 via-blue-300 to-sky-500' : index % 4 === 1 ? 'from-slate-800 via-slate-600 to-slate-900' : index % 4 === 2 ? 'from-emerald-900 via-emerald-700 to-lime-200' : 'from-stone-950 via-stone-800 to-stone-600']
              : '',
          ]"
        >
          <picture v-if="card.background_image_url || card.mobile_background_image_url" class="absolute inset-0">
            <source v-if="card.mobile_background_image_url" media="(max-width: 640px)" :srcset="card.mobile_background_image_url" />
            <img
              :src="card.background_image_url || card.mobile_background_image_url || ''"
              :alt="card.title"
              class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
              loading="lazy"
            />
          </picture>

          <div
            class="absolute inset-0"
            :class="card.text_color === 'dark'
              ? 'bg-gradient-to-br from-white/90 via-white/55 to-white/5'
              : 'bg-gradient-to-br from-black/70 via-black/35 to-black/5'"
          />

          <div class="relative z-10 flex h-full max-w-[20rem] flex-col p-5 sm:max-w-[21rem] sm:p-6">
            <p v-if="card.label" class="text-sm font-semibold leading-5 text-orange-500">{{ card.label }}</p>
            <h3 class="mt-2 text-xl font-black leading-tight min-[390px]:text-2xl sm:text-[1.65rem]">{{ card.title }}</h3>
            <p v-if="card.subtitle" class="mt-3 line-clamp-3 text-sm font-semibold leading-6 opacity-95 sm:text-[0.95rem]">
              {{ card.subtitle }}
            </p>

            <div class="mt-6 sm:mt-7">
              <a
                v-if="isExternal(card.button_link)"
                :href="cardLink(card)"
                class="inline-flex min-h-11 items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-bold text-ink shadow-sm transition hover:bg-mist sm:px-7"
                target="_blank"
                rel="noreferrer"
              >
                {{ card.button_text || 'Learn More' }}
              </a>
              <RouterLink
                v-else
                :to="cardLink(card)"
                class="inline-flex min-h-11 items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-bold text-ink shadow-sm transition hover:bg-mist sm:px-7"
              >
                {{ card.button_text || 'Learn More' }}
              </RouterLink>
            </div>
          </div>
        </article>
      </div>

      <p v-if="homepageStore.promoCardsError && !homepageStore.activePromoCards.length" class="mt-4 text-sm font-semibold text-red-600">
        {{ homepageStore.promoCardsError }}
      </p>
    </div>
  </section>
</template>

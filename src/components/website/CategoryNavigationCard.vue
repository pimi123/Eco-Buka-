<script setup lang="ts">
import { ArrowRight } from 'lucide-vue-next';
import type { HomepageNavigationCard } from '../../types/homepage';

const { card, variant = 'default' } = defineProps<{ card: HomepageNavigationCard; variant?: 'default' | 'showcase' }>();
</script>

<template>
  <RouterLink
    :to="card.link || '/products'"
    class="group min-w-0 rounded-lg bg-white shadow-sm transition hover:shadow-panel"
    :class="variant === 'showcase' ? 'relative flex min-h-[176px] flex-col justify-between overflow-hidden p-5 sm:min-h-[190px]' : 'flex min-h-[126px] items-center justify-between gap-4 p-4 hover:-translate-y-1 sm:min-h-[150px] sm:p-5'"
  >
    <div class="relative z-10 min-w-0">
      <h3 class="font-black leading-6 text-ink" :class="variant === 'showcase' ? 'max-w-[13rem] text-base sm:text-lg' : 'text-base sm:text-lg'">{{ card.title }}</h3>
      <div class="mt-4 inline-flex items-center justify-center rounded-full border border-line text-ink transition group-hover:border-ink" :class="variant === 'showcase' ? 'h-10 w-10' : 'h-9 w-9'">
        <ArrowRight class="h-4 w-4" />
      </div>
    </div>
    <img
      v-if="card.image_url"
      :src="card.image_url"
      :alt="card.title"
      class="object-contain transition group-hover:scale-105"
      :class="variant === 'showcase' ? 'ml-auto mt-3 h-24 w-36 sm:h-28 sm:w-44' : 'h-20 w-24 shrink-0 sm:h-24 sm:w-28'"
      loading="lazy"
      decoding="async"
      sizes="176px"
    />
  </RouterLink>
</template>

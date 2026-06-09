<script setup lang="ts">
import type { HomepageBanner } from '../../types/homepage';
import fallbackUrl from '../../assets/heroes/hero-delta-max.png';

const props = defineProps<{ banner: HomepageBanner }>();

const textClass = props.banner.text_color === 'dark' ? 'text-ink' : 'text-white';
const overlayClass = props.banner.text_color === 'dark'
  ? 'bg-gradient-to-r from-white/92 via-white/72 to-white/10'
  : 'bg-gradient-to-r from-black/78 via-black/42 to-black/10';
const alignmentClass = props.banner.text_alignment === 'center'
  ? 'items-center text-center'
  : props.banner.text_alignment === 'right'
    ? 'items-end text-right'
    : 'items-start text-left';
</script>

<template>
  <section>
    <h2 v-if="banner.section_heading" class="mb-5 text-2xl font-black tracking-normal text-ink sm:text-3xl">
      {{ banner.section_heading }}
    </h2>
    <div class="relative min-h-[360px] overflow-hidden rounded-lg bg-ink shadow-panel sm:min-h-[430px] lg:min-h-[500px]">
      <picture>
        <source v-if="banner.mobile_background_image_url" media="(max-width: 640px)" :srcset="banner.mobile_background_image_url" />
        <img :src="banner.background_image_url || fallbackUrl" :alt="banner.title" class="absolute inset-0 h-full w-full object-cover" loading="lazy" />
      </picture>
      <div class="absolute inset-0" :class="overlayClass" />
      <div class="relative z-10 flex min-h-[360px] px-5 py-8 sm:min-h-[430px] sm:px-8 sm:py-10 lg:min-h-[500px] lg:px-12" :class="alignmentClass">
        <div class="flex max-w-xl flex-col justify-center" :class="textClass">
          <p v-if="banner.eyebrow" class="text-sm font-bold uppercase tracking-wide opacity-90">{{ banner.eyebrow }}</p>
          <h3 class="mt-3 text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">{{ banner.title }}</h3>
          <p v-if="banner.subtitle" class="mt-4 text-base font-semibold leading-7 opacity-90 sm:text-lg">{{ banner.subtitle }}</p>
          <RouterLink
            v-if="banner.button_text && banner.button_link"
            :to="banner.button_link"
            class="mt-7 inline-flex min-h-12 w-full items-center justify-center rounded-full bg-white px-7 py-3 text-sm font-bold text-ink transition hover:bg-mist sm:w-fit"
          >
            {{ banner.button_text }}
          </RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>

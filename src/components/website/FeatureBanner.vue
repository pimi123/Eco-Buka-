<script setup lang="ts">
import type { HomepageBanner } from '../../types/homepage';
import fallbackUrl from '../../assets/heroes/hero-delta-max.png';

const props = defineProps<{ banner: HomepageBanner }>();

const textClass = props.banner.text_color === 'dark' ? 'text-ink' : 'text-white';
const overlayClass = props.banner.text_color === 'dark'
  ? 'bg-gradient-to-t from-white/94 via-white/68 to-white/18 sm:bg-gradient-to-r sm:from-white/92 sm:via-white/72 sm:to-white/10'
  : 'bg-gradient-to-t from-black/88 via-black/56 to-black/16 sm:bg-gradient-to-r sm:from-black/78 sm:via-black/42 sm:to-black/10';
const alignmentClass = props.banner.text_alignment === 'center'
  ? 'items-end text-left sm:items-center sm:text-center'
  : props.banner.text_alignment === 'right'
    ? 'items-end text-left sm:items-end sm:text-right'
    : 'items-start text-left';
</script>

<template>
  <section>
    <h2 v-if="banner.section_heading" class="mb-4 max-w-full text-2xl font-black leading-tight tracking-normal text-ink [overflow-wrap:anywhere] sm:mb-5 sm:text-3xl">
      {{ banner.section_heading }}
    </h2>
    <div class="relative min-h-[340px] overflow-hidden rounded-2xl bg-ink shadow-panel sm:min-h-[430px] sm:rounded-lg lg:min-h-[500px]">
      <picture>
        <source v-if="banner.mobile_background_image_url" media="(max-width: 640px)" :srcset="banner.mobile_background_image_url" />
        <img
          :src="banner.background_image_url || fallbackUrl"
          :alt="banner.title"
          class="absolute inset-0 h-full w-full object-cover object-center"
          loading="lazy"
          decoding="async"
          sizes="(max-width: 767px) 100vw, 1200px"
        />
      </picture>
      <div class="absolute inset-0" :class="overlayClass" />
      <div class="relative z-10 flex min-h-[340px] px-5 py-6 sm:min-h-[430px] sm:px-8 sm:py-10 lg:min-h-[500px] lg:px-12" :class="alignmentClass">
        <div class="flex w-full max-w-[19rem] flex-col justify-end sm:max-w-xl sm:justify-center" :class="textClass">
          <p v-if="banner.eyebrow" class="text-xs font-black uppercase tracking-wide opacity-90 sm:text-sm">{{ banner.eyebrow }}</p>
          <h3 class="mt-2 line-clamp-3 max-w-full text-2xl font-black leading-tight [overflow-wrap:anywhere] min-[390px]:text-[1.7rem] sm:mt-3 sm:text-4xl lg:text-5xl">{{ banner.title }}</h3>
          <p v-if="banner.subtitle" class="mt-3 line-clamp-2 max-w-full text-sm font-semibold leading-6 opacity-90 [overflow-wrap:anywhere] sm:mt-4 sm:text-lg sm:leading-7">{{ banner.subtitle }}</p>
          <RouterLink
            v-if="banner.button_text && banner.button_link"
            :to="banner.button_link"
            class="mt-5 inline-flex min-h-11 w-fit max-w-full items-center justify-center rounded-full bg-white px-7 py-3 text-sm font-black text-ink transition hover:bg-mist sm:mt-7 sm:min-h-12"
          >
            {{ banner.button_text }}
          </RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>

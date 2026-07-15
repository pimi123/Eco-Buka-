<script setup lang="ts">
import { ArrowRight, Zap } from 'lucide-vue-next';

const heroUrl = '/promo/optimized/summer-sale-1280.jpg';
</script>

<template>
  <section class="bg-mist">
    <div class="container-shell grid min-h-0 items-center gap-8 py-8 sm:py-10 lg:min-h-[620px] lg:grid-cols-[0.95fr_1.05fr]">
      <div class="max-w-2xl pt-2 lg:pt-0">
        <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-bold text-energy shadow-sm">
          <Zap class="h-4 w-4" /> Legal distributor energy solutions
        </div>
        <h1 class="mt-5 text-3xl font-black leading-tight tracking-normal text-ink min-[420px]:text-4xl sm:mt-6 sm:text-5xl lg:text-6xl">
          Power your home, business and outdoor life
        </h1>
        <p class="mt-4 max-w-xl text-base leading-7 text-slate-600 sm:mt-5 sm:text-lg sm:leading-8">
          Portable power stations, solar panels and smart energy solutions for every situation.
        </p>
        <div class="mt-7 flex flex-col gap-3 min-[420px]:flex-row min-[420px]:flex-wrap sm:mt-8">
          <RouterLink to="/products" class="btn-primary w-full min-[420px]:w-auto">Shop Products <ArrowRight class="h-4 w-4 shrink-0" /></RouterLink>
          <RouterLink to="/about" class="btn-secondary w-full min-[420px]:w-auto">Explore Solutions</RouterLink>
        </div>
      </div>
      <div class="relative overflow-hidden rounded-lg bg-white shadow-panel">
        <img
          :src="heroUrl"
          alt="Eco Buka portable power and solar kit"
          class="aspect-[4/3] w-full object-cover sm:aspect-[16/10] lg:min-h-[360px]"
          loading="eager"
          fetchpriority="high"
          decoding="async"
          sizes="(max-width: 1023px) 100vw, 52vw"
        />
        <div class="static grid gap-3 bg-white/95 p-4 backdrop-blur sm:absolute sm:bottom-4 sm:left-4 sm:right-4 sm:rounded-md sm:bg-white/92 sm:grid-cols-3">
          <div><p class="text-xl font-black">512Wh+</p><p class="text-xs text-slate-500">Portable capacity</p></div>
          <div><p class="text-xl font-black">220W</p><p class="text-xs text-slate-500">Solar charging</p></div>
          <div><p class="text-xl font-black">24/7</p><p class="text-xs text-slate-500">Backup ready</p></div>
        </div>
      </div>
    </div>
  </section>
</template>

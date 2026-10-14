<template>
  <WebsiteLayout>
    <section class="container-shell py-12 sm:py-20 lg:py-24">
      <div class="mx-auto max-w-6xl">
        <p class="label flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-orange-500" aria-hidden="true"></span>Rreth nesh</p>
        <div class="mt-6 grid gap-7 border-b border-line pb-10 sm:mt-8 sm:pb-14 lg:grid-cols-[1.2fr_0.8fr] lg:items-end lg:gap-16">
          <h1 class="max-w-3xl text-4xl font-black leading-[1.12] tracking-tight sm:text-5xl lg:text-6xl">
            Zgjidhje energjetike.<br>
            <span class="text-slate-400">Për çdo ditë.</span>
          </h1>
          <p class="max-w-lg text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
            EcoBuka është distributor i produkteve EcoFlow. Sjellim zgjidhje për energji portative, karikim solar dhe rezervë energjie për shtëpinë dhe biznesin tuaj.
          </p>
        </div>

        <div class="grid gap-8 py-10 sm:py-14 lg:grid-cols-[1.2fr_0.8fr] lg:gap-16">
          <div>
            <h2 class="text-xl font-bold sm:text-2xl">Energjia që ju duhet, aty ku ju duhet.</h2>
            <p class="mt-4 max-w-xl text-base leading-7 text-slate-600">
              Nga aktivitetet në natyrë te nevojat e përditshme, ju ndihmojmë të gjeni zgjidhjen që i përshtatet mënyrës suaj të jetesës dhe punës.
            </p>
            <RouterLink to="/products" class="mt-7 inline-flex min-h-11 items-center gap-3 text-sm font-bold underline decoration-slate-300 underline-offset-8 transition hover:decoration-ink">
              Zbuloni produktet <ArrowUpRight class="h-4 w-4" aria-hidden="true" />
            </RouterLink>
          </div>
          <div class="divide-y divide-line border-y border-line">
            <div v-for="(area, index) in areas" :key="area.title" class="flex gap-5 py-5">
              <span class="pt-1 text-xs font-semibold text-slate-400">0{{ index + 1 }}</span>
              <div>
                <h3 class="text-base font-bold">{{ area.title }}</h3>
                <p class="mt-1 text-sm leading-6 text-slate-600">{{ area.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-5 rounded-lg bg-mist px-6 py-7 sm:flex-row sm:items-center sm:justify-between sm:px-8">
          <div>
            <h2 class="text-lg font-bold">Keni nevojë për ndihmë me zgjedhjen?</h2>
            <p class="mt-1 text-sm leading-6 text-slate-600">Na tregoni çfarë ju duhet dhe le ta gjejmë zgjidhjen së bashku.</p>
          </div>
          <RouterLink to="/contact?purpose=offer" class="btn-primary shrink-0">Na kontaktoni <ArrowUpRight class="h-4 w-4" aria-hidden="true" /></RouterLink>
        </div>
      </div>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { ArrowUpRight } from 'lucide-vue-next';
import { useSeo } from '../../lib/seo';

const areas = [
  { title: 'Për shtëpinë', description: 'Rezervë energjie dhe zgjidhje solare për përditshmërinë.' },
  { title: 'Për biznesin', description: 'Zgjidhje energjetike për hapësira pune dhe ekipe profesionale.' },
  { title: 'Për aventurën', description: 'Energji portative për udhëtime dhe aktivitete në natyrë.' },
];

useSeo({
  title: 'Rreth EcoBuka',
  description: 'Mësoni më shumë për EcoBuka, distributor i produkteve EcoFlow për energji portative, karikim solar, rezervë për shtëpi dhe nevoja biznesi.',
  canonicalPath: '/about',
});
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell grid gap-8 py-10 sm:py-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-10 lg:py-14">
      <div><p class="label">Rreth nesh</p><h1 class="mt-2 text-3xl font-black sm:text-4xl">Distribuim i zgjidhjeve energjetike Eco Buka</h1></div>
      <div class="space-y-4 text-base leading-7 text-slate-600 sm:space-y-5 sm:text-lg sm:leading-8">
        <p>Eco Buka është distributor i produkteve EcoFlow dhe ofron zgjidhje premium për energji të pastër për shtëpi, biznese, përdorues në natyrë dhe ekipe profesionale.</p>
        <p>Platforma është e përgatitur për katalog produktesh, shportë, porosi manuale, kërkesë për ofertë dhe zgjerime të ardhshme si llogari klientësh, pagesa online dhe menaxhim inventari.</p>
      </div>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

useSeo({
  title: 'Rreth Eco Buka',
  description: 'Mësoni më shumë për Eco Buka, distributor i produkteve EcoFlow për energji portative, karikim solar, rezervë për shtëpi dhe nevoja biznesi.',
  canonicalPath: '/about',
});
</script>

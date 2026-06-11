<template>
  <WebsiteLayout>
    <section class="container-shell grid gap-8 py-10 sm:py-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-10 lg:py-14">
      <div><p class="label">About</p><h1 class="mt-2 text-3xl font-black sm:text-4xl">Eco Buka energy distribution</h1></div>
      <div class="space-y-4 text-base leading-7 text-slate-600 sm:space-y-5 sm:text-lg sm:leading-8">
        <p>Eco Buka is positioned as a legal distributor for premium clean-energy products, serving homes, businesses, outdoor users, and professional teams.</p>
        <p>The theme is ready for a polished catalogue, request-offer workflow, and future ecommerce features such as checkout, accounts, orders, and inventory.</p>
      </div>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

useSeo({
  title: 'About Eco Buka',
  description: 'Learn about Eco Buka, a clean-energy product distributor for portable power, solar charging, home backup, and business energy needs.',
  canonicalPath: '/about',
});
</script>

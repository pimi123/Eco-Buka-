<template>
  <WebsiteLayout>
    <section class="container-shell grid gap-8 py-10 sm:py-12 lg:grid-cols-2 lg:gap-10 lg:py-14">
      <div>
        <p class="label">Contact</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Request an offer</h1>
        <p class="mt-4 text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">Send a product request for home backup, solar generation, camping, office continuity, or business installation.</p>
      </div>
      <form class="grid min-w-0 gap-4 rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5">
        <input class="input-field" placeholder="Full name" />
        <input class="input-field" placeholder="Email or phone" />
        <select class="input-field"><option>Portable power station</option><option>Solar generator kit</option><option>Business backup solution</option></select>
        <textarea class="input-field min-h-32" placeholder="Tell us what you need powered" />
        <button class="btn-primary w-full sm:w-fit" type="button">Send Request</button>
      </form>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

useSeo({
  title: 'Request an Offer',
  description: 'Contact Eco Buka to request an offer for portable power stations, solar generator kits, home backup systems, and business energy solutions.',
  canonicalPath: '/contact',
});
</script>

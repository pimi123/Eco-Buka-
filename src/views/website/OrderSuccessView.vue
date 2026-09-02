<script setup lang="ts">
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

const route = useRoute();
const orderNumber = String(route.query.order || '');

useSeo({
  title: 'Porosia u dërgua',
  description: 'Kërkesa juaj për porosi në Eco Buka u dërgua me sukses.',
  canonicalPath: '/order-success',
});
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-12 sm:py-16">
      <div class="mx-auto max-w-2xl rounded-lg border border-line bg-white p-6 text-center shadow-sm sm:p-8">
        <p class="label">Porosia u pranua</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Kërkesa juaj për porosi u dërgua me sukses.</h1>
        <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">
          Ekipi ynë do tju kontaktojë së shpejti për të konfirmuar disponueshmërinë e produktit, detajet e dërgesës dhe hapat e radhës.
        </p>
        <p v-if="orderNumber" class="mt-6 rounded-lg bg-mist p-4 text-lg font-black">Numri i porosisë: {{ orderNumber }}</p>
        <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
          <RouterLink to="/products" class="btn-primary">Shiko produkte të tjera</RouterLink>
          <RouterLink to="/" class="btn-secondary">Kthehu në ballinë</RouterLink>
        </div>
      </div>
    </section>
  </WebsiteLayout>
</template>

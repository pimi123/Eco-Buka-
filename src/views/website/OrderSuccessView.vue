<script setup lang="ts">
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

const route = useRoute();
const orderNumber = String(route.query.order || '');

useSeo({
  title: 'Order placed',
  description: 'Your Eco Buka order request has been submitted successfully.',
  canonicalPath: '/order-success',
});
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-12 sm:py-16">
      <div class="mx-auto max-w-2xl rounded-lg border border-line bg-white p-6 text-center shadow-sm sm:p-8">
        <p class="label">Order received</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Your order has been placed successfully.</h1>
        <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">
          Our team will contact you shortly to confirm product availability, delivery details, and the next steps.
        </p>
        <p v-if="orderNumber" class="mt-6 rounded-lg bg-mist p-4 text-lg font-black">Order number: {{ orderNumber }}</p>
        <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
          <RouterLink to="/products" class="btn-primary">Continue browsing</RouterLink>
          <RouterLink to="/" class="btn-secondary">Back to home</RouterLink>
        </div>
      </div>
    </section>
  </WebsiteLayout>
</template>

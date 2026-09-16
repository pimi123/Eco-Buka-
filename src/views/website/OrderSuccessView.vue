<script setup lang="ts">
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

const route = useRoute();
const orderNumber = String(route.query.order || '');
const paymentStatus = String(route.query.payment || '');

useSeo({
  title: 'Pagesa u aprovua',
  description: 'Pagesa juaj në Eco Buka u aprovua dhe porosia u pranua me sukses.',
  canonicalPath: '/order-success',
});
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-12 sm:py-16">
      <div class="mx-auto max-w-2xl rounded-lg border border-line bg-white p-6 text-center shadow-sm sm:p-8">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-2xl font-black text-emerald-700">
          ✓
        </div>
        <p class="label mt-5">Pagesa u aprovua</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Porosia juaj u pranua me sukses.</h1>
        <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">
          Pagesa u verifikua nga banka dhe porosia është regjistruar në sistemin Eco Buka. Ekipi ynë do t'ju kontaktojë për dërgesën dhe detajet finale.
        </p>
        <div v-if="orderNumber || paymentStatus" class="mt-6 grid gap-3 rounded-lg bg-mist p-4 text-left">
          <p v-if="orderNumber" class="text-sm font-bold text-slate-600">
            Numri i porosisë
            <span class="mt-1 block text-lg font-black text-ink">{{ orderNumber }}</span>
          </p>
          <p v-if="paymentStatus" class="text-sm font-bold text-slate-600">
            Statusi i pagesës
            <span class="mt-1 block text-lg font-black text-emerald-700">Aprovuar</span>
          </p>
        </div>
        <p class="mt-4 rounded-lg border border-line bg-white p-4 text-sm leading-6 text-slate-600">
          Ruajeni numrin e porosisë. Për momentin porositë nuk kanë ende llogari klienti, prandaj ky numër përdoret për komunikim me ekipin tonë.
        </p>
        <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
          <RouterLink to="/products" class="btn-primary">Shiko produkte të tjera</RouterLink>
          <RouterLink to="/" class="btn-secondary">Kthehu në ballinë</RouterLink>
        </div>
      </div>
    </section>
  </WebsiteLayout>
</template>

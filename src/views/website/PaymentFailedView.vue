<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

const route = useRoute();
const orderNumber = String(route.query.order || '');
const paymentStatus = computed(() => String(route.query.payment || 'failed'));

const statusMessage = computed(() => {
  const messages: Record<string, string> = {
    declined: 'Pagesa u refuzua nga banka ose kartela nuk u aprovua.',
    error: 'Portali i pagesës raportoi një problem teknik gjatë autorizimit.',
    invalid_hash: 'Për arsye sigurie, përgjigjja e pagesës nuk mund të verifikohej.',
    incorrect_return_oid: 'Referenca e pagesës nuk përputhet me porosinë e pritur.',
    amount_mismatch: 'Shuma e kthyer nga banka nuk përputhet me shumën e porosisë.',
    currency_mismatch: 'Valuta e kthyer nga banka nuk përputhet me valutën e porosisë.',
    duplicate: 'Kjo përgjigje pagese është përpunuar më parë.',
    failed: 'Pagesa nuk u përfundua me sukses.',
  };

  return messages[paymentStatus.value] || messages.failed;
});

useSeo({
  title: 'Pagesa nuk u përfundua',
  description: 'Pagesa për porosinë Eco Buka nuk u përfundua. Provo përsëri ose kontakto ekipin tonë.',
  canonicalPath: '/payment-failed',
});
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-12 sm:py-16">
      <div class="mx-auto max-w-2xl rounded-lg border border-line bg-white p-6 text-center shadow-sm sm:p-8">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-2xl font-black text-red-700">
          !
        </div>
        <p class="label mt-5">Pagesa nuk u përfundua</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Pagesa nuk u aprovua.</h1>
        <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">
          {{ statusMessage }}
        </p>

        <div v-if="orderNumber || paymentStatus" class="mt-6 grid gap-3 rounded-lg bg-mist p-4 text-left">
          <p v-if="orderNumber" class="text-sm font-bold text-slate-600">
            Numri i porosisë
            <span class="mt-1 block text-lg font-black text-ink">{{ orderNumber }}</span>
          </p>
          <p class="text-sm font-bold text-slate-600">
            Statusi i pagesës
            <span class="mt-1 block text-lg font-black text-red-700">{{ paymentStatus }}</span>
          </p>
        </div>

        <p class="mt-4 rounded-lg border border-line bg-white p-4 text-sm leading-6 text-slate-600">
          Nëse shuma është rezervuar në kartelë, ju lutemi kontaktoni bankën ose ekipin Eco Buka para se të provoni përsëri.
        </p>

        <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
          <RouterLink to="/checkout" class="btn-primary">Provo përsëri</RouterLink>
          <RouterLink to="/cart" class="btn-secondary">Kthehu te shporta</RouterLink>
          <RouterLink to="/track-order" class="btn-secondary">Kontrollo porosinë</RouterLink>
          <RouterLink to="/contact" class="btn-secondary">Kontakto Eco Buka</RouterLink>
        </div>
      </div>
    </section>
  </WebsiteLayout>
</template>

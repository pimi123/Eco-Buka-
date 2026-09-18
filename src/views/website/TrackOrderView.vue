<script setup lang="ts">
import { reactive, ref } from 'vue';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import OrderSummaryCard from '../../components/orders/OrderSummaryCard.vue';
import { apiPost } from '../../lib/api';
import { useSeo } from '../../lib/seo';
import type { PublicOrder } from '../../types/order';

const loading = ref(false);
const errorMessage = ref('');
const order = ref<PublicOrder | null>(null);
const form = reactive({
  order_number: '',
  contact: '',
});

useSeo({
  title: 'Kontrollo porosinë',
  description: 'Kontrollo statusin e porosisë Eco Buka me numrin e porosisë dhe emailin ose telefonin.',
  canonicalPath: '/track-order',
});

async function submit() {
  if (loading.value) return;

  loading.value = true;
  errorMessage.value = '';
  order.value = null;

  try {
    order.value = await apiPost<PublicOrder>('/orders/track', {
      order_number: form.order_number.trim(),
      contact: form.contact.trim(),
    });
  } catch {
    errorMessage.value = 'Nuk u gjet porosia me këto të dhëna. Kontrolloni numrin e porosisë dhe emailin ose telefonin.';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-10 sm:py-14">
      <div class="mx-auto max-w-3xl">
        <p class="label">Statusi i porosisë</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Kontrollo porosinë</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
          Shkruani numrin e porosisë dhe emailin ose telefonin që keni përdorur gjatë checkout-it.
        </p>

        <form class="mt-8 grid gap-4 rounded-lg border border-line bg-white p-4 shadow-sm sm:p-6" @submit.prevent="submit">
          <p v-if="errorMessage" class="rounded-md border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700">
            {{ errorMessage }}
          </p>
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Numri i porosisë</span>
              <input v-model="form.order_number" class="input-field" type="text" placeholder="p.sh. ORD-20260916-0001" required>
            </label>
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Email ose telefon</span>
              <input v-model="form.contact" class="input-field" type="text" placeholder="email@example.com ose +383..." required>
            </label>
          </div>
          <button class="btn-primary min-h-12 w-full disabled:cursor-not-allowed disabled:opacity-50 sm:w-fit" :disabled="loading">
            {{ loading ? 'Duke kërkuar...' : 'Kontrollo statusin' }}
          </button>
        </form>

        <OrderSummaryCard v-if="order" class="mt-8" :order="order" />
      </div>
    </section>
  </WebsiteLayout>
</template>

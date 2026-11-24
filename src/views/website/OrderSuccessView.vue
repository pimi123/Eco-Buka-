<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { apiPost } from '../../lib/api';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

const route = useRoute();
const orderNumber = String(route.query.order || '');
const paymentStatus = String(route.query.payment || '');
const isCash = route.query.method === 'cash';
const verified = ref(false);
const checking = ref(!isCash);
const heading = computed(() => isCash ? 'Porosia juaj u pranua me sukses.' : verified.value ? 'Pagesa juaj u verifikua me sukses.' : checking.value ? 'Duke verifikuar pagesën…' : 'Pagesa nuk mund të verifikohet.');

onMounted(async () => {
  const token = typeof route.query.token === 'string' ? route.query.token : '';
  if (isCash || !orderNumber || !token) { checking.value = false; return; }
  try {
    // URL parameters alone are never proof of payment.
    await apiPost<{ id: number; total: string | number; currency: string }>(
      '/orders/purchase-confirmation', { order_number: orderNumber, token },
    );
    verified.value = true;
  } catch { /* Only the server payment service records Purchase. */ }
  finally { checking.value = false; }
});

useSeo({
  title: computed(() => heading.value),
  description: isCash ? 'Porosia u pranua. Pagesa bëhet në dorëzim.' : 'Pagesa juaj në EcoBuka u aprovua dhe porosia u pranua me sukses.',
  canonicalPath: '/order-success',
});
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-12 sm:py-16">
      <div class="mx-auto max-w-2xl rounded-lg border border-line bg-white p-6 text-center shadow-sm sm:p-8">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-2xl font-black text-emerald-700">
          {{ isCash || verified ? '✓' : checking ? '…' : '!' }}
        </div>
        <p class="label mt-5">{{ isCash ? 'Pagesë në dorëzim' : 'Verifikimi i pagesës' }}</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ heading }}</h1>
        <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base">
          {{ isCash ? 'Porosia është regjistruar. Pagesa bëhet me para në dorë kur ta pranoni porosinë.' : verified ? 'Pagesa u verifikua nga banka dhe porosia është regjistruar në sistemin EcoBuka.' : 'Statusi kontrollohet në server. Nëse keni përfunduar pagesën, na kontaktoni me numrin e porosisë.' }}
        </p>
        <div v-if="orderNumber || paymentStatus" class="mt-6 grid gap-3 rounded-lg bg-mist p-4 text-left">
          <p v-if="orderNumber" class="text-sm font-bold text-slate-600">
            Numri i porosisë
            <span class="mt-1 block text-lg font-black text-ink">{{ orderNumber }}</span>
          </p>
          <p v-if="paymentStatus && verified" class="text-sm font-bold text-slate-600">
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

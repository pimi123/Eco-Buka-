<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import OrderSummaryCard from '../../components/orders/OrderSummaryCard.vue';
import { apiGet } from '../../lib/api';
import { useSeo } from '../../lib/seo';
import { useAuthStore } from '../../stores/authStore';
import type { PublicOrder } from '../../types/order';

const authStore = useAuthStore();
const router = useRouter();
const loading = ref(false);
const loadingOrders = ref(false);
const message = ref('');
const errorMessage = ref('');
const orders = ref<PublicOrder[]>([]);

const initials = computed(() => {
  const name = authStore.user?.name || authStore.user?.email || 'E B';
  return name.split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase();
});

useSeo({
  title: 'Llogaria ime',
  description: 'Menaxho llogarinë Eco Buka dhe verifikimin e emailit.',
  canonicalPath: '/account',
});

onMounted(() => {
  fetchOrders();
});

async function fetchOrders() {
  loadingOrders.value = true;

  try {
    const response = await apiGet<{ orders: PublicOrder[] }>('/auth/orders');
    orders.value = response.orders;
  } catch {
    orders.value = [];
  } finally {
    loadingOrders.value = false;
  }
}

async function resendVerification() {
  if (loading.value) return;
  loading.value = true;
  message.value = '';
  errorMessage.value = '';

  try {
    const response = await authStore.resendVerification();
    message.value = response.message;
  } catch (error) {
    const response = (error as Error & { response?: { message?: string } }).response;
    errorMessage.value = response?.message || 'Emaili i verifikimit nuk u dërgua.';
  } finally {
    loading.value = false;
  }
}

async function logout() {
  await authStore.logout();
  router.push('/');
}
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-10 sm:py-14">
      <div class="max-w-4xl">
        <p class="label">Llogaria e klientit</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Llogaria ime</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
          Këtu ruhet identiteti i klientit për checkout dhe për lidhjen e porosive me llogarinë.
        </p>
      </div>

      <div class="mt-8 grid gap-5 lg:grid-cols-[1fr_320px]">
        <section class="rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
          <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            <div class="grid h-16 w-16 shrink-0 place-items-center rounded-full bg-ink text-lg font-black text-white">
              {{ initials }}
            </div>
            <div>
              <h2 class="text-2xl font-black">{{ authStore.user?.name }}</h2>
              <p class="mt-1 text-sm font-semibold text-slate-600">{{ authStore.user?.email }}</p>
              <p v-if="authStore.user?.phone" class="mt-1 text-sm font-semibold text-slate-600">{{ authStore.user.phone }}</p>
            </div>
          </div>

          <div class="mt-6 rounded-lg border p-4" :class="authStore.isEmailVerified ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'">
            <p class="text-sm font-black" :class="authStore.isEmailVerified ? 'text-emerald-800' : 'text-amber-900'">
              {{ authStore.isEmailVerified ? 'Emaili është i verifikuar' : 'Emaili ende nuk është i verifikuar' }}
            </p>
            <p class="mt-2 text-sm leading-6 text-slate-700">
              Verifikimi i emailit ndihmon që porositë të lidhen saktë me klientin dhe të ulet rreziku i porosive të pasakta.
            </p>
            <button v-if="!authStore.isEmailVerified" class="btn-secondary mt-4" type="button" :disabled="loading" @click="resendVerification">
              {{ loading ? 'Duke dërguar...' : 'Dërgo emailin e verifikimit' }}
            </button>
          </div>

          <p v-if="message" class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-800">{{ message }}</p>
          <p v-if="errorMessage" class="mt-4 rounded-md bg-red-50 p-3 text-sm font-semibold text-red-700">{{ errorMessage }}</p>
        </section>

        <aside class="h-fit rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
          <h2 class="text-lg font-black">Veprimet</h2>
          <div class="mt-4 grid gap-3">
            <RouterLink class="btn-primary" to="/checkout">Vazhdo në checkout</RouterLink>
            <RouterLink class="btn-secondary" to="/track-order">Kërko porosinë</RouterLink>
            <button class="btn-secondary" type="button" @click="logout">Dil nga llogaria</button>
          </div>
        </aside>
      </div>

      <section class="mt-8 rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="label">Porositë</p>
            <h2 class="mt-2 text-2xl font-black">Porositë e mia</h2>
          </div>
          <RouterLink class="btn-secondary" to="/products">Vazhdo blerjen</RouterLink>
        </div>

        <p v-if="loadingOrders" class="mt-6 rounded-md border border-line bg-mist p-4 text-sm font-semibold text-slate-600">
          Duke ngarkuar porositë...
        </p>
        <p v-else-if="!orders.length" class="mt-6 rounded-md border border-line bg-mist p-4 text-sm font-semibold text-slate-600">
          Ende nuk ka porosi të lidhura me këtë llogari.
        </p>
        <div v-else class="mt-6 grid gap-4">
          <OrderSummaryCard v-for="order in orders" :key="order.order_number" :order="order" />
        </div>
      </section>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { apiPost } from '../../lib/api';
import { deliveryCountries, municipalitiesForCountry } from '../../lib/locations';
import { useSeo } from '../../lib/seo';
import { useCartStore } from '../../stores/cartStore';
import type { CheckoutPayload, OrderResponse } from '../../types/order';

const router = useRouter();
const cartStore = useCartStore();
const loading = ref(false);
const errors = ref<Record<string, string[]>>({});
const form = reactive({
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  country: 'Kosove',
  municipality: '',
  delivery_address: '',
  postal_code: '',
  delivery_details: '',
  customer_note: '',
  policy_accepted: false,
});

const money = (value: number) => new Intl.NumberFormat('sq-XK', { style: 'currency', currency: 'EUR' }).format(value);
const hasItems = computed(() => cartStore.items.length > 0);
const hasUnavailableItems = computed(() => cartStore.items.some((item) => !cartStore.isProductInStock(item.product)));
const municipalityOptions = computed(() => municipalitiesForCountry(form.country));

useSeo({
  title: 'Checkout',
  description: 'Dërgo porosinë Eco Buka dhe ekipi ynë do tju kontaktojë për konfirmim, disponueshmëri dhe dërgesë.',
  canonicalPath: '/checkout',
});

watch(() => form.country, () => {
  form.municipality = '';
});

function fieldError(field: string) {
  return errors.value[field]?.[0] || '';
}

function optionEntries(options?: Record<string, string>) {
  return Object.entries(options || {}).filter(([, value]) => Boolean(value));
}

async function submitOrder() {
  if (loading.value || !hasItems.value || hasUnavailableItems.value) return;

  loading.value = true;
  errors.value = {};

  const payload: CheckoutPayload = {
    ...form,
    city: form.municipality,
    items: cartStore.items.map((item) => ({
      product_id: Number(item.product.id),
      quantity: item.quantity,
      selected_options: item.selected_options,
    })),
  };

  try {
    const response = await apiPost<OrderResponse>('/orders', payload);
    cartStore.clear();
    await router.push({ name: 'order-success', query: { order: response.order_number } });
  } catch (error) {
    const response = (error as Error & { response?: { errors?: Record<string, string[]>; message?: string } }).response;
    errors.value = response?.errors || { general: [response?.message || 'Porosia nuk mund te dergohet. Ju lutemi kontrolloni fushat dhe provoni perseri.'] };
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-8 sm:py-12">
      <div>
        <p class="label">Pa pagesë online</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Përfundimi i porosisë</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
          Dërgo kërkesën për porosi dhe ekipi ynë do tju kontaktojë për të konfirmuar disponueshmërinë, dërgesën dhe detajet finale.
        </p>
      </div>

      <div v-if="!hasItems" class="mt-8 rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Shporta juaj është e zbrazët. Shtoni produkte në shportë para checkout-it.
        <RouterLink to="/products" class="ml-2 font-bold text-ink underline underline-offset-4">Shiko produktet</RouterLink>
      </div>

      <div v-else class="mt-8 grid gap-6 lg:grid-cols-[1fr_380px]">
        <form class="grid gap-6 rounded-lg border border-line bg-white p-4 shadow-sm sm:p-6" @submit.prevent="submitOrder">
          <p v-if="fieldError('general')" class="rounded-md bg-red-50 p-3 text-sm font-semibold text-red-700">{{ fieldError('general') }}</p>
          <p v-if="hasUnavailableItems" class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm font-semibold text-amber-900">
            Shporta përmban produkte që nuk janë në stok. Ju lutemi largojini para dërgimit të porosisë.
          </p>

          <section class="grid gap-4">
            <div>
              <h2 class="text-lg font-black">Të dhënat e klientit</h2>
              <p class="mt-1 text-sm leading-6 text-slate-600">Këto të dhëna përdoren vetëm për konfirmimin e porosisë dhe kontaktin nga ekipi Eco Buka.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <label class="grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Emri dhe mbiemri</span>
                <input v-model="form.customer_name" class="input-field" type="text" autocomplete="name" placeholder="p.sh. Arben Krasniqi">
                <span v-if="fieldError('customer_name')" class="text-xs font-semibold text-red-600">{{ fieldError('customer_name') }}</span>
              </label>
              <label class="grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Telefoni</span>
                <input v-model="form.customer_phone" class="input-field" type="tel" autocomplete="tel" placeholder="+383 44 000 000">
                <span v-if="fieldError('customer_phone')" class="text-xs font-semibold text-red-600">{{ fieldError('customer_phone') }}</span>
              </label>
              <label class="grid gap-2 sm:col-span-2">
                <span class="text-xs font-bold uppercase text-slate-500">Email opsional</span>
                <input v-model="form.customer_email" class="input-field" type="email" autocomplete="email" placeholder="email@example.com">
                <span v-if="fieldError('customer_email')" class="text-xs font-semibold text-red-600">{{ fieldError('customer_email') }}</span>
              </label>
            </div>
          </section>

          <section class="grid gap-4 border-t border-line pt-5">
            <div>
              <h2 class="text-lg font-black">Adresa e dergeses</h2>
              <p class="mt-1 text-sm leading-6 text-slate-600">Zgjedh shtetin dhe qytetin/komunën, pastaj shkruaj adresën sa më saktë.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <label class="grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Shteti</span>
                <select v-model="form.country" class="input-field" autocomplete="country-name">
                  <option v-for="country in deliveryCountries" :key="country.value" :value="country.value">
                    {{ country.label }}
                  </option>
                </select>
                <span v-if="fieldError('country')" class="text-xs font-semibold text-red-600">{{ fieldError('country') }}</span>
              </label>
              <label class="grid gap-2">
                <span class="text-xs font-bold uppercase text-slate-500">Qyteti / Komuna</span>
                <select v-model="form.municipality" class="input-field" autocomplete="address-level2">
                  <option value="" disabled>Zgjedh qytetin</option>
                  <option v-for="municipality in municipalityOptions" :key="municipality" :value="municipality">
                    {{ municipality }}
                  </option>
                </select>
                <span v-if="fieldError('municipality') || fieldError('city')" class="text-xs font-semibold text-red-600">{{ fieldError('municipality') || fieldError('city') }}</span>
              </label>
            </div>
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Adresa e saktë</span>
              <input v-model="form.delivery_address" class="input-field" type="text" autocomplete="street-address" placeholder="Rruga, numri, lagjja">
              <span v-if="fieldError('delivery_address')" class="text-xs font-semibold text-red-600">{{ fieldError('delivery_address') }}</span>
            </label>
            <label class="grid gap-2 sm:max-w-xs">
              <span class="text-xs font-bold uppercase text-slate-500">Kodi postar opsional</span>
              <input v-model="form.postal_code" class="input-field" type="text" autocomplete="postal-code" placeholder="p.sh. 10000">
              <span v-if="fieldError('postal_code')" class="text-xs font-semibold text-red-600">{{ fieldError('postal_code') }}</span>
            </label>
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Detaje shtesë për dërgesë opsionale</span>
              <textarea v-model="form.delivery_details" class="input-field min-h-24" placeholder="Kati, hyrja, pika referuese ose orari i preferuar" />
            </label>
          </section>

          <section class="grid gap-4 border-t border-line pt-5">
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Shënim për porosinë opsional</span>
              <textarea v-model="form.customer_note" class="input-field min-h-24" placeholder="Pyetje ose kerkesa shtese per ekipin tone" />
            </label>
            <label class="flex items-start gap-3 rounded-lg border border-line bg-mist p-4 text-sm leading-6 text-slate-700">
              <input v-model="form.policy_accepted" class="mt-1 h-4 w-4 rounded border-line" type="checkbox">
              <span>
                Pajtohem që Eco Buka të më kontaktojë për këtë porosi dhe pranoj
                <RouterLink class="font-bold text-ink underline underline-offset-4" to="/politika-e-kthimit">Politikën e Kthimit</RouterLink>.
              </span>
            </label>
            <span v-if="fieldError('policy_accepted')" class="text-xs font-semibold text-red-600">{{ fieldError('policy_accepted') }}</span>
            <button class="btn-primary min-h-12 w-full disabled:cursor-not-allowed disabled:opacity-50 sm:w-fit" :disabled="loading || hasUnavailableItems">
              {{ loading ? 'Duke dërguar porosinë...' : 'Dërgo porosinë' }}
            </button>
          </section>
        </form>

        <aside class="h-fit rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5">
          <h2 class="text-lg font-black">Përmbledhja e porosisë</h2>
          <div class="mt-4 grid gap-4">
            <div v-for="item in cartStore.items" :key="item.key" class="grid grid-cols-[64px_1fr] gap-3 border-b border-line pb-4 last:border-b-0 last:pb-0">
              <img class="h-16 w-16 rounded-md bg-mist object-contain p-2" :src="item.product.image_url || '/promo/optimized/summer-sale-1280.jpg'" :alt="item.product.name">
              <div class="min-w-0">
                <p class="line-clamp-2 text-sm font-bold">{{ item.product.name }}</p>
                <p v-if="!cartStore.isProductInStock(item.product)" class="mt-1 text-xs font-black uppercase tracking-wide text-red-600">Nuk është në stok</p>
                <div v-if="optionEntries(item.selected_options).length" class="mt-2 grid gap-1">
                  <p v-for="[label, value] in optionEntries(item.selected_options)" :key="`${item.key}-${label}`" class="text-xs font-semibold text-slate-500">
                    {{ label }}: {{ value }}
                  </p>
                </div>
                <div class="mt-2 flex items-center justify-between gap-3">
                  <input class="w-20 rounded-md border border-line px-2 py-1 text-sm" type="number" min="1" max="99" :value="item.quantity" @input="cartStore.updateQuantity(item.key, Number(($event.target as HTMLInputElement).value))">
                  <button class="text-xs font-bold text-red-600" type="button" @click="cartStore.remove(item.key)">Largo</button>
                </div>
                <p class="mt-2 text-sm font-black">{{ money(Number(item.product.price || 0) * item.quantity) }}</p>
              </div>
            </div>
          </div>
          <div class="mt-5 flex items-center justify-between border-t border-line pt-4">
            <span class="font-bold">Nëntotali</span>
            <span class="text-xl font-black">{{ money(cartStore.subtotal) }}</span>
          </div>
        </aside>
      </div>
    </section>
  </WebsiteLayout>
</template>

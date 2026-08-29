<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { apiPost } from '../../lib/api';
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
  delivery_address: '',
  city: '',
  delivery_details: '',
  customer_note: '',
});

const money = (value: number) => new Intl.NumberFormat('en-EU', { style: 'currency', currency: 'EUR' }).format(value);
const hasItems = computed(() => cartStore.items.length > 0);
const hasUnavailableItems = computed(() => cartStore.items.some((item) => !cartStore.isProductInStock(item.product)));

useSeo({
  title: 'Checkout',
  description: 'Place an Eco Buka order without online payment. Our team will contact you to confirm the details.',
  canonicalPath: '/checkout',
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
    errors.value = response?.errors || { general: [response?.message || 'Order could not be submitted. Please check the form and try again.'] };
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-8 sm:py-12">
      <div>
        <p class="label">No online payment</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Checkout</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
          Submit your order request and our team will contact you to confirm availability, delivery, and final details.
        </p>
      </div>

      <div v-if="!hasItems" class="mt-8 rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Your cart is empty. Browse products and choose Add to Cart before checkout.
        <RouterLink to="/products" class="ml-2 font-bold text-ink underline underline-offset-4">Browse products</RouterLink>
      </div>

      <div v-else class="mt-8 grid gap-6 lg:grid-cols-[1fr_380px]">
        <form class="grid gap-4 rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5" @submit.prevent="submitOrder">
          <p v-if="fieldError('general')" class="rounded-md bg-red-50 p-3 text-sm font-semibold text-red-700">{{ fieldError('general') }}</p>
          <p v-if="hasUnavailableItems" class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm font-semibold text-amber-900">
            Your cart contains out-of-stock products. Please remove them before submitting the order.
          </p>

          <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Full name</span>
              <input v-model="form.customer_name" class="input-field" type="text" autocomplete="name">
              <span v-if="fieldError('customer_name')" class="text-xs font-semibold text-red-600">{{ fieldError('customer_name') }}</span>
            </label>
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Phone</span>
              <input v-model="form.customer_phone" class="input-field" type="tel" autocomplete="tel">
              <span v-if="fieldError('customer_phone')" class="text-xs font-semibold text-red-600">{{ fieldError('customer_phone') }}</span>
            </label>
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Email optional</span>
              <input v-model="form.customer_email" class="input-field" type="email" autocomplete="email">
              <span v-if="fieldError('customer_email')" class="text-xs font-semibold text-red-600">{{ fieldError('customer_email') }}</span>
            </label>
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">City</span>
              <input v-model="form.city" class="input-field" type="text" autocomplete="address-level2">
              <span v-if="fieldError('city')" class="text-xs font-semibold text-red-600">{{ fieldError('city') }}</span>
            </label>
          </div>

          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Delivery address</span>
            <input v-model="form.delivery_address" class="input-field" type="text" autocomplete="street-address">
            <span v-if="fieldError('delivery_address')" class="text-xs font-semibold text-red-600">{{ fieldError('delivery_address') }}</span>
          </label>

          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Delivery details optional</span>
            <textarea v-model="form.delivery_details" class="input-field min-h-24" />
          </label>

          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Customer note optional</span>
            <textarea v-model="form.customer_note" class="input-field min-h-24" />
          </label>

          <button class="btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50 sm:w-fit" :disabled="loading || hasUnavailableItems">
            {{ loading ? 'Submitting order...' : 'Submit Order' }}
          </button>
        </form>

        <aside class="h-fit rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5">
          <h2 class="text-lg font-black">Order summary</h2>
          <div class="mt-4 grid gap-4">
            <div v-for="item in cartStore.items" :key="item.key" class="grid grid-cols-[64px_1fr] gap-3 border-b border-line pb-4 last:border-b-0 last:pb-0">
              <img class="h-16 w-16 rounded-md bg-mist object-contain p-2" :src="item.product.image_url || '/promo/optimized/summer-sale-1280.jpg'" :alt="item.product.name">
              <div class="min-w-0">
                <p class="line-clamp-2 text-sm font-bold">{{ item.product.name }}</p>
                <p v-if="!cartStore.isProductInStock(item.product)" class="mt-1 text-xs font-black uppercase tracking-wide text-red-600">Out of stock</p>
                <div v-if="optionEntries(item.selected_options).length" class="mt-2 grid gap-1">
                  <p v-for="[label, value] in optionEntries(item.selected_options)" :key="`${item.key}-${label}`" class="text-xs font-semibold text-slate-500">
                    {{ label }}: {{ value }}
                  </p>
                </div>
                <div class="mt-2 flex items-center justify-between gap-3">
                  <input class="w-20 rounded-md border border-line px-2 py-1 text-sm" type="number" min="1" max="99" :value="item.quantity" @input="cartStore.updateQuantity(item.key, Number(($event.target as HTMLInputElement).value))">
                  <button class="text-xs font-bold text-red-600" type="button" @click="cartStore.remove(item.key)">Remove</button>
                </div>
                <p class="mt-2 text-sm font-black">{{ money(Number(item.product.price || 0) * item.quantity) }}</p>
              </div>
            </div>
          </div>
          <div class="mt-5 flex items-center justify-between border-t border-line pt-4">
            <span class="font-bold">Subtotal</span>
            <span class="text-xl font-black">{{ money(cartStore.subtotal) }}</span>
          </div>
        </aside>
      </div>
    </section>
  </WebsiteLayout>
</template>

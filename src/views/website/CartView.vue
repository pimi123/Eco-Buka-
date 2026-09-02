<script setup lang="ts">
import { computed } from 'vue';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';
import { useCartStore } from '../../stores/cartStore';

const cartStore = useCartStore();
const fallbackUrl = '/promo/optimized/summer-sale-1280.jpg';
const hasItems = computed(() => cartStore.items.length > 0);
const hasUnavailableItems = computed(() => cartStore.items.some((item) => !cartStore.isProductInStock(item.product)));

const money = (value: number) => new Intl.NumberFormat('sq-XK', { style: 'currency', currency: 'EUR' }).format(value);

function optionEntries(options?: Record<string, string>) {
  return Object.entries(options || {}).filter(([, value]) => Boolean(value));
}

useSeo({
  title: 'Shporta',
  description: 'Kontrolloni produktet në shportën Eco Buka para se të vazhdoni me porosinë.',
  canonicalPath: '/cart',
});
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell py-8 sm:py-12">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="label">Kontrollo porosinë</p>
          <h1 class="mt-2 text-3xl font-black sm:text-4xl">Shporta</h1>
          <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
            Kontrolloni produktet e zgjedhura para se ta dërgoni kërkesën për porosi. Pagesa koordinohet pas konfirmimit nga ekipi ynë.
          </p>
        </div>
        <RouterLink to="/products" class="btn-secondary w-full sm:w-auto">Vazhdo blerjen</RouterLink>
      </div>

      <div v-if="!hasItems" class="mt-8 rounded-lg border border-line bg-white p-6 text-sm font-semibold text-slate-600">
        Shporta juaj është e zbrazët. Shtoni produkte dhe pastaj kthehuni këtu për t'i kontrolluar.
      </div>

      <div v-else class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="grid gap-4">
          <article
            v-for="item in cartStore.items"
            :key="item.key"
            class="grid gap-4 rounded-lg border border-line bg-white p-4 shadow-sm sm:grid-cols-[112px_1fr_auto] sm:items-center sm:p-5"
          >
            <RouterLink :to="`/products/${item.product.slug}`" class="block overflow-hidden rounded-md bg-mist">
              <img
                class="aspect-square w-full object-contain p-3 sm:h-28"
                :src="item.product.image_url || fallbackUrl"
                :alt="item.product.name"
                loading="lazy"
                decoding="async"
              >
            </RouterLink>

            <div class="min-w-0">
              <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ item.product.category || 'Produkt' }}</p>
              <RouterLink :to="`/products/${item.product.slug}`" class="mt-1 block text-lg font-black leading-6 text-ink hover:underline">
                {{ item.product.name }}
              </RouterLink>
              <p v-if="item.product.short_description" class="mt-2 line-clamp-2 text-sm leading-6 text-slate-600">
                {{ item.product.short_description }}
              </p>
              <p v-if="!cartStore.isProductInStock(item.product)" class="mt-2 inline-flex rounded-full bg-slate-950 px-3 py-1 text-xs font-black uppercase tracking-wide text-white">
                Nuk është në stok
              </p>
              <div v-if="optionEntries(item.selected_options).length" class="mt-3 flex flex-wrap gap-2">
                <span
                  v-for="[label, value] in optionEntries(item.selected_options)"
                  :key="`${item.key}-${label}`"
                  class="rounded-md bg-mist px-2 py-1 text-xs font-semibold text-slate-600"
                >
                  {{ label }}: {{ value }}
                </span>
              </div>
            </div>

            <div class="grid gap-3 sm:min-w-40 sm:justify-items-end">
              <p class="text-lg font-black">{{ money(Number(item.product.price || 0) * item.quantity) }}</p>
              <label class="grid gap-1">
                <span class="text-xs font-bold uppercase text-slate-500">Sasia</span>
                <input
                  class="input-field w-24"
                  type="number"
                  min="1"
                  max="99"
                  :value="item.quantity"
                  @input="cartStore.updateQuantity(item.key, Number(($event.target as HTMLInputElement).value))"
                >
              </label>
              <button class="text-sm font-bold text-red-600 hover:underline" type="button" @click="cartStore.remove(item.key)">
                Largo
              </button>
            </div>
          </article>
        </div>

        <aside class="h-fit rounded-lg border border-line bg-white p-5 shadow-sm">
          <h2 class="text-lg font-black">Përmbledhja e shportës</h2>
          <div class="mt-4 grid gap-3 text-sm">
            <div class="flex items-center justify-between gap-4">
              <span class="text-slate-600">Produkte</span>
              <span class="font-bold">{{ cartStore.count }}</span>
            </div>
            <div class="flex items-center justify-between gap-4 border-t border-line pt-3">
              <span class="font-bold">Nëntotali</span>
              <span class="text-xl font-black">{{ money(cartStore.subtotal) }}</span>
            </div>
          </div>
          <p v-if="hasUnavailableItems" class="mt-5 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm font-semibold text-amber-900">
            Largoni produktet që nuk janë në stok para checkout-it.
          </p>
          <RouterLink
            v-if="!hasUnavailableItems"
            to="/checkout"
            class="btn-primary mt-5 w-full"
          >
            Vazhdo në checkout
          </RouterLink>
          <button v-else class="btn-primary mt-3 w-full cursor-not-allowed opacity-50" type="button" disabled>
            Vazhdo në checkout
          </button>
          <RouterLink to="/products" class="btn-secondary mt-3 w-full">Vazhdo blerjen</RouterLink>
        </aside>
      </div>
    </section>
  </WebsiteLayout>
</template>

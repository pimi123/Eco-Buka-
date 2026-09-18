<script setup lang="ts">
import type { PublicOrder } from '../../types/order';

defineProps<{
  order: PublicOrder;
}>();

const money = (value: string | number, currency = 'EUR') => new Intl.NumberFormat('sq-XK', {
  style: 'currency',
  currency,
}).format(Number(value || 0));

function optionEntries(options?: Record<string, string> | null) {
  return Object.entries(options || {}).filter(([, value]) => Boolean(value));
}
</script>

<template>
  <article class="rounded-lg border border-line bg-white p-4 text-left shadow-sm sm:p-6">
    <div class="grid gap-4 border-b border-line pb-5 sm:grid-cols-[1fr_auto] sm:items-start">
      <div>
        <p class="label">Përmbledhja e porosisë</p>
        <h2 class="mt-1 text-2xl font-black">{{ order.order_number }}</h2>
        <p v-if="order.created_at" class="mt-2 text-sm text-slate-500">
          Data: {{ new Date(order.created_at).toLocaleDateString('sq-XK') }}
        </p>
      </div>
      <div class="grid gap-2 sm:min-w-48">
        <p class="rounded-full bg-mist px-3 py-1.5 text-sm font-black text-ink">
          {{ order.status_label }}
        </p>
        <p class="rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-black text-emerald-700">
          Pagesa: {{ order.payment_status_label }}
        </p>
      </div>
    </div>

    <div class="mt-5 grid gap-4">
      <div
        v-for="item in order.items"
        :key="`${order.order_number}-${item.name}`"
        class="grid grid-cols-[72px_1fr] gap-3 rounded-lg border border-line bg-white p-3 sm:grid-cols-[88px_1fr_auto]"
      >
        <img
          class="h-20 w-20 rounded-md bg-mist object-contain p-2 sm:h-24 sm:w-24"
          :src="item.image_url || '/promo/optimized/summer-sale-1280.jpg'"
          :alt="item.name"
          loading="lazy"
        >
        <div class="min-w-0">
          <p v-if="item.category" class="text-xs font-black uppercase tracking-wide text-slate-500">{{ item.category }}</p>
          <h3 class="mt-1 line-clamp-2 text-sm font-black sm:text-base">{{ item.name }}</h3>
          <p class="mt-1 text-sm font-semibold text-slate-600">Sasia: {{ item.quantity }}</p>
          <div v-if="optionEntries(item.selected_options).length" class="mt-2 grid gap-1">
            <p v-for="[label, value] in optionEntries(item.selected_options)" :key="label" class="text-xs font-semibold text-slate-500">
              {{ label }}: {{ value }}
            </p>
          </div>
        </div>
        <div class="col-span-2 flex items-center justify-between border-t border-line pt-3 sm:col-span-1 sm:block sm:border-t-0 sm:pt-0 sm:text-right">
          <p class="text-xs font-bold text-slate-500">{{ money(item.unit_price, order.currency) }} / copë</p>
          <p class="text-base font-black">{{ money(item.line_total, order.currency) }}</p>
        </div>
      </div>
    </div>

    <div class="mt-5 grid gap-3 border-t border-line pt-5 sm:grid-cols-[1fr_auto] sm:items-end">
      <div class="text-sm leading-6 text-slate-600">
        <p v-if="order.customer_name"><strong>Klienti:</strong> {{ order.customer_name }}</p>
        <p v-if="order.delivery_area"><strong>Zona e dërgesës:</strong> {{ order.delivery_area }}</p>
      </div>
      <div class="rounded-lg bg-mist p-4 text-right">
        <p class="text-sm font-bold text-slate-600">Totali</p>
        <p class="text-2xl font-black">{{ money(order.total, order.currency) }}</p>
      </div>
    </div>
  </article>
</template>

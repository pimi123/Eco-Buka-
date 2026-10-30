<script setup lang="ts">
import { computed } from 'vue';
import { discountPercent } from '../../lib/discount';

const props = withDefaults(defineProps<{ price?: number | null; oldPrice?: number | null; inStock?: boolean }>(), { inStock: true });
const discounted = computed(() => props.inStock && Number(props.price) > 0 && Number(props.oldPrice) > Number(props.price));
const percent = computed(() => discountPercent(props.price, props.oldPrice));
const money = (value: number) => new Intl.NumberFormat('sq-XK', { style: 'currency', currency: 'EUR' }).format(value);
</script>

<template>
  <div class="min-w-0">
    <p class="text-base font-black leading-tight min-[390px]:text-lg sm:text-2xl" :class="discounted ? 'text-red-700' : 'text-ink'">
      {{ price ? money(Number(price)) : 'Çmimi sipas kërkesës' }}
    </p>
    <div v-if="discounted" class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
      <span class="sr-only">Çmimi i mëparshëm</span>
      <del class="text-xs font-medium text-slate-500 sm:text-sm">{{ money(Number(oldPrice)) }}</del>
      <span v-if="percent" class="rounded-md bg-red-50 px-1.5 py-1 text-[10px] font-bold leading-none text-red-700 sm:text-xs">Kurse {{ money(Number(oldPrice) - Number(price)) }}</span>
    </div>
  </div>
</template>

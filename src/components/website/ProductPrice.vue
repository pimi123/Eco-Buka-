<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{ price?: number | null; oldPrice?: number | null; inStock?: boolean }>(), { inStock: true });
const discounted = computed(() => props.inStock && Number(props.price) > 0 && Number(props.oldPrice) > Number(props.price));
const money = (value: number) => new Intl.NumberFormat('sq-XK', { style: 'currency', currency: 'EUR' }).format(value);
</script>

<template>
  <div class="min-w-0">
    <p class="break-words text-base font-black leading-normal min-[390px]:text-lg sm:text-2xl" :class="discounted ? 'text-red-700' : 'text-ink'">
      {{ price ? money(Number(price)) : 'Çmimi sipas kërkesës' }}
    </p>
    <div v-if="discounted" class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
      <span class="sr-only">Çmimi i mëparshëm</span>
      <del class="text-xs font-medium text-slate-500 sm:text-sm">{{ money(Number(oldPrice)) }}</del>
    </div>
  </div>
</template>

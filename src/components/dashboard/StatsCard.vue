<script setup lang="ts">
defineProps<{ label: string; value: string | number; detail?: string }>();
</script>

<template>
  <div class="min-w-0 rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5">
    <p class="text-sm font-semibold text-slate-500">{{ label }}</p>
    <p class="mt-3 text-2xl font-black sm:text-3xl">{{ value }}</p>
    <p v-if="detail" class="mt-2 text-sm text-slate-500">{{ detail }}</p>
  </div>
</template>

<script setup lang="ts">
import type { Product } from '../../types/product';
import ProductCard from './ProductCard.vue';

defineProps<{ products: Product[] }>();
</script>

<template>
  <div class="grid min-w-0 grid-cols-1 gap-3 min-[340px]:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 lg:gap-5">
    <ProductCard v-for="product in products" :key="product.id" :product="product" />
  </div>
</template>

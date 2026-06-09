<script setup lang="ts">
import type { Product } from '../../types/product';
import ProductCard from './ProductCard.vue';

defineProps<{ products: Product[] }>();
</script>

<template>
  <div class="grid min-w-0 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:gap-5">
    <ProductCard v-for="product in products" :key="product.id" :product="product" />
  </div>
</template>

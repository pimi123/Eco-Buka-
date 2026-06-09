<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ProductForm from '../../components/dashboard/ProductForm.vue';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import { useCategoryStore } from '../../stores/categoryStore';
import { useProductStore } from '../../stores/productStore';

const route = useRoute();
const router = useRouter();
const productStore = useProductStore();
const categoryStore = useCategoryStore();
const product = computed(() => productStore.products.find((item) => item.id === route.params.id));
const errorMessage = ref('');

onMounted(async () => {
  await Promise.all([productStore.fetchProducts(true), categoryStore.fetchCategories()]);
});

async function save(payload: any) {
  errorMessage.value = '';

  try {
    await productStore.saveProduct({ ...payload, id: route.params.id as string });
    router.push('/dashboard/products');
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Product could not be saved.';
  }
}
</script>

<template>
  <DashboardLayout>
    <h1 class="mb-4 text-xl font-black sm:text-2xl">Edit product</h1>
    <div v-if="errorMessage" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-semibold leading-6 text-red-700">
      {{ errorMessage }}
    </div>
    <ProductForm v-if="product" :product="product" :categories="categoryStore.categories" @save="save" />
  </DashboardLayout>
</template>

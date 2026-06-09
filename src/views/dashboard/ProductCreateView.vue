<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import ProductForm from '../../components/dashboard/ProductForm.vue';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import { hasSupabaseConfig } from '../../lib/supabase';
import { useCategoryStore } from '../../stores/categoryStore';
import { useProductStore } from '../../stores/productStore';

const router = useRouter();
const productStore = useProductStore();
const categoryStore = useCategoryStore();
const errorMessage = ref('');
const saving = ref(false);

onMounted(() => categoryStore.fetchCategories());

async function save(product: any) {
  errorMessage.value = '';
  saving.value = true;

  try {
    await productStore.saveProduct(product);
    router.push('/dashboard/products');
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Product could not be saved.';
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <DashboardLayout>
    <h1 class="mb-4 text-xl font-black sm:text-2xl">Add product</h1>
    <div v-if="!hasSupabaseConfig" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-800">
      Supabase is not configured, so products are saved in this browser only. Add a `.env` file to store products in the database.
    </div>
    <div v-if="errorMessage" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-semibold leading-6 text-red-700">
      {{ errorMessage }}
    </div>
    <div v-if="saving" class="mb-4 rounded-lg border border-line bg-white p-4 text-sm font-semibold text-slate-500">
      Saving product...
    </div>
    <ProductForm :categories="categoryStore.categories" @save="save" />
  </DashboardLayout>
</template>

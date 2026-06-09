<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import DataTable from '../../components/dashboard/DataTable.vue';
import StatsCard from '../../components/dashboard/StatsCard.vue';
import { useCategoryStore } from '../../stores/categoryStore';
import { useProductStore } from '../../stores/productStore';

const productStore = useProductStore();
const categoryStore = useCategoryStore();

onMounted(async () => {
  await Promise.all([productStore.fetchProducts(true), categoryStore.fetchCategories()]);
});
</script>

<template>
  <DashboardLayout>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatsCard label="Total products" :value="productStore.products.length" detail="All catalogue items" />
      <StatsCard label="Total categories" :value="categoryStore.categories.length" detail="Active shop groups" />
      <StatsCard label="Active products" :value="productStore.products.filter((p) => p.active).length" detail="Visible on website" />
      <StatsCard label="Hidden products" :value="productStore.products.filter((p) => !p.active).length" detail="Draft or inactive" />
    </div>
    <div class="mt-6">
      <h2 class="mb-3 text-xl font-black">Recent products</h2>
      <DataTable>
        <thead class="bg-mist text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Status</th></tr></thead>
        <tbody class="divide-y divide-line">
          <tr v-for="product in productStore.products.slice(0, 5)" :key="product.id">
            <td class="px-4 py-3 font-semibold">{{ product.name }}</td><td class="px-4 py-3">{{ product.category }}</td><td class="px-4 py-3">€{{ product.price }}</td><td class="px-4 py-3">{{ product.active ? 'Active' : 'Hidden' }}</td>
          </tr>
        </tbody>
      </DataTable>
    </div>
  </DashboardLayout>
</template>

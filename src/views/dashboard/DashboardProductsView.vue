<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import DataTable from '../../components/dashboard/DataTable.vue';
import { useProductStore } from '../../stores/productStore';
import fallbackUrl from '../../assets/eco-buka-hero.png';

const productStore = useProductStore();
onMounted(() => productStore.fetchProducts(true));
</script>

<template>
  <DashboardLayout>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h1 class="text-2xl font-black">Products</h1>
      <RouterLink to="/dashboard/products/new" class="btn-primary w-full sm:w-auto">Add Product</RouterLink>
    </div>
    <DataTable>
      <thead class="bg-mist text-xs uppercase text-slate-500">
        <tr><th class="px-4 py-3">Image</th><th class="px-4 py-3">Name</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Featured</th><th class="px-4 py-3">Actions</th></tr>
      </thead>
      <tbody class="divide-y divide-line">
        <tr v-for="product in productStore.products" :key="product.id">
          <td class="px-4 py-3"><img :src="product.image_url || fallbackUrl" :alt="product.name" class="h-12 w-16 rounded-md object-cover" /></td>
          <td class="px-4 py-3 font-semibold">{{ product.name }}</td>
          <td class="px-4 py-3">{{ product.category }}</td>
          <td class="px-4 py-3">€{{ product.price }}</td>
          <td class="px-4 py-3">{{ product.active ? 'Active' : 'Hidden' }}</td>
          <td class="px-4 py-3">{{ product.featured ? 'Yes' : 'No' }}</td>
          <td class="px-4 py-3">
            <div class="flex flex-wrap gap-2">
              <RouterLink :to="`/dashboard/products/${product.id}/edit`" class="btn-secondary min-h-9 px-3 py-1.5">Edit</RouterLink>
              <button class="btn-secondary min-h-9 px-3 py-1.5" @click="productStore.toggleActive(product)">Toggle</button>
              <button class="btn-secondary min-h-9 px-3 py-1.5" @click="productStore.deleteProduct(product.id)">Delete</button>
            </div>
          </td>
        </tr>
      </tbody>
    </DataTable>
  </DashboardLayout>
</template>

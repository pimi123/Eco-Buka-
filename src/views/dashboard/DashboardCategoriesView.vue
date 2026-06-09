<script setup lang="ts">
import { onMounted } from 'vue';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import DataTable from '../../components/dashboard/DataTable.vue';
import { useCategoryStore } from '../../stores/categoryStore';

const categoryStore = useCategoryStore();
onMounted(() => categoryStore.fetchCategories());
</script>

<template>
  <DashboardLayout>
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <h1 class="text-2xl font-black">Categories</h1>
      <RouterLink to="/dashboard/categories/new" class="btn-primary w-full sm:w-auto">Add Category</RouterLink>
    </div>
    <DataTable>
      <thead class="bg-mist text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Label</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Actions</th></tr></thead>
      <tbody class="divide-y divide-line">
        <tr v-for="category in categoryStore.categories" :key="category.id">
          <td class="px-4 py-3 font-semibold">{{ category.name }}</td><td class="px-4 py-3">{{ category.slug }}</td><td class="px-4 py-3">{{ category.is_new ? 'New' : '-' }}</td><td class="px-4 py-3">{{ category.active ? 'Active' : 'Hidden' }}</td>
          <td class="px-4 py-3"><button class="btn-secondary min-h-9 px-3 py-1.5" @click="categoryStore.deleteCategory(category.id)">Delete</button></td>
        </tr>
      </tbody>
    </DataTable>
  </DashboardLayout>
</template>

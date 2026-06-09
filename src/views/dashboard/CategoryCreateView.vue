<script setup lang="ts">
import { useRouter } from 'vue-router';
import CategoryForm from '../../components/dashboard/CategoryForm.vue';
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import { useCategoryStore } from '../../stores/categoryStore';

const router = useRouter();
const categoryStore = useCategoryStore();

async function save(category: any) {
  await categoryStore.saveCategory(category);
  router.push('/dashboard/categories');
}
</script>

<template>
  <DashboardLayout>
    <h1 class="mb-4 text-xl font-black sm:text-2xl">Add category</h1>
    <CategoryForm @save="save" />
  </DashboardLayout>
</template>

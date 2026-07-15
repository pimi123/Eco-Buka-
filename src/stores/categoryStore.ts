import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../lib/api';
import { demoCategories } from '../lib/demoData';
import type { Category } from '../types/category';

export const useCategoryStore = defineStore('categories', () => {
  const categories = ref<Category[]>([]);
  const loading = ref(false);

  const activeCategories = computed(() => categories.value.filter((category) => category.active));

  async function fetchCategories() {
    loading.value = true;
    if (hasLaravelApiConfig) {
      try {
        categories.value = await apiGet<Category[]>('/categories');
        loading.value = false;
        return;
      } catch {
        // Fall through to demo content so the storefront stays usable offline.
      }
    }

    categories.value = demoCategories;
    loading.value = false;
  }

  async function saveCategory(category: Partial<Category>) {
    const id = category.id || crypto.randomUUID();
    const next = { active: true, is_new: false, ...category, id } as Category;
    categories.value = categories.value.some((item) => item.id === id)
      ? categories.value.map((item) => (item.id === id ? next : item))
      : [next, ...categories.value.filter((item) => item.id !== id)];
    return next;
  }

  async function deleteCategory(id: string) {
    categories.value = categories.value.filter((category) => category.id !== id);
  }

  return { categories, activeCategories, loading, fetchCategories, saveCategory, deleteCategory };
});

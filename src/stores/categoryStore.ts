import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../lib/api';
import { demoCategories } from '../lib/demoData';
import { hasSupabaseConfig, supabase } from '../lib/supabase';
import type { Category } from '../types/category';

const localCategoriesKey = 'eco-buka-categories';

function readLocalCategories() {
  try {
    const stored = localStorage.getItem(localCategoriesKey);
    return stored ? (JSON.parse(stored) as Category[]) : [];
  } catch {
    return [];
  }
}

function writeLocalCategories(categories: Category[]) {
  localStorage.setItem(localCategoriesKey, JSON.stringify(categories));
}

function cleanCategoryPayload(category: Partial<Category>) {
  const next = { ...category };
  if (next.image_url === '') next.image_url = null;
  return next;
}

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
        // Fall through to the configured local/Supabase strategy.
      }
    }

    if (!hasSupabaseConfig || !supabase) {
      const localCategories = readLocalCategories();
      categories.value = [
        ...localCategories,
        ...demoCategories.filter((demoCategory) => !localCategories.some((localCategory) => localCategory.id === demoCategory.id)),
      ];
      loading.value = false;
      return;
    }

    const { data, error } = await supabase.from('categories').select('*').order('created_at');
    categories.value = error ? demoCategories : (data as Category[]);
    loading.value = false;
  }

  async function saveCategory(category: Partial<Category>) {
    const payload = cleanCategoryPayload(category);

    if (!hasSupabaseConfig || !supabase) {
      const id = payload.id || crypto.randomUUID();
      const next = { active: true, is_new: false, ...payload, id } as Category;
      const localCategories = readLocalCategories();
      const savedCategories = localCategories.some((item) => item.id === id)
        ? localCategories.map((item) => (item.id === id ? next : item))
        : [next, ...localCategories];

      writeLocalCategories(savedCategories);
      categories.value = categories.value.some((item) => item.id === id)
        ? categories.value.map((item) => (item.id === id ? next : item))
        : [next, ...categories.value.filter((item) => item.id !== id)];
      return next;
    }

    const { data, error } = await supabase.from('categories').upsert(payload).select().single();
    if (error) throw error;
    await fetchCategories();
    return data as Category;
  }

  async function deleteCategory(id: string) {
    if (hasSupabaseConfig && supabase) await supabase.from('categories').delete().eq('id', id);
    if (!hasSupabaseConfig || !supabase) writeLocalCategories(readLocalCategories().filter((category) => category.id !== id));
    categories.value = categories.value.filter((category) => category.id !== id);
  }

  return { categories, activeCategories, loading, fetchCategories, saveCategory, deleteCategory };
});

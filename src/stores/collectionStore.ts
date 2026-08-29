import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../lib/api';
import type { Collection } from '../types/collection';

export const useCollectionStore = defineStore('collections', () => {
  const collections = ref<Collection[]>([]);
  const loading = ref(false);

  const activeCollections = computed(() => collections.value.filter((collection) => collection.active));
  const solutionCollections = computed(() => activeCollections.value.filter((collection) => collection.type === 'solution'));

  async function fetchCollections() {
    loading.value = true;

    if (hasLaravelApiConfig) {
      try {
        const data = await apiGet<Collection[]>('/collections');
        collections.value = data.map((collection) => ({ ...collection, id: String(collection.id) }));
        loading.value = false;
        return;
      } catch {
        // Keep the storefront usable when the CMS API is offline.
      }
    }

    collections.value = [];
    loading.value = false;
  }

  return { collections, activeCollections, solutionCollections, loading, fetchCollections };
});

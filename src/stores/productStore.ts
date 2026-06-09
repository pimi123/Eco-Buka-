import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../lib/api';
import { demoProducts } from '../lib/demoData';
import { hasSupabaseConfig, supabase } from '../lib/supabase';
import type { Product } from '../types/product';

const localProductsKey = 'eco-buka-products';

type LaravelProduct = Omit<Product, 'category'> & {
  main_image_url?: string | null;
  gallery_image_urls?: string[];
  category?: { id: number | string; name: string; slug: string } | string;
};

function mapLaravelProduct(product: LaravelProduct): Product {
  return {
    ...product,
    id: String(product.id),
    category_id: product.category_id ? String(product.category_id) : null,
    category: typeof product.category === 'object' ? product.category.name : product.category,
    image_url: product.image_url || product.main_image_url || null,
    gallery: product.gallery || product.gallery_image_urls || [],
  };
}

function readLocalProducts() {
  try {
    const stored = localStorage.getItem(localProductsKey);
    return stored ? (JSON.parse(stored) as Product[]) : [];
  } catch {
    return [];
  }
}

function writeLocalProducts(products: Product[]) {
  localStorage.setItem(localProductsKey, JSON.stringify(products));
}

function cleanProductPayload(product: Partial<Product>) {
  const next = { ...product };

  if (next.category_id === '') next.category_id = null;
  if (next.image_url === '') next.image_url = null;
  if (next.badge === '') next.badge = null;
  if (next.old_price === undefined || next.old_price === null || Number.isNaN(next.old_price)) next.old_price = null;
  if (next.price === undefined || next.price === null || Number.isNaN(next.price)) next.price = null;

  return next;
}

export const useProductStore = defineStore('products', () => {
  const products = ref<Product[]>([]);
  const loading = ref(false);

  const activeProducts = computed(() => products.value.filter((product) => product.active));
  const featuredProducts = computed(() => activeProducts.value.filter((product) => product.featured));

  async function fetchProducts(includeInactive = false) {
    loading.value = true;
    if (hasLaravelApiConfig) {
      try {
        const data = await apiGet<LaravelProduct[]>('/products');
        products.value = data.map(mapLaravelProduct);
        loading.value = false;
        return;
      } catch {
        // Fall through to the configured local/Supabase strategy.
      }
    }

    if (!hasSupabaseConfig || !supabase) {
      const localProducts = readLocalProducts();
      const merged = [
        ...localProducts,
        ...demoProducts.filter((demoProduct) => !localProducts.some((localProduct) => localProduct.id === demoProduct.id)),
      ];
      products.value = includeInactive ? merged : merged.filter((product) => product.active);
      loading.value = false;
      return;
    }

    let query = supabase.from('products').select('*, categories(name, slug)').order('created_at', { ascending: false });
    if (!includeInactive) query = query.eq('active', true);
    const { data, error } = await query;

    products.value = error
      ? demoProducts
      : data.map((item: any) => ({
          ...item,
          category: item.categories?.name,
        }));
    loading.value = false;
  }

  async function saveProduct(product: Partial<Product>) {
    const payload = cleanProductPayload(product);

    if (!hasSupabaseConfig || !supabase) {
      const id = payload.id || crypto.randomUUID();
      const next = { active: true, featured: false, specs: {}, ...payload, id } as Product;
      const localProducts = readLocalProducts();
      const savedProducts = localProducts.some((item) => item.id === id)
        ? localProducts.map((item) => (item.id === id ? next : item))
        : [next, ...localProducts];

      writeLocalProducts(savedProducts);
      products.value = products.value.some((item) => item.id === id)
        ? products.value.map((item) => (item.id === id ? next : item))
        : [next, ...products.value.filter((item) => item.id !== id)];
      return next;
    }

    const { data, error } = await supabase.from('products').upsert(payload).select().single();
    if (error) throw error;
    await fetchProducts(true);
    return data as Product;
  }

  async function deleteProduct(id: string) {
    if (hasSupabaseConfig && supabase) await supabase.from('products').delete().eq('id', id);
    if (!hasSupabaseConfig || !supabase) writeLocalProducts(readLocalProducts().filter((product) => product.id !== id));
    products.value = products.value.filter((product) => product.id !== id);
  }

  async function toggleActive(product: Product) {
    return saveProduct({ ...product, active: !product.active });
  }

  return { products, activeProducts, featuredProducts, loading, fetchProducts, saveProduct, deleteProduct, toggleActive };
});

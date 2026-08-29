import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { apiGet, hasLaravelApiConfig } from '../lib/api';
import { demoProducts } from '../lib/demoData';
import type { Product } from '../types/product';

type LaravelProduct = Omit<Product, 'category'> & {
  main_image_url?: string | null;
  gallery_image_urls?: string[];
  category?: { id: number | string; name: string; slug: string } | string;
  categories?: { id: number | string; name: string; slug: string }[];
  collections?: { id: number | string; name: string; slug: string; type?: string }[];
};

type PaginatedProducts = {
  data: LaravelProduct[];
  current_page: number;
  last_page: number;
  per_page: number;
  total?: number;
};

export type ProductListResult = {
  products: Product[];
  hasMore: boolean;
  currentPage: number;
  lastPage: number;
  total?: number;
};

function mapProductListResponse(data: LaravelProduct[] | PaginatedProducts): ProductListResult {
  if (Array.isArray(data)) {
    return {
      products: data.map(mapLaravelProduct),
      hasMore: false,
      currentPage: 1,
      lastPage: 1,
      total: data.length,
    };
  }

  return {
    products: data.data.map(mapLaravelProduct),
    hasMore: data.current_page < data.last_page,
    currentPage: data.current_page,
    lastPage: data.last_page,
    total: data.total,
  };
}

function normalizeStockValue(value: unknown) {
  return value !== false && value !== 0 && value !== '0';
}

function mapLaravelProduct(product: LaravelProduct): Product {
  return {
    ...product,
    id: String(product.id),
    category_id: product.category_id ? String(product.category_id) : null,
    category: typeof product.category === 'object' ? product.category.name : product.category,
    categories: product.categories?.map((category) => ({ ...category, id: String(category.id) })) || [],
    collections: product.collections?.map((collection) => ({ ...collection, id: String(collection.id) })) || [],
    collection_ids: product.collections?.map((collection) => String(collection.id)) || [],
    image_url: product.image_url || product.main_image_url || null,
    gallery: product.gallery || product.gallery_image_urls || [],
    in_stock: normalizeStockValue(product.in_stock),
  };
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
        // Fall through to demo content so the storefront stays usable offline.
      }
    }

    products.value = includeInactive ? demoProducts : demoProducts.filter((product) => product.active);
    loading.value = false;
  }

  async function fetchProductsByCategory(slug: string, page = 1, perPage = 24): Promise<ProductListResult> {
    loading.value = true;

    if (hasLaravelApiConfig) {
      try {
        const data = await apiGet<LaravelProduct[] | PaginatedProducts>(`/products/category/${slug}?page=${page}&per_page=${perPage}`);
        loading.value = false;
        return mapProductListResponse(data);
      } catch {
        // Fall through to the configured local/Supabase strategy.
      }
    }

    if (!products.value.length) await fetchProducts();

    const normalizedSlug = slug.toLowerCase();
    const filteredProducts = activeProducts.value.filter((product) => {
      const categoryId = String(product.category_id || '').toLowerCase();
      const categoryNameSlug = String(product.category || '')
        .toLowerCase()
        .replace(/&/g, 'and')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');

      return categoryId === normalizedSlug || categoryNameSlug === normalizedSlug;
    });

    const start = (page - 1) * perPage;
    const pagedProducts = filteredProducts.slice(start, start + perPage);

    loading.value = false;
    return {
      products: pagedProducts,
      hasMore: start + perPage < filteredProducts.length,
      currentPage: page,
      lastPage: Math.max(Math.ceil(filteredProducts.length / perPage), 1),
      total: filteredProducts.length,
    };
  }

  async function fetchProductsByCollection(slug: string, page = 1, perPage = 24): Promise<ProductListResult> {
    loading.value = true;

    if (hasLaravelApiConfig) {
      try {
        const data = await apiGet<LaravelProduct[] | PaginatedProducts>(`/collections/${slug}/products?page=${page}&per_page=${perPage}`);
        loading.value = false;
        return mapProductListResponse(data);
      } catch {
        // Fall through to local filtering for offline previews.
      }
    }

    if (!products.value.length) await fetchProducts();

    const normalizedSlug = slug.toLowerCase();
    const filteredProducts = activeProducts.value.filter((product) =>
      product.collections?.some((collection) => collection.slug === normalizedSlug),
    );

    const start = (page - 1) * perPage;
    const pagedProducts = filteredProducts.slice(start, start + perPage);

    loading.value = false;
    return {
      products: pagedProducts,
      hasMore: start + perPage < filteredProducts.length,
      currentPage: page,
      lastPage: Math.max(Math.ceil(filteredProducts.length / perPage), 1),
      total: filteredProducts.length,
    };
  }

  async function saveProduct(product: Partial<Product>) {
    const payload = cleanProductPayload(product);
    const id = payload.id || crypto.randomUUID();
  const next = { active: true, featured: false, in_stock: true, specs: {}, ...payload, id } as Product;
    products.value = products.value.some((item) => item.id === id)
      ? products.value.map((item) => (item.id === id ? next : item))
      : [next, ...products.value.filter((item) => item.id !== id)];
    return next;
  }

  async function deleteProduct(id: string) {
    products.value = products.value.filter((product) => product.id !== id);
  }

  async function toggleActive(product: Product) {
    return saveProduct({ ...product, active: !product.active });
  }

  return { products, activeProducts, featuredProducts, loading, fetchProducts, fetchProductsByCategory, fetchProductsByCollection, saveProduct, deleteProduct, toggleActive };
});

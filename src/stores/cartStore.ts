import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { CartItem } from '../types/order';
import type { Product } from '../types/product';

const STORAGE_KEY = 'eco-buka-cart';
type SelectedOptions = Record<string, string>;

function stableOptionsKey(options: SelectedOptions = {}) {
  return JSON.stringify(
    Object.entries(options)
      .filter(([, value]) => value !== undefined && value !== null && String(value).trim() !== '')
      .sort(([first], [second]) => first.localeCompare(second)),
  );
}

function cartKey(productId: string, selectedOptions: SelectedOptions = {}) {
  return `${productId}:${stableOptionsKey(selectedOptions)}`;
}

function loadCart(): CartItem[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    return Array.isArray(parsed)
      ? parsed.map((item) => ({
        ...item,
        key: item.key || cartKey(String(item.product?.id || ''), item.selected_options || {}),
        quantity: Math.max(1, Number(item.quantity) || 1),
      })).filter((item) => item.product?.id)
      : [];
  } catch {
    return [];
  }
}

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItem[]>(typeof localStorage === 'undefined' ? [] : loadCart());

  const count = computed(() => items.value.reduce((total, item) => total + item.quantity, 0));
  const subtotal = computed(() => items.value.reduce((total, item) => total + Number(item.product.price || 0) * item.quantity, 0));

  function persist() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value));
  }

  function add(product: Product, quantity = 1, selectedOptions: SelectedOptions = {}) {
    const safeQuantity = Math.max(1, Number(quantity) || 1);
    const key = cartKey(product.id, selectedOptions);
    const existing = items.value.find((item) => item.key === key);
    if (existing) {
      existing.quantity += safeQuantity;
    } else {
      items.value.push({ key, product, quantity: safeQuantity, selected_options: selectedOptions });
    }
    persist();
  }

  function updateQuantity(key: string, quantity: number) {
    const safeQuantity = Math.max(1, Number(quantity) || 1);
    items.value = items.value.map((item) => (item.key === key ? { ...item, quantity: safeQuantity } : item));
    persist();
  }

  function remove(key: string) {
    items.value = items.value.filter((item) => item.key !== key);
    persist();
  }

  function clear() {
    items.value = [];
    persist();
  }

  return { items, count, subtotal, add, updateQuantity, remove, clear };
});

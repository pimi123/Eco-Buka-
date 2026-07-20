<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import ProductGallerySlider from '../../components/website/ProductGallerySlider.vue';
import { useSeo } from '../../lib/seo';
import { useCartStore } from '../../stores/cartStore';
import { useProductStore } from '../../stores/productStore';

const route = useRoute();
const productStore = useProductStore();
const cartStore = useCartStore();
const product = computed(() => productStore.activeProducts.find((item) => item.slug === route.params.slug));
const money = (value?: number | null) => (value ? new Intl.NumberFormat('en-EU', { style: 'currency', currency: 'EUR' }).format(value) : 'Request price');
const quantity = ref(1);
const addedMessage = ref('');

type DetailData = Record<string, string> | string[] | null | undefined;

function detailEntries(data: DetailData) {
  if (!data) return [];
  if (Array.isArray(data)) {
    return data.filter(Boolean).map((value) => ({ label: '', value }));
  }

  return Object.entries(data)
    .filter(([, value]) => Boolean(value))
    .map(([label, value]) => ({ label, value }));
}

function isUrl(value: string) {
  return /^https?:\/\//i.test(value) || value.startsWith('/');
}

function addToCart() {
  if (!product.value) return;

  const safeQuantity = Math.min(99, Math.max(1, Number(quantity.value) || 1));
  quantity.value = safeQuantity;
  cartStore.add(product.value, safeQuantity);
  addedMessage.value = `${safeQuantity} ${safeQuantity === 1 ? 'item' : 'items'} added to cart.`;
}

const specEntries = computed(() => detailEntries(product.value?.specs));
const includedEntries = computed(() => detailEntries(product.value?.included_items));
const downloadEntries = computed(() => detailEntries(product.value?.downloads));

useSeo({
  title: computed(() => product.value?.name || 'Product'),
  description: computed(() => product.value?.short_description || product.value?.description || 'View Eco Buka product details, specs, pricing, and request-offer options.'),
  canonicalPath: computed(() => `/products/${String(route.params.slug || '')}`),
});

onMounted(() => productStore.fetchProducts());
</script>

<template>
  <WebsiteLayout>
    <section v-if="product" class="container-shell grid gap-7 py-8 sm:gap-8 sm:py-12 lg:grid-cols-[1.05fr_0.95fr] lg:gap-10">
      <ProductGallerySlider :title="product.name" :main-image="product.image_url" :gallery="product.gallery" />
      <div class="min-w-0">
        <p class="label">{{ product.category }}</p>
        <h1 class="mt-2 text-3xl font-black leading-tight min-[390px]:text-4xl">{{ product.name }}</h1>
        <p class="mt-4 text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">{{ product.short_description }}</p>
        <div class="mt-6 flex flex-wrap items-end gap-3">
          <p class="text-2xl font-black sm:text-3xl">{{ money(product.price) }}</p>
          <p v-if="product.old_price" class="text-slate-400 line-through">{{ money(product.old_price) }}</p>
        </div>
        <div class="mt-7 flex flex-col gap-3 min-[420px]:flex-row min-[420px]:flex-wrap sm:mt-8">
          <label class="grid gap-1">
            <span class="text-xs font-bold uppercase text-slate-500">Quantity</span>
            <input v-model.number="quantity" class="input-field w-28" type="number" min="1" max="99">
          </label>
          <button
            type="button"
            class="btn-primary w-full min-[420px]:w-auto"
            @click="addToCart"
          >
            Add to Cart
          </button>
          <RouterLink to="/cart" class="btn-secondary w-full min-[420px]:w-auto">View Cart</RouterLink>
          <RouterLink to="/contact" class="btn-secondary w-full min-[420px]:w-auto">Contact Us</RouterLink>
        </div>
        <div v-if="addedMessage" class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-800" role="status">
          {{ addedMessage }}
          <RouterLink to="/cart" class="ml-2 underline underline-offset-4">Review cart</RouterLink>
        </div>
        <div v-if="specEntries.length" class="mt-7 grid gap-3 sm:mt-8 sm:grid-cols-2">
          <div v-for="entry in specEntries" :key="`${entry.label}-${entry.value}`" class="min-w-0 rounded-lg border border-line p-4">
            <p v-if="entry.label" class="text-xs font-bold uppercase text-slate-500">{{ entry.label }}</p>
            <p class="mt-1 text-lg font-black">{{ entry.value }}</p>
          </div>
        </div>
      </div>
    </section>
    <section v-if="product" class="container-shell pb-8 sm:pb-14">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
        <div class="rounded-lg border border-line bg-white p-4 sm:p-5">
          <h2 class="font-black">Overview</h2>
          <p class="mt-3 text-sm leading-6 text-slate-600">{{ product.description || product.short_description }}</p>
        </div>

        <div class="rounded-lg border border-line bg-white p-4 sm:p-5">
          <h2 class="font-black">Specifications</h2>
          <dl v-if="specEntries.length" class="mt-3 grid gap-2 text-sm leading-6 text-slate-600">
            <div v-for="entry in specEntries" :key="`detail-${entry.label}-${entry.value}`">
              <dt v-if="entry.label" class="inline font-bold text-ink">{{ entry.label }}: </dt>
              <dd class="inline">{{ entry.value }}</dd>
            </div>
          </dl>
          <p v-else class="mt-3 text-sm leading-6 text-slate-600">Product specifications will be added soon.</p>
        </div>

        <div class="rounded-lg border border-line bg-white p-4 sm:p-5">
          <h2 class="font-black">What is included</h2>
          <ul v-if="includedEntries.length" class="mt-3 grid gap-2 text-sm leading-6 text-slate-600">
            <li v-for="entry in includedEntries" :key="`included-${entry.label}-${entry.value}`">
              <span v-if="entry.label" class="font-bold text-ink">{{ entry.label }}: </span>{{ entry.value }}
            </li>
          </ul>
          <p v-else class="mt-3 text-sm leading-6 text-slate-600">Included items will be confirmed with your offer.</p>
        </div>

        <div class="rounded-lg border border-line bg-white p-4 sm:p-5">
          <h2 class="font-black">Downloads</h2>
          <ul v-if="downloadEntries.length" class="mt-3 grid gap-2 text-sm leading-6 text-slate-600">
            <li v-for="entry in downloadEntries" :key="`download-${entry.label}-${entry.value}`">
              <a v-if="isUrl(entry.value)" class="font-bold text-ink underline-offset-4 hover:underline" :href="entry.value" target="_blank" rel="noreferrer">
                {{ entry.label || entry.value }}
              </a>
              <span v-else><span v-if="entry.label" class="font-bold text-ink">{{ entry.label }}: </span>{{ entry.value }}</span>
            </li>
          </ul>
          <p v-else class="mt-3 text-sm leading-6 text-slate-600">Manuals and datasheets will be added soon.</p>
        </div>
      </div>
    </section>
  </WebsiteLayout>
</template>

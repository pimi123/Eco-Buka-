<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import ImageUploadField from './ImageUploadField.vue';
import type { Category } from '../../types/category';
import type { Product } from '../../types/product';

const props = defineProps<{ product?: Product; categories: Category[] }>();
const emit = defineEmits<{ save: [product: Partial<Product>] }>();

const form = reactive<Partial<Product>>({
  name: '',
  slug: '',
  category_id: '',
  short_description: '',
  description: '',
  price: 0,
  old_price: undefined,
  image_url: '',
  badge: '',
  featured: false,
  active: true,
  specs: { Capacity: '', Output: '', 'Solar Input': '' },
});

watch(
  () => props.product,
  (product) => {
    if (product) Object.assign(form, product, { specs: { Capacity: '', Output: '', 'Solar Input': '', ...product.specs } });
  },
  { immediate: true },
);

const slugPreview = computed(() => form.slug || form.name?.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''));

function submit() {
  emit('save', { ...form, slug: slugPreview.value });
}
</script>

<template>
  <form class="grid max-w-full gap-5 rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5" @submit.prevent="submit">
    <div class="grid min-w-0 gap-4 md:grid-cols-2">
      <label class="grid gap-2"><span class="label">Product name</span><input v-model="form.name" class="input-field" required /></label>
      <label class="grid gap-2"><span class="label">Slug</span><input v-model="form.slug" class="input-field" :placeholder="slugPreview" /></label>
      <label class="grid gap-2"><span class="label">Category</span><select v-model="form.category_id" class="input-field"><option value="">No category</option><option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option></select></label>
      <label class="grid gap-2"><span class="label">Badge</span><input v-model="form.badge" class="input-field" placeholder="New, Sale, Best Seller" /></label>
      <label class="grid gap-2"><span class="label">Price</span><input v-model.number="form.price" type="number" class="input-field" /></label>
      <label class="grid gap-2"><span class="label">Old price</span><input v-model.number="form.old_price" type="number" class="input-field" /></label>
    </div>
    <ImageUploadField v-model="form.image_url" label="Main product image" folder="products" />
    <label class="grid gap-2"><span class="label">Short description</span><textarea v-model="form.short_description" class="input-field min-h-24" /></label>
    <label class="grid gap-2"><span class="label">Full description</span><textarea v-model="form.description" class="input-field min-h-32" /></label>
    <div>
      <span class="label">Specs</span>
      <div class="mt-2 grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-3">
        <label v-for="(_, key) in form.specs" :key="key" class="grid gap-2"><span class="text-xs font-semibold text-slate-500">{{ key }}</span><input v-model="form.specs![key]" class="input-field" /></label>
      </div>
    </div>
    <div class="flex flex-wrap gap-5">
      <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="form.featured" type="checkbox" /> Featured</label>
      <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="form.active" type="checkbox" /> Active</label>
    </div>
    <button class="btn-primary w-full sm:w-fit">Save Product</button>
  </form>
</template>

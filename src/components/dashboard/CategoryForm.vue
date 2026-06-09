<script setup lang="ts">
import { computed, reactive, watch } from 'vue';
import ImageUploadField from './ImageUploadField.vue';
import type { Category } from '../../types/category';

const props = defineProps<{ category?: Category }>();
const emit = defineEmits<{ save: [category: Partial<Category>] }>();

const form = reactive<Partial<Category>>({ name: '', slug: '', description: '', image_url: '', is_new: false, active: true });

watch(() => props.category, (category) => category && Object.assign(form, category), { immediate: true });

const slugPreview = computed(() => form.slug || form.name?.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''));
</script>

<template>
  <form class="grid max-w-full gap-5 rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5" @submit.prevent="emit('save', { ...form, slug: slugPreview })">
    <div class="grid min-w-0 gap-4 md:grid-cols-2">
      <label class="grid gap-2"><span class="label">Category name</span><input v-model="form.name" class="input-field" required /></label>
      <label class="grid gap-2"><span class="label">Slug</span><input v-model="form.slug" class="input-field" :placeholder="slugPreview" /></label>
    </div>
    <label class="grid gap-2"><span class="label">Description</span><textarea v-model="form.description" class="input-field min-h-24" /></label>
    <ImageUploadField v-model="form.image_url" label="Category image" folder="categories" />
    <div class="flex flex-wrap gap-5">
      <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="form.is_new" type="checkbox" /> New label</label>
      <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="form.active" type="checkbox" /> Active</label>
    </div>
    <button class="btn-primary w-full sm:w-fit">Save Category</button>
  </form>
</template>

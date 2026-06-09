<script setup lang="ts">
import { ImagePlus, Loader2, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { hasSupabaseConfig, storageBucket } from '../../lib/supabase';
import { uploadImageFile } from '../../lib/storage';

const props = withDefaults(
  defineProps<{
    modelValue?: string | null;
    label?: string;
    folder?: string;
    placeholder?: string;
  }>(),
  {
    label: 'Image',
    folder: 'uploads',
    placeholder: 'Supabase Storage public URL',
  },
);

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const loading = ref(false);
const errorMessage = ref('');
const notice = ref('');

async function handleFileChange(event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;

  loading.value = true;
  errorMessage.value = '';
  notice.value = '';

  try {
    const uploaded = await uploadImageFile(file, props.folder);
    emit('update:modelValue', uploaded.publicUrl);
    notice.value = uploaded.isTemporary
      ? 'Preview only. Add Supabase keys to store uploads permanently.'
      : `Uploaded to ${storageBucket}.`;
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Image upload failed.';
  } finally {
    loading.value = false;
    input.value = '';
  }
}

function clearImage() {
  emit('update:modelValue', '');
  notice.value = '';
  errorMessage.value = '';
}
</script>

<template>
  <div class="grid gap-2">
    <span class="label">{{ label }}</span>

    <div class="grid gap-3 rounded-lg border border-line bg-mist p-3 sm:grid-cols-[132px_minmax(0,1fr)]">
      <div class="flex aspect-[4/3] items-center justify-center overflow-hidden rounded-md bg-white">
        <img v-if="modelValue" :src="modelValue" :alt="label" class="h-full w-full object-cover" />
        <ImagePlus v-else class="h-8 w-8 text-slate-300" />
      </div>

      <div class="grid min-w-0 gap-3">
        <input :value="modelValue || ''" class="input-field" :placeholder="placeholder" @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)" />

        <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
          <label class="btn-secondary w-full cursor-pointer sm:w-fit">
            <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
            <ImagePlus v-else class="h-4 w-4" />
            {{ loading ? 'Uploading...' : 'Upload Image' }}
            <input class="sr-only" type="file" accept="image/png,image/jpeg,image/webp,image/gif" :disabled="loading" @change="handleFileChange" />
          </label>
          <button v-if="modelValue" class="btn-secondary w-full sm:w-fit" type="button" @click="clearImage">
            <X class="h-4 w-4" /> Remove
          </button>
        </div>

        <p class="text-xs leading-5 text-slate-500">
          Files are stored in Supabase Storage when configured. Accepted: JPG, PNG, WebP, GIF up to 8MB.
        </p>
        <p v-if="!hasSupabaseConfig" class="text-xs font-semibold leading-5 text-amber-700">
          Supabase is not configured yet, so uploads are temporary local previews.
        </p>
        <p v-if="notice" class="text-xs font-semibold leading-5 text-energy">{{ notice }}</p>
        <p v-if="errorMessage" class="text-xs font-semibold leading-5 text-red-600">{{ errorMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';
import { useAuthStore } from '../../stores/authStore';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const loading = ref(false);
const success = ref('');
const errors = ref<Record<string, string[]>>({});
const form = reactive({
  token: typeof route.query.token === 'string' ? route.query.token : '',
  email: typeof route.query.email === 'string' ? route.query.email : '',
  password: '',
  password_confirmation: '',
});

useSeo({
  title: 'Ndrysho fjalëkalimin',
  description: 'Vendos fjalëkalimin e ri për llogarinë Eco Buka.',
  canonicalPath: '/reset-password',
});

function fieldError(field: string) {
  return errors.value[field]?.[0] || '';
}

async function submit() {
  if (loading.value) return;

  loading.value = true;
  success.value = '';
  errors.value = {};

  try {
    const response = await authStore.resetPassword(form);
    success.value = response.message;
    window.setTimeout(() => router.push('/login'), 900);
  } catch (error) {
    const response = (error as Error & { response?: { errors?: Record<string, string[]>; message?: string } }).response;
    errors.value = response?.errors || { general: [response?.message || 'Fjalëkalimi nuk u ndryshua.'] };
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell grid min-h-[70vh] place-items-center py-10">
      <div class="w-full max-w-md rounded-lg border border-line bg-white p-5 shadow-sm sm:p-7">
        <p class="label">Llogaria e klientit</p>
        <h1 class="mt-2 text-3xl font-black">Ndrysho fjalëkalimin</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">
          Vendos fjalëkalimin e ri për llogarinë tuaj.
        </p>

        <p v-if="success" class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-800">{{ success }}</p>
        <p v-if="fieldError('general')" class="mt-5 rounded-md bg-red-50 p-3 text-sm font-semibold text-red-700">{{ fieldError('general') }}</p>

        <form class="mt-6 grid gap-4" @submit.prevent="submit">
          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Email</span>
            <input v-model="form.email" class="input-field" type="email" autocomplete="email" placeholder="email@example.com">
            <span v-if="fieldError('email')" class="text-xs font-semibold text-red-600">{{ fieldError('email') }}</span>
          </label>
          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Fjalëkalimi i ri</span>
            <input v-model="form.password" class="input-field" type="password" autocomplete="new-password" placeholder="Minimum 8 karaktere">
            <span v-if="fieldError('password')" class="text-xs font-semibold text-red-600">{{ fieldError('password') }}</span>
          </label>
          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Konfirmo fjalëkalimin</span>
            <input v-model="form.password_confirmation" class="input-field" type="password" autocomplete="new-password" placeholder="Përsërite fjalëkalimin">
          </label>
          <button class="btn-primary min-h-12 w-full" :disabled="loading || !form.token">
            {{ loading ? 'Duke ndryshuar...' : 'Ndrysho fjalëkalimin' }}
          </button>
        </form>
      </div>
    </section>
  </WebsiteLayout>
</template>

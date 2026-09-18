<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';
import { useAuthStore } from '../../stores/authStore';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const loading = ref(false);
const errors = ref<Record<string, string[]>>({});
const form = reactive({
  email: '',
  password: '',
});

const verified = computed(() => route.query.verified === '1');

useSeo({
  title: 'Kyçja e klientit',
  description: 'Kyçu në llogarinë Eco Buka për të vazhduar checkout-in dhe për të menaxhuar porositë.',
  canonicalPath: '/login',
});

function fieldError(field: string) {
  return errors.value[field]?.[0] || '';
}

function readError(error: unknown, fallback: string) {
  const response = (error as Error & { response?: { errors?: Record<string, string[]>; message?: string } }).response;
  errors.value = response?.errors || { general: [response?.message || fallback] };
}

async function submit() {
  if (loading.value) return;

  loading.value = true;
  errors.value = {};

  try {
    await authStore.login(form);
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/account';
    router.push(redirect.startsWith('/') ? redirect : '/account');
  } catch (error) {
    readError(error, 'Kyçja nuk u krye. Ju lutemi kontrolloni të dhënat.');
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
        <h1 class="mt-2 text-3xl font-black">Kyçu</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">
          Kyçu për të vazhduar checkout-in dhe për të lidhur porositë me llogarinë tuaj.
        </p>

        <p v-if="verified" class="mt-5 rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-800">
          Emaili u verifikua me sukses. Mund të kyçeni tani.
        </p>
        <p v-if="fieldError('general')" class="mt-5 rounded-md bg-red-50 p-3 text-sm font-semibold text-red-700">
          {{ fieldError('general') }}
        </p>

        <form class="mt-6 grid gap-4" @submit.prevent="submit">
          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Email</span>
            <input v-model="form.email" class="input-field" type="email" autocomplete="email" placeholder="email@example.com">
            <span v-if="fieldError('email')" class="text-xs font-semibold text-red-600">{{ fieldError('email') }}</span>
          </label>
          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Fjalëkalimi</span>
            <input v-model="form.password" class="input-field" type="password" autocomplete="current-password" placeholder="••••••••">
            <span v-if="fieldError('password')" class="text-xs font-semibold text-red-600">{{ fieldError('password') }}</span>
          </label>
          <button class="btn-primary min-h-12 w-full" :disabled="loading">
            {{ loading ? 'Duke u kyçur...' : 'Kyçu' }}
          </button>
        </form>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 text-sm font-semibold">
          <RouterLink class="text-ink underline underline-offset-4" to="/forgot-password">Keni harruar fjalëkalimin?</RouterLink>
          <RouterLink class="text-ink underline underline-offset-4" to="/register">Krijo llogari</RouterLink>
        </div>
      </div>
    </section>
  </WebsiteLayout>
</template>

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
const errors = ref<Record<string, string[]>>({});
const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
});

useSeo({
  title: 'Krijo llogari',
  description: 'Krijo llogari Eco Buka për checkout më të sigurt dhe menaxhim të porosive.',
  canonicalPath: '/register',
});

function fieldError(field: string) {
  return errors.value[field]?.[0] || '';
}

function readError(error: unknown) {
  const response = (error as Error & { response?: { errors?: Record<string, string[]>; message?: string } }).response;
  errors.value = response?.errors || { general: [response?.message || 'Regjistrimi nuk u krye. Ju lutemi provoni përsëri.'] };
}

async function submit() {
  if (loading.value) return;

  loading.value = true;
  errors.value = {};

  try {
    await authStore.register(form);
    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/account?verify_email=1';
    router.push(redirect.startsWith('/') ? redirect : '/account?verify_email=1');
  } catch (error) {
    readError(error);
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell grid min-h-[70vh] place-items-center py-10">
      <div class="w-full max-w-2xl rounded-lg border border-line bg-white p-5 shadow-sm sm:p-7">
        <p class="label">Llogaria e klientit</p>
        <h1 class="mt-2 text-3xl font-black">Krijo llogari</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">
          Llogaria përdoret për checkout, verifikim emaili dhe lidhjen e porosive me klientin.
        </p>

        <p v-if="fieldError('general')" class="mt-5 rounded-md bg-red-50 p-3 text-sm font-semibold text-red-700">
          {{ fieldError('general') }}
        </p>

        <form class="mt-6 grid gap-4" @submit.prevent="submit">
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Emri</span>
              <input v-model="form.first_name" class="input-field" type="text" autocomplete="given-name" placeholder="Arben">
              <span v-if="fieldError('first_name')" class="text-xs font-semibold text-red-600">{{ fieldError('first_name') }}</span>
            </label>
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Mbiemri</span>
              <input v-model="form.last_name" class="input-field" type="text" autocomplete="family-name" placeholder="Krasniqi">
              <span v-if="fieldError('last_name')" class="text-xs font-semibold text-red-600">{{ fieldError('last_name') }}</span>
            </label>
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Email</span>
              <input v-model="form.email" class="input-field" type="email" autocomplete="email" placeholder="email@example.com">
              <span v-if="fieldError('email')" class="text-xs font-semibold text-red-600">{{ fieldError('email') }}</span>
            </label>
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Telefoni</span>
              <input v-model="form.phone" class="input-field" type="tel" autocomplete="tel" placeholder="+383 44 000 000">
              <span v-if="fieldError('phone')" class="text-xs font-semibold text-red-600">{{ fieldError('phone') }}</span>
            </label>
          </div>
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Fjalëkalimi</span>
              <input v-model="form.password" class="input-field" type="password" autocomplete="new-password" placeholder="Minimum 8 karaktere">
              <span v-if="fieldError('password')" class="text-xs font-semibold text-red-600">{{ fieldError('password') }}</span>
            </label>
            <label class="grid gap-2">
              <span class="text-xs font-bold uppercase text-slate-500">Konfirmo fjalëkalimin</span>
              <input v-model="form.password_confirmation" class="input-field" type="password" autocomplete="new-password" placeholder="Përsërite fjalëkalimin">
            </label>
          </div>
          <button class="btn-primary min-h-12 w-full sm:w-fit" :disabled="loading">
            {{ loading ? 'Duke krijuar llogarinë...' : 'Krijo llogari' }}
          </button>
        </form>

        <p class="mt-5 text-sm font-semibold text-slate-600">
          Keni llogari?
          <RouterLink class="text-ink underline underline-offset-4" to="/login">Kyçu këtu</RouterLink>
        </p>
      </div>
    </section>
  </WebsiteLayout>
</template>

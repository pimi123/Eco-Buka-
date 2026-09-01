<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { apiPost } from '../../lib/api';
import { useSeo } from '../../lib/seo';
import type { ContactMessagePayload, ContactMessageResponse, ContactPurpose } from '../../types/contact';

const route = useRoute();
const loading = ref(false);
const successMessage = ref('');
const errors = ref<Record<string, string[]>>({});

const purposeOptions: Array<{ value: ContactPurpose; label: string; title: string; description: string; placeholder: string }> = [
  {
    value: 'offer',
    label: 'Kërkesë për ofertë',
    title: 'Kërko ofertë',
    description: 'Na tregoni çfarë zgjidhjeje energjie ju nevojitet dhe ekipi Eco Buka do tju kontaktojë me propozim.',
    placeholder: 'P.sh. Kam nevojë për zgjidhje backup për shtëpi, biznes ose sistem solar.',
  },
  {
    value: 'return',
    label: 'Kthim ose garancion',
    title: 'Kërkesë për kthim ose garancion',
    description: 'Përshkruani produktin, numrin e porosisë nëse e keni, dhe arsyen pse kërkoni kontroll ose kthim.',
    placeholder: 'P.sh. Produkti ka problem me karikim. Numri i porosisë është...',
  },
  {
    value: 'support',
    label: 'Mbështetje teknike',
    title: 'Kërko mbështetje',
    description: 'Dërgoni pyetje teknike ose kërkesë për ndihmë rreth përdorimit, instalimit ose mirëmbajtjes.',
    placeholder: 'P.sh. Kam pyetje për instalimin ose konfigurimin e produktit.',
  },
  {
    value: 'general',
    label: 'Pyetje e përgjithshme',
    title: 'Na kontaktoni',
    description: 'Përdoreni këtë opsion për pyetje të përgjithshme rreth Eco Buka, produkteve ose shërbimit.',
    placeholder: 'Shkruani mesazhin tuaj.',
  },
];

function normalizedPurpose(value: unknown): ContactPurpose {
  return purposeOptions.some((option) => option.value === value) ? value as ContactPurpose : 'offer';
}

const form = reactive<ContactMessagePayload>({
  purpose: normalizedPurpose(route.query.purpose),
  name: '',
  phone: '',
  email: '',
  subject: '',
  message: '',
  source_path: window.location.pathname + window.location.search,
});

const selectedPurpose = computed(() => purposeOptions.find((option) => option.value === form.purpose) || purposeOptions[0]);

useSeo({
  title: 'Kontakt',
  description: 'Kontaktoni Eco Buka për ofertë, kthim produkti, garancion ose mbështetje teknike.',
  canonicalPath: '/contact',
});

watch(() => route.query.purpose, (purpose) => {
  form.purpose = normalizedPurpose(purpose);
  form.source_path = window.location.pathname + window.location.search;
});

function fieldError(field: string) {
  return errors.value[field]?.[0] || '';
}

async function submitContactMessage() {
  if (loading.value) return;

  loading.value = true;
  successMessage.value = '';
  errors.value = {};

  try {
    const response = await apiPost<ContactMessageResponse>('/contact-messages', form);
    successMessage.value = response.message;
    form.name = '';
    form.phone = '';
    form.email = '';
    form.subject = '';
    form.message = '';
  } catch (error) {
    const response = (error as Error & { response?: { errors?: Record<string, string[]>; message?: string } }).response;
    errors.value = response?.errors || { general: [response?.message || 'Mesazhi nuk mund të dërgohet. Ju lutemi provoni përsëri.'] };
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <WebsiteLayout>
    <section class="container-shell grid gap-8 py-10 sm:py-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-10 lg:py-14">
      <div>
        <p class="label">Kontakt</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ selectedPurpose.title }}</h1>
        <p class="mt-4 text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">{{ selectedPurpose.description }}</p>

        <div class="mt-6 grid gap-3 rounded-lg border border-line bg-white p-4 text-sm leading-6 text-slate-600 shadow-sm">
          <p class="font-black text-ink">Kontakt i shpejtë</p>
          <a class="font-semibold hover:text-ink hover:underline" href="tel:+38345977007">Kosovë: +383 45 977 007</a>
          <a class="font-semibold hover:text-ink hover:underline" href="tel:+355688049525">Shqipëri: +355 68 804 9525</a>
          <a class="font-semibold hover:text-ink hover:underline" href="mailto:info@ecoflowks.com">info@ecoflowks.com</a>
        </div>
      </div>

      <form class="grid min-w-0 gap-5 rounded-lg border border-line bg-white p-4 shadow-sm sm:p-6" @submit.prevent="submitContactMessage">
        <p v-if="successMessage" class="rounded-md border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-800" role="status">
          {{ successMessage }}
        </p>
        <p v-if="fieldError('general')" class="rounded-md bg-red-50 p-3 text-sm font-semibold text-red-700">
          {{ fieldError('general') }}
        </p>

        <label class="grid gap-2">
          <span class="text-xs font-bold uppercase text-slate-500">Arsyeja e kontaktit</span>
          <select v-model="form.purpose" class="input-field">
            <option v-for="option in purposeOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <span v-if="fieldError('purpose')" class="text-xs font-semibold text-red-600">{{ fieldError('purpose') }}</span>
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Emri dhe mbiemri</span>
            <input v-model="form.name" class="input-field" type="text" autocomplete="name" placeholder="p.sh. Arben Krasniqi">
            <span v-if="fieldError('name')" class="text-xs font-semibold text-red-600">{{ fieldError('name') }}</span>
          </label>
          <label class="grid gap-2">
            <span class="text-xs font-bold uppercase text-slate-500">Telefoni</span>
            <input v-model="form.phone" class="input-field" type="tel" autocomplete="tel" placeholder="+383 44 000 000">
            <span v-if="fieldError('phone')" class="text-xs font-semibold text-red-600">{{ fieldError('phone') }}</span>
          </label>
        </div>

        <label class="grid gap-2">
          <span class="text-xs font-bold uppercase text-slate-500">Email opsional</span>
          <input v-model="form.email" class="input-field" type="email" autocomplete="email" placeholder="email@example.com">
          <span v-if="fieldError('email')" class="text-xs font-semibold text-red-600">{{ fieldError('email') }}</span>
        </label>

        <label class="grid gap-2">
          <span class="text-xs font-bold uppercase text-slate-500">Subjekti opsional</span>
          <input v-model="form.subject" class="input-field" type="text" placeholder="p.sh. Ofertë për sistem solar">
          <span v-if="fieldError('subject')" class="text-xs font-semibold text-red-600">{{ fieldError('subject') }}</span>
        </label>

        <label class="grid gap-2">
          <span class="text-xs font-bold uppercase text-slate-500">Mesazhi</span>
          <textarea v-model="form.message" class="input-field min-h-36" :placeholder="selectedPurpose.placeholder" />
          <span v-if="fieldError('message')" class="text-xs font-semibold text-red-600">{{ fieldError('message') }}</span>
        </label>

        <button class="btn-primary min-h-12 w-full disabled:cursor-wait disabled:opacity-60 sm:w-fit" :disabled="loading">
          {{ loading ? 'Duke dërguar...' : 'Dërgo mesazhin' }}
        </button>
      </form>
    </section>
  </WebsiteLayout>
</template>

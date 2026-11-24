<script setup lang="ts">
import { ref, watch } from 'vue';
import { consentChosen, consentOpen, preferences, setTrackingPreferences } from '../../lib/metaPixel';
const analytics = ref(preferences.value.analytics);
const marketing = ref(preferences.value.marketing);
watch(consentOpen, () => { analytics.value = preferences.value.analytics; marketing.value = preferences.value.marketing; });
</script>
<template>
  <button v-if="consentChosen && !consentOpen" type="button" class="fixed bottom-3 left-3 z-40 rounded-md border border-line bg-white px-3 py-2 text-xs font-bold shadow-sm" @click="consentOpen = true">Preferencat e cookies</button>
  <section v-if="!consentChosen || consentOpen" aria-label="Preferencat e cookies" class="fixed inset-x-3 bottom-3 z-50 mx-auto max-w-xl rounded-xl border border-line bg-white p-4 shadow-xl sm:p-5">
    <h2 class="text-lg font-black">Zgjedhjet tuaja të privatësisë</h2>
    <p class="mt-2 text-sm leading-6 text-slate-600">Cookies e domosdoshme mbajnë shportën dhe checkout-in funksional. Me lejen tuaj përdorim Google Analytics për matje dhe Meta për marketing e matjen e porosive/pagesave. Mund t’i ndryshoni zgjedhjet më vonë.</p>
    <RouterLink to="/politika-e-cookies" class="mt-2 inline-block text-sm underline">Politika e cookies</RouterLink>
    <div class="mt-3 flex flex-wrap gap-5 text-sm"><label class="flex items-center gap-2"><input v-model="analytics" type="checkbox">Analitikë</label><label class="flex items-center gap-2"><input v-model="marketing" type="checkbox">Marketing Meta</label></div>
    <div class="mt-4 grid gap-2 sm:grid-cols-3">
      <button type="button" class="rounded-md border border-line px-3 py-3 text-sm font-bold" @click="setTrackingPreferences({ analytics: false, marketing: false })">Vetëm të domosdoshmet</button>
      <button type="button" class="rounded-md border border-line px-3 py-3 text-sm font-bold" @click="setTrackingPreferences({ analytics, marketing })">Ruaj zgjedhjet</button>
      <button type="button" class="rounded-md border border-line px-3 py-3 text-sm font-bold" @click="setTrackingPreferences({ analytics: true, marketing: true })">Prano të gjitha</button>
    </div>
  </section>
</template>

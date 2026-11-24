<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';
useSeo({ title: 'Faqja nuk u gjet', description: 'Faqja që kërkoni nuk ekziston.', noindex: true });
</script>
<template><WebsiteLayout><section class="container-shell py-16"><p class="label">404</p><h1 class="mt-2 text-3xl font-black">Faqja nuk u gjet</h1><p class="mt-4 text-slate-600">Kontrolloni adresën ose shfletoni produktet tona.</p><RouterLink to="/products" class="btn-primary mt-6">Shiko produktet</RouterLink></section></WebsiteLayout></template>

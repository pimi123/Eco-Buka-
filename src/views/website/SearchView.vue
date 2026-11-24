<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import CatalogBrowser from '../../components/website/CatalogBrowser.vue';
import { useSeo } from '../../lib/seo';
useSeo({ title: 'Kërko produkte', description: 'Shfletoni dhe filtroni produktet EcoBuka sipas kategorisë, serisë, çmimit dhe ofertave.', canonicalPath: '/search' });
</script>
<template><WebsiteLayout><CatalogBrowser title="Kërko produkte" /></WebsiteLayout></template>
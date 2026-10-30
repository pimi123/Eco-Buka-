<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import CatalogBrowser from '../../components/website/CatalogBrowser.vue';
import { useSeo } from '../../lib/seo';
useSeo({ title: 'Produktet', description: 'Shfletoni dhe filtroni produktet EcoBuka sipas kategorisë, serisë, çmimit dhe ofertave.', canonicalPath: '/products' });
</script>
<template><WebsiteLayout><CatalogBrowser title="Produktet" /></WebsiteLayout></template>
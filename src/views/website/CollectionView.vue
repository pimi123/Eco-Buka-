<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import CatalogBrowser from '../../components/website/CatalogBrowser.vue';
import { useSeo } from '../../lib/seo';
const route = useRoute();
const slug = computed(() => String(route.params.slug || ''));
useSeo({ title: computed(() => slug.value.replace(/-/g, ' ')), description: 'Shfletoni produktet EcoBuka sipas kategorisë, serisë dhe çmimit.', canonicalPath: computed(() => '/collections/' + slug.value) });
</script>
<template><WebsiteLayout><CatalogBrowser :collection="slug" /></WebsiteLayout></template>
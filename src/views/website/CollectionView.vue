<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import CatalogBrowser from '../../components/website/CatalogBrowser.vue';
const route = useRoute();
const slug = computed(() => String(route.params.slug || ''));
</script>
<template><WebsiteLayout><CatalogBrowser :collection="slug" /></WebsiteLayout></template>
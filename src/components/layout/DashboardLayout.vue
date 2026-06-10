<script setup lang="ts">
import DashboardSidebar from './DashboardSidebar.vue';
import DashboardTopbar from './DashboardTopbar.vue';
</script>

<template>
  <div class="min-h-screen min-w-0 overflow-x-clip bg-mist lg:grid lg:grid-cols-[250px_minmax(0,1fr)]">
    <DashboardSidebar />
    <main class="min-w-0">
      <DashboardTopbar />
      <div class="min-w-0 p-4 sm:p-5 lg:p-6">
        <slot />
      </div>
    </main>
  </div>
</template>

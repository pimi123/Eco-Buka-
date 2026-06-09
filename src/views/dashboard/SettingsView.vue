<script setup lang="ts">
import DashboardLayout from '../../components/layout/DashboardLayout.vue';
import { hasSupabaseConfig, storageBucket } from '../../lib/supabase';
</script>

<template>
  <DashboardLayout>
    <div class="min-w-0 rounded-lg border border-line bg-white p-4 shadow-sm sm:p-5">
      <h1 class="text-xl font-black sm:text-2xl">Settings</h1>
      <div class="mt-6 grid gap-4 text-sm">
        <p><span class="font-bold">Supabase:</span> {{ hasSupabaseConfig ? 'Configured' : 'Using demo fallback data' }}</p>
        <p><span class="font-bold">Storage bucket:</span> {{ storageBucket }}</p>
        <p class="max-w-2xl text-slate-600">Add `VITE_SUPABASE_URL`, `VITE_SUPABASE_ANON_KEY`, and `VITE_SUPABASE_STORAGE_BUCKET` to `.env` when the database is ready.</p>
      </div>
    </div>
  </DashboardLayout>
</template>

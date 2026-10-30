<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { SlidersHorizontal, X } from 'lucide-vue-next';
import { apiGet, hasLaravelApiConfig } from '../../lib/api';
import { demoProducts } from '../../lib/demoData';
import { discountsFirst, isProductInStock } from '../../lib/discount';
import type { Product } from '../../types/product';
import ProductGrid from './ProductGrid.vue';

const props = defineProps<{ category?: string; collection?: string; title?: string }>();
type Facets = { categories: { slug: string; name: string; count: number }[]; series: { name: string; count: number }[]; min: number; max: number };
type Result = { data: (Omit<Product, 'category'> & { category?: { name: string } | string })[]; total: number; last_page: number; facets: Facets | null; context?: { name: string; description?: string } };
const route = useRoute(); const router = useRouter();
const list = (key: string) => String(route.query[key] || '').split(',').filter(Boolean);
const query = ref(''); const categories = ref<string[]>([]); const series = ref<string[]>([]);
const min = ref(''); const max = ref(''); const sale = ref(false); const stock = ref(false); const sort = ref('recommended');
const page = computed(() => Math.max(1, Number(route.query.page) || 1));
const products = ref<Product[]>([]); const total = ref(0); const lastPage = ref(1); const loading = ref(false); const error = ref('');
const facets = ref<Facets>({ categories: [], series: [], min: 0, max: 0 });
const context = ref<{ name: string; description?: string }>(); const open = ref(false);
const filterPanel = ref<HTMLElement>();
let previousFocus: HTMLElement | null = null;
watch(open, async value => {
  if (value) { previousFocus = document.activeElement as HTMLElement; await nextTick(); filterPanel.value?.querySelector<HTMLButtonElement>('button')?.focus(); }
  else previousFocus?.focus();
});
function panelKeys(event: KeyboardEvent) {
  if (!open.value) return;
  if (event.key === 'Escape') { open.value = false; return; }
  if (event.key !== 'Tab') return;
  const items = [...(filterPanel.value?.querySelectorAll<HTMLElement>('button, input, summary') || [])].filter(el => el.getClientRects().length && !el.hasAttribute('disabled'));
  if (event.shiftKey && document.activeElement === items[0]) { event.preventDefault(); items[items.length - 1]?.focus(); }
  else if (!event.shiftKey && document.activeElement === items[items.length - 1]) { event.preventDefault(); items[0]?.focus(); }
}
const activeCount = computed(() => categories.value.length + series.value.length + Number(!!min.value) + Number(!!max.value) + Number(sale.value) + Number(stock.value));
let timer: ReturnType<typeof setTimeout>; let controller: AbortController | undefined; let requestId = 0; let facetKey = '';
const chips = computed(() => [
  ...categories.value.map(v => ({ label: facets.value.categories.find(c => c.slug === v)?.name || v, remove: () => { categories.value = categories.value.filter(c => c !== v); apply(); } })),
  ...series.value.map(v => ({ label: v, remove: () => { series.value = series.value.filter(c => c !== v); apply(); } })),
  ...(min.value ? [{ label: `Nga €${min.value}`, remove: () => { min.value = ''; apply(); } }] : []),
  ...(max.value ? [{ label: `Deri €${max.value}`, remove: () => { max.value = ''; apply(); } }] : []),
  ...(sale.value ? [{ label: 'Me zbritje', remove: () => { sale.value = false; apply(); } }] : []),
  ...(stock.value ? [{ label: 'Në stok', remove: () => { stock.value = false; apply(); } }] : []),
]);
function apply(nextPage = 1) {
  clearTimeout(timer);
  const next = { ...route.query };
  for (const key of ['q','categories','series','min','max','sale','stock','sort','page']) delete next[key];
  Object.assign(next, {
    ...(query.value.trim() ? { q: query.value.trim() } : {}), ...(categories.value.length ? { categories: categories.value.join(',') } : {}),
    ...(series.value.length ? { series: series.value.join(',') } : {}), ...(min.value ? { min: min.value } : {}), ...(max.value ? { max: max.value } : {}),
    ...(sale.value ? { sale: '1' } : {}), ...(stock.value ? { stock: '1' } : {}), ...(sort.value !== 'recommended' ? { sort: sort.value } : {}), ...(nextPage > 1 ? { page: String(nextPage) } : {}),
  });
  router.push({ query: next });
}
function debounce() { clearTimeout(timer); timer = setTimeout(() => apply(), 400); }
function clear() { categories.value = []; series.value = []; min.value = ''; max.value = ''; sale.value = false; stock.value = false; apply(); }

async function load() {
  query.value = String(route.query.q || ''); categories.value = list('categories'); series.value = list('series');
  min.value = String(route.query.min || ''); max.value = String(route.query.max || ''); sale.value = route.query.sale === '1'; stock.value = route.query.stock === '1'; sort.value = String(route.query.sort || 'recommended');
  controller?.abort(); controller = new AbortController(); const id = ++requestId;
  const activeController = controller;
  const timeout = setTimeout(() => activeController.abort(), 15000);
  loading.value = true; error.value = '';
  if (min.value && max.value && Number(min.value) > Number(max.value)) { clearTimeout(timeout); error.value = 'Çmimi minimal duhet të jetë më i vogël se çmimi maksimal.'; loading.value = false; products.value = []; return; }
  try {
    const key = `${props.category || ''}|${props.collection || ''}`;
    if (hasLaravelApiConfig) {
      const params = new URLSearchParams({ page: String(page.value), sort: sort.value });
      if (props.category) params.set('category', props.category);
      if (props.collection) params.set('collection', props.collection);
      for (const [k,v] of Object.entries({ q: query.value, min: min.value, max: max.value, sale: sale.value ? '1' : '', stock: stock.value ? '1' : '' })) if (v) params.set(k,v);
      categories.value.forEach(v => params.append('categories[]',v)); series.value.forEach(v => params.append('series[]',v));
      if (facetKey !== key) params.set('facets','1');
      const result = await apiGet<Result>(`/catalog?${params}`, controller.signal);
      if (id !== requestId) return;
      products.value = result.data.map(p => ({ ...p, id: String(p.id), category: typeof p.category === 'object' ? p.category?.name : p.category }));
      total.value = result.total; lastPage.value = result.last_page; context.value = result.context;
      if (result.facets) { facets.value = result.facets; facetKey = key; }
    } else {
      const base = demoProducts.filter(p => p.active && (!props.category || p.category_id === props.category || p.categories?.some(c => c.slug === props.category)) && (!props.collection || p.collections?.some(c => c.slug === props.collection)));
      const names = ['DELTA','RIVER','STREAM','RAPID','PowerOcean'];
      const groups = new Map<string, {slug:string;name:string;count:number}>();
      base.forEach(p => { const slug = String(p.category_id || ''); const g = groups.get(slug) || {slug,name:p.category || slug,count:0}; g.count++; groups.set(slug,g); });
      facets.value = { categories: [...groups.values()], series: names.map(name => ({name,count:base.filter(p => p.name.toUpperCase().includes(name.toUpperCase())).length})).filter(s => s.count), min: 0, max: Math.ceil(Math.max(0,...base.map(p => Number(p.price || 0)))) };
      let filtered = base.filter(p => `${p.name} ${p.short_description || ''} ${p.category || ''}`.toLowerCase().includes(query.value.toLowerCase()) && (!categories.value.length || categories.value.includes(String(p.category_id))) && (!series.value.length || series.value.some(s => p.name.toUpperCase().includes(s.toUpperCase()))) && (!min.value || (p.price != null && Number(p.price) >= Number(min.value))) && (!max.value || (p.price != null && Number(p.price) <= Number(max.value))) && (!stock.value || isProductInStock(p)) && (!sale.value || (isProductInStock(p) && Number(p.price)>0 && Number(p.old_price)>Number(p.price))));
      if (sort.value.startsWith('price')) filtered.sort((a,b) => a.price == null ? 1 : b.price == null ? -1 : (Number(a.price)-Number(b.price))*(sort.value === 'price_desc' ? -1 : 1));
      else if (sort.value === 'newest') filtered.sort((a,b) => String(b.created_at || '').localeCompare(String(a.created_at || '')));
      else filtered = discountsFirst(filtered);
      total.value = filtered.length; lastPage.value = Math.max(1,Math.ceil(filtered.length/24)); products.value = filtered.slice((page.value-1)*24,page.value*24);
    }
  } catch { if (id === requestId) { error.value = 'Produktet nuk mund të ngarkohen. Provo përsëri.'; products.value = []; } }
  finally { clearTimeout(timeout); if (id === requestId) loading.value = false; }
}
watch(() => [route.fullPath, props.category, props.collection], load, { immediate: true });
onBeforeUnmount(() => { clearTimeout(timer); controller?.abort(); });
</script>

<template>
  <section class="container-shell py-8 sm:py-12">
    <p class="label">EcoBuka · Katalogu</p>
    <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ context?.name || title || 'Kërko produkte' }}</h1>
    <p v-if="context?.description" class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">{{ context.description }}</p>
    <label class="mt-6 block max-w-2xl"><span class="sr-only">Kërko produkte</span><input v-model="query" type="search" class="input-field w-full" placeholder="Kërko sipas emrit ose kategorisë…" @input="debounce" @keydown.enter="apply()" /></label>
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-y border-line py-4">
      <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-line px-4 py-2 text-sm font-bold lg:hidden" @click="open = true"><SlidersHorizontal class="h-4 w-4" /> Filtro <span v-if="activeCount" class="rounded-full bg-ink px-2 text-white">{{ activeCount }}</span></button>
      <p class="text-sm text-slate-600" aria-live="polite">{{ loading ? 'Duke kërkuar…' : `${total} produkte` }}</p>
      <label class="flex items-center gap-2 text-sm"><span class="hidden sm:inline">Rendit:</span><select v-model="sort" class="rounded-lg border border-line bg-white px-3 py-2" aria-label="Rendit produktet" @change="apply()"><option value="recommended">Të rekomanduara</option><option value="price_asc">Çmimi më i ulët</option><option value="price_desc">Çmimi më i lartë</option><option value="newest">Më të rejat</option></select></label>
    </div>
    <div class="mt-6 grid items-start gap-6 lg:grid-cols-[230px_minmax(0,1fr)]">
      <div v-if="open" class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="open = false"></div>
      <aside ref="filterPanel" :role="open ? 'dialog' : undefined" :aria-modal="open ? true : undefined" aria-label="Filtrimi i produkteve" @keydown="panelKeys" :class="open ? 'fixed inset-y-0 left-0 z-50 flex w-[min(90vw,360px)] flex-col bg-white shadow-xl' : 'hidden lg:block'" class="lg:sticky lg:top-24 lg:w-auto lg:rounded-xl lg:border lg:border-line lg:bg-white">
        <div class="flex items-center justify-between border-b border-line p-4"><h2 class="font-bold">Filtro produktet</h2><button v-if="open" type="button" aria-label="Mbyll filtrat" @click="open = false"><X class="h-5 w-5" /></button></div>
        <div class="flex-1 space-y-5 overflow-y-auto p-4">
          <p class="text-xs leading-5 text-slate-500">Numrat tregojnë produktet në këtë katalog, përpara filtrave.</p>
          <details open><summary class="cursor-pointer text-sm font-bold">Kategoria</summary><div class="mt-3 space-y-3"><label v-for="c in facets.categories" :key="c.slug" class="flex items-start gap-2 text-sm"><input v-model="categories" type="checkbox" :value="c.slug" class="mt-1 accent-emerald-600" @change="apply()" /><span class="flex-1">{{ c.name }}</span><span class="text-xs text-slate-500">{{ c.count }}</span></label></div></details>
          <details v-if="facets.series.length" open><summary class="cursor-pointer text-sm font-bold">Seria</summary><div class="mt-3 space-y-3"><label v-for="s in facets.series" :key="s.name" class="flex items-center gap-2 text-sm"><input v-model="series" type="checkbox" :value="s.name" class="accent-emerald-600" @change="apply()" /><span class="flex-1">{{ s.name }}</span><span class="text-xs text-slate-500">{{ s.count }}</span></label></div></details>
          <details open><summary class="cursor-pointer text-sm font-bold">Çmimi (€)</summary><div class="mt-3 grid grid-cols-2 gap-2"><label class="text-xs text-slate-500">Nga<input v-model="min" type="number" min="0" step="0.01" placeholder="0" class="mt-1 w-full rounded-lg border border-line p-2 text-sm text-ink" @input="debounce" /></label><label class="text-xs text-slate-500">Deri<input v-model="max" type="number" min="0" step="0.01" :placeholder="String(facets.max)" class="mt-1 w-full rounded-lg border border-line p-2 text-sm text-ink" @input="debounce" /></label></div><label class="mt-4 block text-xs text-slate-500">Çmimi maksimal<input :value="max || facets.max" type="range" :min="facets.min" :max="facets.max || 1" step="1" class="mt-2 w-full accent-emerald-600" @change="max = ($event.target as HTMLInputElement).value; apply()" /></label></details>
          <div class="space-y-3 border-t border-line pt-4"><label class="flex gap-2 text-sm font-semibold"><input v-model="sale" type="checkbox" class="accent-red-700" @change="apply()" /> Vetëm me zbritje</label><label class="flex gap-2 text-sm font-semibold"><input v-model="stock" type="checkbox" class="accent-emerald-600" @change="apply()" /> Vetëm në stok</label></div>
          <button v-if="activeCount" type="button" class="text-sm font-bold underline underline-offset-4" @click="clear">Pastro filtrat</button>
        </div>
        <div v-if="open" class="border-t border-line bg-white p-4"><button type="button" class="w-full rounded-full bg-ink py-3 text-sm font-bold text-white" @click="open = false">{{ loading ? 'Duke kërkuar…' : `Shfaq ${total} produkte` }}</button></div>
      </aside>
      <div class="min-w-0" :aria-busy="loading">
        <div v-if="chips.length" class="mb-5 flex flex-wrap gap-2"><button v-for="chip in chips" :key="chip.label" type="button" class="inline-flex items-center gap-2 rounded-full border border-line bg-mist px-3 py-2 text-xs font-semibold" @click="chip.remove">{{ chip.label }} <X class="h-3 w-3" /></button></div>
        <div v-if="error" role="alert" class="rounded-xl border border-red-100 bg-red-50 p-6 text-sm text-red-700">{{ error }} <button type="button" class="underline" @click="load">Provo përsëri</button></div>
        <div v-else-if="loading" class="grid grid-cols-2 gap-4 md:grid-cols-3"><div v-for="i in 6" :key="i" class="h-80 animate-pulse rounded-xl bg-mist"></div></div>
        <div v-else-if="!products.length" class="rounded-xl border border-line bg-mist px-6 py-12 text-center"><h2 class="text-xl font-bold">Nuk u gjet asnjë produkt</h2><p class="mt-2 text-sm text-slate-600">Provo një kërkim tjetër ose hiq disa filtra.</p><button type="button" class="mt-5 rounded-full bg-ink px-6 py-3 text-sm font-bold text-white" @click="clear">Pastro filtrat</button></div>
        <ProductGrid v-else :products="products" preserve-order />
        <nav v-if="lastPage > 1 && !loading" class="mt-8 flex items-center justify-center gap-4" aria-label="Faqet e produkteve"><button type="button" :disabled="page <= 1" class="rounded-lg border border-line px-4 py-2 text-sm disabled:opacity-40" @click="apply(page-1)">Mbrapa</button><span class="text-sm">{{ page }} / {{ lastPage }}</span><button type="button" :disabled="page >= lastPage" class="rounded-lg border border-line px-4 py-2 text-sm disabled:opacity-40" @click="apply(page+1)">Para</button></nav>
      </div>
    </div>
  </section>
</template>

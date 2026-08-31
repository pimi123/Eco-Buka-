<script setup lang="ts">
import {
  BatteryCharging,
  CheckCircle2,
  Flame,
  Home,
  Phone,
  ShieldCheck,
  Smartphone,
  Zap,
} from 'lucide-vue-next';
import { ref } from 'vue';
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

const heroImage = new URL('../../assets/heroes/hero-solar-home.png', import.meta.url).href;
const systemImage = new URL('../../assets/heroes/hero-delta-max.png', import.meta.url).href;
const backupImage = new URL('../../assets/heroes/hero-portable-energy.png', import.meta.url).href;
const heroVideoUrl = '/videos/powerocean-hero.mp4';
const heroVideoFailed = ref(false);

const phoneNumber = '+383 45 977 007';
const phoneHref = 'tel:+38345977007';
const whatsappHref =
  'https://wa.me/38345977007?text=P%C3%ABrsh%C3%ABndetje%20Eco%20Buka%2C%20dua%20nj%C3%AB%20ofert%C3%AB%20p%C3%ABr%20EcoFlow%20PowerOcean.';

const highlights = [
  { label: 'Kapacitet i zgjerueshëm', value: '5-45 kWh' },
  { label: 'Fuqi rezervë', value: 'Deri 12 kW' },
  { label: 'Teknologjia e baterisë', value: 'LFP' },
  { label: 'Garancion', value: '15 vjet' },
];

const benefits = [
  {
    icon: BatteryCharging,
    title: 'Filloni me një bateri',
    text: 'Nisni me një investim më të vogël dhe zgjerojeni sistemin më vonë, kur rriten nevojat e shtëpisë ose biznesit.',
  },
  {
    icon: ShieldCheck,
    title: 'I ndërtuar për besueshmëri',
    text: 'Teknologjia LFP, menaxhimi i integruar i baterisë dhe mbrojtja modulare ndihmojnë që sistemi të funksionojë në mënyrë stabile.',
  },
  {
    icon: Zap,
    title: 'Energji rezervë kur ndërpritet rryma',
    text: 'PowerOcean është i përshtatshëm për pajisjet kryesore që duhet të vazhdojnë punën gjatë ndërprerjeve të energjisë.',
  },
  {
    icon: Smartphone,
    title: 'Monitorim inteligjent',
    text: 'Përcillni prodhimin solar, konsumin, baterinë dhe sjelljen e energjisë përmes aplikacionit EcoFlow dhe web portalit.',
  },
];

const appliances = [
  'Frigoriferi',
  'Router',
  'Ndriçimi',
  'Kompjuteri',
  'Ngrirja',
  'Makina e larjes',
  'Enëlarësja',
  'Pajisje të zgjedhura të kuzhinës',
];

const specs = [
  'EcoFlow PowerOcean Three-phase për ruajtje solare dhe energji rezervë në shtëpi',
  'Inverter hibrid, module baterie LFP dhe ekosistem EcoFlow App',
  'Kapacitet baterie i zgjerueshëm nga 5 kWh deri në 45 kWh',
  'Fuqi rezervë deri në 10 kW, me kulm deri në 12 kW për periudha të shkurtra',
  'Qeliza baterie LFP me referencë deri në 6000 cikle sipas kushteve të testimit të prodhuesit',
  'Opsione instalimi në mur ose në dysheme, varësisht nga projekti',
];

useSeo({
  title: 'EcoFlow PowerOcean për Shtëpi dhe Backup Solar',
  description:
    'Faqe informative për EcoFlow PowerOcean nga Eco Buka: ruajtje solare, energji rezervë, bateri LFP, monitorim inteligjent dhe kërkesë për ofertë në Kosovë dhe Shqipëri.',
  canonicalPath: '/solutions/powerocean',
});
</script>

<template>
  <WebsiteLayout>
    <section class="relative isolate overflow-hidden bg-ink text-white">
      <img :src="heroImage" alt="Zgjidhje solare me bateri për shtëpi" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high" />
      <video
        v-if="!heroVideoFailed"
        class="absolute inset-0 hidden h-full w-full object-cover md:block"
        :poster="heroImage"
        autoplay
        muted
        loop
        playsinline
        preload="metadata"
        aria-hidden="true"
        @error="heroVideoFailed = true"
      >
        <source :src="heroVideoUrl" type="video/mp4" />
      </video>
      <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/45 to-black/20"></div>

      <div class="container-shell relative z-10 grid min-h-[640px] items-center py-16 sm:min-h-[700px] lg:min-h-[760px]">
        <div class="max-w-3xl">
          <p class="text-xs font-black uppercase tracking-[0.26em] text-orange-400 sm:text-sm">
            EcoFlow PowerOcean
          </p>
          <h1 class="mt-4 text-4xl font-black leading-[0.95] sm:text-6xl lg:text-7xl">
            Zgjidhje solare me bateri për shtëpi
          </h1>
          <p class="mt-5 max-w-2xl text-base font-semibold leading-7 text-white/90 sm:text-xl sm:leading-8">
            Sistem i zgjerueshëm për ruajtje solare dhe energji rezervë për shtëpi, zyra dhe biznese që kërkojnë më shumë pavarësi energjetike.
          </p>
          <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a class="inline-flex min-h-12 items-center justify-center rounded-full bg-white px-7 text-sm font-black text-ink transition hover:scale-[1.02] hover:bg-slate-100" :href="phoneHref">
              Merr Ofertë
            </a>
            <a class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/45 bg-white/10 px-7 text-sm font-black text-white backdrop-blur transition hover:bg-white/20" :href="whatsappHref" target="_blank" rel="noreferrer">
              WhatsApp Eco Buka
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="border-b border-line bg-white">
      <div class="container-shell grid gap-3 py-5 sm:grid-cols-2 lg:grid-cols-4">
        <article v-for="item in highlights" :key="item.label" class="rounded-lg border border-line bg-mist px-4 py-5">
          <p class="text-2xl font-black text-ink">{{ item.value }}</p>
          <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500">{{ item.label }}</p>
        </article>
      </div>
    </section>

    <section class="container-shell py-12 sm:py-16 lg:py-20">
      <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
        <div>
          <p class="label">Pse PowerOcean</p>
          <h2 class="mt-2 text-3xl font-black leading-tight text-ink sm:text-4xl">
            I krijuar për shtëpi që kanë nevojë për energji rezervë, ruajtje solare dhe kontroll afatgjatë.
          </h2>
        </div>
        <p class="text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
          PowerOcean është sistem modular energjie për shtëpi. Nuk është vetëm bateri: kombinon inverterin, modulet e baterisë, monitorimin dhe logjikën e energjisë rezervë në një zgjidhje të zgjerueshme.
        </p>
      </div>

      <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article v-for="benefit in benefits" :key="benefit.title" class="rounded-lg border border-line bg-white p-5 shadow-sm">
          <component :is="benefit.icon" class="h-7 w-7 text-energy" />
          <h3 class="mt-4 text-lg font-black text-ink">{{ benefit.title }}</h3>
          <p class="mt-3 text-sm leading-6 text-slate-600">{{ benefit.text }}</p>
        </article>
      </div>
    </section>

    <section class="bg-mist py-12 sm:py-16 lg:py-20">
      <div class="container-shell grid gap-8 lg:grid-cols-2 lg:items-center">
        <div class="overflow-hidden rounded-lg">
          <img :src="systemImage" alt="Sistem modular PowerOcean me bateri për shtëpi" class="h-full min-h-[320px] w-full object-cover" loading="lazy" />
        </div>
        <div>
          <p class="label">Investim fleksibil</p>
          <h2 class="mt-2 text-3xl font-black leading-tight text-ink sm:text-4xl">
            Filloni me kapacitet më të vogël dhe zgjerojeni kur projekti ka nevojë për më shumë energji.
          </h2>
          <p class="mt-4 text-base leading-7 text-slate-600">
            Sistemi mund të fillojë me një modul baterie 5 kWh dhe të zgjerohet deri në 45 kWh. Kjo e bën të përshtatshëm për instalime me faza, ku klienti mund të fillojë tani dhe të shtojë kapacitet më vonë.
          </p>
          <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg bg-white p-4">
              <p class="text-xl font-black">5 kWh</p>
              <p class="mt-1 text-xs font-semibold text-slate-500">Moduli fillestar</p>
            </div>
            <div class="rounded-lg bg-white p-4">
              <p class="text-xl font-black">45 kWh</p>
              <p class="mt-1 text-xs font-semibold text-slate-500">Kapacitet i zgjerueshëm</p>
            </div>
            <div class="rounded-lg bg-white p-4">
              <p class="text-xl font-black">LFP</p>
              <p class="mt-1 text-xs font-semibold text-slate-500">Teknologjia e baterisë</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container-shell py-12 sm:py-16 lg:py-20">
      <div class="grid gap-8 lg:grid-cols-[1fr_0.9fr] lg:items-center">
        <div>
          <p class="label">Mbrojtje rezervë</p>
          <h2 class="mt-2 text-3xl font-black leading-tight text-ink sm:text-4xl">
            Mbani pajisjet kryesore në punë kur rrjeti elektrik ndërpritet.
          </h2>
          <p class="mt-4 text-base leading-7 text-slate-600">
            Për planifikimin e energjisë rezervë, Eco Buka mund ta ndihmojë klientin të përcaktojë cilat pajisje duhet të mbulohen dhe ta dimensionojë sistemin sipas përdorimit real.
          </p>
          <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div v-for="item in appliances" :key="item" class="flex min-h-12 items-center gap-2 rounded-lg border border-line bg-white px-3 text-sm font-bold text-ink">
              <CheckCircle2 class="h-4 w-4 shrink-0 text-energy" />
              <span>{{ item }}</span>
            </div>
          </div>
        </div>
        <div class="overflow-hidden rounded-lg">
          <img :src="backupImage" alt="Energji rezervë për shtëpi dhe punë" class="h-full min-h-[320px] w-full object-cover" loading="lazy" />
        </div>
      </div>
    </section>

    <section class="bg-ink py-12 text-white sm:py-16 lg:py-20">
      <div class="container-shell grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
        <div>
          <p class="text-xs font-black uppercase tracking-[0.24em] text-orange-400">Siguri dhe garanci</p>
          <h2 class="mt-3 text-3xl font-black leading-tight sm:text-4xl">
            I projektuar për përdorim afatgjatë të energjisë në shtëpi.
          </h2>
          <p class="mt-4 text-base leading-7 text-slate-300">
            PowerOcean fokusohet në sigurinë e baterisë, kontrollin inteligjent dhe jetëgjatësinë e sistemit. Dizajni final dhe detajet e instalimit duhet të konfirmohen gjithmonë sipas lokacionit konkret.
          </p>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
          <article class="rounded-lg border border-white/10 bg-white/5 p-5">
            <Flame class="h-7 w-7 text-orange-400" />
            <h3 class="mt-4 text-lg font-black">Mbrojtje nga zjarri</h3>
            <p class="mt-2 text-sm leading-6 text-slate-300">Sistemet e sigurisë nga prodhuesi ndihmojnë në mbrojtjen e funksionimit të baterisë.</p>
          </article>
          <article class="rounded-lg border border-white/10 bg-white/5 p-5">
            <ShieldCheck class="h-7 w-7 text-energy" />
            <h3 class="mt-4 text-lg font-black">BMS për çdo bateri</h3>
            <p class="mt-2 text-sm leading-6 text-slate-300">Menaxhimi i baterisë ndihmon në monitorimin dhe mbrojtjen e moduleve individuale.</p>
          </article>
          <article class="rounded-lg border border-white/10 bg-white/5 p-5">
            <Home class="h-7 w-7 text-ocean" />
            <h3 class="mt-4 text-lg font-black">Garanci 15 vjet</h3>
            <p class="mt-2 text-sm leading-6 text-slate-300">EcoFlow prezanton garanci afatgjatë për sistemin PowerOcean.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="container-shell py-12 sm:py-16 lg:py-20">
      <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div>
          <p class="label">Përmbledhje e sistemit</p>
          <h2 class="mt-2 text-3xl font-black leading-tight text-ink sm:text-4xl">Çfarë përfshin zgjidhja</h2>
          <p class="mt-4 text-base leading-7 text-slate-600">
            Instalimi i saktë varet nga objekti, konsumi, sistemi solar dhe prioritetet për energji rezervë. Eco Buka duhet ta konfirmojë konfigurimin final para blerjes.
          </p>
        </div>
        <div class="grid gap-3">
          <div v-for="spec in specs" :key="spec" class="rounded-lg border border-line bg-white p-4 text-sm font-semibold leading-6 text-slate-700">
            {{ spec }}
          </div>
        </div>
      </div>
    </section>

    <section class="container-shell pb-12 sm:pb-16 lg:pb-20">
      <div class="overflow-hidden rounded-lg bg-ink">
        <div class="grid gap-0 lg:grid-cols-[1.1fr_0.9fr]">
          <div class="p-6 text-white sm:p-8 lg:p-10">
            <p class="text-xs font-black uppercase tracking-[0.24em] text-orange-400">Kërko ofertë për projekt</p>
            <h2 class="mt-3 text-3xl font-black leading-tight sm:text-4xl">
              Tregoni Eco Buka çfarë dëshironi të furnizoni me energji.
            </h2>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-300">
              Për PowerOcean, konfigurimi i duhur varet nga shtëpia, biznesi, panelet solare, pajisjet për energji rezervë dhe buxheti. Kontakto Eco Buka për dimensionim dhe detaje të ofertës.
            </p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
              <a class="inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-white px-7 text-sm font-black text-ink transition hover:bg-slate-100" :href="phoneHref">
                <Phone class="h-4 w-4" />
                {{ phoneNumber }}
              </a>
              <a class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/20 bg-white/10 px-7 text-sm font-black text-white transition hover:bg-white/20" :href="whatsappHref" target="_blank" rel="noreferrer">
                Kërko në WhatsApp
              </a>
            </div>
          </div>
          <div class="relative min-h-[280px]">
            <img :src="heroImage" alt="Konsultim për energji solare në shtëpi" class="absolute inset-0 h-full w-full object-cover opacity-80" loading="lazy" />
          </div>
        </div>
      </div>
    </section>
  </WebsiteLayout>
</template>

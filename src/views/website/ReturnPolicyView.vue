<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-end lg:gap-10">
        <div>
          <p class="label">Politikat e klientit</p>
          <h1 class="mt-2 text-3xl font-black leading-tight text-ink sm:text-4xl lg:text-5xl">
            Politika e kthimit dhe zëvendësimit të produkteve
          </h1>
        </div>
        <p class="text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
          Kjo faqe shpjegon kushtet kryesore për kthimin, kontrollin e garancionit dhe zëvendësimin e produkteve të blera nga EcoBuka.
        </p>
      </div>
    </section>

    <section class="container-shell py-10 sm:py-12 lg:py-14">
      <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-950 sm:p-5">
        Produktet funksionale dhe pa defekte nuk mund të kthehen vetëm për shkak të ndërrimit të mendjes së klientit. Kthimi ose zëvendësimi shqyrtohet kur produkti ka defekt të mbuluar nga garancioni, është dëmtuar gjatë transportit ose është dërguar produkt i gabuar.
      </div>

      <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="item in policyItems"
          :key="item.title"
          class="rounded-lg border border-line bg-white p-5 shadow-sm"
        >
          <p class="label">{{ item.label }}</p>
          <h2 class="mt-2 text-xl font-black leading-7 text-ink">{{ item.title }}</h2>
          <p class="mt-3 text-sm leading-6 text-slate-600">{{ item.description }}</p>
        </article>
      </div>

      <div class="mt-6 grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
          <p class="label">Si bëhet kërkesa</p>
          <h2 class="mt-2 text-2xl font-black text-ink">Procedura për kthim ose garancion</h2>
          <ol class="mt-5 grid gap-4 text-sm leading-6 text-slate-600">
            <li v-for="(step, index) in steps" :key="step" class="flex gap-3">
              <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-ink text-xs font-black text-white">{{ index + 1 }}</span>
              <span>{{ step }}</span>
            </li>
          </ol>
        </article>

        <article class="rounded-lg border border-line bg-ink p-5 text-white shadow-sm sm:p-6">
          <p class="text-xs font-black uppercase tracking-wide text-white/60">Kontakt për mbështetje</p>
          <h2 class="mt-2 text-2xl font-black">Na kontaktoni para kthimit</h2>
          <p class="mt-4 text-sm leading-6 text-white/75">
            Përpara se të dërgoni një produkt për kontroll, kontaktoni EcoBuka që ekipi të verifikojë rastin dhe t’ju japë udhëzimet e sakta.
          </p>
          <div class="mt-5 grid gap-3 text-sm font-semibold text-white/85">
            <a class="hover:text-white hover:underline" href="tel:+38345977007">Kosovë: +383 45 977 007</a>
            <a class="hover:text-white hover:underline" href="tel:+355688049525">Shqipëri: +355 68 804 9525</a>
            <a class="hover:text-white hover:underline" href="mailto:info@ecoflowks.com">info@ecoflowks.com</a>
          </div>
          <RouterLink class="mt-6 inline-flex min-h-11 items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-black text-ink transition hover:bg-mist" to="/contact?purpose=return">
            Kontakto EcoBuka
          </RouterLink>
        </article>
      </div>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

const policyItems = [
  {
    label: 'Afati',
    title: 'Kthimi lidhet me periudhën e garancionit',
    description: 'Produkti mund të kthehet për kontroll gjatë periudhës së vlefshme të garancionit, nëse dyshohet për defekt që mbulohet nga kushtet e garancionit.',
  },
  {
    label: 'Gjendja',
    title: 'Produkti duhet të jetë i kompletuar',
    description: 'Produkti duhet të përmbajë pjesët, komponentët dhe aksesorët që janë dorëzuar me të. Përdorimi normal nuk e anulon automatikisht të drejtën për garancion.',
  },
  {
    label: 'Rastet',
    title: 'Kur pranohet kthimi',
    description: 'Kthimi shqyrtohet për defekte të mbuluara nga garancioni, dëmtime gjatë transportit ose kur klientit i është dorëzuar produkt i gabuar.',
  },
  {
    label: 'Transporti',
    title: 'Kostoja mbulohet për raste të aprovuara',
    description: 'Kur rasti aprovohet për defekt garancioni, dëmtim gjatë transportit ose produkt të gabuar, kostoja e kthimit mbulohet nga EcoBuka.',
  },
  {
    label: 'Zgjidhja',
    title: 'EcoBuka ofron zëvendësim',
    description: 'Për rastet e mbuluara nga kjo politikë, kompensimi bëhet në formë zëvendësimi me produkt funksional dhe pa defekt.',
  },
  {
    label: 'Dëshmia',
    title: 'Kërkohet faturë ose numër porosie',
    description: 'Për verifikimin e blerjes dhe vlefshmërisë së garancionit kërkohet dëshmi e blerjes, si fatura ose numri i porosisë.',
  },
];

const steps = [
  'Klienti kontakton EcoBuka përmes telefonit, emailit, WhatsApp-it, formularit në website ose kontaktit direkt.',
  'Ekipi shqyrton rastin dhe kërkon informacionet e nevojshme për produktin, defektin ose dëmtimin e raportuar.',
  'Nëse rasti pranohet për kontroll, klienti merr udhëzime për dorëzim ose transport të produktit në depot e EcoBuka në Prizren, Kosovë.',
  'Pas kontrollit teknik, EcoBuka konfirmon nëse rasti mbulohet nga garancioni ose nga politika e kthimit.',
  'Nëse produkti për zëvendësim është në stok, zëvendësimi synohet të realizohet brenda një periudhe deri në 2 javë pas aprovimit.',
  'Nëse produkti nuk është në stok, klienti informohet për pritjen deri në ardhjen e produktit nga depoja furnizuese.',
];

useSeo({
  title: 'Politika e Kthimit dhe Zëvendësimit',
  description: 'Lexoni kushtet e EcoBuka për kthimin, garancionin, dëmtimet gjatë transportit, produktet e gabuara dhe zëvendësimin e produkteve.',
  canonicalPath: '/politika-e-kthimit',
});
</script>

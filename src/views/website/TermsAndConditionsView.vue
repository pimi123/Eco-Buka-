<template>
  <WebsiteLayout>
    <section class="container-shell py-8 sm:py-12">
      <div class="max-w-3xl">
        <p class="label">Dokument ligjor</p>
        <h1 class="mt-2 text-3xl font-black sm:text-4xl">Termat dhe Kushtet</h1>
        <p class="mt-4 text-sm leading-7 text-slate-600 sm:text-base">
          Këto terma përcaktojnë mënyrën e përdorimit të faqes Eco Buka, porositë, pagesat, dërgesën,
          komunikimin me klientin dhe përgjegjësitë kryesore gjatë blerjes së produkteve.
        </p>
      </div>

      <div class="mt-8 grid gap-5 lg:grid-cols-[1fr_340px]">
        <article class="grid gap-5">
          <section v-for="section in sections" :key="section.title" class="rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-xl font-black">{{ section.title }}</h2>
            <div class="mt-3 grid gap-3 text-sm leading-7 text-slate-600 sm:text-base">
              <p v-for="paragraph in section.paragraphs" :key="paragraph">{{ paragraph }}</p>
            </div>
          </section>
        </article>

        <aside class="h-fit rounded-lg bg-ink p-5 text-white shadow-sm sm:p-6">
          <p class="text-xs font-black uppercase tracking-wide text-white/60">Kontakt</p>
          <h2 class="mt-2 text-2xl font-black">Eco Buka</h2>
          <div class="mt-4 grid gap-2 text-sm leading-6 text-slate-200">
            <p>BUKA sh.p.k</p>
            <p>Rr. Tringë Smajli p.n., 10000 Prishtinë, Republika e Kosovës</p>
            <p>Lokacioni i punës: Prizren, tranziti i vjetër</p>
            <a class="font-bold underline underline-offset-4" href="mailto:ecobuka.ks@gmail.com">ecobuka.ks@gmail.com</a>
            <a class="font-bold underline underline-offset-4" href="tel:+38345977007">+383 45 977 007</a>
          </div>
        </aside>
      </div>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

const sections = [
  {
    title: '1. Informata për shitësin',
    paragraphs: [
      'Faqja menaxhohet nga BUKA sh.p.k, me brendin tregtar Eco Buka. Eco Buka është distributor i produkteve EcoFlow dhe ofron zgjidhje për furnizim, ruajtje dhe përdorim të energjisë për shtëpi, biznese dhe përdorim në natyrë.',
      'Të dhënat e biznesit, adresa, kontaktet dhe informatat zyrtare janë të publikuara në faqen “Informata për kompaninë”.',
    ],
  },
  {
    title: '2. Produktet dhe informacioni në faqe',
    paragraphs: [
      'Eco Buka synon që përshkrimet, fotografitë, specifikat, çmimet dhe disponueshmëria e produkteve të jenë sa më të sakta. Megjithatë, disa detaje mund të ndryshojnë varësisht nga modeli, konfigurimi, furnizimi ose konfirmimi final nga ekipi ynë.',
      'Produktet me para-porosi, si disa zgjidhje PowerOcean ose konfigurime të veçanta, konfirmohen veçmas para dorëzimit ose instalimit.',
    ],
  },
  {
    title: '3. Porosia dhe pranimi i kushteve',
    paragraphs: [
      'Për të vazhduar me porosi dhe pagesë, klienti duhet të pranojë këto Terma dhe Kushte dhe Politikën e Kthimit. Pranimi ruhet si pjesë e të dhënave të porosisë.',
      'Pas dërgimit të porosisë, klienti pranon email konfirmimi me numrin e porosisë, produktet e porositura, sasitë, totalin, adresën dhe informatat për konfirmimin e dërgesës.',
    ],
  },
  {
    title: '4. Pagesa',
    paragraphs: [
      'Pagesat online realizohen përmes ofruesit bankar të pagesave. Eco Buka nuk ruan numrin e plotë të kartelës, CVV-në ose datën e skadimit të kartelës.',
      'Porosia konsiderohet e paguar vetëm pasi banka ose ofruesi i pagesës kthen konfirmim të vlefshëm dhe sistemi e verifikon pagesën.',
    ],
  },
  {
    title: '5. Dërgesa dhe koha e arritjes',
    paragraphs: [
      'Koha e arritjes varet nga disponueshmëria e produktit, lokacioni i klientit, mënyra e dërgesës dhe nëse produkti kërkon konfigurim ose konfirmim shtesë.',
      'Eco Buka kontakton klientin për të konfirmuar disponueshmërinë, afatin e pritshëm të arritjes dhe detajet finale të dërgesës.',
    ],
  },
  {
    title: '6. Kthimet, anulimet dhe garancioni',
    paragraphs: [
      'Kthimet dhe anulimet trajtohen sipas Politikës së Kthimit të publikuar në faqe. Klienti duhet të kontaktojë Eco Buka para kthimit të produktit për të marrë udhëzimet e sakta.',
      'Garancioni zbatohet sipas kushteve të produktit dhe dokumentacionit përkatës. Për produktet Eco Buka, periudha e komunikuar e garancionit është 5 vjet, përveç rasteve kur specifikohet ndryshe për produkt të caktuar.',
    ],
  },
  {
    title: '7. Dëshmitë e porosisë dhe komunikimi',
    paragraphs: [
      'Eco Buka ruan të dhënat e porosisë, produktet e porositura, pranimin e kushteve, statusin e pagesës dhe komunikimin e nevojshëm për përpunimin e porosisë.',
      'Këto të dhëna mund të përdoren si dëshmi në rast ankese, verifikimi bankar, kërkese për kthim ose kontestimi të pagesës.',
    ],
  },
  {
    title: '8. Përditësimi i termeve',
    paragraphs: [
      'Eco Buka mund t’i përditësojë këto terma kur ndryshojnë proceset, produktet, pagesat ose kërkesat ligjore. Versioni i publikuar në faqe vlen në momentin kur klienti kryen porosinë.',
    ],
  },
];

useSeo({
  title: 'Termat dhe Kushtet',
  description: 'Lexoni Termat dhe Kushtet e Eco Buka për porositë, pagesat, dërgesën, kthimet, garancionin dhe komunikimin me klientin.',
  canonicalPath: '/termat-dhe-kushtet',
});
</script>

<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-end lg:gap-10">
        <div>
          <p class="label">Cookies dhe ruajtja lokale</p>
          <h1 class="mt-2 text-3xl font-black leading-tight text-ink sm:text-4xl lg:text-5xl">
            Politika e Cookies
          </h1>
        </div>
        <p class="text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
          Kjo politikë shpjegon se si EcoBuka përdor cookies dhe ruajtje lokale për funksionimin e faqes, sigurinë, shportën dhe preferencat bazë të përdoruesit.
        </p>
      </div>
    </section>

    <section class="container-shell py-10 sm:py-12 lg:py-14">
      <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-sm font-semibold leading-6 text-emerald-950 sm:p-5">
        Aktualisht EcoBuka përdor cookies dhe ruajtje lokale të domosdoshme për funksionimin e faqes, shportës, sigurinë dhe ruajtjen e zgjedhjeve bazë.
      </div>

      <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="item in cookieItems"
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
          <p class="label">Kategoritë</p>
          <h2 class="mt-2 text-2xl font-black text-ink">Llojet që përdoren në faqe</h2>
          <div class="mt-5 grid gap-4">
            <div v-for="category in cookieCategories" :key="category.title" class="rounded-lg bg-mist p-4">
              <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-black text-ink">{{ category.title }}</h3>
                <span class="rounded-full bg-ink px-2.5 py-1 text-xs font-black text-white">Të domosdoshme</span>
              </div>
              <p class="mt-2 text-sm leading-6 text-slate-600">{{ category.description }}</p>
            </div>
          </div>
        </article>

        <article class="rounded-lg border border-line bg-ink p-5 text-white shadow-sm sm:p-6">
          <p class="text-xs font-black uppercase tracking-wide text-white/60">Zgjedhjet e përdoruesit</p>
          <h2 class="mt-2 text-2xl font-black">Si mund t’i kontrolloni cookies</h2>
          <p class="mt-4 text-sm leading-6 text-white/75">
            Cookies dhe ruajtja lokale që janë të domosdoshme nuk mund të çaktivizohen plotësisht pa ndikuar në funksionimin e faqes, shportës ose formularëve.
          </p>
          <ul class="mt-5 grid gap-3 text-sm leading-6 text-white/80">
            <li>Bllokoni cookies nga cilësimet e shfletuesit tuaj.</li>
            <li>Fshini të dhënat e ruajtura lokale nga cilësimet e shfletuesit.</li>
            <li>Na kontaktoni për pyetje rreth privatësisë ose cookies.</li>
          </ul>
          <RouterLink class="mt-6 inline-flex min-h-11 items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-black text-ink transition hover:bg-mist" to="/contact?purpose=support">
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

const cookieItems = [
  {
    label: 'Funksionimi',
    title: 'Cookies të domosdoshme',
    description: 'Këto përdoren për funksionimin bazë të faqes, sigurinë, ruajtjen e sesionit dhe mbrojtjen e formularëve.',
  },
  {
    label: 'Shporta',
    title: 'Ruajtja lokale për shportën',
    description: 'Shporta mund të ruhet në browser përmes localStorage, që produktet e zgjedhura të mos humbin kur faqja rifreskohet ose kur përdoruesi lëviz në faqe të tjera.',
  },
  {
    label: 'Siguria',
    title: 'Mbrojtja e formularëve',
    description: 'Cookies teknike mund të përdoren për të mbrojtur formularët, për të parandaluar abuzimin dhe për të mbajtur komunikimin me faqen të sigurt.',
  },
  {
    label: 'Preferenca',
    title: 'Zgjedhje bazë',
    description: 'Faqja mund të ruajë zgjedhje të thjeshta të përdoruesit, si pranimi i njoftimit për cookies ose preferenca të tjera bazë të faqes.',
  },
];

const cookieCategories = [
  {
    title: 'Të domosdoshme',
    description: 'Nevojiten për funksionimin e faqes, shportës, sigurisë, login-it dhe mbrojtjen e formave. Pa to disa pjesë të faqes mund të mos funksionojnë si duhet.',
  },
  {
    title: 'Ruajtje lokale',
    description: 'Përdoret për shportën dhe funksione të ngjashme që e bëjnë faqen më praktike gjatë përdorimit.',
  },
  {
    title: 'Preferenca',
    description: 'Mund të ruajë zgjedhje të thjeshta të përdoruesit në mënyrë që faqja të funksionojë më mirë gjatë vizitave të ardhshme.',
  },
];

useSeo({
  title: 'Politika e Cookies',
  description: 'Lexoni Politikën e Cookies së EcoBuka për cookies të domosdoshme, ruajtjen lokale, shportën, sesionin, sigurinë dhe preferencat bazë.',
  canonicalPath: '/politika-e-cookies',
});
</script>

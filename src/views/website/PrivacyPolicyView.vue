<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-end lg:gap-10">
        <div>
          <p class="label">Politikat e privatësisë</p>
          <h1 class="mt-2 text-3xl font-black leading-tight text-ink sm:text-4xl lg:text-5xl">
            Politika e Privatësisë
          </h1>
        </div>
        <p class="text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
          Kjo politikë shpjegon se si EcoBuka mbledh, përdor, ruan dhe mbron të dhënat personale gjatë përdorimit të faqes, porosive, kërkesave për ofertë, kthimeve dhe komunikimeve me klientët.
        </p>
      </div>
    </section>

    <section class="container-shell py-10 sm:py-12 lg:py-14">
      <div class="rounded-lg border border-amber-100 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-950 sm:p-5">
        Email për kërkesa lidhur me privatësinë:
        <a class="underline underline-offset-4" href="mailto:ecobuka.ks@gmail.com">ecobuka.ks@gmail.com</a>.
      </div>

      <div class="mt-6 grid gap-5 lg:grid-cols-[0.8fr_1.2fr]">
        <aside class="rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
          <p class="label">Kontrolluesi i të dhënave</p>
          <h2 class="mt-2 text-2xl font-black text-ink">Buka SH.P.K. / EcoBuka</h2>
          <div class="mt-5 grid gap-3 text-sm leading-6 text-slate-600">
            <p><span class="font-bold text-ink">Numri i biznesit:</span> 810197493</p>
            <p><span class="font-bold text-ink">Numri fiskal:</span> 810197493</p>
            <p><span class="font-bold text-ink">Adresa:</span> Rr. Tringë Smajli, p.n., 10000 Prishtinë, Republika e Kosovës</p>
            <p><span class="font-bold text-ink">Lokacioni operativ:</span> Prizren, Tranziti i Vjetër</p>
            <p><span class="font-bold text-ink">Telefon:</span> +383 45 977 007</p>
            <p><span class="font-bold text-ink">Email:</span> ecobuka.ks@gmail.com</p>
          </div>
        </aside>

        <article class="rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
          <p class="label">Përmbledhje</p>
          <h2 class="mt-2 text-2xl font-black text-ink">Kur zbatohet kjo politikë</h2>
          <p class="mt-4 text-sm leading-7 text-slate-600">
            Kjo politikë zbatohet kur vizitoni faqen, bëni porosi, kërkoni ofertë, kontaktoni EcoBuka përmes formularit, telefonit, emailit ose WhatsApp-it, kërkoni kthim, garanci, servis ose mbështetje.
          </p>
          <p class="mt-3 text-sm leading-7 text-slate-600">
            EcoBuka është distributor i produkteve EcoFlow dhe përdor të dhënat personale vetëm për qëllime të ligjshme, të nevojshme dhe të lidhura me shërbimin ndaj klientit.
          </p>
        </article>
      </div>

      <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="item in privacyItems"
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
          <p class="label">Të drejtat e klientit</p>
          <h2 class="mt-2 text-2xl font-black text-ink">Çfarë mund të kërkoni</h2>
          <ul class="mt-5 grid gap-3 text-sm leading-6 text-slate-600">
            <li v-for="right in customerRights" :key="right" class="flex gap-3">
              <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
              <span>{{ right }}</span>
            </li>
          </ul>
        </article>

        <article class="rounded-lg border border-line bg-ink p-5 text-white shadow-sm sm:p-6">
          <p class="text-xs font-black uppercase tracking-wide text-white/60">Kontakt për privatësi</p>
          <h2 class="mt-2 text-2xl font-black">Kërkesa për të dhënat personale</h2>
          <p class="mt-4 text-sm leading-6 text-white/75">
            Për qasje, korrigjim, fshirje, kufizim, kundërshtim ose tërheqje të pëlqimit, na kontaktoni me subjektin “Kërkesë për privatësi”.
          </p>
          <div class="mt-5 grid gap-3 text-sm font-semibold text-white/85">
            <a class="hover:text-white hover:underline" href="mailto:ecobuka.ks@gmail.com">ecobuka.ks@gmail.com</a>
            <a class="hover:text-white hover:underline" href="tel:+38345977007">Kosovë: +383 45 977 007</a>
            <RouterLink class="hover:text-white hover:underline" to="/contact?purpose=support">Dërgo kërkesë përmes faqes</RouterLink>
          </div>
        </article>
      </div>

      <article class="mt-6 rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
        <p class="label">Baza ligjore</p>
        <h2 class="mt-2 text-2xl font-black text-ink">Ligjet dhe përditësimet</h2>
        <p class="mt-4 text-sm leading-7 text-slate-600">
          Kjo politikë është përgatitur duke marrë parasysh Ligjin Nr. 06/L-082 për Mbrojtjen e të Dhënave Personale, kërkesat për tregtinë elektronike, kontratat në distancë, faturimin, kontabilitetin dhe detyrimet e tjera të zbatueshme në Republikën e Kosovës.
        </p>
        <p class="mt-3 text-sm leading-7 text-slate-600">
          Politika mund të përditësohet kur ndryshojnë shërbimet, mënyrat e pagesës, transportuesit, ofruesit teknikë, praktikat e përpunimit ose kërkesat ligjore.
        </p>
      </article>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

const privacyItems = [
  {
    label: 'Të dhënat',
    title: 'Çfarë mbledhim',
    description: 'Mund të mbledhim emrin, mbiemrin, telefonin, emailin, adresën e dorëzimit, qytetin, shtetin, të dhënat e porosisë, kërkesat për ofertë, kthim, garanci dhe komunikimet me klientin.',
  },
  {
    label: 'Porositë',
    title: 'Pse i përdorim',
    description: 'Të dhënat përdoren për konfirmimin e porosisë, përgatitjen e ofertës, dorëzimin, faturimin, pagesën, kthimin, garancinë, servisin dhe mbështetjen ndaj klientit.',
  },
  {
    label: 'Pagesat',
    title: 'Të dhënat e kartelës',
    description: 'Kur pagesa online të aktivizohet, të dhënat e kartelës përpunohen nga banka ose procesuesi i autorizuar. EcoBuka nuk ruan numrin e plotë të kartelës, CVV/CVC, PIN ose kredenciale bankare.',
  },
  {
    label: 'Teknike',
    title: 'IP, browser dhe siguri',
    description: 'Faqja mund të përpunojë IP-në, llojin e pajisjes, browser-in, regjistrat teknikë dhe të dhënat e sesionit për siguri, diagnostikim, parandalim abuzimi dhe funksionim të faqes.',
  },
  {
    label: 'Cookies',
    title: 'Cookies dhe localStorage',
    description: 'Faqja përdor cookies të domosdoshme dhe localStorage për funksione si shporta, sesioni dhe siguria. Cookies analitike ose marketingu përdoren vetëm nëse aktivizohen dhe merret pëlqimi i nevojshëm.',
  },
  {
    label: 'Palët e treta',
    title: 'Me kë mund të ndahen',
    description: 'Të dhënat mund të ndahen vetëm kur është e nevojshme me transportues, bankë ose procesues pagese, ofrues teknikë, kontabilistë, këshilltarë profesionalë ose autoritete publike kur kërkohet me ligj.',
  },
  {
    label: 'Ruajtja',
    title: 'Sa kohë ruhen',
    description: 'Të dhënat ruhen vetëm aq sa nevojitet për porosi, faturim, garanci, kthim, mbështetje, detyrime ligjore ose mbrojtje të kërkesave. Kërkesat që nuk çojnë në porosi mund të ruhen deri në 12 muaj.',
  },
  {
    label: 'Marketing',
    title: 'Komunikime promocionale',
    description: 'Komunikimet promocionale dërgohen vetëm kur ekziston bazë ligjore dhe, kur kërkohet, pëlqim paraprak. Pëlqimi mund të tërhiqet në çdo kohë.',
  },
  {
    label: 'Siguria',
    title: 'Si i mbrojmë të dhënat',
    description: 'EcoBuka përdor masa teknike dhe organizative si kufizim qasjeje, fjalëkalime të sigurta, lidhje të enkriptuara, kopje rezervë, përditësime dhe monitorim të sigurisë.',
  },
];

const customerRights = [
  'Të kërkoni konfirmim nëse EcoBuka përpunon të dhënat tuaja personale.',
  'Të kërkoni qasje në të dhëna dhe një kopje të tyre.',
  'Të kërkoni korrigjimin ose plotësimin e të dhënave të pasakta ose të paplota.',
  'Të kërkoni fshirjen ose kufizimin e përpunimit kur kushtet ligjore plotësohen.',
  'Të kundërshtoni marketingun e drejtpërdrejtë dhe përpunimin mbi bazën e interesit legjitim.',
  'Të tërhiqni pëlqimin kur përpunimi bazohet në pëlqim.',
  'Të paraqisni ankesë pranë Agjencisë për Informim dhe Privatësi.',
];

useSeo({
  title: 'Politika e Privatësisë',
  description: 'Lexoni Politikën e Privatësisë së EcoBuka për të dhënat personale, porositë, pagesat, cookies, kthimet, garancinë dhe të drejtat e klientit.',
  canonicalPath: '/politika-e-privatesise',
});
</script>

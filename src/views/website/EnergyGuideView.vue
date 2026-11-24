<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';
useSeo({
  title: 'Si të zgjidhni një stacion portativ energjie',
  description: 'Mësoni dallimin mes W dhe Wh, si të vlerësoni kohën e punës së një baterie dhe çfarë të kontrolloni para blerjes së një stacioni energjie.',
  canonicalPath: '/guides/zgjedhja-e-stacionit-te-energjise',
});
</script>

<template>
  <WebsiteLayout>
    <article class="container-shell max-w-4xl py-10 sm:py-16">
      <p class="label">Udhëzues blerjeje · EcoBuka</p>
      <h1 class="mt-3 text-3xl font-black leading-tight sm:text-4xl">Si të zgjidhni një stacion portativ energjie</h1>
      <p class="mt-5 text-lg leading-8 text-slate-600">Filloni me pajisjet që dëshironi të furnizoni, fuqinë që kërkojnë dhe sa kohë duhet të punojnë. Kapaciteti i baterisë dhe fuqia e daljes janë dy gjëra të ndryshme.</p>
      <section class="mt-10 space-y-4">
        <h2 class="text-2xl font-black">Çfarë dallimi ka mes W dhe Wh?</h2>
        <p class="leading-7 text-slate-700"><strong>W (vat)</strong> tregon fuqinë që një pajisje kërkon në një moment. <strong>Wh (vat-orë)</strong> tregon sasinë e energjisë që ruan bateria. Një pajisje që përdor vazhdimisht 100 W konsumon afërsisht 100 Wh në një orë.</p>
        <p class="leading-7 text-slate-700">Mblidhni fuqinë e pajisjeve që do të përdoren njëkohësisht. Dalja e vazhdueshme e stacionit duhet ta përballojë këtë ngarkesë. Për frigoriferë, pompa dhe pajisje me motor kontrolloni edhe fuqinë e nisjes sipas prodhuesit.</p>
      </section>
      <section class="mt-10 space-y-4">
        <h2 class="text-2xl font-black">Sa kohë mund të punojë një pajisje me bateri?</h2>
        <p class="leading-7 text-slate-700">Për një ngarkesë konstante, një vlerësim i thjeshtuar është:</p>
        <p class="rounded-lg bg-mist p-5 font-bold leading-7">Koha në orë ≈ kapaciteti në Wh × pjesa e shfrytëzueshme ÷ ngarkesa në W</p>
        <p class="leading-7 text-slate-700">Për shembull, një bateri 1,024 Wh me ngarkesë konstante 100 W dhe faktor të supozuar 0.85 do të jepte rreth <strong>8.7 orë</strong>. Faktori 0.85 është vetëm supozim për këtë llogaritje, jo matje apo premtim për një produkt të caktuar.</p>
        <p class="leading-7 text-slate-700">Rezultati real ndikohet nga humbjet e inverterit, konsumi i vetë stacionit, temperatura, gjendja e baterisë dhe cilësimet. Frigoriferi nuk konsumon të njëjtën fuqi gjatë gjithë kohës; përdorni konsumin e matur ose të dhënat e etiketës energjetike për vlerësim më të mirë.</p>
      </section>
      <section class="mt-10 space-y-4">
        <h2 class="text-2xl font-black">Çfarë duhet të kontrolloni për karikimin solar?</h2>
        <p class="leading-7 text-slate-700">Kontrolloni kufijtë e tensionit, rrymës dhe fuqisë hyrëse të stacionit, lidhësit dhe përputhshmërinë me panelet. Fuqia nominale e panelit nuk është prodhim i garantuar: hijet, orientimi, moti dhe temperatura ndikojnë në karikim.</p>
        <p class="leading-7 text-slate-700">Për instalime të lidhura me shtëpinë ose rrjetin elektrik, kërkoni vlerësim nga instalues i kualifikuar dhe ndiqni udhëzimet e prodhuesit.</p>
      </section>
      <section class="mt-10 space-y-4">
        <h2 class="text-2xl font-black">Para se të blini</h2>
        <ul class="list-disc space-y-3 pl-5 leading-7 text-slate-700">
          <li>Shënoni pajisjet, fuqinë e tyre dhe orët e përdorimit.</li>
          <li>Kontrolloni kapacitetin, fuqinë e vazhdueshme dhe fuqinë e nisjes së modelit.</li>
          <li>Verifikoni portat, peshën, mundësitë e zgjerimit dhe kohën e karikimit.</li>
          <li>Konfirmoni çmimin, disponueshmërinë, kushtet e garancionit dhe dërgesën për adresën tuaj.</li>
        </ul>
        <p class="leading-7 text-slate-700">EcoBuka shërben klientë në Kosovë dhe Shqipëri. Për të krahasuar një model me nevojat tuaja, na dërgoni listën e pajisjeve dhe kohën e dëshiruar të përdorimit.</p>
        <div class="flex flex-wrap gap-3 pt-3"><RouterLink to="/category/power-stations" class="btn-primary">Shiko stacionet e energjisë</RouterLink><RouterLink to="/contact" class="btn-secondary">Kërko ndihmë për zgjedhjen</RouterLink></div>
      </section>
    </article>
  </WebsiteLayout>
</template>

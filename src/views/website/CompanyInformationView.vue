<template>
  <WebsiteLayout>
    <section class="bg-mist py-10 sm:py-12 lg:py-14">
      <div class="container-shell grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-end lg:gap-10">
        <div>
          <p class="label">Company information</p>
          <h1 class="mt-2 text-3xl font-black leading-tight text-ink sm:text-4xl lg:text-5xl">
            Official business details for EcoBuka
          </h1>
        </div>
        <p class="text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
          EcoBuka eshte kompani e specializuar ne ofrimin e zgjidhjeve inteligjente EcoFlow per furnizim dhe ruajtje te energjise. Produktet tona u pergjigjen nevojave te shtepive, bizneseve dhe aktiviteteve ne natyre, duke siguruar energji te qendrueshme edhe gjate nderprerjeve te rrymes.
        </p>
      </div>
    </section>

    <section class="container-shell py-10 sm:py-12 lg:py-14">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-5">
        <article class="rounded-lg border border-line bg-white p-5 shadow-sm">
          <p class="label">Legal identity</p>
          <h2 class="mt-2 text-xl font-black text-ink">BUKA sh.p.k</h2>
          <dl class="mt-4 grid gap-3 text-sm leading-6 text-slate-600">
            <div>
              <dt class="font-bold text-ink">Brand name</dt>
              <dd>EcoBuka</dd>
            </div>
            <div>
              <dt class="font-bold text-ink">Business number</dt>
              <dd>810197493</dd>
            </div>
            <div>
              <dt class="font-bold text-ink">Fiscal number</dt>
              <dd>810197493</dd>
            </div>
          </dl>
        </article>

        <article class="rounded-lg border border-line bg-white p-5 shadow-sm">
          <p class="label">Locations</p>
          <h2 class="mt-2 text-xl font-black text-ink">Address and operations</h2>
          <dl class="mt-4 grid gap-3 text-sm leading-6 text-slate-600">
            <div>
              <dt class="font-bold text-ink">Registered address</dt>
              <dd>Rr. Tringe Smaili pn<br>10000 Prishtine<br>Republika e Kosoves</dd>
            </div>
            <div>
              <dt class="font-bold text-ink">Operating location</dt>
              <dd>Prizren, tranziti vjeter</dd>
            </div>
          </dl>
        </article>

        <article class="rounded-lg border border-line bg-white p-5 shadow-sm">
          <p class="label">Support</p>
          <h2 class="mt-2 text-xl font-black text-ink">Contact channels</h2>
          <dl class="mt-4 grid gap-3 text-sm leading-6 text-slate-600">
            <div>
              <dt class="font-bold text-ink">Kosovo</dt>
              <dd><a class="hover:text-ink hover:underline" href="tel:+38345977007">+383 45 977 007</a></dd>
            </div>
            <div>
              <dt class="font-bold text-ink">Albania</dt>
              <dd><a class="hover:text-ink hover:underline" href="tel:+355688049525">+355 68 804 9525</a></dd>
            </div>
            <div>
              <dt class="font-bold text-ink">Email</dt>
              <dd><a class="hover:text-ink hover:underline" href="mailto:info@ecoflowks.com">info@ecoflowks.com</a></dd>
            </div>
            <div>
              <dt class="font-bold text-ink">Support channels</dt>
              <dd>Phone, email, WhatsApp</dd>
            </div>
          </dl>
        </article>

        <article class="rounded-lg border border-line bg-white p-5 shadow-sm">
          <p class="label">Availability</p>
          <h2 class="mt-2 text-xl font-black text-ink">Business hours</h2>
          <p class="mt-4 text-sm leading-6 text-slate-600">
            Customer support and sales assistance are available from 09:00 to 21:00.
          </p>
        </article>

        <article class="rounded-lg border border-line bg-white p-5 shadow-sm">
          <p class="label">Service area</p>
          <h2 class="mt-2 text-xl font-black text-ink">Regional support</h2>
          <p class="mt-4 text-sm leading-6 text-slate-600">
            EcoBuka serves customers in Kosovo, Albania, and North Macedonia.
          </p>
        </article>

        <article class="rounded-lg border border-line bg-white p-5 shadow-sm">
          <p class="label">Warranty</p>
          <h2 class="mt-2 text-xl font-black text-ink">5-year warranty</h2>
          <p class="mt-4 text-sm leading-6 text-slate-600">
            Products are supported with a 5-year warranty, subject to the applicable product and warranty conditions.
          </p>
        </article>
      </div>

      <div class="mt-5 grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
        <article class="rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
          <p class="label">Distributor statement</p>
          <h2 class="mt-2 text-2xl font-black text-ink">EcoBuka is a distributor of EcoFlow products.</h2>
          <p class="mt-4 text-sm leading-6 text-slate-600 sm:text-base sm:leading-7">
            EcoBuka ofron zgjidhje moderne per furnizim, ruajtje dhe menaxhim te energjise, duke u fokusuar ne produkte EcoFlow per shtepi, biznese dhe perdorim ne natyre.
          </p>
        </article>

        <article class="rounded-lg border border-line bg-white p-5 shadow-sm sm:p-6">
          <p class="label">Social media</p>
          <h2 class="mt-2 text-2xl font-black text-ink">Follow EcoBuka</h2>
          <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <a class="btn-secondary w-full sm:w-auto" href="https://www.instagram.com/p/Db8fOVPCuIs/" target="_blank" rel="noreferrer">Instagram</a>
            <a class="btn-secondary w-full sm:w-auto" href="https://www.facebook.com/RTKNaten" target="_blank" rel="noreferrer">Facebook</a>
          </div>
        </article>
      </div>
    </section>
  </WebsiteLayout>
</template>

<script setup lang="ts">
import WebsiteLayout from '../../components/layout/WebsiteLayout.vue';
import { useSeo } from '../../lib/seo';

useSeo({
  title: 'Company Information',
  description: 'Official EcoBuka company details, business registration information, contact channels, service area, and warranty information.',
  canonicalPath: '/company-information',
});
</script>

<template>
  <footer class="border-t border-line bg-ink text-white">
    <div class="container-shell grid gap-8 py-8 sm:py-10 md:grid-cols-[1.4fr_1fr_1fr]">
      <div>
        <RouterLink to="/" class="inline-flex rounded-md bg-white px-3 py-2" aria-label="Ballina Eco Buka">
          <img :src="logoUrl" alt="Eco Buka" class="h-9 w-auto max-w-[220px] object-contain" />
        </RouterLink>
        <p class="mt-3 max-w-md text-sm leading-6 text-slate-300">
          Zgjidhje premium për energji portative, karikim solar dhe rezervë energjie për Kosovë, Shqipëri, shtëpi, biznese dhe aktivitete në natyrë.
        </p>
      </div>
      <div>
        <p class="font-semibold">Dyqani</p>
        <div class="mt-3 grid gap-1 text-sm text-slate-300">
          <RouterLink to="/products" class="py-1.5">Produktet</RouterLink>
          <RouterLink to="/category/power-stations" class="py-1.5">Stacione energjie</RouterLink>
          <RouterLink to="/category/solar-panels" class="py-1.5">Panele solare</RouterLink>
          <RouterLink to="/category/solar-generators" class="py-1.5">Gjeneratorë solarë</RouterLink>
          <RouterLink to="/category/accessories" class="py-1.5">Aksesorë</RouterLink>
          <RouterLink to="/solutions/powerocean" class="py-1.5">PowerOcean</RouterLink>
          <RouterLink to="/contact" class="py-1.5">Kërko ofertë</RouterLink>
        </div>
      </div>
      <div>
        <p class="font-semibold">Mbështetje</p>
        <div class="mt-3 grid gap-1 text-sm text-slate-300">
          <RouterLink to="/about" class="py-1.5">Rreth Eco Buka</RouterLink>
          <RouterLink to="/company-information" class="py-1.5">Informata për kompaninë</RouterLink>
          <RouterLink to="/politika-e-kthimit" class="py-1.5">Politika e kthimit</RouterLink>
          <RouterLink to="/politika-e-privatesise" class="py-1.5">Politika e privatësisë</RouterLink>
          <RouterLink to="/politika-e-cookies" class="py-1.5">Politika e cookies</RouterLink>
          <RouterLink to="/contact" class="py-1.5">Kontakt</RouterLink>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import logoUrl from '../../assets/eco-buka-logo.svg';
</script>

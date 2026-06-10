<template>
  <footer class="border-t border-line bg-ink text-white">
    <div class="container-shell grid gap-8 py-8 sm:py-10 md:grid-cols-[1.4fr_1fr_1fr]">
      <div>
        <p class="text-2xl font-black">Eco Buka</p>
        <p class="mt-3 max-w-md text-sm leading-6 text-slate-300">
          Premium portable energy, solar charging, and backup solutions for Albania, homes, businesses, and outdoor teams.
        </p>
      </div>
      <div>
        <p class="font-semibold">Shop</p>
        <div class="mt-3 grid gap-1 text-sm text-slate-300">
          <RouterLink to="/products" class="py-1.5">Products</RouterLink>
          <RouterLink to="/category/power-stations" class="py-1.5">Power stations</RouterLink>
          <RouterLink to="/category/solar-panels" class="py-1.5">Solar panels</RouterLink>
          <RouterLink to="/category/solar-generators" class="py-1.5">Solar generators</RouterLink>
          <RouterLink to="/category/accessories" class="py-1.5">Accessories</RouterLink>
          <RouterLink to="/contact" class="py-1.5">Request offer</RouterLink>
        </div>
      </div>
      <div>
        <p class="font-semibold">Support</p>
        <div class="mt-3 grid gap-1 text-sm text-slate-300">
          <RouterLink to="/about" class="py-1.5">About Eco Buka</RouterLink>
          <RouterLink to="/dashboard" class="py-1.5">Dashboard</RouterLink>
          <RouterLink to="/contact" class="py-1.5">Contact</RouterLink>
        </div>
      </div>
    </div>
  </footer>
</template>

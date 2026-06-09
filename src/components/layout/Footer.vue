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
        <div class="mt-3 grid gap-2 text-sm text-slate-300">
          <RouterLink to="/products">Products</RouterLink>
          <RouterLink to="/categories/solar-generators">Solar generators</RouterLink>
          <RouterLink to="/contact">Request offer</RouterLink>
        </div>
      </div>
      <div>
        <p class="font-semibold">Support</p>
        <div class="mt-3 grid gap-2 text-sm text-slate-300">
          <RouterLink to="/about">About Eco Buka</RouterLink>
          <RouterLink to="/dashboard">Dashboard</RouterLink>
          <RouterLink to="/contact">Contact</RouterLink>
        </div>
      </div>
    </div>
  </footer>
</template>

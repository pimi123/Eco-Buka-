<script setup lang="ts">
import Header from './Header.vue';
import Footer from './Footer.vue';
import TrackingConsent from './TrackingConsent.vue';
import { useStructuredData } from '../../lib/seo';
import logoUrl from '../../assets/eco-buka-logo.svg';

useStructuredData('business-structured-data', () => {
  const origin = import.meta.env.VITE_SITE_URL || window.location.origin;
  return {
    '@context': 'https://schema.org', '@type': 'Organization',
    '@id': new URL('/#organization', origin).href,
    name: 'EcoBuka', legalName: 'BUKA sh.p.k', url: origin,
    logo: new URL(logoUrl, origin).href,
    telephone: '+38345977007', email: 'ecobuka.ks@gmail.com',
    address: { '@type': 'PostalAddress', streetAddress: 'Rr. Tringe Smaili pn', addressLocality: 'Prishtinë', postalCode: '10000', addressCountry: 'XK' },
    areaServed: ['Kosovë', 'Shqipëri', 'Maqedoni e Veriut'],
  };
});
</script>

<template>
  <Header />
  <main>
    <slot />
  </main>
  <Footer />
  <TrackingConsent />
</template>

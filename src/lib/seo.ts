import { computed, onBeforeUnmount, unref, watchEffect, type MaybeRefOrGetter } from 'vue';
import { useRoute } from 'vue-router';

interface SeoConfig {
  title?: MaybeRefOrGetter<string | null | undefined>;
  description?: MaybeRefOrGetter<string | null | undefined>;
  canonicalPath?: MaybeRefOrGetter<string | null | undefined>;
  image?: MaybeRefOrGetter<string | null | undefined>;
  noindex?: MaybeRefOrGetter<boolean>;
  type?: 'website' | 'product';
  structuredData?: MaybeRefOrGetter<Record<string, unknown> | null>;
}

const siteName = 'EcoBuka';
const defaultDescription =
  'Stacione portative energjie, panele solare dhe bateri rezervë për shtëpi e biznes nga EcoBuka në Kosovë dhe Shqipëri.';

function readValue<T>(value: MaybeRefOrGetter<T>): T {
  return typeof value === 'function' ? (value as () => T)() : unref(value);
}

function upsertMeta(selector: string, createAttributes: Record<string, string>, content: string) {
  let element = document.head.querySelector<HTMLMetaElement>(selector);

  if (!element) {
    element = document.createElement('meta');
    Object.entries(createAttributes).forEach(([key, value]) => element?.setAttribute(key, value));
    document.head.appendChild(element);
  }

  element.setAttribute('content', content);
}

function upsertCanonical(href: string) {
  let element = document.head.querySelector<HTMLLinkElement>('link[rel="canonical"]');

  if (!element) {
    element = document.createElement('link');
    element.setAttribute('rel', 'canonical');
    document.head.appendChild(element);
  }

  element.setAttribute('href', href);
}

function absoluteUrl(path?: string | null) {
  if (typeof window === 'undefined') return '';
  if (!path) return window.location.href.split('#')[0];
  return new URL(path, import.meta.env.VITE_SITE_URL || window.location.origin).href;
}

export function useStructuredData(id: string, data: MaybeRefOrGetter<Record<string, unknown> | null>) {
  watchEffect(() => {
    if (typeof document === 'undefined') return;
    const value = readValue(data);
    let element = document.getElementById(id);
    if (!value) { element?.remove(); return; }
    if (!element) {
      element = document.createElement('script');
      element.id = id;
      element.setAttribute('type', 'application/ld+json');
      document.head.appendChild(element);
    }
    element.textContent = JSON.stringify(value).replace(/</g, '\\u003c');
  });
  onBeforeUnmount(() => document.getElementById(id)?.remove());
}

export function useSeo(config: SeoConfig) {
  const route = useRoute();
  useStructuredData('page-structured-data', () => config.structuredData ? readValue(config.structuredData) : null);
  const resolvedTitle = computed(() => {
    const title = String(readValue(config.title) || '').trim();
    return title ? `${title} | ${siteName}` : siteName;
  });

  const resolvedDescription = computed(() => {
    const description = String(readValue(config.description) || '').trim();
    return description || defaultDescription;
  });

  watchEffect(() => {
    if (typeof document === 'undefined') return;

    const title = resolvedTitle.value;
    const description = resolvedDescription.value;
    const canonicalUrl = new URL(absoluteUrl(readValue(config.canonicalPath)));
    const page = Number(route.query.page);
    if (Number.isInteger(page) && page > 1) canonicalUrl.searchParams.set('page', String(page));
    const canonical = canonicalUrl.href;
    const utilityPage = /^\/(cart|checkout|search|order-success|payment-failed)(\/|$)/.test(route.path);
    const filtered = ['q', 'categories', 'series', 'min', 'max', 'sale', 'stock', 'sort'].some(key => route.query[key] != null);
    const noindex = utilityPage || filtered || (config.noindex ? readValue(config.noindex) : false);
    const image = absoluteUrl(readValue(config.image) || '/images/social/eco-buka.jpg');

    document.title = title;
    upsertMeta('meta[name="description"]', { name: 'description' }, description);
    upsertMeta('meta[property="og:title"]', { property: 'og:title' }, title);
    upsertMeta('meta[property="og:description"]', { property: 'og:description' }, description);
    upsertMeta('meta[property="og:type"]', { property: 'og:type' }, config.type || 'website');
    upsertMeta('meta[property="og:url"]', { property: 'og:url' }, canonical);
    upsertMeta('meta[name="twitter:card"]', { name: 'twitter:card' }, 'summary_large_image');
    upsertMeta('meta[property="og:image"]', { property: 'og:image' }, image);
    upsertMeta('meta[name="twitter:image"]', { name: 'twitter:image' }, image);
    upsertMeta('meta[name="robots"]', { name: 'robots' }, noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large');
    upsertCanonical(canonical);
  });
}

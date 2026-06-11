import { computed, unref, watchEffect, type MaybeRefOrGetter } from 'vue';

interface SeoConfig {
  title?: MaybeRefOrGetter<string | null | undefined>;
  description?: MaybeRefOrGetter<string | null | undefined>;
  canonicalPath?: MaybeRefOrGetter<string | null | undefined>;
}

const siteName = 'Eco Buka';
const defaultDescription =
  'Eco Buka provides portable power stations, solar panels, solar generator kits, and home backup energy solutions.';

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
  return new URL(path, window.location.origin).href;
}

export function useSeo(config: SeoConfig) {
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
    const canonical = absoluteUrl(readValue(config.canonicalPath));

    document.title = title;
    upsertMeta('meta[name="description"]', { name: 'description' }, description);
    upsertMeta('meta[property="og:title"]', { property: 'og:title' }, title);
    upsertMeta('meta[property="og:description"]', { property: 'og:description' }, description);
    upsertMeta('meta[property="og:type"]', { property: 'og:type' }, 'website');
    upsertMeta('meta[property="og:url"]', { property: 'og:url' }, canonical);
    upsertMeta('meta[name="twitter:card"]', { name: 'twitter:card' }, 'summary_large_image');
    upsertCanonical(canonical);
  });
}

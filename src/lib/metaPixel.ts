import { ref } from 'vue';
import { apiGet, apiPost } from './api';
import type { Product } from '../types/product';

export const CONSENT_VERSION = '2026-11-20';
const CONSENT_KEY = 'eco-tracking-consent';
const RECEIPTS_KEY = 'eco-tracking-receipts';
type Preferences = { analytics: boolean; marketing: boolean; version: string };
type MetaConfig = { enabled: boolean; pixel_id: string; catalog_ids_verified: boolean };
type Pixel = ((...args: unknown[]) => void) & { queue?: unknown[][]; callMethod?: (...args: unknown[]) => void; loaded?: boolean; version?: string; push?: Pixel };
type TrackingWindow = Window & { fbq?: Pixel; _fbq?: Pixel; dataLayer?: unknown[]; gtag?: (...args: unknown[]) => void; [key: `ga-disable-${string}`]: boolean };
const win = window as unknown as TrackingWindow;
const empty: Preferences = { analytics: false, marketing: false, version: CONSENT_VERSION };
function read<T>(key: string, fallback: T): T {
  try { return JSON.parse(localStorage.getItem(key) || 'null') || fallback; } catch { return fallback; }
}
const stored = read<Preferences>(CONSENT_KEY, empty);
const validStoredConsent = stored.version === CONSENT_VERSION && typeof stored.analytics === 'boolean' && typeof stored.marketing === 'boolean';
export const preferences = ref<Preferences>(validStoredConsent ? stored : { ...empty });
export const consentChosen = ref(validStoredConsent && localStorageSafeExists(CONSENT_KEY));
export const consentOpen = ref(false);
function localStorageSafeExists(key: string) { try { return Boolean(localStorage.getItem(key)); } catch { return false; } }
function save(key: string, value: unknown) { try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* Checkout works without storage. */ } }
let config: MetaConfig | undefined;
let configPromise: Promise<MetaConfig | undefined> | undefined;
let initialized = false;
let gaInitialized = false;
let lastPath = '';
let click: { value: string; observed: number } | undefined;
const sent = new Set<string>();
const sensitivePage = () => /^\/(order-success|payment-failed)(\/|$)/.test(location.pathname);
const cookie = (name: string) => document.cookie.split('; ').find(item => item.startsWith(name + '='))?.slice(name.length + 1);

async function metaConfig() {
  if (!configPromise) configPromise = apiGet<MetaConfig>('/tracking/meta/config').then(value => (config = value)).catch(() => undefined);
  return configPromise;
}
function externalScript(id: string, src: string) {
  if (document.getElementById(id)) return;
  const script = document.createElement('script'); script.id = id; script.async = true; script.src = src; document.head.appendChild(script);
}
async function ensurePixel() {
  if (!preferences.value.marketing || sensitivePage()) return false;
  const settings = await metaConfig();
  if (!settings?.enabled || !/^\d+$/.test(settings.pixel_id) || !preferences.value.marketing || sensitivePage()) return false;
  if (!initialized) {
    if (!win.fbq) {
      const pixel: Pixel = (...args) => { if (pixel.callMethod) pixel.callMethod(...args); else pixel.queue!.push(args); };
      pixel.queue = []; pixel.loaded = true; pixel.version = '2.0'; pixel.push = pixel;
      win.fbq = pixel; win._fbq = pixel;
    }
    win.fbq('consent', 'revoke');
    win.fbq('set', 'autoConfig', false, settings.pixel_id);
    win.fbq('init', settings.pixel_id);
    initialized = true;
    externalScript('meta-pixel-sdk', 'https://connect.facebook.net/en_US/fbevents.js');
  }
  win.fbq?.('consent', 'grant');
  return true;
}
function observeClick() {
  if (!preferences.value.marketing) return;
  const id = new URLSearchParams(location.search).get('fbclid');
  if (id && /^[A-Za-z0-9_-]{5,400}$/.test(id) && cookie('_fbc')?.split('.').slice(3).join('.') !== id) {
    click = { value: id, observed: Date.now() };
    document.cookie = `_fbc=fb.1.${click.observed}.${click.value}; Max-Age=2592000; Path=/; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
  }
}
export function trackingContext() {
  if (!preferences.value.marketing) return { marketing: false, consent_version: CONSENT_VERSION };
  const fbc = cookie('_fbc') || (click ? `fb.1.${click.observed}.${click.value}` : undefined);
  return { marketing: true, consent_version: CONSENT_VERSION, fbp: cookie('_fbp'), fbc };
}
export async function trackMetaEvent(name: 'PageView' | 'ViewContent' | 'AddToCart' | 'InitiateCheckout' | 'OrderSubmitted', data: Record<string, unknown> = {}, eventId?: string) {
  try {
    if (eventId && (sent.has(eventId) || read<string[]>('meta-browser-events', []).includes(eventId))) return;
    if (!await ensurePixel()) return;
    const payload = { ...data };
    if (!config?.catalog_ids_verified) { delete payload.content_ids; delete payload.contents; delete payload.content_type; }
    if (eventId && sent.has(eventId)) return;
    win.fbq?.(name === 'OrderSubmitted' ? 'trackCustom' : 'track', name, payload, eventId ? { eventID: eventId } : {});
    if (eventId) { sent.add(eventId); save('meta-browser-events', [...new Set([...read<string[]>('meta-browser-events', []), eventId])].slice(-100)); }
  } catch { /* Tracking cannot interrupt checkout. */ }
}
export function productEventData(product: Product, quantity = 1) {
  return { content_ids: [String(product.id)], contents: [{ id: String(product.id), quantity, item_price: Number(product.price || 0) }], content_type: 'product', currency: 'EUR', value: Number(product.price || 0) * quantity };
}
function gaPageView(path: string) {
  const id = 'G-4G4FW956HL';
  win[`ga-disable-${id}`] = !preferences.value.analytics;
  if (!preferences.value.analytics || sensitivePage()) return;
  if (!gaInitialized) {
    win.dataLayer ||= [];
    win.gtag = function () { win.dataLayer!.push(arguments); };
    win.gtag('js', new Date()); win.gtag('config', id, { send_page_view: false });
    externalScript('google-analytics-sdk', `https://www.googletagmanager.com/gtag/js?id=${id}`);
    gaInitialized = true;
  }
  win.gtag?.('event', 'page_view', { page_location: location.origin + path, page_path: path });
}
export function trackPageView() {
  observeClick();
  const path = location.pathname;
  if (sensitivePage() || path === lastPath) return;
  lastPath = path;
  gaPageView(path);
  void trackMetaEvent('PageView');
}
type Receipt = { id: number; token: string; at: number };
export function rememberTrackingReceipt(order: { id: number; tracking_token: string }) {
  const records = read<Receipt[]>(RECEIPTS_KEY, []).filter(row => row.at > Date.now() - 30 * 86400000 && row.id !== order.id);
  if (preferences.value.marketing) save(RECEIPTS_KEY, [...records, { id: order.id, token: order.tracking_token, at: Date.now() }].slice(-20));
}
async function revokeReceipts() {
  const records = read<Receipt[]>(RECEIPTS_KEY, []);
  if (!records.length) return;
  try { await apiPost('/tracking/meta/revoke', { orders: records.map(({ id, token }) => ({ id, token })) }); save(RECEIPTS_KEY, []); } catch { /* Retry withdrawal on a later denied-consent load. */ }
}
function clearMarketingCookies() {
  const names = [...(!preferences.value.marketing ? ['_fbp', '_fbc'] : []), ...(!preferences.value.analytics ? document.cookie.split('; ').map(item => item.split('=')[0]).filter(name => name.startsWith('_ga')) : [])];
  for (const name of names) {
    document.cookie = `${name}=; Max-Age=0; Path=/; SameSite=Lax`;
    const parts = location.hostname.split('.');
    for (let index = 0; index < parts.length - 1; index++) document.cookie = `${name}=; Max-Age=0; Path=/; Domain=.${parts.slice(index).join('.')}; SameSite=Lax`;
  }
}
export function setTrackingPreferences(value: { analytics: boolean; marketing: boolean }) {
  preferences.value = { analytics: value.analytics === true, marketing: value.marketing === true, version: CONSENT_VERSION };
  save(CONSENT_KEY, preferences.value); consentChosen.value = true; consentOpen.value = false;
  win['ga-disable-G-4G4FW956HL'] = !value.analytics;
  if (!value.marketing) { win.fbq?.('consent', 'revoke'); click = undefined; void revokeReceipts(); }
  if (!value.marketing || !value.analytics) clearMarketingCookies();
  lastPath = ''; trackPageView();
}
export function initializeTracking() {
  if (!preferences.value.marketing) void revokeReceipts();
  observeClick();
}

type Purchase = { id: number; total: string | number; currency: string };
type Pixel = (...args: unknown[]) => void;
const sent = new Set<string>();

// Storage failures and tracking blockers must never interrupt checkout.
export function trackPurchase(order: Purchase): void {
  const pixel = (window as Window & { fbq?: Pixel }).fbq;
  const value = Number(order.total);
  if (!pixel || !Number.isFinite(value) || value < 0 || !order.currency) return;
  const eventId = `purchase_${order.id}`;
  const key = `meta:${eventId}`;
  if (sent.has(key)) return;
  try {
    if (localStorage.getItem(key)) return;
  } catch { /* Storage may be disabled. */ }
  try {
    pixel('track', 'Purchase', { value, currency: order.currency }, { eventID: eventId });
    sent.add(key);
    try { localStorage.setItem(key, '1'); } catch { /* Keep the in-memory guard. */ }
  } catch { /* Analytics must not prevent a successful order. */ }
}

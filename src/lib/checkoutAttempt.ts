let memory: { fingerprint: string; key: string } | undefined;

export async function checkoutAttempt(payload: unknown): Promise<string> {
  const digest = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(JSON.stringify(payload)));
  const fingerprint = Array.from(new Uint8Array(digest), value => value.toString(16).padStart(2, '0')).join('');
  let saved = memory;
  try { saved = JSON.parse(sessionStorage.getItem('eco-checkout-attempt') || 'null') || saved; } catch { /* Keep the in-memory retry key. */ }
  if (saved?.fingerprint === fingerprint) return saved.key;
  const next = { fingerprint, key: crypto.randomUUID() };
  memory = next;
  try { sessionStorage.setItem('eco-checkout-attempt', JSON.stringify(next)); } catch { /* No customer fields are stored. */ }
  return next.key;
}

export function finishCheckoutAttempt() {
  memory = undefined;
  try { sessionStorage.removeItem('eco-checkout-attempt'); } catch { /* Checkout has already succeeded. */ }
}

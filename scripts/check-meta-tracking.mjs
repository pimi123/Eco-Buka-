import assert from 'node:assert/strict';
import { chromium } from 'playwright';

const origin = process.env.TEST_ORIGIN || 'http://127.0.0.1:5173';
const browser = await chromium.launch({ channel: process.env.BROWSER_CHANNEL || 'chrome' });
const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
const product = { id: '7', slug: 'tracking-test', name: 'Test battery', price: 249.90, old_price: 399, active: true, in_stock: true, image_url: '/images/payments/star-card.webp' };
const submissions = [];
const revocations = [];
const scripts = [];
await context.addInitScript(() => {
  window.__metaCalls = [];
  window.fbq = (...args) => window.__metaCalls.push(args);
});
await context.route('**/*', async route => {
  const request = route.request(); const url = new URL(request.url());
  if (/connect.facebook.net|googletagmanager.com/.test(url.hostname)) { scripts.push(request.url()); return route.fulfill({ contentType: 'text/javascript', body: '' }); }
  if (/facebook.com|google-analytics.com|graph.facebook.com/.test(url.hostname)) throw new Error('No conversion traffic may leave this test.');
  if (url.pathname.startsWith('/api/')) {
    if (url.pathname.endsWith('/tracking/meta/config')) return route.fulfill({ json: { enabled: true, pixel_id: '1426946475629785', catalog_ids_verified: true } });
    if (url.pathname.endsWith('/products/tracking-test')) return route.fulfill({ json: product });
    if (url.pathname.endsWith('/payments/nestpay/options')) return route.fulfill({ json: { installments_enabled: false, allowed_installments: [], minimum_installment_amount: 0 } });
    if (url.pathname.endsWith('/orders') && request.method() === 'POST') {
      submissions.push(request.postDataJSON());
      return route.fulfill({ status: 201, json: { id: 7, order_number: 'TEST-7', tracking_token: 'test-token-7', submission_event_id: 'order_submitted_7', total: 249.90, currency: 'EUR', status: 'pending', payment_method: 'cash' } });
    }
    if (url.pathname.endsWith('/tracking/meta/revoke')) { revocations.push(request.postDataJSON()); return route.fulfill({ json: { revoked: true } }); }
    if (url.pathname.endsWith('/orders/purchase-confirmation')) return route.fulfill({ status: 409, json: { message: 'Not paid' } });
    return route.fulfill({ json: [] });
  }
  if (!['GET', 'HEAD'].includes(request.method())) throw new Error('Unexpected external write');
  if (request.resourceType() === 'document') {
    const response = await route.fetch();
    return route.fulfill({ response, headers: { ...response.headers(), 'content-security-policy': "default-src 'self'; script-src 'self' https://connect.facebook.net https://www.googletagmanager.com; style-src 'self' 'unsafe-inline'; connect-src 'self' http://127.0.0.1:8001 ws://127.0.0.1:5173; img-src 'self' data:; font-src 'self';" } });
  }
  return route.continue();
});
const page = await context.newPage();
const events = () => page.evaluate(() => window.__metaCalls.filter(call => ['track', 'trackCustom'].includes(call[0])));
try {
  await page.goto(`${origin}/products/tracking-test?fbclid=syntheticClick_12345`);
  await page.getByRole('heading', { level: 1, name: product.name }).waitFor();
  assert.equal(scripts.length, 0, 'No tracker scripts before consent');
  assert.equal((await events()).length, 0);
  assert(!await page.evaluate(() => document.cookie.includes('_fbc=')));
  await page.getByRole('button', { name: 'Vetëm të domosdoshmet', exact: true }).click();
  assert.equal(scripts.length, 0, 'Essential-only consent does not load trackers');
  await page.getByRole('button', { name: 'Preferencat e cookies', exact: true }).click();
  await page.getByRole('button', { name: 'Prano të gjitha', exact: true }).click();
  await page.waitForFunction(() => window.__metaCalls.some(call => call[1] === 'ViewContent'));
  assert.equal((await events()).filter(call => call[1] === 'PageView').length, 1);
  assert.equal((await events()).filter(call => call[1] === 'ViewContent').length, 1);
  assert(await page.evaluate(() => document.cookie.includes('_fbc=fb.1.')));
  await page.getByRole('button', { name: 'Shto në shportë', exact: true }).click();
  await page.waitForFunction(() => window.__metaCalls.some(call => call[1] === 'AddToCart'));
  const add = (await events()).find(call => call[1] === 'AddToCart');
  assert.equal(add[2].value, 249.90);
  assert.equal(add[2].contents[0].quantity, 1);
  await page.getByRole('link', { name: 'Shiko shportën', exact: true }).first().click();
  await page.waitForURL('**/cart');
  await page.locator('a[href="/checkout"]').first().click();
  await page.waitForURL('**/checkout');
  await page.waitForFunction(() => window.__metaCalls.some(call => call[1] === 'InitiateCheckout'));
  await page.locator('input[autocomplete="name"]').fill('Test Customer');
  await page.locator('input[type="tel"]').fill('+38344111222');
  await page.locator('input[type="email"]').fill('synthetic@example.test');
  await page.locator('select[autocomplete="address-level2"]').selectOption({ index: 1 });
  await page.locator('input[autocomplete="street-address"]').fill('Synthetic test address');
  await page.locator('label').filter({ has: page.locator('input[name="payment_method"][value="cash"]') }).click();
  await page.locator('form input[type="checkbox"]').check();
  await page.getByRole('button', { name: 'Konfirmo porosinë', exact: true }).click();
  await page.waitForURL('**/order-success?**');
  assert.equal(submissions.length, 1);
  assert.match(submissions[0].checkout_key, /^[0-9a-f-]{36}$/);
  assert.equal(submissions[0].tracking.marketing, true);
  assert.match(submissions[0].tracking.fbc, /^fb\.1\.\d{13}\.syntheticClick_12345$/);
  assert.equal((await events()).filter(call => call[1] === 'Purchase').length, 0, 'An accepted cash order is not a browser Purchase');
  await page.reload();
  assert.equal((await events()).filter(call => call[1] === 'Purchase').length, 0);
  await page.getByRole('button', { name: 'Preferencat e cookies', exact: true }).click();
  const revokeResponse = page.waitForResponse(response => response.url().includes('/tracking/meta/revoke'));
  await page.getByRole('button', { name: 'Vetëm të domosdoshmet', exact: true }).click();
  await page.waitForFunction(() => !document.cookie.includes('_fbc='));
  await revokeResponse;
  assert.equal(revocations[0].orders[0].token, 'test-token-7');
  await page.goto(`${origin}/order-success?payment=approved&order=FAKE&token=forged`);
  await page.getByRole('heading', { name: 'Pagesa nuk mund të verifikohet.' }).waitFor();
  assert.equal((await events()).filter(call => call[1] === 'Purchase').length, 0);
  console.log('PASS: strict-CSP bootstrap, default-denied consent, funnel events, cash submission, attribution, refresh, withdrawal and forged success URL. No real orders/events were sent.');
} finally { await browser.close(); }

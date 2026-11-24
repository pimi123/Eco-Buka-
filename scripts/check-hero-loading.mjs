import assert from 'node:assert/strict';
import { chromium } from 'playwright';

const origin = process.env.TEST_ORIGIN || 'http://127.0.0.1:5173';
const browser = await chromium.launch({ channel: process.env.BROWSER_CHANNEL || 'chrome' });
const banners = [1, 2].map(id => ({
  id: String(id), title: `Uploaded banner ${id}`, subtitle: 'Uploaded content', active: true, sort_order: id,
  background_image_url: `/hero-test/desktop-${id}.svg`, mobile_background_image_url: `/hero-test/mobile-${id}.svg`,
  button_text: 'Shiko produktet', button_link: '/products', text_color: 'light', text_alignment: 'left',
}));
function deferred() { let resolve; const promise = new Promise(done => { resolve = done; }); return { promise, resolve }; }
async function setup(width, mode = 'normal') {
  const context = await browser.newContext({ viewport: { width, height: 900 } });
  const api = deferred(); const image = deferred(); const nextImage = deferred(); const requested = [];
  await context.route('**/*', async route => {
    const request = route.request(); const url = new URL(request.url());
    if (!['GET', 'HEAD'].includes(request.method()) || /facebook|google-analytics|googletagmanager/.test(url.hostname)) return route.abort();
    if (url.pathname.startsWith('/hero-test/')) {
      requested.push(url.pathname);
      await image.promise;
      if (url.pathname.endsWith('-2.svg')) await nextImage.promise;
      if (mode === 'image-error') return route.fulfill({ status: 404, body: '' });
      return route.fulfill({ contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="600"><rect width="1200" height="600" fill="#244569"/></svg>' });
    }
    if (url.pathname.endsWith('/home/hero-banners')) {
      await api.promise;
      if (mode === 'error' || mode === 'bootstrap') return route.fulfill({ status: 503, json: {} });
      return route.fulfill({ json: mode === 'empty' ? [] : banners });
    }
    if (url.pathname.startsWith('/api/')) {
      if (url.pathname.endsWith('/catalog')) return route.fulfill({ json: { data: [], total: 0, last_page: 1, facets: null } });
      if (url.pathname.endsWith('/homepage')) return route.fulfill({ json: { showcase_sections: [{ id: 'none', active: false }] } });
      if (url.pathname.includes('/home/showcase/')) return route.fulfill({ json: { section: {}, products: [] } });
      return route.fulfill({ json: [] });
    }
    if (mode === 'bootstrap' && request.resourceType() === 'document' && url.pathname === '/') {
      const response = await route.fetch();
      const initial = banners.map(banner => ({ id: banner.id, eyebrow: '', title: banner.title, subtitle: banner.subtitle,
        primaryButtonText: banner.button_text, primaryButtonLink: banner.button_link,
        image: banner.background_image_url, mobileImage: banner.mobile_background_image_url,
        imageAlt: banner.title, overlayStyle: '', textColor: 'text-white', alignment: 'left' }));
      const encoded = JSON.stringify(initial).replaceAll('&', '&amp;').replaceAll('"', '&quot;').replaceAll('<', '&lt;');
      return route.fulfill({ response, body: (await response.text()).replace('<div id="app"></div>', `<div id="app"><section data-hero-slides="${encoded}"></section></div>`) });
    }
    return route.continue();
  });
  const page = await context.newPage();
  await page.goto(origin, { waitUntil: 'domcontentloaded' });
  const hero = page.locator('section[aria-label="Promovimet kryesore EcoBuka"]');
  await hero.waitFor();
  // Dismiss the independent consent panel so carousel controls remain reachable.
  await page.getByRole('button', { name: 'Vetëm të domosdoshmet', exact: true }).click();
  return { context, page, hero, api, image, nextImage, requested };
}
try {
  for (const width of [390, 640, 641, 1280]) {
    const { context, page, hero, api, image, nextImage, requested } = await setup(width);
    const height = (await hero.boundingBox()).height;
    assert.equal(await hero.getAttribute('aria-busy'), 'true');
    assert.equal(await hero.locator('img').count(), 0, 'Do not render default photos while the API is pending');
    assert.equal(await page.locator('link[data-hero-preload]').count(), 0, 'Do not preload default photos');
    assert(!await hero.textContent().then(text => text.includes('Seria DELTA 3 Max')));
    const expected = `/hero-test/${width <= 640 ? 'mobile' : 'desktop'}-1.svg`;
    const firstImageRequest = page.waitForRequest(request => new URL(request.url()).pathname === expected);
    api.resolve();
    await firstImageRequest;
    await hero.getByRole('heading', { name: 'Uploaded banner 1', exact: true }).waitFor();
    assert.deepEqual(requested, [expected], 'Only the matching viewport image should download, not hidden slides or the other variant');
    image.resolve();
    await hero.locator('img').evaluate(image => image.decode());
    await page.waitForFunction(() => document.querySelector('section[aria-label="Promovimet kryesore EcoBuka"]')?.getAttribute('aria-busy') === 'false');
    assert.equal((await hero.boundingBox()).height, height, 'Loading must not change banner height');
    assert.equal(new URL(await hero.locator('img').evaluate(image => image.currentSrc)).pathname, expected);
    assert.equal(await hero.locator('picture').count(), 1, 'Hidden slides are not fetched on initial load');
    const next = `/hero-test/${width <= 640 ? 'mobile' : 'desktop'}-2.svg`;
    const nextRequest = page.waitForRequest(request => new URL(request.url()).pathname === next);
    await hero.getByRole('button', { name: 'Shko te baneri 2', exact: true }).click();
    await nextRequest;
    await hero.getByRole('heading', { name: 'Uploaded banner 1', exact: true }).waitFor();
    assert.equal(await hero.getAttribute('aria-busy'), 'true', 'Keep the current banner while the next image loads');
    nextImage.resolve();
    await hero.getByRole('heading', { name: 'Uploaded banner 2', exact: true }).waitFor();
    await hero.getByRole('link', { name: 'Shiko produktet', exact: true }).first().click();
    await page.waitForURL('**/products');
    assert.equal(await page.locator('link[data-hero-preload]').count(), 0, 'Preload hints must not remain on unrelated pages');
    await context.close();
    console.log(`PASS: delayed API/image, correct variant, fixed height and carousel at ${width}px.`);
  }
  for (const mode of ['empty', 'error', 'image-error']) {
    const { context, page, hero, api, image } = await setup(390, mode);
    api.resolve(); image.resolve();
    await page.waitForFunction(() => document.querySelector('section[aria-label="Promovimet kryesore EcoBuka"]')?.getAttribute('aria-busy') === 'false');
    assert.equal(await hero.locator('img[src*="/promo/"]').count(), 0, 'Failures must never switch to default marketing photos');
    assert.equal(await hero.locator('h1').count(), 1, 'Keep a usable heading on empty/error responses');
    if (mode !== 'image-error') assert.equal(await hero.locator('img').count(), 0);
    await context.close();
    console.log(`PASS: ${mode} remains usable without a default-photo flash.`);
  }
  {
    const { context, page, hero, api, image, requested } = await setup(390, 'bootstrap');
    await hero.getByRole('heading', { name: 'Uploaded banner 1', exact: true }).waitFor();
    assert(!await hero.textContent().then(text => text.includes('Po ngarkohet banneri')));
    image.resolve();
    await hero.locator('img').evaluate(image => image.decode());
    api.resolve();
    await page.waitForLoadState('networkidle');
    assert.deepEqual(requested, ['/hero-test/mobile-1.svg']);
    assert.equal(await page.locator('link[data-hero-preload]').count(), 2, 'Keep one mobile and one desktop media-gated preload, without duplicate hints');
    await hero.getByRole('heading', { name: 'Uploaded banner 1', exact: true }).waitFor();
    await context.close();
    console.log('PASS: prerendered banner survives client mount and API failure without a blank/default reset.');
  }
} finally { await browser.close(); }

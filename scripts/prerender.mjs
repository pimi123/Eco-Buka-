import { createServer } from 'node:http';
import { readFile, writeFile, mkdir, stat, copyFile } from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { chromium } from 'playwright';
import { loadEnv } from 'vite';

const escapeXml = value => String(value).replace(/[<>&"']/g, char => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;', "'": '&apos;' }[char]));
const staticPaths = ['/', '/products', '/about', '/contact', '/company-information', '/solutions/powerocean', '/politika-e-kthimit', '/termat-dhe-kushtet', '/politika-e-privatesise', '/politika-e-cookies', '/guides/zgjedhja-e-stacionit-te-energjise'];
const utilityPaths = ['/cart', '/checkout', '/search', '/order-success', '/payment-failed'];

export async function prerender({ directory = 'dist', siteUrl, apiUrl, channel = process.env.BROWSER_CHANNEL } = {}) {
  const site = new URL(siteUrl);
  if (!['http:', 'https:'].includes(site.protocol) || site.pathname !== '/' || site.search || site.hash) throw new Error('VITE_SITE_URL must be the storefront origin, e.g. https://shop.example.com');
  const root = path.resolve(directory);
  const template = await readFile(path.join(root, 'index.html'), 'utf8');
  const api = String(apiUrl).replace(/\/$/, '');
  if (!apiUrl) throw new Error('VITE_API_BASE_URL is required; demo data cannot be published.');
  async function get(resource) {
    const response = await fetch(`${api}/${resource}`, { signal: AbortSignal.timeout(30000), headers: { Accept: 'application/json' } });
    if (!response.ok) throw new Error(`Cannot generate SEO pages: ${resource} returned ${response.status}`);
    const data = await response.json();
    if (!Array.isArray(data)) throw new Error(`Expected a list from ${resource}`);
    return data;
  }
  // Read published content only. No order, payment or admin endpoints are called.
  const products = await get('products');
  const categories = await get('categories');
  const collections = await get('collections');
  const paths = [...staticPaths];
  const validSlug = value => typeof value === 'string' && /^[a-zA-Z0-9_-]+$/.test(value);
  for (const [prefix, records] of [['products', products], ['category', categories], ['collections', collections]]) {
    for (const record of records) {
      if (!validSlug(record.slug)) throw new Error(`Invalid ${prefix} slug: ${record.slug}`);
      paths.push(`/${prefix}/${record.slug}`);
    }
  }
  const uniquePaths = [...new Set(paths)];
  const types = { '.html': 'text/html', '.js': 'text/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.json': 'application/json', '.jpg': 'image/jpeg', '.png': 'image/png', '.webp': 'image/webp', '.woff2': 'font/woff2', '.otf': 'font/otf' };
  const server = createServer(async (request, response) => {
    try {
      const pathname = decodeURIComponent(new URL(request.url, 'http://localhost').pathname);
      const file = path.resolve(root, `.${pathname}`);
      if (file !== root && !file.startsWith(root + path.sep)) { response.writeHead(403).end(); return; }
      let info;
      try { info = await stat(file); } catch { /* Frontend routes use the build template. */ }
      if (info?.isFile()) {
        response.writeHead(200, { 'Content-Type': types[path.extname(file)] || 'application/octet-stream' });
        response.end(await readFile(file));
      } else {
        response.writeHead(200, { 'Content-Type': 'text/html' }); response.end(template);
      }
    } catch { response.writeHead(500).end(); }
  });
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  let browser;
  try {
    browser = await chromium.launch(channel ? { channel } : {});
    const context = await browser.newContext();
    const origin = `http://127.0.0.1:${server.address().port}`;
    // Do not send analytics events, load advertising scripts, or download media during a build.
    await context.route('**/*', async route => {
      const request = route.request();
      // A valid placeholder avoids triggering image-error handlers that would alter saved src URLs.
      if (request.resourceType() === 'image') return route.fulfill({ contentType: 'image/png', body: Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a3ioAAAAASUVORK5CYII=', 'base64') });
      if (['media', 'font'].includes(request.resourceType())) return route.abort();
      const url = request.url();
      if (url.startsWith(api + '/')) {
        const response = await route.fetch();
        return route.fulfill({ response, headers: { ...response.headers(), 'access-control-allow-origin': origin } });
      }
      if (url.startsWith(origin + '/')) return route.continue();
      return route.abort();
    });
    const results = [];
    for (const routePath of [...uniquePaths, ...utilityPaths, '/404']) {
      const page = await context.newPage();
      const failures = [];
      page.on('pageerror', error => failures.push(error.message));
      page.on('response', response => {
        if (response.url().startsWith(api + '/') && response.status() >= 400) failures.push(`${response.status()} ${response.url()}`);
      });
      await page.goto(origin + routePath, { waitUntil: 'networkidle', timeout: 60000 });
      await page.waitForFunction(() => !document.querySelector('.animate-pulse, [aria-busy="true"]'), null, { timeout: 30000 });
      const indexed = uniquePaths.includes(routePath);
      if (failures.length) throw new Error(`${routePath}: ${failures.join('; ')}`);
      const html = await page.evaluate(({ siteOrigin, routePath, indexed }) => {
        if (indexed && !document.querySelector('main h1')) throw new Error('Missing main heading');
        if (indexed && !document.querySelector('main')?.textContent?.trim()) throw new Error('Missing page content');
        const canonical = document.querySelector('link[rel="canonical"]');
        canonical?.setAttribute('href', new URL(routePath, siteOrigin).href);
        document.querySelector('meta[property="og:url"]')?.setAttribute('content', new URL(routePath, siteOrigin).href);
        for (const element of document.querySelectorAll('script[type="application/ld+json"]')) {
          const data = JSON.parse(element.textContent);
          if (JSON.stringify(data).includes(window.location.origin)) throw new Error('Structured data uses the renderer origin; rebuild with VITE_SITE_URL set');
        }
        if (indexed && document.querySelector('meta[name="robots"]')?.content.startsWith('noindex')) throw new Error('Published page is marked noindex');
        // Runtime-created preload hints are not needed in the saved HTML.
        document.querySelectorAll('link[rel="modulepreload"]').forEach(element => element.remove());
        document.querySelectorAll('script[src*="connect.facebook.net"]').forEach(element => element.remove());
        return '<!doctype html>\n' + document.documentElement.outerHTML;
      }, { siteOrigin: site.origin, routePath, indexed });
      results.push([routePath, html]);
      await page.close();
      console.log(`Verified HTML: ${routePath}`);
    }
    // Publish files only after every route passed the checks.
    for (const [routePath, html] of results) {
      const destination = routePath === '/404' ? path.join(root, '404.html') : path.join(root, routePath.slice(1), 'index.html');
      await mkdir(path.dirname(destination), { recursive: true });
      await writeFile(destination, html);
    }
    const urls = uniquePaths.map(routePath => `<url><loc>${escapeXml(new URL(routePath, site).href)}</loc></url>`).join('\n');
    await writeFile(path.join(root, 'sitemap.xml'), `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urls}\n</urlset>\n`);
    await writeFile(path.join(root, 'robots.txt'), `User-agent: *\nAllow: /\n\nSitemap: ${new URL('/sitemap.xml', site).href}\n`);
    await writeFile(path.join(root, 'seo-manifest.json'), JSON.stringify({ site: site.origin, paths: uniquePaths }, null, 2));
    await copyFile(path.resolve('deploy/frontend.htaccess'), path.join(root, '.htaccess'));
    console.log(`Generated ${uniquePaths.length} indexable pages, sitemap, robots.txt and 404.html.`);
    return uniquePaths;
  } finally {
    await browser?.close();
    await new Promise(resolve => server.close(resolve));
  }
}

if (process.argv[1] && import.meta.url === pathToFileURL(path.resolve(process.argv[1])).href) {
  const env = { ...loadEnv('production', process.cwd(), ''), ...process.env };
  await prerender({ siteUrl: env.VITE_SITE_URL, apiUrl: env.VITE_API_BASE_URL });
}

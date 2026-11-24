import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import path from 'node:path';

const root = path.resolve(process.argv[2] || 'dist');
const manifest = JSON.parse(await readFile(path.join(root, 'seo-manifest.json'), 'utf8'));
const sitemap = await readFile(path.join(root, 'sitemap.xml'), 'utf8');
for (const route of manifest.paths) {
  const html = await readFile(path.join(root, route.slice(1), 'index.html'), 'utf8');
  assert.match(html, /<html[^>]*lang="sq"/);
  assert.match(html, /<h1[\s>]/);
  assert.match(html, /<meta name="description" content="[^"]+"/);
  assert(html.includes(`href="${new URL(route, manifest.site).href}"`), `Missing canonical: ${route}`);
  assert(!html.includes('content="noindex, follow"'), `Unexpected noindex: ${route}`);
  if (route.startsWith('/products/')) {
    const match = html.match(/<script[^>]*id="page-structured-data"[^>]*>([\s\S]*?)<\/script>/);
    assert(match, `Missing product schema: ${route}`);
    const schema = JSON.parse(match[1]);
    assert.equal(schema['@graph'][0]['@type'], 'Product');
  }
}
for (const utility of ['cart', 'checkout', 'search', 'order-success', 'payment-failed']) {
  const html = await readFile(path.join(root, utility, 'index.html'), 'utf8');
  assert(html.includes('content="noindex, follow"'));
  assert(!sitemap.includes(`<loc>${manifest.site}/${utility}</loc>`));
}
assert((await readFile(path.join(root, '404.html'), 'utf8')).includes('Faqja nuk u gjet'));
assert((await readFile(path.join(root, 'robots.txt'), 'utf8')).includes(`Sitemap: ${manifest.site}/sitemap.xml`));
console.log(`PASS: ${manifest.paths.length} HTML pages, product schemas, canonical URLs, sitemap, robots and utility exclusions.`);

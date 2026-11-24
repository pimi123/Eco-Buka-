# SEO build and verification

The existing Laravel API and MySQL configuration are unchanged. Public pages can be served as prerendered HTML; Vue continues to handle navigation, cart, checkout and payments.

## Production build

Set `VITE_SITE_URL` to the canonical storefront origin (HTTPS, no path) and `VITE_API_BASE_URL` to the production API before building. Do not use the local API in a production build.

```sh
npm ci
npx playwright install --with-deps chromium
# From backend/, generate thumbnails for uploaded product images first:
# php artisan media:optimize-products
npm run build:seo
```

The build reads active products, categories and collections from the API, renders the actual Vue pages, checks API responses and headings, and creates route-specific HTML, sitemap.xml, robots.txt and 404.html under dist. Analytics and media downloads are blocked during rendering. The build fails if content cannot be verified.

Deploy the entire dist directory atomically. Rebuild whenever public products, prices, availability, categories or page content change; saved HTML is a snapshot, while the interactive app refreshes data from the API. For frequently changing inventory, use runtime SSR instead of infrequent snapshots. Keep the prior deployed build if generation fails.

## Web-server routing

Configure these rules in the existing FRONTEND virtual host only. Do not replace Laravel's API/admin virtual host.

Example Nginx rules inside the existing server block:

```nginx
index index.html;
error_page 404 /404.html;
location = /404.html { internal; }
location ~ ^/categories/([^/]+)/?$ { return 301 /category/$1$is_args$args; }
location ~ ^/collection/([^/]+)/?$ { return 301 /collections/$1$is_args$args; }
location / { try_files $uri $uri/index.html =404; }
location ~* ^/assets/.*\.(js|css|woff2|otf)$ {
    try_files $uri =404;
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

The immutable rule applies only to hashed build assets under /assets/. Enable Brotli/gzip on the existing host. Avoid long caching of HTML, sitemap and robots.txt. Preserve the existing HTTPS and canonical-host redirects.

Apache deployments need equivalent rules: serve generated directory index files, preserve API routes, and use `ErrorDocument 404 /404.html` instead of routing every missing URL to index.html with status 200. Utility routes have generated noindex pages; do not disallow them in robots.txt, as crawlers must read their noindex tags.

A frontend-only Apache configuration with these rules is included in deploy/frontend.htaccess and copied to dist/.htaccess only after a successful SEO build. It must not overwrite backend/public/.htaccess. On Nginx, use the rules above and add equivalent 301 redirects for the legacy policy URLs defined in src/router/index.ts.

## Media maintenance

Run `php artisan media:optimize-products` from backend after uploading or replacing product images. It generates 320/640/960-pixel WebP variants on the public storage disk and exposes them as image_srcset; original files and database fields are retained. Remote and frontend asset URLs are not fetched by this command. Existing /storage serving must remain configured on the API host. Run this before the SEO build.

Optimized local images and WOFF2 fonts are committed. To regenerate them after editing source assets, run scripts/optimize-media.py with Pillow and scripts/optimize-fonts.py with fonttools and brotli. These are development tools, not production runtime dependencies.

## Check after deployment

- View source for a product: its title, description, heading, price and JSON-LD must be present without executing JavaScript.
- A random nonexistent URL and missing product URL must return HTTP 404.
- Confirm sitemap.xml uses the live domain and excludes checkout, cart, search and payment URLs.
- Submit the sitemap in Google Search Console; inspect representative product/category URLs and validate product markup with Google's Rich Results Test.
- Run PageSpeed Insights on the homepage, product page and category page. Local browser checks are not production Core Web Vitals measurements.
- Confirm Googlebot, Bingbot and OAI-SearchBot are not blocked by hosting firewall/bot protection. Allowing search crawling does not guarantee indexing or AI recommendations.

For local browser regression checks: start Vite, then run `npm run check:seo` with `TEST_ORIGIN` set to its origin. The check uses isolated mock catalog data and never places orders. Set `BROWSER_CHANNEL=chrome` to use an installed Chrome for prerendering; otherwise install Playwright Chromium.

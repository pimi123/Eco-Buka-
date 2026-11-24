# Paid-only Meta tracking — implementation and deployment

## Implemented locally

- Existing Pixel ID 1426946475629785 retained; no second Pixel or dataset was created.
- Removed unconditional inline Meta/GA snippets and the noscript marketing beacon. First-party compiled JavaScript loads optional SDKs only after the relevant consent choice and server feature flag. Separate analytics/marketing preferences are default-denied, versioned and editable.
- Browser events: PageView for initial/SPA pages, ViewContent after a real product loads, AddToCart after mutation, InitiateCheckout for a nonempty cart. AddPaymentInfo is intentionally absent because the hosted bank UI does not provide a trusted accepted-details signal.
- **OrderSubmitted and Purchase are server-only business events.** OrderSubmitted is recorded in the order transaction. Cash acceptance is never Purchase. Purchase is recorded for the first approved, fully paid, matching EUR payment; there is no browser Purchase or late thank-you-page replay.
- The outbox has unique order/event constraints and stable IDs order_submitted_<id> / purchase_<id>. Callback/admin repeats, queued retries and page refreshes cannot create additional logical events. Checkout retries use a UUID attempt key and payload hash; changed payload under a reused key is rejected.
- Card initiation requires the opaque token returned by accepted checkout. Pending initiation retries reuse the gateway reference and nonce. Approval requires signed amount/currency and existing signature verification. Receipt verification also checks paid amount equals order total.
- Fixed the alternate NestPay session table mapping. Session approval uses frozen prices/quantities even when public prices or stock change after initiation, rather than silently repricing a paid order.
- Admin has a separate full-cash-receipt action with reference, actual collection time, channel, staff identity and receipt attestation. Fulfillment changes are independent. Partial/overpaid/cancelled/non-cash cases do not qualify.
- Matching payload and attribution context are encrypted. Email and valid international phone are normalized/SHA-256 hashed; eligible IP, UA, fbp and fbc remain unhashed. Ambiguous names, unsupported country codes and invented customer IDs are omitted.
- Catalog IDs are omitted unless META_CATALOG_IDS_VERIFIED is enabled after confirming website product IDs equal Meta retailer IDs. Do not enable it if a different SKU/variant mapping is used.
- Actual eligible fbclid is preserved as fbc only after consent. Withdrawal stops browser calls and revokes unsent events for remembered orders using opaque receipt tokens. This-device lookup is capped at 20 orders/30 days; cross-device requests/historical delivered data use the privacy support process. Hashing is not anonymization.
- Feature flag defaults off. Events created while disabled have no matching payload and are not backfilled when enabled. Refused/unknown consent is also ineligible without a personal payload.

## Server setup — not enabled or deployed by this change

Deploy backend/frontend together: old clients lack the new checkout UUID/token. Preserve APP_KEY because encrypted data depends on it. Use the normal database backup before production migrations.

```sh
cd backend
php artisan migrate --force
php artisan config:cache
php artisan queue:work --queue=meta,default --timeout=30 --tries=3
```

Supervise the worker and run Laravel's scheduler every minute through the existing scheduler/crontab (`php artisan schedule:run`). meta:dispatch recovers due durable rows. No Meta request runs inside a payment transaction. A worker consuming only the default queue will not consume the meta queue.

Provision these values only in the server's private environment:

```dotenv
META_ENABLED=false
META_PIXEL_ID=1426946475629785
META_ACCESS_TOKEN=
META_API_VERSION=v26.0
META_TEST_EVENT_CODE=
META_CATALOG_IDS_VERIFIED=false
```

An authorized Meta administrator must verify ownership, permissions and domain/ad-account connection. Do not put a token in VITE variables, frontend code, URLs, screenshots or chat. Use an approved test dataset first: test_event_code does not guarantee fake events are excluded from production measurement. Remove it for production. Enable META_ENABLED only after secure configuration, consent/privacy review and test approval. No token was supplied and no real Meta event was sent in these tests.

Review deploy/meta-csp.nginx.conf and deliberately replace/merge the frontend's enforcing and report-only policy. Do not append a second enforcing policy while keeping the old restrictive one, remove CSP, or add unsafe-inline JavaScript. Preserve and verify the bank form-action origin. Live SDK/CSP verification requires separate deployment approval.

Production bank success/failure URLs must reach the signature-verifying backend endpoints, not frontend or development nestpay-test routes. No real bank credentials/return URLs were changed. Result handling still depends on receipt of the bank response; provider server notifications/status reconciliation require bank support and further testing. CAPI cannot recover an approval the merchant never receives.

## Delivery, errors and retention

One logical event can have multiple HTTP attempts. Delivery uses an atomic cache lock, bounded timeouts and immutable occurrence time/payload. Use shared database/Redis cache across workers/hosts. Temporary failures/429/5xx back off with jitter; invalid credentials/payloads become failed. Sensitive API bodies and exception messages are not logged. Acknowledgment requires events_received=1 and proves API acceptance, not attribution.

Retries are capped at 12. Events older than seven days, or unknown-delivery retries later than 47 hours after their first attempt, expire without restamping. No automatic replay of delivered/failed/expired events or historical paid orders is implemented. A genuinely new payment on an old order uses the new payment occurrence time. Card paid_at is the trusted approval callback-processing time; no unverified provider-origin timestamp is invented.

Daily retention removes matching payload/context after 30 days. Financial ledger rows remain for aggregate reconciliation. No refund/negative Purchase or partial-payment conversion is invented. Partial collections, refunds and chargebacks require an approved finance workflow; cancellation does not retract an event already delivered to Meta.

```sh
php artisan meta:dispatch --limit=100
php artisan meta:report --from=2026-11-20 --to=2026-11-21
php artisan meta:health
```

Report windows are [from,to), using application/DB time (UTC here). Report includes submitted orders, distinct fully paid orders/revenue, event statuses/values and prevented duplicates. Use a defined submission cohort for submission-to-paid rate. meta:health exits nonzero for invalid enabled configuration, failed/expired rows or pending/retry older than 30 minutes. Connect it to existing monitoring; no unsolicited email/Slack alerts are sent.

## Verification

PHP tests use SQLite memory, fake mail and fake/prohibited Meta HTTP. They do not change the application's MySQL settings. Browser tests mock API writes and block real conversion traffic.

```sh
cd backend
php vendor/bin/phpunit
```

When SQLite is disabled locally, enable it per command with -d extension=pdo_sqlite and -d extension=sqlite3 plus the correct extension_dir. Do not change MySQL configuration to run tests.

With Vite running locally:

```sh
npm run check:meta
npm run check:seo
npm run build
```

check:meta uses installed Chrome by default; BROWSER_CHANNEL selects another installed channel and TEST_ORIGIN selects Vite. It checks default-denied/reject/grant/revoke consent, strict script-src, product/cart/checkout events, unpaid cash submission, attribution, refresh and forged success URLs. It sends no real orders/payments/Meta events.

Still required: authorized Events Manager Test Events; real dataset/ad-account/catalog checks; approved bank sandbox tests; live CSP/consent checks; production worker/scheduler supervision and aggregate reconciliation. Local tests do not establish Event Match Quality, attributed ROAS or campaign eligibility.

## Validation result — 20 November 2026

- Local backend suite: 59 tests passed, 320 assertions. Includes accepted unpaid orders, checkout idempotency and refused-consent retries, verified/invalid/missing-field/mismatched callbacks, stable gateway references, repeated callbacks, separate legitimate orders, original confirmation time, manual full collection/permissions, partial collection rejection, frozen sale prices through a stock change, Meta outage/retry, permanent error, expiration, encrypted matching data, withdrawal, queue recovery and reporting/health commands.
- Browser check passed under a strict script-src policy without unsafe-inline JavaScript: no optional tracking before consent or after essential-only choice; correct product/cart/checkout events after grant; zero browser Purchase on cash acceptance, refresh or forged approval; real fbclid context preserved only after grant; withdrawal sent to the mock endpoint. All order/payment API writes and conversion traffic were simulated/blocked.
- Existing browser SEO/media checks passed. Production-style local build generated and verified 62 public HTML pages, sitemap and robots.txt with consent denied by default. This build uses LOCAL domain/API settings and is not a production artifact to upload unchanged.
- The new migration was successfully applied to verified LOCAL MySQL, after starting the existing local XAMPP database. No production database or MySQL connection settings were changed. Meta remains disabled; its real local public config endpoint returns enabled=false and exposes no token.
- Old callback test redirect contracts were corrected to current backend return paths/parameters; the real session table-name defect was fixed. No failing tests were skipped or removed.
- No real Meta events, real orders, bank payments, production CSP changes, campaign changes or production deployment were performed.

## Readiness answers

| Question | Current answer |
|---|---|
| Pixel installed? | Existing ID retained; new consent-aware first-party bootstrap is locally verified. Production remains on its existing blocked installation until separately deployed/configured |
| CAPI connected? | Client, encrypted durable outbox, retries and worker recovery implemented and tested with HTTP fakes. Real token/dataset acceptance is not verified |
| Independent OrderSubmitted? | Yes, backend event at accepted order creation |
| Paid-only Purchase? | Yes in local tests: verified full card payment or an explicit authorized full-cash receipt; never initial cash submission or receipt-page load |
| Accurate value/currency? | Server-confirmed amount, EUR, actual sale price and immutable snapshot; equality/positive-amount guards. Production bank semantics still need sandbox validation |
| Duplicates prevented? | Database uniqueness and idempotent checkout/payment processing verified locally. Purchase is server-only, so no browser/server Purchase copy needs merging |
| Purchase campaign optimization ready? | Not yet: production deployment, real Meta/account testing and volume assessment remain |
| Purchase/CPA/value/ROAS verified? | Not in the actual ad account; API acceptance and ad attribution are separate |
| Missing before launch? | Secure token, approved test dataset, catalog mapping review, live CSP, worker/scheduler supervision, bank sandbox verification and aggregate reconciliation |
| Black Friday launch-ready? | Local implementation is tested; production/Meta validation is still required before declaring campaign readiness |

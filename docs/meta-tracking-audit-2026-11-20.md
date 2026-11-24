# EcoFlow Kosovo — Meta Pixel and Conversions API audit

**Follow-up:** local implementation and test results after this audit are documented in [meta-tracking-implementation.md](meta-tracking-implementation.md). Findings below describe the audited pre-implementation state; production infrastructure and Meta account verification were not changed by the local implementation.

**Audit date:** 20 November 2026, Europe/Tirane.  
**Storefront:** https://ecoflowks.com/  
**Scope:** repository review, public live-site inspection, isolated local tests, official Meta documentation, proposed implementation and validation plan.  
**Change boundary:** this report is the only new project file created for this audit. No tracking implementation, production infrastructure, Meta assets, campaigns, real orders or real payments were changed. Earlier uncommitted SEO/performance work was already present and was preserved.

## Executive conclusion

**The current tracking is not ready for a Sales campaign optimized for verified paid purchases.**

Two critical findings must be addressed together:

1. The deployed HTML includes Pixel `1426946475629785`, but the live enforcing Content Security Policy blocks its initialization. In isolated Chrome, `window.fbq` was undefined on product and checkout pages. The same policy blocks Google Analytics. This is a verified production runtime blocker, not simply an Events Manager access limitation.
2. The existing cash checkout calls `trackPurchase(response)` immediately after creating an unpaid order. This matches the earlier accepted-order tracking approach, but violates the new paid-only requirement. The deployed checkout JavaScript contains this same branch. **Do not simply unblock the Pixel first: that would activate incorrect cash-order Purchase reporting.**

There is no Conversions API implementation or independent OrderSubmitted event in the audited repository. Cash-payment collection has no structured confirmation workflow. Card payment verification exists, but tracking remains dependent on browser return and browser storage.

## A. Current implementation audit

Statuses describe the observed component, not assumed Meta account behavior. A browser call, accepted HTTP request, and advertising attribution are three different levels of evidence.

| Component | Existing status | Evidence | Issue | Recommended action |
|---|---|---|---|---|
| Meta Pixel | Incorrect | `index.html` initializes `1426946475629785`; live HTML contains that same ID; live `fbq` is undefined and console reports CSP violations | Installation exists in source but cannot execute on production | Preserve this installation/ID pending ownership verification; repair consent-aware loading and narrowly scoped CSP |
| Conversions API | Missing | No server Meta client, Graph `/events` calls, Meta configuration, outbox, or tracking jobs found | No repository-backed server delivery | Add a server integration after approval; external account-side integrations remain Not verified |
| PageView | Incorrect | One inline base call; production blocks it | No PageView in inspected sessions; no explicit SPA route hook in source | Consent-aware initial and subsequent route tracking, without counting initial navigation twice |
| ViewContent | Missing | Product detail component has no Meta event call | Product views unavailable to this integration | Track after a valid product has loaded |
| AddToCart | Missing | `cartStore.add()` and detail-view handler mutate the cart but send no Meta event | No successful cart-add tracking | Track the successful mutation with added quantity and selling price |
| InitiateCheckout | Missing | Checkout mounts and loads payment options without tracking | No checkout-start signal | Fire when checkout is entered with a nonempty valid cart; define a checkout-attempt ID |
| AddPaymentInfo | Missing | Card details are collected on a hosted bank page, not in the merchant form | Merchant cannot currently verify acceptance of payment details | Do not equate selecting a radio button or starting payment with this event; defer unless a reliable signal is available |
| Order Submitted | Missing | No `Lead` or `OrderSubmitted` call at accepted order creation | Submitted orders cannot be distinguished from paid sales | Use a custom `OrderSubmitted` event tied to the backend-created order |
| Purchase | Incorrect | Cash branch calls Purchase on order creation; card return requests backend verification first | Unpaid cash orders qualify in current code; card events are browser dependent; live CSP currently suppresses execution | Cash creation must emit only OrderSubmitted; emit Purchase from the first verified full-payment confirmation |
| Payment confirmation | Partially working | Signed NestPay response validation; approved payment and `paid_at` persistence; local hash tests pass | No verified live bank transaction; no structured cash confirmation; alternate checkout-session defect; settlement semantics unverified | Keep verified card checks, close identified gaps, add an auditable manual-payment workflow |
| Event deduplication | Partially working | Stable `purchase_<order.id>` and browser storage guards; payment callbacks have row locks and processed timestamps | No persistent tracking-event uniqueness; a second browser can attempt the event again; no server/browser setup to validate | Unique database event per order/event name; shared IDs for approved dual-source delivery |
| Customer matching | Missing | Checkout stores customer details but no server matching payload or order attribution context | No consent-linked `_fbp`, `_fbc`, click ID, client IP/UA persistence | Capture eligible context at submission and normalize/hash only permitted fields |
| Event reliability | Missing | Browser helper marks an event sent after calling/queuing `fbq`; no acceptance check/outbox/retry | Misses can be permanent; browser exit prevents delivery | Transactional outbox, retryable worker, bounded retries and reconciliation |
| Meta account/campaign setup | Not verified | No authenticated Events Manager/Business Portfolio inspection was performed | Dataset ownership, ad-account connection, diagnostics and optimization eligibility are unknown | Complete the administrator checklist below |

### Every existing explicit Meta event

| Event / branch | Trigger and source | Explicit parameters | Pixel/dataset evidence | Duplication and effectiveness | Disposition |
|---|---|---|---|---|---|
| PageView | Browser inline base script on a full document load | No explicit custom parameters or event ID | Source and live HTML both initialize `1426946475629785`; intended business ownership Not verified | One explicit base installation; blocked by production CSP; SPA navigation has no explicit PageView call | Maintain, repair execution/consent and add controlled route handling |
| Purchase — cash | Browser, after `POST /orders` returns successfully and `paymentMethod === 'cash'` | Numeric `value = Number(order.total)`, `currency`, fourth-argument `eventID = purchase_<id>` | Uses global `fbq('track', ...)` and the base Pixel above | Business rule is incorrect for paid-only tracking; same-browser suppression exists; currently blocked live | Replace this branch with OrderSubmitted, not another Pixel |
| Purchase — card | Browser `OrderSuccessView.onMounted()`, after `POST /orders/purchase-confirmation` succeeds using order number and tracking token | Same value/currency/eventID | Same base Pixel; Meta receipt Not verified | Better eligibility check than URL-only success, but depends on return-page execution and storage; live CSP prevents it | Move authoritative emission to server; optional verified browser copy with shared ID and a deduplication-age limit |
| PageView — noscript | Browser image only when JavaScript is disabled | Pixel `id`, `ev=PageView`, `noscript=1` | Same ID | Not a second JavaScript installation; production `img-src` also does not allow the Facebook beacon host | Review with the consent design; do not treat it as a safe consent bypass |

No explicit ViewContent, AddToCart, InitiateCheckout, AddPaymentInfo, Lead or OrderSubmitted events were found. Google uses direct `gtag` with `G-4G4FW956HL`, not a `GTM-...` container. A `dataLayer` for gtag does not itself establish Google Tag Manager usage. Automatic events configured inside Meta cannot be assessed without account access, and the blocked live SDK cannot execute them in the inspected sessions.

## B. Existing architecture and workflows

### Stack and deployment evidence

| Area | Observed implementation |
|---|---|
| Frontend | Vue 3 + TypeScript, Pinia, Vue Router history mode, Vite; installed lockfile versions: Vue 3.5.35, Vue Router 4.6.4, Pinia 2.3.1, Vite 5.4.21, TypeScript 5.9.3 |
| Backend | PHP `^8.3`, Laravel requirement `^13.8`, installed framework v13.14.0; controllers/services and Eloquent ORM |
| Database | Local backend configuration specifies MySQL. Production database configuration was not accessed. Tests explicitly use SQLite `:memory:`; running them did not change MySQL settings |
| Routing | `src/router/index.ts` handles storefront routes; `backend/routes/api.php` exposes JSON endpoints; `backend/routes/web.php` protects admin routes with `auth` and `EnsureAdmin` |
| Rendering | Live HTML is a small client-rendered shell with an empty `#app`, `lang="en"` and `/assets/index-CcovJUp_.js`. Earlier local SEO work adds optional prerendering; it is not the inspected deployed build |
| Hosting | Public responses identify Nginx. The CSP is supplied by production infrastructure, whose configuration is not present in the inspected deployment files. The browser contacts `api.ecoflowks.com`. OS, process manager, deployment revision, production credentials and worker supervision remain Not verified |
| Orders | `CreateOrderService::create()` creates an order and item snapshots in one database transaction, with order number and random tracking token |
| Payments | `card` and `cash` only in checkout validation; NestPay hosted 3D payment integration. Local configuration points to a TEB Kosovo staging gateway and a development return tunnel, not evidence of production gateway settings |
| Queues | Laravel database queue configured locally; jobs/failed_jobs migrations exist. No Meta job/outbox, scheduled reconciliation or verified production queue worker found |
| Customer accounts | No customer storefront sign-in/account flow found in routes or inspected pages. Backend user/authentication infrastructure is used for admin |
| Tracking | Pixel base, browser Purchase helper, direct Google Analytics. No Meta SDK/library or server event integration found |

### Accepted order submission

Relevant files/functions:

- `src/views/website/CheckoutView.vue::submitOrder()`
- `backend/routes/api.php`: `POST /api/orders`
- `backend/app/Http/Requests/StoreOrderRequest.php`
- `backend/app/Http/Controllers/Api/OrderController.php::store()`
- `backend/app/Services/Orders/CreateOrderService.php::create()`
- `backend/app/Models/Order.php` and `OrderItem.php`

The form sends customer, delivery and policy-acceptance fields, payment method, product IDs, quantities and selected options. Email, name, phone, municipality, address, supported country and policy acceptance are validated. Backend product availability is checked again under row locks.

The server uses the current product `price`, not client-supplied money or `old_price`. Arithmetic is accumulated in cents; item unit prices, line totals and a product snapshot are stored. Shipping is currently explicitly `0`; total equals subtotal plus that fee; currency is `EUR`. No separate tax line, tax calculation, coupon calculation or partial-payment model was found in this creation path. Any tax already embedded in product prices cannot be determined from this code alone.

An order is successfully submitted when this transaction commits and the API returns HTTP 201 with its ID, order number, total and status. Initial status is `pending`, including cash orders. For cash, a confirmation email is attempted after commit. That email does not prove payment.

**Repeated requests can create separate orders.** The UI has loading/submission flags, but the backend has no checkout request-idempotency key. Unique order numbers and payment idempotency keys do not deduplicate a customer's retried order-creation request.

### Online card lifecycle

Current frontend path:

1. Create a pending order through `POST /api/orders`.
2. Call `POST /api/orders/{id}/payments/nestpay`.
3. `InitiateNestPayPaymentService::initiate()` locks the order; validates currency, amount, state and absence of an already approved payment; creates/reuses a pending payment.
4. It produces a signed NestPay form using the server's stored amount. The frontend constructs a hidden HTML form and performs a top-level POST to the configured bank gateway. No card fields are collected in the merchant form. This is not a merchant-page payment iframe.
5. Bank response returns to the configured success/failure endpoint. `NestPayResultController` delegates to `ProcessNestPayResultService::process()`.
6. `NestPayHashService::validateResponseHash()` verifies the SHA-512-based ver3 signature using the server store key with `hash_equals`. The service locates the payment, locks it and checks `processed_at` for repeat handling.
7. Returned amount/currency are compared with stored payment values **when those fields are supplied**. Approval requires `Response === 'Approved'` and `ProcReturnCode === '00'`. It stores `status=approved`, `paid_at`, `processed_at` and provider references, and advances a pending order to `confirmed`.
8. The server redirects to the merchant success page with the order number and tracking token. The success view calls `/orders/purchase-confirmation`; that endpoint requires the token, `payment_method=card`, an approved payment with `paid_at`, and a noncancelled order. Only then does the browser helper attempt Purchase.

The existing signature verification is worth preserving. It is not equivalent to relying on `?payment=approved`. However, production callback URL configuration, bank contract/authorization/capture semantics and end-to-end payment completion were not verified. `paid_at=now()` records when the callback was processed, not a separately verified gateway transaction timestamp.

No independent server-to-server payment webhook/reconciliation mechanism was found beyond the configured result endpoints. If the bank return depends on the customer's browser completing the POST, server-side Meta delivery alone will not recover a payment the merchant never learned about. Verify bank notification/status-query support before claiming browser-independent payment confirmation.

There is a second backend path, `POST /api/checkout/nestpay`, using `InitiateNestPayCheckoutSessionService`, which defers order creation until approval. The current frontend does not call it. It still requires review because it is routed and the callback service falls back to it for unmatched payment references.

### Cash/manual payment lifecycle

Cash orders are saved pending and immediately attempt browser Purchase. No cash payment record is created. The admin update action only accepts an order `status` and `admin_note`; it does not record amount collected, paid timestamp, collection reference or confirming administrator as a payment transaction.

`confirmed`, `processing` and `completed` are fulfillment/order states. Staff can set them directly. **None is a reliable proxy for cash collected.** The source does not establish a qualifying manual-payment transition. Bank transfer, delivery-provider remittance ingestion, partial payment, capture, refund and chargeback workflows were not found.

Proposed prerequisite: an explicit authorized "Confirm payment received" action records collection amount, currency, payment method, actual collection time, confirming staff member and evidence/reference. Delivery alone is not sufficient. The exact point—customer cash collection or receipt of remitted funds—must be approved by the business and used consistently.

### Actual statuses

- Order: `pending`, `confirmed`, `processing`, `completed`, `cancelled`, `payment_failed`.
- Payment: `pending`, `approved`, `declined`, `error`, `cancelled`.
- There is no order `paid` state or order-level `paid_at`. Card evidence lives on `payments`.
- A first qualifying Purchase should use a verified full-payment transition; later fulfillment edits must not create another event. A new tracking event must not be tied merely to `Order::setStatus()`.

## C. Prioritized findings

### Critical

**C1 — Live CSP disables the installed trackers.** The live response includes both enforcing and report-only CSP. The enforcing policy includes `script-src 'self'`, `connect-src 'self' https://api.ecoflowks.com`, and `img-src 'self' data: blob: https://api.ecoflowks.com`. It permits neither inline tracking initialization nor Meta/Google remote script and event origins. Chrome reported blocked inline execution and blocked gtag.js; `fbq` and `gtag` were undefined. There is no second functioning Pixel to preserve as an alternative.

**C2 — Cash creation is mislabeled as a paid purchase.** Checkout line 140 calls Purchase before cash collection. It must be corrected before enabling CSP access. Historical false conversions, if any were received under earlier policies, cannot be assumed to disappear after a code fix.

### High

**H1 — Paid events have no backend delivery.** Closing the bank/return tab, script blocking or missing return token can suppress tracking. There is no CAPI or durable event ledger.

**H2 — No cash-payment confirmation model.** A fulfillment dropdown cannot establish collected revenue. Do not automatically map `completed` to paid.

**H3 — Cookie-policy/implementation mismatch.** The policy describes essential-only storage and conditionally enabled marketing cookies, but the base tracking snippets are unconditional. No consent UI/gating was found in source or inspected live pages. The checkout terms checkbox covers purchase policies, not an explicit advertising-sharing choice. CSP currently suppresses execution; that does not implement a consent system. Determine applicable legal basis, policy disclosures, retention and withdrawal handling before enabling customer-data sharing. This is a technical finding, not a legal determination for all jurisdictions.

**H4 — Order creation lacks request idempotency.** A network retry can create a second order and future second logical conversion. Add backend idempotency without merging legitimate repeat purchases.

**H5 — Alternate session model/table mismatch.** Eloquent resolves `NestPayCheckoutSession` to `nest_pay_checkout_sessions`; its migration creates `nestpay_checkout_sessions`, and the model has no explicit table override. A local test of an unmatched provider reference reaches this path and returns 500. The alternative checkout-session payment path is not production-safe based on the audited code/tests.

**H6 — Alternate session approval can reprice an already authorized transaction.** It saves amount/item snapshots at initiation, but approval calls `CreateOrderService::create($session->order_payload)`, which reads current product prices and stock again. A Black Friday price or stock change while bank payment is pending could produce an order amount different from the approved payment—or fail order creation after the bank accepted payment. Approval must use a validated immutable snapshot or a deliberate reconciliation path, not silently reprice.

**H7 — Production callback configuration requires verification.** Return URL defaults can lead directly to frontend routes; the example environment also references development test-return routes that do not verify payment. Verify actual production values route through signed result processing. This is a configuration risk, not a claim that production currently uses those development defaults.

**H8 — Payment initiation has no order-ownership credential.** The public route accepts a bound numeric order ID, and `InitiateNestPayPaymentRequest::authorize()` returns true; no order tracking token is checked there. Require an opaque signed/secret checkout credential to prevent unrelated callers from initiating or replacing another order's pending payment. No live exploitation was attempted.

### Medium

- Browser deduplication is local only. In isolated execution, one module emitted once, reload with the same storage stayed at one, and a new storage context emitted a second request for the same synthetic order. This proves repeat attempts are possible, not that Meta necessarily counted two conversions.
- A stub/queued `fbq` call marks local storage as sent even when no event has been delivered. No acknowledgment/retry exists.
- Card verification returns `order.total`, not an immutable confirmed payment amount, and does not independently compare it with the approved payment amount. Ordinary order-first flow uses the same server total, but this invariant needs an explicit check and tests.
- The success page displays an approval message from URL-derived presentation state even if its verification request fails. Tracking is gated for card, but the customer-facing message can be misleading.
- Amount/currency checking is conditional on fields being present; verify the bank's signed response schema and required fields, rather than silently interpreting a sparse response as fully checked.
- No persisted Meta attribution context, explicit customer normalization/hashing, catalog ID mapping, delivery health metrics or reconciliation.
- Provider response metadata uses an exclusion list, not a minimal allowlist. Admin mail-error logs include customer email. Minimize stored/logged fields and verify that bank payload variations cannot retain prohibited card data. No real customer logs were opened.
- Tracking/verification endpoints and bank callbacks share the `orders` IP rate limiter. Review legitimate callback bursts and NAT/shared-gateway traffic separately; do not remove authenticity checks.

### Lower-priority funnel improvements

Add product/cart/checkout events with meaningful success conditions, update SPA PageView handling and verify catalog matching. Do not fabricate an AddPaymentInfo signal on hosted payment initiation.

## D. Proposed architecture — approval required

### Business rules and event plan

Use custom **OrderSubmitted**, rather than Lead, because this action is an accepted commerce order, not a general inquiry. Keep paid Purchase as a separate standard event.

| Event | Exact intended trigger | Source and logical ID | Parameters / restrictions |
|---|---|---|---|
| PageView | Eligible initial page and completed SPA navigation, after required consent | Browser, fresh page/navigation occurrence | Do not double count the initial router navigation |
| ViewContent | A real active product finishes loading | Browser, product-view occurrence | Verified catalog-compatible content ID, content_type, optional selling value/EUR |
| AddToCart | A valid item is successfully added to the cart | Browser, mutation occurrence | Added quantity, selling item_price, added-line value, currency, content IDs |
| InitiateCheckout | Customer enters checkout with a nonempty eligible cart | Browser, checkout attempt | contents, content IDs, numeric value, EUR and num_items |
| AddPaymentInfo | Only if an accepted-payment-details signal is actually available | Defer under current hosted-bank integration | Do not substitute radio selection, submit click or gateway initiation |
| OrderSubmitted | First durable accepted order creation | Server outbox; optional browser acknowledgment copy | `order_submitted_<order-id>` shared across copies; no event for failed validation/creation |
| Purchase — card | First verified fully paid eligible order, from the authoritative payment-confirmation service | Server outbox; optional browser copy from the verified receipt response | Preserve stable `purchase_<order-id>` where appropriate; confirmed amount/EUR and item snapshot |
| Purchase — cash/manual | Actual payment collection recorded and authorized | Server outbox only by default | Actual collection time; honest action_source for how the conversion occurred; no event at initial order creation |

Pixel ownership must be confirmed before production configuration. No second installation or dataset will be created to work around the existing issue.

### Durable event flow

1. Persist consent/attribution context at checkout only where permitted, associated with the accepted order or prepayment session.
2. Deduplicate checkout submissions with a stable per-attempt idempotency key and payload hash. Same key/same payload returns the original accepted order; changed payload under the same key is rejected or requires a new attempt. Different legitimate attempts remain distinct.
3. Inside the transaction that accepts an order, insert one OrderSubmitted outbox row.
4. Inside the transaction that first confirms qualifying full payment, insert one Purchase row with `UNIQUE(order_id, event_name)` or equivalent persistent uniqueness. Retain a reference to the payment evidence. One order must not gain extra Purchase events for fulfillment edits or a second callback/payment row.
5. After commit, a worker sends the stored event to Meta. Do not make Meta network requests while holding payment/order locks. Database polling/scheduled recovery must recover unsent outbox rows even if a queue dispatch is missed.
6. Persist event occurrence time separately from created/attempted/delivered timestamps. Retries reuse the same ID, amount and event time.
7. Use timeouts, exponential backoff, jitter and explicit pending/retry/delivered/permanent-failure/expired states. Treat authentication, invalid payload, throttling and transient outages distinctly. Never log tokens or complete user_data/payloads.
8. Track unknown delivery outcomes explicitly. Do not promise exactly-once HTTP delivery: there must be one logical persistent event, with controlled retry/deduplication. Late duplicate browser delivery outside Meta's documented window must be suppressed, not assumed safe forever.
9. An optional browser Purchase is returned only from verified payment evidence and shares the server event ID. Do not send a fresh browser copy days later merely because a receipt is revisited. Cash/manual conversions need no browser copy.

### Payment and revenue definition

Proposed revenue is the full verified customer transaction amount in EUR, after selling-price discounts, including charged delivery and any tax already included in the charged amount. Current delivery is zero and there is no separate tax calculation. Do not add estimated tax or use `old_price`.

For card, use immutable `payments.amount/currency` from verified payment evidence and reconcile with immutable order/item totals. For manual cash collection, use the confirmed full collected amount and an explicit received-at time. Partial payments must not each generate a full-order Purchase; until supported and approved, route partial/overpaid/mismatched cases to reconciliation without automatic full-sale reporting.

Do not emit extra Purchases for shipment, delivery or status re-saves. Refunds and chargebacks do not automatically reverse a previously accepted Meta Purchase. Maintain internal net-revenue/refund reporting and approve any separate Meta reporting scheme before implementing it; never send a negative Purchase as an improvised reversal.

### Exact customer-data availability

| Field | Available now? | Proposed handling |
|---|---|---|
| em | Required checkout email; stored on order | If permitted, normalize lowercase/trim and SHA-256; omit unusable data |
| ph | Required phone, currently only validated as a string | Validate/normalize international digits using confirmed country context, then SHA-256; do not guess an ambiguous number |
| external_id | No stable storefront customer account ID found | Optional; omit until an appropriate stable customer identifier is defined; an order ID is not automatically a customer ID |
| fn, ln | Only combined customer_name | Optional; avoid guessing name boundaries; use only a verified parsing/collection policy |
| ct | Municipality and city stored | Normalize per Meta field rules and hash if permitted |
| country | Human-readable country label stored | Map only to a Meta-supported code; specifically verify Kosovo support instead of inventing a mapping or silently substituting another country |
| zp | Optional postal code | Normalize/hash only when actually provided |
| client_ip_address | Available transiently on checkout request; not stored for attribution | Preserve eligible original shopper IP with trusted-proxy configuration; never substitute worker/server IP; do not hash |
| client_user_agent | Available on browser checkout request; not stored for attribution | Preserve eligible shopper UA for website events; never use the worker UA; do not hash |
| fbp | May exist only when the Pixel can run; not currently associated with orders | Preserve the actual eligible cookie value, without hashing |
| fbc / fbclid | Not captured by current order API | Preserve actual click context where permitted. Derive fbc only from a real received fbclid with the original observation time; do not invent click identifiers |

Customer data requires consent/legal-basis and retention review. Hashes remain sensitive matching data and are not a substitute for that review. A refusal must not be bypassed by sending the same data from the server. Store minimal context, restrict staff access, protect it at rest and define deletion/withdrawal handling.

### Meta API requirements verified against official documentation

The implementation should use a versioned server-side endpoint of the form `POST https://graph.facebook.com/{version}/{pixel_id}/events`. Keep token, dataset ID, API version and optional test code in backend configuration; never use a `VITE_...` token or send it to the frontend. Meta's current Graph version table lists **v26.0**; CAPI follows Graph's support schedule rather than the shorter general Marketing API schedule. Pin a supported version, test it and schedule upgrades. [Meta versions](https://developers.facebook.com/docs/graph-api/changelog/versions), [CAPI usage](https://developers.facebook.com/documentation/ads-commerce/conversions-api/using-the-api).

Server events require their occurrence name/time, user_data and an accurate action_source. Website events also require an event_source_url and shopper user agent. Persist an explicit event ID even where the API lists it as optional. Use a sanitized merchant source URL with no order tracking tokens, email or other personal query parameters. Actual digital card checkout can be classified as website; staff entering a cash receipt in the admin UI does not make the cash collection a website conversion. Determine the real cash channel; do not label delivery collection as a physical_store visit unless that actually happened. [Server parameters](https://developers.facebook.com/documentation/ads-commerce/conversions-api/parameters/server-event), [required matching parameters](https://developers.facebook.com/documentation/ads-commerce/conversions-api/best-practices).

Use a numeric monetary `value`, `currency: EUR`, string `order_id`, catalog-compatible string content_ids and contents objects with id/quantity/item_price where available. The current standard-parameter reference limits num_items to InitiateCheckout; do not add it automatically to Purchase as though it were a required purchase field. The Meta catalog and its retailer IDs are Not verified. Current database product IDs are only a proposed mapping until the catalog is inspected. [Custom parameters](https://developers.facebook.com/documentation/ads-commerce/conversions-api/parameters/custom-data).

Normalize and SHA-256 the appropriate contact fields; do not hash IP, UA, fbp or fbc. Matching fields are not all mandatory, but combinations must meet Meta's matching requirements. Email and phone are available; do not fabricate missing fields. [Customer parameters](https://developers.facebook.com/documentation/ads-commerce/conversions-api/parameters/customer-information-parameters).

For dual-source events, browser eventID and server event_id, event name and dataset must correspond. Meta documents a **48-hour** deduplication window; persistent backend uniqueness is still necessary. [Deduplication](https://developers.facebook.com/documentation/ads-commerce/conversions-api/deduplicate-pixel-and-server-events).

Use Unix seconds for the actual occurrence time, not the retry time. The website server-event reference allows events up to **seven days** old; do not restamp older conversions or let an expired event invalidate a batch. A cash order submitted ten days ago but genuinely paid today has a new payment occurrence today; the order date is not the Purchase date. An already-paid event left unsent for too long is a different problem. Investigate any applicable supported offline-event rules before choosing an alternative; the offline-specific documentation returned HTTP 429 during this audit, so no different offline age limit was verified here. [Event-time rules](https://developers.facebook.com/documentation/ads-commerce/conversions-api/parameters/server-event), [attribution identifiers](https://developers.facebook.com/documentation/ads-commerce/conversions-api/parameters/fbp-and-fbc).

Use Test Events on an approved test dataset/environment. Meta explicitly states that test_event_code events are not dropped and can participate in measurement/targeting. Do not assume a test code makes fake orders harmless in a production dataset. No such requests were sent in this audit. [Meta Test Events](https://developers.facebook.com/documentation/ads-commerce/conversions-api/using-the-api#testEvents).

### File-by-file implementation plan

All entries below are proposed, not implemented in this audit.

| File/component | Current → required behavior and reason | Dependencies | Risk | Validation |
|---|---|---|---|---|
| `CheckoutView.vue`, `src/lib/metaPixel.ts` | Cash Purchase → accepted OrderSubmitted; correct purchase eligibility before unblocking live Pixel | Approved event contract | High: attribution meaning changes | Cash HTTP 201 emits OrderSubmitted and no Purchase; validation/network failures emit neither |
| `index.html`, proposed consent/analytics module, router hook | Unconditional inline boot and document-only PageView → single consent-aware initialization and SPA events | Consent decision; approved same dataset | Medium | No requests before required consent; one initial PageView; one per subsequent eligible navigation |
| Production Nginx CSP configuration | Self-only tracker-blocking CSP → narrowly scoped consent-compatible scripts/connect/img rules | Production administrator and explicit infrastructure approval | High | Stage/report-only validation; retain payment form-action restrictions; no blanket unsafe-inline/CSP removal |
| `StoreOrderRequest`, order controller/service, checkout types and schema migration | No order attribution/request key → validated minimal context and atomic checkout idempotency | Data retention/consent rules; frontend/back-end compatibility | Medium | Same request key is one order; separate legitimate checkout keys create separate orders; no client-set prices/IP |
| `ProcessNestPayResultService` and proposed payment-confirmation service | Approved rows only → immutable confirmed sale + unique outbox Purchase in same transaction | Bank callback schema; migration; confirmed full-payment rule | High | Signed approvals, wrong hashes, wrong/missing fields, duplicates, amount mismatch, callback order and queue outage |
| `NestPayCheckoutSession` and session services | Table mismatch/repricing → explicit correct table and preserved accepted payment/item snapshot | Decision whether to retain this alternative flow | High | Unmatched OID is a safe rejection; price/stock change during payment cannot silently alter paid amount |
| Admin order/payment controller, request and view; proposed payment schema additions | Fulfillment edits only → auditable restricted confirm-payment action | Cash collection definition and permissions | High | Unpaid completed order emits no Purchase; confirmed full receipt emits one; repeated saves do not duplicate |
| Proposed `meta_conversion_events` migration/model/outbox service | No event ledger → unique order/event, original time, amount, delivery status and attempts | Existing database; retention policy | Medium | Transaction rollback, uniqueness/concurrency and recovery tests |
| Proposed `config/meta.php`, server Meta client, job and dispatch/reconcile command | No server delivery → versioned authenticated request, safe retries and health metrics | Backend token, verified dataset, worker/scheduler, network access | Medium | HTTP fakes for timeout/429/5xx/invalid token, stable retries, no token/payload logs |
| `OrderController::purchaseConfirmation`, `OrderSuccessView.vue` | Browser-only event and URL-derived approval UI → authoritative verified receipt with shared ID and late-copy guard | Server ledger/payment state | Medium | Forged URL, bad token, failed/unpaid/cancelled cases cannot produce Purchase or misleading paid UI |
| Product detail, cart store and checkout | No funnel events → actual success-based ViewContent/AddToCart/InitiateCheckout | Consent, product ID mapping | Low–medium | Product error/out-of-stock does not qualify; added quantity/selling value correct |
| Payment-initiation route/request | Numeric order ID alone → opaque verified checkout ownership credential | Coordinated frontend/API response update | Medium | Unrelated/invalid credential rejected; existing legitimate checkout remains functional |
| Cookie/privacy policies and consent UI | Essential-only claims/terms acceptance → accurate disclosure and real preferences | Business/legal review | Medium | Reject/withdraw flow blocks future eligible sharing without breaking checkout |
| Local tests and staging deployment checks | Incomplete/outdated payment tests → current contracts and 16-case tracking matrix | Isolated data, bank sandbox, test dataset | Medium | No production orders; review all failures before rollout |

Recommended rollout order: approve business rules and data handling; fix incorrect cash classification; add durable payment/outbox integration and consent controls; validate locally and in staging; verify account setup; approve narrowly scoped CSP/deployment changes; monitor reconciliation before Purchase-optimized campaign launch. Establish a cutover date; do not blindly replay historical cash orders, purchases or browser receipts into Meta.

## E. Implementation status

**No implementation changes were made in this audit.** This follows the supplied instruction to assess the current system first and the separate requirement to approve the architecture before backend integration. No new Pixel, CAPI endpoint, migration, consent banner, admin payment action or production configuration has been installed.

Missing production prerequisites are not filled with guessed values: Meta business/dataset ownership, secure token provisioning, test dataset, worker supervision, actual bank callback configuration, catalog mapping, manual collection policy and consent/retention decisions remain outstanding.

## F. Validation evidence and acceptance matrix

### Checks actually performed

1. Read source, routes, validation, models, migrations, payment services, admin flow, policies, package/composer lockfiles and selected nonsecret configuration. Secret values and customer records were not displayed.
2. Inspected live homepage, category, product, add-to-cart, cart and checkout in isolated Chrome. Adding to cart changed only that session's localStorage. Checkout showed card/cash choices, one merchant form and no payment iframe. No customer account link was found. Network interception prohibited non-GET requests and blocked conversion beacons; no real order/payment was submitted.
3. Confirmed the live Pixel ID in HTML, read deployed checkout/helper assets and observed enforcing CSP errors. Deployed checkout calls its Purchase helper in the cash branch; live `fbq` was undefined. No Meta event request was observed in these inspected sessions.
4. Executed the existing browser helper in memory with synthetic data: `value` serialized as numeric `249.9`, currency `EUR`, event ID `purchase_12345`; repeated same-module/same-storage attempts stayed at one; a separate storage context produced a second call; a queued stub was marked sent without delivery. No Meta requests were made by this helper test.
5. Ran existing local PHP tests against SQLite in memory, with mail/queue test configuration, without changing production/local MySQL settings. Selected feature suite: **19 tests, 13 passed, 6 failed, 81 assertions**. Separate NestPay hash suite: **9 passed, 12 assertions**. Combined: **28 tests, 22 passed, 6 failed**.
6. Reproduced model table naming without accessing production data: Eloquent returned `nest_pay_checkout_sessions`, while the migration creates `nestpay_checkout_sessions`.
7. Read current official Meta documentation for Pixel event semantics, CAPI endpoint/versioning, customer matching, deduplication, action_source and timestamps. Some Meta pages were rate limited and the Business Tools legal page required login; those areas were not represented as fully verified.

### Existing feature-test results

| Suite | Result | Meaning |
|---|---|---|
| CashOrderTest | 3 passed | Cash saved pending/unpaid; card initiation forbidden for cash; validation coverage. Does not validate correct Meta Purchase semantics |
| PurchaseConfirmationTest | 1 passed | Bad token rejected, unpaid card rejected, approved paid card response accepted with actual server total, cancelled order rejected |
| NestPayPaymentInitiationTest | 9 passed | Server authoritative amount and initiation/instalment/state checks in test environment |
| NestPayResultHandlingTest | 6 failed | Five redirect expectations conflict with current return path/query behavior; those assertions fail before later state assertions. Unknown provider OID produces HTTP 500 through the checkout-session table mismatch |
| NestPayHashServiceTest | 9 passed | Local signature generation/verification cases; not a production bank certification |

Failures were not edited away. Passing initiation/signature checks does not establish a validated end-to-end payment. None of these tests confirms receipt in Meta Events Manager.

### Required end-to-end acceptance matrix

These are **future acceptance criteria**, not a claim that the unimplemented integration passed them.

| # | Scenario | Required result | Current evidence / remaining work |
|---|---|---|---|
| 1 | Product view | ViewContent with mapped product parameters | Missing in repository; live event blocked |
| 2 | Add to cart | AddToCart for actual added quantity/value | Cart mutation observed; event missing |
| 3 | Checkout start | InitiateCheckout on meaningful entry | Flow observed; event missing |
| 4 | Accepted unpaid order | One OrderSubmitted; zero Purchase | Current cash code violates this criterion |
| 5 | Failed card payment | Zero Purchase | Source checks present; callback suite not clean; staging verification required |
| 6 | Verified full payment | One logical Purchase with paid amount/time | No server event exists; local confirmation API test passes |
| 7 | Receipt refresh | No additional logical Purchase | Same-storage helper guard demonstrated; persistent/cross-context protection missing |
| 8 | Repeated callback | No new payment conversion event | Payment guard exists; persistent event guard missing and callback regression test fails early |
| 9 | Payment several hours after order | Original real payment time; preserved eligible attribution | Context capture/delivery missing |
| 10 | Manual cash confirmation | Purchase only on actual qualifying full collection | Prerequisite workflow missing |
| 11 | Cancelled unpaid order | Zero Purchase | Card confirmation test rejects cancelled orders; cash was already incorrectly attempted at creation |
| 12 | Two legitimate paid orders | Two independent logical Purchases | Stable per-order ID exists, durable order/event tests missing |
| 13 | Meta outage | Payment commit succeeds; one outbox row retries | No outbox/client currently exists |
| 14 | Browser/server copies | Shared name/ID, one deduplicated logical event in Meta | No CAPI, no authenticated Events Manager verification |
| 15 | Black Friday discount | Confirmed selling amount, not old_price | Order-first source uses selling price; session repricing defect must be resolved |
| 16 | Partial payments/refunds | Approved policy, correct internal net revenue, no improvised duplicate/negative Purchase | No supported workflow found; separate implementation approval required |

Meta API acceptance responses, Event Match Quality, event coverage, deduplication diagnostics and Test Events results are **Not verified**, not "passed" or "zero errors". No server payload was sent.

### Reconciliation and monitoring design

Report separately over a clearly defined time window and cohort:

- Accepted orders by submitted-at time, including unpaid/cancelled outcomes.
- First fully paid orders and confirmed revenue by actual paid-at time.
- Logical Purchase outbox rows, sum of their values and their associated payment evidence.
- Delivered/accepted, retrying, permanently failed, consent-ineligible and expired events.
- Duplicate attempts prevented, recorded without storing extra conversion rows or personal payloads.
- Differences between eligible paid orders and generated events; between generated and acknowledged events; between confirmed and transmitted values.

Delivery records do not exist today, so no real reconciled revenue/event totals are available from this audit. Do not query/display live customer data merely to fill the report. Start with synthetic staging data, then approve aggregate-only production reconciliation.

Backend acknowledgment proves API acceptance, not attribution to an ad. Ads Manager can report fewer purchases than the internal paid ledger because of attribution windows, matching, consent and other measurement rules. A submitted-to-paid conversion rate should use a submitted-order cohort and a defined observation horizon, not divide unrelated daily totals. Internal paid/refund records remain the revenue source of truth.

## Meta administrator verification checklist

No authenticated Business Portfolio, Events Manager, ad account, catalog or campaign access was used. The following must be verified by an authorized administrator or a separately authorized authenticated session:

1. Confirm the intended business/legal entity, dataset name and ownership of Pixel/Dataset `1426946475629785`; verify `ecoflowks.com` domain association and website connection.
2. Confirm connected ad accounts, system-user access, least-privilege token permissions, token provisioning/rotation and dataset access. Do not paste tokens into chat or frontend environment variables.
3. Inspect partner integrations, gateways, automatic events and Event Setup Tool URL/button rules. Disable only conflicting/incorrect Purchase definitions through an approved change; repository searches cannot rule out account-side integrations.
4. Inspect browser/server last activity, volume, diagnostics, rejected parameters, restrictions, Event Match Quality, coverage and deduplication. Record evidence and dates; do not infer these from code.
5. Verify actual Meta catalog retailer/product/variant IDs against website IDs before enabling catalog-matched events. A local database ID or schema.org SKU is not proof of a Meta catalog mapping.
6. Choose an approved test dataset/environment, obtain Test Events access/code securely and complete all applicable matrix scenarios. Confirm fake events cannot contaminate the real campaign dataset merely because a test code is present.
7. Verify real Purchase activity/value/currency and optional dual-source merging; confirm event selection is available in the intended Sales campaign/ad set, with appropriate attribution settings and permissions.
8. Review production aggregate reconciliation and alerting, bank callback verification and cash collection policy before launch. Evaluate conversion volume without relabeling unconfirmed orders as purchases.
9. No campaign creation, ad-set edits, spend changes or launches are authorized by this audit.

## G. Marketing readiness summary

| Question | Answer as of this audit |
|---|---|
| 1. Is Meta Pixel installed correctly? | Present once in source/live HTML, but **not functioning correctly live** because the enforcing CSP blocks it |
| 2. Is CAPI connected correctly? | **No implementation in this repository.** Any external Meta integration remains Not verified |
| 3. Is Order Submitted independent? | **No.** No independent event exists |
| 4. Does Purchase occur only after verified payment? | **No.** Cash creation currently attempts Purchase; card has a verification gate but is browser dependent |
| 5. Are purchase values accurate? | Ordinary order-first path uses server-calculated EUR selling total. End-to-end confirmed transaction/value accuracy is **not fully verified**, and the session path has a repricing risk |
| 6. Are duplicates prevented? | Some browser/payment guards exist; **persistent logical tracking-event idempotency and reliable dual-source behavior are missing** |
| 7. Can Meta Ads optimize for Purchase? | Intended ad-account/dataset event selection **Not verified**; current implementation should not be considered ready |
| 8. Are Purchase, Cost per Purchase, value and ROAS available? | These are the intended attributed reporting metrics once valid events/value and account setup are established; their current availability/accuracy in this account is **Not verified** |
| 9. What remains? | Approve paid/consent/revenue rules; repair cash semantics and CSP safely; add manual payment evidence, durable CAPI and attribution capture; resolve payment defects; validate staging and Meta; approve production deployment |
| 10. Ready for Black Friday Sales? | **No.** Do not optimize spend against the current signals as verified paid-purchase data |

**Next decision:** approve the proposed architecture and implementation scope before source changes. Production deployment, CSP changes, Meta asset changes and real-payment testing require separate explicit authorization, as specified in the supplied request.

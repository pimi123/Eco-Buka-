# Eco Buka Payment Gateway Roadmap

This document keeps the requirements and implementation plan for adding online Visa/Mastercard payments to Eco Buka later.

## Current Status

The app is currently a catalogue/CMS ecommerce foundation:

- Vue storefront
- Laravel backend/CMS
- Products and categories
- Hero banners, promo cards, showcase sections
- Image/video upload support
- Product detail pages
- Contact/request-offer flow

It is not yet a complete payment-ready ecommerce system.

Missing for online card payments:

- Cart
- Checkout
- Orders
- Order items
- Payment records
- Payment gateway integration
- Bank callback/webhook handling
- Success/failure/cancel pages
- Admin order management
- Customer order emails
- Refund/order status management

## Bank Approval Website Requirements

The public website should include:

- HTTPS on the main domain
- HTTPS on the API/admin domain
- Real products with real prices
- Clear product categories
- Product detail pages
- No placeholder text
- No broken images
- No broken links
- No visible 500 errors
- EUR pricing
- Clear delivery/shipping information
- Clear refund/return information
- Business contact details

Required public pages:

- About us
- Contact
- Terms and Conditions
- Privacy Policy
- Refund / Return Policy
- Shipping / Delivery Policy
- Payment Policy
- Warranty Policy
- Cookie Policy

Business details to show on the site:

- Legal business name
- Business registration number
- Fiscal number / VAT number if applicable
- Registered address
- Phone number
- Official email
- Working hours

## Information Needed From The Business

Before implementation, collect:

- Legal business name
- Business registration number
- Fiscal number
- VAT status
- Registered address
- Official email
- Phone number
- Delivery areas
- Delivery fees
- Return/refund rules
- Warranty terms
- Whether prices include VAT
- Whether sales are Kosovo-only or international
- Selected bank or payment service provider
- Gateway technical documentation
- Test merchant credentials
- Production merchant credentials

## Payment Provider Requirements

For Kosovo, the practical route is usually a local bank virtual POS / ecommerce payment gateway or a PSP that supports Kosovo merchants.

Ask the provider for:

- Merchant ID
- API key
- Secret/signature key
- Test environment URL
- Production environment URL
- Payment initiation endpoint
- Callback/webhook endpoint format
- Success URL requirements
- Fail URL requirements
- Cancel URL requirements
- Signature verification documentation
- 3D Secure support
- EUR support
- Settlement bank account requirements

Important: Eco Buka should not process or store card numbers directly. Use hosted payment page or tokenized payment flow from the bank/PSP.

## Required Database Tables

Add Laravel migrations/models for:

- customers
- addresses
- carts or session-based cart
- cart_items
- orders
- order_items
- payments
- payment_events
- shipping_methods
- refunds
- tax_rates if VAT/tax logic is required
- coupons later if discounts are needed

## Suggested Order Statuses

- draft
- pending_payment
- paid
- payment_failed
- cancelled
- processing
- shipped
- delivered
- refunded
- partially_refunded

## Required Frontend Features

Vue storefront needs:

- Add to cart
- Cart page
- Quantity update
- Remove from cart
- Cart totals
- Checkout page
- Customer details form
- Billing address
- Shipping address
- Delivery method selection
- Payment method section
- Order review before payment
- Place order button
- Redirect to hosted payment page
- Success page
- Failed payment page
- Cancelled payment page
- Mobile-friendly checkout

## Required Backend Features

Laravel backend needs:

- Cart/order APIs
- Server-side price calculation
- Server-side stock validation if stock is added
- Order creation with pending_payment status
- Payment record creation
- Payment initiation service
- Payment callback/webhook endpoint
- Gateway signature verification
- Payment status reconciliation
- Order confirmation email
- Admin order list
- Admin order detail
- Admin fulfillment status update
- Payment event logs
- Optional CSV export

## Secure Payment Flow

1. Customer adds products to cart.
2. Customer opens checkout.
3. Backend validates cart and recalculates totals.
4. Backend creates order with status pending_payment.
5. Backend creates payment record.
6. Backend redirects customer to bank/PSP hosted payment page.
7. Customer pays with Visa/Mastercard.
8. Bank/PSP redirects customer to success/fail/cancel URL.
9. Bank/PSP sends server-to-server callback/webhook.
10. Backend verifies callback signature.
11. Backend marks payment as paid only if verification passes.
12. Backend updates order status to paid/processing.
13. Customer receives confirmation email.
14. Admin sees order and payment status in CMS.

## Security Requirements

- Never store card numbers.
- Never store CVV.
- Never trust totals sent from frontend.
- Use HTTPS everywhere.
- Use hosted checkout/tokenization.
- Verify gateway signatures.
- Log payment events.
- Add rate limiting to checkout/payment endpoints.
- Keep APP_DEBUG=false in production.
- Protect admin with strong credentials.
- Restrict error display in production.
- Keep database backups.
- Keep uploaded files served safely.
- Validate all checkout inputs server-side.

## Estimated Development Time

Minimal bank-ready foundation:

- 7 to 10 working days

Full test-mode payment integration:

- 2 to 3 weeks

Production-ready ecommerce payment flow:

- 3 to 4 weeks

The timeline depends heavily on how quickly the bank or PSP provides documentation and test credentials.

## Recommended Phases

Phase 1:

- Cart
- Checkout
- Orders
- Order admin
- Legal pages
- Email confirmation

Phase 2:

- Payment gateway test integration
- Callback/webhook
- Payment event logs
- Success/failure/cancel pages

Phase 3:

- Production credentials
- Real payment testing
- Security hardening
- QA
- Bank approval checklist
- Live launch

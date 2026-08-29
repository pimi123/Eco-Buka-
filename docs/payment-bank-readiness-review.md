# Eco Buka Payment And Bank Readiness Review

Date: 2026-08-12

## Review Summary

The roadmap is good and realistic, but it is much bigger than simply adding a payment method. The core message is correct: Eco Buka should first become a secure, reliable, production-ready ecommerce website before adding online card payments.

The project does not need to be locked to Raiffeisen right now. The same roadmap can support Raiffeisen or another proper bank/payment provider later if the architecture is clean.

## Already Done

These items are already implemented or mostly implemented in the current project:

- Vue storefront
- Laravel CMS/API
- Product catalogue
- Categories
- Product detail pages
- Dynamic homepage sections
- File/image/video uploads
- Product gallery management
- Main image management
- Cart
- Manual checkout
- Order records
- Admin order management
- Basic admin login
- Basic validation
- Basic API throttling for orders
- CSRF protection for CMS forms
- SEO titles, meta descriptions, and canonical URLs
- Responsive structure
- HTTPS setup work on live
- API and database work locally when XAMPP/MySQL is running

## Partially Done

These areas exist, but need strengthening:

- Admin security
- Upload security
- Order states
- Checkout validation
- Production server hardening
- Legal/footer pages
- Email communication
- Backups
- Monitoring
- Performance optimization
- Evidence/documentation for bank review

## Not Needed Immediately

These should not be started yet:

- Raiffeisen-specific payment integration
- Payment callback/webhook system
- Refund module
- Payment event table
- PCI evidence package
- ASV scan
- Live card payment button
- Production payment test
- Bank sandbox implementation

Those come later, after the website is operationally ready.

## Critical Before Launch

These should come before real customers use the site seriously:

1. Company information page/section
2. Terms and Conditions
3. Privacy Policy
4. Delivery Policy
5. Return/Refund Policy
6. Warranty/Complaints Policy
7. Better contact/support details
8. Email notification when an order is placed
9. Server backup plan
10. Production `.env` security check
11. Admin password/MFA improvement
12. Remove or clean test products and test categories
13. Confirm API uses live DB data in production
14. Avoid misleading production fallback/demo commerce data
15. Confirm uploads/storage work on live
16. Basic monitoring for server/API errors

## Critical Before Payments

These are for a later payment phase:

1. Separate order status, payment status, and fulfillment status
2. Server-authoritative totals
3. Delivery fee rules
4. Inventory/stock reservation
5. Idempotency key for checkout
6. Payment provider hosted redirect
7. Callback verification
8. Payment audit logs
9. Refund flow
10. Invoice/fiscal process
11. Bank evidence package

## Suggested Step-By-Step Order

Do not start with payments.

Recommended order:

1. Clean production content and test data.
2. Add all legal/business pages.
3. Improve checkout confirmation and policy acceptance.
4. Add order emails.
5. Add delivery rules.
6. Add inventory/stock fields.
7. Harden admin/security.
8. Add backups and monitoring.
9. Prepare payment architecture.
10. Choose and integrate the bank/payment provider.

## Conclusion

The roadmap is useful, but it should be treated as a long-term checklist. The immediate goal is not to add card payments. The immediate goal is to make Eco Buka secure, reliable, professional, and production-ready.

Payments should be implemented only after the order system, legal content, server configuration, admin security, backup strategy, monitoring, and operational workflows are ready.

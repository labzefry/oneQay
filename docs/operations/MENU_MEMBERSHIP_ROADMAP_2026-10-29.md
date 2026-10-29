# Menu book, table ordering and loyalty: implementation contract

Author by Lab | zefry. Status: planned; not implemented or deployed.

Food and beverage tenants need a separately classified menu book sourced from their existing products. Menu categories are independent of inventory categories. Owners control product visibility, cover, template and selected spotlight photographs. Existing inventory and prices remain canonical; a menu does not create duplicate products. Other business types retain their separate workflows.

A public menu QR resolves only the published tenant menu. Table QR leads to a table-specific order flow with server-bound tenant/outlet/table, current published availability and server-owned prices. Customer submission creates an unpaid order; only an authorized cashier can collect and finalize payment. Repeated requests must be idempotent and table/order input must be validated. Public visitors must not obtain business administration or unrelated tenant access.

Discount policies require owner authorization, explicit applicability, effective dates, limits, stacking rules and integer monetary arithmetic. The cashier checkout must recalculate eligibility on the server. Offline operation must not grant a promotion or points twice when replaying events; unresolved or expired policies must fail safely. Refund and cancellation must reverse or compensate loyalty effects without changing historical receipts.

Membership is tenant-scoped and based on canonical customer records. Points use an immutable ledger with explicit accrual, redemption, expiry, correction and reversal, and atomic concurrency control. Reward inventory, allowed discount bounds, arithmetic precision, rounding and financial audit must be defined and tested. Do not equate membership with marketing consent. Marketing broadcasts only to eligible customers who explicitly consented, with unsubscribe and per-recipient delivery states, existing SMTP suppression and no cross-tenant access.

Use the existing seven-color palette and Iconify; colored icons may use palette colors. Maintain readable contrast, proportional typography, responsive design and accessible progress/error states. Required testing includes tenant isolation, duplicate/replay/concurrency, price tampering, cancellation/refund, QR misuse, consent/unsubscribe, email failures, offline conflicts, MySQL and SQLite migration/rollback, and public/cashier integration.

Implementation sequence: (1) catalog/menu schema, owner configuration, published menu and QR; (2) table-order handoff and cashier settlement; (3) discounts integrated with checkout; (4) customer membership ledger and rewards; (5) consent-based marketing integration; (6) supported Windows-client update and end-to-end deployment. Each phase must be independently reviewed and truthfully reported; UI elements cannot be advertised as working before the underlying workflow is complete.

Security work is a parallel release track: determine exact installed PHP/Node/Python versions and lockfile advisories, patch vulnerable dependencies where compatible, and test certified web and desktop releases. CI has reported a medium symfony/mime advisory affecting the locked package; consult the current advisory and resolve version discrepancies before changing the lockfile. Repository scans do not establish hosting runtime patch level. Avoid disabling security checks or changing server runtimes without compatibility validation and rollback.

## Visual reference received 29 October 2026

The owner supplied https://billionscoffee.co.id/menu as the menu-book reference. Its public page identifies the menu experience as “Flip Menu”; use a browsable page-turning menu as inspiration, with accessible previous/next controls and a readable mobile alternative. The reference page alone does not establish all its interactions or permission to copy its images, text, branding, or design assets. Use oneQay branding and owner-authorized product photos. This is a requirement, not an implemented feature.

## Initial source implementation checkpoint

The draft introduces an additive menu category and per-product classification schema plus an authenticated catalog service. Categories and assignments are scoped by verified tenant, organization and outlet; inventory categories, canonical product prices and stock are not changed. Assignment has optimistic version checking and command idempotency. Visibility and spotlight flags are separate. SQLite synthetic integration passed 293 combined assertions, including preview ordering of menu dependencies before product reset; MySQL CI must pass independently. The service is not yet exposed through a page or HTTP API and nothing has been migrated or deployed. Public menu/QR, owner UI, promotions, loyalty ledger, POS integration and Windows update remain outstanding. Local integer loyalty arithmetic is likewise not connected to checkout.

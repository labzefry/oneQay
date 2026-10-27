# Industry workspace reference — 27 October 2026

Owner supplied 20 single-page image PDFs: dashboard, catalog, stock receipts, customer and supplier, completed sales, purchases, finance, product reports, staff, attendance, POS settings and store, rounding, tax, shipping, currency, invoice, email and stock settings. Reference has a blue top header, hierarchical navigation and dense operational tables. Treat third-party visuals as functional reference, preserve oneQay identity and the seven approved palette colors.

## Progress
- Shared service workspace identifies laundry, boarding and homestay by type through distinct approved-palette header treatments and text/icon labels. The same component serves web and Windows.
- Desktop POS binds its industry identifier to the authenticated server snapshot and uses a palette-only rail treatment. This is presentation, not tenant authorization.

## Remaining — not completed
- Catalogue every actionable setting and workflow in reference and compare with live oneQay capabilities; build missing backend, authorization, tenant isolation, persistence, web UI, exports, and functional tests rather than decorative or dead menu links.
- Develop tailored laundry, boarding and homestay workflows without mixing records; continue known gaps including deposits, refunds, housekeeping and specialist reports where appropriate.
- Build reference-style grouped navigation/header for business web and contextual desktop POS, with responsive accessibility and Iconify, without expanding owner-only controls to cashier roles.
- Produce signed Windows patch via the existing updater identity/key after validation; avoid uninstall and preserve offline queues.
- Run MySQL/SQLite, web/Windows builds, role/tenant isolation, offline and migration tests; prepare safe cPanel release/rollback. Do not claim full parity, real transactions, SMTP or hardware acceptance before verification.
- cPanel access is requested only when repository changes are ready for deployment. The existing deployed oneqaydev source is 275e1b293eea; stable Windows is 0.2.4, candidate 0.2.5 remains unpublished.

## Continuation: service navigation and access
The merchant shell now accepts the server-authorized service-workspace navigation response when returning from a settings page, while checking tenant, organization and outlet against any existing scope. Commerce overview retains its device scope check. Owner security links follow the server eligibility flag. Service types receive distinct palette-only sidebar accents. Frontend checks/build must pass before merge; this is not a claim that the remaining feature inventory is complete.

## Continuation: role-scoped reference navigation
Merchant navigation groups existing server-authorized destinations into product/inventory, sales/cashier, purchasing, customers/people, finance, reports and settings. Unknown destinations remain visible in an other group instead of being silently lost. Business type is labeled in the header context and the service link marks the current page. Grouping changes presentation only: route and action permissions remain enforced on the server. Web type-check, palette guard and production build passed locally. Full reference features and end-to-end acceptance remain open.

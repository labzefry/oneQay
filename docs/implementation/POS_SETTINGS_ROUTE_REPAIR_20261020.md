# Cashier settings routing repair — 20 October 2026

Author by Lab | zefry

The owner reported that `/pos/business/desktop` rendered an empty generic business table rather than the cashier settings page. The generic route was registered before the dedicated DesktopPosServiceProvider route and accepted `desktop` as its module.

PR #969 restricts that generic GET route to the six actual generic-table modules. The real Laravel matcher regression passes 38 assertions across both provider registration orders, retaining session/context middleware, all four desktop settings actions and the other business pages. Source: `c52bb6095ed0f95de4fd19632dbab4966f9b2abb`; certification run `39362719306` must succeed before hosting execution.

The code-only cPanel successor is bound to the actual deployed `5f2acf1aec6c` runtime, bridge, routing, assets and 6,384-file source inventory. It verifies exactly 35 migration receipts and 84 existing tables, and contains no migration execution path. Existing account credentials, role assignments, cashier sessions, operations, business preferences and SMTP configuration are preserved. The runtime gate additionally matches the actual cashier settings route to DesktopEnterpriseController before switching public routing.

Use the existing native owner grant, private rollback, fixed public bridge, shared retention lock and verified ZIP policy. After success replace the every-minute worker cron and remove the temporary update cron. A new owner grant must bind the exact code-only request; prior release grants are not reusable. Record actual host evidence after execution; this document alone does not establish deployment or authenticated acceptance.

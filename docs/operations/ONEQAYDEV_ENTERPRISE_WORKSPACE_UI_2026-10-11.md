# oneQayDev enterprise workspace presentation

Author by Lab | zefry

## Trigger and scope

The owner reported successful merchant sign-in and requested a more complete production interface on 11 October 2026. The current active release is `production-192c8a3f7190`, source `192c8a3f719006ecdc5f8a4216d09c285b747e59`, at `https://oneqaydev.n07.my.id`. Native activation grant `6108227185` and host result SHA256 `ae75314557a4aa2b885bdbbf5df56f04837a690a72385e99c9f08dad778e2356` establish `BUSINESS_ACTIVATED_ACCEPTANCE_PENDING`; the grant execution window does not automatically deactivate a successfully activated business. The owner's sign-in report is not independent proof of MFA/TOTP, CASH sale, shift close, receipt, reconciliation, or reporting acceptance.

This successor changes eight application presentation paths under `apps/web/resources`: the stylesheet and app entry, a shared merchant shell, a local icon component plus bundled icon data/license, the operations home, and cashier. No controller, route, permission, session middleware, monetary computation, transaction transport, dependency lock, runtime environment, database, migration, merchant credential, or provisioning code changes.

## Resulting behavior

Authenticated `Pos/*` pages receive a consistent application sidebar and top bar. Destinations come from the current authorized overview; deep pages use a bounded same-origin read-only request with the Inertia version and current scope, rejecting stale/redirected/denied responses. The drawer supports Escape, keyboard focus containment, focus return, and inert background content. A pending cart or submission prompts before navigation.

The operations home presents actual catalog, sellable-product, shift, and opening-cash readiness and the server-recommended next task. Missing readiness remains unverified. There are no invented financial metrics, product images, SKU, outlet names, or permissions. The cashier product grid and cart remain backed by the existing catalog and receipt data. The financial and transport script beginning at `type CatalogItem` is byte-for-byte preserved (SHA256 `ebd59097f9d1a5a2923d8d840c8de87125461d0d32539ab88f85c769851867fd`). Lucide icons are bundled locally with their ISC license.

## Verification and delivery boundary

The PR runs the existing source contracts, application regressions, dependency audits, TypeScript checking, and production build. Its compiled preview artifact supports local desktop/mobile and cashier interaction verification without production transactions. Exact bounded historical source-envelope successors admit only these eight application presentation files and this PR's explicit workflow/documentation set; all functional regression steps remain enabled.

A separate certified business release is required after merge. Deployment must retain current business-on environment, merchant, MFA, credentials, database, sessions, untouched protected staging, and a rollback to the currently working business-on release. This change does not claim deployment or business acceptance before actual owner-host evidence.

The companion certified-workspace update operator, isolated boundary/rollback fixture and handoff are under `tools/production` and `docs/operations/ONEQAYDEV_WORKSPACE_UPDATE_HANDOFF.md`. They install a separate certified release, preserve business-on rollback and session continuity, and never modify backend, schema, merchant or staging.

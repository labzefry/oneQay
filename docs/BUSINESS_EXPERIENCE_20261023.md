# Business experience implementation checkpoint — 2026-10-23

Status: implementation branch, NOT production certified or deployed. Base main: 90a9b298c3b44a4cfa7700a23fc78d1eec0655b2. Installed production remains d63cf4dd44af. Windows stable remains 0.2.4; 0.2.5 is a candidate only.

## Scope implemented
- Tenant subdomain claim, availability and suggestions, immutable business-facing address, owner-only versioned rename, retired-name quarantine, notification and session revocation.
- Host-bound, encrypted 45-second single-use session handoff. Host-only cookies required. Domain routing is disabled by default until wildcard DNS, TLS and cPanel routing are verified.
- Exact active business-owner optional TOTP policy, password verification, enrollment/confirmation/disable, factor epoch and session/recovery revocation. Platform-owner TOTP remains mandatory.
- Separate laundry / boarding / homestay resource, order, invoice, payment and shift tables and state transitions; scoped tenant, organization, outlet and device access. Existing commerce remains separate.
- One shared web/Windows service workspace, native durable FIFO per-cashier outbox, server-derived business type. Rates and resource administration remain web-only.
- Platform reviewed raster logo upload/versioning, shared branding; account logout/switch; bilingual industry homepage defaults.

## Validation so far
- PHP business-experience.php: 154 assertions, includes the 108 existing simplified onboarding assertions.
- Web and Windows Vue type checks and production frontend builds passed.
- Rust POS core existing 23 tests passed. Native Windows compilation and installed-device acceptance still required.

## Release gates and known remaining work
- Extend session handoff replay/wrong-host/expiry, TOTP re-enable/confirm and cross-tenant tests. Validate MySQL locking and native Windows build in CI.
- Complete explicit service outbox conflict review and uncertain web request recovery; never delete an unconfirmed financial event or generate a replacement ID silently.
- Browser visual/accessibility checks, final homepage published-content migration strategy (never overwrite a customized published page), and service report printing acceptance.
- Additive migrations add 19 tables (platform 4 + vertical 15): target 108 tables / 41 receipts from production 89 / 39. A fresh certified deployment operator must apply and verify schema; old source-only updater is insufficient.
- Wildcard *.oneqaydev.n07.my.id DNS/TLS/document-root mapping is NOT verified. Keep ONEQAY_BUSINESS_DOMAINS_ENABLED=false until complete; choosing a slug alone does not provision DNS.
- Service modules currently implement core intake/reservation/billing/cash lifecycles; deposits, refunds, housekeeping and full specialist reporting are not certified.
- Never claim SMTP delivery or real cash pilot acceptance without actual evidence. Do not rotate the working Windows stable-v2 signing key.

## Design contract
Only #1B1B39, #3965FA, #99B7FC, #E9EBFF, #FFFFFF, #EDEDED, #202020 and alpha variants. Body 16px, readable controls, visible focus, restrained morph surfaces, reduced-motion support. Iconify bundled icons. Copyright oneQay in UI; Author by Lab | zefry in source only.

## Continuation
Work in this branch; inspect this checkpoint and git status before repeating work. Add meaningful validation results here. Do not merge or publish candidate 0.2.5 until CI and blocking queue/security issues are resolved. Never reset existing business data during upgrade.

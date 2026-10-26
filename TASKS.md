## 2026-10-26 — Additive deployment operator qualification

PR #987 head ce441a68b5fadbee4be7736c110f395ae170562b completed 163 GitHub checks: 161 successful and 2 skipped. A dedicated successor operator now binds installed d63cf4dd44af, verifies the exact two additional module migrations, preserves all 89 historical table definitions and 39 migration receipts, and expects 108 tables / 41 receipts. Private SQL backup precedes migration; partial DDL stops for forward repair; failed public probes restore prior routing/assets without dropping new tables. Healthy-worker retention uses a verified private ZIP before expanded predecessor removal.

Local SQLite operator rehearsal passed 91 assertions, including source tampering, partial migration refusal, completed replay, backup, atomic routing recovery and archive verification. PHP lint and Python compilation passed. MySQL CI on the new head remains required. No production mutation, reset, wildcard activation, merge, or stable Windows publication has occurred. Independent review and exact-head lifecycle evidence remain required by AI_CONSTITUTION.md. New owner authorization to complete work was received in chat; this is not represented as an independent review.

## 2026-10-26 — PR #987 continuation (not deployed)

Business experience work resumes from `docs/BUSINESS_EXPERIENCE_20261023.md`. The remaining MySQL failure used a tenant identifier in the 32-character business audit reference. Cancellation now resolves the canonical business ID; replay coverage checks a single correctly bound audit record. Final CI and additive deployment qualification remain required. No production data was reset.

## 2026-10-22 — Actual UI hosting and blank-page/worker recovery

Author by Lab | zefry. Host receipt proves `production-7e4197b44322` (PR #978) deployed00:23:07WIB, 6,398 files, 38 receipts/88 tables, no migration. Blank homepage was public build0750 causing certified JS/CSS404; bounded build mode recovery restored rendering with all bytes unchanged. Worker proc_open failure was replaced with source-bound Laravel Kernel::call + BufferedOutput, atomic private JSON and no external process. First recovered sample01:17:04WIB: failed=false, mail/health/campaign completed, queue0. [Actual evidence, cron and continuation](docs/operations/ONEQAYDEV_UI_RECOVERY_2026-10-22.md). Original receipt/runtime/accounts/schema retained; SMTP inbox, cash/offline and full UI acceptance remain unverified. This supersedes older not-deployed/7ef-only wording for oneQayDev, not staging.

## 2026-10-21 — Cashier/shift and owner operation feedback expansion (branch only)

Author by Lab | zefry. PR #978 draft adds bounded POS checkout with single immutable sale operation ID and no blind retry, shift cash/open/close timeout handling, queued-versus-confirmed responses, bounded RBAC and business commands, owner SMTP/DNS/report/MFA request ceilings, report download progress, and dismissible localized feedback. Seven-color styling was also extended across cashier, shifts, owner login, business workspace and public website. Business PHP, DB schema/migrations, payment semantics, stock tracked/unlimited, SMTP backend and offline queues are unchanged. The change is **not deployed**; complete CI, approval, missing UI surfaces and certified successor operator remain required. [Branch checkpoint](docs/operations/ONEQAY_UI_FOUNDATION_CHECKPOINT_2026-10-21.md).

**UI source coverage follow-on:** Seven-color and proportional typography styling now also touches cashier, open/close shift, business management/access, public registration, platform login, and shared website CSS; the source-level palette guard enumerates these migrated stylesheets. No claim of full app accessibility, client-operation completeness or native Windows coverage; new exact-head CI and interactive review still required. Hosting release remains unchanged.

## 2026-10-21 — UI enterprise foundation source milestone (NOT deployed)

**UI foundation follow-on:** Platform Control/SMTP styling and owner operation feedback extended on PR #978 branch; palette guard includes both scoped style sections. Updated head needs its own checks and review. Hosting and schema remain unchanged. [Checkpoint](docs/operations/ONEQAY_UI_FOUNDATION_CHECKPOINT_2026-10-21.md).

Author by Lab | zefry. Branch `agent/ui-enterprise-palette-feedback-20261021` implements the initial scoped palette/morph/typography and operation-feedback primitives from [owner brief](docs/design/ONEQAY_UI_MODERNIZATION_2026-10-21.md). It updates shared merchant CSS, LocalizedShell, Business Settings/Products, and POS desktop configuration, with a palette guard and composable-state synthetic checks wired to the existing integrated-business CI. It does **not** claim full Website/Platform/POS/Windows coverage, successful browser QA, merge, or cPanel deployment. Existing host remains `production-7efdda7648c2` with 38 receipts/88 tables. No migrations/schema/tenant grants/finance/stock functions are changed. See [incremental source checkpoint](docs/operations/ONEQAY_UI_FOUNDATION_CHECKPOINT_2026-10-21.md).

## 2026-10-21 — Actual production deployment and next UI/UX handoff

Author by Lab | zefry. Application PR #975 (`7efdda7648c2`) is actually deployed on oneqaydev.n07.my.id at 18:17:29 WIB: `ON_PRESERVED`, native production pass, 6,395 files, 38 migration receipts / 88 tables. Private backup verified; new worker healthy with zero queued mail in the observed sample; old expanded release/payload archived as verified private ZIPs. SMTP inbox delivery, real cash/offline pilot and signed Windows patch promotion remain unverified/separate. [Actual checkpoint and evidence](docs/operations/ONEQAYDEV_DEPLOYED_CHECKPOINT_2026-10-21.md) supersede historical pre-deployment wording below for this target only.

Next owner-requested scope is [modern enterprise UI/UX with exact seven-color palette, restrained morph effects, proportional type and resilient operation progress](docs/design/ONEQAY_UI_MODERNIZATION_2026-10-21.md). [Palette contract](docs/design/ONEQAY_UI_PALETTE_2026-10-21.json) is recorded; implementation is PENDING. [Ordinary-chat continuation prompt](docs/operations/MASTER_CONTINUATION_PROMPT_ONEQAY_2026-10-21.md) uses owner-operated cPanel File Manager/PHP cron without Cloud Browser. Documentation handoff changes no application, schema, runtime, permissions or deployment gates.

## 2026-10-14 — Catalog, reporting downloads and persistent currency successor

Author by Lab | zefry. Target: oneqaydev.n07.my.id; predecessor application f5a037ce60aa05c61e0b9c197f2e171c4d01fa8c, host update-result SHA256 73f0176fb4e07713ffe6aa619e03964ef5d351db44961ede2286bab9cc9a7cf3.

- Delivered source: reference-driven blue header and white grouped sidebar; product catalog/search/category/brand/variant/SKU/barcode/price fields; guarded CSV/XLSX import preview (500 rows) and all-or-nothing canonical catalog/opening-stock commit; PDF and real XLSX downloads across filtered pages (10,000-row bound); persistent organization currency/symbol/name settings; exact integer decimal formatting across POS and business views; dated sales trend from scoped correction-aware POS facts.
- Currency defaults apply to new records. Existing financial/product currency is preserved; no FX conversion or mass relabelling. Wholesale/cost fields are stored catalog metadata, not an automatic checkout discount engine.
- Only one new additive business-module migration creates preferences and product profiles. Historical migrations, merchant identities, credentials, existing stock and records must remain unchanged.
- Local verification: 50 merchandising/SQL/file checks, 73 existing integrated-business checks, and 20 canonical role-administration checks passed on SQLite. Native MySQL and compiled-browser qualification are pending; no hosting deployment of this successor is claimed yet.
- Olsera is a visual/navigation reference. External marketplace/GoFood services, payroll/tax automation, loyalty redemption, coupon pricing, and commission settlement are not delivered merely by adding a menu. Existing owner SMTP/worker setup and authenticated host acceptance remain pending.

# oneQay Tasks

## 2026-10-14 — Host receipt baseline and prepared-candidate recovery

Actual oneQayDev host evidence contains canonical historical migrations 1–27 plus previously installed inventory replenishment #28. The first f5a037 module attempt stopped at EXACT_HISTORICAL_RECEIPTS_REQUIRED before new module DDL or public switch. The bounded successor validates exact receipt names and existing replenishment schema, preserves #28 and installs only pending business/platform migrations. Recovery is restricted to the hash-bound observed stop, unchanged predecessor/backup, certified candidate/runtime and absence of any module-installation checkpoint. Qualification and later host execution must be recorded separately; no successful deploy is asserted by this entry.

Author by Lab | zefry

## 2026-10-12 — Business OS delivery and real-host gate
- [x] Merge PR #949 Business Command Center into `main=d15f2fc3f9aa9320670657d03b044e132bf812a2`.
- [x] Certify source release `production-d15f2fc3f9aa`, run `38236323278` SUCCESS; artifact `11741255632`.
- [x] Capture source-backed E0–E5 capability matrix in `docs/operations/ONEQAY_ENTERPRISE_CAPABILITY_MATRIX_2026-10-12.md`.
- [x] Deliver no-secret/no-write oneQayDev read-only host-binding inspector with source regression.
- [ ] Obtain owner-host read-only inspection evidence and inspect current live release; last user-reported host source is `e93b7fef42cb`, still acceptance pending.
- [ ] Build and qualify backend-capable exact-source d15f successor update operator and rollback; previous bilingual operator must NOT be reused.
- [ ] Deploy only with fresh applicable host owner authority, then test real authorized CASH shift/sale/idempotency/receipt/stock/closing and deny cases.
- [ ] Implement remaining E2–E5 complete business workflows, not UI placeholders, in integrated vertical slices.


## 2026-10-12 — Indonesian and English workspace

- Implement an Indonesian default with a persistent English option on merchant entry, MFA/recovery, all POS workspaces, reports, preview screens, and system status.
- Keep the chosen language reactive without reloading, preserve current forms and carts, and keep product names, identifiers, credentials, and API enum values unchanged.
- Add a bounded certified successor operator from the successfully recovered `091312accfd0` BUSINESS_ON release. Preserve its exact runtime, merchant, sessions, union assets, protected staging, and rollback.
- Verify compiled bilingual behavior and the existing financial/navigation fixtures before certification and direct cPanel deployment. Deployment is pending actual host evidence; business transaction acceptance remains unverified.


**Canonical main:** `8d343fd90b00390e73169c9def4d13e1cc24f2fe`

## Current merchant production and enterprise workspace

- [x] Production release `192c8a3f7190` activated on oneqaydev; host result SHA256 `ae75314557a4aa2b885bdbbf5df56f04837a690a72385e99c9f08dad778e2356` qualifies the native runtime gate, sessions, cookie security, merchant preservation, and isolated routing.
- [x] Owner reports successful sign-in on 11 October 2026. This is owner-reported, not independently verified MFA/TOTP or CASH business acceptance.
- [x] Implement shared merchant navigation, operational overview, and cashier presentation improvements using existing data and permissions.
- [x] Qualify 19 compiled desktop/mobile presentation and transaction interaction scenarios; certify immutable source `091312accfd0` through main run `38139769412`.
- [ ] Apply the certified presentation update with current merchant, sessions, configuration, data, and business-on rollback preserved. The first attempt stopped before public switch; exact disabled-symlink recovery is prepared and locally verified, pending owner-host execution.

## Completed and do not replay

- [x] Durable target selected: `oneqay-durable-staging-01`.
- [x] Durable merchant bootstrap completed.
- [x] Migration #27 executed and verified.
- [x] `pos.shift.close` provisioned with `default_grant = NONE`.
- [x] Final Shift Close runtime activation completed.
- [x] Current runtime source `505518f79e8a70f789b94f5074a040eae785aeb0` deployed to durable staging.
- [x] Post-activation producer run `36407450933` completed successfully.
- [x] Trusted ingestion run `36413265200` attempt 1 completed successfully.
- [x] Selected-target generation qualification run `36414746533` completed successfully.
- [x] PR #922 squash merged as canonical main `8d343fd90b00390e73169c9def4d13e1cc24f2fe`.
- [x] Selected target generation reconciled to source `505518f...`, artifact `5b3030d...`, readiness `069a27b...`.
- [x] Same-source Production candidate published by run `36380024637`.
- [x] Same-source Production dark-deployment operator kit published by run `36380081412`.

Do not repeat merchant bootstrap, migration #27, permission provisioning, feature activation, staging deployment, post-activation producer, trusted ingestion, or same-target generation reconciliation for this generation.

## Current Production-readiness work

- [ ] Materialize exact Production target input for the real isolated Production environment.
- [ ] Keep Production private bindings outside the public document root with owner-only permissions.
- [ ] Run Production target qualification only; do not deploy during preflight.
- [ ] Confirm whether the real Production host satisfies the current symlink-backed active-release/document-root contract.
- [ ] If the host uses the same no-symlink/fixed-public-root constraints observed on durable staging, implement and qualify a Production `FIXED_PUBLIC_BRIDGE` successor before any deployment authority request.
- [ ] Only after a qualified target exists, prepare the exact Production deployment-authority request.
- [ ] Obtain separate short-lived operational authority before any dark deployment.
- [ ] Cap dark deployment at `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.
- [ ] Do not activate Production business traffic without a separate later authority and business-runtime readiness gate.

## Exact Production source material

- Production runtime source: `505518f79e8a70f789b94f5074a040eae785aeb0`.
- Production release ID: `production-505518f79e8a`.
- Production archive SHA-256: `906fb0dd630fc1de95e23a5505999567e2f287b53da8cb8ec346f9d44e311695`.
- Production candidate artifact ID: `10952267537`.
- Production operator-kit artifact ID: `10952223291`.
- Durable-staging prerequisite artifact SHA-256: `5b3030d5154e3b0938a944c5b6b0d33218078f37d8f066a4e9cc4067ff285451`.

## Operational boundary

- Production deployment authority: `NOT_GRANTED`.
- Production traffic: `NOT_AUTHORIZED`.
- Technical Preview: `NOT_AUTHORIZED`.
- updater: `INACTIVE`.
- no migration #27 replay.
- no permission reprovisioning.
- no Final Shift Close reactivation.
- no target reselection.

Author by Lab | zefry

## Public website / CMS / onboarding — 2026-10-15

Implement and qualify website pages, visual editing, preview/publication, registration and owner isolation. Deployment requires a newly certified cPanel successor from ec48e3fffdbb; do not reuse the catalog package.


## Public website cPanel deployment — 2026-10-15

Author by Lab | zefry. Exact source 14e1cdb9f11f9b1a6384aa1659dab86cac5e1078 successor over evidenced ec48e3 host: preserve 31 receipts, add one website migration/four tables, preserve all credentials, roles and SMTP, verify public and owner routes separately, retain BUSINESS_ON rollback. See docs/operations/ONEQAY_PUBLIC_WEBSITE_DEPLOYMENT_2026-10-15.md. Host execution remains pending.

## 2026-10-21 — Mail delivery, category creation and stock policy

Stage 1 source changes: independent SMTP/NOC worker phases; live owner delivery reports and safe SMTP error guidance; automatic 72-hour email log retention protecting active queues/dedupe; tenant/outlet category creation; explicit tracked/unlimited stock through canonical sale/void, web and offline desktop 0.2.1. Two additive module migrations require a separately qualified deployment. Hosting remains c2e (PR #974); deployment is deferred per Product Owner. [Implementation, schema, compatibility and validation](docs/implementation/MAIL_CATALOG_STOCK_20261021.md). Author by Lab | zefry.


## 2026-10-21 — SMTP/category/stock hosting successor

Stage 2 authorized after cPanel login. Exact application source 7efdda7648c2; guarded 36/87 to 38/88 migration with private database backup, rollback protection and ZIP retention. Continue from `docs/implementation/MAIL_STOCK_DEPLOY_20261021.md` and actual deployment evidence on PR #975.

## POS sync recovery and per-business data reset — 2026-10-22

Owner-requested Windows queue recovery, item-level control-plane diagnostics and explicit per-business operational reset are implemented. Source/hosting/installed-Windows acceptance remain distinct. See [checkpoint](docs/operations/POS_SYNC_AND_BUSINESS_DATA_RESET_2026-10-22.md) for actual evidence, reset scope, private backup storage, and required additive deployment. Production data has not been reset.

## 2026-10-23 — POS account flow successor (PR #984)

- [x] Implement cashier switching with isolated durable profiles and nonblocking shift guidance.
- [x] Implement reconnect sync, explicit reused-email errors, existing-owner POS enablement and safe activation navigation.
- [x] Add branded multipart email and login-screen signed-patch controls with consistent pending-data backup.
- [x] Local SQLite account integration (108 assertions), Rust offline core (23 tests), web/POS builds, palette guard and desktop UI interaction checks passed.
- [ ] Complete exact-head CI, MySQL and native Windows checks; review/merge evidence.
- [ ] Publish compatible signed Windows update, install certified web successor, verify actual user-device sync.

Details and limitations: [checkpoint](docs/operations/POS_ACCOUNT_FLOW_UPDATE_2026-10-23.md). Author by Lab | zefry.

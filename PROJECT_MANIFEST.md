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

> **CURRENT 2026-10-12 ONEQAY BUSINESS OS CHECKPOINT:** Main `d15f2fc3f9aa9320670657d03b044e132bf812a2` (PR #949), source-certified release `production-d15f2fc3f9aa` (Actions run `38236323278`, SUCCESS). Last owner-reported live oneQayDev release `production-e93b7fef42cb` is `BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING`; Business OS d15f host deployment and real CASH/Final Shift Close acceptance have NOT been proven. See [E0–E5 capability inventory](docs/operations/ONEQAY_ENTERPRISE_CAPABILITY_MATRIX_2026-10-12.md) and [host read-only preflight](docs/operations/ONEQAYDEV_BUSINESS_OS_HOST_PREFLIGHT_2026-10-12.md). Older claims below are historical, not current.

## 2026-10-14 — Host receipt baseline and prepared-candidate recovery

Actual oneQayDev host evidence contains canonical historical migrations 1–27 plus previously installed inventory replenishment #28. The first f5a037 module attempt stopped at EXACT_HISTORICAL_RECEIPTS_REQUIRED before new module DDL or public switch. The bounded successor validates exact receipt names and existing replenishment schema, preserves #28 and installs only pending business/platform migrations. Recovery is restricted to the hash-bound observed stop, unchanged predecessor/backup, certified candidate/runtime and absence of any module-installation checkpoint. Qualification and later host execution must be recorded separately; no successful deploy is asserted by this entry.

Author by Lab | zefry

> **Latest verified Production Go-Live handoff (2026-10-11):** [Canonical real-business checkpoint](docs/operations/PRODUCTION_GO_LIVE_CHECKPOINT_2026-10-11.md) · [Master continuation prompt](docs/operations/MASTER_CONTINUATION_PROMPT_ONEQAY_2026-10-11.md) · [oneQayDev cPanel upgrade handoff](docs/operations/ONEQAYDEV_CPANEL_UPGRADE_HANDOFF_2026-10-11.md). PR #938 is MERGED; certified application source `409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`. **Actual cPanel on-host upgrade, merchant provisioning and real CASH transaction are NOT YET VERIFIED.** Older SHA/status text below is historical context. Never rerun migrations or touch occupied staging.

# oneQay Project Manifest

## CURRENT OPERATIONAL CHECKPOINT — 2026-10-08 (supersedes historical values below)

Canonical evidence baseline: post-selected-target PR #930 squash at 5224fdb74a31b590ec34f50f83d6ef78073f7678. Newly qualified deployed durable staging: source a5e833bce4baa22c21a144c558a0ca123f1d0b1e; environment oneqay-durable-staging-01; Final Shift Close ACTIVE. Reattestation run 37773074943 SUCCESS, trusted ingestion 37773352841 SUCCESS, generation run 37774113768 SUCCESS, PR #930 MERGED. Selected-target state SELECTED_NOT_AUTHORIZED; Production deployment/business traffic NOT_AUTHORIZED. Published same-source Production RC run 37761181710/artifact 11542731123 and generic operator kit run 37761262551/artifact 11542027944 remain dark artifacts, NOT host deployment. Customer-facing business go-live target: oneqay.n07.my.id, currently serving staging. **Do not deploy Production to its occupied public root without a qualified cutover/rollback plan or assume dark-deployment equals real POS readiness.**

**Read the current dated authoritative handoff FIRST:** [Production go-live handoff (2026-10-08)](docs/operations/PRODUCTION_GO_LIVE_HANDOFF_2026-10-08.md). The historical snapshot below is retained as past evidence, not current runtime state.

---

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

- canonical main after post-activation selected-target generation reconciliation: `8d343fd90b00390e73169c9def4d13e1cc24f2fe`
- reconciliation PR: #922 — squash merged
- current application/runtime source generation: `505518f79e8a70f789b94f5074a040eae785aeb0`
- Final Shift Close: `ACTIVE`
- migration #27: `EXECUTED` / no replay
- permission `pos.shift.close`: `PROVISIONED`, `default_grant = NONE`
- deployment authority: `NOT_GRANTED`
- Technical Preview: `NOT_AUTHORIZED`
- Production deployment/traffic: `NOT_AUTHORIZED`
- updater: `INACTIVE`

## Selected durable target generation

- environment: `oneqay-durable-staging-01`
- runtime class: `durable-staging`
- selection state: `SELECTED_NOT_AUTHORIZED`
- running source: `505518f79e8a70f789b94f5074a040eae785aeb0`
- running artifact SHA-256: `5b3030d5154e3b0938a944c5b6b0d33218078f37d8f066a4e9cc4067ff285451`
- readiness attestation SHA-256: `069a27b16ff81082bd3d746fe678dc966b8f2a61bb95c35f5ab4c0168f7d347e`
- selection fingerprint SHA-256: `bdce15c5053446119979efe6b9f72572857c06a0caa11d649b8ec54d7239a411`
- trusted ingestion run: `36413265200`, attempt `1`
- ingestion fingerprint SHA-256: `f9c9e205a7670ba4e69cdf6f82c32a17589c79880e048d9452213fd5cdafbd04`

## Post-activation evidence chain

- post-activation durable-runtime producer run: `36407450933` — SUCCESS
- trusted ingestion run: `36413265200` — SUCCESS
- selected-target generation qualification run: `36414746533` — SUCCESS
- selected-target generation PR #922 merge: `8d343fd90b00390e73169c9def4d13e1cc24f2fe`

## Exact-source Production material

Production candidate for source `505518f79e8a70f789b94f5074a040eae785aeb0`:

- publication run: `36380024637`
- Actions artifact ID: `10952267537`
- artifact name: `oneqay-production-505518f79e8a-operator-bundle`
- Production archive SHA-256: `906fb0dd630fc1de95e23a5505999567e2f287b53da8cb8ec346f9d44e311695`
- release ID: `production-505518f79e8a`

Production dark-deployment operator kit:

- publication run: `36380081412`
- Actions artifact ID: `10952223291`
- artifact name: `oneqay-production-operator-kit-505518f79e8a`
- Production traffic activation: `NOT_AUTHORIZED`
- deployment authority: `NOT_GRANTED`

## Next material production-readiness blocker

A real Production target must be qualified before any deployment authority request exists. The current Production adapter supports `CPANEL_CRON_PHP_CLI_NO_SSH`, but its target qualification/execution contract still requires a symlink-backed active-release pointer and a document root exactly under that active release.

The observed durable-staging cPanel target required `FIXED_PUBLIC_BRIDGE` and could not use PHP symlink semantics. That staging observation is not itself a Production target qualification. Production therefore remains fail-closed until the actual Production target proves the current Production contract or a governed Production fixed-public-bridge successor is published.

No Production deployment, Production traffic activation, migration replay, permission reprovisioning, feature reactivation, target reselection, updater activation, or producer dispatch is authorized by this manifest reconciliation.

Author by Lab | zefry

## Public website successor — 2026-10-15

New public website and owner CMS preserve canonical business identity, roles, SMTP ownership and runtime gates. See docs/operations/ONEQAY_PUBLIC_WEBSITE_CMS_2026-10-15.md. Source completion is not host acceptance.


## Public website cPanel deployment — 2026-10-15

Author by Lab | zefry. Exact source 14e1cdb9f11f9b1a6384aa1659dab86cac5e1078 successor over evidenced ec48e3 host: preserve 31 receipts, add one website migration/four tables, preserve all credentials, roles and SMTP, verify public and owner routes separately, retain BUSINESS_ON rollback. See docs/operations/ONEQAY_PUBLIC_WEBSITE_DEPLOYMENT_2026-10-15.md. Host execution remains pending.

## 2026-10-21 — Mail delivery, category creation and stock policy

Stage 1 source changes: independent SMTP/NOC worker phases; live owner delivery reports and safe SMTP error guidance; automatic 72-hour email log retention protecting active queues/dedupe; tenant/outlet category creation; explicit tracked/unlimited stock through canonical sale/void, web and offline desktop 0.2.1. Two additive module migrations require a separately qualified deployment. Hosting remains c2e (PR #974); deployment is deferred per Product Owner. [Implementation, schema, compatibility and validation](docs/implementation/MAIL_CATALOG_STOCK_20261021.md). Author by Lab | zefry.


## 2026-10-21 — SMTP/category/stock hosting successor

Stage 2 authorized after cPanel login. Exact application source 7efdda7648c2; guarded 36/87 to 38/88 migration with private database backup, rollback protection and ZIP retention. Continue from `docs/implementation/MAIL_STOCK_DEPLOY_20261021.md` and actual deployment evidence on PR #975.

## POS sync recovery and per-business data reset — 2026-10-22

Owner-requested Windows queue recovery, item-level control-plane diagnostics and explicit per-business operational reset are implemented. Source/hosting/installed-Windows acceptance remain distinct. See [checkpoint](docs/operations/POS_SYNC_AND_BUSINESS_DATA_RESET_2026-10-22.md) for actual evidence, reset scope, private backup storage, and required additive deployment. Production data has not been reset.

## 2026-10-23 — Cashier and onboarding source successor

Owner-requested scoped implementation is on PR #984: account switching keeps isolated SQLite profiles; owner POS binds existing identity and MFA; normalized duplicate account email is rejected; activation navigates through canonical authentication; HTML/plain-text email; signed patch preparation preserves active shifts and pending receipts. No new schema, production reset or financial-history rewrite. Publication/deployment and installed-device acceptance remain separately evidenced. [Checkpoint](docs/operations/POS_ACCOUNT_FLOW_UPDATE_2026-10-23.md).

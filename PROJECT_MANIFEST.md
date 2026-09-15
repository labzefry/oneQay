# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint168
**Canonical engineering commit:** `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`
**Latest engineering PR:** #754 — `Sprint168: add POS shift history performance workspace`
**Final engineering head:** `05652fc5b11fdad7bb11e9c992f79d6f5436e29d`
**Sprint168 regression:** `34921162121` — successful
**M7.1 Application Regression:** `34921161920` — successful
**Governance Required Checks:** `34921162056` — successful
**PHP Foundation Regression:** `34921162098` — successful
**Engineering envelope:** 12 paths — `d42a5a7d8568766e524ff662107ffdb35452d4f9ac00f26cdc8e419d36bc3c25`
**Canonical reconciliation envelope:** 6 paths — `350fd16d111bb398c34a63e1519cc02206bcc373378a9366768c198ccf8146b5`
**Next position:** Sprint169 bounded discovery from the fully reconciled Sprint168 checkpoint; no objective preselected.

> `d3703a6b18f478acd812e7892c3871ce3aaf7bfa` is the canonical Sprint168 **engineering** evidence. The reconciliation squash must never replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint167 added live exact-device active-shift performance, but operators still lacked a durable historical view of closed outlet shifts across devices. Sprint168 closes that read-only visibility gap by reconciling canonical Final Shift Close evidence with immutable shift-bound sales, full-sale void, and CASH-refund evidence.

## 2. Objective / Gap

`POS_SHIFT_HISTORY_PERFORMANCE_WORKSPACE`.

## 3. What changed

- Added read-only exact tenant + organization + outlet closed-shift history.
- Same-outlet cross-device history is deliberate; newest 50 closed shifts are listed.
- Only persisted shifts with `active_slot IS NULL` and exactly one canonical Final Shift Close evidence row are eligible.
- Optional selected shift defaults to newest eligible closed shift.
- Each shift validates opener, closer, device, open/close/cutoff identity and canonical expected/observed/variance/review arithmetic.
- Selected-shift performance derives immutable completed-sale, full-sale-void, and CASH-refund evidence by tender + currency + scale.
- Active net value equals gross minus full-sale void; CASH refund remains separate and is not subtracted twice.
- Legacy null-shift sales inside the selected shift window fail closed.
- Foreign-scope evidence, outside-window evidence, malformed tender/evidence, inconsistent void/refund evidence, numeric overflow, more than 64 buckets, or missing canonical close evidence fail closed.
- Reused existing deny-by-default `pos.reporting.sales-summary.view`; no new permission or provisioning was introduced.
- Added guarded `GET /pos/reporting/shift-history/{shiftId?}` / `pos.reporting.shift-history` behind default-false `ONEQAY_POS_SHIFT_HISTORY_PERFORMANCE_ENABLED`.
- Delivery requires Local/Test/CI, persistence, operational reporting, exact session controls, and existing `ONEQAY_POS_SHIFT_CLOSE_ENABLED` source/runtime dependency.
- Registered as a bounded child of POS Operations Hub; Hub discovery remains permission + `Route::has()` guarded.
- Added responsive Vue/Inertia read-only UI and focused SQLite regression.
- Left global routes/providers, shared permission registry, schema/migrations, existing shift/sale/cash/stock mutation owners, Final Shift Close provider, deployment, and updater untouched.

## 4. Evidence / Qualification

- Parent canonical post-Sprint167 checkpoint: `418bf77ac9115544577ab1bcba8b4c419c62446d`.
- Engineering PR #754 completed the surfaced exact-head PR-triggered matrix successfully.
- Exact engineering head: `05652fc5b11fdad7bb11e9c992f79d6f5436e29d`.
- Repository-native Product Owner merge authority: successful on the exact engineering head.
- Canonical engineering squash: `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`.
- Parent-to-engineering compare: ahead 1, behind 0, exactly the qualified 12-path engineering envelope.
- Dedicated Sprint168 run `34921162121`: successful.
- M7.1 `34921161920`, Governance `34921162056`, PHP Foundation `34921162098`, POS Sprint156–Sprint167 regressions, and surfaced historical Final Shift Close controls succeeded on the exact engineering head.

## 5. Product progression through Sprint168

The guarded POS operational chain now includes operational sales reporting, cashier sale entry, shift start, sale corrections, immutable sale history/receipt detail, catalog/opening inventory setup, guarded operations navigation, cash-variance reconciliation, positive inventory replenishment, inventory accountability, product-level sales performance, live active-shift performance, and closed-shift historical performance.

Sprint168 is read-only. It does not authorize pricing/margin accounting, customer/discount/payment domains, purchasing/supplier workflows, arbitrary stock adjustment, stocktake mutation, inventory transfer, or any new operational activation authority.

## 6. Operational boundaries / NO-GO

Machine-readable operational authority remains under `ops/final-shift-close/` and is unchanged:

- durable activation target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- real target-bound capability evidence: absent;
- real dependency-envelope evidence: absent;
- producer dispatch: `NOT_PERFORMED`;
- runtime allowlist: Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

## 7. Next position

Begin Sprint169 bounded discovery only after Sprint168 canonical reconciliation closes. Do not preselect an objective. Select the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from current source, contracts, and regressions; reuse existing canonical owners and do not infer new mutation or operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry

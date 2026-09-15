# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint167  
**Canonical engineering commit:** `dbdf0aa90a6d6127cf10113ec8c1092b8780504f`  
**Latest engineering PR:** #752 — `Sprint167: add POS active shift performance workspace`  
**Final engineering head:** `05047a7f04650b52c4035e4ca40171609fa8db10`  
**Sprint167 regression:** `34919045197` — successful  
**M7.1 Application Regression:** `34919045660` — successful  
**Governance Required Checks:** `34919045650` — successful  
**PHP Foundation Regression:** `34919045018` — successful  
**Engineering envelope:** 12 paths — `e9ca990e36ce896428bc749d19cf47025ce0525fd707f5e330d5d1cfbbfc8b48`  
**Canonical reconciliation envelope:** 6 paths — `2fd60a53d8996d41452cb10036d350a14a4d95a2943e897c233d29f3eac32b14`  
**Next position:** Sprint168 bounded discovery from the fully reconciled Sprint167 checkpoint; no objective preselected.

> `dbdf0aa90a6d6127cf10113ec8c1092b8780504f` is the canonical Sprint167 **engineering** evidence. The reconciliation squash must never replace it as the canonical engineering commit.

## 1. Purpose / Why

The canonical POS already bound completed sales immutably to the active exact-device shift, while outlet Sales Summary remained aggregate-only and cash-variance reconciliation focused on closing-cash control. Operators still lacked a live read-only view of the current shift that explained completed transactions, tender/currency mix, full-sale voids, cash refunds, and active net sales value.

Sprint167 closes that operational visibility gap without creating a new permission, mutation owner, schema, or operational activation dependency.

## 2. Objective / Gap

`POS_ACTIVE_SHIFT_PERFORMANCE_WORKSPACE`.

## 3. What changed

- Added exact tenant + organization + outlet + device active-shift performance reporting.
- Reads only the current exact-device shift with `active_slot=1`; no active shift is an explicit valid empty state.
- Derives KPI only from immutable sales bound to the exact active shift.
- Buckets evidence by canonical tender category + currency + scale.
- Reports completed-sale, full-void, active-sale, and cash-refund counts plus gross, void, active-net, and cash-refund values.
- Defines active net value as gross minus full-sale void; CASH refund remains separate and is not subtracted twice.
- Fails closed when legacy null-shift sale evidence exists on the same device at or after shift opening.
- Fails closed on scope mismatch, malformed tender/evidence modes, inconsistent void/refund evidence, numeric overflow, or more than 64 buckets.
- Reuses existing deny-by-default `pos.reporting.sales-summary.view`; no new permission identifier or provisioning was introduced.
- Added guarded `GET /pos/reporting/active-shift-performance` / `pos.reporting.active-shift-performance` delivery behind default-false `ONEQAY_POS_ACTIVE_SHIFT_PERFORMANCE_ENABLED`.
- Registered the capability as a bounded child of the POS Operations Hub; Hub discovery continues to require reporting access and `Route::has()`.
- Added responsive Vue/Inertia read-only UI and focused SQLite regression.
- Left global routes/providers, shared permission registry, sale/shift/stock mutation owners, schema/migrations, Final Shift Close operational state, deployment, and updater untouched.

## 4. Evidence / Qualification

- Parent canonical post-Sprint166 checkpoint: `43e4108ba3806c7f0e97a8c8284d77e0160c9583`.
- Engineering PR #752 completed the surfaced exact-head PR-triggered matrix successfully.
- Exact engineering head: `05047a7f04650b52c4035e4ca40171609fa8db10`.
- Repository-native Product Owner merge authority: successful on the exact engineering head.
- Canonical engineering squash: `dbdf0aa90a6d6127cf10113ec8c1092b8780504f`.
- Parent-to-engineering compare: ahead 1, behind 0, exactly the qualified 12-path engineering envelope.
- Dedicated Sprint167 run `34919045197`: successful, including exact-envelope qualification, operational NO-GO assertions, source-contract assertions, PHP syntax, canonical/POS regressions, focused Sprint167 SQLite regression, Vue type-check/build, and tracked-source cleanliness.
- M7.1 `34919045660`, Governance `34919045650`, PHP Foundation `34919045018`, POS Sprint156–Sprint166 regressions, and surfaced historical Final Shift Close controls succeeded on the exact engineering head.

## 5. Product progression through Sprint167

The guarded POS operational chain now includes operational sales reporting, cashier sale entry, shift start, sale corrections, immutable sale history/receipt detail, catalog/opening inventory setup, common operations navigation, cash-variance reconciliation, positive inventory replenishment, inventory accountability, product-level sales performance, and live active-shift performance.

Sprint167 is read-only. It does not authorize shift-history analytics, pricing/margin accounting, customer/discount/payment domains, arbitrary stock correction, purchasing/supplier flows, inventory transfer, or stocktake mutation.

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

Begin Sprint168 bounded discovery only after Sprint167 canonical reconciliation closes. Do not preselect an objective. Select the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from current source, contracts, and regressions; reuse existing canonical owners and do not infer new mutation or operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry

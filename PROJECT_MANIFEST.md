# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-15

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint166
**Canonical engineering commit:** `e2758d0170a081953aaae11711ecc1ec3c0f8e78`
**Latest engineering PR:** #750 — `Sprint166: add POS product sales performance workspace`
**Final engineering head:** `7f8de2dc2639938a02f5413a1a1edbbb0b7e925f`
**Sprint166 regression:** `34917171015` — successful
**M7.1 Application Regression:** `34917171092` — successful
**Governance Required Checks:** `34917171084` — successful
**PHP Foundation Regression:** `34917171083` — successful
**Engineering envelope:** 12 paths — `3687acbd962b494a354c705c43eda5a71c1de426b5691d9b0f13b1620af2e228`
**Canonical reconciliation envelope:** 6 paths — `d887f8bcf393ad56a74d481e64aa3963c8678012f87025dcbf93ef8bde5ea4c8`
**Next position:** Sprint167 bounded discovery from the fully reconciled Sprint166 checkpoint; no objective preselected.

> `e2758d0170a081953aaae11711ecc1ec3c0f8e78` is the canonical Sprint166 **engineering** evidence. The reconciliation squash must never replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint156 delivered outlet-level operational sales reporting, while Sprint164 and Sprint165 made ongoing inventory receiving and inventory accountability operational. The remaining reporting gap was product-level performance: operators could see total sales but could not see which products generated completed-sale quantity/value or how full-sale voids changed the active product result.

Sprint166 closes that gap without introducing a new mutation authority, schema, permission, or reporting owner. It adds a bounded read-only product/currency performance view over existing immutable sales evidence.

## 2. Objective / Gap

`POS_PRODUCT_SALES_PERFORMANCE_WORKSPACE`.

## 3. What changed

- Added exact tenant + organization + outlet product sales performance reporting.
- Derives product/currency buckets from immutable completed sale lines.
- Reports gross quantity/value, full-sale-void quantity/value, and net active quantity/value.
- Subtracts full-sale void exactly once; CASH refund is deliberately not subtracted again because the canonical CASH refund follows an existing full-sale void.
- Preserves historical currency/scale boundaries as separate buckets.
- Preserves inactive catalog products when immutable sales history exists.
- Fails closed on orphaned, malformed, overflowing, negative, or inconsistent aggregate evidence.
- Bounds the read to 250 product/currency buckets and exposes explicit truncation.
- Reuses existing deny-by-default `pos.reporting.sales-summary.view` authority; no new permission identifier or provisioning was added.
- Added guarded `GET /pos/reporting/product-performance` / `pos.reporting.product-performance` delivery with default-false `ONEQAY_POS_PRODUCT_SALES_PERFORMANCE_ENABLED`.
- Registered the capability as a bounded child of the POS Operations Hub and retained `Route::has()` discovery.
- Added responsive Vue/Inertia UI and focused executable regression.
- Left existing Sales Summary owner, global route/provider ownership, schema/migrations, sale mutation, inventory mutation, purchasing/supplier, transfer, stocktake, and arbitrary stock adjustment untouched.

## 4. Evidence / Qualification

- Parent canonical post-Sprint165 checkpoint: `addbacaf5dbe02b9ceb4824b7c842c4145701387`.
- Engineering PR #750 completed the surfaced exact-head PR-triggered matrix successfully.
- Exact engineering head: `7f8de2dc2639938a02f5413a1a1edbbb0b7e925f`.
- Repository-native Product Owner merge authority: successful on the exact engineering head.
- Canonical engineering squash: `e2758d0170a081953aaae11711ecc1ec3c0f8e78`.
- Parent-to-engineering compare: ahead 1, behind 0, exactly the qualified 12-path engineering envelope.
- Dedicated Sprint166 run `34917171015`: successful, including exact-envelope qualification, operational NO-GO assertions, source-contract assertions, PHP syntax, canonical/POS regressions, focused Sprint166 SQLite regression, Vue type-check/build, and tracked-source cleanliness.

## 5. Product progression through Sprint166

The guarded POS operational chain now includes operational sales reporting, cashier sale entry, shift start, sale corrections, immutable sale history/receipt detail, catalog/opening inventory setup, a common operations hub, cash-variance reconciliation, positive inventory replenishment, inventory accountability, and product-level sales performance.

Sprint166 remains read-only. It does not imply future pricing analytics, margin/cost accounting, arbitrary stock correction, purchasing, supplier, transfer, or stocktake authority.

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

Begin Sprint167 bounded discovery only after Sprint166 canonical reconciliation closes. Do not preselect an objective. Select the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from current source, contracts, and regressions; reuse existing canonical owners and do not infer new operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry

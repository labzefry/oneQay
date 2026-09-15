# oneQay Roadmap

**Roadmap checkpoint:** Sprint166 closed canonically
**Canonical engineering baseline:** `e2758d0170a081953aaae11711ecc1ec3c0f8e78`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint166 horizon

Sprint166 closed the product-level operational reporting gap with `POS_PRODUCT_SALES_PERFORMANCE_WORKSPACE`.

The capability is read-only and exact tenant + organization + outlet scoped. It derives product/currency performance from immutable completed-sale lines, reports gross and full-sale-void quantities/values plus net active results, keeps historical currency/scale boundaries separate, retains inactive catalog history, fails closed on corrupt/orphaned/inconsistent evidence, and bounds output to 250 buckets with explicit truncation.

CASH refund is not subtracted a second time because the canonical refund path follows a full-sale void. The workspace reuses existing `pos.reporting.sales-summary.view`, adds no permission or provisioning, and remains default-false and Local/Test/CI gated.

Engineering PR #750 squash merged at `e2758d0170a081953aaae11711ecc1ec3c0f8e78`. Engineering envelope: 12 paths, SHA-256 `3687acbd962b494a354c705c43eda5a71c1de426b5691d9b0f13b1620af2e228`.

Canonical reconciliation envelope: six paths, SHA-256 `d887f8bcf393ad56a74d481e64aa3963c8678012f87025dcbf93ef8bde5ea4c8`.

## Product progression through Sprint166

The bounded operational POS chain now includes:

- operational sales reporting;
- cashier sale entry;
- shift start;
- sale correction;
- immutable sale history and receipt detail;
- catalog/opening-inventory setup;
- guarded POS operations navigation;
- cash-variance reconciliation;
- positive inventory replenishment;
- inventory accountability;
- product-level sales performance.

Future pricing analytics, margin/cost accounting, purchasing/supplier workflows, arbitrary stock adjustment, stocktake mutation, and inventory transfer remain separate possible bounded domains and are not authorized by Sprint166.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; real target-bound capability/dependency evidence remains absent; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint167 selection rule

Begin Sprint167 bounded discovery from the fully reconciled Sprint166 canonical checkpoint. Do not preselect an objective.

Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap. Reuse canonical shift, sale, catalog, inventory, void/refund, authorization, reporting, reconciliation, replenishment, accountability, and product-performance owners. Any new mutation authority or operational activation requires separate bounded justification and authorization.

Preserve fail-closed behavior, deny-by-default authorization, tenant/outlet isolation, exact-head CI, source-only operational posture, and the six-path canonical reconciliation model.

Author by Lab | zefry

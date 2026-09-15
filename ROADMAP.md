# oneQay Roadmap

**Roadmap checkpoint:** Sprint168 closed canonically
**Canonical engineering baseline:** `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint168 horizon

Sprint168 closed the closed-shift historical visibility gap with `POS_SHIFT_HISTORY_PERFORMANCE_WORKSPACE`.

The capability is read-only and exact tenant + organization + outlet scoped, with deliberate same-outlet cross-device visibility. It lists the newest 50 closed shifts that have exactly one canonical Final Shift Close evidence row, validates close identity and cash-variance/review arithmetic, and derives selected-shift sale performance from immutable shift-bound completed-sale, full-sale-void, and CASH-refund evidence.

Active net equals gross minus full-sale void. CASH refund remains separate and is not deducted twice. Missing close evidence, legacy null-shift sales inside the shift window, foreign-scope/outside-window evidence, malformed tender/evidence, inconsistent full-void/refund evidence, overflow, and excessive bucket count fail closed.

The workspace reuses existing `pos.reporting.sales-summary.view`, adds no permission or provisioning, and remains default-false and Local/Test/CI gated with existing Final Shift Close source/runtime readiness dependency.

Engineering PR #754 squash merged at `d3703a6b18f478acd812e7892c3871ce3aaf7bfa`. Engineering envelope: 12 paths, SHA-256 `d42a5a7d8568766e524ff662107ffdb35452d4f9ac00f26cdc8e419d36bc3c25`.

Canonical reconciliation envelope: six paths, SHA-256 `350fd16d111bb398c34a63e1519cc02206bcc373378a9366768c198ccf8146b5`.

## Product progression through Sprint168

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
- product-level sales performance;
- live active-shift performance;
- closed-shift historical performance.

Pricing/margin accounting, customer/discount/payment domains, purchasing/supplier workflows, arbitrary stock adjustment, stocktake mutation, inventory transfer, and operational activation remain separate possible bounded domains and are not authorized by Sprint168.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; real target-bound capability/dependency evidence remains absent; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint169 selection rule

Begin Sprint169 bounded discovery from the fully reconciled Sprint168 canonical checkpoint. Do not preselect an objective.

Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap. Reuse canonical shift, sale, catalog, inventory, void/refund, authorization, reporting, reconciliation, replenishment, accountability, product-performance, active-shift-performance, and shift-history-performance owners. Any new mutation authority or operational activation requires separate bounded justification and authorization.

Preserve fail-closed behavior, deny-by-default authorization, tenant/outlet/device isolation, exact-head CI, source-only operational posture, and the six-path canonical reconciliation model.

Author by Lab | zefry

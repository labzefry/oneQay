# oneQay Roadmap

**Roadmap checkpoint:** Sprint167 closed canonically  
**Canonical engineering baseline:** `dbdf0aa90a6d6127cf10113ec8c1092b8780504f`  
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint167 horizon

Sprint167 closed the live exact-device shift visibility gap with `POS_ACTIVE_SHIFT_PERFORMANCE_WORKSPACE`.

The capability is read-only and exact tenant + organization + outlet + device scoped. It reads only the current `active_slot=1` shift, derives tender/currency/scale performance from immutable shift-bound sales, reports completed/full-void/active/cash-refund counts plus gross/full-void/active-net/refunded-cash values, and treats no active shift as a valid explicit empty state.

Active net equals gross minus full-sale void. CASH refund remains separate and is not deducted twice. Legacy null-shift sales at/after opening, scope mismatch, malformed tender/evidence modes, inconsistent full-void/refund evidence, overflow, and excessive bucket count fail closed.

The workspace reuses existing `pos.reporting.sales-summary.view`, adds no permission or provisioning, and remains default-false and Local/Test/CI gated.

Engineering PR #752 squash merged at `dbdf0aa90a6d6127cf10113ec8c1092b8780504f`. Engineering envelope: 12 paths, SHA-256 `e9ca990e36ce896428bc749d19cf47025ce0525fd707f5e330d5d1cfbbfc8b48`.

Canonical reconciliation envelope: six paths, SHA-256 `2fd60a53d8996d41452cb10036d350a14a4d95a2943e897c233d29f3eac32b14`.

## Product progression through Sprint167

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
- live active-shift performance.

Shift-history analytics, pricing/margin accounting, customer/discount/payment domains, purchasing/supplier workflows, arbitrary stock adjustment, stocktake mutation, and inventory transfer remain separate possible bounded domains and are not authorized by Sprint167.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; real target-bound capability/dependency evidence remains absent; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint168 selection rule

Begin Sprint168 bounded discovery from the fully reconciled Sprint167 canonical checkpoint. Do not preselect an objective.

Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap. Reuse canonical shift, sale, catalog, inventory, void/refund, authorization, reporting, reconciliation, replenishment, accountability, product-performance, and active-shift-performance owners. Any new mutation authority or operational activation requires separate bounded justification and authorization.

Preserve fail-closed behavior, deny-by-default authorization, tenant/outlet/device isolation, exact-head CI, source-only operational posture, and the six-path canonical reconciliation model.

Author by Lab | zefry

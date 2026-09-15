# oneQay Roadmap

**Roadmap checkpoint:** Sprint165 closed canonically
**Canonical engineering baseline:** `dc6340c04ac710bd38966d897b27e23fd92c0a41`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint165 horizon

Sprint165 closed the material inventory-accountability visibility gap with `POS_INVENTORY_ACCOUNTABILITY_WORKSPACE`.

The workspace is read-only and derives expected current stock from canonical immutable evidence: opening baseline + positive replenishment + full-sale void restoration - completed sale lines. Persisted catalog stock must match exactly or the workspace fails closed. Reconciliation does not infer causality from same-second timestamps; timestamps order display only.

The capability reuses existing `pos.inventory.baseline` OR `pos.inventory.replenish` authority. It does not add a permission, migration, schema, arbitrary adjustment engine, stocktake mutation, purchasing/supplier domain, transfer capability, or negative stock mutation.

Engineering PR #748 squash merged at `dc6340c04ac710bd38966d897b27e23fd92c0a41`. Engineering envelope: 14 paths, SHA-256 `d83945325461acdd9db7f1ab02bb908a1e0e263a6f680e2f0acf1f3256978786`.

Canonical reconciliation envelope: six paths, SHA-256 `2f10216f9f2c86a188a924c66a8473c17cd5310da47bd768a8ab1bb0f4a17533`.

## Product progression through Sprint165

The current guarded POS operational surface includes reporting, cashier sale entry, shift start, sale corrections, immutable sale history/receipt detail, catalog/opening-inventory setup, Operations Hub navigation, cash-variance reconciliation, positive inventory replenishment, and inventory accountability.

Inventory lifecycle boundaries are explicit:
- opening baseline = one-time initialization;
- replenishment = positive stock receipt after baseline;
- completed sale = stock decrement;
- full-sale void = sold-stock restoration;
- CASH refund = financial correction without a second stock restoration;
- accountability = read-only reconciliation across those existing owners.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. No roadmap text grants operational authority. Selected durable target remains `null`; migration #27 execution remains `NOT_EXECUTED`; permission provisioning remains `NONE`; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint166 selection rule

Begin Sprint166 bounded discovery only from the fully reconciled Sprint165 canonical checkpoint. Do not preselect the objective, source envelope, permission, migration, or mutation authority.

Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap after inspecting current source/contracts/regressions. Reuse existing canonical shift, sale, catalog, inventory, replenishment, accountability, void/refund, authorization, reporting, and reconciliation owners.

Potential future domains such as arbitrary stock correction, stocktake, supplier purchasing, goods receiving beyond bounded replenishment, inter-outlet transfer, or negative inventory mutation are **not** authorized by Sprint165 and must not be selected without independent bounded evidence that the gap is both material and not already owned.

Preserve fail-closed behavior, deny-by-default authorization, tenant/outlet isolation, exact-head CI, source-only operational posture, and the six-path canonical reconciliation model.

Author by Lab | zefry

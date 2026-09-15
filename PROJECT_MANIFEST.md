# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint165
**Canonical engineering commit:** `dc6340c04ac710bd38966d897b27e23fd92c0a41`
**Latest engineering PR:** #748 — `Sprint165: add POS inventory accountability workspace`
**Final engineering head:** `553b7b07b6b30ae37ebd36a041e2cf85ffa09c03`
**Sprint165 regression:** `34915204475` — successful
**M7.1 regression:** `34915204297` — successful
**Governance Required Checks:** `34915204324` — successful
**PHP Foundation Regression:** `34915204303` — successful
**Engineering envelope:** 14 paths — `d83945325461acdd9db7f1ab02bb908a1e0e263a6f680e2f0acf1f3256978786`
**Canonical reconciliation envelope:** 6 paths — `2f10216f9f2c86a188a924c66a8473c17cd5310da47bd768a8ab1bb0f4a17533`
**Next position:** Sprint166 bounded discovery from the post-Sprint165 canonical checkpoint; no objective preselected.

> `dc6340c04ac710bd38966d897b27e23fd92c0a41` is the canonical **engineering** evidence for Sprint165. The reconciliation squash must never replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint164 established positive-only ongoing inventory replenishment after the canonical one-time opening inventory baseline. At that point oneQay had immutable stock-affecting evidence from four canonical owners: opening baseline, replenishment, completed-sale lines, and full-sale void restoration. The product still lacked one operational read surface that could prove the persisted current quantity reconciles exactly to those sources.

Sprint165 closes that accountability gap without inventing a new mutation authority. It gives authorized inventory operators a read-only view of how current stock was derived and fails closed whenever the durable evidence cannot explain the persisted balance.

## 2. Objective / Gap

`POS_INVENTORY_ACCOUNTABILITY_WORKSPACE`.

The bounded objective is inventory accountability only. It does **not** add arbitrary stock adjustment, negative adjustment, stocktake mutation, supplier/purchasing, transfer, receiving beyond the Sprint164 replenishment owner, or any new permission identifier.

## 3. What changed

Sprint165 added a tenant + organization + outlet scoped read model and Vue/Inertia workspace that derives each baselined product's expected available quantity as:

`opening quantity + positive replenishment + full-sale void restoration - completed sale quantity = expected current quantity`

The server requires that expected quantity equal the canonical catalog `available_quantity`. Any mismatch, orphaned evidence, malformed quantity, overflow, invalid scope, or unsupported runtime fails closed instead of presenting a misleading balance.

The reconciliation equation is aggregate and commutative. Event timestamps are used only for deterministic presentation of recent movement evidence, not to decide the balance. This avoids inventing causal ordering when durable events share timestamps.

Cash refunds are intentionally excluded from stock movement derivation because the canonical CASH refund owner does not restore inventory. Full-sale void remains the only correction owner that restores sold stock.

Inactive but already-baselined products remain visible because accountability is historical rather than an active-catalog merchandising view. The workspace exposes up to 200 recent immutable stock evidence entries from opening baseline, replenishment, completed sale decrement, and full-sale void restoration.

Authorization reuses existing inventory authorities only. Access is deny-by-default and requires either the existing `pos.inventory.baseline` authority or Sprint164's `pos.inventory.replenish` authority. The Operations Hub uses the same OR composition, filters the destination through `Route::has()`, and the target workspace independently authorizes again.

Delivery is default-off through `ONEQAY_POS_INVENTORY_ACCOUNTABILITY_ENABLED` and remains restricted to Local/Test/CI, durable persistence enabled, exact session-control values, and verified POS session context. The workspace is a bounded child provider of the POS Operations Hub. No global route registry, global provider registry, shared `PosPermission.php`, Final Shift Close provider, global migration directory, or module-owned migration directory changed.

## 4. Evidence / Qualification

Engineering PR #748 used exact base `e5ee1190605863fc985d5c218c796d8e4524f6b4` and exact final engineering head `553b7b07b6b30ae37ebd36a041e2cf85ffa09c03`.

The final engineering envelope was exactly 14 paths with sorted-newline SHA-256 `d83945325461acdd9db7f1ab02bb908a1e0e263a6f680e2f0acf1f3256978786`. The complete latest surfaced pull-request-triggered matrix succeeded, including dedicated Sprint165 run `34915204475`, M7.1 `34915204297`, Governance `34915204324`, PHP Foundation `34915204303`, current POS regressions, and surfaced historical Final Shift Close controls.

Repository-native exact-head Product Owner merge authority was verified for `553b7b07b6b30ae37ebd36a041e2cf85ffa09c03`. PR #748 then squash merged with expected-head protection at engineering squash `dc6340c04ac710bd38966d897b27e23fd92c0a41`.

Post-engineering verification proved the Sprint164 canonical checkpoint to Sprint165 engineering squash is exactly one commit, behind by zero, with exactly the qualified 14-path delta.

Focused regression uses a disposable SQLite database to prove opening 10 + replenishment 5 - completed sales 7 + full-sale void restoration 4 = current 12, proves CASH refund does not create stock restoration, preserves inactive baselined products, prevents foreign tenant/outlet leakage, validates both accepted existing inventory authorities, rejects no-authority access, rejects non-CI/runtime or disabled feature states, and fails closed when persisted current stock is intentionally corrupted.

## 5. Operational boundaries / NO-GO

Sprint165 grants no operational activation authority. Machine-readable operational state remains authoritative and unchanged:

- durable activation target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- Final Shift Close migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- real target-bound capability evidence: absent;
- real dependency-envelope evidence: absent;
- Final Shift Close feature activation: `INACTIVE`;
- runtime allowlist: Local/Test/CI only;
- deployment authority: `NOT_GRANTED`;
- Technical Preview: `NOT_AUTHORIZED`;
- Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

Authoritative operational files remain:

- `ops/final-shift-close/STATE.json`
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`
- `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`

## 6. Next position

Begin **Sprint166 bounded discovery** only from the fully reconciled post-Sprint165 `main`. Do not preselect an objective, permission, mutation authority, migration, schema shape, or source envelope. Inspect current source/contracts/regressions and select the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap.

Sprint165 does not imply authority for stock correction, stocktake adjustment, inter-outlet transfer, supplier purchasing, or negative inventory mutation. Any such capability requires its own bounded justification and fail-closed lifecycle.

Every material sprint continues to record **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**.

Author by Lab | zefry

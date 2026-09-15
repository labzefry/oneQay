# Changelog

## 2026-09-15 — Sprint165 closed

**Sprint165: POS inventory accountability workspace**

- **Purpose / Why:** canonical opening baseline, replenishment, sale decrement, and full-sale void restoration evidence existed, but there was no operational read model proving current stock reconciled to those immutable sources.
- **Objective / Gap:** `POS_INVENTORY_ACCOUNTABILITY_WORKSPACE`.
- Added read-only tenant + organization + outlet inventory accountability.
- For every baselined product, the server derives `opening + replenishment + full-sale-void restoration - completed sale quantity` and requires exact equality with canonical current catalog stock.
- Made balance derivation aggregate/commutative so same-second event ordering cannot invent causality; timestamps are used only for deterministic presentation.
- Explicitly excluded CASH refund evidence from stock restoration because canonical cash refund does not restore inventory.
- Preserved inactive but baselined products for historical accountability.
- Added up to 200 recent immutable movement entries spanning opening baseline, replenishment, completed-sale decrement, and full-sale void restoration.
- Reused existing inventory authorities only: `pos.inventory.baseline` OR `pos.inventory.replenish`; no new permission identifier or provisioning was introduced.
- Added guarded `GET /pos/inventory/accountability` / `pos.inventory.accountability.workspace` delivery, default-false `ONEQAY_POS_INVENTORY_ACCOUNTABILITY_ENABLED`, Local/Test/CI restriction, persistence/session gates, target re-authorization, and Operations Hub `Route::has()` discovery.
- Added fail-closed checks for corrupt/orphan evidence, scope leakage, overflow, unsupported runtime, disabled feature state, and persisted-stock mismatch.
- Added no migration, schema change, arbitrary stock adjustment, stocktake mutation, supplier/purchasing flow, transfer flow, negative inventory authority, global route/provider change, shared permission-registry change, or Final Shift Close owner change.
- Exact engineering head `553b7b07b6b30ae37ebd36a041e2cf85ffa09c03` completed the surfaced PR-triggered matrix successfully.
- Sprint165 regression run `34915204475`, M7.1 `34915204297`, Governance `34915204324`, PHP Foundation `34915204303`, POS successor regressions, and surfaced Final Shift Close historical controls succeeded.
- Repository-native exact-head Product Owner merge authority verified successfully.
- Engineering envelope: 14 paths; SHA-256 `d83945325461acdd9db7f1ab02bb908a1e0e263a6f680e2f0acf1f3256978786`.
- Engineering PR #748 squash merged at `dc6340c04ac710bd38966d897b27e23fd92c0a41`.
- Canonical reconciliation envelope: six paths; SHA-256 `2f10216f9f2c86a188a924c66a8473c17cd5310da47bd768a8ab1bb0f4a17533`.
- **Operational boundaries / NO-GO:** selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`.
- **Next position:** Sprint166 bounded discovery; no objective, permission, migration, mutation authority, or source envelope preselected.

## 2026-09-15 — Sprint164 closed

**Sprint164: POS inventory replenishment foundation workspace**

- Added positive-only ongoing inventory replenishment after the canonical one-time opening baseline.
- Added dedicated deny-by-default `pos.inventory.replenish` authority with no automatic grant or provisioning.
- Added stable operation identity, replay protection, active-product/baseline prerequisites, locked overflow-safe stock transition, and immutable before/received/after evidence.
- Added module-owned migration #28 under `apps/web/database/module-migrations/pos/`; global migration horizon remains through #27.
- Preserved sale completion as decrement owner, full-sale void as restoration owner, and cash refund as non-restoring.
- Engineering envelope: 22 paths; SHA-256 `b826b746e18bc39025735e10fe645ad559eceab992801190a654624462811f8e`.
- Engineering PR #746 squash merged at `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`.
- Reconciliation envelope: six paths; SHA-256 `19c035b85a4698f60a78cbb70ccd0c1b82835c47073f5dcb2e2443f49c88f0fa`.

## 2026-09-14 — Sprint163 closed

- Materialized `POS_CASH_VARIANCE_RECONCILIATION_WORKSPACE` over existing explanation/reviewer authorities while preserving server-authoritative variance subjects, maker-checker separation, and terminal rejection semantics.
- Engineering PR #744 squash merged at `a920e63c1a1d2623664b416422c3d5a471e389a6`.

## 2026-09-14 — Sprint162 closed

- Added guarded read-only POS operations hub using existing permissions and delivered named-route discovery.
- Engineering PR #742 squash merged at `332bcff11b40307d350c7ce5b3a6c08913c4251c`.

## 2026-09-14 — Sprint161 closed

- Added guarded catalog/opening-stock operational workspace over existing catalog-preparation and one-time inventory-baseline authorities.
- Engineering PR #740 squash merged at `33080c0b5f5c6f66e9994ad7f05ba78dea241294`.

## 2026-09-14 — Sprint160 closed

- Added read-only immutable sale history and receipt detail with correction evidence validation and foreign-scope non-disclosure.
- Engineering PR #738 squash merged at `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`.

## 2026-09-14 — Sprint159 closed

- Added operational sale correction workspace over canonical void and CASH-refund authorities.
- Engineering PR #736 squash merged at `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`.

## 2026-09-14 — Sprint158 closed

- Added resumable exact-device shift-start workspace over canonical shift-opening and opening-cash authorities.
- Engineering PR #734 squash merged at `d6eb8f7f359584130deea9ec0ed3572add3c05aa`.

## 2026-09-14 — Sprint157 closed

- Added operational POS cashier sale-entry workspace over canonical sale completion authority.
- Engineering PR #732 squash merged at `b4d21b208a0580f4b40565b028dd6aed9bb190b8`.

## 2026-09-14 — Sprint156 closed

- Added read-only tenant + organization + outlet scoped operational sales reporting.
- Engineering PR #730 squash merged at `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`.

## Earlier material engineering history

Sprint88–Sprint155 established the Final Shift Close source/readiness chain through migration source materialization, runtime dependency/readiness, selected-target identity, attestation/selection binding, DB binding/control-plane hardening, capability/dependency evidence foundations, permission-provisioning binding, and source-only activation foundations. Earlier work established architecture/governance, authentication/session hardening, POS sale/payment/receipt/catalog/inventory foundations, full-sale void/refund, and cash/variance evidence.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

Current values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, target selection blocked with selected target `null`, real capability evidence `NONE`, real dependency-envelope evidence `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview/Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

Each material sprint records **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry

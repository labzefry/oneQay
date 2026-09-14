# Changelog

This changelog records **material canonical progress**, not every intermediate compatibility or CI-only commit. Detailed provenance remains in merged pull requests, Git history, workflows, tests, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-14 — Sprint157 closed

**Sprint157: operational POS cashier sale-entry workspace**

- **Purpose / Why:** canonical POS already had authoritative sale completion, catalog, stock, shift, authorization, idempotency, and receipt semantics, but no real operational cashier workspace.
- **Objective / Gap:** `POS_CASHIER_SALE_ENTRY_WORKSPACE`.
- **What changed:** added scoped cashier read models, read-only workspace repository, exact-device active-shift readiness, dedicated guarded provider/controller, fail-closed workspace config, Vue/Inertia cashier UI, and executable SQLite regression.
- Cashier catalog is tenant/outlet scoped and exposes only active positive-stock items.
- Authorization reuses existing deny-by-default `pos.sale.complete` permission.
- Checkout posts to the existing canonical `pos.sales.complete` endpoint; `CompleteSale` and `LaravelDurablePosSaleRepository` remain mutation authority.
- Cart prevents mixed currency/scale and does not accept arbitrary client-side prices.
- CASH and MANUAL_EXTERNAL tender rules are validated in the UI while authoritative server rules remain final.
- No automatic network retry was introduced.
- `routes/web.php`, global provider registry, Composer manifest, database migrations, and operational state remained unchanged.
- Engineering PR #732 squash merged.
- Parent canonical post-Sprint156 checkpoint: `1255fd1a310792c50e174465aa91417af23bd47e`.
- Final exact engineering head: `0ff14cf95aa54cd798fe5d1b5611c2890757e5b3`.
- Complete exact-head PR-triggered matrix: successful.
- Sprint157 regression run `34815027880`: successful.
- M7.1 Application Regression run `34815027865`: successful.
- Governance Required Checks run `34815027920`: successful.
- PHP Foundation Regression run `34815027969`: successful.
- Sprint96 run `34815028008`, Sprint97 run `34815027894`, Sprint126 run `34815027942`, Sprint148 run `34815027851`, and Sprint156 run `34815027870`: successful.
- Repository-native exact-head Product Owner merge authority: successful.
- Engineering envelope: exactly 12 paths; SHA-256 `f363bbfff9b1a52479c0f6d76e7cefe4b14ac89c597b2cd7894713e34bcc2f5b`.
- Canonical engineering squash: `b4d21b208a0580f4b40565b028dd6aed9bb190b8`.
- Post-merge verification proved exactly one squash commit over `1255fd1a310792c50e174465aa91417af23bd47e` and exactly the qualified 12-path delta.
- Post-Sprint157 reconciliation envelope: six paths; SHA-256 `4393c47067856f6d426cc2ce3f976bda78a72c13adaedb47ff53cc93f2c4ca1c`.
- **Operational boundaries / NO-GO:** target selection remains blocked/null; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; real capability/dependency evidence remains `NONE`; Final Shift Close runtime allowlist remains Local/Test/CI; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.
- **Next position:** Sprint158 bounded discovery from canonical post-Sprint157; no objective or source envelope is preselected.

## 2026-09-14 — Sprint156 closed

**Sprint156: POS operational sales reporting**

- Added read-only tenant + organization + outlet scoped operational sales reporting using canonical POS persistence.
- Preserved currency and currency-scale boundaries and deny-by-default authorization.
- Added guarded delivery, Vue/Inertia dashboard, and executable SQLite regression.
- Corrected historical workflow successor compatibility only where exact-head CI proved stale ownership.
- Engineering PR #730 squash merged at `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`.
- Final engineering head `5e460d1c7c5174cc831106ade3e3fa6309acba4d`; complete exact-head matrix and Product Owner authority successful.
- Engineering envelope SHA-256: `34c6dab2c898ddd9133aaa6d5413ca7b345127020d8f04fe54f861a4d1a5e79c`.
- Reconciliation envelope SHA-256: `adba5b23ef33aeb360ebb4090b3f848fc2a3704807a60026c1344b2e0d1a54f4`.

## 2026-09-14 — Sprint155 closed

- Added deterministic source-only Final Shift Close activation transport handoff envelope and executable regression.
- Engineering PR #728 squash merged at `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`.
- Concrete configuration-mutation transport and dispatchable activation executor remained `NOT_IMPLEMENTED`; feature activation remained `INACTIVE`.

## Earlier material engineering history

Sprint88–Sprint155 established the Final Shift Close source/readiness chain through migration source materialization, runtime dependency/readiness, selected-target identity, attestation/selection binding, DB binding/control-plane hardening, target-bound capability/dependency evidence foundations, permission-provisioning binding, and feature-activation source foundations. Earlier work also established architecture/governance, authentication/session hardening, POS sale/payment/receipt/catalog/inventory foundations, cash/variance evidence, and regression-preservation controls.

## Current lifecycle boundary

Machine-readable authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

Current values remain migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, target selection blocked with selected target `null`, real capability evidence `NONE`, real dependency-envelope evidence `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview/Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

## Sprint description standard

Each material sprint records: **Purpose / Why; Objective / Gap; What changed; Evidence / Qualification; Operational boundaries / NO-GO; Next position**.

Author by Lab | zefry

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Canonical engineering checkpoint:** Sprint157  
**Canonical engineering commit:** `b4d21b208a0580f4b40565b028dd6aed9bb190b8`  
**Latest engineering PR:** #732 — `Sprint157: add operational POS cashier sale-entry workspace`  
**Status date:** 2026-09-14

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

Sprint157 closed the bounded P1 product-usability gap `POS_CASHIER_SALE_ENTRY_WORKSPACE`. Canonical POS sale completion, catalog, stock, active-shift, authorization, idempotency, and receipt semantics already existed; Sprint157 added a real operational cashier surface over those existing authorities rather than creating a second transaction engine.

The cashier workspace reads only the authorized tenant/outlet catalog, exposes only active positive-stock items, binds shift readiness to the exact tenant + organization + outlet + device, reuses `pos.sale.complete`, preserves currency/scale boundaries, and submits checkout to the existing canonical sale endpoint. Server-side sale completion remains authoritative for price, stock, active shift, Money rules, persistence, idempotency, tender semantics, sale/event writes, and receipt/change calculation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint157 |
| Canonical engineering commit | `b4d21b208a0580f4b40565b028dd6aed9bb190b8` |
| Latest engineering PR | #732, squash merged |
| Sprint157 final engineering head | `0ff14cf95aa54cd798fe5d1b5611c2890757e5b3` |
| Sprint157 exact-head CI | Complete PR-triggered matrix successful |
| Sprint157 Product Owner authority | `product-owner-merge-authority=success` for exact engineering head |
| Sprint157 engineering envelope | 12 paths; SHA-256 `f363bbfff9b1a52479c0f6d76e7cefe4b14ac89c597b2cd7894713e34bcc2f5b` |
| Post-Sprint157 reconciliation envelope | 6 paths; SHA-256 `4393c47067856f6d426cc2ce3f976bda78a72c13adaedb47ff53cc93f2c4ca1c` |
| POS cashier workspace | Materialized as guarded Local/Test/CI delivery |
| Cashier authorization | Existing `pos.sale.complete`, deny-by-default |
| Cashier scope | Tenant + organization + outlet + device |
| Catalog authority | Server-supplied active, positive-stock scoped catalog |
| Sale mutation authority | Existing canonical `/pos/sales` / `CompleteSale` path |
| POS operational sales reporting | Materialized read-only through Sprint156 |
| Runtime allowlist | `local`, `test`, `ci` only |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected activation target | `null` |
| Final Shift Close migration #27 | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Real target-bound capability evidence | `NONE` |
| Real dependency-envelope evidence | `NONE` |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |

## 2. Material engineering progress

### Platform, governance, and POS foundation

The repository has established modular-monolith architecture, tenant isolation, deny-by-default authorization, first-party session and privileged-authentication foundations, versioned REST governance, deterministic CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work now includes shift/register opening, sale completion/payment/receipt evidence, catalog preparation, inventory baseline, durable idempotency, cash variance/adjudication, Final Shift Close source/readiness controls, operational sales reporting, and a guarded cashier sale-entry workspace.

### Final Shift Close engineering chain

Sprint88 through Sprint155 established the source/readiness chain from migration #27 materialization through runtime dependency/readiness, selected-target identity, DB binding/control-plane hardening, capability/dependency evidence foundations, permission-provisioning binding, and source-only feature-activation planning/handoff. Operational execution remains separately gated and has not occurred.

### Product-readiness pivot

- Sprint156 — read-only tenant + organization + outlet scoped POS operational sales reporting.
- Sprint157 — operational POS cashier sale-entry workspace over existing canonical transaction authority.

## 3. Sprint157 description and closure evidence

### Purpose / Why

Post-Sprint156 discovery showed the authoritative sale engine and catalog/inventory foundations existed, but a real operational cashier workspace did not. Sale entry was API-only or synthetic Technical Preview interaction, which left a material usability gap on the shortest path to a usable product.

### Objective / Gap

Bounded objective: `POS_CASHIER_SALE_ENTRY_WORKSPACE`.

### What changed

- Added cashier catalog item and workspace snapshot application read models.
- Added a scoped read-only repository and authorized `ViewPosCashierWorkspace` service.
- Added a Laravel read repository over existing shift and sale-catalog persistence.
- Added a dedicated fail-closed cashier provider and GET `/pos/cashier` Inertia delivery.
- Added `ONEQAY_POS_CASHIER_WORKSPACE_ENABLED`, default false.
- Added a Vue/Inertia cashier UI with product search, stock-bounded quantities, active-shift readiness, single currency/scale cart, CASH/MANUAL_EXTERNAL tender validation, and explicit no-auto-retry behavior.
- Reused the existing named `pos.sales.complete` endpoint and existing `PosPermission::completeSale()` authorization.
- Added disposable SQLite regression covering scope isolation, exact-device shift binding, sellable-stock filtering, currency/scale preservation, and runtime/feature/persistence fail-closed behavior.
- Left `routes/web.php`, global provider registry, Composer manifest, migrations, and all authoritative operational state unchanged.

### Evidence / Qualification

- Engineering PR: #732, squash merged.
- Parent canonical post-Sprint156 checkpoint: `1255fd1a310792c50e174465aa91417af23bd47e`.
- Final exact engineering head: `0ff14cf95aa54cd798fe5d1b5611c2890757e5b3`.
- Complete exact-head PR-triggered matrix: **successful**.
- Sprint157 cashier regression run `34815027880`: successful.
- M7.1 Application Regression run `34815027865`: successful.
- Governance Required Checks run `34815027920`: successful.
- PHP Foundation Regression run `34815027969`: successful.
- Sprint96 run `34815028008`, Sprint97 run `34815027894`, Sprint126 run `34815027942`, Sprint148 run `34815027851`, and Sprint156 run `34815027870`: successful.
- Repository-native Product Owner authorization: exact PR #732 and exact engineering head.
- `product-owner-merge-authority`: successful — exact-head authority verified.
- Engineering envelope: 12 paths; SHA-256 `f363bbfff9b1a52479c0f6d76e7cefe4b14ac89c597b2cd7894713e34bcc2f5b`.
- Canonical engineering squash commit: `b4d21b208a0580f4b40565b028dd6aed9bb190b8`.
- Post-merge verification: exactly one squash commit above `1255fd1a310792c50e174465aa91417af23bd47e`, with exactly the 12 qualified engineering paths.
- Post-Sprint157 reconciliation envelope: six paths; SHA-256 `4393c47067856f6d426cc2ce3f976bda78a72c13adaedb47ff53cc93f2c4ca1c`.

### Operational boundaries / NO-GO

Sprint157 does not select or persist a durable target, dispatch capability/dependency producers, execute migration #27, provision permissions, produce real target-bound capability/dependency evidence, widen the Final Shift Close runtime allowlist, activate Final Shift Close, grant deployment authority, activate Technical Preview/Production, or activate the updater.

Machine-readable state remains target selection blocked with `selected_target=null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview and Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

### Next position

The next engineering position is **Sprint158 bounded discovery from canonical post-Sprint157**. No Sprint158 objective, implementation, or source envelope is preselected.

## 4. Operational truth — NO-GO remains authoritative

- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- selected target: `null`;
- target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- capability-evidence producer dispatch: `NOT_PERFORMED`; real capability evidence: `NONE`;
- dependency-evidence producer dispatch: `NOT_PERFORMED`; real dependency evidence: `NONE`;
- Final Shift Close runtime allowlist remains Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`.

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## 5. Documentation responsibility model

- `PROJECT_MANIFEST.md` — canonical human-readable current project/lifecycle state;
- `README.md` — concise entry-point summary;
- `CHANGELOG.md` — material chronology;
- `TASKS.md` — current completed/pending workboard;
- `ROADMAP.md` — forward sequencing and lifecycle gates;
- `docs/SPRINT*.md` — detailed historical bounded-sprint evidence where materialized;
- `ops/final-shift-close/*.json` — machine-readable operational authority;
- merged PRs and Git history — immutable implementation provenance.

## 6. Mandatory sprint description and update rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**. Every material closed sprint reconciles this manifest and the four root summary documents while the just-closed sprint preservation workflow remains successor-compatible.

Author by Lab | zefry

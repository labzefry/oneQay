# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint159
**Canonical engineering commit:** `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`
**Latest engineering PR:** #736 — `Sprint159: add operational POS sale correction workspace`
**Status date:** 2026-09-14

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

Sprint159 closed the bounded P1 product-usability gap `POS_SALE_CORRECTION_WORKSPACE`. Canonical full-sale void and full CASH refund mutation contracts, authorization, idempotency, inventory reversal, evidence, and audit events already existed; Sprint159 added a real operational correction workspace over those authorities instead of creating a second correction engine.

The correction workspace reads recent sales in exact tenant + organization + outlet scope, preserves original sale device and original-shift state, validates void/refund evidence integrity, and exposes action eligibility independently through existing `pos.sale.void` and `pos.sale.refund` permissions. CASH correction remains explicitly two-stage: `COMPLETED → VOIDED → REFUNDED`. MANUAL_EXTERNAL sales may be voided but require external settlement rather than an in-app CASH refund.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint159 |
| Canonical engineering commit | `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4` |
| Latest engineering PR | #736, squash merged |
| Sprint159 final engineering head | `475c4d8fb9a1e17467e71c77fb837351d77e48e2` |
| Sprint159 exact-head CI | Complete surfaced PR-triggered matrix successful |
| Sprint159 Product Owner authority | `product-owner-merge-authority=success` for exact engineering head |
| Sprint159 engineering envelope | 11 paths; SHA-256 `6a5f49136334f99c182220a7db89a7869611be8a5b65a6cd3fe4c73fcc40cf00` |
| Post-Sprint159 reconciliation envelope | 6 paths; SHA-256 `e7ebee58804975f9d64bc3061c13dccd4c1212877fe73cb75072163e101070dc` |
| POS operational sales reporting | Materialized through Sprint156 |
| POS cashier workspace | Materialized through Sprint157 |
| POS shift-start workspace | Materialized through Sprint158 |
| POS sale correction workspace | Materialized through Sprint159 |
| Correction scope | Tenant + organization + outlet; original sale device visible |
| Void authorization | Existing `pos.sale.void`, deny-by-default |
| CASH refund authorization | Existing `pos.sale.refund`, deny-by-default |
| Void mutation authority | Existing `VoidSale` / `pos.sales.void` |
| CASH refund mutation authority | Existing `RecordCashRefund` / `pos.sales.cash-refund` |
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

Bounded POS work includes shift/register opening, sale completion/payment/receipt evidence, catalog preparation, inventory baseline, durable idempotency, full-sale void, full CASH refund evidence, cash variance/adjudication, Final Shift Close source/readiness controls, operational sales reporting, cashier sale entry, shift-start operations, and sale correction operations.

### Final Shift Close engineering chain

Sprint88 through Sprint155 established source/readiness controls through migration #27 materialization, runtime dependency/readiness, selected-target identity, DB binding/control-plane hardening, capability/dependency evidence foundations, permission-provisioning binding, and source-only feature-activation planning/handoff. Operational execution remains separately gated and has not occurred.

### Product-readiness pivot

- Sprint156 — read-only tenant + organization + outlet scoped POS operational sales reporting.
- Sprint157 — operational POS cashier sale-entry workspace over existing sale authority.
- Sprint158 — resumable exact-device shift-start workspace over existing shift-opening/opening-cash authorities.
- Sprint159 — operational sale correction workspace over existing void/refund authorities.

## 3. Sprint159 description and closure evidence

### Purpose / Why

Post-Sprint158 discovery showed that oneQay could open a register, sell, report, and close shifts, while canonical correction APIs already existed but remained API-only. A cashier/authorized operator had no guarded operational surface for sale void/refund correction.

### Objective / Gap

Bounded objective: `POS_SALE_CORRECTION_WORKSPACE`.

### What changed

- Added correction workspace repository contract and validated snapshot model.
- Added exact tenant + organization + outlet scoped read repository using bounded batch queries for sale, void, refund, and original-shift state.
- Preserved original sale device as evidence without inventing a same-device correction restriction absent from canonical mutation authority.
- Added evidence-integrity fail-closed checks for amount, currency, scale, tender, scope, evidence mode, void-before-refund ordering, and duplicate evidence.
- Added authorized query service that permits workspace access when at least one correction permission is granted and independently gates each action.
- Added dedicated guarded GET `/pos/sales/corrections` Inertia delivery and explicit `ONEQAY_POS_SALE_CORRECTION_WORKSPACE_ENABLED`, default false.
- Reused existing named POST endpoints `pos.sales.void` and `pos.sales.cash-refund`; no composite correction mutation was added.
- Added Vue/Inertia correction UI with search/filter, state badges, original-device/shift visibility, explicit confirmation, network-uncertainty lockout, and manual authoritative refresh.
- Added no arbitrary refund amount input; full-sale amount remains authoritative from server persistence and mutation contracts.
- Added disposable SQLite regression covering scope isolation, permission narrowing, eligibility transitions, currency/scale preservation, capability gates, and corrupt refund-without-void rejection.
- Left `routes/web.php`, global provider registry, Composer manifest, migrations, and all authoritative operational state unchanged.

### Evidence / Qualification

- Engineering PR: #736, squash merged.
- Parent canonical post-Sprint158 checkpoint: `6a23eefcc10a60d306d969834c594c73a7b7d2bf`.
- Final exact engineering head: `475c4d8fb9a1e17467e71c77fb837351d77e48e2`.
- Complete surfaced exact-head PR-triggered matrix: **successful**.
- Sprint159 regression run `34818568216`: successful.
- M7.1 Application Regression run `34818568151`: successful.
- Governance Required Checks run `34818568451`: successful.
- PHP Foundation Regression run `34818568650`: successful.
- Sprint96 run `34818568084`, Sprint97 run `34818569245`, Sprint126 run `34818568682`, Sprint148 run `34818568207`, Sprint156 run `34818568303`, Sprint157 run `34818568110`, and Sprint158 run `34818568571`: successful.
- Repository-native Product Owner authorization: exact PR #736 and exact engineering head.
- `product-owner-merge-authority`: successful — exact-head authority verified.
- Engineering envelope: 11 paths; SHA-256 `6a5f49136334f99c182220a7db89a7869611be8a5b65a6cd3fe4c73fcc40cf00`.
- Canonical engineering squash commit: `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`.
- Post-merge verification: exactly one squash commit above `6a23eefcc10a60d306d969834c594c73a7b7d2bf`, with exactly the 11 qualified engineering paths.
- Post-Sprint159 reconciliation envelope: six paths; SHA-256 `e7ebee58804975f9d64bc3061c13dccd4c1212877fe73cb75072163e101070dc`.

### Operational boundaries / NO-GO

Sprint159 does not select or persist a durable target, dispatch capability/dependency producers, execute migration #27, provision permissions, produce real target-bound capability/dependency evidence, widen the Final Shift Close runtime allowlist, activate Final Shift Close, grant deployment authority, activate Technical Preview/Production, or activate the updater.

Machine-readable state remains target selection blocked with `selected_target=null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview and Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

### Next position

The next engineering position is **Sprint160 bounded discovery from canonical post-Sprint159**. No Sprint160 objective, implementation, or source envelope is preselected.

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

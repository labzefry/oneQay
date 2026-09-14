# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint159**.

- Canonical engineering commit: `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`
- Engineering PR: #736 — `Sprint159: add operational POS sale correction workspace`
- Final engineering head: `475c4d8fb9a1e17467e71c77fb837351d77e48e2`
- Complete surfaced exact-head PR-triggered qualification: successful
- Sprint159 correction regression run `34818568216`: successful
- M7.1 Application Regression run `34818568151`: successful
- Governance Required Checks run `34818568451`: successful
- PHP Foundation Regression run `34818568650`: successful
- Repository-native `product-owner-merge-authority`: successful for the exact engineering head
- Sprint159 engineering envelope: 11 paths, SHA-256 `6a5f49136334f99c182220a7db89a7869611be8a5b65a6cd3fe4c73fcc40cf00`
- Post-Sprint159 reconciliation envelope: six paths, SHA-256 `e7ebee58804975f9d64bc3061c13dccd4c1212877fe73cb75072163e101070dc`
- Next engineering position: **Sprint160 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint159 — operational POS sale correction workspace

**Purpose / Why:** canonical oneQay already had full-sale void and full CASH refund APIs with authorization, idempotency, inventory reversal, evidence, and audit events, but no guarded operational UI to use them.

**Objective / Gap:** `POS_SALE_CORRECTION_WORKSPACE` — provide a safe correction surface over existing canonical mutation authority without creating a second correction engine.

**What changed:** Sprint159 added validated correction read models, bounded batch persistence reads, exact tenant + organization + outlet scope, original-sale device and shift visibility, independent void/refund permission gating, guarded GET `/pos/sales/corrections` Inertia delivery, explicit workspace feature arming, Vue correction UI, and executable SQLite regression. CASH correction remains two-stage (`COMPLETED → VOIDED → REFUNDED`); MANUAL_EXTERNAL voids remain external-settlement cases. Network ambiguity locks further UI mutation until authoritative refresh. Existing `VoidSale` and `RecordCashRefund` mutation paths remain authoritative.

**Evidence / Qualification:** PR #736; exact engineering head `475c4d8fb9a1e17467e71c77fb837351d77e48e2`; all surfaced PR-triggered exact-head runs successful; authority status successful; engineering squash `e4e6a0f55bbb3b6fd4126df66f4815e281ad0dc4`; engineering envelope SHA-256 `6a5f49136334f99c182220a7db89a7869611be8a5b65a6cd3fe4c73fcc40cf00`.

**Operational boundaries / NO-GO:** no durable target was selected or persisted; migration #27 remains `NOT_EXECUTED`; permissions remain `NONE`; real capability/dependency evidence remains absent; Final Shift Close runtime allowlist remains Local/Test/CI; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

**Next position:** Sprint160 bounded discovery from canonical post-Sprint159; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, exact-head merge controls, and historical-regression preservation;
- bounded POS shift/register, sale/payment/receipt, catalog, inventory baseline, sale void/refund, cash-variance/adjudication, reviewer controls, operational reporting, cashier sale entry, shift start, and sale correction;
- Final Shift Close migration/readiness, selected-target identity, attestation/selection binding, migration DB binding, runtime control-plane hardening, capability/dependency evidence source readiness, permission-provisioning binding, and feature-activation source foundations through Sprint155;
- product-readiness operational surfaces through Sprint159 without changing operational activation authority.

## Operational status remains intentionally gated

| Gate | State |
| --- | --- |
| Migration #27 execution | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |
| Real target-bound capability evidence | `NONE` |
| Dependency-evidence producer dispatch | `NOT_PERFORMED` |
| Real dependency-envelope evidence | `NONE` |
| Final Shift Close runtime allowlist | Local/Test/CI only |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected target | `null` |

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## Technology baseline

- Backend: Laravel / PHP
- Frontend: Vue 3 + Inertia + Vite
- Database: MySQL-compatible
- Architecture: Modular Monolith First, Clean Architecture, DDD
- Authorization: tenant-context first, deny-by-default
- API governance: versioned REST, stable error envelope, correlation ID, tenant context, idempotency, cursor pagination, signed webhooks, replay protection

## Documentation map

- [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) — current project/lifecycle source of truth
- [`CHANGELOG.md`](CHANGELOG.md) — material chronological progress
- [`TASKS.md`](TASKS.md) — current completed/pending workboard
- [`ROADMAP.md`](ROADMAP.md) — future sequencing and gates
- `docs/SPRINT*.md` — detailed historical sprint evidence where materialized
- `ops/final-shift-close/*.json` — machine-readable operational state

## Sprint documentation rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**.

Author by Lab | zefry

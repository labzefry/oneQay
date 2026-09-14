# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint160**.

- Canonical engineering commit: `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`
- Engineering PR: #738 — `Sprint160: add immutable POS sale history detail workspace`
- Final engineering head: `d159aa26c748c5a624f0f71fe6c135f0d4f36be9`
- Complete surfaced exact-head PR-triggered qualification: successful
- Sprint160 regression run `34847797273`: successful
- M7.1 Application Regression run `34847797253`: successful
- Governance Required Checks run `34847797404`: successful
- PHP Foundation Regression run `34847797433`: successful
- Repository-native `product-owner-merge-authority`: successful for the exact engineering head
- Sprint160 engineering envelope: 10 paths, SHA-256 `f60bd3698cbc28cfccdf8b79c446e5e138203246afdb16aaea1c5637aa327181`
- Post-Sprint160 reconciliation envelope: six paths, SHA-256 `388c671587b1d0e21206260c5f0003fb215d606df494eaa528b1e6d839de7847`
- Next engineering position: **Sprint161 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint160 — immutable POS sale history and receipt detail

**Purpose / Why:** oneQay already persisted immutable sale headers and line items, but operational reporting exposed only aggregates/recent headers and correction operations. Authorized users had no line-level historical receipt drill-down.

**Objective / Gap:** `POS_SALE_HISTORY_DETAIL_WORKSPACE` — provide a read-only transaction history and exact canonical receipt lookup over existing persistence without creating another transaction engine or migration.

**What changed:** Sprint160 added exact tenant + organization + outlet scoped latest-50 history, exact `sale-<24 hex>` lookup, immutable `oneqay_pos_sale_lines` receipt detail, fail-closed receipt arithmetic and correction-evidence validation, legacy nullable shift preservation, existing reporting-permission reuse, guarded reporting delivery, explicit history feature arming, Vue/Inertia detail UI, precision-safe atomic string rendering, and executable SQLite regression. Mutable current catalog names are intentionally not reconstructed as historical receipt evidence.

**Evidence / Qualification:** PR #738; exact engineering head `d159aa26c748c5a624f0f71fe6c135f0d4f36be9`; all surfaced PR-triggered exact-head runs successful; authority status successful; engineering squash `e6ef6e77d8a2d0dea16d7c17dde78bece904b78b`; engineering envelope SHA-256 `f60bd3698cbc28cfccdf8b79c446e5e138203246afdb16aaea1c5637aa327181`.

**Operational boundaries / NO-GO:** no durable target was selected or persisted; migration #27 remains `NOT_EXECUTED`; permissions remain `NONE`; real capability/dependency evidence remains absent; Final Shift Close runtime allowlist remains Local/Test/CI; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

**Next position:** Sprint161 bounded discovery from canonical post-Sprint160; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, exact-head merge controls, and historical-regression preservation;
- bounded POS shift/register, sale/payment/receipt, catalog, inventory baseline, sale void/refund, cash-variance/adjudication, reviewer controls, operational reporting, cashier sale entry, shift start, sale correction, and immutable sale history/detail;
- Final Shift Close migration/readiness, selected-target identity, attestation/selection binding, migration DB binding, runtime control-plane hardening, capability/dependency evidence source readiness, permission-provisioning binding, and feature-activation source foundations through Sprint155;
- product-readiness operational surfaces through Sprint160 without changing operational activation authority.

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

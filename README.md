# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint156**.

- Canonical engineering commit: `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`
- Engineering PR: #730 — `Sprint156: add tenant-scoped POS operational sales reporting`
- Final engineering head: `5e460d1c7c5174cc831106ade3e3fa6309acba4d`
- Complete exact-head PR-triggered qualification: successful
- Sprint156 reporting regression run `34813484159`: successful
- M7.1 Application Regression run `34813484204`: successful
- Governance Required Checks run `34813484278`: successful
- PHP Foundation Regression run `34813484276`: successful
- Repository-native `product-owner-merge-authority`: successful for the exact engineering head
- Sprint156 engineering envelope: 24 paths, SHA-256 `34c6dab2c898ddd9133aaa6d5413ca7b345127020d8f04fe54f861a4d1a5e79c`
- Post-Sprint156 reconciliation envelope: six paths, SHA-256 `adba5b23ef33aeb360ebb4090b3f848fc2a3704807a60026c1344b2e0d1a54f4`
- Next engineering position after reconciliation: **Sprint157 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint156 — POS operational sales reporting

**Purpose / Why:** post-Sprint155 discovery showed the remaining Final Shift Close blockers were operational/runtime prerequisites rather than another useful source abstraction. Canonical product scope still required reporting, analytics, and a BI-like operational surface.

**Objective / Gap:** `POS_OPERATIONAL_SALES_REPORTING` — provide a cohesive read-only tenant + organization + outlet scoped sales summary using canonical POS persistence.

**What changed:** Sprint156 added the operational summary application contract, Laravel read repository, authorized query service, guarded HTTP delivery, dedicated reporting service provider, Vue/Inertia dashboard, fail-closed reporting configuration, and executable SQLite regression. Currency and currency-scale boundaries are preserved. Historical workflow ownership was narrowed only where exact-head CI proved stale successor freezing; substantive regressions remain active. Global provider registration remains canonical, and Sprint148 qualification concurrency is isolated per exact head.

**Evidence / Qualification:** PR #730; exact engineering head `5e460d1c7c5174cc831106ade3e3fa6309acba4d`; all PR-triggered exact-head runs successful; authority status successful; engineering squash `259cc00037ee0d3fb909cbcf2c87d39ffb26f9b9`; engineering envelope SHA-256 `34c6dab2c898ddd9133aaa6d5413ca7b345127020d8f04fe54f861a4d1a5e79c`.

**Operational boundaries / NO-GO:** no durable target was selected or persisted; migration #27 remains `NOT_EXECUTED`; permissions remain `NONE`; real capability/dependency evidence remains absent; runtime allowlist remains Local/Test/CI; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

**Next position:** after reconciliation squash merge and verification, Sprint157 bounded discovery only; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, exact-head merge controls, and historical-regression preservation;
- bounded POS shift/register, sale/payment/receipt, catalog, cash-variance, adjudication, reviewer controls, and operational sales reporting;
- Final Shift Close migration/readiness, selected-target identity, attestation/selection binding, migration DB binding, runtime control-plane hardening, capability/dependency evidence source readiness, permission-provisioning binding, and feature-activation source foundations through Sprint155;
- Sprint156 product-readiness pivot to a tenant-scoped read-only operational reporting surface without changing operational authority.

## Operational status remains intentionally gated

| Gate | State |
| --- | --- |
| Migration #27 execution | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |
| Real target-bound capability evidence | `NONE` |
| Dependency-evidence producer dispatch | `NOT_PERFORMED` |
| Real dependency-envelope evidence | `NONE` |
| Runtime allowlist | Local/Test/CI only |
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

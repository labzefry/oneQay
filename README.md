# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint158**.

- Canonical engineering commit: `d6eb8f7f359584130deea9ec0ed3572add3c05aa`
- Engineering PR: #734 — `Sprint158: add operational POS shift-start workspace`
- Final engineering head: `1e2a0ae959a88870ae4728f46f07b08ae0c08a2c`
- Complete exact-head PR-triggered qualification: successful
- Sprint158 shift-start regression run `34816888058`: successful
- M7.1 Application Regression run `34816888381`: successful
- Governance Required Checks run `34816888368`: successful
- PHP Foundation Regression run `34816887952`: successful
- Repository-native `product-owner-merge-authority`: successful for the exact engineering head
- Sprint158 engineering envelope: 11 paths, SHA-256 `3828b5914b64b4862ce4d39ff037261b796c232e5ac7c6fb001913a096ac1666`
- Post-Sprint158 reconciliation envelope: six paths, SHA-256 `0257dde337eee65e156c49f59b54a61506b47866babb2c68a8bcc3a1a2e3321f`
- Next engineering position: **Sprint159 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint158 — operational POS shift-start workspace

**Purpose / Why:** Sprint157 made sale entry operational, but the cashier still required an active exact-device shift. Canonical shift-opening and opening-cash mutation contracts already existed, yet no operational start-of-register page connected the operator to those contracts.

**Objective / Gap:** `POS_SHIFT_START_WORKSPACE` — provide a guarded, resumable start-of-shift surface without creating another mutation engine.

**What changed:** Sprint158 added a scoped shift-start snapshot, read-only repository, authorized query service, dedicated fail-closed provider/controller, GET `/pos/shift-start` Inertia delivery, explicit workspace feature flag, Vue three-state flow, and executable SQLite regression. It requires both existing shift-start permissions, reads exact tenant + organization + outlet + device state, reuses the canonical `/pos/shifts/open` and `/pos/shifts/opening-cash` mutation routes, preserves partial completion, and performs no hidden retry.

**Evidence / Qualification:** PR #734; exact engineering head `1e2a0ae959a88870ae4728f46f07b08ae0c08a2c`; all PR-triggered exact-head runs successful; authority status successful; engineering squash `d6eb8f7f359584130deea9ec0ed3572add3c05aa`; engineering envelope SHA-256 `3828b5914b64b4862ce4d39ff037261b796c232e5ac7c6fb001913a096ac1666`.

**Operational boundaries / NO-GO:** no durable target was selected or persisted; migration #27 remains `NOT_EXECUTED`; permissions remain `NONE`; real capability/dependency evidence remains absent; Final Shift Close runtime allowlist remains Local/Test/CI; feature activation remains `INACTIVE`; deployment authority remains `NOT_GRANTED`; Technical Preview/Production remain `NOT_AUTHORIZED`; updater remains `INACTIVE`.

**Next position:** Sprint159 bounded discovery from canonical post-Sprint158; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, exact-head merge controls, and historical-regression preservation;
- bounded POS shift/register opening, opening-cash evidence, sale/payment/receipt, catalog, inventory baseline, cash-variance/adjudication, reviewer controls, operational reporting, cashier sale entry, and operational shift start;
- Final Shift Close migration/readiness, selected-target identity, attestation/selection binding, migration DB binding, runtime control-plane hardening, capability/dependency evidence source readiness, permission-provisioning binding, and feature-activation source foundations through Sprint155;
- Sprint156 reporting, Sprint157 cashier usability, and Sprint158 shift-start usability without changing operational activation authority.

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

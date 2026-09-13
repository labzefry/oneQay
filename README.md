# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint154**.

- Canonical engineering commit: `5349acbffd1c90087d37a1a0f74ce0ffd7e87773`
- Latest engineering PR: #726 — `Sprint154: materialize feature activation executor source foundation`
- Final engineering head: `132dbd0048ad40248efe093e22f276b487890487`
- Exact-head pull-request qualification: **39/39 workflow runs successful**
- Repository-native Product Owner merge-authority run: `34772516177` successful
- Final Sprint154 engineering envelope: seven paths, SHA-256 `5dcf1fcd0b638ed9ec3d311947055a2b2c96c74d8e8fb5b74ff1f1b96bd1296f`
- Initial Sprint154 source-foundation envelope: six paths, SHA-256 `35499fbb12404b3ab5f25de924d4528060f4cf4eb362091b116e5faf3c3766c3`
- Post-Sprint154 reconciliation envelope: six paths, SHA-256 `ba0208b79fb9790435dcc968fe85e05aece4a980cb891e4c2e946efcbc65650f`
- Activation executor source foundation: `MATERIALIZED_SOURCE_ONLY`
- Dispatchable feature-activation executor: `NOT_IMPLEMENTED`
- Configuration-mutation transport: `NOT_IMPLEMENTED`
- Activation executor dispatch: `NOT_PERFORMED`
- Runtime allowlist remains `local/test/ci`; runtime allowlist change remains `NOT_IMPLEMENTED`
- Next engineering position after reconciliation: **Sprint155 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint154 description

**Purpose / Why:** canonical post-Sprint153 still lacked the selected-target-bound Final Shift Close feature-activation executor required by the downstream ordering. Discovery also proved there was no repository-native concrete activation transport to reuse safely.

**Objective / Gap:** materialize a source-only deterministic execution-plan foundation that requires exact selected-target identity, migration #27 execution on that target, same-target permission provisioning, full durable dependency-envelope qualification, and separate exact feature-activation authority, without inventing transport or performing activation.

**What changed:** Sprint154 added `FinalShiftCloseFeatureActivationExecutionPlan.php`, executable regression, machine-readable source-foundation contract, downstream readiness state, active Sprint154 regression, and detailed documentation. Exact-head CI run `34772115090` proved Sprint116 still asserted the older feature-activation eligibility state, so the final engineering envelope expanded by exactly one historical workflow path and Sprint116 became successor-compatible.

**Evidence / Qualification:** PR #726; final head `132dbd0048ad40248efe093e22f276b487890487`; 39/39 exact-head CI successful; authority run `34772516177`; canonical engineering squash `5349acbffd1c90087d37a1a0f74ce0ffd7e87773`; final seven-path engineering SHA-256 `5dcf1fcd0b638ed9ec3d311947055a2b2c96c74d8e8fb5b74ff1f1b96bd1296f`.

**Operational boundaries / NO-GO:** No selected target was persisted, no activation executor was dispatched, no concrete configuration transport was implemented, migration #27 remains unexecuted, permissions remain `NONE`, real capability/dependency evidence remains absent, runtime allowlist remains `local/test/ci`, feature activation remains `INACTIVE`, deployment authority remains `NOT_GRANTED`, Technical Preview/Production remain `NOT_AUTHORIZED`, and updater remains `INACTIVE`.

**Next position:** after reconciliation is squash merged and verified, Sprint155 bounded discovery only; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, catalog, cash-variance, adjudication, and reviewer-control foundations;
- Final Shift Close migration/readiness, selected-target identity, attestation/selection binding, migration DB binding, runtime control-plane hardening, capability-evidence identity/producer readiness, full dependency-envelope qualification, deterministic dependency-evidence construction, trusted dependency-evidence producer source, selected-target-bound permission-provisioning hardening, and feature-activation executor source foundation through Sprint154.

## Operational status remains intentionally gated

| Gate | State |
| --- | --- |
| Migration #27 execution | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |
| Real target-bound capability evidence | `NONE` |
| Dependency-evidence producer dispatch | `NOT_PERFORMED` |
| Real dependency-envelope evidence | `NONE` |
| Activation executor source foundation | `MATERIALIZED_SOURCE_ONLY` |
| Dispatchable feature-activation executor | `NOT_IMPLEMENTED` |
| Configuration-mutation transport | `NOT_IMPLEMENTED` |
| Activation executor dispatch | `NOT_PERFORMED` |
| Runtime allowlist change | `NOT_IMPLEMENTED` |
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
- `docs/SPRINT*.md` — detailed sprint descriptions and evidence
- `ops/final-shift-close/*.json` — machine-readable operational state

## Sprint documentation rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**.

Author by Lab | zefry

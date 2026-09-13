# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint151**.

- Canonical engineering commit: `68b8362f326e56cfec478f0275b6d29ed0f54dec`
- Latest engineering PR: #720 — `Sprint151 source foundation`
- Final engineering head: `f41e32bc44e0ee0346f5efd15d54f6af21b8a19a`
- Sprint151 pull-request qualification: **36/36 workflow runs successful** on the exact final engineering head
- Sprint151 repository-native Product Owner merge-authority run: `34764011476` successful
- Sprint151 engineering envelope: five paths, SHA-256 `ef000ec9172dcee1f08a6c8ea9149fb957a307e7929eaaa42c5d4b08bad94c54`
- Post-Sprint151 reconciliation envelope: six paths, SHA-256 `82d72910aaa4409113cfbb8b2b7f326daa51c205d7a6bfefa90ad5624604fb25`
- Dependency-envelope qualifier: `MATERIALIZED_SOURCE_ONLY`
- Dependency-evidence source foundation: `MATERIALIZED_SOURCE_ONLY`
- Dispatchable dependency-evidence producer: `NOT_IMPLEMENTED`
- Real dependency-envelope evidence: `NONE`
- Runtime allowlist remains `local/test/ci`; runtime allowlist change remains `NOT_IMPLEMENTED`
- Next engineering position after reconciliation: **Sprint152 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint151 description

**Purpose / Why:** Sprint150 qualified the complete nine-component durable-runtime dependency envelope but intentionally left the source responsible for deterministically constructing that evidence shape unimplemented.

**Objective / Gap:** Materialize a source-only application foundation that accepts only the exact nine canonical dependency observations, binds them to exact selected-target/runtime/source/artifact and Sprint148 capability-evidence identity, rejects synthetic or malformed evidence, and requires Sprint150 qualification before output is accepted.

**What changed:** Sprint151 added `FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer`, a machine-readable source-foundation contract, downstream-readiness integration, an exact-head workflow, and six-section sprint documentation. The published scope deliberately does **not** include a dispatchable dependency-evidence producer or operational transport.

**Evidence / Qualification:** PR #720; final engineering head `f41e32bc44e0ee0346f5efd15d54f6af21b8a19a`; 36/36 exact-head CI successful; repository-native authority run `34764011476`; canonical engineering commit `68b8362f326e56cfec478f0275b6d29ed0f54dec`; five-path engineering envelope SHA-256 `ef000ec9172dcee1f08a6c8ea9149fb957a307e7929eaaa42c5d4b08bad94c54`.

**Operational boundaries / NO-GO:** No target selection/persistence, producer dispatch, real capability/dependency evidence, runtime allowlist widening, migration execution, permission provisioning, feature activation, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

**Next position:** After reconciliation is squash merged and verified, Sprint152 bounded discovery only; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, and catalog foundations;
- JRN-010 expected-cash, sale-to-shift binding, cash variance, explanation/adjudication, maker-checker, and reviewer-control foundations;
- Final Shift Close migration/readiness, selected-target identity, attestation/selection binding, capability-evidence identity/producer readiness, full dependency-envelope qualification, and dependency-evidence construction source foundation through Sprint151;
- runtime-control-plane, authenticated HTTP, throttle/rejection, route/action/budget/metadata identity hardening.

## Operational status remains intentionally gated

| Gate | State |
| --- | --- |
| Migration #27 execution | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected target | `null` |
| Capability-evidence producer | `MATERIALIZED_NOT_DISPATCHED` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |
| Real target-bound capability evidence | `NONE` |
| Dependency-envelope qualifier | `MATERIALIZED_SOURCE_ONLY` |
| Dependency-evidence source foundation | `MATERIALIZED_SOURCE_ONLY` |
| Dispatchable dependency-evidence producer | `NOT_IMPLEMENTED` |
| Real dependency-envelope evidence | `NONE` |
| Runtime allowlist change | `NOT_IMPLEMENTED` |

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

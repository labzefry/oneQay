# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest closed **engineering** sprint is **Sprint149**.

- Canonical engineering commit: `662a892c3269d945579594da03121bce960c9074`
- Latest engineering PR: #716 — `Sprint149: durable runtime target-bound capability evidence producer`
- Final engineering head: `449832afd18117c58fb034ad23c9d4217bd3cd1e`
- Sprint149 pull-request qualification: **34/34 workflow runs successful** on the exact final engineering head
- Sprint149 repository-native Product Owner merge-authority run: `34759693893` successful
- Sprint149 engineering envelope: eight paths, SHA-256 `8acea0b1cc826dedbe2dd55f38b4aa24fd4854a96ee32dde9f7552d22d239a91`
- Post-Sprint149 reconciliation envelope: six paths, SHA-256 `f97c59e253a6d3d47ff84f026690e700c3c85c2b9e8baf9ec00bfb57c1663c7e`
- Next engineering position after reconciliation: **Sprint150 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint149 description

**Purpose / Why:** Sprint148 established exact target-bound capability-evidence identity qualification but intentionally left real capability evidence absent and the trusted producer unimplemented.

**Objective / Gap:** Materialize a trusted protected-environment producer source that can, only in a future separately authorized execution, obtain capability-specific observations, recompute Sprint110/Sprint111 identity, bind all four Sprint148 evidence kinds to the exact target, and require final Sprint148 qualification.

**What changed:** Sprint149 added `FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer`, executable positive/fail-closed regression coverage, a protected-environment source-only producer workflow, a machine-readable producer contract, and downstream-readiness integration. Exact-head CI also proved historical Sprint148 froze the successor-owned producer-materialization state, so that historical workflow alone was made successor-compatible while retaining its evidence-binding and NO-GO invariants.

**Evidence / Qualification:** PR #716; final engineering head `449832afd18117c58fb034ad23c9d4217bd3cd1e`; 34/34 exact-head CI successful; repository-native authority run `34759693893`; canonical engineering commit `662a892c3269d945579594da03121bce960c9074`; eight-path engineering envelope SHA-256 `8acea0b1cc826dedbe2dd55f38b4aa24fd4854a96ee32dde9f7552d22d239a91`.

**Operational boundaries / NO-GO:** Producer source was materialized but **not dispatched**. No real capability evidence, target persistence/activation, migration execution, permission provisioning, feature activation, runtime allowlist change, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

**Next position:** After this reconciliation is squash merged and verified, Sprint150 bounded discovery only; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, and catalog foundations;
- JRN-010 expected-cash, immutable sale-to-shift binding, cash variance, explanation/adjudication, maker-checker, and reviewer-authorization foundations;
- Final Shift Close source/readiness work including source-only migration #27 and application/runtime readiness contracts;
- durable-runtime readiness, exact selected-target identity, producer/ingestion/persistence/binding readiness, target-bound capability-evidence qualification, and trusted capability-evidence producer source readiness through Sprint149;
- runtime-binding manifest, DB-binding attestation, token-policy, delivery-gate, authenticated HTTP, throttle, rejection hardening, route/action identity, exact throttle-budget identity, and canonical throttle-rejection metadata qualification.

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
| Runtime allowlist change | `NOT_IMPLEMENTED` |
| Real target-bound capability evidence | `NONE` |
| Trusted capability-evidence producer | `MATERIALIZED_NOT_DISPATCHED` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

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

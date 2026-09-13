# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint150**.

- Canonical engineering commit: `d743a054092231729fa0e33cd34538f9d1e81787`
- Latest engineering PR: #718 — `Sprint150: qualify selected runtime durable dependency envelope`
- Final engineering head: `7102733080735f4591bb17df76bdec928975e826`
- Sprint150 pull-request qualification: **35/35 workflow runs successful** on the exact final engineering head
- Sprint150 repository-native Product Owner merge-authority run: `34761524119` successful
- Sprint150 engineering envelope: six paths, SHA-256 `9f261895ab0373af5d6d385c3db3f93e5e510061b8e843e4110ecce992d9a0e6`
- Post-Sprint150 reconciliation envelope: six paths, SHA-256 `578765b03de34048670791017fcffe8680b65e6bede9f57d22affb255f5ee43f`
- Dependency-envelope qualifier: `MATERIALIZED_SOURCE_ONLY`
- Real dependency-envelope evidence: `NONE`
- Dependency-evidence producer: `NOT_IMPLEMENTED`
- Runtime allowlist remains `local/test/ci`; runtime allowlist change remains `NOT_IMPLEMENTED`
- Next engineering position after reconciliation: **Sprint151 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint150 description

**Purpose / Why:** Sprint107 established the nine-component runtime dependency inventory and blocked allowlist widening, while Sprint148/Sprint149 established exact target-bound capability evidence and producer source readiness. The full dependency envelope still lacked qualification against the exact selected runtime identity.

**Objective / Gap:** Require all nine Sprint107 components to be independently verified, secret-free, non-synthetic, SHA-256 identified, and bound to the exact Sprint111 selected runtime plus the exact Sprint148 target/capability-evidence identity before any future runtime-allowlist eligibility.

**What changed:** Sprint150 added `FinalShiftCloseDurableRuntimeDependencyEnvelope`, executable positive/fail-closed regression coverage, a machine-readable dependency-envelope qualification contract, downstream-readiness ordering, and an exact-head workflow. No historical compatibility expansion was required.

**Evidence / Qualification:** PR #718; final engineering head `7102733080735f4591bb17df76bdec928975e826`; 35/35 exact-head CI successful; repository-native authority run `34761524119`; canonical engineering commit `d743a054092231729fa0e33cd34538f9d1e81787`; six-path engineering envelope SHA-256 `9f261895ab0373af5d6d385c3db3f93e5e510061b8e843e4110ecce992d9a0e6`.

**Operational boundaries / NO-GO:** No selected target, producer dispatch, real dependency evidence, runtime allowlist widening, migration execution, permission provisioning, feature activation, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

**Next position:** After reconciliation is squash merged and verified, Sprint151 bounded discovery only; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, and catalog foundations;
- JRN-010 expected-cash, immutable sale-to-shift binding, cash variance, explanation/adjudication, maker-checker, and reviewer-authorization foundations;
- Final Shift Close migration/readiness, exact selected-target identity, attestation/selection binding, capability-evidence identity/producer readiness, and exact full dependency-envelope qualification through Sprint150;
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
| Real dependency-envelope evidence | `NONE` |
| Dependency-evidence producer | `NOT_IMPLEMENTED` |
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

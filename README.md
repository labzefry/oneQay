# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with a Modular Monolith First architecture, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest closed **engineering** sprint is **Sprint148**.

- Canonical engineering commit: `7a07a3163842e60332ccd3e3780d4e970280d46c`
- Latest engineering PR: #714 — `Sprint148: durable runtime target-bound capability evidence identity binding`
- Final engineering head: `eba8687297e7b82bf5adfcd770d86233139b0454`
- Sprint148 pull-request qualification: **33/33 workflow runs successful** on the exact final engineering head
- Sprint148 repository-native Product Owner merge-authority run: `34756294306` successful
- Sprint148 engineering envelope: seven paths, SHA-256 `f7d9cfb173b54ac863cc70f10b9ae3df2f6715a4abdae0c7c32cbdb399bda5a6`
- Sprint148 merge: squash merged with expected-head guard
- Post-Sprint148 reconciliation envelope: six paths, SHA-256 `5c315771e9777b9d5a5b428a206c7cec711e14a7851a83c685a5a5cfb378dfc5`
- Next engineering position after reconciliation: **Sprint149 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint148 description

**Purpose / Why:** Sprint110 established durable-runtime readiness capability claims and Sprint111 bound qualified readiness to an exact selected target, but those booleans were not capability-specific evidence tied to that target identity.

**Objective / Gap:** Require future evidence for authenticated configuration mutation, read-before/write/read-after verification, non-mutating health attestation, and verified rollback to be individually `VERIFIED`, secret-free, digest-qualified, and bound to the exact Sprint111 target identity and selection fingerprint while authority remains `NOT_GRANTED`, feature remains `INACTIVE`, and the runtime allowlist remains unchanged.

**What changed:** Sprint148 added `FinalShiftCloseDurableRuntimeCapabilityEvidence`, executable positive/fail-closed regression coverage, a machine-readable capability-evidence binding contract, post-selection readiness integration, and an exact-head workflow. Exact-head CI also proved historical Sprint116 still locked its original full envelope, so only that workflow was converted to successor-compatible historical regression while preserving its owned invariants.

**Evidence / Qualification:** PR #714; final engineering head `eba8687297e7b82bf5adfcd770d86233139b0454`; 33/33 exact-head CI successful; repository-native authority run `34756294306`; canonical engineering commit `7a07a3163842e60332ccd3e3780d4e970280d46c`; seven-path engineering envelope SHA-256 `f7d9cfb173b54ac863cc70f10b9ae3df2f6715a4abdae0c7c32cbdb399bda5a6`.

**Operational boundaries / NO-GO:** No real target selection or activation, capability-evidence production/dispatch, migration execution, permission provisioning, feature activation, runtime-token provisioning, runtime allowlist change, operational manifest/DB invocation, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

**Next position:** After this reconciliation is squash merged and verified, Sprint149 bounded discovery only; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, and catalog foundations;
- JRN-010 expected-cash, immutable sale-to-shift binding, cash variance, explanation/adjudication, maker-checker, and reviewer-authorization foundations;
- Final Shift Close source/readiness work including source-only migration #27 and application/runtime readiness contracts;
- durable-runtime readiness, exact selected-target identity, producer/ingestion/persistence/binding readiness, and target-bound capability-evidence identity qualification;
- runtime-binding manifest, DB-binding attestation, token-policy, delivery-gate, authenticated HTTP, throttle, rejection hardening, route/action identity, exact throttle-budget identity, and canonical throttle-rejection metadata identity qualification through Sprint147;
- target-bound durable-runtime capability evidence qualification through Sprint148.

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
| Trusted capability-evidence producer | `NOT_IMPLEMENTED` |

Machine-readable operational authority:

- `ops/final-shift-close/STATE.json`
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

## Technology baseline

- Backend: Laravel / PHP
- Frontend: Vue 3 + Inertia + Vite
- Database: MySQL-compatible
- Architecture: Modular Monolith First, Clean Architecture, DDD
- Authorization: tenant-context first, deny-by-default
- API governance: versioned REST, stable error envelope, correlation ID, tenant context, idempotency, cursor pagination, signed webhooks and replay protection

## Documentation map

- [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) — current project/lifecycle source of truth
- [`CHANGELOG.md`](CHANGELOG.md) — material chronological progress
- [`TASKS.md`](TASKS.md) — current completed/pending workboard
- [`ROADMAP.md`](ROADMAP.md) — future sequencing and gates
- `docs/SPRINT*.md` — detailed sprint descriptions and evidence
- `ops/final-shift-close/*.json` — machine-readable operational state

## Sprint documentation rule

Every material sprint must record **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**. Root documents stay concise; the per-sprint document retains full evidence.

Author by Lab | zefry

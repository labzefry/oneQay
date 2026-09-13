# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint153**.

- Canonical engineering commit: `e42ea89fca2b3974e7fa9b0a95fa31a901e9840c`
- Latest engineering PR: #724 — `Sprint153: durable runtime dependency envelope evidence producer`
- Final engineering head: `44a17e4590df58472114aad547dd1cd3087f8e96`
- Sprint153 pull-request qualification: **38/38 workflow runs successful** on the exact final engineering head
- Sprint153 repository-native Product Owner merge-authority run: `34767471668` successful
- Sprint153 engineering envelope: six paths, SHA-256 `7269a0e5f8927c2763411d0700b0f456cdcb0781f1d198179966a97a7b934a79`
- Post-Sprint153 reconciliation envelope: six paths, SHA-256 `6a0fd4267f940c02d23e95ce4085e89cdf25a52186d95619e1b1da89488dfc46`
- Dependency-evidence source foundation: `MATERIALIZED_SOURCE_ONLY`
- Trusted dependency-evidence producer source: `MATERIALIZED_NOT_DISPATCHED`
- Dependency-evidence producer dispatch: `NOT_PERFORMED`
- Real dependency-envelope evidence: `NONE`
- Runtime allowlist remains `local/test/ci`; runtime allowlist change remains `NOT_IMPLEMENTED`
- Next engineering position after reconciliation: **Sprint154 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint153 description

**Purpose / Why:** Sprint150 qualified the exact nine-component target-bound dependency envelope and Sprint151 provided deterministic evidence construction, but canonical post-Sprint152 still lacked the trusted protected-environment producer transport needed to obtain authenticated dependency observations and publish a future qualified evidence bundle.

**Objective / Gap:** Materialize a source-only producer that requires canonical `SELECTED_NOT_AUTHORIZED`, exact trusted Sprint149 capability-producer provenance, authenticated HTTPS dependency observations, Sprint151 construction, and Sprint150 final qualification, while accepting no caller-selected target identity.

**What changed:** Sprint153 added the protected dependency-envelope evidence producer workflow, producer contract, downstream source-readiness state, exact-head regression, and six-section documentation. Exact-head CI proved one stale Sprint151 canonical successor-state assertion; the historical Sprint151 extension remains unchanged while its workflow now accepts the materialized-not-dispatched successor state only when dispatch remains `NOT_PERFORMED`.

**Evidence / Qualification:** PR #724; final engineering head `44a17e4590df58472114aad547dd1cd3087f8e96`; 38/38 exact-head CI successful; repository-native authority run `34767471668`; canonical engineering commit `e42ea89fca2b3974e7fa9b0a95fa31a901e9840c`; six-path engineering envelope SHA-256 `7269a0e5f8927c2763411d0700b0f456cdcb0781f1d198179966a97a7b934a79`.

**Operational boundaries / NO-GO:** The dependency producer was not dispatched. No target persistence, real dependency/capability evidence production, migration execution, permission mutation, runtime allowlist widening, feature activation, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

**Next position:** After reconciliation is squash merged and verified, Sprint154 bounded discovery only; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, catalog, cash-variance, adjudication, and reviewer-control foundations;
- Final Shift Close migration/readiness, selected-target identity, attestation/selection binding, migration DB binding, runtime control-plane hardening, capability-evidence identity/producer readiness, full dependency-envelope qualification, deterministic dependency-evidence construction, trusted dependency-evidence producer source, and selected-target-bound permission provisioning source through Sprint153.

## Operational status remains intentionally gated

| Gate | State |
| --- | --- |
| Migration #27 execution | `NOT_EXECUTED` |
| Permission selected-target binding source | `MATERIALIZED_NOT_DISPATCHED` |
| Permission selected-target binding evidence | `NONE` |
| Permission provisioning | `NONE` |
| Capability-evidence producer | `MATERIALIZED_NOT_DISPATCHED` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |
| Real target-bound capability evidence | `NONE` |
| Dependency-evidence source foundation | `MATERIALIZED_SOURCE_ONLY` |
| Dependency-evidence producer | `MATERIALIZED_NOT_DISPATCHED` |
| Dependency-evidence producer dispatch | `NOT_PERFORMED` |
| Real dependency-envelope evidence | `NONE` |
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

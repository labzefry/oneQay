# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint152**.

- Canonical engineering commit: `c6abc9356ad329c1a2273a71a4a8ca0e50822825`
- Latest engineering PR: #722 — `Sprint152: bind permission provisioning to selected target database`
- Final engineering head: `70c30e407271c0e78439143230159f7b1c63f291`
- Sprint152 pull-request qualification: **38/38 workflow runs successful** on the exact final engineering head
- Sprint152 repository-native Product Owner merge-authority run: `34765603014` successful
- Sprint152 engineering envelope: seven paths, SHA-256 `7ed9c7cc6da5f03f73fdbd3ef18f4315896b95832331c2c2d5e2fa6bb2151590`
- Post-Sprint152 reconciliation envelope: six paths, SHA-256 `bd3613c19baad6d74925a78dd72dcaf8121fbec842ace3e9279beaf8ecf8b971`
- Permission selected-target binding source: `MATERIALIZED_NOT_DISPATCHED`
- Permission selected-target binding evidence: `NONE`
- Permission provisioning: `NONE`
- Dependency-evidence source foundation: `MATERIALIZED_SOURCE_ONLY`
- Dispatchable dependency-evidence producer: `NOT_IMPLEMENTED`
- Real dependency-envelope evidence: `NONE`
- Runtime allowlist remains `local/test/ci`; runtime allowlist change remains `NOT_IMPLEMENTED`
- Next engineering position after reconciliation: **Sprint153 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint152 description

**Purpose / Why:** The existing `pos.shift.close` provisioning executor could use protected DB credentials without proving that those credentials addressed the exact durable runtime selected by the canonical selection chain. Sprint118 already owns trusted selected-target DB-binding evidence, so Sprint152 reuses that evidence rather than introducing a duplicate producer.

**Objective / Gap:** Require exact canonical `SELECTED_NOT_AUTHORIZED` target identity, exact Sprint118 binding run/artifact/status evidence, and immediate pre-mutation `DATABASE()` / `@@hostname` / `@@port` readback whose canonical SHA-256 matches trusted evidence via `hash_equals()`.

**What changed:** Sprint152 hardened the existing permission-provisioning workflow, added a machine-readable selected-target binding contract, downstream readiness state, exact-head regression, and six-section sprint documentation. CI-proven Sprint104 and Sprint116 historical compatibility corrections preserved their original security and NO-GO semantics while removing stale successor assumptions.

**Evidence / Qualification:** PR #722; final engineering head `70c30e407271c0e78439143230159f7b1c63f291`; 38/38 exact-head CI successful; repository-native authority run `34765603014`; canonical engineering commit `c6abc9356ad329c1a2273a71a4a8ca0e50822825`; seven-path engineering envelope SHA-256 `7ed9c7cc6da5f03f73fdbd3ef18f4315896b95832331c2c2d5e2fa6bb2151590`.

**Operational boundaries / NO-GO:** No target persistence, binding producer dispatch, migration execution, permission mutation, capability/dependency evidence production, runtime allowlist widening, feature activation, deployment/release, Technical Preview activation, Production activation, or updater activation occurred.

**Next position:** After reconciliation is squash merged and verified, Sprint153 bounded discovery only; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, catalog, cash-variance, adjudication, and reviewer-control foundations;
- Final Shift Close migration/readiness, selected-target identity, attestation/selection binding, migration DB binding, runtime control-plane hardening, capability-evidence identity/producer readiness, full dependency-envelope qualification, dependency-evidence source foundation, and selected-target-bound permission provisioning source through Sprint152.

## Operational status remains intentionally gated

| Gate | State |
| --- | --- |
| Migration #27 execution | `NOT_EXECUTED` |
| Permission selected-target binding source | `MATERIALIZED_NOT_DISPATCHED` |
| Permission selected-target binding evidence | `NONE` |
| Permission provisioning | `NONE` |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected target | `null` |
| Capability-evidence producer | `MATERIALIZED_NOT_DISPATCHED` |
| Real target-bound capability evidence | `NONE` |
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

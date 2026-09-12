# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with a Modular Monolith First architecture, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest closed **engineering** sprint is **Sprint138**.

- Canonical engineering commit: `7f562ea48a0255b7e9803f6b268bd00a7e3b3dcf`
- Latest engineering PR: #692 — `Sprint138: canonical control-plane authenticated HTTP throttle regression`
- Sprint138 pull-request qualification: **22/22 workflow runs successful** on the exact authorized head
- Sprint138 merge: squash merged with exact-head guard
- Next engineering position: **Sprint139 bounded discovery**, not yet treated as started or complete

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

> Older sprint/status sections in specialized handbooks are historical provenance. They do not override `PROJECT_MANIFEST.md` or the machine-readable operational state under `ops/final-shift-close/`.

## What the repository has reached

Material canonical progress includes:

- core modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- POS foundations for shift/register opening, sale completion/payment/receipt evidence, and catalog preparation;
- JRN-010 prerequisite work covering expected cash, immutable sale-to-shift binding, cash variance, explanation/adjudication, maker-checker, and reviewer authorization;
- Final Shift Close source/readiness work including source-only migration #27 and application/runtime readiness contracts;
- runtime-binding manifest, database-binding attestation, authorization, token-policy, middleware/controller, delivery-gate, authenticated HTTP, and throttle regression chains;
- Sprint130–Sprint138 canonical control-plane hardening, now including canonical-valid authenticated HTTP positive-path, fail-closed, and request-budget enforcement while production filesystem/database side-effect adapters remain unresolved.

Sprint138 specifically proves that the materialization `1/min` and DB-attestation `2/min` route throttles execute through the real HTTP middleware: excess authenticated requests return HTTP `429` before a second materialization write or third database-identity read can occur.

Detailed chronology is in [`CHANGELOG.md`](CHANGELOG.md), current work is in [`TASKS.md`](TASKS.md), and future sequencing is in [`ROADMAP.md`](ROADMAP.md).

## Operational status remains intentionally gated

Source readiness is **not** operational activation.

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

Machine-readable operational authority:

- `ops/final-shift-close/STATE.json`
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

No README wording grants deployment, release, migration execution, permission provisioning, runtime-token provisioning, target activation, Technical Preview activation, Production activation, or updater activation.

## Technology baseline

- Backend: Laravel / PHP
- Frontend: Vue 3 + Inertia + Vite
- Database: MySQL-compatible
- Architecture: Modular Monolith First, Clean Architecture, DDD
- Authorization: tenant-context first, deny-by-default
- API governance: versioned REST, stable error envelope, correlation ID, tenant context, idempotency, cursor pagination, signed webhooks and replay protection
- Runtime evolution: transactional-outbox readiness and infrastructure adapters for future hosting evolution

## Documentation map

- [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) — current project/lifecycle source of truth
- [`CHANGELOG.md`](CHANGELOG.md) — material chronological progress
- [`TASKS.md`](TASKS.md) — current completed/pending workboard
- [`ROADMAP.md`](ROADMAP.md) — future sequencing and gates
- [`ARCHITECTURE.md`](ARCHITECTURE.md) — architecture decisions and boundaries
- [`API_SPEC.md`](API_SPEC.md) — API governance and contracts
- [`DATABASE.md`](DATABASE.md) — database design/history
- [`TESTING.md`](TESTING.md) — testing and qualification practices
- [`SECURITY.md`](SECURITY.md) — security posture
- [`DEPLOYMENT.md`](DEPLOYMENT.md) — deployment procedures; not evidence of deployment authority
- [`RELEASE.md`](RELEASE.md) — release procedures; not evidence of release/activation
- `docs/SPRINT*.md` — bounded sprint evidence
- `ops/final-shift-close/*.json` — machine-readable operational state

## Documentation freshness rule

Every material sprint closure must reconcile the canonical state documents promptly and make the just-closed sprint workflow successor-compatible. Detailed historical evidence stays in per-sprint documents and Git history; root status documents remain concise, current, and non-contradictory.

Author by Lab | zefry

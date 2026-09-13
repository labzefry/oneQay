# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with a Modular Monolith First architecture, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest closed **engineering** sprint is **Sprint147**.

- Canonical engineering commit: `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`
- Latest engineering PR: #711 — `Sprint147: canonical throttle rejection metadata identity response hardening`
- Sprint147 pull-request qualification: **31/31 workflow runs successful** on the exact authorized head
- Sprint147 Product Owner merge-authority workflow run: `34752002084` successful
- Sprint147 merge: squash merged with expected-head guard
- Next engineering position: **Sprint148 bounded discovery**, not yet treated as started or complete

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint147 description

**Purpose / Why:** Sprint146 secured throttle-response ownership using canonical request identity plus exact per-route rate-limit ceilings, but the hardener still did not require the complete framework throttle-rejection metadata shape before rewriting a `429` response.

**Objective / Gap:** Require the previously qualified canonical request identity and throttle ceiling **plus** `X-RateLimit-Remaining: 0`, decimal non-empty `Retry-After`, and decimal non-empty `X-RateLimit-Reset` before Final Shift Close privacy/security rewriting is allowed.

**What changed:** The hardener now validates the complete throttle-rejection metadata identity; Sprint147 added direct regression coverage for materialization POST and DB-attestation GET/HEAD, and updated the Sprint144 historical fixture to the canonical metadata shape proved by Sprint142.

**Evidence / Qualification:** PR #711; final engineering head `513d95dbb4d5ab95a8f6c3282f8911cf339a9697`; 31/31 exact-head CI successful; authority run `34752002084`; canonical engineering commit `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`; six-path engineering envelope SHA-256 `ffd176da808eda16fccdf0375fcae2fd5bcc1cfc4b931aca6492ca31eb9b1d40`.

**Operational boundaries / NO-GO:** No migration execution, permission provisioning, feature activation, runtime-token provisioning, deployment/release, durable-target activation, operational manifest/DB invocation, Technical Preview activation, Production activation, or updater activation occurred.

**Next position:** Sprint148 bounded discovery only; no objective or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, and catalog foundations;
- JRN-010 expected-cash, immutable sale-to-shift binding, cash variance, explanation/adjudication, maker-checker, and reviewer-authorization foundations;
- Final Shift Close source/readiness work including source-only migration #27 and application/runtime readiness contracts;
- runtime-binding manifest, DB-binding attestation, token-policy, delivery-gate, authenticated HTTP, throttle, rejection hardening, route/action identity, exact throttle-budget identity, and canonical throttle-rejection metadata identity qualification through Sprint147.

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

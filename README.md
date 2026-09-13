# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with a Modular Monolith First architecture, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest closed **engineering** sprint is **Sprint145**.

- Canonical engineering commit: `6d4fc06ac1166d15d8598d2a6d39d594f2493767`
- Latest engineering PR: #707 — `Sprint145: canonical action identity throttle response hardening`
- Sprint145 pull-request qualification: **29/29 workflow runs successful** on the exact authorized head
- Sprint145 Product Owner merge-authority workflow run: `34749677796` successful
- Sprint145 merge: squash merged with expected-head guard
- Next engineering position: **Sprint146 bounded discovery**, not yet treated as started or complete

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint145 description

**Purpose / Why:** Sprint144 already required canonical route name + method + path before rewriting throttle responses, but canonical controller action was not yet part of ownership. A route could therefore imitate those signals with a noncanonical action.

**Objective / Gap:** Require **route name + canonical controller action + exact method + exact path** before Final Shift Close throttle-response privacy/security rewriting is allowed.

**What changed:** The throttle-response hardener now validates canonical action identity. A Sprint145 regression proves noncanonical actions remain framework-owned. The Sprint144 positive fixture was made successor-compatible with Laravel's canonical controller metadata.

**Evidence:** PR #707; exact engineering head `eeb93032fb8611e031d207ce95c1825dea7e2f2d`; 29/29 exact-head CI successful; authority run `34749677796`; canonical engineering commit `6d4fc06ac1166d15d8598d2a6d39d594f2493767`; six-path envelope SHA-256 `ed1a67c7a7b89e26cd4c3ade350132b8eca7c4e2142f76d9b69495ac0ba2fad2`.

**Boundary:** No migration execution, permission provisioning, runtime-token provisioning, deployment/release, durable-target activation, Technical Preview activation, Production activation, or updater activation occurred.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, and catalog foundations;
- JRN-010 expected-cash, immutable sale-to-shift binding, cash variance, explanation/adjudication, maker-checker, and reviewer-authorization foundations;
- Final Shift Close source/readiness work including source-only migration #27 and application/runtime readiness contracts;
- runtime-binding manifest, DB-binding attestation, token-policy, delivery-gate, authenticated HTTP, throttle, rejection hardening, route identity, and controller-action ownership qualification through Sprint145.

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

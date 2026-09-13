# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with a Modular Monolith First architecture, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest closed **engineering** sprint is **Sprint146**.

- Canonical engineering commit: `a5e4aec8c142e7478a0e58d2d732dbf106393b06`
- Latest engineering PR: #709 — `Sprint146: canonical throttle budget identity response hardening`
- Sprint146 pull-request qualification: **30/30 workflow runs successful** on the exact authorized head
- Sprint146 Product Owner merge-authority workflow run: `34750648988` successful
- Sprint146 merge: squash merged with expected-head guard
- Next engineering position: **Sprint147 bounded discovery**, not yet treated as started or complete

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint146 description

**Purpose / Why:** Sprint145 secured request ownership using canonical route name, canonical controller action, exact method, and exact path, but the throttle-response hardener still accepted any present `X-RateLimit-Limit` value.

**Objective / Gap:** Require the previously qualified canonical request identity **plus the exact canonical per-route throttle ceiling** before Final Shift Close `429` privacy/security rewriting is allowed.

**What changed:** The hardener now resolves the expected canonical throttle ceiling and requires exact-string equality: materialization POST `1`, DB-attestation GET/HEAD `2`. A Sprint146 regression proves cross-budget, arbitrary, and numeric-alias ceilings remain framework-owned while exact canonical ceilings preserve hardened behavior.

**Evidence / Qualification:** PR #709; exact engineering head `b520b8e8c565a96b4c41e7838a68492f4b836066`; 30/30 exact-head CI successful; authority run `34750648988`; canonical engineering commit `a5e4aec8c142e7478a0e58d2d732dbf106393b06`; five-path envelope SHA-256 `bbc0da27fe84ca8a1fafcf4b76bcf9e1f42e595c01d35544a94793c1a7fec161`.

**Operational boundaries / NO-GO:** No migration execution, permission provisioning, feature activation, runtime-token provisioning, deployment/release, durable-target activation, operational manifest/DB invocation, Technical Preview activation, Production activation, or updater activation occurred.

**Next position:** Sprint147 bounded discovery only; no objective or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, and catalog foundations;
- JRN-010 expected-cash, immutable sale-to-shift binding, cash variance, explanation/adjudication, maker-checker, and reviewer-authorization foundations;
- Final Shift Close source/readiness work including source-only migration #27 and application/runtime readiness contracts;
- runtime-binding manifest, DB-binding attestation, token-policy, delivery-gate, authenticated HTTP, throttle, rejection hardening, route/action identity, and exact throttle-budget ownership qualification through Sprint146.

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

# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed **engineering** sprint is **Sprint155**.

- Canonical engineering commit: `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`
- Engineering PR: #728 — `Sprint155: add feature activation source handoff envelope`
- Final engineering head: `4871e0ade60e8ac5e6f44b2bc27f0319ca150e63`
- Exact-head pull-request qualification: **36/36 workflow runs successful**
- PHP Foundation run `34774606244`: successful
- M7.1 Application Regression run `34774606266`: successful and explicitly executed the Sprint155 regression
- Repository-native Product Owner merge-authority run: `34775351008` successful
- Sprint155 engineering envelope: three paths, SHA-256 `29619b928a422615647184c5316d9679dd4c4d582d759e89e8704e335ed982cb`
- Post-Sprint155 reconciliation envelope: six paths, SHA-256 `323efb8b04badda3874aa7542285499b7be7b8df139cc86fd43b294aac7f8a38`
- Activation executor source foundation: `MATERIALIZED_SOURCE_ONLY`
- Source-only activation transport handoff envelope: materialized
- Concrete configuration-mutation transport: `NOT_IMPLEMENTED`
- Dispatchable feature-activation executor: `NOT_IMPLEMENTED`
- Network / executor dispatch: `NOT_PERFORMED`
- Runtime allowlist remains `local/test/ci`; runtime allowlist change remains `NOT_IMPLEMENTED`
- Next engineering position after reconciliation: **Sprint156 bounded discovery**, with no preselected objective or source envelope

For the full current project state, use **[`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md)** as the canonical human-readable source of truth.

## Sprint155 description

**Purpose / Why:** Sprint154 supplied a deterministic activation execution plan, but canonical source still lacked a bounded handoff object that could bind that plan to already-qualified target-capability identity while explicitly preserving the absence of any concrete adapter or network dispatch.

**Objective / Gap:** `FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION_TRANSPORT_SOURCE_FOUNDATION` — materialize a deterministic, fail-closed source-only handoff envelope without creating an operational transport or mutation surface.

**What changed:** Sprint155 added `FinalShiftCloseFeatureActivationTransportEnvelope.php`, its executable regression, and a three-line registration in the existing M7.1 persistence harness. Proposed dedicated operationally suggestive workflow/document/metadata additions were rejected by connector safety guards during engineering; scope was narrowed rather than bypassing those guards. The post-merge reconciliation adds only a non-operational source-contract preservation workflow that runs existing PHP syntax and application tests.

**Evidence / Qualification:** PR #728; final head `4871e0ade60e8ac5e6f44b2bc27f0319ca150e63`; 36/36 exact-head CI successful; M7.1 run `34774606266` explicitly reported `Final Shift Close feature activation transport envelope regression passed.`; authority run `34775351008`; engineering squash `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`; engineering envelope SHA-256 `29619b928a422615647184c5316d9679dd4c4d582d759e89e8704e335ed982cb`.

**Operational boundaries / NO-GO:** No target was selected or persisted, no concrete configuration transport or adapter was implemented, no network or executor dispatch occurred, migration #27 remains unexecuted, permissions remain `NONE`, real capability/dependency evidence remains absent, runtime allowlist remains `local/test/ci`, feature activation remains `INACTIVE`, deployment authority remains `NOT_GRANTED`, Technical Preview/Production remain `NOT_AUTHORIZED`, and updater remains `INACTIVE`.

**Next position:** after reconciliation is squash merged and verified, Sprint156 bounded discovery only; no objective, implementation, or source envelope is preselected.

## What the repository has reached

Material canonical progress includes:

- modular-monolith, tenant-isolation, authorization, API-governance, CI/governance, and exact-head merge controls;
- bounded POS shift/register, sale/payment/receipt, catalog, cash-variance, adjudication, and reviewer-control foundations;
- Final Shift Close migration/readiness, selected-target identity, attestation/selection binding, migration DB binding, runtime control-plane hardening, capability-evidence identity/producer readiness, full dependency-envelope qualification, deterministic dependency-evidence construction, trusted dependency-evidence producer source, selected-target-bound permission-provisioning hardening, feature-activation execution-plan source foundation, and the Sprint155 source-only activation transport handoff envelope.

## Operational status remains intentionally gated

| Gate | State |
| --- | --- |
| Migration #27 execution | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |
| Real target-bound capability evidence | `NONE` |
| Dependency-evidence producer dispatch | `NOT_PERFORMED` |
| Real dependency-envelope evidence | `NONE` |
| Activation executor source foundation | `MATERIALIZED_SOURCE_ONLY` |
| Source-only activation transport handoff envelope | materialized |
| Dispatchable feature-activation executor | `NOT_IMPLEMENTED` |
| Concrete configuration-mutation transport | `NOT_IMPLEMENTED` |
| Network / executor dispatch | `NOT_PERFORMED` |
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
- `docs/SPRINT*.md` — detailed historical sprint evidence where materialized
- `ops/final-shift-close/*.json` — machine-readable operational state

## Sprint documentation rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**.

Author by Lab | zefry

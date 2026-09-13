# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Canonical engineering checkpoint:** Sprint155  
**Canonical engineering commit:** `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`  
**Latest engineering PR:** #728 — `Sprint155: add feature activation source handoff envelope`  
**Status date:** 2026-09-14

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

Sprint155 materialized a deterministic **source-only feature-activation transport handoff envelope** between the Sprint154 activation execution plan and previously qualified target-capability identity. It does not implement a concrete configuration-mutation adapter, network dispatch, runtime allowlist widening, or feature activation.

The executable Sprint155 regression is registered through the existing M7.1 application harness. This gives direct CI coverage without introducing a new operational workflow, dependency script, credential path, endpoint, or dispatch surface.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint155 |
| Canonical engineering commit | `1e84e1b3e07915a1d20b56fe768b0d1454f901d2` |
| Latest engineering PR | #728, squash merged |
| Sprint155 final engineering head | `4871e0ade60e8ac5e6f44b2bc27f0319ca150e63` |
| Sprint155 exact-head CI | 36/36 pull-request workflow runs successful |
| Sprint155 Product Owner authority | run `34775351008`, successful repository-native verification |
| Sprint155 engineering envelope | 3 paths; SHA-256 `29619b928a422615647184c5316d9679dd4c4d582d759e89e8704e335ed982cb` |
| Post-Sprint155 reconciliation envelope | 6 paths; SHA-256 `323efb8b04badda3874aa7542285499b7be7b8df139cc86fd43b294aac7f8a38` |
| Activation executor source foundation | `MATERIALIZED_SOURCE_ONLY` |
| Source-only activation transport handoff envelope | `MATERIALIZED_SOURCE_ONLY` |
| Concrete configuration-mutation transport | `NOT_IMPLEMENTED` |
| Dispatchable feature-activation executor | `NOT_IMPLEMENTED` |
| Network / executor dispatch | `NOT_PERFORMED` |
| Runtime allowlist | `local`, `test`, `ci` only |
| Runtime allowlist change | `NOT_IMPLEMENTED` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected activation target | `null` |
| Final Shift Close migration #27 | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Real target-bound capability evidence | `NONE` |
| Real dependency-envelope evidence | `NONE` |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |

## 2. Material engineering progress

### Platform, governance, and POS foundation

The repository has established modular-monolith architecture, tenant isolation, deny-by-default authorization, first-party session and privileged-authentication foundations, versioned REST governance, CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work includes shift/register opening, sale completion/payment/receipt evidence, tenant/outlet-scoped catalog preparation, durable idempotency preservation, cash-variance and adjudication foundations, and historical regression compatibility.

### Final Shift Close engineering chain

- Sprint88 — migration #27 source materialization only; migration remained `NOT_EXECUTED`.
- Sprint107 — nine-component runtime dependency inventory and explicit allowlist block.
- Sprint110–Sprint111 — durable-runtime readiness and exact selected-target identity.
- Sprint113–Sprint118 — attestation/ingestion, selection persistence, migration selected-target binding, and trusted DB-binding producer source readiness.
- Sprint119–Sprint147 — runtime DB-binding/materialization control plane plus canonical HTTP/auth/throttle hardening.
- Sprint148 — exact target-bound capability-evidence identity qualification.
- Sprint149 — trusted capability-evidence producer source materialization without dispatch.
- Sprint150 — exact selected-runtime full nine-component dependency-envelope qualification.
- Sprint151 — deterministic target-bound dependency-envelope evidence construction source foundation.
- Sprint152 — selected-target database binding for permission provisioning.
- Sprint153 — trusted protected-environment dependency-envelope evidence producer source without dispatch.
- Sprint154 — selected-target-bound feature-activation execution-plan source foundation.
- Sprint155 — deterministic source-only activation transport handoff envelope plus executable regression in the existing M7.1 harness.

## 3. Sprint155 description and closure evidence

### Purpose / Why

Post-Sprint154 had a deterministic activation execution plan, but no bounded source object that could bind that plan to the already-qualified target-capability identity while preserving the explicit absence of a concrete adapter and network dispatch.

Discovery also proved there was still no repository-native dispatchable Final Shift Close activation workflow or concrete configuration-mutation transport. Sprint155 therefore remained source-only.

### Objective / Gap

Bounded objective: `FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION_TRANSPORT_SOURCE_FOUNDATION`.

The objective was to materialize a deterministic handoff envelope that validates exact target identity, selection fingerprint, capability evidence identity, ordered activation-plan semantics, and fail-closed NO-GO states without creating any operational transport or mutation surface.

### What changed

- Added `FinalShiftCloseFeatureActivationTransportEnvelope.php`.
- Added executable regression `pos-final-shift-close-feature-activation-transport-envelope.php`.
- Registered that regression through `apps/web/tests/persistence.php`, so the existing M7.1 `composer test` path executes it.
- The final engineering envelope intentionally remained three paths after connector safety guards rejected proposed dedicated operationally suggestive workflow/document/metadata artifacts. No guard bypass was attempted.
- Post-engineering reconciliation added a non-operational source-contract preservation workflow that only installs locked PHP dependencies, validates PHP syntax, and runs the existing application regression suite.

### Evidence / Qualification

- Engineering PR: #728, squash merged.
- Parent canonical post-Sprint154 checkpoint: `056d0300af925c9e8adf04a11a107cc4f5fde196`.
- Final exact engineering head: `4871e0ade60e8ac5e6f44b2bc27f0319ca150e63`.
- Exact-head pull-request qualification: **36/36 successful**.
- PHP Foundation Regression: run `34774606244`, successful.
- M7.1 Application Regression: run `34774606266`, successful.
- M7.1 logs explicitly reported `Final Shift Close feature activation transport envelope regression passed.`
- Repository-native Product Owner merge-authority run: `34775351008`, successful.
- Engineering envelope: three paths; SHA-256 `29619b928a422615647184c5316d9679dd4c4d582d759e89e8704e335ed982cb`.
- Canonical engineering squash commit: `1e84e1b3e07915a1d20b56fe768b0d1454f901d2`.
- Post-merge verification: exactly one squash commit above `056d0300af925c9e8adf04a11a107cc4f5fde196`, with exactly the three engineering paths.
- Post-Sprint155 reconciliation envelope: six paths; SHA-256 `323efb8b04badda3874aa7542285499b7be7b8df139cc86fd43b294aac7f8a38`.

### Operational boundaries / NO-GO

Sprint155 does not persist a target, dispatch a producer or executor, implement a concrete configuration-mutation adapter, perform network dispatch, execute migration #27, provision permissions, produce real capability/dependency evidence, widen the runtime allowlist, activate Final Shift Close, deploy/release, activate Technical Preview/Production, or activate the updater.

Machine-readable state after the engineering merge still records target selection blocked with `selected_target=null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview and Production `NOT_AUTHORIZED`, and updater `INACTIVE`.

### Next position

After post-Sprint155 reconciliation is squash merged and verified, the next engineering position is **Sprint156 bounded discovery from canonical post-Sprint155**. No Sprint156 objective, implementation, or source envelope is preselected.

## 4. Operational truth — NO-GO remains authoritative

Current machine-readable state remains:

- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- selected target: `null`;
- target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- capability-evidence producer dispatch: `NOT_PERFORMED`; real capability evidence: `NONE`;
- dependency-evidence producer dispatch: `NOT_PERFORMED`; real dependency evidence: `NONE`;
- activation executor source foundation: `MATERIALIZED_SOURCE_ONLY`;
- source-only activation transport handoff envelope: materialized as engineering source only;
- dispatchable feature-activation executor: `NOT_IMPLEMENTED`;
- concrete configuration-mutation transport: `NOT_IMPLEMENTED`;
- runtime allowlist change: `NOT_IMPLEMENTED`;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`.

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## 5. Documentation responsibility model

- `PROJECT_MANIFEST.md` — canonical human-readable current project/lifecycle state;
- `README.md` — concise entry-point summary;
- `CHANGELOG.md` — material chronology;
- `TASKS.md` — current completed/pending workboard;
- `ROADMAP.md` — forward sequencing and lifecycle gates;
- `docs/SPRINT*.md` — detailed historical bounded-sprint evidence where materialized;
- `ops/final-shift-close/*.json` — machine-readable operational authority;
- merged PRs and Git history — immutable implementation provenance.

## 6. Mandatory sprint description and update rule

Every material sprint records **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**. Every material closed sprint reconciles this manifest and the four root summary documents, while the just-closed sprint preservation workflow remains source-only and successor-compatible.

Author by Lab | zefry

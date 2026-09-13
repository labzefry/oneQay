# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint150
**Canonical engineering commit:** `d743a054092231729fa0e33cd34538f9d1e81787`
**Latest engineering PR:** #718 — `Sprint150: qualify selected runtime durable dependency envelope`
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint150 engineering**. Sprint150 materialized a fail-closed source qualifier for the complete nine-component Sprint107 durable-runtime dependency envelope, bound to exact Sprint111 selected-target identity and exact Sprint148 target-bound capability-evidence identity. It did not create real dependency-envelope evidence, a dependency-evidence producer, a selected target, or a runtime-allowlist change.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint150 |
| Latest engineering commit | `d743a054092231729fa0e33cd34538f9d1e81787` |
| Latest engineering PR | #718, squash merged |
| Sprint150 final engineering head | `7102733080735f4591bb17df76bdec928975e826` |
| Sprint150 exact-head CI | 35/35 pull-request workflow runs successful |
| Sprint150 Product Owner authority | run `34761524119`, successful exact-head repository-native verification |
| Sprint150 engineering envelope | six paths; SHA-256 `9f261895ab0373af5d6d385c3db3f93e5e510061b8e843e4110ecce992d9a0e6` |
| Post-Sprint150 reconciliation envelope | six paths; SHA-256 `578765b03de34048670791017fcffe8680b65e6bede9f57d22affb255f5ee43f` |
| Dependency-envelope qualifier | `MATERIALIZED_SOURCE_ONLY` |
| Real dependency-envelope evidence | `NONE` |
| Dependency-evidence producer | `NOT_IMPLEMENTED` |
| Runtime allowlist | `local`, `test`, `ci` only |
| Runtime allowlist change | `NOT_IMPLEMENTED` |
| Real target-bound capability evidence | `NONE` |
| Trusted capability-evidence producer | `MATERIALIZED_NOT_DISPATCHED` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected activation target | `null` |
| Final Shift Close migration #27 | `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |

## 2. Material engineering progress

### Platform, governance, and POS foundation

The repository has established modular-monolith architecture, tenant isolation, deny-by-default authorization, first-party session and privileged-authentication foundations, versioned REST governance, CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work includes shift/register opening, sale completion/payment/receipt evidence, tenant/outlet-scoped catalog preparation, durable idempotency preservation, and historical regression compatibility.

### JRN-010 cash and variance chain

Representative milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. These remain source/evidence achievements, not production activation.

### Final Shift Close engineering chain

- Sprint88 — migration #27 source materialization only; migration remained `NOT_EXECUTED`.
- Sprint89 — application-readiness contract and fail-closed prerequisites.
- Sprint107 — nine-component runtime dependency inventory and explicit allowlist block.
- Sprint110 — durable-runtime readiness shape and capability claims.
- Sprint111 — exact durable-runtime selected-target identity and deterministic selection fingerprint.
- Sprint113–Sprint117 — attestation producer/ingestion, selection-persistence and selected-target binding readiness, all source-only and non-operational.
- Sprint130–Sprint147 — canonical runtime-control-plane, authenticated HTTP, throttle/rejection, route/action/budget/metadata identity hardening.
- Sprint148 — target-bound durable-runtime capability-evidence identity qualification.
- Sprint149 — trusted protected-environment capability-evidence producer source materialization without dispatch.
- Sprint150 — exact selected-runtime-class full durable dependency-envelope qualification source, without real dependency evidence or allowlist widening.

## 3. Sprint150 description and closure evidence

### Purpose / Why

Sprint107 established the canonical nine-component dependency inventory and intentionally blocked runtime-allowlist widening. Sprint148 and Sprint149 established target-bound capability-evidence qualification and producer source readiness, but no source owner qualified the full dependency envelope against the exact selected durable runtime identity.

### Objective / Gap

Bounded objective: `DURABLE_RUNTIME_SELECTED_CLASS_DEPENDENCY_ENVELOPE_QUALIFICATION`.

The Sprint150 qualifier requires exact Sprint148 capability evidence to qualify first, then requires every Sprint107 dependency component to be independently verified, bound to the exact selected runtime class and target binding, represented by lowercase SHA-256 evidence identity, secret-free, and non-synthetic.

### What changed

- Added `FinalShiftCloseDurableRuntimeDependencyEnvelope` as a pure application-layer qualifier.
- Added executable positive and fail-closed regression coverage.
- Added `DURABLE_RUNTIME_DEPENDENCY_ENVELOPE_QUALIFICATION_CONTRACT.json`.
- Extended `POST_SELECTION_DOWNSTREAM_READINESS.json` with explicit dependency-envelope qualification readiness and ordering.
- Added an exact-head Sprint150 workflow and detailed six-section Sprint150 document.
- Preserved `FinalShiftCloseServiceProvider` as `local/test/ci` only.
- No historical compatibility expansion was required because all existing PR-triggered workflows passed on the frozen six-path envelope.

### Evidence / Qualification

- Engineering PR: #718, squash merged.
- Canonical engineering squash commit: `d743a054092231729fa0e33cd34538f9d1e81787`.
- Parent canonical post-Sprint149 reconciliation checkpoint: `0113ee31db38dcd8f7c1378b109371ad900c6f69`.
- Final exact engineering head before merge: `7102733080735f4591bb17df76bdec928975e826`.
- Exact-head pull-request qualification: **35/35 successful**.
- Repository-native Product Owner merge-authority run: `34761524119`, successful exact-head verification.
- Final engineering envelope: six paths.
- Frozen engineering envelope SHA-256: `9f261895ab0373af5d6d385c3db3f93e5e510061b8e843e4110ecce992d9a0e6`.
- Merge method: squash with expected-head guard.
- Post-merge verification: one squash commit with parent exactly `0113ee31db38dcd8f7c1378b109371ad900c6f69`; exact six-path engineering delta; operational NO-GO unchanged.
- Post-Sprint150 reconciliation envelope: six paths; SHA-256 `578765b03de34048670791017fcffe8680b65e6bede9f57d22affb255f5ee43f`.

### Operational boundaries / NO-GO

Sprint150 does not select or persist a durable target, dispatch the Sprint149 capability-evidence producer, create real capability evidence, create or dispatch dependency-envelope evidence, widen the runtime allowlist, execute migration #27, provision permissions, materialize or execute feature activation, deploy/release, activate Technical Preview or Production, or activate the updater.

### Next position

After this canonical reconciliation is squash merged and verified, the next engineering position is **Sprint151 bounded discovery from canonical post-Sprint150**. No Sprint151 objective, implementation, or source envelope is preselected by this manifest.

## 4. Operational truth — NO-GO remains authoritative

Current machine-readable state remains:

- migration #27 execution: `NOT_EXECUTED` / `NOT_PERFORMED`;
- permission provisioning: `NONE`;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview activation: `NOT_AUTHORIZED`;
- Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`;
- durable target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- capability-evidence producer: `MATERIALIZED_NOT_DISPATCHED`;
- capability-evidence producer dispatch: `NOT_PERFORMED`;
- real capability evidence: `NONE`;
- dependency-envelope qualifier: `MATERIALIZED_SOURCE_ONLY`;
- dependency-envelope evidence: `NONE`;
- dependency-evidence producer: `NOT_IMPLEMENTED`;
- runtime allowlist change: `NOT_IMPLEMENTED`.

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## 5. What is not yet complete

The following remain separate future work or operational actions:

- qualify, select, and persist a real non-synthetic durable target;
- dispatch trusted capability-evidence production and obtain real capability evidence;
- materialize trusted dependency-envelope evidence production and obtain real dependency evidence;
- execute migration #27 and provision permission on the exact selected target;
- qualify any selected runtime class for allowlist widening and then perform a separately authorized allowlist change;
- materialize and separately authorize feature activation;
- deploy/release or activate Technical Preview, Production, or updater.

## 6. Documentation responsibility model

- `PROJECT_MANIFEST.md` — canonical human-readable current project/lifecycle state;
- `README.md` — concise entry-point summary;
- `CHANGELOG.md` — material chronology;
- `TASKS.md` — current completed/pending workboard;
- `ROADMAP.md` — forward sequencing and lifecycle gates;
- `docs/SPRINT*.md` — detailed bounded sprint evidence;
- `ops/final-shift-close/*.json` — machine-readable operational authority;
- merged PRs and Git history — immutable implementation provenance.

## 7. Mandatory sprint description rule

Every material sprint must document **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**.

## 8. Update rule

Every material closed sprint must reconcile this manifest and the four root summary documents during closure. The just-closed sprint workflow must relinquish full-envelope ownership and remain successor-compatible while preserving its historical executable regression.

Author by Lab | zefry

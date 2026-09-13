# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint151
**Canonical engineering commit:** `68b8362f326e56cfec478f0275b6d29ed0f54dec`
**Latest engineering PR:** #720 — `Sprint151 source foundation`
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint151 engineering**. Sprint151 materialized the source-only application foundation that deterministically constructs the exact target-bound nine-component dependency-envelope evidence shape and requires the Sprint150 qualifier to accept its output. It deliberately did not materialize a dispatchable dependency-evidence producer and did not create real dependency evidence or operational authority.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint151 |
| Latest engineering commit | `68b8362f326e56cfec478f0275b6d29ed0f54dec` |
| Latest engineering PR | #720, squash merged |
| Sprint151 final engineering head | `f41e32bc44e0ee0346f5efd15d54f6af21b8a19a` |
| Sprint151 exact-head CI | 36/36 pull-request workflow runs successful |
| Sprint151 Product Owner authority | run `34764011476`, successful exact-head repository-native verification |
| Sprint151 engineering envelope | five paths; SHA-256 `ef000ec9172dcee1f08a6c8ea9149fb957a307e7929eaaa42c5d4b08bad94c54` |
| Post-Sprint151 reconciliation envelope | six paths; SHA-256 `82d72910aaa4409113cfbb8b2b7f326daa51c205d7a6bfefa90ad5624604fb25` |
| Dependency-envelope qualifier | `MATERIALIZED_SOURCE_ONLY` |
| Dependency-evidence source foundation | `MATERIALIZED_SOURCE_ONLY` |
| Dispatchable dependency-evidence producer | `NOT_IMPLEMENTED` |
| Real dependency-envelope evidence | `NONE` |
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

### Final Shift Close engineering chain

- Sprint88 — migration #27 source materialization only; migration remained `NOT_EXECUTED`.
- Sprint89 — application-readiness contract and fail-closed prerequisites.
- Sprint107 — nine-component runtime dependency inventory and explicit allowlist block.
- Sprint110 — durable-runtime readiness shape and capability claims.
- Sprint111 — exact durable-runtime selected-target identity and deterministic selection fingerprint.
- Sprint113–Sprint117 — attestation producer/ingestion, selection-persistence and selected-target binding readiness, all source-only and non-operational.
- Sprint130–Sprint147 — canonical runtime-control-plane and authenticated HTTP/throttle identity hardening.
- Sprint148 — exact target-bound durable-runtime capability-evidence identity qualification.
- Sprint149 — trusted protected-environment capability-evidence producer source materialization without dispatch.
- Sprint150 — exact selected-runtime-class full nine-component dependency-envelope qualification source.
- Sprint151 — target-bound dependency-envelope evidence construction source foundation, while the dispatchable producer remains unimplemented.

## 3. Sprint151 description and closure evidence

### Purpose / Why

Sprint150 established a strict qualifier for the complete nine-component durable-runtime dependency envelope but intentionally left the source responsible for deterministically constructing that evidence shape unimplemented. Discovery also proved the existing runtime-attestation and capability-evidence producers do not own the nine dependency-component evidence records.

### Objective / Gap

Bounded objective: `DURABLE_RUNTIME_TARGET_BOUND_DEPENDENCY_ENVELOPE_EVIDENCE_SOURCE_FOUNDATION`.

The source foundation must accept only the exact nine canonical dependency observations, bind them to exact selected-target, runtime, source/artifact, and Sprint148 capability-evidence identity, reject synthetic mixing and malformed evidence digests, and require independent Sprint150 qualification before output is accepted.

### What changed

- Added `FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer` as a source-only application component.
- Added `DURABLE_RUNTIME_DEPENDENCY_ENVELOPE_SOURCE_FOUNDATION_CONTRACT.json`.
- Extended `POST_SELECTION_DOWNSTREAM_READINESS.json` with explicit source-foundation readiness while keeping the dispatchable producer unimplemented and real evidence absent.
- Added an exact-head Sprint151 workflow and six-section Sprint151 document.
- Preserved the Final Shift Close runtime allowlist as `local/test/ci` only.
- The original discovery considered a dispatchable producer, but the published Sprint151 scope was deliberately reduced before PR publication to a safe source-only foundation; no transport or operational workflow was added.

### Evidence / Qualification

- Engineering PR: #720, squash merged.
- Canonical engineering squash commit: `68b8362f326e56cfec478f0275b6d29ed0f54dec`.
- Parent canonical post-Sprint150 reconciliation checkpoint: `ea328965af0ff15c5431450f3d9888f43cd36b63`.
- Final exact engineering head before merge: `f41e32bc44e0ee0346f5efd15d54f6af21b8a19a`.
- Exact-head pull-request qualification: **36/36 successful**.
- Repository-native Product Owner merge-authority run: `34764011476`, successful exact-head verification.
- Final engineering envelope: five paths.
- Frozen engineering envelope SHA-256: `ef000ec9172dcee1f08a6c8ea9149fb957a307e7929eaaa42c5d4b08bad94c54`.
- Merge method: squash with expected-head guard.
- Post-merge verification: one squash commit with parent exactly `ea328965af0ff15c5431450f3d9888f43cd36b63`; exact five-path engineering delta; operational NO-GO unchanged.
- Post-Sprint151 reconciliation envelope: six paths; SHA-256 `82d72910aaa4409113cfbb8b2b7f326daa51c205d7a6bfefa90ad5624604fb25`.

### Operational boundaries / NO-GO

Sprint151 does not select or persist a durable target, dispatch any capability/dependency producer, create real capability or dependency evidence, widen the runtime allowlist, execute migration #27, provision permissions, materialize or execute feature activation, deploy/release, activate Technical Preview or Production, or activate the updater.

### Next position

After post-Sprint151 canonical reconciliation is squash merged and verified, the next engineering position is **Sprint152 bounded discovery from canonical post-Sprint151**. No Sprint152 objective, implementation, or source envelope is preselected by this manifest.

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
- dependency-evidence source foundation: `MATERIALIZED_SOURCE_ONLY`;
- dispatchable dependency-evidence producer: `NOT_IMPLEMENTED`;
- real dependency-envelope evidence: `NONE`;
- runtime allowlist change: `NOT_IMPLEMENTED`.

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## 5. What is not yet complete

The following remain separate future work or operational actions:

- qualify, select, and persist a real non-synthetic durable target;
- dispatch trusted capability-evidence production and obtain real capability evidence;
- materialize a separately qualified dispatchable dependency-envelope evidence producer and obtain real dependency evidence;
- execute migration #27 and provision permission on the exact selected target;
- qualify any selected runtime class for allowlist widening and perform a separately authorized allowlist change;
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

Every material closed sprint must reconcile this manifest and the four root summary documents during closure. The just-closed sprint workflow must relinquish full-envelope ownership and remain successor-compatible while preserving its historical qualification and operational boundaries.

Author by Lab | zefry

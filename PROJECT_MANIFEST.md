# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Canonical engineering checkpoint:** Sprint149  
**Canonical engineering commit:** `662a892c3269d945579594da03121bce960c9074`  
**Latest engineering PR:** #716 — `Sprint149: durable runtime target-bound capability evidence producer`  
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint149 engineering**. Sprint149 materialized the trusted protected-environment source producer required to create exact target-bound durable-runtime capability evidence in a future separately authorized execution. The producer source is materialized but **not dispatched**, no real capability evidence exists, no durable target has been selected or persisted, and no operational activation has occurred.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint149 |
| Latest engineering commit | `662a892c3269d945579594da03121bce960c9074` |
| Latest engineering PR | #716, squash merged |
| Sprint149 final engineering head | `449832afd18117c58fb034ad23c9d4217bd3cd1e` |
| Sprint149 exact-head CI | 34/34 pull-request workflow runs successful |
| Sprint149 Product Owner authority | run `34759693893`, successful exact-head repository-native verification |
| Sprint149 engineering envelope | eight paths; SHA-256 `8acea0b1cc826dedbe2dd55f38b4aa24fd4854a96ee32dde9f7552d22d239a91` |
| Post-Sprint149 reconciliation envelope | six paths; SHA-256 `f97c59e253a6d3d47ff84f026690e700c3c85c2b9e8baf9ec00bfb57c1663c7e` |
| Architecture | Modular Monolith First, Clean Architecture, DDD |
| Backend | Laravel / PHP |
| Frontend | Vue 3 + Inertia + Vite |
| Database | MySQL-compatible |
| Tenant model | First-class tenant context, deny-by-default authorization |
| Final Shift Close migration #27 | Source materialized; `NOT_EXECUTED` |
| Permission provisioning | `NONE` |
| Final Shift Close feature activation | `INACTIVE` |
| Deployment authority | `NOT_GRANTED` |
| Technical Preview activation | `NOT_AUTHORIZED` |
| Production activation | `NOT_AUTHORIZED` |
| Updater activation | `INACTIVE` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected activation target | `NONE` / `null` |
| Runtime allowlist change | `NOT_IMPLEMENTED` |
| Real target-bound capability evidence | `NONE` |
| Trusted capability-evidence producer | `MATERIALIZED_NOT_DISPATCHED` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |

## 2. Material engineering progress

### Platform, governance, and POS foundation

The repository has established modular-monolith architecture, tenant isolation, deny-by-default authorization, first-party session and privileged-authentication foundations, versioned REST governance, CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work includes shift/register opening, sale completion/payment/receipt evidence, tenant/outlet-scoped catalog preparation, durable idempotency preservation, and historical regression compatibility.

### JRN-010 cash and variance chain

Representative milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. These remain source/evidence achievements, not production activation.

### Final Shift Close engineering chain

- Sprint88 — migration #27 source materialization only; migration remained `NOT_EXECUTED`.
- Sprint89 — application-readiness contract and fail-closed prerequisites.
- Sprint110 — durable-runtime readiness shape and capability claims.
- Sprint111 — exact durable-runtime selected-target identity and deterministic selection fingerprint, still `SELECTED_NOT_AUTHORIZED` when eventually persisted.
- Sprint113–Sprint117 — attestation producer/ingestion, selection-persistence and selected-target binding readiness, all source-only and non-operational.
- Sprint130–Sprint147 — canonical runtime control-plane, authenticated HTTP, throttle/rejection, route/action/budget/metadata identity hardening.
- Sprint148 — target-bound durable-runtime capability-evidence identity qualification before any future runtime-allowlist eligibility.
- Sprint149 — trusted protected-environment target-bound capability-evidence producer source materialization, without dispatch or real evidence creation.

## 3. Sprint149 description and closure evidence

### Purpose / Why

Sprint148 defined the exact identity contract for four capability-specific durable-runtime evidence records but deliberately left `real_capability_evidence = NONE` and the trusted producer unimplemented. Sprint149 closes only that source-readiness gap.

### Objective / Gap

Bounded objective: `DURABLE_RUNTIME_TARGET_BOUND_CAPABILITY_EVIDENCE_PRODUCER`.

The producer must obtain capability-specific observation digests from an authenticated protected-environment endpoint, reject Sprint110 boolean claims as evidence, recompute Sprint110 readiness and Sprint111 target identity, match a future persisted `SELECTED_NOT_AUTHORIZED` target, bind each capability evidence identity to the exact target, and require final Sprint148 qualification.

### What changed

- Added `FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer` as a deterministic, fail-closed application producer.
- Added executable positive and negative Sprint149 regression coverage.
- Added protected-environment `workflow_dispatch` producer source `.github/workflows/final-shift-close-durable-runtime-capability-evidence.yml`.
- Added `DURABLE_RUNTIME_CAPABILITY_EVIDENCE_PRODUCER_CONTRACT.json`.
- Extended `POST_SELECTION_DOWNSTREAM_READINESS.json` with producer source readiness while keeping real evidence absent and producer dispatch not performed.
- Added detailed six-section Sprint149 documentation and an exact-head engineering workflow.
- Exact-head CI proved historical Sprint148 still froze `trusted_evidence_producer_materialized == false`; the engineering envelope therefore expanded from seven to eight paths only to make Sprint148 successor-compatible while preserving Sprint148 evidence-binding and NO-GO invariants.
- No producer dispatch, target persistence, runtime allowlist change, activation, provisioning, migration execution, or deployment occurred.

### Evidence / Qualification

- Engineering PR: #716, squash merged.
- Canonical engineering squash commit: `662a892c3269d945579594da03121bce960c9074`.
- Parent canonical post-Sprint148 reconciliation checkpoint: `c595da17fd023a9eb2ebd3f046e6f17940430a03`.
- Final exact engineering head before merge: `449832afd18117c58fb034ad23c9d4217bd3cd1e`.
- Exact-head pull-request qualification: **34/34 successful**.
- Repository-native Product Owner merge-authority run: `34759693893`, successful exact-head verification.
- Final engineering envelope: eight paths.
- Frozen engineering envelope SHA-256: `8acea0b1cc826dedbe2dd55f38b4aa24fd4854a96ee32dde9f7552d22d239a91`.
- Merge method: squash with expected-head guard.
- Post-merge verification: one squash commit with parent exactly `c595da17fd023a9eb2ebd3f046e6f17940430a03`; operational NO-GO unchanged.
- Post-Sprint149 reconciliation envelope: six paths; SHA-256 `f97c59e253a6d3d47ff84f026690e700c3c85c2b9e8baf9ec00bfb57c1663c7e`.

Final engineering paths:

1. `.github/workflows/final-shift-close-durable-runtime-capability-evidence.yml`
2. `.github/workflows/sprint148-final-shift-close-durable-runtime-capability-evidence-binding-regression.yml`
3. `.github/workflows/sprint149-final-shift-close-durable-runtime-capability-evidence-producer-regression.yml`
4. `apps/web/app/Application/Pos/FinalShiftCloseDurableRuntimeCapabilityEvidenceProducer.php`
5. `apps/web/tests/pos-final-shift-close-durable-runtime-capability-evidence-producer.php`
6. `docs/SPRINT149_FINAL_SHIFT_CLOSE_DURABLE_RUNTIME_CAPABILITY_EVIDENCE_PRODUCER.md`
7. `ops/final-shift-close/DURABLE_RUNTIME_CAPABILITY_EVIDENCE_PRODUCER_CONTRACT.json`
8. `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`

### Operational boundaries / NO-GO

Sprint149 does not grant or perform operational activation. It does not dispatch the capability-evidence producer, persist a durable target, create real capability evidence, execute migration #27, provision permissions, change the Final Shift Close runtime allowlist, materialize or execute feature activation, deploy/release, activate Technical Preview or Production, or activate the updater.

### Next position

After post-Sprint149 canonical reconciliation is squash merged and verified, the next engineering position is **Sprint150 bounded discovery from canonical post-Sprint149**. No Sprint150 objective, implementation, or source envelope is preselected by this manifest.

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
- runtime allowlist change: `NOT_IMPLEMENTED`;
- real capability evidence: `NONE`;
- trusted capability-evidence producer: `MATERIALIZED_NOT_DISPATCHED`;
- capability-evidence producer dispatch: `NOT_PERFORMED`.

Machine-readable operational authority:

- `ops/final-shift-close/STATE.json`
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

If this manifest and machine-readable state files ever conflict on an operational state, the machine-readable state files are authoritative for that gate.

## 5. What is not yet complete

The following must not be described as complete unless separately qualified and authorized:

- qualification, selection, and persistence of a real non-synthetic durable activation target;
- dispatch of trusted capability-evidence producer and production of real target-bound capability evidence;
- execution of migration #27 in an operational environment;
- provisioning of Final Shift Close permissions;
- runtime allowlist widening for a selected durable runtime class;
- Final Shift Close activation;
- Technical Preview activation;
- Production activation;
- deployment/release publication;
- updater activation.

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

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint153
**Canonical engineering commit:** `e42ea89fca2b3974e7fa9b0a95fa31a901e9840c`
**Latest engineering PR:** #724 — `Sprint153: durable runtime dependency envelope evidence producer`
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint153 engineering**. Sprint153 materialized the source-only protected-environment producer for exact target-bound durable-runtime dependency-envelope evidence. The producer cannot accept caller-selected target identity, requires canonical `SELECTED_NOT_AUTHORIZED`, resolves exact successful trusted Sprint149 capability-evidence producer provenance, fetches dependency observations only from an authenticated HTTPS protected-environment channel, reuses the Sprint151 deterministic construction source, and requires the Sprint150 qualifier before evidence publication.

Sprint153 did not dispatch the producer and did not create real dependency-envelope evidence. Canonical target selection remains blocked, migration #27 remains unexecuted, permission provisioning remains `NONE`, runtime allowlist widening remains unimplemented, and feature activation remains inactive.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint153 |
| Latest engineering commit | `e42ea89fca2b3974e7fa9b0a95fa31a901e9840c` |
| Latest engineering PR | #724, squash merged |
| Sprint153 final engineering head | `44a17e4590df58472114aad547dd1cd3087f8e96` |
| Sprint153 exact-head CI | 38/38 pull-request workflow runs successful |
| Sprint153 Product Owner authority | run `34767471668`, successful exact-head repository-native verification |
| Sprint153 engineering envelope | six paths; SHA-256 `7269a0e5f8927c2763411d0700b0f456cdcb0781f1d198179966a97a7b934a79` |
| Post-Sprint153 reconciliation envelope | six paths; SHA-256 `6a0fd4267f940c02d23e95ce4085e89cdf25a52186d95619e1b1da89488dfc46` |
| Dependency-envelope qualifier | `MATERIALIZED_SOURCE_ONLY` |
| Dependency-evidence source foundation | `MATERIALIZED_SOURCE_ONLY` |
| Trusted dependency-evidence producer source | `MATERIALIZED_NOT_DISPATCHED` |
| Dependency-evidence producer dispatch | `NOT_PERFORMED` |
| Real dependency-envelope evidence | `NONE` |
| Trusted capability-evidence producer | `MATERIALIZED_NOT_DISPATCHED` |
| Capability-evidence producer dispatch | `NOT_PERFORMED` |
| Real target-bound capability evidence | `NONE` |
| Permission selected-target binding source | `MATERIALIZED_NOT_DISPATCHED` |
| Permission selected-target binding evidence | `NONE` |
| Permission provisioning | `NONE` |
| Runtime allowlist | `local`, `test`, `ci` only |
| Runtime allowlist change | `NOT_IMPLEMENTED` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected activation target | `null` |
| Final Shift Close migration #27 | `NOT_EXECUTED` |
| Feature activation executor | `NOT_IMPLEMENTED` |
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
- Sprint89 — application-readiness contract and fail-closed prerequisites.
- Sprint107 — nine-component runtime dependency inventory and explicit allowlist block.
- Sprint110–Sprint111 — durable-runtime readiness and exact selected-target identity.
- Sprint113–Sprint118 — attestation/ingestion, selection persistence, migration selected-target binding, and trusted DB-binding producer source readiness.
- Sprint119–Sprint129 — runtime DB-binding attestation/materialization control plane and successor-compatible historical regressions.
- Sprint130–Sprint147 — canonical runtime-control-plane and authenticated HTTP/throttle identity hardening.
- Sprint148 — exact target-bound durable-runtime capability-evidence identity qualification.
- Sprint149 — trusted capability-evidence producer source materialization without dispatch.
- Sprint150 — exact selected-runtime-class nine-component dependency-envelope qualification.
- Sprint151 — target-bound dependency-envelope evidence deterministic construction source foundation.
- Sprint152 — selected-target database binding for permission provisioning, reusing Sprint118 evidence and immediate DB readback.
- Sprint153 — trusted protected-environment dependency-envelope evidence producer source materialization without dispatch.

## 3. Sprint153 description and closure evidence

### Purpose / Why

Sprint150 established the final strict qualifier for the complete target-bound nine-component durable-runtime dependency envelope, and Sprint151 established deterministic evidence construction. Canonical post-Sprint152 still lacked the trusted protected-environment producer transport required to obtain authenticated dependency observations and publish a future qualified evidence bundle.

Discovery also proved that migration #27 selected-target database binding was not a missing source gap: the existing migration executor already consumes exact Sprint118 binding evidence. Therefore the dependency-evidence producer was the smallest material non-duplicative production-readiness gap.

### Objective / Gap

Bounded objective: `DURABLE_RUNTIME_TARGET_BOUND_DEPENDENCY_ENVELOPE_EVIDENCE_PRODUCER`.

The producer must fail closed unless it runs from canonical `main` under the protected dependency-evidence environment, sees a persisted `SELECTED_NOT_AUTHORIZED` target matching protected environment identity, resolves an exact successful trusted Sprint149 capability-evidence producer artifact, obtains dependency observations from authenticated HTTPS transport, and passes existing Sprint151 construction plus Sprint150 final qualification.

### What changed

- Added `.github/workflows/final-shift-close-durable-runtime-dependency-envelope-evidence.yml` as the trusted producer source.
- The producer has no caller target inputs and requires exact canonical source plus protected environment identity.
- Capability evidence is resolved from an exact successful trusted Sprint149 workflow run/artifact rather than accepted from the dependency-observation endpoint.
- Dependency observations are accepted only from an authenticated HTTPS protected-environment channel.
- Existing Sprint151 `FinalShiftCloseDurableRuntimeDependencyEnvelopeEvidenceProducer` remains the deterministic construction core.
- Existing Sprint150 `FinalShiftCloseDurableRuntimeDependencyEnvelope` remains the final independent qualifier.
- Added `DURABLE_RUNTIME_DEPENDENCY_ENVELOPE_EVIDENCE_PRODUCER_CONTRACT.json`, Sprint153 documentation, downstream source-readiness state, and exact-head Sprint153 regression.
- Exact-head CI proved Sprint151 historical regression still locked the old current canonical producer state. The historical extension remains `NOT_IMPLEMENTED` for Sprint151 provenance, while the workflow now accepts the successor state only when producer source is materialized and dispatch remains `NOT_PERFORMED`.
- An active-regression artifact-name assertion was corrected from `dependency-envelope-evidence.json` to the actual emitted `dependency-evidence.json` without changing production semantics or scope.

### Evidence / Qualification

- Engineering PR: #724, squash merged.
- Canonical engineering squash commit: `e42ea89fca2b3974e7fa9b0a95fa31a901e9840c`.
- Parent canonical post-Sprint152 reconciliation checkpoint: `fc1efce072abdbb3e06d4ba22d6aaaa169cd447d`.
- Final exact engineering head before merge: `44a17e4590df58472114aad547dd1cd3087f8e96`.
- Exact-head pull-request qualification: **38/38 successful**.
- Repository-native Product Owner merge-authority run: `34767471668`, successful exact-head verification.
- Initial engineering envelope: five paths; SHA-256 `4d184a18b6d9814474233bda0bf4761e7045ebfb0a8cda5ea56b6b296cef627b`.
- CI-proven Sprint151 compatibility conflict: run `34767104436`.
- Final engineering envelope: six paths; SHA-256 `7269a0e5f8927c2763411d0700b0f456cdcb0781f1d198179966a97a7b934a79`.
- Merge method: squash with expected-head guard.
- Post-merge verification: one squash commit above exact parent `fc1efce072abdbb3e06d4ba22d6aaaa169cd447d`; exact six-path engineering delta; operational NO-GO unchanged.
- Post-Sprint153 reconciliation envelope: six paths; SHA-256 `6a0fd4267f940c02d23e95ce4085e89cdf25a52186d95619e1b1da89488dfc46`.

### Operational boundaries / NO-GO

Sprint153 does not select or persist a durable target, dispatch the new dependency-evidence producer, produce real dependency-envelope evidence, execute migration #27, provision permissions, dispatch the capability-evidence producer, create real capability evidence, widen the runtime allowlist, materialize/execute feature activation, deploy/release, activate Technical Preview or Production, or activate the updater.

### Next position

After post-Sprint153 canonical reconciliation is squash merged and verified, the next engineering position is **Sprint154 bounded discovery from canonical post-Sprint153**. No Sprint154 objective, implementation, or source envelope is preselected by this manifest.

## 4. Operational truth — NO-GO remains authoritative

Current machine-readable state remains:

- migration #27 execution: `NOT_EXECUTED` / `NOT_PERFORMED`;
- permission selected-target binding source: `MATERIALIZED_NOT_DISPATCHED`;
- permission selected-target binding evidence: `NONE`;
- permission provisioning: `NONE`;
- capability-evidence producer: `MATERIALIZED_NOT_DISPATCHED`;
- capability-evidence producer dispatch: `NOT_PERFORMED`;
- real capability evidence: `NONE`;
- dependency-envelope qualifier: `MATERIALIZED_SOURCE_ONLY`;
- dependency-evidence source foundation: `MATERIALIZED_SOURCE_ONLY`;
- dependency-evidence producer: `MATERIALIZED_NOT_DISPATCHED`;
- dependency-evidence producer dispatch: `NOT_PERFORMED`;
- real dependency-envelope evidence: `NONE`;
- runtime allowlist change: `NOT_IMPLEMENTED`;
- feature activation executor: `NOT_IMPLEMENTED`;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview activation: `NOT_AUTHORIZED`;
- Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`;
- durable target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`.

Machine-readable operational authority remains in `ops/final-shift-close/STATE.json`, `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`, and `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`.

## 5. What is not yet complete

The following remain separate future work or operational actions:

- qualify, select, and persist a real non-synthetic durable target;
- produce real selected-target DB-binding evidence and execute migration #27 under separate authority;
- execute selected-target-bound permission provisioning under separate authority;
- dispatch the trusted capability-evidence producer and obtain real capability evidence;
- dispatch the trusted dependency-envelope evidence producer and obtain real dependency evidence;
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

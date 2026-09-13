# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint152
**Canonical engineering commit:** `c6abc9356ad329c1a2273a71a4a8ca0e50822825`
**Latest engineering PR:** #722 — `Sprint152: bind permission provisioning to selected target database`
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint152 engineering**. Sprint152 hardened the existing Final Shift Close permission-provisioning executor so future permission mutation cannot proceed from unbound database credentials. The executor now requires exact canonical selected-target identity, reuses exact Sprint118 selected-target database-binding evidence, and independently reads the actual provisioning database identity immediately before mutation so its canonical SHA-256 fingerprint must match the trusted binding via `hash_equals()`.

Sprint152 performed no operational execution. Canonical target selection remains blocked, migration #27 remains unexecuted, permission provisioning remains `NONE`, and feature activation remains inactive.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint152 |
| Latest engineering commit | `c6abc9356ad329c1a2273a71a4a8ca0e50822825` |
| Latest engineering PR | #722, squash merged |
| Sprint152 final engineering head | `70c30e407271c0e78439143230159f7b1c63f291` |
| Sprint152 exact-head CI | 38/38 pull-request workflow runs successful |
| Sprint152 Product Owner authority | run `34765603014`, successful exact-head repository-native verification |
| Sprint152 engineering envelope | seven paths; SHA-256 `7ed9c7cc6da5f03f73fdbd3ef18f4315896b95832331c2c2d5e2fa6bb2151590` |
| Post-Sprint152 reconciliation envelope | six paths; SHA-256 `bd3613c19baad6d74925a78dd72dcaf8121fbec842ace3e9279beaf8ecf8b971` |
| Permission selected-target binding source | `MATERIALIZED_NOT_DISPATCHED` |
| Permission selected-target binding evidence | `NONE` |
| Permission provisioning | `NONE` |
| Dependency-envelope qualifier | `MATERIALIZED_SOURCE_ONLY` |
| Dependency-evidence source foundation | `MATERIALIZED_SOURCE_ONLY` |
| Dispatchable dependency-evidence producer | `NOT_IMPLEMENTED` |
| Real dependency-envelope evidence | `NONE` |
| Trusted capability-evidence producer | `MATERIALIZED_NOT_DISPATCHED` |
| Real target-bound capability evidence | `NONE` |
| Runtime allowlist | `local`, `test`, `ci` only |
| Runtime allowlist change | `NOT_IMPLEMENTED` |
| Durable activation target | `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` |
| Selected activation target | `null` |
| Final Shift Close migration #27 | `NOT_EXECUTED` |
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
- Sprint110–Sprint111 — durable-runtime readiness and exact selected-target identity.
- Sprint113–Sprint118 — attestation/ingestion, selection persistence, migration selected-target binding, and trusted DB-binding producer source readiness.
- Sprint119–Sprint129 — runtime DB-binding attestation/materialization control plane and successor-compatible historical regressions.
- Sprint130–Sprint147 — canonical runtime-control-plane and authenticated HTTP/throttle identity hardening.
- Sprint148 — exact target-bound durable-runtime capability-evidence identity qualification.
- Sprint149 — trusted capability-evidence producer source materialization without dispatch.
- Sprint150 — exact selected-runtime-class nine-component dependency-envelope qualification.
- Sprint151 — target-bound dependency-envelope evidence construction source foundation.
- Sprint152 — selected-target database binding for permission provisioning, reusing Sprint118 evidence and immediate DB readback.

## 3. Sprint152 description and closure evidence

### Purpose / Why

The existing Final Shift Close permission-provisioning executor could perform a bounded `pos.shift.close` grant using protected environment database credentials, but those credentials were not proven to address the exact durable runtime selected by the canonical target-selection chain. Sprint118 already owns trusted selected-target database-binding evidence, so duplicating that producer would be incorrect.

### Objective / Gap

Bounded objective: `PERMISSION_PROVISIONING_SELECTED_TARGET_DATABASE_BINDING`.

Future permission provisioning must fail closed unless it resolves exact trusted Sprint118 binding evidence, proves that evidence matches the canonical `SELECTED_NOT_AUTHORIZED` target, independently reads `DATABASE()`, `@@hostname`, and `@@port` from the actual provisioning connection immediately before mutation, and matches the resulting canonical SHA-256 fingerprint to trusted evidence.

### What changed

- Hardened `.github/workflows/final-shift-close-permission-provisioning.yml` with exact binding run ID/attempt inputs and canonical selected-target preflight.
- Reused the Sprint118 `binding.json` and `execution.json` artifact rather than introducing another binding producer.
- Added exact artifact, workflow-run, target-provenance, evidence-status, and fingerprint checks.
- Added immediate pre-mutation database identity readback and `hash_equals()` binding verification.
- Preserved the exact migration #27 record/table prerequisite, protected-control authority, protected-role rejection, durable mutation journal, one-role permission grant, and `NO_DEFAULT_GRANT` assignment invariants.
- Added `PERMISSION_PROVISIONING_SELECTED_TARGET_BINDING_CONTRACT.json`, Sprint152 documentation, and an active exact-head regression.
- Exact-head CI required Sprint104 and Sprint116 historical workflows to become successor-compatible without weakening their owned semantic or NO-GO invariants.

### Evidence / Qualification

- Engineering PR: #722, squash merged.
- Canonical engineering squash commit: `c6abc9356ad329c1a2273a71a4a8ca0e50822825`.
- Parent canonical post-Sprint151 reconciliation checkpoint: `28e5ed8a29f4e4f7d2436f26f87de9341efc3b0d`.
- Final exact engineering head before merge: `70c30e407271c0e78439143230159f7b1c63f291`.
- Exact-head pull-request qualification: **38/38 successful**.
- Repository-native Product Owner merge-authority run: `34765603014`, successful exact-head verification.
- Final engineering envelope: seven paths.
- Frozen engineering envelope SHA-256: `7ed9c7cc6da5f03f73fdbd3ef18f4315896b95832331c2c2d5e2fa6bb2151590`.
- Initial five-path CI exposed Sprint104 full-envelope ownership; the six-path correction then exposed a Sprint104 historical-fingerprint assertion and Sprint116 successor-state conflict. Both were corrected only through bounded historical workflow compatibility changes.
- Merge method: squash with expected-head guard.
- Post-merge verification: one squash commit above exact parent `28e5ed8a29f4e4f7d2436f26f87de9341efc3b0d`; exact seven-path engineering delta; operational NO-GO unchanged.
- Post-Sprint152 reconciliation envelope: six paths; SHA-256 `bd3613c19baad6d74925a78dd72dcaf8121fbec842ace3e9279beaf8ecf8b971`.

### Operational boundaries / NO-GO

Sprint152 does not select or persist a durable target, dispatch the Sprint118 binding producer, create real selected-target DB-binding evidence, execute migration #27, provision permissions, dispatch capability/dependency producers, create real capability/dependency evidence, widen the runtime allowlist, activate the feature, deploy/release, activate Technical Preview or Production, or activate the updater.

### Next position

After post-Sprint152 canonical reconciliation is squash merged and verified, the next engineering position is **Sprint153 bounded discovery from canonical post-Sprint152**. No Sprint153 objective, implementation, or source envelope is preselected by this manifest.

## 4. Operational truth — NO-GO remains authoritative

Current machine-readable state remains:

- migration #27 execution: `NOT_EXECUTED` / `NOT_PERFORMED`;
- permission provisioning: `NONE`;
- permission selected-target binding source: `MATERIALIZED_NOT_DISPATCHED`;
- permission selected-target binding evidence: `NONE`;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview activation: `NOT_AUTHORIZED`;
- Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`;
- durable target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- capability-evidence producer: `MATERIALIZED_NOT_DISPATCHED`;
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
- produce real selected-target DB-binding evidence and execute migration #27 under separate authority;
- execute selected-target-bound permission provisioning under separate authority;
- dispatch trusted capability-evidence production and obtain real capability evidence;
- materialize a separately qualified dispatchable dependency-envelope evidence producer and obtain real dependency evidence;
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

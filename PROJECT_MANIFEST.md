# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint154
**Canonical engineering commit:** `5349acbffd1c90087d37a1a0f74ce0ffd7e87773`
**Latest engineering PR:** #726 — `Sprint154: materialize feature activation executor source foundation`
**Status date:** 2026-09-14

> This file is the canonical human-readable source of truth for current oneQay project status. Machine-readable operational state remains authoritative for operational gates.

## 1. Current project state

oneQay remains an actively engineered enterprise business-management platform using Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint154 engineering**. Sprint154 materialized a framework-independent, deterministic source foundation for a future selected-target-bound Final Shift Close feature-activation executor. The source validates future exact prerequisites and produces only an execution plan; it does not provide a dispatchable executor or concrete configuration-mutation transport.

Sprint154 also made the historical Sprint116 downstream-readiness regression successor-compatible after exact-head CI proved one stale feature-activation eligibility assertion. No operational action occurred.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest completed engineering sprint | Sprint154 |
| Latest engineering commit | `5349acbffd1c90087d37a1a0f74ce0ffd7e87773` |
| Latest engineering PR | #726, squash merged |
| Sprint154 final engineering head | `132dbd0048ad40248efe093e22f276b487890487` |
| Sprint154 exact-head CI | 39/39 pull-request workflow runs successful |
| Sprint154 Product Owner authority | run `34772516177`, successful repository-native verification |
| Sprint154 final engineering envelope | seven paths; SHA-256 `5dcf1fcd0b638ed9ec3d311947055a2b2c96c74d8e8fb5b74ff1f1b96bd1296f` |
| Sprint154 initial source-foundation envelope | six paths; SHA-256 `35499fbb12404b3ab5f25de924d4528060f4cf4eb362091b116e5faf3c3766c3` |
| Post-Sprint154 reconciliation envelope | six paths; SHA-256 `ba0208b79fb9790435dcc968fe85e05aece4a980cb891e4c2e946efcbc65650f` |
| Activation executor source foundation | `MATERIALIZED_SOURCE_ONLY` |
| Dispatchable feature-activation executor | `NOT_IMPLEMENTED` |
| Configuration-mutation transport | `NOT_IMPLEMENTED` |
| Activation executor dispatch | `NOT_PERFORMED` |
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
- Sprint154 — selected-target-bound feature-activation execution-plan source foundation; concrete transport and dispatchable executor remain absent.

## 3. Sprint154 description and closure evidence

### Purpose / Why

Canonical post-Sprint153 still had no Final Shift Close feature-activation executor. Discovery proved the provider remains restricted to `local/test/ci`, while the future durable target must be a non-synthetic isolated non-production runtime. Discovery also proved the repository had capability requirements for authenticated configuration mutation/readback/health/rollback, but no Final Shift Close repository-native concrete activation transport.

### Objective / Gap

Bounded objective: `FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION_EXECUTOR_SOURCE_FOUNDATION`.

Sprint154 therefore materialized only the deterministic source foundation required before any future dispatchable executor can exist. It binds a future plan to the exact selected target, migration #27 executed on that target, same-target permission provisioning, full durable dependency-envelope qualification, and separate exact feature-activation authority.

### What changed

- Added `FinalShiftCloseFeatureActivationExecutionPlan.php`.
- Added an executable regression proving current canonical NO-GO fails closed and future exact prerequisite records produce a deterministic plan.
- Added `FEATURE_ACTIVATION_EXECUTOR_SOURCE_FOUNDATION_CONTRACT.json`.
- Advanced downstream readiness to record the source foundation while keeping the dispatchable executor and configuration transport `NOT_IMPLEMENTED`.
- Added the active Sprint154 regression and six-section Sprint154 documentation.
- Exact-head CI run `34772115090` proved historical Sprint116 still required the pre-Sprint154 feature-activation eligibility value. The final engineering envelope expanded by exactly one historical workflow path and Sprint116 now accepts the stronger successor state only while operational NO-GO remains preserved.

### Evidence / Qualification

- Engineering PR: #726, squash merged.
- Canonical engineering squash commit: `5349acbffd1c90087d37a1a0f74ce0ffd7e87773`.
- Parent canonical post-Sprint153 checkpoint: `a2b6177d030608775c90f370c17ba0779f28d783`.
- Final exact engineering head: `132dbd0048ad40248efe093e22f276b487890487`.
- Exact-head pull-request qualification: **39/39 successful**.
- Repository-native Product Owner merge-authority run: `34772516177`, successful.
- Initial six-path envelope SHA-256: `35499fbb12404b3ab5f25de924d4528060f4cf4eb362091b116e5faf3c3766c3`.
- CI-proven Sprint116 compatibility conflict: run `34772115090`.
- Final seven-path engineering envelope SHA-256: `5dcf1fcd0b638ed9ec3d311947055a2b2c96c74d8e8fb5b74ff1f1b96bd1296f`.
- Post-merge verification: exactly one squash commit above `a2b6177d030608775c90f370c17ba0779f28d783`, with exactly the seven engineering paths.
- Post-Sprint154 reconciliation envelope: six paths; SHA-256 `ba0208b79fb9790435dcc968fe85e05aece4a980cb891e4c2e946efcbc65650f`.

### Operational boundaries / NO-GO

Sprint154 does not persist a target, dispatch an activation executor, implement concrete configuration transport, execute migration #27, provision permissions, produce real capability/dependency evidence, widen the runtime allowlist, activate Final Shift Close, deploy/release, activate Technical Preview/Production, or activate the updater.

### Next position

After post-Sprint154 reconciliation is squash merged and verified, the next engineering position is **Sprint155 bounded discovery from canonical post-Sprint154**. No Sprint155 objective, implementation, or source envelope is preselected.

## 4. Operational truth — NO-GO remains authoritative

Current machine-readable state remains:

- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- selected target: `null`;
- target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- capability-evidence producer dispatch: `NOT_PERFORMED`; real capability evidence: `NONE`;
- dependency-evidence producer dispatch: `NOT_PERFORMED`; real dependency evidence: `NONE`;
- activation executor source foundation: `MATERIALIZED_SOURCE_ONLY`;
- dispatchable feature-activation executor: `NOT_IMPLEMENTED`;
- configuration-mutation transport: `NOT_IMPLEMENTED`;
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
- `docs/SPRINT*.md` — detailed bounded sprint evidence;
- `ops/final-shift-close/*.json` — machine-readable operational authority;
- merged PRs and Git history — immutable implementation provenance.

## 6. Mandatory sprint description and update rule

Every material sprint documents **Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position**. Every material closed sprint reconciles this manifest and the four root summary documents, while the just-closed sprint workflow relinquishes full-envelope ownership and remains successor-compatible.

Author by Lab | zefry

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Canonical engineering checkpoint:** Sprint134  
**Canonical engineering commit:** `185cbe9ddd4346b9d8c9e7ac7af283a20b9617bd`  
**Latest engineering PR:** #684 — `Sprint134: canonical control-plane delivery gate registration regression`  
**Status date:** 2026-09-12

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP must summarize or reference this manifest rather than independently inventing a newer project state. Per-sprint documents and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a modular-monolith-first architecture. The repository has progressed through Sprint134. The latest engineering chain is focused on Final Shift Close readiness, fail-closed runtime control-plane boundaries, and executable regression evidence.

The repository is **not** represented as production-activated merely because source, migrations, providers, controllers, workflows, or regression evidence exist. Source publication and operational activation are separate lifecycle states.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint134 |
| Latest engineering commit | `185cbe9ddd4346b9d8c9e7ac7af283a20b9617bd` |
| Latest engineering PR | #684, merged |
| Sprint134 exact-head CI | 18/18 pull-request workflow runs successful |
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

## 2. What has actually been completed

### Foundation and governance

The repository has established the core modular-monolith architecture, tenant isolation model, deny-by-default authorization posture, versioned REST governance, correlation/idempotency conventions, CI governance, bounded source envelopes, exact-head qualification, and repository-native Product Owner merge-authorization controls.

### POS and accounting evidence chain

Earlier bounded sprints published POS foundations including register/shift opening, sale completion/payment/receipt evidence, catalog preparation, and subsequent cash/shift evidence work. The post-Sprint54 sequence then advanced JRN-010 prerequisite work through expected-cash derivation, immutable sale-to-shift binding, cash-variance source readiness/foundation, adjudication/explanation evidence, and maker-checker/reviewer authorization controls.

Representative canonical milestones from Git history include:

- Sprint55 — bounded expected-cash derivation entry gate;
- Sprint64 — cash-variance source foundation;
- Sprint70 — durable cash-variance explanation foundation;
- Sprint80 — scoped cash-variance reviewer authorization policy and implementation chain.

These milestones are source/evidence achievements. They do not by themselves imply production activation.

### Final Shift Close readiness chain

The later sprint sequence moved from cash-variance readiness into Final Shift Close source and runtime-control-plane hardening.

- Sprint88 materialized migration #27 as a **source-only** Final Shift Close migration foundation while explicitly preserving no-execution/no-runtime/no-permission/no-activation boundaries.
- Sprint89 selected the Final Shift Close application-readiness contract and fail-closed prerequisites.
- Subsequent sprints progressively qualified application, provider, runtime binding, manifest, database-binding attestation, authorization, token policy, and historical compatibility boundaries.
- Sprint130 established the canonical runtime control-plane token policy: minimum 32, maximum 512, canonical allowed-character policy, exact bearer semantics, and fail-closed handling.
- Sprint131 added CI-only synthetic middleware positive-path regression.
- Sprint132 added CI-only synthetic direct-controller positive-path regression.
- Sprint133 added direct-controller fail-closed regression.
- Sprint134 added provider/route delivery-gate registration regression for a canonical-length token containing a disallowed character, while preserving historical regressions and the operational NO-GO boundary.

## 3. Sprint134 closure evidence

Sprint134 is closed through PR #684.

- Canonical squash commit: `185cbe9ddd4346b9d8c9e7ac7af283a20b9617bd`
- Parent engineering commit: `5e6b4929cfbf8e6c54fede283df249b60db9bf1d` (Sprint133)
- Exact changed engineering envelope: five paths
- Frozen Sprint134 envelope SHA-256: `73b87b460ef349a05ab801d10fc6dd4a19d056625f4a042ff621ab96a12d2c0e`
- Exact-head pull-request workflows: 18/18 successful
- Product Owner merge authority workflow: successful
- Merge method: squash

Detailed Sprint134 evidence remains in:

- `docs/SPRINT134_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_DELIVERY_GATE_REGISTRATION_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_DELIVERY_GATE_REGISTRATION_REGRESSION_CONTRACT.json`

## 4. Operational truth — NO-GO remains authoritative

The following states are intentionally unchanged and must not be inferred from source readiness:

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
- feature-activation evidence producer: `NOT_IMPLEMENTED`.

Machine-readable authority remains in:

- `ops/final-shift-close/STATE.json`
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

If this manifest and those machine-readable state files ever conflict on an operational state, the machine-readable state files are authoritative for the operational gate.

## 5. What is not yet complete

The following must not be described as completed unless separately qualified and authorized:

- execution of migration #27 in an operational environment;
- provisioning of Final Shift Close permissions;
- activation of the Final Shift Close feature;
- selection and qualification of a non-synthetic durable activation target;
- real operational runtime-binding manifest materialization;
- real operational database-binding attestation;
- Technical Preview activation;
- Production activation;
- deployment/release publication;
- updater activation.

## 6. Next engineering position

The next engineering activity after this documentation reconciliation is **Sprint135 bounded discovery from the canonical post-Sprint134 engineering checkpoint**. Sprint135 is not considered started or completed merely because it appears in planning documents.

Any Sprint135 work must select the smallest non-duplicative engineering gap, preserve historical executable regressions, remain fail-closed, and avoid converting source readiness into operational authority.

## 7. Documentation responsibility model

To prevent another documentation drift:

- `PROJECT_MANIFEST.md` — canonical current project state and lifecycle truth;
- `README.md` — concise repository entry point and current status summary;
- `CHANGELOG.md` — chronological material-change summary, not a second state database;
- `TASKS.md` — current completed/pending workboard;
- `ROADMAP.md` — forward-looking sequencing and gates;
- per-sprint files under `docs/` — detailed bounded engineering evidence;
- `ops/final-shift-close/*.json` — machine-readable operational gate authority;
- Git history / merged PRs — immutable implementation provenance.

Historical status sections in technical handbooks may describe the state at the time they were written. They are provenance, not the current project-state authority.

## 8. Update rule

Every future closed sprint that materially changes project state should update this manifest, README, TASKS, ROADMAP, and CHANGELOG in the same bounded documentation reconciliation or explicitly record why no update is required. Current status must never be inferred from an old sprint heading left at the top of a handbook.

Author by Lab | zefry

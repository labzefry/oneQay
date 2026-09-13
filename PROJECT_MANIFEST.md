# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint148
**Canonical engineering commit:** `7a07a3163842e60332ccd3e3780d4e970280d46c`
**Latest engineering PR:** #714 — `Sprint148: durable runtime target-bound capability evidence identity binding`
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint148**. The current Final Shift Close chain now qualifies capability-specific durable-runtime evidence identity against the exact Sprint111 selected-target model while deliberately keeping source readiness separate from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint148 |
| Latest engineering commit | `7a07a3163842e60332ccd3e3780d4e970280d46c` |
| Latest engineering PR | #714, merged |
| Sprint148 final engineering head | `eba8687297e7b82bf5adfcd770d86233139b0454` |
| Sprint148 exact-head CI | 33/33 pull-request workflow runs successful |
| Sprint148 Product Owner authority | run `34756294306`, successful exact-head repository-native verification |
| Sprint148 engineering envelope | seven paths; SHA-256 `f7d9cfb173b54ac863cc70f10b9ae3df2f6715a4abdae0c7c32cbdb399bda5a6` |
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
| Trusted capability-evidence producer | `NOT_IMPLEMENTED` |

## 2. Material engineering progress

### Platform, governance, and POS foundation

The repository has established modular-monolith architecture, tenant isolation, deny-by-default authorization, first-party session and privileged-authentication foundations, versioned REST governance, CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work includes shift/register opening, sale completion/payment/receipt evidence, tenant/outlet-scoped catalog preparation, durable idempotency preservation, and historical regression compatibility.

### JRN-010 cash and variance chain

Representative milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. These remain source/evidence achievements, not production activation.

### Final Shift Close engineering chain

- Sprint88 — migration #27 source materialization only; migration remained `NOT_EXECUTED`.
- Sprint89 — application-readiness contract and fail-closed prerequisites.
- Sprint110 — durable-runtime readiness shape, including required capability claims.
- Sprint111 — exact durable-runtime selected-target identity and deterministic selection fingerprint, still `SELECTED_NOT_AUTHORIZED`.
- Sprint113–Sprint117 — attestation producer/ingestion, selection-persistence and selected-target binding readiness, all source-only and non-operational.
- Sprint130 — canonical runtime control-plane token policy.
- Sprint131–Sprint133 — middleware/controller positive-path and fail-closed qualification.
- Sprint134–Sprint135 — delivery-gate and route-registration metadata qualification.
- Sprint136–Sprint138 — authenticated HTTP positive, fail-closed, and throttle qualification.
- Sprint139 — authentication-before-throttle hardening.
- Sprint140–Sprint141 — authentication-rejection response hardening and HTTP-kernel propagation.
- Sprint142 — authenticated throttle-rejection response hardening.
- Sprint143 — DB-attestation HEAD throttle-rejection parity.
- Sprint144 — named-route identity ownership hardening.
- Sprint145 — canonical controller-action identity added to throttle-response ownership.
- Sprint146 — exact canonical per-route throttle-budget identity added to response ownership.
- Sprint147 — canonical throttle-rejection metadata shape added to response ownership.
- Sprint148 — target-bound durable-runtime capability-evidence identity qualification added before any future runtime-allowlist eligibility.

## 3. Sprint148 description and closure evidence

### Purpose / Why

Sprint110 established canonical durable-runtime readiness claims and Sprint111 bound a qualified readiness attestation to an exact durable target identity. The four required capability booleans were necessary but were not themselves capability-specific evidence proving those capabilities for the exact selected environment, runtime class, running source, artifact, readiness attestation, and selection fingerprint.

### Objective / Gap

Bounded objective: `DURABLE_RUNTIME_TARGET_BOUND_CAPABILITY_EVIDENCE_IDENTITY_BINDING`.

Sprint148 requires future capability evidence to be bound to the exact Sprint111 selected target. Qualification requires the Sprint110 readiness attestation to remain valid, Sprint111 selection to recompute exactly, activation authority to remain `NOT_GRANTED`, feature activation to remain `INACTIVE`, runtime allowlist change to remain `NOT_IMPLEMENTED`, and all four capability-evidence records to be `VERIFIED`, secret-free, digest-qualified, and bound to the same deterministic target-binding SHA-256.

### What changed

- Added `FinalShiftCloseDurableRuntimeCapabilityEvidence` as a pure application-layer target-bound evidence qualifier.
- Added executable Sprint148 positive and fail-closed regressions for missing, malformed, cross-target, identity-drift and secret-bearing evidence.
- Added `DURABLE_RUNTIME_CAPABILITY_EVIDENCE_BINDING_CONTRACT.json`.
- Extended `POST_SELECTION_DOWNSTREAM_READINESS.json` so future runtime-allowlist eligibility explicitly requires target-bound capability-evidence identity qualification before full durable-envelope qualification.
- Added an exact-head Sprint148 workflow and detailed six-section Sprint148 document.
- Exact-head CI proved historical Sprint116 still owned its original full three-path PR envelope. Sprint148 therefore converted only that workflow to successor-compatible historical regression while preserving its owned post-selection and NO-GO invariants.
- No real capability-evidence producer, target selection, allowlist widening, activation, provisioning, migration execution, or deployment occurred.

### Evidence / Qualification

- Engineering PR: #714, squash merged.
- Canonical engineering squash commit: `7a07a3163842e60332ccd3e3780d4e970280d46c`.
- Parent canonical post-Sprint147 reconciliation checkpoint: `54e209c6ef50b1f42c38fd30c7e6e12d757a8cd9`.
- Final exact engineering head before merge: `eba8687297e7b82bf5adfcd770d86233139b0454`.
- Exact-head pull-request qualification: **33/33 successful**.
- Repository-native Product Owner merge-authority run: `34756294306`, successful exact-head verification.
- Final Sprint148 engineering envelope: seven paths.
- Frozen engineering envelope SHA-256: `f7d9cfb173b54ac863cc70f10b9ae3df2f6715a4abdae0c7c32cbdb399bda5a6`.
- Merge method: squash with expected-head guard.
- Post-merge verification: one squash commit with parent exactly `54e209c6ef50b1f42c38fd30c7e6e12d757a8cd9`; operational NO-GO unchanged.
- Post-Sprint148 reconciliation envelope: six paths; frozen SHA-256 `5c315771e9777b9d5a5b428a206c7cec711e14a7851a83c685a5a5cfb378dfc5`.

Final engineering paths:

1. `.github/workflows/sprint116-final-shift-close-post-selection-downstream-readiness.yml`
2. `.github/workflows/sprint148-final-shift-close-durable-runtime-capability-evidence-binding-regression.yml`
3. `apps/web/app/Application/Pos/FinalShiftCloseDurableRuntimeCapabilityEvidence.php`
4. `apps/web/tests/pos-final-shift-close-durable-runtime-capability-evidence-binding.php`
5. `docs/SPRINT148_FINAL_SHIFT_CLOSE_DURABLE_RUNTIME_CAPABILITY_EVIDENCE_BINDING.md`
6. `ops/final-shift-close/DURABLE_RUNTIME_CAPABILITY_EVIDENCE_BINDING_CONTRACT.json`
7. `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`

### Operational boundaries / NO-GO

Sprint148 does not grant or perform operational activation. It does not execute migration #27, provision permissions, persist or activate a durable target, produce or ingest real capability evidence, provision an operational runtime token, change the Final Shift Close runtime allowlist, materialize or execute feature activation, perform operational runtime-binding manifest materialization or operational DB attestation, deploy/release, activate Technical Preview or Production, or activate the updater.

### Next position

After this canonical reconciliation is squash merged and verified, the next engineering position is **Sprint149 bounded discovery from canonical post-Sprint148**. No Sprint149 objective, implementation, or source envelope is preselected by this manifest.

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
- trusted capability-evidence producer: `NOT_IMPLEMENTED`.

Machine-readable operational authority:

- `ops/final-shift-close/STATE.json`
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

If this manifest and the machine-readable state files ever conflict on an operational state, the machine-readable state files are authoritative for that gate.

## 5. What is not yet complete

The following must not be described as complete unless separately qualified and authorized:

- qualification and selection of a real non-synthetic durable activation target;
- production of real target-bound capability evidence;
- trusted capability-evidence producer materialization/dispatch;
- execution of migration #27 in an operational environment;
- provisioning of Final Shift Close permissions;
- activation of Final Shift Close;
- real operational runtime-binding manifest materialization;
- real operational database-binding attestation;
- operational runtime-token provisioning;
- runtime allowlist widening for a selected durable runtime class;
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

Every material sprint must document, at minimum:

1. **Purpose / Why** — why the sprint exists;
2. **Objective / Gap** — the exact non-duplicative bounded problem;
3. **What changed** — source/test/workflow/documentation delta;
4. **Evidence / Qualification** — PR, exact head, CI result, authority, merge SHA, envelope;
5. **Operational boundaries / NO-GO** — what the sprint explicitly did not activate or execute;
6. **Next position** — the next bounded discovery checkpoint without preselecting an unproven implementation.

The detailed version belongs in the per-sprint document; this manifest and root summaries must preserve a concise current description.

## 8. Update rule

Every material closed sprint must reconcile this manifest and the four root summary documents during closure. The just-closed sprint workflow must relinquish full-envelope ownership and remain successor-compatible while preserving its historical executable regression.

Author by Lab | zefry

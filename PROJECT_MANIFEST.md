# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint145
**Canonical engineering commit:** `6d4fc06ac1166d15d8598d2a6d39d594f2493767`
**Latest engineering PR:** #707 — `Sprint145: canonical action identity throttle response hardening`
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint145**. The latest engineering chain focuses on Final Shift Close runtime control-plane qualification while deliberately separating source readiness from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint145 |
| Latest engineering commit | `6d4fc06ac1166d15d8598d2a6d39d594f2493767` |
| Latest engineering PR | #707, merged |
| Sprint145 exact-head CI | 29/29 pull-request workflow runs successful |
| Sprint145 Product Owner authority | run `34749677796`, successful |
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

## 2. Material engineering progress

### Platform, governance, and POS foundation

The repository has established modular-monolith architecture, tenant isolation, deny-by-default authorization, first-party session and privileged-authentication foundations, versioned REST governance, CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work includes shift/register opening, sale completion/payment/receipt evidence, tenant/outlet-scoped catalog preparation, durable idempotency preservation, and historical regression compatibility.

### JRN-010 cash and variance chain

Representative milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. These remain source/evidence achievements, not production activation.

### Final Shift Close engineering chain

- Sprint88 — migration #27 source materialization only; migration remained `NOT_EXECUTED`.
- Sprint89 — application-readiness contract and fail-closed prerequisites.
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

## 3. Sprint145 description and closure evidence

### Purpose / Why

Sprint144 had already limited throttle-response rewriting to canonical route name + exact method + exact path. The remaining gap was that a route could reproduce those three signals while using a noncanonical action/controller and still be treated as owned by the hardener. Sprint145 closes that ownership ambiguity.

### Objective / Gap

Bounded objective: `CANONICAL_CONTROL_PLANE_ACTION_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION`.

The canonical throttle-response hardener must own a response only when all four signals match:

1. canonical route name;
2. canonical controller action;
3. exact HTTP method;
4. exact internal path.

A same-name/path/method route with a noncanonical action remains framework-owned.

### What changed

- Added canonical controller-action matching to `HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware`.
- Added a dedicated Sprint145 executable regression for canonical vs noncanonical action ownership.
- Corrected the historical Sprint144 positive fixture to carry the canonical controller action metadata produced by Laravel routing.
- Preserved Sprint144 behavior, canonical route registration, auth-before-throttle ordering, token policy, throttle budgets, controllers, and application services.

### Evidence / Qualification

- Engineering PR: #707, squash merged.
- Canonical engineering squash commit: `6d4fc06ac1166d15d8598d2a6d39d594f2493767`.
- Parent canonical documentation checkpoint: `a3ab64bffae0f1322eb731908b9ca1bf9dddf9b6`.
- Final exact-head SHA before merge: `eeb93032fb8611e031d207ce95c1825dea7e2f2d`.
- Exact-head pull-request qualification: 29/29 successful.
- Product Owner merge-authority workflow run: `34749677796`, successful.
- Exact Sprint145 engineering envelope: six paths.
- Frozen engineering envelope SHA-256: `ed1a67c7a7b89e26cd4c3ade350132b8eca7c4e2142f76d9b69495ac0ba2fad2`.
- Merge method: squash with expected-head guard.

Detailed evidence:

- `docs/SPRINT145_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_ACTION_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_ACTION_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`
- `apps/web/tests/final-shift-close-runtime-control-plane-action-identity-throttle-response-hardening-regression.php`

### Operational boundaries / NO-GO

Sprint145 does not grant or perform operational activation. It does not execute migration #27, provision permissions, provision runtime tokens, select or activate a durable target, deploy/release, activate Technical Preview or Production, or activate the updater.

### Next position

The next engineering position is **Sprint146 bounded discovery from canonical post-Sprint145**. No Sprint146 objective or source envelope is preselected by this manifest.

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
- feature-activation evidence producer: `NOT_IMPLEMENTED`.

Machine-readable operational authority:

- `ops/final-shift-close/STATE.json`
- `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`

If this manifest and the machine-readable state files ever conflict on an operational state, the machine-readable state files are authoritative for that gate.

## 5. What is not yet complete

The following must not be described as complete unless separately qualified and authorized:

- execution of migration #27 in an operational environment;
- provisioning of Final Shift Close permissions;
- activation of Final Shift Close;
- qualification and selection of a non-synthetic durable activation target;
- real operational runtime-binding manifest materialization;
- real operational database-binding attestation;
- operational runtime-token provisioning;
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

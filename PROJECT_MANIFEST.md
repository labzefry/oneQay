# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint146
**Canonical engineering commit:** `a5e4aec8c142e7478a0e58d2d732dbf106393b06`
**Latest engineering PR:** #709 — `Sprint146: canonical throttle budget identity response hardening`
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint146**. The current Final Shift Close engineering chain qualifies runtime control-plane behavior while deliberately keeping source readiness separate from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint146 |
| Latest engineering commit | `a5e4aec8c142e7478a0e58d2d732dbf106393b06` |
| Latest engineering PR | #709, merged |
| Sprint146 exact-head CI | 30/30 pull-request workflow runs successful |
| Sprint146 Product Owner authority | run `34750648988`, successful |
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
- Sprint146 — exact canonical per-route throttle-budget identity added to response ownership.

## 3. Sprint146 description and closure evidence

### Purpose / Why

Sprint145 completed request ownership for the Final Shift Close throttle-response hardener using canonical route name, canonical controller action, exact HTTP method, and exact internal path. The response-side ownership check still accepted any present `X-RateLimit-Limit` value, so a canonical request identity paired with an unexpected throttle ceiling could still be rewritten as if it were canonical.

### Objective / Gap

Bounded objective: `CANONICAL_CONTROL_PLANE_THROTTLE_BUDGET_IDENTITY_RESPONSE_HARDENING_REGRESSION`.

Response ownership now requires the previously qualified request identity plus the exact canonical rate-limit ceiling:

1. materialization POST requires `X-RateLimit-Limit: 1`;
2. DB-attestation GET requires `X-RateLimit-Limit: 2`;
3. DB-attestation HEAD requires `X-RateLimit-Limit: 2`.

Cross-route ceilings, arbitrary ceilings, and numeric aliases such as `01` or `02` remain framework-owned.

### What changed

- Added explicit canonical throttle-limit constants to `HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware`.
- Added `canonicalThrottleLimit(Request): ?string` so request identity and expected response budget are resolved together.
- Required exact-string equality between the response `X-RateLimit-Limit` header and the route's canonical ceiling before privacy/security `429` rewriting.
- Added a dedicated Sprint146 regression covering materialization POST and DB-attestation GET/HEAD canonical and mismatched budgets.
- Added Sprint146 machine-readable contract, exact-head workflow, and detailed six-section Sprint description.
- Preserved Sprint145 action identity and Sprint142 real HTTP-kernel throttle evidence.

### Evidence / Qualification

- Engineering PR: #709, squash merged.
- Canonical engineering squash commit: `a5e4aec8c142e7478a0e58d2d732dbf106393b06`.
- Parent canonical documentation checkpoint: `ebaf16c64245c67e9ecf8cac613696e5661a02ac`.
- Final exact-head SHA before merge: `b520b8e8c565a96b4c41e7838a68492f4b836066`.
- Exact-head pull-request qualification: 30/30 successful.
- Product Owner merge-authority workflow run: `34750648988`, successful.
- Exact Sprint146 engineering envelope: five paths.
- Frozen engineering envelope SHA-256: `bbc0da27fe84ca8a1fafcf4b76bcf9e1f42e595c01d35544a94793c1a7fec161`.
- Merge method: squash with expected-head guard.

Detailed evidence:

- `docs/SPRINT146_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_THROTTLE_BUDGET_IDENTITY_RESPONSE_HARDENING_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_THROTTLE_BUDGET_IDENTITY_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`
- `apps/web/tests/final-shift-close-runtime-control-plane-throttle-budget-identity-response-hardening-regression.php`

### Operational boundaries / NO-GO

Sprint146 does not grant or perform operational activation. It does not execute migration #27, provision permissions, activate Final Shift Close, provision an operational runtime token, select or activate a durable target, perform operational runtime-binding manifest materialization or operational DB attestation, deploy/release, activate Technical Preview or Production, or activate the updater.

### Next position

The next engineering position is **Sprint147 bounded discovery from canonical post-Sprint146**. No Sprint147 objective or source envelope is preselected by this manifest.

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

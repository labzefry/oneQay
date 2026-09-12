# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint136
**Canonical engineering commit:** `2a92a870d9c8388bdb9ac4f516e9d671237ce116`
**Latest engineering PR:** #688 — `Sprint136: canonical control-plane authenticated HTTP positive path regression`
**Status date:** 2026-09-12

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint136**. The latest engineering chain focuses on Final Shift Close runtime control-plane qualification while deliberately separating source readiness from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint136 |
| Latest engineering commit | `2a92a870d9c8388bdb9ac4f516e9d671237ce116` |
| Latest engineering PR | #688, merged |
| Sprint136 exact-head CI | 20/20 pull-request workflow runs successful |
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

### Platform, governance, and POS foundation

The repository has established the modular-monolith architecture, tenant isolation model, deny-by-default authorization posture, first-party session and privileged-authentication foundations, versioned REST governance, CI/governance controls, exact-head qualification, and repository-native Product Owner merge authorization.

Bounded POS work includes register/shift opening, sale completion/payment/receipt evidence, tenant/outlet-scoped catalog preparation, durable idempotency preservation, and historical regression compatibility.

### JRN-010 cash and variance chain

Representative material milestones include Sprint55 expected-cash derivation, Sprint64 cash-variance source foundation, Sprint70 durable cash-variance explanation, and Sprint80 scoped reviewer authorization. The broader chain established immutable sale-to-shift binding, cash-variance evidence, explanation/adjudication, maker-checker, and reviewer authorization foundations.

These are source/evidence achievements, not production activation.

### Final Shift Close engineering chain

- Sprint88 — migration #27 source materialization only; migration remained `NOT_EXECUTED`.
- Sprint89 — Final Shift Close application-readiness contract and fail-closed prerequisites.
- Later bounded sprints progressively qualified application/service, provider, runtime-binding manifest, DB-binding attestation, authorization, target-readiness, and historical compatibility boundaries.
- Sprint130 — canonical runtime control-plane token policy.
- Sprint131 — CI-only synthetic middleware positive-path regression.
- Sprint132 — CI-only synthetic direct-controller positive-path regression.
- Sprint133 — direct-controller fail-closed regression.
- Sprint134 — cross-provider delivery-gate route-absence regression.
- Sprint135 — canonical-valid-token cross-provider route-registration metadata and inertness regression.
- Sprint136 — canonical-valid-token authenticated HTTP positive-path regression proving the composed registered route → token middleware → real controller path for both control planes while side-effect application services remain synthetic/test-owned and production filesystem/database adapters remain unresolved.

## 3. Sprint136 closure evidence

Sprint136 is closed through engineering PR #688.

- Canonical engineering squash commit: `2a92a870d9c8388bdb9ac4f516e9d671237ce116`
- Parent canonical documentation checkpoint: `8e839881d7022df46516cd5a8ce83cdc58ee7a1b`
- Exact Sprint136 engineering envelope: four paths
- Frozen Sprint136 envelope SHA-256: `b459daf7b7f52fd2b028452b821dfa13760f8b050116bdf2fdb5db4d8485fca8`
- Final exact-head engineering SHA before merge: `93c447566efbd75e3e206ab0756cf49716231b11`
- Exact-head pull-request workflows: 20/20 successful
- Product Owner merge authority workflow run `34685054761`: successful
- Merge method: squash with exact-head guard

Sprint136 qualified a test-process-only authenticated HTTP composition using a canonical-minimum valid synthetic token. Real registered routes, real token middleware, and real controllers were exercised. Materialization and DB-attestation side effects were supplied by synthetic/test-owned application instances and in-memory doubles; production filesystem and database identity adapters were bomb-isolated and remained unresolved.

Detailed evidence:

- `docs/SPRINT136_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_AUTHENTICATED_HTTP_POSITIVE_PATH_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_AUTHENTICATED_HTTP_POSITIVE_PATH_REGRESSION_CONTRACT.json`
- `apps/web/tests/final-shift-close-runtime-control-plane-authenticated-http-positive-path-regression.php`

## 4. Operational truth — NO-GO remains authoritative

Sprint136 does not grant operational authority. Current machine-readable state remains:

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

If this manifest and the machine-readable state files ever conflict on an operational state, the machine-readable state files are authoritative for that operational gate.

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

## 6. Next engineering position

The next engineering activity is **Sprint137 bounded discovery from canonical post-Sprint136**.

Sprint137 is not considered started or complete merely because it is named here. Bounded discovery must identify the smallest non-duplicative engineering gap, preserve Sprint130–Sprint136 executable ownership, remain fail-closed and deny-by-default, and avoid converting source readiness into operational authority.

No Sprint137 implementation or source envelope is preselected by this manifest.

## 7. Documentation responsibility model

- `PROJECT_MANIFEST.md` — canonical human-readable current project/lifecycle state;
- `README.md` — concise entry-point summary;
- `CHANGELOG.md` — material chronology;
- `TASKS.md` — current completed/pending workboard;
- `ROADMAP.md` — forward sequencing and lifecycle gates;
- `docs/SPRINT*.md` — detailed bounded sprint evidence;
- `ops/final-shift-close/*.json` — machine-readable operational authority;
- merged PRs and Git history — immutable implementation provenance.

Historical status sections in technical handbooks remain provenance and do not override this manifest.

## 8. Update rule

Every material closed sprint must reconcile this manifest and the four root summary documents in the closure/handoff, or explicitly record why no current-state update is required. The just-closed active sprint workflow must also relinquish full-envelope ownership and remain successor-compatible while preserving its historical executable regression.

Author by Lab | zefry

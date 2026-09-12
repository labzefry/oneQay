# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint135
**Canonical engineering commit:** `c13ea1e468154fc1997a48ad5ce7c0132ddbc1c9`
**Latest engineering PR:** #686 — `Sprint135: canonical control-plane positive registration metadata regression`
**Status date:** 2026-09-12

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint135**. The latest engineering chain focuses on Final Shift Close runtime control-plane qualification while deliberately separating source readiness from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint135 |
| Latest engineering commit | `c13ea1e468154fc1997a48ad5ce7c0132ddbc1c9` |
| Latest engineering PR | #686, merged |
| Sprint135 exact-head CI | 19/19 pull-request workflow runs successful |
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

Representative material milestones include:

- Sprint55 — expected-cash derivation entry gate;
- Sprint64 — cash-variance source foundation;
- Sprint70 — durable cash-variance explanation foundation;
- Sprint80 — scoped cash-variance reviewer authorization policy and implementation chain.

This chain established expected-cash, immutable sale-to-shift binding, cash-variance evidence, explanation/adjudication, maker-checker, and reviewer authorization foundations. These are source/evidence achievements, not production activation.

### Final Shift Close engineering chain

- Sprint88 — migration #27 source materialization only; migration remained `NOT_EXECUTED`.
- Sprint89 — Final Shift Close application-readiness contract and fail-closed prerequisites.
- Later bounded sprints progressively qualified application/service, provider, runtime-binding manifest, DB-binding attestation, authorization, target-readiness, and historical compatibility boundaries.
- Sprint130 — canonical runtime control-plane token policy.
- Sprint131 — CI-only synthetic middleware positive-path regression.
- Sprint132 — CI-only synthetic direct-controller positive-path regression.
- Sprint133 — direct-controller fail-closed regression.
- Sprint134 — cross-provider delivery-gate route-absence regression for a canonical-length token containing a disallowed character.
- Sprint135 — cross-provider positive registration metadata regression using one canonical valid synthetic token, proving exact route/controller/method/throttle/token-middleware metadata while registration remains inert and production side-effect services remain unresolved.

## 3. Sprint135 closure evidence

Sprint135 is closed through PR #686.

- Canonical squash commit: `c13ea1e468154fc1997a48ad5ce7c0132ddbc1c9`
- Parent canonical checkpoint: `3bc22b9f31b9855ea15b750cd67af1d13537a93c`
- Exact Sprint135 engineering envelope: four paths
- Frozen Sprint135 envelope SHA-256: `628d99da2ce7f65938dc12df387c829748e36c4a8d8cab18a08eb5402fcf78fc`
- Exact-head pull-request workflows: 19/19 successful
- Product Owner merge authority workflow run `34683875845`: successful
- Merge method: squash with exact-head guard

Detailed evidence:

- `docs/SPRINT135_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_POSITIVE_REGISTRATION_METADATA_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_POSITIVE_REGISTRATION_METADATA_REGRESSION_CONTRACT.json`
- `apps/web/tests/final-shift-close-runtime-control-plane-positive-registration-metadata-regression.php`

## 4. Operational truth — NO-GO remains authoritative

Sprint135 does not grant operational authority. Current machine-readable state remains:

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
- Technical Preview activation;
- Production activation;
- deployment/release publication;
- updater activation.

## 6. Next engineering position

The next engineering activity is **Sprint136 bounded discovery from canonical post-Sprint135**.

Sprint136 is not considered started or complete merely because it is named here. Bounded discovery must first identify the smallest non-duplicative engineering gap, preserve Sprint130–Sprint135 executable ownership, remain fail-closed and deny-by-default, and avoid converting source readiness into operational authority.

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

Every material closed sprint must reconcile this manifest and the four root summary documents in the closure/handoff, or explicitly record why no current-state update is required. Current project status must never be inferred from a stale historical sprint heading.

Author by Lab | zefry

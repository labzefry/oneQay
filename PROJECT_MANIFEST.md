# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint141
**Canonical engineering commit:** `81d4d19c61e99746495bec804c0e7d8a9778a257`
**Latest engineering PR:** #699 — `Sprint141: canonical HTTP auth rejection response regression`
**Status date:** 2026-09-12

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint141**. The latest engineering chain focuses on Final Shift Close runtime control-plane qualification while deliberately separating source readiness from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint141 |
| Latest engineering commit | `81d4d19c61e99746495bec804c0e7d8a9778a257` |
| Latest engineering PR | #699, merged |
| Sprint141 exact-head CI | 25/25 pull-request workflow runs successful |
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
- Sprint136 — canonical-valid-token authenticated HTTP positive-path regression through real route, token middleware, and real controller with synthetic side-effect services.
- Sprint137 — canonical-valid-token authenticated HTTP fail-closed regression with exact 503 translation and non-disclosure.
- Sprint138 — canonical-valid authenticated HTTP throttle regression proving materialization `1/min` and DB-attestation `2/min` enforcement.
- Sprint139 — authentication-before-throttle hardening, proving wrong-bearer traffic cannot consume authenticated request budget.
- Sprint140 — direct-middleware authentication-rejection response hardening, preserving owned status semantics while making auth rejections empty-body, private/no-store, no-cache, nosniff, robot-excluded, and non-reflective.
- Sprint141 — registered-route HTTP-kernel propagation regression proving Sprint140 hardened rejection responses survive the composed HTTP path for missing, malformed, and mismatched bearer credentials while controllers and side-effect application services remain unresolved.

## 3. Sprint141 closure evidence

Sprint141 is closed through engineering PR #699.

- Canonical engineering squash commit: `81d4d19c61e99746495bec804c0e7d8a9778a257`
- Parent canonical documentation checkpoint: `c49c5438180a89a393201c688482977fd9c9e3f1`
- Exact Sprint141 engineering envelope: four paths
- Frozen Sprint141 envelope SHA-256: `b75d55835722110dee38759068ce3f527efc0ecdbd2201f4fde9b4372211641a`
- Final exact-head engineering SHA before merge: `8dc34649d15ca65e6b198221d0354bee8e75ecb1`
- Exact-head pull-request workflows: 25/25 successful
- Product Owner merge authority workflow run `34709040922`: successful
- Merge method: squash with exact-head guard

Sprint141 is regression-only: it does not change production middleware, providers, routes, controllers, token policy, throttle limits, application services, infrastructure adapters, or operational state.

With canonical-valid synthetic expected tokens enabling both registered control-plane routes, Sprint141 dispatches real in-process requests through the Laravel HTTP kernel. Missing, malformed, and structurally canonical-valid mismatched bearer credentials preserve materialization HTTP `401` and DB-attestation cloaked HTTP `404`. Every rejection remains empty-body with `Cache-Control` containing `no-store` and `private`, `Pragma: no-cache`, `X-Content-Type-Options: nosniff`, and `X-Robots-Tag: noindex, nofollow, noarchive`.

Expected-token, mismatched-token, and synthetic internal-path values are not reflected. The manifest writer/materializer, database identity reader/attestation service, and both controllers remain unresolved; the canonical runtime-binding manifest remains absent. Sprint139 authentication-before-throttle ownership and Sprint140 direct-middleware response-hardening ownership remain preserved.

Detailed evidence:

- `docs/SPRINT141_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_HTTP_AUTH_REJECTION_RESPONSE_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_HTTP_AUTH_REJECTION_RESPONSE_REGRESSION_CONTRACT.json`
- `apps/web/tests/final-shift-close-runtime-control-plane-http-auth-rejection-response-regression.php`

## 4. Operational truth — NO-GO remains authoritative

Sprint141 does not grant operational authority. Current machine-readable state remains:

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

The next engineering activity is **Sprint142 bounded discovery from canonical post-Sprint141**.

Sprint142 is not considered started or complete merely because it is named here. Bounded discovery must identify the smallest non-duplicative engineering gap, preserve Sprint130–Sprint141 executable ownership, remain fail-closed and deny-by-default, and avoid converting source readiness into operational authority.

No Sprint142 implementation or source envelope is preselected by this manifest.

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

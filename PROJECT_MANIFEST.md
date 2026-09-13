# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint142
**Canonical engineering commit:** `61a6d68b45303a796c5eb7c2afa78b16e740da53`
**Latest engineering PR:** #701 — `Sprint142: canonical control-plane throttle rejection response hardening`
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint142**. The latest engineering chain focuses on Final Shift Close runtime control-plane qualification while deliberately separating source readiness from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint142 |
| Latest engineering commit | `61a6d68b45303a796c5eb7c2afa78b16e740da53` |
| Latest engineering PR | #701, merged |
| Sprint142 exact-head CI | 26/26 pull-request workflow runs successful |
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
- Sprint142 — authenticated throttle-rejection response hardening, preserving HTTP `429`, auth-before-throttle ordering, exact `1,1` / `2,1` limits, and Laravel rate-limit metadata while making the two owned control-plane throttle responses empty-body, private/no-store, no-cache, nosniff, robot-excluded, and non-reflective.

## 3. Sprint142 closure evidence

Sprint142 is closed through engineering PR #701.

- Canonical engineering squash commit: `61a6d68b45303a796c5eb7c2afa78b16e740da53`
- Parent canonical documentation checkpoint: `669661c70a376f4adc6c9f805af38c60f5672382`
- Exact Sprint142 engineering envelope: six paths
- Frozen Sprint142 envelope SHA-256: `51245b1a9610a4a1989f52bfd76b80d57683e4c7069f6f7eafecee77d6147023`
- Final exact-head engineering SHA before merge: `c4154f33d21d3e6005a339662ce4c4f5642b8ed0`
- Exact-head pull-request workflows: 26/26 successful
- Product Owner merge authority workflow run `34744364161`: successful
- Merge method: squash with exact-head guard

Sprint142 introduces a narrowly scoped global response post-processor for throttle-generated HTTP `429` responses. It is registered by the already-canonical runtime-binding materialization provider, but does not rewrite either control-plane route middleware stack. Materialization remains auth then `throttle:1,1`; DB-attestation remains auth then `throttle:2,1`.

The hardener is inert unless the request is exactly POST `/internal/final-shift-close/runtime-binding-manifest/materialize` or GET `/internal/final-shift-close/runtime-db-binding-attestation`, the response is HTTP `429`, and Laravel throttle metadata includes `Retry-After` and `X-RateLimit-Limit`. Qualifying responses retain HTTP `429` plus `Retry-After`, `X-RateLimit-Limit`, `X-RateLimit-Remaining`, and `X-RateLimit-Reset`, while the body is emptied and `Cache-Control: no-store, private`, `Pragma: no-cache`, `X-Content-Type-Options: nosniff`, and `X-Robots-Tag: noindex, nofollow, noarchive` are enforced.

The Sprint142 executable regression uses real registered routes, canonical token middleware, the Laravel HTTP kernel, and real throttle middleware with synthetic side-effect services. Materialization performs exactly one synthetic write before its second same-IP request is hardened HTTP `429`; DB-attestation performs exactly two synthetic reads before its third same-IP request is hardened HTTP `429`. An unrelated synthetic throttled route remains outside Sprint142 hardening. Production filesystem/database side-effect bindings remain guarded, and the canonical runtime-binding manifest remains untouched.

Sprint138 throttle enforcement, Sprint139 auth-before-throttle ownership, Sprint140 direct auth-rejection response hardening, and Sprint141 HTTP-kernel auth-rejection propagation remain preserved.

Detailed evidence:

- `docs/SPRINT142_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_THROTTLE_REJECTION_RESPONSE_HARDENING_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_THROTTLE_REJECTION_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`
- `apps/web/tests/final-shift-close-runtime-control-plane-throttle-rejection-response-hardening-regression.php`

## 4. Operational truth — NO-GO remains authoritative

Sprint142 does not grant operational authority. Current machine-readable state remains:

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

The next engineering activity is **Sprint143 bounded discovery from canonical post-Sprint142**.

Sprint143 is not considered started or complete merely because it is named here. Bounded discovery must identify the smallest non-duplicative engineering gap, preserve Sprint130–Sprint142 executable ownership, remain fail-closed and deny-by-default, and avoid converting source readiness into operational authority.

No Sprint143 implementation or source envelope is preselected by this manifest.

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

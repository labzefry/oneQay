# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint140
**Canonical engineering commit:** `446f9ff80f646d2e77d0885b887da58a37994d28`
**Latest engineering PR:** #697 — `Sprint140: canonical control-plane auth rejection response hardening regression`
**Status date:** 2026-09-12

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint140**. The latest engineering chain focuses on Final Shift Close runtime control-plane qualification while deliberately separating source readiness from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint140 |
| Latest engineering commit | `446f9ff80f646d2e77d0885b887da58a37994d28` |
| Latest engineering PR | #697, merged |
| Sprint140 exact-head CI | 25/25 pull-request workflow runs successful |
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
- Sprint139 — authentication-before-throttle hardening, proving unauthenticated/mismatched bearer traffic cannot consume authenticated request budget.
- Sprint140 — cross-provider authentication-rejection response hardening, preserving existing HTTP dispositions while making every auth rejection empty-body, non-cacheable/private, non-sniffable, robot-excluded, and non-reflective.

## 3. Sprint140 closure evidence

Sprint140 is closed through engineering PR #697.

- Canonical engineering squash commit: `446f9ff80f646d2e77d0885b887da58a37994d28`
- Parent canonical documentation checkpoint: `0d08996238d7aec0db3476d0dedf6d69ef3f0669`
- Exact Sprint140 engineering envelope: nine paths
- Frozen Sprint140 envelope SHA-256: `58f0fe3105a12fc3c6cac98e736743349237a5221a63376f668358c5c09fe1b3`
- Final exact-head engineering SHA before merge: `3ef7024368d8b68c98974535d6aa44f925e4ced5`
- Exact-head pull-request workflows: 25/25 successful
- Product Owner merge authority workflow run `34707917274`: successful
- Merge method: squash with exact-head guard

Sprint140 hardened both runtime control-plane authentication middleware rejection surfaces without changing canonical token policy, route registration, middleware ordering, throttle limits, controllers, or application-service behavior.

Materialization preserves HTTP `503` for invalid expected-token configuration and HTTP `401` for missing, malformed, or mismatched bearer credentials. DB-binding attestation preserves cloaked HTTP `404` for the same invalid credential classes. All rejection responses are empty-body and expose `Cache-Control: no-store, private`, `Pragma: no-cache`, `X-Content-Type-Options: nosniff`, and `X-Robots-Tag: noindex, nofollow, noarchive`; bearer fixtures and internal paths are not reflected.

The Sprint140 executable regression invokes the middleware directly in a synthetic test container, verifies matching credentials still reach a synthetic HTTP `204` continuation, and verifies production manifest-writer, database-identity-reader, and both controllers remain unresolved. No real route invocation, filesystem write, database connection, operational token, or runtime target was used.

Initial exact-head qualification also identified historical Sprint126, Sprint130, and Sprint131 workflow assertions that locked the old literal `abort(503)` / `abort(401)` representation. Those workflow assertions were updated within the Sprint140 bounded envelope to recognize the hardened response representation while retaining their executable token-policy, route-registration, positive-path, and fail-closed tests.

Detailed evidence:

- `docs/SPRINT140_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_AUTH_REJECTION_RESPONSE_HARDENING_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_AUTH_REJECTION_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`
- `apps/web/tests/final-shift-close-runtime-control-plane-auth-rejection-response-hardening-regression.php`

## 4. Operational truth — NO-GO remains authoritative

Sprint140 does not grant operational authority. Current machine-readable state remains:

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

The next engineering activity is **Sprint141 bounded discovery from canonical post-Sprint140**.

Sprint141 is not considered started or complete merely because it is named here. Bounded discovery must identify the smallest non-duplicative engineering gap, preserve Sprint130–Sprint140 executable ownership, remain fail-closed and deny-by-default, and avoid converting source readiness into operational authority.

No Sprint141 implementation or source envelope is preselected by this manifest.

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

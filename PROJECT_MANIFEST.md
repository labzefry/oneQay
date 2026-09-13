# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint144
**Canonical engineering commit:** `98840c29c21bcf6b1d81cb2afe21d07eb720e120`
**Latest engineering PR:** #705 — `Sprint144: canonical named-route identity throttle response hardening`
**Status date:** 2026-09-13

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint144**. The latest engineering chain focuses on Final Shift Close runtime control-plane qualification while deliberately separating source readiness from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint144 |
| Latest engineering commit | `98840c29c21bcf6b1d81cb2afe21d07eb720e120` |
| Latest engineering PR | #705, merged |
| Sprint144 exact-head CI | 28/28 pull-request workflow runs successful |
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
- Sprint130 — canonical runtime control-plane token policy.
- Sprint131 — CI-only synthetic middleware positive-path regression.
- Sprint132 — CI-only synthetic direct-controller positive-path regression.
- Sprint133 — direct-controller fail-closed regression.
- Sprint134 — cross-provider delivery-gate route-absence regression.
- Sprint135 — canonical-valid-token cross-provider route-registration metadata and inertness regression, including DB-attestation `GET,HEAD` method ownership.
- Sprint136 — authenticated HTTP positive path through real route, token middleware, and controller with synthetic side-effect services.
- Sprint137 — authenticated HTTP fail-closed translation and non-disclosure regression.
- Sprint138 — authenticated HTTP throttle regression proving materialization `1/min` and DB-attestation `2/min` enforcement.
- Sprint139 — authentication-before-throttle hardening so wrong-bearer traffic cannot consume authenticated request budget.
- Sprint140 — authentication-rejection response hardening with empty-body privacy/security metadata.
- Sprint141 — registered-route HTTP-kernel propagation of Sprint140 hardened authentication rejection responses.
- Sprint142 — authenticated throttle-rejection response hardening for materialization POST and DB-attestation GET, preserving HTTP `429` and Laravel rate-limit metadata.
- Sprint143 — DB-attestation HEAD throttle-rejection parity hardening.
- Sprint144 — throttle-response hardening ownership now requires canonical route name plus exact method plus exact path; same-path/method noncanonical routes remain framework-owned.

## 3. Sprint144 closure evidence

Sprint144 is closed through engineering PR #705.

- Canonical engineering squash commit: `98840c29c21bcf6b1d81cb2afe21d07eb720e120`
- Parent canonical documentation checkpoint: `7356081623524acb8218d6933a7104eecea36009`
- Exact Sprint144 engineering envelope: five paths
- Frozen Sprint144 envelope SHA-256: `a057418c27d32d2a3c0bb88bf694fb7389304c392d101e04a1d45955805c50ab`
- Final exact-head engineering SHA before merge: `66883b9c838b76fadaa5cd0c38c84a51a5176b7f`
- Exact-head pull-request workflows: 28/28 successful
- Product Owner merge authority workflow run `34748118905`: successful
- Merge method: squash with exact-head guard

Sprint144 hardens the global Final Shift Close throttle-response rewriter so response ownership requires the canonical route name in addition to the exact HTTP method and exact internal path. Canonical materialization POST and DB-attestation GET/HEAD retain the established hardened HTTP `429` behavior. Same-path/method noncanonical routes and unresolved route identity remain framework-owned and are not rewritten by the Sprint144 hardener.

Route registration, auth-before-throttle ordering, token policy, controllers, application services, exact throttle budgets, migration state, runtime target state, and all operational gates remain unchanged. Sprint143 and earlier executable ownership remain preserved.

Detailed evidence:

- `docs/SPRINT144_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_NAMED_ROUTE_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_NAMED_ROUTE_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`
- `apps/web/tests/final-shift-close-runtime-control-plane-named-route-identity-throttle-response-hardening-regression.php`

## 4. Operational truth — NO-GO remains authoritative

Sprint144 does not grant operational authority. Current machine-readable state remains:

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

The next engineering activity is **Sprint145 bounded discovery from canonical post-Sprint144**.

Sprint145 is not considered started or complete merely because it is named here. Bounded discovery must identify the smallest non-duplicative engineering gap, preserve Sprint130–Sprint144 executable ownership, remain fail-closed and deny-by-default, and avoid converting source readiness into operational authority.

No Sprint145 implementation or source envelope is preselected by this manifest.

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

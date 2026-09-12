# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Canonical engineering checkpoint:** Sprint139
**Canonical engineering commit:** `4028e05485589be649eb437c800b70c2990decf2`
**Latest engineering PR:** #694 — `Sprint139: canonical control-plane auth-before-throttle regression`
**Status date:** 2026-09-12

> This file is the canonical human-readable source of truth for current oneQay project status. README, CHANGELOG, TASKS, and ROADMAP summarize this manifest. Per-sprint documents, contracts, workflows, merged pull requests, and Git history remain the detailed evidence trail.

## 1. Current project state

oneQay is an actively engineered enterprise business-management platform using a Modular Monolith First architecture with Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, and fail-closed engineering controls.

The repository has progressed through **Sprint139**. The latest engineering chain focuses on Final Shift Close runtime control-plane qualification while deliberately separating source readiness from operational activation.

### Canonical state summary

| Area | Current canonical state |
| --- | --- |
| Latest closed engineering sprint | Sprint139 |
| Latest engineering commit | `4028e05485589be649eb437c800b70c2990decf2` |
| Latest engineering PR | #694, merged |
| Sprint139 exact-head CI | 24/24 pull-request workflow runs successful |
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
- Sprint137 — canonical-valid-token authenticated HTTP fail-closed regression proving materialization and DB-attestation failures remain exact HTTP 503 contracts through the composed HTTP path while production filesystem/database adapters remain unresolved and sensitive internals remain undisclosed.
- Sprint138 — canonical-valid authenticated HTTP throttle regression proving materialization `1/min` and DB-attestation `2/min` enforcement through the real HTTP throttle middleware, with excess authenticated requests rejected as HTTP 429 before repeated synthetic side effects.
- Sprint139 — authentication-before-throttle hardening for both control planes, proving a wrong structurally valid bearer from the same limiter key cannot consume authenticated request budget before the canonical token middleware rejects it.

## 3. Sprint139 closure evidence

Sprint139 is closed through engineering PR #694.

- Canonical engineering squash commit: `4028e05485589be649eb437c800b70c2990decf2`
- Parent canonical documentation checkpoint: `0cbf88f00d55c82bd94ef54532f81ccebe6ddcb1`
- Exact Sprint139 engineering envelope: six paths
- Frozen Sprint139 envelope SHA-256: `be9546afdf82465da5f9d160845edc4cf6599f306445f941226e12288a7b2286`
- Final exact-head engineering SHA before merge: `b55892dcd8ae7dd5c83cea88e6f70496b4669d0f`
- Exact-head pull-request workflows: 24/24 successful
- Product Owner merge authority workflow run `34705209701`: successful
- Merge method: squash with exact-head guard

Sprint139 corrected only middleware ordering in the two runtime control-plane service providers. Materialization now applies `RequireFinalShiftCloseRuntimeBindingManifestMaterializationTokenMiddleware` before `throttle:1,1`; DB-binding attestation applies `RequireFinalShiftCloseRuntimeBindingTokenMiddleware` before `throttle:2,1`. Route URIs, controller targets, token policy, throttle limits, delivery gates, application services, and infrastructure bindings were otherwise unchanged.

The Sprint139 test used canonical-valid expected and wrong synthetic bearer fixtures from the same source IP per control plane. The wrong materialization bearer returned HTTP `401` without consuming the single authenticated limiter slot, so the first subsequent valid request still returned HTTP `200`; the next valid request returned `429` and the in-memory writer remained at one invocation. The wrong DB-attestation bearer retained the canonical cloaked HTTP `404` without consuming either authenticated limiter slot; two subsequent valid requests returned `200`, the third returned `429`, and the synthetic identity reader remained at two invocations. Production filesystem/database adapters remained unresolved and no operational bearer or runtime target was used.

Detailed evidence:

- `docs/SPRINT139_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_AUTH_BEFORE_THROTTLE_REGRESSION.md`
- `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_AUTH_BEFORE_THROTTLE_REGRESSION_CONTRACT.json`
- `apps/web/tests/final-shift-close-runtime-control-plane-auth-before-throttle-regression.php`

## 4. Operational truth — NO-GO remains authoritative

Sprint139 does not grant operational authority. Current machine-readable state remains:

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

The next engineering activity is **Sprint140 bounded discovery from canonical post-Sprint139**.

Sprint140 is not considered started or complete merely because it is named here. Bounded discovery must identify the smallest non-duplicative engineering gap, preserve Sprint130–Sprint139 executable ownership, remain fail-closed and deny-by-default, and avoid converting source readiness into operational authority.

No Sprint140 implementation or source envelope is preselected by this manifest.

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

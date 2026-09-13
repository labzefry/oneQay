# Sprint146 — Final Shift Close Canonical Control Plane Throttle Budget Identity Response Hardening Regression

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Canonical base:** `ebaf16c64245c67e9ecf8cac613696e5661a02ac`
**Objective:** `CANONICAL_CONTROL_PLANE_THROTTLE_BUDGET_IDENTITY_RESPONSE_HARDENING_REGRESSION`

## Purpose / Why

Sprint145 completed request ownership for the Final Shift Close throttle-response hardener using canonical route name, canonical controller action, exact HTTP method, and exact internal path. The response-side ownership check still accepted any present `X-RateLimit-Limit` value.

That left a bounded ambiguity: a canonical request carrying a framework-style `429` with an unexpected, cross-route, arbitrary, or numerically similar ceiling could still receive Final Shift Close privacy/security rewriting even though the response did not prove the canonical throttle budget for that route.

## Objective / Gap

The Sprint146 objective is to bind response hardening to the exact canonical per-route throttle ceiling in addition to the already-qualified request identity.

Canonical throttle budget identity is:

- materialization POST: exact `X-RateLimit-Limit: 1`;
- DB-attestation GET/HEAD: exact `X-RateLimit-Limit: 2`.

Unexpected values remain framework-owned. Numeric aliases such as `01` or `02` are intentionally not treated as canonical equivalents.

## What changed

- Added explicit canonical throttle-limit constants to `HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware`.
- Replaced request-only ownership resolution with `canonicalThrottleLimit(Request): ?string` so route identity and expected budget are resolved together.
- Required the actual `X-RateLimit-Limit` header to exactly equal the route's canonical throttle ceiling before rewriting a `429` response.
- Added a dedicated Sprint146 direct-middleware regression covering materialization POST plus DB-attestation GET and HEAD.
- Preserved route registration, auth-before-throttle ordering, configured `throttle:1,1` / `throttle:2,1` middleware, canonical controller actions, token policy, controller/application behavior, and historical response hardening.

## Evidence / Qualification

Sprint146 executable regression proves:

- materialization canonical identity + `Limit: 2`, `99`, or `01` remains framework-owned;
- DB GET canonical identity + `Limit: 1`, `99`, or `02` remains framework-owned;
- DB HEAD canonical identity + `Limit: 1`, `99`, or `02` remains framework-owned;
- materialization exact `Limit: 1` still receives the established empty-body/privacy-security hardening;
- DB GET/HEAD exact `Limit: 2` still receives the established hardening;
- framework-provided rate-limit metadata and unrelated probe headers remain preserved;
- Sprint145 action-identity regression remains successor-compatible and executable;
- Sprint142 real HTTP-kernel throttle regression remains the historical proof that registered canonical routes actually emit the configured ceilings.

Exact Sprint146 source envelope:

1. `.github/workflows/sprint146-final-shift-close-canonical-control-plane-throttle-budget-identity-response-hardening-regression.yml`
2. `apps/web/app/Delivery/Http/Middleware/HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware.php`
3. `apps/web/tests/final-shift-close-runtime-control-plane-throttle-budget-identity-response-hardening-regression.php`
4. `docs/SPRINT146_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_THROTTLE_BUDGET_IDENTITY_RESPONSE_HARDENING_REGRESSION.md`
5. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_THROTTLE_BUDGET_IDENTITY_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`bbc0da27fe84ca8a1fafcf4b76bcf9e1f42e595c01d35544a94793c1a7fec161`

Closure requires exact-head pull-request CI, repository-native Product Owner merge authority, final race verification, and squash merge with an expected-head guard.

## Operational boundaries / NO-GO

Sprint146 is CI-only source engineering. It does not:

- execute migration #27;
- provision Final Shift Close permissions;
- activate Final Shift Close;
- provision an operational runtime token;
- select, invoke, or activate a durable runtime target;
- materialize the canonical runtime-binding manifest operationally;
- establish a real operational database connection or perform operational DB attestation;
- deploy or publish a release;
- activate Technical Preview;
- activate Production;
- activate the updater.

Machine-readable lifecycle state remains authoritative.

## Next position

After Sprint146 closes, the next position is **Sprint147 bounded discovery from canonical post-Sprint146**. No Sprint147 objective or source envelope is preselected here.

Author by Lab | zefry

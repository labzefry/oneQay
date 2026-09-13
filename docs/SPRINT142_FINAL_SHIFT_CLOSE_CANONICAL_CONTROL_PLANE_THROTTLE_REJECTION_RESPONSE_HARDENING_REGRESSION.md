# Sprint142 — Final Shift Close Canonical Control Plane Throttle Rejection Response Hardening Regression

Author by Lab | zefry

## Objective

Sprint142 closes the smallest non-duplicative response-hardening gap remaining after Sprint138–Sprint141: authenticated requests that correctly reach the canonical Laravel throttle middleware and exceed the preserved limits still return HTTP `429`, but the historical chain did not own an empty-body/privacy-hardening contract for that throttle rejection surface.

Sprint142 does not change the canonical token policy, route registration, controller/application behavior, authentication-before-throttle ordering, or the existing materialization `1/min` and DB-attestation `2/min` limits.

## Bounded implementation

A single global response post-processor is registered from the already-canonical materialization service provider. The post-processor is inert unless all of the following are true:

- the request is exactly POST `/internal/final-shift-close/runtime-binding-manifest/materialize` or GET `/internal/final-shift-close/runtime-db-binding-attestation`;
- the response status is HTTP `429`;
- the response contains Laravel throttle metadata `Retry-After` and `X-RateLimit-Limit`.

For that narrow surface, the existing response body is cleared and these headers are set:

- `Cache-Control: no-store, private`
- `Pragma: no-cache`
- `X-Content-Type-Options: nosniff`
- `X-Robots-Tag: noindex, nofollow, noarchive`

Existing Laravel throttle metadata is preserved, including `Retry-After`, `X-RateLimit-Limit`, `X-RateLimit-Remaining`, and `X-RateLimit-Reset`.

## Executable qualification

The Sprint142 regression uses canonical-valid synthetic bearer credentials, the real registered routes, real token middleware, real Laravel HTTP kernel, and real throttle middleware with array-cache isolation. Side effects remain synthetic: an in-memory manifest writer and database identity reader are injected while production bindings are guarded.

The regression proves:

- materialization first authenticated request succeeds and performs exactly one synthetic write;
- materialization second same-IP authenticated request remains HTTP `429`, performs no second write, has an empty body and hardened metadata, and preserves rate-limit metadata;
- DB-attestation first two authenticated requests succeed and perform exactly two synthetic identity reads;
- DB-attestation third same-IP authenticated request remains HTTP `429`, performs no third read, has an empty body and hardened metadata, and preserves rate-limit metadata;
- bearer fixtures and synthetic/canonical internal paths are not reflected in the hardened response surface;
- an unrelated synthetic throttled route remains outside Sprint142 hardening, proving exact scope;
- the materialization and DB provider route middleware stacks remain unchanged, including auth-before-throttle ordering and exact `1,1` / `2,1` limits;
- Sprint138 throttle enforcement, Sprint139 auth-before-throttle, Sprint140 direct auth-rejection hardening, and Sprint141 HTTP-kernel auth-rejection propagation remain preserved.

## Exact source envelope

1. `.github/workflows/sprint142-final-shift-close-canonical-control-plane-throttle-rejection-response-hardening-regression.yml`
2. `apps/web/app/Delivery/Http/Middleware/HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware.php`
3. `apps/web/app/Providers/FinalShiftCloseRuntimeBindingManifestMaterializationServiceProvider.php`
4. `apps/web/tests/final-shift-close-runtime-control-plane-throttle-rejection-response-hardening-regression.php`
5. `docs/SPRINT142_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_THROTTLE_REJECTION_RESPONSE_HARDENING_REGRESSION.md`
6. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_THROTTLE_REJECTION_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256: `51245b1a9610a4a1989f52bfd76b80d57683e4c7069f6f7eafecee77d6147023`.

## Operational boundary

CI-only synthetic qualification. Sprint142 does not execute migration #27, provision permissions, activate Final Shift Close, select/invoke a durable target, provision an operational token, write the canonical runtime manifest, resolve production filesystem/database side-effect adapters for the bounded regression, establish a real database connection, deploy/release, activate Technical Preview, activate Production, or activate the updater.

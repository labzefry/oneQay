# Sprint141 — Final Shift Close Canonical Control Plane HTTP Auth-Rejection Response Regression

Author by Lab | zefry

## Objective

Sprint141 closes the remaining HTTP-composition gap after Sprint139 and Sprint140 by proving that canonical authentication-rejection response hardening survives dispatch through the real registered Final Shift Close control-plane routes and the Laravel HTTP kernel.

Sprint141 is regression-only. It does not change production middleware, providers, routes, controllers, token policy, throttle limits, application services, infrastructure adapters, or operational state.

## Proven gap

Historical ownership already establishes:

- Sprint139: canonical token authentication executes before throttle accounting, so a structurally valid wrong bearer does not consume authenticated request budget.
- Sprint140: direct invocation of both canonical token middlewares produces hardened auth-rejection responses with empty bodies, private/no-store caching metadata, no-cache pragma, nosniff, robot exclusion, and no credential/internal-path reflection.

The missing executable boundary is the composed HTTP path: no prior regression proves those Sprint140 rejection response properties remain intact after the request enters the actual registered route and traverses the Laravel HTTP kernel.

## Sprint141 qualification

With canonical-valid synthetic expected tokens configured so both control-plane routes register, Sprint141 sends real in-process HTTP requests for these rejected credential classes:

### Runtime-binding manifest materialization

- missing Authorization header → HTTP `401`;
- malformed short bearer → HTTP `401`;
- structurally canonical-valid but mismatched bearer → HTTP `401`.

### Runtime DB-binding attestation

- missing Authorization header → cloaked HTTP `404`;
- malformed short bearer → cloaked HTTP `404`;
- structurally canonical-valid but mismatched bearer → cloaked HTTP `404`.

Every rejection must preserve:

- empty response body;
- `Cache-Control` containing `no-store` and `private`;
- `Pragma: no-cache`;
- `X-Content-Type-Options: nosniff`;
- `X-Robots-Tag: noindex, nofollow, noarchive`;
- no expected-token reflection;
- no mismatched-token reflection;
- no synthetic internal-path reflection.

## Inertness boundary

All Sprint141 requests are rejected by authentication before controller/application-service execution. The regression requires:

- materialization controller unresolved;
- DB-attestation controller unresolved;
- runtime-binding manifest writer unresolved;
- runtime-binding manifest materializer unresolved;
- runtime database identity reader unresolved;
- runtime DB-binding attestation service unresolved;
- canonical runtime-binding manifest absent before and after dispatch.

No synthetic side-effect service is required because no request crosses the authentication boundary.

## Relationship to historical ownership

Sprint141 does not replace or broaden historical responsibilities:

- Sprint130 remains canonical token-policy owner.
- Sprint134 remains invalid/unqualified delivery-gate route-absence owner.
- Sprint135 remains canonical-valid route-registration metadata owner.
- Sprint136 remains authenticated HTTP positive-path owner.
- Sprint137 remains authenticated HTTP application-failure translation owner.
- Sprint138 remains authenticated HTTP throttle-enforcement owner.
- Sprint139 remains authentication-before-throttle budget-isolation owner.
- Sprint140 remains direct-middleware auth-rejection response-hardening owner.

Sprint141 owns only HTTP-kernel propagation and inertness of those hardened auth-rejection responses on registered routes.

## Frozen source envelope

Exactly four paths are owned by Sprint141:

1. `.github/workflows/sprint141-final-shift-close-canonical-control-plane-http-auth-rejection-response-regression.yml`
2. `apps/web/tests/final-shift-close-runtime-control-plane-http-auth-rejection-response-regression.php`
3. `docs/SPRINT141_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_HTTP_AUTH_REJECTION_RESPONSE_REGRESSION.md`
4. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_HTTP_AUTH_REJECTION_RESPONSE_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`b75d55835722110dee38759068ce3f527efc0ecdbd2201f4fde9b4372211641a`

## Operational boundary

Sprint141 is CI-only and synthetic. It does not authorize or perform:

- migration #27 execution;
- permission provisioning;
- Final Shift Close feature activation;
- durable-target qualification, selection, or invocation;
- operational runtime-token provisioning;
- canonical runtime-binding manifest write;
- production filesystem adapter resolution;
- production database identity-reader resolution;
- real database connection or identity read;
- deployment or release;
- Technical Preview activation;
- Production activation;
- updater activation.

Operational NO-GO state remains authoritative in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

Author by Lab | zefry

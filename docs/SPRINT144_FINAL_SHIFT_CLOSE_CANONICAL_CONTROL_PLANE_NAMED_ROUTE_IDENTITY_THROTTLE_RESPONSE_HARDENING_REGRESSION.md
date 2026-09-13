# Sprint144 — Final Shift Close Canonical Control Plane Named-Route Identity Throttle Response Hardening Regression

Author by Lab | zefry

## Objective

Close the smallest remaining ownership gap in the Final Shift Close throttle-response hardener by requiring canonical route identity in addition to the already-qualified HTTP method and path constraints.

Sprint143 preserved canonical DB-attestation HEAD parity. Sprint142 already proved that an unrelated throttled route on a different path remains framework-owned. Neither sprint proved that a route using the same canonical method and path but a noncanonical route name remains outside Final Shift Close response ownership.

## Canonical checkpoint

- Base canonical `main`: `7356081623524acb8218d6933a7104eecea36009`
- Previous closed engineering sprint: Sprint143
- Sprint143 engineering commit: `b307d925400e9707c137f75bcfa3823a182fb84f`
- Sprint143 reconciliation commit: `7356081623524acb8218d6933a7104eecea36009`

## Bounded change

`HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware` now requires all three ownership signals before rewriting an HTTP `429` response:

1. exact canonical named route;
2. exact canonical HTTP method;
3. exact canonical path.

Canonical ownership remains:

- materialization: route `internal.final-shift-close.runtime-binding-manifest.materialize`, method `POST`, canonical materialization path;
- DB attestation: route `internal.final-shift-close.runtime-db-binding-attestation`, methods `GET` or `HEAD`, canonical DB-attestation path.

If the request has no resolved Laravel route, or the route name is not canonical, the hardener returns the downstream response unchanged even when method/path and rate-limit headers otherwise match.

## Executable regression

`apps/web/tests/final-shift-close-runtime-control-plane-named-route-identity-throttle-response-hardening-regression.php` directly exercises the middleware with synthetic Laravel `Request` and `Route` objects. It proves:

- same canonical materialization path + `POST` + noncanonical route name remains framework-owned;
- same canonical DB-attestation path + `GET` + noncanonical route name remains framework-owned;
- same canonical DB-attestation path + `HEAD` + noncanonical route name remains framework-owned;
- same canonical DB-attestation path without a resolved route remains framework-owned;
- canonical materialization named route + `POST` is still hardened;
- canonical DB-attestation named route + `GET` is still hardened;
- canonical DB-attestation named route + `HEAD` is still hardened;
- canonical hardening still preserves HTTP `429`, framework rate-limit metadata, and unrelated framework headers while enforcing the existing empty-body privacy/security response.

The regression performs no database read, no filesystem manifest write, no control-plane network request, and no operational runtime action.

## Historical ownership preserved

Sprint144 does not replace earlier regression ownership. The active Sprint144 workflow must continue to execute:

- Sprint143 DB-attestation HEAD throttle-rejection parity;
- Sprint142 canonical authenticated throttle-response hardening;
- Sprint141 registered-route auth-rejection propagation;
- Sprint140 direct auth-rejection response hardening;
- Sprint139 authentication-before-throttle ordering;
- Sprint138 authenticated HTTP throttle enforcement;
- Sprint135 route registration metadata and GET/HEAD ownership.

Provider registration, route declarations, token policy, auth middleware, controllers, application services, throttle limits, and canonical operational state are unchanged.

## Exact source envelope

1. `.github/workflows/sprint144-final-shift-close-canonical-control-plane-named-route-identity-throttle-response-hardening-regression.yml`
2. `apps/web/app/Delivery/Http/Middleware/HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware.php`
3. `apps/web/tests/final-shift-close-runtime-control-plane-named-route-identity-throttle-response-hardening-regression.php`
4. `docs/SPRINT144_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_NAMED_ROUTE_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION.md`
5. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_NAMED_ROUTE_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`a057418c27d32d2a3c0bb88bf694fb7389304c392d101e04a1d45955805c50ab`

## Operational boundary

Sprint144 is source/CI qualification only. The authoritative NO-GO boundaries remain unchanged:

- migration #27: `NOT_EXECUTED` / `NOT_PERFORMED`;
- permission provisioning: `NONE`;
- feature activation: `INACTIVE`;
- durable activation target: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview: `NOT_AUTHORIZED`;
- Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

No deployment, release, runtime-token provisioning, migration execution, permission provisioning, durable-target activation, runtime-manifest materialization, production database access, Technical Preview activation, Production activation, or updater activation is performed or authorized by Sprint144.

Author by Lab | zefry

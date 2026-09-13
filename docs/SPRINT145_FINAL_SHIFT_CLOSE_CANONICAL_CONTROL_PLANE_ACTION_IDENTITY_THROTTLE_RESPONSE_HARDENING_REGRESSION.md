# Sprint145 — Final Shift Close Action Identity Throttle Response Hardening

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Canonical base:** `a3ab64bffae0f1322eb731908b9ca1bf9dddf9b6`  
**Objective:** `CANONICAL_CONTROL_PLANE_ACTION_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION`

## Discovery

Sprint144 requires canonical route name, exact method, and exact path before the Final Shift Close throttle-response hardener rewrites HTTP `429`. Sprint145 discovery found that the hardener still did not verify the resolved route action.

Sprint135 already proves the registered routes target the canonical invokable controllers. Sprint145 closes the remaining response-ownership gap: a route that copies the canonical name, method, and path but uses a closure or other noncanonical action must stay framework-owned.

## Bounded change

The ownership signal becomes:

`route name + canonical action + exact method + exact path`

Canonical controller actions remain the existing materialization and DB-attestation invokable controllers. No route registration, middleware ordering, token policy, controller behavior, application-service behavior, or throttle limit is changed.

## Executable regression

The Sprint145 regression proves that noncanonical actions remain framework-owned for materialization POST and DB-attestation GET/HEAD, while canonical controller actions continue to receive the established empty-body privacy/security hardening for framework `429` responses. Existing rate-limit metadata and unrelated framework headers remain preserved.

The regression directly invokes the middleware and does not dispatch either canonical controller.

## Successor compatibility

The historical Sprint144 route-identity regression originally used generic closure actions for its positive fixtures. Sprint145 updates only those positive fixtures to use the already-canonical controller actions established by Sprint135. Sprint144's owned invariant—canonical route name versus noncanonical route name—remains unchanged.

## Exact source envelope

1. `.github/workflows/sprint145-final-shift-close-canonical-control-plane-action-identity-throttle-response-hardening-regression.yml`
2. `apps/web/app/Delivery/Http/Middleware/HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware.php`
3. `apps/web/tests/final-shift-close-runtime-control-plane-action-identity-throttle-response-hardening-regression.php`
4. `apps/web/tests/final-shift-close-runtime-control-plane-named-route-identity-throttle-response-hardening-regression.php`
5. `docs/SPRINT145_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_ACTION_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION.md`
6. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_ACTION_IDENTITY_THROTTLE_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`ed1a67c7a7b89e26cd4c3ade350132b8eca7c4e2142f76d9b69495ac0ba2fad2`

## Lifecycle boundary

Machine-readable lifecycle state remains authoritative. Sprint145 is CI-only source engineering and does not change any runtime activation state.

Sprint145 closes only after exact-head CI, repository-native Product Owner authority, final race verification, and squash merge with an expected-head guard.

Author by Lab | zefry

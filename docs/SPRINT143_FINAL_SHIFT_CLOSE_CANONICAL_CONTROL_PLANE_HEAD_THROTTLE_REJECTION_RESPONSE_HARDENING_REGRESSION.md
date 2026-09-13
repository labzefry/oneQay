# Sprint143 — Final Shift Close Canonical Control Plane HEAD Throttle Rejection Response Hardening Regression

Author by Lab | zefry

## Objective

Close the smallest non-duplicative gap after Sprint142: the canonical runtime DB-binding attestation route is registered by Laravel as `GET,HEAD`, while the Sprint142 throttle-response hardener only owned `GET` for that path. Sprint143 extends only the hardener's DB-attestation method ownership so authenticated over-budget `HEAD` requests receive the same fail-closed privacy/security response contract as `GET`.

## Canonical predecessor evidence

- Sprint135 already proves the DB-attestation route exposes exactly `GET,HEAD`.
- Sprint138 proves the DB-attestation throttle remains exactly `2,1`.
- Sprint139 proves canonical token authentication remains before throttle.
- Sprint140 and Sprint141 own authentication-rejection response hardening and composed HTTP propagation.
- Sprint142 owns authenticated throttle-rejection response hardening for materialization POST and DB-attestation GET.

Sprint143 does not redefine those contracts. It closes only the previously unowned DB-attestation `HEAD` throttle-rejection surface.

## Bounded change

Production semantic change is limited to:

`apps/web/app/Delivery/Http/Middleware/HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware.php`

The DB-attestation owned request predicate changes from GET-only to GET-or-HEAD. Materialization remains POST-only. The hardening preconditions remain unchanged: HTTP `429`, `Retry-After`, and `X-RateLimit-Limit` must all be present before any response mutation occurs.

No provider, route, token policy, controller, application service, throttle limit, middleware ordering, configuration key, database binding, runtime manifest writer, migration, deployment, or activation state is changed.

## Executable qualification

The Sprint143 regression:

1. enables only the canonical DB-attestation control plane with a canonical-valid synthetic bearer;
2. boots the real Laravel HTTP kernel and confirms the named DB-attestation route still exposes `GET,HEAD`;
3. supplies a private temporary synthetic runtime-binding manifest and synthetic database identity reader;
4. guards the production database identity reader binding so accidental resolution fails the regression;
5. sends three authenticated `HEAD` requests from the same synthetic IP;
6. proves the first two requests return HTTP `200` and perform exactly two synthetic database identity reads;
7. proves the third request is throttled before any third read and returns HTTP `429` with empty body;
8. proves `Cache-Control` contains `no-store` and `private`, `Pragma` is `no-cache`, `X-Content-Type-Options` is `nosniff`, and `X-Robots-Tag` is `noindex, nofollow, noarchive`;
9. proves `Retry-After`, `X-RateLimit-Limit`, `X-RateLimit-Remaining`, and `X-RateLimit-Reset` remain present and valid;
10. proves the canonical runtime-binding manifest path remains untouched.

The workflow also executes Sprint142's GET/POST throttle-response hardening regression plus Sprint141/Sprint140/Sprint139/Sprint138 historical regressions.

## Exact source envelope

1. `.github/workflows/sprint143-final-shift-close-canonical-control-plane-head-throttle-rejection-response-hardening-regression.yml`
2. `apps/web/app/Delivery/Http/Middleware/HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware.php`
3. `apps/web/tests/final-shift-close-runtime-control-plane-head-throttle-rejection-response-hardening-regression.php`
4. `docs/SPRINT143_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_HEAD_THROTTLE_REJECTION_RESPONSE_HARDENING_REGRESSION.md`
5. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_HEAD_THROTTLE_REJECTION_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`30df6d628ffa67335dd51275137c022cd461264a137b28b5d9fa623a1d1298ed`

## Operational boundary

Sprint143 is CI-only synthetic qualification. It does not execute migration #27, provision permissions, activate Final Shift Close, select or invoke a durable target, provision an operational runtime token, write the canonical runtime-binding manifest, connect to a real database, deploy/release, activate Technical Preview or Production, or activate the updater.

All operational NO-GO state remains authoritative in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

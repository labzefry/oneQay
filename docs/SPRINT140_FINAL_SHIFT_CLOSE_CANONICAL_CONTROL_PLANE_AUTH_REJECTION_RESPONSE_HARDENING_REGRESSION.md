# Sprint140 — Final Shift Close Canonical Control Plane Auth Rejection Response Hardening Regression

**Product:** oneQay — The Future of Intelligent Business Management  
**Owner / attribution:** Lab | zefry  
**Execution mode:** `CI_ONLY_SYNTHETIC_AUTH_REJECTION_RESPONSE_HARDENING_REGRESSION`

## Objective

Sprint140 closes the smallest remaining non-duplicative security gap after Sprint139: authentication rejection responses across the two Final Shift Close runtime control planes did not expose one consistent privacy/security metadata contract.

Sprint139 already owns authentication-before-throttle ordering and authenticated throttle-budget isolation. Sprint140 does not change that ordering, token policy, route registration, throttle limits, controllers, application services, or operational state.

## New executable ownership

Sprint140 owns only the following response-hardening contract for authentication middleware rejections:

- response body is empty;
- `Cache-Control` contains `no-store` and `private`;
- `Pragma` is `no-cache`;
- `X-Content-Type-Options` is `nosniff`;
- `X-Robots-Tag` is `noindex, nofollow, noarchive`;
- bearer fixtures and internal paths are never reflected.

Status semantics remain unchanged:

- materialization invalid expected token: HTTP `503`;
- materialization missing, malformed, or mismatched bearer: HTTP `401`;
- DB-attestation invalid expected token, missing bearer, malformed bearer, or mismatched bearer: cloaked HTTP `404`.

Canonical-valid matching bearer credentials must still pass to the next middleware stage unchanged.

## Source boundary

Exactly six paths are authorized for Sprint140:

1. `.github/workflows/sprint140-final-shift-close-canonical-control-plane-auth-rejection-response-hardening-regression.yml`
2. `apps/web/app/Delivery/Http/Middleware/RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware.php`
3. `apps/web/app/Delivery/Http/Middleware/RequireFinalShiftCloseRuntimeBindingTokenMiddleware.php`
4. `apps/web/tests/final-shift-close-runtime-control-plane-auth-rejection-response-hardening-regression.php`
5. `docs/SPRINT140_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_AUTH_REJECTION_RESPONSE_HARDENING_REGRESSION.md`
6. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_AUTH_REJECTION_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`774f7d28ba9544455f1db7ac74a543482dd5fa352d27942843cb427fe9de7a40`

## Historical ownership preserved

Sprint140 preserves:

- Sprint130 canonical token policy;
- Sprint134 route-absence delivery-gate qualification;
- Sprint135 canonical-valid registration metadata and inertness;
- Sprint136 authenticated HTTP positive path;
- Sprint137 authenticated HTTP fail-closed behavior;
- Sprint138 authenticated HTTP throttle behavior;
- Sprint139 authentication-before-throttle and same-IP budget isolation.

The historical Sprint139 workflow remains successor-compatible and no longer owns a full-PR envelope.

## Operational boundary

Sprint140 is CI-only and synthetic. It does not:

- execute migration #27;
- provision permissions;
- activate Final Shift Close;
- select or invoke a durable runtime target;
- provision an operational runtime token;
- write the canonical runtime-binding manifest;
- resolve or invoke the production filesystem writer;
- establish a real database connection;
- resolve or invoke the production database identity reader;
- grant deployment or release authority;
- activate Technical Preview, Production, or updater flows.

The current operational NO-GO state remains authoritative.

## Success criteria

Sprint140 is complete only after the exact six-path envelope is qualified on an exact PR head, all required workflows are terminal successful, repository-native Product Owner merge authority succeeds for that exact head, final race checks remain clean, the PR is squash merged with an expected-head guard, and post-merge verification confirms the exact bounded delta and unchanged operational NO-GO state.

Author by Lab | zefry

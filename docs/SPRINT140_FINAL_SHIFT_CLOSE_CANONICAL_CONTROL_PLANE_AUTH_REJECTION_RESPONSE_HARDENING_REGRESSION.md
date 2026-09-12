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

Exactly nine paths are authorized for Sprint140:

1. `.github/workflows/sprint126-final-shift-close-runtime-binding-manifest-control-plane-token-hardening.yml`
2. `.github/workflows/sprint130-final-shift-close-canonical-runtime-control-plane-token-policy.yml`
3. `.github/workflows/sprint131-final-shift-close-canonical-control-plane-positive-path-regression.yml`
4. `.github/workflows/sprint140-final-shift-close-canonical-control-plane-auth-rejection-response-hardening-regression.yml`
5. `apps/web/app/Delivery/Http/Middleware/RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware.php`
6. `apps/web/app/Delivery/Http/Middleware/RequireFinalShiftCloseRuntimeBindingTokenMiddleware.php`
7. `apps/web/tests/final-shift-close-runtime-control-plane-auth-rejection-response-hardening-regression.php`
8. `docs/SPRINT140_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_AUTH_REJECTION_RESPONSE_HARDENING_REGRESSION.md`
9. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_AUTH_REJECTION_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`58f0fe3105a12fc3c6cac98e736743349237a5221a63376f668358c5c09fe1b3`

The initial corrected-envelope draft accidentally used the non-existent path segment `RuntimeBindingManifestMaterializationTokenMiddleware.php`. Exact-head CI rejected that fingerprint before any executable step. The envelope is corrected here to the actual canonical source path `RuntimeBindingMaterializationTokenMiddleware.php`; the bounded nine-file set itself is unchanged.

## Historical workflow compatibility correction

Initial exact-head CI correctly exposed three historical workflows whose static source-shape assertions still required the pre-Sprint140 materialization implementation to contain literal `abort(503)` and `abort(401)` calls. Sprint140 intentionally replaced those calls with explicit `rejectionResponse(503)` and `rejectionResponse(401)` responses so privacy/security headers can be attached without changing the owned HTTP status semantics.

The bounded compatibility correction updates only Sprint126, Sprint130, and Sprint131 workflow assertions to recognize the hardened representation. Their executable token-policy, route-registration, positive-path, and fail-closed disposition tests remain preserved. No dummy, commented, or dead `abort()` text is introduced to manufacture a green result.

## Historical ownership preserved

Sprint140 preserves:

- Sprint126 materialization token-hardening semantics and executable registration checks;
- Sprint130 canonical token policy and executable disposition matrix;
- Sprint131 canonical middleware positive-path and fail-closed regressions;
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

Sprint140 is complete only after the exact nine-path envelope is qualified on an exact PR head, all required workflows are terminal successful, repository-native Product Owner merge authority succeeds for that exact head, final race checks remain clean, the PR is squash merged with an expected-head guard, and post-merge verification confirms the exact bounded delta and unchanged operational NO-GO state.

Author by Lab | zefry

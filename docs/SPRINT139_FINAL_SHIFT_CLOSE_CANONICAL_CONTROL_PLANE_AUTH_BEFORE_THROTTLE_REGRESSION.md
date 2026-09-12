# Sprint139 Final Shift Close Canonical Control Plane Auth Before Throttle Regression

Author by Lab | zefry

## Canonical baseline

Sprint139 starts from canonical post-Sprint138 `main`:

`0cbf88f00d55c82bd94ef54532f81ccebe6ddcb1`

Canonical engineering checkpoint remains Sprint138 engineering commit:

`7f562ea48a0255b7e9803f6b268bd00a7e3b3dcf`

Sprint138 is closed. Its authenticated HTTP throttle regression remains historical executable evidence and its workflow is successor-compatible.

## Bounded objective

Sprint139 qualifies and corrects the smallest remaining non-duplicative runtime control-plane security gap:

**bearer-token authentication must execute before throttle accounting so unauthenticated or mismatched-bearer traffic cannot consume the authenticated control-plane request budget.**

The production-source change is deliberately minimal: reorder existing route middleware only. Sprint139 does not change route URIs, methods, controller targets, token policy, throttle limits, delivery-gate configuration, application services, infrastructure adapters, migration state, permissions, feature activation, deployment authority, or operational runtime configuration.

## Why this is not duplicate coverage

Historical ownership already proves:

- Sprint130: canonical runtime control-plane token validity and bearer parsing policy;
- Sprint131: middleware positive path;
- Sprint132: direct-controller positive path;
- Sprint133: direct-controller fail-closed translation;
- Sprint134: route absence when delivery configuration is unqualified;
- Sprint135: canonical-valid cross-provider registration metadata, including both token middleware and throttle strings;
- Sprint136: canonical-valid authenticated HTTP positive path through the real registered route, token middleware, and controller;
- Sprint137: canonical-valid authenticated HTTP fail-closed path;
- Sprint138: authenticated excess requests are throttled at the configured `1,1` and `2,1` limits without repeated synthetic side effects.

Those regressions establish authentication behavior, throttle attachment, and throttle enforcement independently. They do not prove that a wrong bearer is rejected before the rate limiter consumes a request slot.

At the Sprint139 baseline, both providers declare throttle before bearer-token middleware. Therefore a request from the same limiter key can reach throttle accounting before token validation. Sprint139 owns only this ordering and budget-isolation gap.

## Exact security invariant

For each control plane:

1. the canonical token middleware must precede the existing throttle middleware in route middleware order;
2. a structurally canonical-valid but incorrect bearer must be rejected by token middleware;
3. that rejected request must not consume the request budget subsequently available to the valid bearer from the same source IP;
4. allowed valid requests must retain the existing successful controller behavior using test-owned synthetic side-effect services;
5. authenticated excess requests must still receive HTTP 429 at the unchanged canonical throttle limit;
6. production filesystem/database adapters must remain unresolved in the Sprint139 test process.

## Materialization control plane

Canonical route:

`POST /internal/final-shift-close/runtime-binding-manifest/materialize`

Middleware order after Sprint139:

1. `RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware`;
2. `throttle:1,1`.

Executable qualification uses one source IP for both invalid and valid requests:

1. a wrong canonical-valid bearer returns HTTP 401;
2. the in-memory writer remains at zero invocations;
3. the first valid bearer request from the same source IP returns HTTP 200;
4. the writer is invoked exactly once;
5. the second valid bearer request from the same source IP returns HTTP 429;
6. the writer remains at exactly one invocation;
7. the production filesystem writer binding remains unresolved;
8. the canonical runtime manifest is never written.

Under the pre-Sprint139 ordering, the wrong bearer can consume the `1,1` limiter slot and cause the first valid request from the same IP to be throttled. The Sprint139 regression therefore detects the exact historical weakness.

## DB-binding attestation control plane

Canonical route:

`GET /internal/final-shift-close/runtime-db-binding-attestation`

Middleware order after Sprint139:

1. `RequireFinalShiftCloseRuntimeBindingTokenMiddleware`;
2. `throttle:2,1`.

Executable qualification again uses one source IP for invalid and valid requests:

1. a wrong canonical-valid bearer returns the canonical cloaked HTTP 404 response;
2. the synthetic database identity reader remains at zero invocations;
3. the first valid bearer request from the same source IP returns HTTP 200;
4. the second valid bearer request from the same source IP returns HTTP 200;
5. the synthetic reader is invoked exactly twice;
6. the third valid bearer request from the same source IP returns HTTP 429;
7. the synthetic reader remains at exactly two invocations;
8. the production database identity reader remains unresolved;
9. no Laravel `oneqay` connection or real database identity read occurs.

Under the pre-Sprint139 ordering, the wrong bearer can consume one of the `2,1` limiter slots and reduce the authenticated budget. Sprint139 prevents that pre-authentication budget poisoning.

## Security and isolation boundary

Sprint139 may use:

- canonical registered routes;
- canonical token middleware;
- Laravel's canonical HTTP kernel and throttle middleware;
- one canonical-valid expected synthetic bearer token;
- one canonical-valid but mismatched synthetic bearer token;
- one synthetic source IP per control plane shared by its wrong and valid requests;
- synthetic target-selection and runtime-binding manifest fixtures under an isolated temporary directory;
- an in-memory manifest writer;
- an in-memory database identity reader.

Sprint139 must not use:

- an operational bearer token;
- a non-synthetic durable runtime target;
- the production filesystem manifest writer;
- the canonical runtime-binding manifest path for writes;
- a real Laravel `oneqay` database connection;
- a real database identity read;
- migration #27 execution;
- permission provisioning;
- feature activation;
- deployment, release, Technical Preview, Production, or updater activation.

Wrong-bearer and throttle responses must not expose bearer fixtures or synthetic fixture paths.

## Frozen source envelope

Exactly six paths are authorized:

1. `.github/workflows/sprint139-final-shift-close-canonical-control-plane-auth-before-throttle-regression.yml`
2. `apps/web/app/Providers/FinalShiftCloseRuntimeBindingManifestMaterializationServiceProvider.php`
3. `apps/web/app/Providers/FinalShiftCloseRuntimeDbBindingAttestationServiceProvider.php`
4. `apps/web/tests/final-shift-close-runtime-control-plane-auth-before-throttle-regression.php`
5. `docs/SPRINT139_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_AUTH_BEFORE_THROTTLE_REGRESSION.md`
6. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_AUTH_BEFORE_THROTTLE_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`be9546afdf82465da5f9d160845edc4cf6599f306445f941226e12288a7b2286`

The only production-source paths in the envelope are the two service providers, and their authorized semantic delta is middleware ordering only.

## Operational NO-GO

Sprint139 does not change canonical operational state:

- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- Final Shift Close feature: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview: `NOT_AUTHORIZED`;
- Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`;
- durable target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`.

## Closure rule

Sprint139 closes only after:

- exact six-path envelope qualification;
- provider changes are limited to token-before-throttle ordering;
- the new same-IP wrong-bearer budget-isolation regression succeeds in CI;
- Sprint135 registration metadata, Sprint136 positive HTTP, Sprint137 fail-closed HTTP, and Sprint138 authenticated throttle regressions remain green;
- exact-head pull-request CI is terminal successful;
- repository-native Product Owner exact-head authority succeeds;
- final race verification remains clean;
- squash merge uses the exact authorized head;
- post-merge verification confirms one canonical squash commit, exact envelope, attribution, and unchanged operational NO-GO state.

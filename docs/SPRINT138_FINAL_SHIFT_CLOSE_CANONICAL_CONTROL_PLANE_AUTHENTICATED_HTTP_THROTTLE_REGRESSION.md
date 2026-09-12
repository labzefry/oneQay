# Sprint138 Final Shift Close Canonical Control Plane Authenticated HTTP Throttle Regression

Author by Lab | zefry

## Canonical baseline

Sprint138 starts from canonical post-Sprint137 `main`:

`15acffd23cac4c28a395f1d901a54eb94b6ca06e`

Canonical engineering state remains Sprint137 engineering commit:

`62196919fb2c2a172bc0a290159aa26a045d4ed9`

Sprint137 is closed. Its authenticated HTTP fail-closed regression remains historical evidence and its workflow is successor-compatible.

## Bounded objective

Sprint138 qualifies the smallest remaining non-duplicative runtime control-plane gap:

**canonical-valid authenticated HTTP throttle enforcement through the registered control-plane routes without repeated downstream side effects after the configured request budget is exhausted.**

This is a CI-only synthetic regression. It does not change provider source, throttle configuration, token policy, routes, controllers, application services, infrastructure adapters, migration state, permissions, feature activation, deployment authority, or operational runtime configuration.

## Why this is not duplicate coverage

Historical ownership already proves:

- Sprint130: canonical runtime control-plane token validity and bearer policy;
- Sprint131: middleware positive path;
- Sprint132: direct-controller positive path;
- Sprint133: direct-controller fail-closed translation;
- Sprint134: route absence when delivery configuration is unqualified;
- Sprint135: canonical-valid cross-provider route registration metadata, including the configured throttle middleware strings;
- Sprint136: canonical-valid authenticated HTTP positive path through route, token middleware, and real controller;
- Sprint137: canonical-valid authenticated HTTP fail-closed path through route, token middleware, and real controller.

The historical control-plane tests also assert that materialization carries `throttle:1,1` and DB attestation carries `throttle:2,1`.

Those checks prove attachment and metadata. They do not execute requests beyond the configured budget and do not prove that excess authenticated requests are rejected with HTTP 429 before a repeated synthetic side effect occurs.

Sprint138 owns only that missing executable consequence.

## Exact regression responsibility

A single canonical-minimum synthetic bearer token is used for both control planes and is accepted by the Sprint130 token policy.

### Materialization control plane

The test process enables the canonical materialization delivery gate and dispatches requests through the real HTTP kernel to:

`POST /internal/final-shift-close/runtime-binding-manifest/materialize`

Qualification requires:

1. the first authenticated request returns HTTP 200 using a synthetic target-selection fixture and test-owned in-memory writer;
2. the writer is invoked exactly once;
3. a second authenticated request in the same limiter window returns HTTP 429;
4. the writer invocation count remains exactly one after the rejected request;
5. the production filesystem writer binding is never resolved;
6. the canonical runtime manifest is never created.

This proves the configured `throttle:1,1` behavior rather than merely inspecting route metadata.

### DB-binding attestation control plane

The test process enables the canonical DB-attestation delivery gate and dispatches requests through the real HTTP kernel to:

`GET /internal/final-shift-close/runtime-db-binding-attestation`

Qualification requires:

1. the first two authenticated requests return HTTP 200 using a synthetic manifest and test-owned in-memory database identity reader;
2. the synthetic reader is invoked exactly twice;
3. a third authenticated request in the same limiter window returns HTTP 429;
4. the synthetic reader invocation count remains exactly two after the rejected request;
5. the production database identity reader binding is never resolved;
6. no Laravel `oneqay` database connection or real database identity read occurs.

This proves the configured `throttle:2,1` behavior rather than merely inspecting route metadata.

## Security and isolation boundary

Sprint138 may use:

- canonical registered routes;
- canonical token middleware;
- Laravel's canonical HTTP kernel and throttle middleware;
- one canonical-valid synthetic bearer fixture;
- synthetic target-selection and runtime-binding manifest fixtures under an isolated temporary directory;
- an in-memory manifest writer;
- an in-memory database identity reader.

Sprint138 must not use:

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

The HTTP 429 responses must not expose the synthetic bearer credential or synthetic fixture paths.

## Frozen source envelope

Exactly four paths are authorized:

1. `.github/workflows/sprint138-final-shift-close-canonical-control-plane-authenticated-http-throttle-regression.yml`
2. `apps/web/tests/final-shift-close-runtime-control-plane-authenticated-http-throttle-regression.php`
3. `docs/SPRINT138_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_AUTHENTICATED_HTTP_THROTTLE_REGRESSION.md`
4. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_AUTHENTICATED_HTTP_THROTTLE_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`b26aae5dde0a1c39a2cbfbe494ed628ee8eb8c64597404caf17a01def13fe0ab`

No production source path is in the Sprint138 envelope.

## Operational NO-GO

Sprint138 does not change the canonical operational state:

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

Sprint138 closes only after:

- exact four-path envelope qualification;
- the new authenticated HTTP throttle regression succeeds in CI;
- historical Sprint136 positive HTTP and Sprint137 fail-closed HTTP evidence remain green;
- exact-head pull-request CI is terminal successful;
- repository-native Product Owner exact-head authority succeeds;
- final race verification remains clean;
- squash merge uses the exact authorized head;
- post-merge verification confirms one canonical squash commit, the exact envelope, attribution, and unchanged operational NO-GO state.

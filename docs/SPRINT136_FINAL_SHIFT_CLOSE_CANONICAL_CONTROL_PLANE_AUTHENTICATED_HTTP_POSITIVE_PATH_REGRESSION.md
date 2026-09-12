# Sprint136 — Final Shift Close Canonical Control-Plane Authenticated HTTP Positive-Path Regression

Author by Lab | zefry

## Bounded objective

Sprint136 adds CI-only executable proof for the smallest remaining non-duplicative Final Shift Close control-plane gap after Sprint135: a canonical valid synthetic bearer credential must traverse the registered HTTP route, token middleware, invokable controller, and test-owned application service successfully for both runtime-binding manifest materialization and runtime DB-binding attestation, without resolving or invoking production side-effect adapters.

Execution mode:

`CI_ONLY_SYNTHETIC_AUTHENTICATED_HTTP_POSITIVE_PATH_REGRESSION`

## Why this is not duplicate work

The existing chain already owns adjacent invariants:

- Sprint130 owns canonical control-plane token validity and bearer parsing policy;
- Sprint131 owns direct middleware positive-path behavior;
- Sprint132 owns direct controller positive-path behavior with synthetic application services;
- Sprint133 owns direct controller fail-closed translation;
- Sprint134 owns cross-provider route absence when delivery is unqualified;
- Sprint135 owns cross-provider positive route registration metadata and registration inertness;
- older provider-local control-plane regressions own disabled/invalid registration and unauthenticated HTTP rejection.

What remained unqualified was the composed positive path. No existing regression sends a canonical valid authenticated request through the real registered Final Shift Close control-plane route and middleware into the real controller while replacing side-effect application bindings with synthetic test-owned instances.

Sprint136 therefore owns only this composed boundary.

## Synthetic authenticated HTTP boundary

Sprint136 uses one test-owned token exactly at `FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH`. The token is accepted by the canonical policy and is injected only into the test process environment before application bootstrap.

Both control-plane providers are enabled only inside the test process. This does not provision an operational runtime credential or mutate any external runtime configuration.

The test then bootstraps the canonical HTTP kernel and confirms both named routes are present before dispatch.

## Materialization HTTP positive path

The materialization half must:

1. create a private temporary selection fixture outside the canonical runtime paths;
2. construct an in-memory `FinalShiftCloseRuntimeBindingManifestWriter` test double;
3. construct a real `FinalShiftCloseRuntimeBindingManifestMaterializer` against the synthetic selection fixture;
4. replace only the container binding for the materializer with that test-owned instance;
5. replace the production writer interface binding with a bomb binding that records and rejects any accidental container resolution;
6. send an authenticated JSON `POST` through the canonical registered URI;
7. receive HTTP 200 from the real controller through the real route and middleware stack;
8. prove the in-memory writer ran exactly once;
9. prove the production writer bomb binding was never resolved;
10. prove the canonical runtime manifest path was not written.

The request payload retains the canonical two-field controller contract:

- `operation_id`;
- `expected_selection_fingerprint_sha256`.

## DB-binding attestation HTTP positive path

After successful synthetic materialization, Sprint136 writes the captured synthetic manifest only to a private temporary test path.

The DB-attestation half must:

1. construct an in-memory `FinalShiftCloseRuntimeDatabaseIdentityReader` test double;
2. construct a real `FinalShiftCloseRuntimeDbBindingAttestation` against the temporary synthetic manifest;
3. replace only the container binding for the attestation service with that test-owned instance;
4. replace the production database identity reader binding with a bomb binding that records and rejects any accidental container resolution;
5. send an authenticated `GET` through the canonical registered URI;
6. receive HTTP 200 from the real controller through the real route and middleware stack;
7. prove the synthetic identity reader ran exactly once;
8. prove the production identity-reader bomb binding was never resolved;
9. preserve `migration27_state = NOT_EXECUTED` and `attestation_mode = READ_ONLY`;
10. prove the canonical runtime manifest path remains absent.

No Laravel `oneqay` database connection is opened by this regression.

## Production-side-effect isolation

Sprint136 deliberately exercises real HTTP dispatch but not real operational adapters.

The following production-side-effect paths remain forbidden:

- `FilesystemFinalShiftCloseRuntimeBindingManifestWriter` resolution or invocation;
- canonical runtime binding manifest write;
- production `FinalShiftCloseRuntimeDatabaseIdentityReader` resolution;
- `LaravelFinalShiftCloseRuntimeDatabaseIdentityReader` resolution or invocation;
- Laravel `oneqay` database connection;
- operational durable-target selection or mutation;
- operational runtime token provisioning.

The test-owned materializer and attestation service use synthetic temporary fixtures and in-memory collaborators. The HTTP route, middleware, controller resolution and controller invocation are real in-process application behavior; the backing side effects are not operational.

## Canonical response invariants

The materialization HTTP response must retain:

- HTTP 200;
- requested operation ID;
- selected-target fingerprint;
- `MATERIALIZED_SELECTED_TARGET_BINDING_MANIFEST` state;
- `Cache-Control` containing `no-store` and `private`;
- `Pragma: no-cache`;
- `X-Robots-Tag: noindex, nofollow, noarchive`.

The DB-binding attestation HTTP response must retain:

- HTTP 200;
- `VERIFIED_SELECTED_TARGET_DATABASE` binding state;
- `migration27_state = NOT_EXECUTED`;
- `attestation_mode = READ_ONLY`;
- deterministic synthetic database-binding fingerprint;
- `secrets_embedded = false`;
- `Cache-Control` containing `no-store` and `private`;
- `Pragma: no-cache`;
- `X-Content-Type-Options: nosniff`;
- `X-Robots-Tag: noindex, nofollow, noarchive`.

## Historical ownership

Sprint136 does not replace or weaken previous ownership:

- Sprint130 token policy remains authoritative;
- Sprint131 middleware positive-path regression remains authoritative for direct middleware qualification;
- Sprint132 direct-controller positive path remains authoritative;
- Sprint133 controller fail-closed regression remains authoritative;
- Sprint134 delivery-gate route-absence regression remains authoritative;
- Sprint135 route-registration metadata and inertness remain authoritative.

Sprint136 composes these layers only far enough to prove the authenticated synthetic HTTP positive path.

## Exact Sprint136 source envelope

Sprint136 contains exactly four paths:

1. `.github/workflows/sprint136-final-shift-close-canonical-control-plane-authenticated-http-positive-path-regression.yml`
2. `apps/web/tests/final-shift-close-runtime-control-plane-authenticated-http-positive-path-regression.php`
3. `docs/SPRINT136_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_AUTHENTICATED_HTTP_POSITIVE_PATH_REGRESSION.md`
4. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_AUTHENTICATED_HTTP_POSITIVE_PATH_REGRESSION_CONTRACT.json`

Source-envelope SHA-256:

`b459daf7b7f52fd2b028452b821dfa13760f8b050116bdf2fdb5db4d8485fca8`

Hash semantics:

- sort paths lexicographically;
- concatenate one path per line;
- include the final newline;
- compute SHA-256 over the resulting UTF-8 bytes.

## Canonical no-go boundary

`SPRINT136_SYNTHETIC_AUTHENTICATED_HTTP_ROUTE_INVOCATION = PERFORMED_IN_TEST_PROCESS_ONLY`

`SPRINT136_OPERATIONAL_CONTROL_PLANE_REQUEST = NOT_PERFORMED`

`SPRINT136_PRODUCTION_FILESYSTEM_ADAPTER_RESOLUTION = NOT_PERFORMED`

`SPRINT136_CANONICAL_RUNTIME_MANIFEST_WRITE = NOT_PERFORMED`

`SPRINT136_REAL_DATABASE_CONNECTION = NOT_PERFORMED`

`SPRINT136_REAL_DATABASE_IDENTITY_READ = NOT_PERFORMED`

`SPRINT136_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

## Closure condition

Sprint136 is qualified only when the exact four-path envelope and fingerprint match, the canonical minimum-length synthetic token is accepted by Sprint130 policy, both canonical routes are registered, authenticated HTTP dispatch reaches both real controllers through their real middleware, the synthetic materializer and attestation service produce their expected HTTP 200 contracts, production side-effect bomb bindings remain untouched, historical Sprint130–Sprint135 regressions remain successful, required repository checks are terminal SUCCESS on the exact PR HEAD, repository-native exact-head Product Owner merge authority is valid, final race checks remain clean, and post-merge operational state is unchanged.

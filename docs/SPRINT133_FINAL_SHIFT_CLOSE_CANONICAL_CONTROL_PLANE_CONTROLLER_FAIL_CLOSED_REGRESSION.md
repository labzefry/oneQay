# Sprint133 — Final Shift Close Canonical Control-Plane Controller Fail-Closed Regression

Author by Lab | zefry

## Bounded objective

Sprint133 adds CI-only executable proof that the Final Shift Close control-plane controllers preserve their fail-closed HTTP mappings when fully synthetic, isolated request and application-service failures occur after the Sprint132 controller positive-path boundary has already been qualified.

This proof intentionally invokes the controller classes directly. It does not send a request to either real control-plane route, does not use production service-provider bindings, does not write the canonical runtime binding manifest, does not read the real application database connection, and does not perform any operational control-plane request.

## Controller fail-closed qualification

The regression must prove all of the following:

- a materialization request with an invalid JSON field set is rejected directly by the controller with HTTP 422 and the exact `invalid_request` error contract;
- invalid-request rejection occurs before the synthetic manifest writer is invoked;
- a correctly shaped materialization request whose synthetic application-service qualification fails is mapped by the controller to HTTP 503 and the exact `materialization_unavailable` error contract;
- the materialization failure response does not expose exception text, fixture internals, filesystem paths, credentials, tokens, or application-service implementation details;
- materialization error responses retain `Cache-Control: no-store, private`, `Pragma: no-cache`, and `X-Robots-Tag: noindex, nofollow, noarchive` protections;
- the runtime DB binding attestation controller maps a synthetic attestation/application failure to HTTP 503 and the exact `RUNTIME_DB_BINDING_ATTESTATION_UNAVAILABLE` error contract;
- a deterministic synthetic database identity-reader failure is also contained behind the same HTTP 503 response without exposing database identity, exception text, connection details, credentials, or internal implementation data;
- DB attestation error responses retain `Cache-Control: no-store, private`, `Pragma: no-cache`, `X-Content-Type-Options: nosniff`, and `X-Robots-Tag: noindex, nofollow, noarchive` protections;
- temporary synthetic fixtures are created only inside an isolated test-owned temporary directory and are removed after the regression;
- Sprint132 controller positive-path regression and all existing middleware/application fail-closed regressions remain unchanged.

The controller regression may invoke existing application services only through synthetic dependencies and test-owned temporary fixtures. It must not resolve those services through production container bindings.

## Synthetic failure boundary

For manifest materialization, the test may construct `FinalShiftCloseRuntimeBindingManifestMaterializer` directly with:

- a test-owned `FinalShiftCloseRuntimeBindingManifestWriter` implementation that records invocation count and never writes the canonical runtime manifest; and
- an isolated temporary selection fixture whose controlled validation failure is used only to prove controller HTTP 503 containment.

The malformed field-set case must be rejected before application-service execution and must prove that the synthetic writer remains uninvoked.

For DB binding attestation, the test may construct `FinalShiftCloseRuntimeDbBindingAttestation` directly with:

- an isolated temporary manifest fixture used to trigger deterministic application-service rejection; and/or
- a test-owned `FinalShiftCloseRuntimeDatabaseIdentityReader` implementation that throws a deterministic synthetic exception when invoked.

No filesystem runtime writer adapter, Laravel database adapter, provider-resolved runtime service, operational route, real database connection, canonical selection mutation, or external runtime endpoint is eligible for this proof.

## Non-duplication boundary

Sprint133 does not replace or re-run the responsibility of existing middleware/control-plane negative-path regressions as its primary objective. Missing or malformed Bearer credentials remain owned by the existing materialization and DB-attestation control-plane regressions before controller execution.

Sprint133 specifically qualifies error translation and information containment at the controller boundary after direct controller invocation.

## Successor compatibility

Sprint132 becomes historical once Sprint133 exists. Its workflow must stop owning the full successor PR envelope while continuing to validate its owned controller positive-path invariant, synthetic isolation boundary, exact-head checkout, executable regression, security dispositions, and no-go boundary.

Sprint133 alone owns the exact Sprint133 source envelope.

## Exact Sprint133 source envelope

Sprint133 contains exactly five paths:

1. `.github/workflows/sprint132-final-shift-close-canonical-control-plane-controller-positive-path-regression.yml`
2. `.github/workflows/sprint133-final-shift-close-canonical-control-plane-controller-fail-closed-regression.yml`
3. `apps/web/tests/final-shift-close-runtime-control-plane-controller-fail-closed-regression.php`
4. `docs/SPRINT133_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_CONTROLLER_FAIL_CLOSED_REGRESSION.md`
5. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_CONTROLLER_FAIL_CLOSED_REGRESSION_CONTRACT.json`

Source-envelope SHA-256: `2be909fed12150b2f831ab6ae9877e9609859c1a1b81792d165f79771ac31786`.

## Canonical no-go boundary

`SPRINT133_VALID_OPERATIONAL_CONTROL_PLANE_REQUEST = NOT_PERFORMED`

`SPRINT133_REAL_ROUTE_INVOCATION = NOT_PERFORMED`

`SPRINT133_PRODUCTION_CONTAINER_SERVICE_RESOLUTION = NOT_PERFORMED`

`SPRINT133_REAL_FILESYSTEM_ADAPTER_INVOCATION = NOT_PERFORMED`

`SPRINT133_CANONICAL_RUNTIME_MANIFEST_WRITE = NOT_PERFORMED`

`SPRINT133_REAL_DATABASE_CONNECTION = NOT_PERFORMED`

`SPRINT133_REAL_DATABASE_IDENTITY_READ = NOT_PERFORMED`

`SPRINT133_RUNTIME_CONFIGURATION_MUTATION = NOT_PERFORMED`

`SPRINT133_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SPRINT133_CANONICAL_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SPRINT133_OPERATIONAL_RUNTIME_DB_BINDING_ATTESTATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

## Closure condition

Sprint133 is qualified only when the exact five-path envelope and fingerprint match, the materialization controller proves both HTTP 422 invalid-request rejection and HTTP 503 application-failure containment, the DB attestation controller proves HTTP 503 containment for synthetic attestation/identity-reader failures, all error responses retain their security headers without internal-detail leakage, no production route/container/adapter is invoked, Sprint132 positive-path and historical security regressions remain successful, required repository checks are terminal SUCCESS on the exact PR HEAD, exact-head Product Owner merge authority is valid, the final race check is clean, and post-merge operational state remains unchanged.

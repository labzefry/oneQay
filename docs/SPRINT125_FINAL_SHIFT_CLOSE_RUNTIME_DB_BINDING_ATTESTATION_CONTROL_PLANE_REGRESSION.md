# Sprint125 — Final Shift Close Runtime DB Binding Attestation Control Plane Regression

Author by Lab | zefry

## Objective

Sprint125 adds executable application-bootstrap regression coverage for the Sprint124 runtime DB-binding attestation control-plane gate without changing provider, configuration, bootstrap, route, database, manifest, selected-target, migration, permission, deployment, or activation state.

The regression proves that the attestation route is absent unless both the explicit default-off gate is enabled and the dedicated bearer token satisfies the bounded 32–512 character contract.

## Executable Gate Matrix

The application is bootstrapped independently for each synthetic CI-only case:

- disabled gate with otherwise valid token: route absent;
- enabled gate with empty token: route absent;
- enabled gate with 31-character token: route absent;
- enabled gate with 513-character token: route absent;
- enabled gate with valid 32-character token: route registered.

For the valid-registration case the test inspects the route table and proves:

- route name remains `internal.final-shift-close.runtime-db-binding-attestation`;
- endpoint remains GET-only;
- `throttle:2,1` remains attached;
- `RequireFinalShiftCloseRuntimeBindingTokenMiddleware` remains attached;
- missing or malformed bearer credentials return indistinguishable HTTP 404 responses;
- authentication rejection remains `no-store`, `private`, and `nosniff`.

The test never sends a valid bearer credential to the attestation endpoint. Therefore the controller, runtime manifest reader, and active database identity reader are not intentionally invoked by Sprint125.

## Source Preservation Boundary

Sprint125 does not modify:

- `apps/web/app/Providers/FinalShiftCloseRuntimeDbBindingAttestationServiceProvider.php`;
- `apps/web/app/Delivery/Http/Middleware/RequireFinalShiftCloseRuntimeBindingTokenMiddleware.php`;
- `apps/web/bootstrap/app.php`;
- `apps/web/bootstrap/providers.php`;
- `apps/web/config/final_shift_close_runtime_db_binding_attestation.php`;
- `apps/web/config/final_shift_close_runtime_binding_materialization.php`;
- `apps/web/config/oneqay.php`;
- Final Shift Close runtime routes;
- database migrations;
- canonical state or durable target selection.

Sprint120 through Sprint124 remain active as exact successor-compatibility gates and continue to preserve historical manifest writer, materialization, write-once/CAS, and read-only DB-binding attestation boundaries.

## Canonical Boundaries

`SPRINT125_CONTROL_PLANE_REGRESSION = EXECUTABLE_CI_ONLY`

`SPRINT125_RUNTIME_CONFIGURATION_MUTATION = NOT_PERFORMED`

`SPRINT125_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SPRINT125_VALID_BEARER_REQUEST = NOT_PERFORMED`

`SPRINT125_RUNTIME_DB_BINDING_ATTESTATION = NOT_PERFORMED`

`SPRINT125_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

No runtime secret provisioning, environment mutation, valid attestation request, runtime manifest materialization, target selection, DB-binding producer dispatch, migration #27 execution, permission provisioning, deployment, release, feature activation, Technical Preview activation, Production activation, or updater activation is performed by Sprint125.

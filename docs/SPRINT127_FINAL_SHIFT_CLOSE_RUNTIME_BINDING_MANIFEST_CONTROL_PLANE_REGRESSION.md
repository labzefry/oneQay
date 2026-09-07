# Sprint127 — Final Shift Close Runtime Binding Manifest Control Plane Regression

Author by Lab | zefry

## Objective

Sprint127 adds executable Laravel application-bootstrap and HTTP middleware regression coverage for the Sprint126-hardened runtime binding manifest materialization control plane.

This Sprint is CI-only. It does not provision a runtime token, mutate configuration, send a valid bearer credential, invoke the materialization controller, write a runtime binding manifest, select a target, or perform any migration, permission, deployment, or activation action.

## Executable Gate Matrix

Each case runs in an independent PHP process with synthetic CI-only configuration:

- disabled gate with otherwise valid 32-character token: route absent and POST surface returns HTTP 404;
- enabled gate with empty token: route absent and POST surface returns HTTP 404;
- enabled gate with 31-character token: route absent and POST surface returns HTTP 404;
- enabled gate with 513-character token: route absent and POST surface returns HTTP 404;
- enabled gate with valid 32-character token and missing Authorization header: route registered, request rejected with HTTP 401;
- enabled gate with valid 32-character token and malformed `Bearer short` Authorization header: route registered, request rejected with HTTP 401.

Missing-auth and malformed-auth cases run in separate processes because the control plane retains `throttle:1,1`; this prevents rate limiting from obscuring authentication disposition.

## Registered Route Contract

When the gate and configured token are valid, the test proves:

- route name remains `internal.final-shift-close.runtime-binding-manifest.materialize`;
- POST remains present;
- GET remains absent;
- `throttle:1,1` remains attached;
- `RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware` remains attached;
- missing and malformed bearer credentials are rejected before controller execution.

No valid bearer credential is sent by Sprint127.

## Source Preservation

Sprint127 does not modify:

- materialization provider or bearer middleware;
- materialization configuration;
- application or shared bootstrap;
- materialization route, controller, materializer, or writer;
- DB-binding attestation control plane;
- database migrations;
- canonical `STATE.json`;
- durable activation target selection.

Sprint120 through Sprint126 remain active as exact successor-compatibility gates.

## Canonical Boundaries

`SPRINT127_CONTROL_PLANE_REGRESSION = EXECUTABLE_CI_ONLY`

`SPRINT127_TOKEN_POLICY = PRESERVED_BOUNDED_32_512_ASCII`

`SPRINT127_CONTROL_CHANNEL_DELIVERY = INACTIVE`

`SPRINT127_RUNTIME_CONFIGURATION_MUTATION = NOT_PERFORMED`

`SPRINT127_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SPRINT127_VALID_BEARER_REQUEST = NOT_PERFORMED`

`SPRINT127_CONTROLLER_INVOCATION = NOT_PERFORMED`

`SPRINT127_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SPRINT127_RUNTIME_DB_BINDING_ATTESTATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

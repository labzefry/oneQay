# Sprint126 — Final Shift Close Runtime Binding Manifest Control Plane Token Hardening

Author by Lab | zefry

## Objective

Sprint126 hardens the source-only bearer-token boundary of the Sprint123 runtime binding manifest materialization control plane without enabling delivery, provisioning a runtime token, invoking materialization, changing selected-target state, or performing any operational action.

The Sprint123 provider previously treated any non-empty token as sufficient for route registration. Sprint126 narrows that source contract so registration is permitted only when the configured token is a string between 32 and 512 characters. The materialization bearer middleware applies the same bounded length and an explicit ASCII credential character set.

## Security Boundary

The hardened source behavior is:

- delivery remains gated by `ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_ENABLED`;
- the configuration default remains `false`;
- the token configuration default remains empty;
- route registration is denied unless token length is 32–512 characters;
- middleware rejects an invalid expected-token configuration with HTTP 503;
- bearer credentials must match `[A-Za-z0-9._~+/-=]{32,512}`;
- bearer comparison remains constant-time through `hash_equals()`;
- missing, malformed, too-short, too-long, or mismatched bearer credentials remain rejected with HTTP 401;
- the route remains POST-only and throttled at one request per minute;
- no caller-supplied target authority is added;
- the canonical selected-target manifest materialization and immutable writer semantics remain unchanged.

Sprint126 intentionally does not change the existing HTTP status contract because this bounded Sprint is token-quality hardening only.

## Source Preservation

Sprint126 does not modify:

- `apps/web/config/final_shift_close_runtime_binding_materialization.php`;
- `apps/web/bootstrap/app.php`;
- `apps/web/bootstrap/providers.php`;
- runtime materialization route or controller;
- manifest materializer or writer;
- runtime DB-binding attestation provider/middleware/config;
- database migrations;
- canonical `STATE.json`;
- durable activation target selection.

Sprint120 through Sprint125 remain active through exact successor-compatibility gates.

## Canonical Boundaries

`SPRINT126_TOKEN_POLICY = BOUNDED_32_512_ASCII_SOURCE_ONLY`

`SPRINT126_PROVIDER_REGISTRATION = PRESERVED_REGISTERED_SOURCE_ONLY`

`SPRINT126_CONTROL_CHANNEL_DELIVERY = INACTIVE`

`SPRINT126_RUNTIME_CONFIGURATION_MUTATION = NOT_PERFORMED`

`SPRINT126_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SPRINT126_VALID_BEARER_REQUEST = NOT_PERFORMED`

`SPRINT126_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SPRINT126_RUNTIME_DB_BINDING_ATTESTATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

No runtime token provisioning, environment mutation, valid materialization request, manifest materialization, runtime DB-binding attestation, target selection, DB-binding producer dispatch, migration #27 execution, permission provisioning, deployment, release, feature activation, Technical Preview activation, Production activation, or updater activation is performed by Sprint126.

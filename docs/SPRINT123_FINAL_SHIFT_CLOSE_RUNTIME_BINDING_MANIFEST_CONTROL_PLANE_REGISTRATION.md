# Sprint123 — Final Shift Close Runtime Binding Manifest Control Plane Registration

Author by Lab | zefry

## Objective

Sprint123 closes the bounded source-wiring gap left intentionally by Sprint121 and Sprint122.

The runtime binding manifest materialization service provider is now registered with the application, but HTTP delivery remains fail-closed by default. Route registration requires both an explicitly enabled default-off configuration gate and a non-empty dedicated bearer token configuration.

No runtime token is provisioned, no gate is enabled, and no materialization request is executed by this Sprint.

## Security Semantics

Sprint123 preserves the Sprint120 writer boundary, Sprint121 canonical-selected-target-only materialization contract, and Sprint122 write-once/CAS persistence contract.

The source registration boundary is constrained as follows:

- the provider is registered through the existing `Application::configure()->withProviders()` bootstrap mechanism in `bootstrap/app.php`;
- `bootstrap/providers.php` remains unchanged, avoiding unrelated Technical Preview/POS historical compatibility triggers;
- the delivery gate defaults to `false`;
- an empty token prevents route registration even if the enabled flag is true;
- the middleware independently rejects an empty configured token with HTTP 503;
- bearer comparison remains constant-time through `hash_equals()`;
- the materialization route remains throttled to one request per minute;
- no caller target identity fields are added;
- the canonical durable selection and Final Shift Close operational state files are unchanged;
- the runtime DB-binding attestation provider remains unregistered and hard-denied.

## Configuration Source Boundary

The source declares only names and fail-closed defaults in the isolated `config/final_shift_close_runtime_binding_materialization.php` file:

`ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_ENABLED=false`

`ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_TOKEN=`

Sprint123 does not write either value into any environment, secret store, deployment target, or runtime. The global `config/oneqay.php` remains unchanged.

## Successor Compatibility

Sprint120, Sprint121, and Sprint122 qualification workflows remain active as successor-compatibility gates. They continue to prove historical writer semantics, authenticated canonical-target materialization semantics, and immutable write-once persistence while accepting this bounded provider-registration successor envelope.

## Canonical Boundaries

`SPRINT123_PROVIDER_REGISTRATION = REGISTERED_SOURCE_ONLY`

`SPRINT123_CONTROL_CHANNEL_DELIVERY = INACTIVE`

`SPRINT123_DELIVERY_GATE = DEFAULT_OFF_AND_TOKEN_REQUIRED`

`SPRINT123_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SPRINT123_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`DB_BINDING_PROVIDER_REGISTRATION = NOT_REGISTERED`

`DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

No workflow dispatch, environment mutation, secret provisioning, manifest materialization, selected-target persistence, DB-binding producer execution, migration #27 execution, permission provisioning, deployment, release, feature activation, Technical Preview activation, Production activation, or updater activation is performed by Sprint123.

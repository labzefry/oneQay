# Sprint124 — Final Shift Close Runtime DB Binding Attestation Control Plane Registration

Author by Lab | zefry

## Objective

Sprint124 closes the bounded source-wiring gap intentionally left by Sprint119 for the read-only runtime DB-binding attestation endpoint.

The runtime DB-binding attestation service provider is registered with the application through the existing `Application::configure()->withProviders()` bootstrap boundary, while HTTP delivery remains fail-closed by default. Route registration requires both an explicitly enabled default-off configuration gate and a dedicated bearer token whose length is between 32 and 512 characters.

No runtime token is provisioned, no gate is enabled, and no attestation request is executed by this Sprint.

## Security Semantics

Sprint124 preserves the Sprint119 read-only active-database identity and selected-runtime provenance contract and the Sprint123 runtime binding manifest materialization boundary.

The source registration boundary is constrained as follows:

- the provider is registered through `apps/web/bootstrap/app.php`;
- `apps/web/bootstrap/providers.php` remains unchanged;
- the global `apps/web/config/oneqay.php` remains unchanged;
- the delivery gate defaults to `false`;
- the token defaults to an empty string;
- the provider rejects route registration unless the token is a string between 32 and 512 characters;
- the middleware receives the expected token through contextual container binding;
- malformed, missing, empty, too-short, too-long, or mismatched bearer credentials remain indistinguishable through an HTTP 404 response;
- bearer comparison remains constant-time through `hash_equals()`;
- the attestation route remains GET-only and throttled to two requests per minute;
- the attestation service remains read-only against the active `oneqay` database connection;
- migration #27 presence remains a fail-closed rejection condition inside the reader;
- the runtime binding manifest remains owner-only, non-symlink, selected-target provenance;
- no caller-supplied target identity or database identity authority is introduced.

## Configuration Source Boundary

The source declares only names and fail-closed defaults:

`ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_ENABLED=false`

`ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_TOKEN=`

Sprint124 does not write either value into any environment, secret store, deployment target, runtime, or repository secret.

## Successor Compatibility

Sprint96 and Sprint119 through Sprint123 qualification workflows remain active as exact successor-compatibility gates. Historical runtime wiring, read-only database identity, manifest writer, canonical materialization, write-once/CAS persistence, and default-off manifest control-plane semantics remain executable or source-verified as applicable.

Unknown source envelopes remain fail-closed.

## Canonical Boundaries

`SPRINT124_PROVIDER_REGISTRATION = REGISTERED_SOURCE_ONLY`

`SPRINT124_RUNTIME_DB_BINDING_ATTESTATION_DELIVERY = INACTIVE`

`SPRINT124_DELIVERY_GATE = DEFAULT_OFF_AND_TOKEN_REQUIRED`

`SPRINT124_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SPRINT124_RUNTIME_DB_BINDING_ATTESTATION = NOT_PERFORMED`

`SPRINT124_DATABASE_IDENTITY_MODE = READ_ONLY`

`SPRINT124_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

No workflow dispatch, environment mutation, secret provisioning, runtime manifest materialization, runtime DB-binding attestation invocation, selected-target persistence, DB-binding producer execution, migration #27 execution, permission provisioning, deployment, release, feature activation, Technical Preview activation, Production activation, or updater activation is performed by Sprint124.

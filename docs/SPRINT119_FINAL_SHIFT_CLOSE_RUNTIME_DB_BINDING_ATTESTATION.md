# Sprint119 Final Shift Close Runtime DB-Binding Attestation Source

Author by Lab | zefry

## Purpose

Sprint119 materializes the runtime-side source contract required by the Sprint118 selected-target database-binding producer.

The source can derive a secret-free database identity fingerprint from the application's active `oneqay` MySQL connection and can emit the exact 16-field response consumed by Sprint118. It is intentionally **not delivered** in this sprint.

Sprint119 does not deploy or activate the endpoint, select a runtime target, dispatch the Sprint118 producer, execute migration #27, provision `pos.shift.close`, widen runtime allowlists, enable Final Shift Close, activate Technical Preview or Production, release, or activate the updater.

## Double fail-closed delivery boundary

Two independent source conditions keep delivery inactive:

1. `FinalShiftCloseRuntimeDbBindingAttestationServiceProvider` is **not registered** in `apps/web/bootstrap/providers.php`.
2. its `deliveryEnabled()` method is a hard `false`.

A successor must change source and pass separate qualification before the route can become reachable.

The bearer token configuration key is reserved as:

`oneqay.final_shift_close_runtime_db_binding_attestation.token`

Sprint119 does not materialize that configuration. Missing token configuration therefore also fails closed.

## Runtime binding manifest

The service reads only:

`storage/app/private/final-shift-close-runtime-binding.json`

The manifest must be a regular non-symlink file, owner-only, readable, and at most 32768 bytes.

It must bind:

- `selection_state = SELECTED_NOT_AUTHORIZED`;
- environment ID;
- eligible non-synthetic non-production runtime class;
- exact running source SHA;
- exact running artifact SHA-256;
- readiness attestation SHA-256;
- selection fingerprint SHA-256;
- trusted ingestion run ID/attempt/fingerprint;
- `secrets_embedded = false`.

Sprint119 does not implement the writer for this manifest. This prevents source publication from manufacturing runtime provenance.

## Active database identity

The infrastructure reader uses the application's canonical `oneqay` connection and requires MySQL.

Before returning any attestation it proves:

- `migrations` exists;
- `oneqay_pos_shift_close_evidence` does not exist;
- migration `0000_00_00_000027_create_pos_shift_close_evidence_foundation` is not recorded.

It then reads only:

```sql
SELECT DATABASE() AS database_name, @@hostname AS server_hostname, @@port AS server_port
```

The canonical payload is key-sorted and hashed with SHA-256 using unescaped JSON. No database credential, token, manifest path, table data, or secret is returned.

## HTTP source contract

Reserved route:

`GET /internal/final-shift-close/runtime-db-binding-attestation`

Reserved middleware:

`RequireFinalShiftCloseRuntimeBindingTokenMiddleware`

The middleware accepts only an exact bearer token and compares it using `hash_equals()`. Failure returns a no-store 404 response.

The successful JSON payload contains exactly the fields required by Sprint118:

- schema version;
- feature;
- verified binding state;
- environment/runtime identity;
- running source/artifact provenance;
- readiness and selection fingerprints;
- trusted ingestion identity;
- migration27 `NOT_EXECUTED`;
- database binding algorithm + SHA-256;
- database identity source;
- read-only attestation mode;
- `secrets_embedded = false`.

## Exact source envelope

Sorted-newline SHA-256:

`2b7741ab925e255f9faefedfaebafcff001f294137fbae41fe4519fd710bf061`

Sprint119 changes exactly 12 paths and does not modify the Sprint118 producer, migration executor, migration source, operational state, durable target selection, config, or provider registration.

## Current no-go disposition

`SPRINT119_RUNTIME_DB_BINDING_ATTESTATION_SOURCE = MATERIALIZED`

`SPRINT119_RUNTIME_DB_BINDING_ATTESTATION_DELIVERY = INACTIVE`

`SPRINT119_PROVIDER_REGISTRATION = NOT_REGISTERED`

`SPRINT119_DELIVERY_GATE = HARD_FALSE`

`SPRINT119_TOKEN_CONFIGURATION = NOT_MATERIALIZED`

`SPRINT119_RUNTIME_MANIFEST_WRITER = NOT_IMPLEMENTED`

`CURRENT_SELECTION_STATE = BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`

`SELECTED_TARGET = NONE`

`SPRINT118_DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION27_SELECTED_TARGET_BINDING_EVIDENCE = NONE`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`RUNTIME_ALLOWLIST_CHANGE = NOT_IMPLEMENTED`

`ENVIRONMENT_DEPLOYMENT = NOT_PERFORMED`

`TECHNICAL_PREVIEW_ACTIVATION = NOT_AUTHORIZED`

`PRODUCTION_ACTIVATION = NOT_AUTHORIZED`

`UPDATER_ACTIVATION = INACTIVE`

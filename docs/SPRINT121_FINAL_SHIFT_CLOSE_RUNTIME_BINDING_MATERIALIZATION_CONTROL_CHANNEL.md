# Sprint121 — Final Shift Close Runtime Binding Materialization Control Channel

Author by Lab | zefry

## Purpose

Sprint121 materializes a source-only authenticated control-channel contract for future
materialization of the private Final Shift Close runtime binding manifest introduced
in Sprint120.

This Sprint does **not** make the channel reachable and does **not** write a runtime
manifest.

## Security boundary

The future request is an exact five-field object:

- `schema_version = 1`
- `operation = final-shift-close.runtime-binding.materialize`
- deterministic `operation_id = fscbind_<first32(manifest_sha256)>`
- exact SHA-256 of the canonical Sprint120 manifest JSON
- exact Sprint120 manifest payload

Unknown fields, caller-selected target fields, malformed fingerprints, Preview,
synthetic, Production, or secret-bearing manifest payloads are rejected.

Authentication uses a dedicated Bearer-token namespace with exact parsing and
`hash_equals()`. The token configuration is intentionally absent in Sprint121, so
the middleware denies by default.

## Anti-stale blocker

Sprint120 intentionally provides an atomic private filesystem writer, but that writer
permits deterministic rewrite. It does not provide a create-once or compare-and-swap
selection guard.

Therefore Sprint121 does **not** bind
`FinalShiftCloseRuntimeBindingMaterializer` to the Sprint120 writer.

A separately qualified successor must first materialize write-once/CAS semantics that
prove a different or stale selected-target manifest cannot replace an existing trusted
runtime binding.

## Delivery boundary

The reserved source route is:

`POST /internal/final-shift-close/runtime-binding-materialization`

The new service provider:

- is absent from `bootstrap/providers.php`;
- has a hard `false` delivery gate;
- intentionally binds no materializer implementation.

Therefore source publication cannot materialize a manifest or expose the control
channel.

## Canonical no-go state

`SPRINT121_RUNTIME_BINDING_MATERIALIZATION_CONTROL_CHANNEL_SOURCE = MATERIALIZED`

`SPRINT121_RUNTIME_BINDING_MATERIALIZATION_DELIVERY = INACTIVE`

`SPRINT121_MATERIALIZER_IMPLEMENTATION = NOT_IMPLEMENTED`

`SPRINT121_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SPRINT119_RUNTIME_DB_BINDING_ATTESTATION_DELIVERY = INACTIVE`

`SELECTED_TARGET = NONE`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`TECHNICAL_PREVIEW_ACTIVATION = NOT_AUTHORIZED`

`PRODUCTION_ACTIVATION = NOT_AUTHORIZED`

`UPDATER_ACTIVATION = INACTIVE`

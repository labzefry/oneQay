# Sprint121 — Final Shift Close Runtime Binding Manifest Control Channel

Author by Lab | zefry

## Purpose

Sprint121 materializes source-only authenticated control-channel plumbing that can later invoke the Sprint120 private manifest writer. It does not activate the channel or materialize a runtime manifest.

## Trust boundary

The request may contain only:

- `operation_id`;
- `expected_selection_fingerprint_sha256`.

The caller cannot supply environment ID, runtime class, running source SHA, artifact digest, readiness digest, ingestion provenance, or manifest contents. `FinalShiftCloseRuntimeBindingManifestMaterializer` reads `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json` and requires `SELECTED_NOT_AUTHORIZED` before deriving the manifest exclusively from `selected_target`.

The expected selection fingerprint is compared with `hash_equals()` before the Sprint120 writer can be invoked.

## HTTP source

Reserved route:

`POST /internal/final-shift-close/runtime-binding-manifest/materialize`

The source includes exact Bearer authentication, one-request-per-minute throttling, no-store/no-index responses, exact request keys, and generic failure responses.

## Double fail-closed delivery

`FinalShiftCloseRuntimeBindingManifestMaterializationServiceProvider` is intentionally absent from `bootstrap/providers.php` and `deliveryEnabled()` returns `false`.

The token configuration key `oneqay.final_shift_close_runtime_binding_materialization_token` is referenced by source but is not materialized in configuration in this sprint.

Therefore source publication cannot expose the route or write the runtime binding manifest.

## Operational no-go

`SPRINT121_CONTROL_CHANNEL_SOURCE = MATERIALIZED`

`SPRINT121_CONTROL_CHANNEL_DELIVERY = INACTIVE`

`SPRINT121_PROVIDER_REGISTRATION = NOT_REGISTERED`

`SPRINT121_DELIVERY_GATE = HARD_FALSE`

`SPRINT121_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`SPRINT118_DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`TECHNICAL_PREVIEW_ACTIVATION = NOT_AUTHORIZED`

`PRODUCTION_ACTIVATION = NOT_AUTHORIZED`

`UPDATER_ACTIVATION = INACTIVE`

# Sprint120 — Final Shift Close Runtime Binding Manifest Writer

Author by Lab | zefry

## Purpose

Sprint120 materializes the source-only writer required to create the private runtime binding manifest consumed by the Sprint119 read-only runtime database-binding attestation source.

This sprint does not create a runtime manifest on any deployed environment and does not expose a command, HTTP route, provider registration, deployment hook, or other operational invocation channel.

## Canonical starting point

- Sprint119 canonical main: `c0ec8895de06d843e2dcad5e69f718df5bab6be9`.
- Durable selected target remains `NONE`.
- Sprint118 selected-target database-binding producer has not been dispatched.
- Migration #27 remains not executed.
- Permission provisioning remains `NONE`.
- Final Shift Close remains `INACTIVE`.

## Writer contract

`FinalShiftCloseRuntimeBindingManifest` accepts only the exact manifest shape consumed by Sprint119:

- schema version `1`;
- feature `final-shift-close`;
- selection state `SELECTED_NOT_AUTHORIZED`;
- explicit environment ID and non-synthetic, non-Production durable runtime class;
- exact running source commit;
- exact running artifact SHA-256;
- readiness-attestation SHA-256;
- selection fingerprint SHA-256;
- exact trusted-ingestion run ID, run attempt, and ingestion fingerprint;
- `secrets_embedded=false`.

Unknown fields fail closed.

The object serializes a deterministic canonical JSON representation with a final newline.

## Filesystem safety

`FilesystemFinalShiftCloseRuntimeBindingManifestWriter` requires an absolute target path and a pre-existing private parent directory.

The writer:

1. rejects symlink or group/world-accessible parent directories;
2. rejects symlink targets;
3. refuses an existing target that is not already a private regular file;
4. uses an owner-only lock file;
5. writes an owner-only exclusive temporary file;
6. flushes and fsyncs when available;
7. atomically renames the temporary file over the target;
8. re-applies `0600` to the final manifest;
9. verifies the final file is a private regular non-symlink file;
10. verifies the final SHA-256 matches the canonical payload.

This is a source safety primitive only. It is not an operational distribution or configuration channel.

## Explicitly not implemented

Sprint120 does not implement:

- writer command registration;
- writer HTTP route;
- writer service provider;
- runtime manifest distribution;
- authenticated configuration mutation;
- provider registration for Sprint119;
- Sprint119 delivery activation;
- runtime token configuration;
- deployment;
- Sprint118 producer dispatch;
- migration #27 execution;
- `pos.shift.close` provisioning;
- Final Shift Close activation;
- Technical Preview or Production activation;
- updater activation.

## Qualification

The Sprint120 workflow locks the exact seven-path source envelope, validates the machine-readable contract, verifies that Sprint119 provider registration and hard-deny delivery remain unchanged, syntax-checks the PHP source, and runs the standalone filesystem regression.

`SPRINT120_RUNTIME_BINDING_MANIFEST_WRITER_SOURCE = MATERIALIZED`

`SPRINT120_WRITER_INVOCATION = NOT_IMPLEMENTED`

`SPRINT120_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SPRINT119_RUNTIME_DB_BINDING_ATTESTATION_DELIVERY = INACTIVE`

`SELECTED_TARGET = NONE`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

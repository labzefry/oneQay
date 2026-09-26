# Sprint256 — Final Shift Close Post-Activation Reattestation and Selected-Target Generation Successor

Author by Lab | zefry

## Purpose

Sprint256 closes the repository-side continuity gap after an authorized same-target durable-staging source promotion while Final Shift Close is already `ACTIVE`.

The historical Sprint110–Sprint115 attestation, ingestion, and target-selection chain is intentionally preserved. That chain is the original pre-activation readiness boundary and requires `feature_activation_state = INACTIVE`; it must not be reused to describe a post-activation runtime.

The canonical post-activation promotion contract already requires: verify Final Shift Close remains `ACTIVE`, produce fresh runtime reattestation, update the selected-target generation for the same durable-staging target, and only then advance dark-production readiness.

Sprint256 materializes that successor chain without executing live deployment, feature activation, migration replay, permission reprovisioning, target reselection, Production deployment, or updater activation.

## Successor workflows

The post-activation producer is `.github/workflows/final-shift-close-post-activation-durable-runtime-reattestation.yml`. It runs only through `workflow_dispatch` from canonical `main`, reuses the protected environment `final-shift-close-durable-runtime-attestation`, qualifies protected deployment evidence with `tools/qualify-durable-staging-post-activation-deployment-evidence.php`, fetches authenticated runtime readiness, requires exact durable-staging environment/source/artifact identity and `feature_activation_state = ACTIVE`, and publishes secret-free attestation/provenance evidence.

The trusted ingestion successor is `.github/workflows/final-shift-close-post-activation-durable-runtime-reattestation-ingestion.yml`. It accepts only an exact successful run of the post-activation producer and preserves `ingestion_state = ACCEPTED_NOT_SELECTED`, `canonical_selection_state = SELECTED_NOT_AUTHORIZED`, `selected_target = null`, `activation_authority_state = NOT_GRANTED`, `feature_activation_state = ACTIVE`, `runtime_allowlist_change = NOT_PERFORMED`, and `persistence_state = NOT_PERFORMED`. It does not mutate canonical target selection.

The same-target generation gate is `.github/workflows/final-shift-close-post-activation-selected-target-generation.yml`. It accepts an exact one-file PR changing only `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`. It requires the selection state to remain `SELECTED_NOT_AUTHORIZED`, environment ID/runtime class to remain unchanged, the prior running source to be an ancestor of the new source, exact fresh source/artifact/readiness/fingerprint/trusted-ingestion binding, no field outside `selected_target` to change, exact-head Product Owner merge authority, and successful generation evidence before merge. This is a selected-target generation update, not target reselection.

## Historical compatibility

The historical workflows `.github/workflows/final-shift-close-durable-runtime-attestation.yml`, `.github/workflows/final-shift-close-durable-runtime-attestation-ingestion.yml`, and `.github/workflows/final-shift-close-durable-runtime-target-selection-persistence.yml`, together with the historical readiness/ingestion/selector PHP classes, remain untouched and keep their original pre-activation semantics.

## Protected-environment inputs after merge

Before operational dispatch, the reused protected environment must be bound to the fresh post-activation deployment:

- `ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID = oneqay-durable-staging-01`
- `ONEQAY_DURABLE_RUNTIME_CLASS = durable-staging`
- `ONEQAY_DURABLE_RUNTIME_RUNNING_SOURCE_COMMIT = 5a96825defb8a805b8b754f0d64fceb7e0826369`
- `ONEQAY_DURABLE_RUNTIME_RUNNING_ARTIFACT_SHA256 = 1e64d6507eed5e60af9dde1a7c271224c4c1c9b7e7e1da1e2ddbf373fda7b6e1`
- `ONEQAY_DURABLE_STAGING_DEPLOYMENT_PLAN_FINGERPRINT = b95bbae109512b3ebbc9e820fc26c012745ac23569539fb9ffc48abdee9f6dec`
- `ONEQAY_DURABLE_STAGING_DEPLOYMENT_AUTHORITY_SHA256 = a6c9c2a936be26b8f09c683cf5d3421876dcfd443c2395600dede9ecb736eeca`
- `ONEQAY_DURABLE_STAGING_DEPLOYMENT_EVIDENCE_B64` must contain the protected base64 encoding of the exact post-activation deployment evidence.

The existing attestation URL, attestation token, and protection assertions are reused and must never be exposed in repository content.

## Operational boundary

Sprint256 source materialization does not dispatch a successor workflow and does not mutate runtime state. Production traffic activation remains `NOT_AUTHORIZED`; Production deployment is not performed; updater activation remains `INACTIVE`; migration #27 is not replayed; permission `pos.shift.close` is not reprovisioned; Final Shift Close remains `ACTIVE`.

After Sprint256 merges, the next operational step is protected-environment rebinding followed by fresh post-activation reattestation dispatch.

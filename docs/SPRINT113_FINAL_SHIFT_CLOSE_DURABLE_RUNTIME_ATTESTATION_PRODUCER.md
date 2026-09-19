# Sprint113 Final Shift Close Durable Runtime Attestation Producer

Author by Lab | zefry

## Purpose

Sprint113 materializes the trusted producer source reserved by Sprint112 for future isolated non-production durable runtime readiness attestation.

The producer is source-only in this Sprint. It is **not dispatched**, does not create or deploy an environment, does not persist target selection, does not widen a runtime allowlist, and does not activate Final Shift Close.

## Canonical producer

- workflow: `.github/workflows/final-shift-close-durable-runtime-attestation.yml`
- event: manual `workflow_dispatch`
- caller-supplied target inputs: none
- repository: `labzefry/oneQay`
- required ref: `refs/heads/main`
- GitHub Environment: `final-shift-close-durable-runtime-attestation`
- evidence context: `final-shift-close-durable-runtime-attestation-evidence`
- evidence target: exact running application source commit
- evidence bundle artifact: `final-shift-close-durable-runtime-attestation`

The workflow refuses to run from a non-current `main` source. Runtime target identity is not accepted from dispatch input; it is bound to protected-environment variables and an authenticated HTTPS runtime attestation endpoint.

Sprint209 strengthens this producer: before the runtime endpoint is contacted, protected durable-staging deployment execution evidence must qualify against the exact environment, running source, artifact SHA-256, Sprint207 deployment-plan fingerprint, and Sprint208 deployment-authority SHA-256. A readiness endpoint by itself is no longer sufficient evidence of an authorized deployment path.

## Protected environment prerequisites

Before any future dispatch, the GitHub Environment must be configured outside repository source with appropriate review/protection controls. Sprint113 cannot machine-verify GitHub Environment reviewer configuration from repository source, so it does not claim that configuration exists.

Required environment secrets:

- `ONEQAY_DURABLE_RUNTIME_ATTESTATION_URL`
- `ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN`
- `ONEQAY_DURABLE_RUNTIME_ATTESTATION_PROTECTION_ASSERTION`
- `ONEQAY_DURABLE_STAGING_DEPLOYMENT_EVIDENCE_B64`
- `ONEQAY_DURABLE_STAGING_DEPLOYMENT_EVIDENCE_PROTECTION_ASSERTION`

Required environment variables:

- `ONEQAY_DURABLE_RUNTIME_ENVIRONMENT_ID`
- `ONEQAY_DURABLE_RUNTIME_CLASS`
- `ONEQAY_DURABLE_RUNTIME_RUNNING_SOURCE_COMMIT`
- `ONEQAY_DURABLE_RUNTIME_RUNNING_ARTIFACT_SHA256`
- `ONEQAY_DURABLE_STAGING_DEPLOYMENT_PLAN_FINGERPRINT`
- `ONEQAY_DURABLE_STAGING_DEPLOYMENT_AUTHORITY_SHA256`

The attestation protection assertion must equal `REVIEW_GATED_V1`. The deployment-evidence protection assertion must equal `REVIEW_GATED_DEPLOYMENT_EVIDENCE_V1`. These are fail-closed sentinels and do not replace actual GitHub Environment reviewer/protection configuration.

The deployment evidence is transported as base64-encoded JSON through the protected GitHub Environment secret, decoded only into a private temporary file, capped at 32 KiB, and validated by `tools/qualify-durable-staging-deployment-evidence.php`. The evidence itself must declare `secrets_embedded=false` and preserve all operational NO-GO boundaries.

## Runtime evidence contract

The endpoint must be HTTPS and authenticated with the protected-environment bearer token. Its response is capped at 32 KiB and must be the exact 22-field Sprint110 readiness attestation.

Only after deployment evidence qualifies is the runtime readiness endpoint fetched. The current Sprint110 validator is then executed against the response. In addition:

- environment identity must equal the protected-environment configured identity;
- runtime class must equal the protected-environment configured class;
- the runtime class must not be `local`, `test`, `testing`, `ci`, `preview`, `synthetic-preview`, `production`, or `prod`;
- exact running source commit must exist in `labzefry/oneQay` and belong to canonical `main` history;
- exact running artifact SHA-256 must equal the protected deployment-evidence binding;
- exact running source commit must equal the protected deployment-evidence binding;
- deployment evidence must prove preflight, immutable extraction, public-document-root validation, external configuration binding, provenance/readback checks, non-mutating health, and rollback-path verification;
- exact running artifact SHA-256 is carried unchanged into provenance.

## Evidence production

After qualification, the workflow creates two secret-free JSON files:

1. `attestation.json` — exact Sprint110 readiness attestation;
2. `provenance.json` — Sprint112-compatible provenance including producer source SHA, run ID, run attempt, attestation SHA-256, runtime/build binding, deployment-evidence SHA-256, Sprint207 deployment-plan fingerprint, Sprint208 deployment-authority SHA-256, and evidence context.

Sprint210 makes those three deployment bindings mandatory provenance fields and validates them as non-zero lowercase SHA-256 values before publication. This closes continuity from the protected Sprint209 deployment-evidence gate into Sprint112/Sprint114 ingestion.

The workflow publishes `pending` status first, uploads the evidence bundle, and only then publishes `success` to the exact running source commit. A failure before the final status cannot satisfy Sprint112 ingestion because the required latest evidence state is not `success`.

The artifact upload action is immutably pinned to `ea165f8d65b6e75b540449e92b4886f43607fa02` (`actions/upload-artifact` v4.6.2 source commit).

## Security boundaries

Sprint113 does not:

- dispatch the attestation producer;
- assert that a real durable target exists;
- execute trusted ingestion;
- persist `SELECTED_NOT_AUTHORIZED`;
- modify `ops/final-shift-close/STATE.json`;
- modify `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`;
- expand Final Shift Close runtime allowlists;
- run migration #27;
- provision `pos.shift.close`;
- enable `ONEQAY_POS_SHIFT_CLOSE_ENABLED`;
- activate Technical Preview or Production;
- deploy, release, change DNS, or activate updater behavior.

## Canonical status after Sprint113

`DURABLE_RUNTIME_ATTESTATION_PRODUCER_SOURCE = MATERIALIZED_NOT_DISPATCHED`

`PROTECTED_ENVIRONMENT_CONFIGURATION = REQUIRED_OUT_OF_BAND_WITH_SOURCE_VERIFIED_DEPLOYMENT_EVIDENCE`

`REAL_DURABLE_RUNTIME_ATTESTATION = NONE`

`TRUSTED_ATTESTATION_INGESTION_EXECUTION = NOT_IMPLEMENTED`

`SELECTED_TARGET = NONE`

`RUNTIME_ALLOWLIST_CHANGE = NOT_IMPLEMENTED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`TECHNICAL_PREVIEW_ACTIVATION = NOT_AUTHORIZED`

`PRODUCTION_ACTIVATION = NOT_AUTHORIZED`

`UPDATER_ACTIVATION = INACTIVE`

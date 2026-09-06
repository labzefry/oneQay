# Sprint114 — Final Shift Close Durable Runtime Attestation Ingestion Execution Gate

Author by Lab | zefry

## Purpose

Sprint114 materializes the source-only executor that can retrieve one exact trusted Sprint113 durable-runtime attestation evidence artifact and run the Sprint112 ingestion contract against it.

The executor is deliberately bounded to evidence retrieval and deterministic candidate ingestion. It does **not** select or persist a durable activation target, grant activation authority, widen runtime allowlists, deploy an environment, execute migration #27, provision `pos.shift.close`, or activate Final Shift Close.

## Canonical entry state

- Sprint110 durable runtime readiness validator: materialized.
- Sprint111 deterministic target-selection mechanism: materialized, but no target selected.
- Sprint112 provenance + ingestion gate: materialized.
- Sprint113 trusted attestation producer source: materialized but not dispatched.
- Real durable runtime attestation: none.
- Canonical target selection: blocked / selected target none.
- Final Shift Close feature: inactive.

## Executor source

Workflow:

`.github/workflows/final-shift-close-durable-runtime-attestation-ingestion.yml`

The workflow is manual `workflow_dispatch` only and accepts exactly two evidence-identity inputs:

1. `producer_run_id`
2. `producer_run_attempt`

These values identify an already-completed Sprint113 producer run. They do **not** identify or override environment ID, runtime class, source commit, artifact digest, feature state, or any activation target field.

## Exact producer-run qualification

Before evidence is consumed, the executor requires all of the following:

- executor itself is running from current canonical `main`;
- repository is exactly `labzefry/oneQay`;
- producer run exists in this repository;
- producer run ID and run attempt exactly match dispatch inputs;
- producer event is `workflow_dispatch`;
- producer workflow path is exactly `.github/workflows/final-shift-close-durable-runtime-attestation.yml`;
- producer head branch is `main`;
- producer run is `completed` with conclusion `success`;
- producer head SHA remains in canonical-main history relative to current executor source.

## Exact artifact qualification

The executor accepts exactly one non-expired producer artifact named:

`final-shift-close-durable-runtime-attestation`

The artifact ZIP is capped at 262,144 bytes and must contain exactly:

- `attestation.json`
- `provenance.json`

Each JSON file is capped at 32,768 bytes and must decode to an object.

No replacement file, extra artifact member, expired artifact, duplicate artifact, or differently named artifact is accepted.

## Run-to-provenance binding

The retrieved provenance must bind exactly to the resolved GitHub Actions run:

- `producer_run_id` equals the exact resolved run ID;
- `producer_run_attempt` equals the exact resolved attempt;
- `producer_source_commit` equals the producer run `head_sha`;
- producer workflow path remains canonical;
- producer ref remains `refs/heads/main`;
- producer evidence status context remains `final-shift-close-durable-runtime-attestation-evidence`;
- provenance declares success and contains no secrets.

The executor then checks the actual GitHub commit statuses for the attested running source. A matching success status must exist for the canonical producer evidence context **and** its `target_url` must point to the same exact producer run ID. This prevents an unrelated or stale success from satisfying ingestion.

## Sprint110 + Sprint112 reuse

The executor does not duplicate readiness or provenance semantics. It invokes the canonical application classes:

- `FinalShiftCloseDurableRuntimeReadiness`
- `FinalShiftCloseDurableRuntimeAttestationProvenance`
- `FinalShiftCloseDurableRuntimeAttestationIngestion`

Therefore a retrieved artifact only reaches ingestion if the current Sprint110 readiness contract and Sprint112 provenance contract still accept it.

## Output boundary

The only accepted ingestion state is:

`ACCEPTED_NOT_SELECTED`

The execution output must continue to report:

- selected target = null;
- activation authority = `NOT_GRANTED`;
- feature activation = `INACTIVE`;
- persistence = `NOT_PERFORMED`.

The executor creates a secret-free evidence artifact named:

`final-shift-close-durable-runtime-attestation-ingestion`

containing:

- `attestation.json`
- `provenance.json`
- `ingestion.json`
- `execution.json`

The status context `final-shift-close-durable-runtime-attestation-ingestion-evidence` is published as `pending` before artifact publication and may become `success` only after the ingestion artifact upload succeeds.

## Sprint114 execution posture

Sprint114 only merges the executor source and its qualification contract. The workflow is **not dispatched** in Sprint114 because no real durable runtime attestation currently exists.

`DURABLE_RUNTIME_ATTESTATION_INGESTION_EXECUTOR_SOURCE = MATERIALIZED_NOT_DISPATCHED`

`PRODUCER_DISPATCH = NOT_PERFORMED`

`REAL_DURABLE_RUNTIME_ATTESTATION = NONE`

`TRUSTED_ATTESTATION_INGESTION_EXECUTION = NOT_PERFORMED`

`INGESTION_OUTPUT_STATE = ACCEPTED_NOT_SELECTED`

`SELECTED_TARGET = NONE`

`RUNTIME_ALLOWLIST_CHANGE = NOT_IMPLEMENTED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`TECHNICAL_PREVIEW_ACTIVATION = NOT_AUTHORIZED`

`PRODUCTION_ACTIVATION = NOT_AUTHORIZED`

`UPDATER_ACTIVATION = INACTIVE`

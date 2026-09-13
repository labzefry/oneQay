# Sprint148 — Final Shift Close Durable Runtime Capability Evidence Binding

Author by Lab | zefry

## Purpose / Why

Sprint110 established the fail-closed durable-runtime readiness attestation shape, including four required capability booleans: authenticated configuration mutation, read-before/write/read-after verification, non-mutating health attestation, and verified flag rollback. Sprint111 then bound a qualified readiness attestation to an exact durable target identity and deterministic selection fingerprint while preserving `SELECTED_NOT_AUTHORIZED`.

Those controls are necessary but not yet sufficient for future runtime-class eligibility. A boolean capability claim inside a readiness attestation proves that the claim has the canonical shape, but it does not by itself prove that capability-specific evidence exists for the exact environment, runtime class, source commit, artifact, readiness attestation, and selection fingerprint that Sprint111 selected.

`POST_SELECTION_DOWNSTREAM_READINESS.json` already forbids widening the Final Shift Close runtime allowlist before the full durable dependency envelope is qualified. Sprint148 closes the missing identity layer between generic capability claims and that future dependency-envelope qualification without performing any operational action.

## Objective / Gap

Bounded objective:

`DURABLE_RUNTIME_TARGET_BOUND_CAPABILITY_EVIDENCE_IDENTITY_BINDING`

Sprint148 requires a capability-evidence bundle to be bound to the exact Sprint111 selected target before it can become a prerequisite for any future runtime-allowlist eligibility. Qualification requires all of the following:

1. the Sprint110 readiness attestation still qualifies exactly;
2. the supplied Sprint111 selection recomputes exactly from that attestation;
3. selection remains `SELECTED_NOT_AUTHORIZED`, activation authority remains `NOT_GRANTED`, Final Shift Close remains `INACTIVE`, and runtime allowlist change remains `NOT_IMPLEMENTED`;
4. evidence identity exactly matches environment ID, runtime class, source commit, artifact SHA-256, readiness-attestation SHA-256, and selection fingerprint;
5. a deterministic target-binding SHA-256 binds that complete selected-target identity;
6. each required capability has its own `VERIFIED` evidence record, canonical evidence kind, lowercase 64-character SHA-256 digest, exact target-binding digest, and no embedded secret;
7. evidence-bundle fingerprinting is deterministic and key-order independent.

Missing capability evidence, malformed digests, generic/incorrect evidence kinds, selection or runtime identity drift, cross-target evidence, secret-bearing evidence, or an unqualified Sprint110 attestation all fail closed.

## What changed

- Added `FinalShiftCloseDurableRuntimeCapabilityEvidence` as a pure application-layer qualifier for target-bound capability evidence identity.
- Added an executable Sprint148 regression covering the canonical qualification path and fail-closed cases for missing evidence, malformed digest, wrong evidence kind, wrong target binding, target identity drift, secret-bearing evidence, unqualified readiness, selection fingerprint drift, and authority drift.
- Added `DURABLE_RUNTIME_CAPABILITY_EVIDENCE_BINDING_CONTRACT.json` as the machine-readable Sprint148 contract.
- Extended `POST_SELECTION_DOWNSTREAM_READINESS.json` so future runtime-allowlist eligibility explicitly requires target-bound capability-evidence identity qualification before the full durable runtime envelope can qualify.
- Added an active exact-head Sprint148 workflow that owns the six-path engineering envelope, validates the new contract/integration, preserves Sprint110/Sprint111 historical behavior, executes the regression, and reasserts canonical operational NO-GO state.
- No runtime class was enabled and no real capability-evidence producer was materialized or dispatched.

## Evidence / Qualification

Sprint148 is CI-only bounded source engineering. Its exact six-path source envelope is:

1. `.github/workflows/sprint148-final-shift-close-durable-runtime-capability-evidence-binding-regression.yml`
2. `apps/web/app/Application/Pos/FinalShiftCloseDurableRuntimeCapabilityEvidence.php`
3. `apps/web/tests/pos-final-shift-close-durable-runtime-capability-evidence-binding.php`
4. `docs/SPRINT148_FINAL_SHIFT_CLOSE_DURABLE_RUNTIME_CAPABILITY_EVIDENCE_BINDING.md`
5. `ops/final-shift-close/DURABLE_RUNTIME_CAPABILITY_EVIDENCE_BINDING_CONTRACT.json`
6. `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`

Frozen sorted newline-terminated envelope SHA-256:

`57ba389d323f4c2be1e37708fa86324f2731acda53ae3f7b7133d806f0f3c895`

Qualification requires:

- exact-head checkout and exact six-path envelope lock;
- machine-readable Sprint148 contract validation;
- post-selection readiness integration validation;
- executable Sprint148 target-bound capability-evidence regression;
- Sprint110 durable-runtime readiness regression preservation;
- Sprint111 durable-runtime target-selection regression preservation;
- PHP syntax validation, Composer validation/install/audit, and tracked-source cleanliness;
- canonical lifecycle and durable-target NO-GO assertions;
- repository-native Product Owner merge authority before squash merge.

Real capability evidence is intentionally absent in Sprint148. The new source qualifies only the identity and shape of future evidence once such evidence is produced under a separately authorized operational lifecycle.

## Operational boundaries / NO-GO

Sprint148 does not execute, authorize, provision, select, activate, or deploy any operational resource. It does not:

- execute migration #27;
- provision Final Shift Close permissions;
- activate Final Shift Close;
- provision an operational runtime/control-plane token;
- persist or activate a durable runtime target;
- dispatch the durable-runtime attestation producer or trusted ingestion executor;
- produce or ingest real capability evidence;
- materialize a trusted capability-evidence producer;
- perform operational runtime-binding manifest materialization;
- perform operational DB-binding attestation;
- change the Final Shift Close runtime allowlist;
- materialize or dispatch the feature-activation executor;
- deploy or publish a release;
- activate Technical Preview;
- activate Production;
- activate the updater.

Machine-readable operational state remains authoritative in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

## Next position

After Sprint148 engineering is squash merged and its canonical documentation reconciliation is fully qualified and squash merged, Sprint148 may be declared CLOSED.

The next position is **Sprint149 bounded discovery from canonical post-Sprint148**. No Sprint149 objective, implementation, operational action, or source envelope is preselected here. Discovery must continue to prioritize the smallest material production-readiness prerequisite that does not bypass the canonical lifecycle or require ungranted operational authority.

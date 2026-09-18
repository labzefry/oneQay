# oneQay Roadmap

**Roadmap checkpoint:** Sprint186 closed canonically
**Canonical engineering baseline:** `7cb9e59ede2908f44f4f0d7b2d1c855d885bf3ae`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint186 horizon

Sprint186 closed `GOVERNED_ACTIVATION_READINESS_HANDOFF`.

The governed installation journey now advances beyond verified pending database configuration into a private tamper-evident handoff suitable for a future separately authorized activation step.

The handoff binds the exact governed release ID, pending environment SHA-256 and byte length, and safe database compatibility evidence. Any pending-file tampering or release mismatch invalidates readiness.

The operator UI shows the handoff as `SEALED / NOT AUTHORIZED`; readiness never implies runtime activation.

Engineering PR #802 qualified at 74/74 on final head `3c6453cd54ac0bc907d85ec01d7410d2c48e19fb` and squash merged at `7cb9e59ede2908f44f4f0d7b2d1c855d885bf3ae`.

Canonical main-push M7.5 run `35387074508` completed successfully. Final engineering envelope: 10 paths, SHA-256 `6a948cea5e7d88f95abec930a0681f851df9d04a1eaaa835f4aac3de1e0c8102`.

Canonical reconciliation envelope: 8 paths, SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Product progression through Sprint186

The product now has a governed path from deterministic release generation through installation readiness, secure pre-boot setup, live database compatibility proof, verified pending configuration, and tamper-evident exact-release activation handoff.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint187 selection rule

Begin Sprint187 bounded discovery from fully reconciled Sprint186. Select the smallest material P0/P1 blocker after sealed handoff that most directly advances toward a usable governed installation/onboarding experience.

No active environment promotion, migration execution, real permission provisioning, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry

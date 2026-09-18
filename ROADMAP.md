# oneQay Roadmap

**Roadmap checkpoint:** Sprint185 closed canonically
**Canonical engineering baseline:** `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint185 horizon

Sprint185 closed `PREBOOT_DATABASE_COMPATIBILITY_VERIFICATION`.

The governed installation journey now advances beyond secure configuration preparation into live database compatibility proof before pending runtime configuration is accepted.

The pre-boot verifier proves MySQL/MariaDB connectivity, server version shape, `utf8mb4`, UTC, acceptable schema ownership, and database-scoped least privilege. Unsafe or incompatible targets fail closed without committing `.env.pending`.

When verification succeeds, safe evidence is bound into the pending runtime configuration while activation remains explicitly false.

Engineering PR #799 qualified at 71/71 on final head `bf17397f739eab4aae531ed6b6a1b0b5430ae98e` and squash merged at `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`.

Canonical main-push M7.5 run `35382800589` completed successfully. Final engineering envelope: 10 paths, SHA-256 `775caa7723278af855b888f2c6bac592d9187e7b988619799f90e2e7a6950e99`.

Canonical reconciliation envelope: 8 paths, SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Product progression through Sprint185

The product now has a governed path from release automation through readiness and secure pre-boot setup to verified pending runtime configuration backed by a real database compatibility check.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint186 selection rule

Begin Sprint186 bounded discovery from fully reconciled Sprint185. Select the smallest material P0/P1 blocker after verified pending configuration that most directly advances toward a usable governed installation/onboarding experience.

No active environment promotion, migration execution, real permission provisioning, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry

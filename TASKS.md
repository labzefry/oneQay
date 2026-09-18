# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint185 closed
**Canonical engineering commit:** `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`
**Engineering PR:** #799 — `Sprint185: verify database compatibility before pending configuration`
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint185 state

Sprint185 materialized `PREBOOT_DATABASE_COMPATIBILITY_VERIFICATION`.

- [x] Post-Sprint184 installation journey reviewed from exact canonical checkpoint.
- [x] Material blocker selected: database compatibility had not been proven before pending configuration commit.
- [x] Pre-boot live database compatibility verifier implemented.
- [x] MySQL/MariaDB connectivity and version-shape verification implemented.
- [x] `utf8mb4` database charset verification implemented.
- [x] UTC session posture verification implemented.
- [x] Empty/recognized/foreign schema classification implemented.
- [x] Database-scoped least-privilege verification implemented.
- [x] Unsafe global/admin-style privileges fail closed.
- [x] Failed verification creates no pending configuration and preserves the still-valid authority for retry.
- [x] Successful verification binds safe database facts into `.env.pending`.
- [x] Verified pending state exposed as `PENDING_CONFIGURATION_VERIFIED`.
- [x] Active `.env` creation remains excluded.
- [x] Persistence, Technical Preview, Production, and updater activation remain excluded.
- [x] Dedicated Sprint185 regression used a real MySQL 8 service and least-privilege fixture.
- [x] Invalid live credentials proved fail-closed without secret or exception leakage.
- [x] Sprint184 pending-only regression remained successful.
- [x] Sprint172 database-readiness regression remained successful.
- [x] Governed M7.5 artifact packages the verifier and preserves NO_SCHEMA_CHANGE.
- [x] M7.5 and Sprint32/Sprint33/Sprint34 historical compatibility preserved for the exact Sprint185 engineering envelope.
- [x] Final engineering head completed 71/71 pull-request workflows successfully.
- [x] Product Owner exact-head merge authority succeeded.
- [x] PR #799 squash merged at `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`.
- [x] Canonical main-push M7.5 run `35382800589` completed successfully.
- [x] Operational NO-GO remains unchanged.
- [x] Exact eight-path canonical reconciliation envelope defined.

Final engineering envelope: 10 paths; SHA-256 `775caa7723278af855b888f2c6bac592d9187e7b988619799f90e2e7a6950e99`.

Canonical reconciliation envelope: 8 paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Preserved lifecycle state

Machine-readable operational state remains authoritative and unchanged: selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; runtime allowlist Local/Test/CI only.

## Sprint185 canonical closure

- [x] Engineering PR qualified and squash merged.
- [x] Final engineering evidence frozen at `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`.
- [x] Canonical main-push M7.5 qualification succeeded.
- [x] Canonical reconciliation limited to Sprint32/Sprint33/Sprint34 preservation workflows + five project-state documents.
- [x] Reconciliation preserves final engineering evidence rather than replacing it with reconciliation squash.
- [x] Operational NO-GO remains unchanged.

## Next engineering position

Begin **Sprint186 bounded discovery** only from fully reconciled Sprint185. Prioritize the next end-to-end installation/onboarding blocker after verified pending configuration, without environment activation, migration execution, updater activation, deployment, durable-target selection, Technical Preview, or Production authority.

Author by Lab | zefry

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint185
**Objective:** `PREBOOT_DATABASE_COMPATIBILITY_VERIFICATION`
**Canonical engineering commit:** `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`
**Engineering PR:** #799 — `Sprint185: verify database compatibility before pending configuration`
**Final engineering head:** `bf17397f739eab4aae531ed6b6a1b0b5430ae98e`
**Exact-head qualification:** 71/71 successful
**Canonical main-push M7.5 qualification:** run `35382800589` — SUCCESS
**Engineering envelope:** 10 paths — `775caa7723278af855b888f2c6bac592d9187e7b988619799f90e2e7a6950e99`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint184 reconciliation `d7401729266df97216ee8c5b04ff502177687461`
**Next position:** Sprint186 bounded discovery from the fully reconciled Sprint185 checkpoint.

> `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01` is the canonical Sprint185 engineering evidence. The reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint185 closes a material installation risk left after Sprint184: a syntactically valid database configuration could previously be accepted into `.env.pending` without proving that the target database was reachable and compatible with oneQay.

## 2. What changed

- Added a pre-boot database compatibility verifier that runs before pending runtime configuration is committed.
- Verifies MySQL/MariaDB connectivity using the submitted installation configuration.
- Verifies server-version shape, `utf8mb4`, UTC session posture, and schema ownership state.
- Classifies schema state as `empty`, `recognized`, or `foreign` and rejects foreign/incompatible targets.
- Verifies that the configured database principal is database-scoped and rejects global/admin-style privileges.
- Preserves the still-valid one-time installation authority when database verification fails so an operator can correct the configuration and retry.
- Commits `.env.pending` only after compatibility succeeds and binds safe verification facts into that pending configuration.
- Exposes `PENDING_CONFIGURATION_VERIFIED` in the pre-boot installer.
- Keeps `ONEQAY_INSTALLATION_ACTIVATION_AUTHORIZED=false`, persistence disabled, Technical Preview disabled, and updater control disabled.
- Packages the verifier into the deterministic governed M7.5 release artifact.
- Preserves M7.5 and Sprint32/Sprint33/Sprint34 historical qualification for the exact Sprint185 engineering envelope only; migration, authentication, and recovery application source semantics remain unchanged.

## 3. Evidence / Qualification

- Canonical parent before Sprint185 engineering: `d7401729266df97216ee8c5b04ff502177687461`.
- Final engineering head `bf17397f739eab4aae531ed6b6a1b0b5430ae98e` completed 71/71 pull-request workflows successfully.
- Dedicated Sprint185 regression started an actual MySQL 8 service, created a database-scoped least-privilege installation principal, proved successful live compatibility verification, proved invalid credentials fail closed, preserved Sprint184 semantics, and confirmed no secret/exception leakage.
- Governance, M7.5 release, Sprint32/Sprint33/Sprint34, Sprint172 installation database readiness, POS workspace, and Final Shift Close preservation regressions succeeded on the exact engineering head.
- Repository-native Product Owner merge authority succeeded on the exact qualified head.
- PR #799 squash merged at `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`.
- Canonical main-push M7.5 run `35382800589` completed successfully, including deterministic release packaging, installer-readiness sidecar materialization, manifest/checksum/no-schema-change verification, deterministic archive reproduction, artifact upload, and tracked-source cleanliness.
- Final engineering envelope: exactly 10 paths; SHA-256 `775caa7723278af855b888f2c6bac592d9187e7b988619799f90e2e7a6950e99`.
- Canonical reconciliation envelope: exactly 8 paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## 4. Operational boundaries / NO-GO

Machine-readable operational authority under `ops/final-shift-close/` remains authoritative and unchanged:

- durable activation target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- producer dispatch: `NOT_PERFORMED`;
- runtime allowlist: Local/Test/CI only;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

Sprint185 verifies database compatibility and prepares verified pending configuration only. It does not create active `.env`, mutate schema/business rows during verification, execute migrations, activate persistence, provision production permissions, select a durable target, deploy, activate the updater, or activate Technical Preview/Production.

## 5. Next position

Begin Sprint186 bounded discovery from fully reconciled Sprint185. Select the smallest material P0/P1 blocker after verified pending configuration that advances the governed installation/onboarding journey without implicitly granting operational activation.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry

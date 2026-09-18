# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint186
**Objective:** `GOVERNED_ACTIVATION_READINESS_HANDOFF`
**Canonical engineering commit:** `7cb9e59ede2908f44f4f0d7b2d1c855d885bf3ae`
**Engineering PR:** #802 — `Sprint186: seal governed activation readiness handoff`
**Final engineering head:** `3c6453cd54ac0bc907d85ec01d7410d2c48e19fb`
**Exact-head qualification:** 74/74 successful
**Canonical main-push M7.5 qualification:** run `35387074508` — SUCCESS
**Engineering envelope:** 10 paths — `6a948cea5e7d88f95abec930a0681f851df9d04a1eaaa835f4aac3de1e0c8102`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint185 reconciliation `b2955478c0c4c5554a105361255b6f7bb371adb6`
**Next position:** Sprint187 bounded discovery from the fully reconciled Sprint186 checkpoint.

> `7cb9e59ede2908f44f4f0d7b2d1c855d885bf3ae` is the canonical Sprint186 engineering evidence. The reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint186 closes the material handoff gap after verified pending configuration. Sprint185 proved that the database and pending runtime configuration were compatible, but future activation still lacked durable evidence binding those exact pending bytes to the exact governed release.

## 2. What changed

- Added a private pre-boot activation-readiness handoff.
- Binds `.env.pending` to the exact governed release using `ONEQAY_INSTALLATION_RELEASE_ID`.
- Creates private `activation-readiness.json` only after verified pending configuration is successfully committed.
- Binds the handoff to the SHA-256 and byte length of the exact pending environment.
- Carries only safe database evidence: engine, server version, charset, timezone, schema state, and least-privilege status.
- Rejects handoff readiness if the pending configuration is tampered with or belongs to a different governed release.
- Keeps the existing `PENDING_CONFIGURATION_VERIFIED` state while adding explicit `activation_handoff_ready` evidence.
- Updates the professional pre-boot installer UI to show `SEALED / NOT AUTHORIZED`.
- Packages the activation-readiness verifier into the governed M7.5 artifact.
- Declares the private handoff path in `RELEASE.json`.
- Explicitly records activation, migration execution, Technical Preview, Production, and updater authority as false.
- Preserves M7.5 and Sprint32/Sprint33/Sprint34 historical qualification only for the exact Sprint186 envelope; migration, authentication, recovery, and business application semantics remain unchanged.

## 3. Evidence / Qualification

- Canonical parent before Sprint186 engineering: `b2955478c0c4c5554a105361255b6f7bb371adb6`.
- Final engineering head `3c6453cd54ac0bc907d85ec01d7410d2c48e19fb` completed 74/74 pull-request workflows successfully.
- Dedicated Sprint186 regression proved exact-release binding, pending SHA-256 and byte-length binding, private permissions, tamper detection, release mismatch denial, no-secret leakage, preserved Sprint184/Sprint185 behavior, governed artifact packaging, and handoff-before-activation NO-GO.
- Repository-native Product Owner merge authority succeeded on the exact qualified head.
- PR #802 squash merged at `7cb9e59ede2908f44f4f0d7b2d1c855d885bf3ae`.
- Canonical main-push M7.5 run `35387074508` completed successfully, including deterministic artifact reproduction, installer sidecar, manifest/checksum/no-schema-change validation, upload, and source cleanliness.
- Post-merge shared-runtime main-push regressions also succeeded: cPanel shared-runtime run `35387074572`, shared-runtime boundary run `35387074473`, and Sprint155 source-contract run `35387074543`.
- Final engineering envelope: exactly 10 paths; SHA-256 `6a948cea5e7d88f95abec930a0681f851df9d04a1eaaa835f4aac3de1e0c8102`.
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

Sprint186 creates readiness evidence only. It does not create active `.env`, execute migrations, mutate schema/business data, enable persistence, select a durable target, deploy, activate the updater, or activate Technical Preview/Production.

## 5. Next position

Begin Sprint187 bounded discovery from fully reconciled Sprint186. Select the smallest material P0/P1 blocker after sealed activation-readiness handoff that advances the governed installation/onboarding journey without crossing operational activation authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry

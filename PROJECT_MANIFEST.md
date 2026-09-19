# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint188
**Objective:** `GOVERNED_RUNTIME_PROMOTION_QUALIFICATION_FOUNDATION`
**Canonical engineering commit:** `1d8e13871bc86ed51312c8e6dae5651018452f97`
**Engineering PR:** #806 — `Sprint188: qualify governed runtime promotion authority`
**Final engineering head:** `f194edacc2300ac155dd6fb88d0fadf2819f50ae`
**Exact-head qualification:** 76/76 successful
**Canonical main-push M7.5 qualification:** run `35413516260` — SUCCESS
**Engineering envelope:** 10 paths — `cd3306762c9f36c76154a989d8b464ad8c2ec0fbf7833c3e6a0d065afbf064b0`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint187 reconciliation `719f69487f3b587369ea097d0c5b9d0d606d05ba`
**Next position:** Sprint189 bounded discovery from the fully reconciled Sprint188 checkpoint.

> `1d8e13871bc86ed51312c8e6dae5651018452f97` is the canonical Sprint188 engineering evidence. The reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint188 closes the qualification gap after the Sprint187 promotion request. The installation chain already produced a reviewable `PENDING_APPROVAL` request, but lacked a fail-closed source contract for validating a separately provisioned exact-bound authority before any future promotion execution.

## 2. What changed

- Added the public machine-readable runtime configuration promotion authority schema.
- Added a private pre-boot authority qualification service.
- Binds authority to the exact governed release, request ID, pending-environment SHA-256, activation-readiness SHA-256, and promotion-request SHA-256.
- Requires bounded authority lifetime of at most 900 seconds.
- Requires `single_use=true`.
- Requires an out-of-band approval token stored only as SHA-256 in the authority artifact.
- Rejects missing, malformed, expired, mismatched, tampered, or wrong-token authority fail closed.
- Adds a separate operator qualification action and authority status to the pre-boot installer.
- Successful qualification returns only `PROMOTION_QUALIFIED_NOT_EXECUTED`.
- Qualification performs no file writes and does not create active `.env`.
- Packages qualification source and authority schema into the governed M7.5 artifact.
- Preserves M7.5 and Sprint32/Sprint33/Sprint34 historical qualification only for the exact Sprint188 envelope.

## 3. Evidence / Qualification

- Canonical parent before Sprint188 engineering: `719f69487f3b587369ea097d0c5b9d0d606d05ba`.
- Initial Sprint188 head exposed a regression expectation mismatch: a structurally valid but byte-tampered request correctly invalidated exact authority binding as `PROMOTION_AUTHORITY_INVALID`; the test was corrected without changing the ten-path envelope.
- Final engineering head `f194edacc2300ac155dd6fb88d0fadf2819f50ae` completed 76/76 pull-request workflows successfully.
- Dedicated Sprint188 regression proved exact authority binding, bounded lifetime, token verification, fail-closed missing/expired/mismatched/tampered cases, no secret leakage, preserved Sprint184–Sprint187 behavior, governed artifact packaging, and qualification-before-execution NO-GO.
- Repository-native Product Owner merge authority succeeded on the exact qualified head.
- PR #806 squash merged at `1d8e13871bc86ed51312c8e6dae5651018452f97`.
- Canonical main-push M7.5 run `35413516260` completed successfully.
- Post-merge shared-runtime evidence also succeeded: cPanel run `35413516312`, shared-runtime boundary run `35413516293`, and Sprint155 source-contract run `35413516309`.
- Final engineering envelope: exactly 10 paths; SHA-256 `cd3306762c9f36c76154a989d8b464ad8c2ec0fbf7833c3e6a0d065afbf064b0`.
- Canonical reconciliation envelope: exactly 8 paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## 4. Operational boundaries / NO-GO

Machine-readable operational authority under `ops/final-shift-close/` remains authoritative and unchanged:

- durable activation target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

Sprint188 does not provision real promotion authority, persist successful qualification, copy `.env.pending` to `.env`, execute migrations, mutate database/business state, change runtime flags, deploy, activate Technical Preview/Production, activate updater, or select a durable target.

## 5. Next position

Begin Sprint189 bounded discovery from fully reconciled Sprint188. Select the smallest material P0/P1 blocker after exact-bound authority qualification that advances the governed installation/onboarding journey without crossing operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry

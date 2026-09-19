# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint190
**Objective:** `RUNTIME_CONFIGURATION_ATOMIC_PROMOTION_EXECUTOR`
**Canonical engineering commit:** `a7d71df2201e7940082d6e1e469698ec84f1224c`
**Engineering PR:** #811 — `Sprint190: add atomic runtime configuration promotion executor`
**Final engineering head:** `c3e166f14c0de5f038602813407c208f2d526a3c`
**Exact-head qualification:** 78/78 successful
**Canonical main-push M7.5 qualification:** run `35416546056` — SUCCESS
**Engineering envelope:** 9 paths — `3efa46f499d6f2df2d5b64f31eb35630366e43e6b037213297e3457bf0170d7f`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint189 reconciliation `9aba1e79fb08245b1237f2fc60245dffef8be6b9`
**Next position:** Sprint191 bounded discovery from the fully reconciled Sprint190 checkpoint.

> `a7d71df2201e7940082d6e1e469698ec84f1224c` is the canonical Sprint190 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint190 closes the source-capability gap after durable promotion readiness. The repository now contains a complete atomic pending-to-active runtime configuration executor while keeping execution dormant and unregistered.

## Delivered capability

- Added `PrebootRuntimeConfigurationPromotionExecution` as a source-only executor.
- Requires fresh Sprint188 authority qualification and valid Sprint189 durable execution-readiness evidence.
- Binds execution to exact release, request, authority, pending configuration, handoff, readiness, and qualification fingerprint.
- Atomically materializes active `.env` from the exact verified `.env.pending` bytes.
- Verifies active read-back SHA-256 and required fail-closed runtime markers before consuming pending configuration.
- Persists a private 0600 execution receipt with exact source/evidence digests and logical one-time authority/readiness consumption state.
- Rolls back active materialization and restores pending configuration when post-promotion receipt persistence fails.
- Denies execution replay once active `.env` exists.
- Packages the dormant executor source and execution-receipt schema in the governed M7.5 artifact.
- Public installer does not register or expose the executor; release metadata records `promotion_executor_registration_state=NOT_REGISTERED` and `promotion_execution_state=NOT_EXECUTED`.

## Qualification evidence

- Initial Sprint190 head exposed a test-only safety assertion that treated a rejection-string literal as a forbidden execution primitive; product source and prior regressions were unaffected.
- The assertion was corrected in the same PR without changing the nine-path envelope.
- Final head `c3e166f14c0de5f038602813407c208f2d526a3c` completed 78/78 workflows successfully.
- Dedicated Sprint190 regression proved exact-byte promotion semantics, wrong-token/tamper denial, active/read-back verification, private receipt, replay denial, immutable source evidence, no secret leakage, and no public registration.
- PR #811 squash merged at `a7d71df2201e7940082d6e1e469698ec84f1224c`.
- Canonical main-push M7.5 run `35416546056` succeeded.
- Post-merge shared-runtime evidence succeeded: shared-runtime `35416546005`, cPanel `35416546026`, Sprint155 `35416546049`.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`.

Sprint190 adds dormant executor source only. No live runtime promotion was performed and no public execution entrypoint exists.

## Next position

Begin Sprint191 bounded discovery from fully reconciled Sprint190. Select the smallest material P0/P1 blocker after dormant executor completion without crossing operational activation authority implicitly.

Author by Lab | zefry

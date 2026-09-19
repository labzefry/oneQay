# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint195  
**Objective:** `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_READINESS`  
**Canonical engineering commit:** `022b1667ce25a9f4b86a71c95b2c59ad37793d4e`  
**Engineering PR:** #821 — `Sprint195: qualify Technical Preview activation authority readiness`  
**Final engineering head:** `11ec883590d05d28aeeac08a0286a3e149db00e7`  
**Exact-head qualification:** 83/83 successful  
**Dedicated Sprint195 qualification:** run `35425205497` — SUCCESS  
**Exact-head M7.5 qualification:** run `35425205248` — SUCCESS  
**M7.1 qualification:** run `35425205249` — SUCCESS  
**Governance qualification:** run `35425205459` — SUCCESS  
**PHP Foundation qualification:** run `35425205135` — SUCCESS  
**cPanel qualification:** run `35425205076` — SUCCESS  
**Shared-runtime qualification:** run `35425205240` — SUCCESS  
**Engineering envelope:** 11 paths — `b6b06d99b300fa67cc6341f098eff73c430b11c6d98444ebb75a352d3e7589c2`  
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`  
**Previous canonical checkpoint:** Sprint194 reconciliation `e58d2450aa38df979d639603b7adaf421a6aa492`  
**Next position:** Sprint196 bounded discovery after canonical Sprint195 reconciliation.

> `022b1667ce25a9f4b86a71c95b2c59ad37793d4e` is the canonical Sprint195 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint195 closes the authority/readiness gap after Sprint194 created the guarded Technical Preview activation request. The repository can now qualify a separately provisioned operational authority and seal exact-bound execution-readiness evidence while retaining a hard stop before target-environment preflight and Technical Preview activation execution.

## Delivered capability

- Added `PrebootTechnicalPreviewActivationAuthorityReadiness`.
- Requires a valid Sprint194 activation request bound to valid Sprint193 installation completion and the exact active runtime environment.
- Validates authority ID, exact request/release/evidence digests, one-time approval token, single-use semantics, and a maximum 900-second authority lifetime.
- Authority scope is `ENABLE_SYNTHETIC_TECHNICAL_PREVIEW_RUNTIME`.
- Persists private 0600 readiness evidence atomically.
- Readiness state is `TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED`.
- Exact replay is idempotent while bound evidence remains unchanged.
- Wrong token, authority/request/evidence tamper, expiry, unsafe boundary, or wrong release fails closed.
- Readiness preserves mandatory target preflight for HTTPS, single instance, private file sessions, runtime-envelope revalidation, Preview off-switch, post-activation health checks, rollback/recovery, synthetic-only data, and no migration execution.
- Installer exposes guarded `qualify_technical_preview_activation_authority` with exact confirmation `QUALIFY_TECHNICAL_PREVIEW_ACTIVATION`.
- Operator UI reports `QUALIFIED / READY / NOT ACTIVATED`.
- M7.5 packages the authority/readiness contracts while build-time Technical Preview activation remains false.
- No Sprint195 source path performs target-host preflight or activation execution.

## Qualification evidence

- Final head `11ec883590d05d28aeeac08a0286a3e149db00e7` completed 83/83 workflows successfully.
- Dedicated Sprint195 `35425205497`, M7.5 `35425205248`, M7.1 `35425205249`, Governance `35425205459`, PHP Foundation `35425205135`, cPanel `35425205076`, and shared-runtime `35425205240` succeeded.
- Engineering PR #821 squash merged at `022b1667ce25a9f4b86a71c95b2c59ad37793d4e`.
- Post-merge verification confirmed the previous canonical `e58d2450aa38df979d639603b7adaf421a6aa492` to engineering squash delta is exactly the frozen 11-path envelope.
- Operational NO-GO remained unchanged after engineering merge.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`.

Sprint195 qualifies separately provisioned authority only as a source/runtime contract. It does not provision live authority, perform target-host preflight, execute Technical Preview activation, execute migrations, provision permissions, activate Final Shift Close, enable persistence/updater, activate Production, grant deployment authority, select a durable target, or dispatch a producer.

## Next position

Begin Sprint196 business-first bounded discovery from fully reconciled Sprint195. Prioritize the smallest material blocker after activation execution-readiness, especially the mandatory target-environment preflight / guarded activation boundary if live repository evidence confirms it is still missing. Do not pre-authorize Technical Preview activation.

Author by Lab | zefry

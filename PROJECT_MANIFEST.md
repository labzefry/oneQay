# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint194
**Objective:** `GUARDED_TECHNICAL_PREVIEW_ACTIVATION_REQUEST`
**Canonical engineering commit:** `7e3e58d7be012ee5d797acb879cfb9f9a1e829dc`
**Engineering PR:** #819 — `Sprint194: add guarded Technical Preview activation request`
**Final engineering head:** `af1027156209977a75d24f54fec031f86bedf8f6`
**Exact-head qualification:** 82/82 successful
**Dedicated Sprint194 qualification:** run `35423991024` — SUCCESS
**Exact-head M7.5 qualification:** run `35423990554` — SUCCESS
**Canonical main-push M7.5 qualification:** run `35424129329` — SUCCESS
**Engineering envelope:** 10 paths — `a85b8ceffbf533ea5f3aff7555dba90129c2c851bb8d9bb8ed427ce2e572f804`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint193 reconciliation `cdf8168c659a69887efeb3b798a9231903a0a3b0`
**Next position:** Sprint195 bounded discovery from the fully reconciled Sprint194 checkpoint.

> `7e3e58d7be012ee5d797acb879cfb9f9a1e829dc` is the canonical Sprint194 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint194 closes the approval-request gap after Sprint193 sealed installation completion. The installation journey can now materialize an exact-bound request for separate Technical Preview operational authority without activating Technical Preview, persistence, migrations, deployment, updater, or Production.

## Delivered capability

- Added `PrebootTechnicalPreviewActivationRequest`.
- Request eligibility requires valid `INSTALLATION_CONFIGURATION_COMPLETE_NOT_ACTIVATED` evidence.
- Binds exact release ID, active environment SHA-256, and installation-completion SHA-256.
- Persists private 0600 request evidence atomically.
- Uses deterministic request identity and records `created_at_unix` for audit evidence.
- Exact replay is idempotent while all bound evidence remains unchanged.
- Active configuration, completion, or request tamper fails closed.
- Required authority remains `NOT_GRANTED` and separate operational authority remains mandatory.
- Installer exposes guarded `create_technical_preview_activation_request` with exact confirmation `REQUEST_TECHNICAL_PREVIEW_ACTIVATION`.
- Operator UI reports `PENDING APPROVAL / NOT AUTHORIZED`.
- Governed M7.5 release packages request source/schema and records request state as `NOT_CREATED_AT_BUILD`.
- No request operation mutates the active runtime environment or grants activation authority.

## Qualification evidence

- Final head `af1027156209977a75d24f54fec031f86bedf8f6` completed 82/82 workflows successfully.
- Dedicated Sprint194 run `35423991024`, exact-head M7.5 `35423990554`, M7.1 `35423990528`, Governance `35423990342`, PHP Foundation `35423990506`, cPanel `35423990515`, and shared-runtime `35423990587` succeeded.
- Engineering PR #819 squash merged at `7e3e58d7be012ee5d797acb879cfb9f9a1e829dc`.
- Canonical main-push M7.5 run `35424129329` succeeded.
- Post-merge shared-runtime `35424129284`, cPanel `35424129292`, and Sprint155 `35424129446` succeeded.
- Historical push-only startup failures for M7.4/Sprint35–Sprint39 produced zero jobs and did not indicate a Sprint194 source regression.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`.

Sprint194 creates an approval request only. It does not execute migrations, provision permissions, activate Final Shift Close, activate Technical Preview/Production, enable persistence/updater, grant deployment authority, select a durable target, or dispatch a producer.

## Next position

Begin Sprint195 bounded discovery from fully reconciled Sprint194 and select the next smallest material P0/P1 blocker toward safe first application operation while preserving separate operational authority.

Author by Lab | zefry

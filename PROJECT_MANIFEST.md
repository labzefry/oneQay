# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint196
**Objective:** `TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT`
**Canonical engineering commit:** `9948aeadc562b6188453872f09bd3afb754dd0c0`
**Engineering PR:** #823 — `Sprint196: qualify Technical Preview target environment preflight`
**Final engineering head:** `afc443420ddef9283c7f575ce311b97fc026e658`
**Exact-head qualification:** 84/84 successful
**Dedicated Sprint196 qualification:** run `35426685626` — SUCCESS
**Exact-head M7.5 qualification:** run `35426685578` — SUCCESS
**M7.1 qualification:** run `35426686129` — SUCCESS
**Governance qualification:** run `35426686140` — SUCCESS
**PHP Foundation qualification:** run `35426685380` — SUCCESS
**cPanel qualification:** run `35426685589` — SUCCESS
**Shared-runtime qualification:** run `35426685604` — SUCCESS
**Engineering envelope:** 14 paths — `bb875f82e7cfc7a8347bf6d92f916f6124eb5b095e27f9582189ebec5c74e904`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint195 reconciliation `27bcd7fbd9c13a88a28544e3fd26e4dfe46354c0`
**Next position:** Sprint197 business-first bounded discovery after canonical Sprint196 reconciliation.

> `9948aeadc562b6188453872f09bd3afb754dd0c0` is the canonical Sprint196 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint196 closes the mandatory target-environment-preflight gap after Sprint195 sealed Technical Preview activation execution-readiness. It also corrects the prepared deployed Preview session envelope so later target qualification matches the runtime policy rather than qualifying an incomplete configuration.

## Delivered capability

- Installer prepares runtime class `preview` while `ONEQAY_TECHNICAL_PREVIEW_ENABLED=false`.
- Adds explicit `single` instance mode, `synthetic` data class, Production-data prohibition, and persistence disabled.
- Creates a private persistent `shared/runtime/sessions` directory and supports `SESSION_FILES` through Laravel session configuration.
- Prepares file sessions with 60-minute lifetime, encryption, Secure cookie, host-only/Lax runtime policy compatibility, and dedicated `oneqay-preview-session`.
- Added `PrebootTechnicalPreviewTargetEnvironmentPreflight`.
- Target preflight requires valid Sprint195 execution-readiness and unexpired authority.
- Validates HTTPS/TLS, exact configured host, single-instance target, private writable session directory outside public root, exact runtime envelope, Preview off-switch, synthetic/no-schema-change release metadata, absence of stale config cache, Preview route off-switch contract, health contract, rollback/recovery contract, and Production-data prohibition.
- Stores only hashed host/session identities and private non-secret evidence.
- Exact replay is idempotent; HTTP, host mismatch, unsafe session permissions, stale cache, expired authority, tamper, and evidence drift fail closed.
- Installer exposes guarded `run_technical_preview_target_preflight` with exact confirmation `RUN_TECHNICAL_PREVIEW_TARGET_PREFLIGHT`.
- Operator state is `PREFLIGHT PASSED / NOT ACTIVATED`.
- No Sprint196 path executes activation or post-activation health checks.

## Qualification evidence

- Final head `afc443420ddef9283c7f575ce311b97fc026e658` completed 84/84 workflows successfully.
- Dedicated Sprint196 `35426685626`, M7.5 `35426685578`, M7.1 `35426686129`, Governance `35426686140`, PHP Foundation `35426685380`, cPanel `35426685589`, and shared-runtime `35426685604` succeeded.
- Engineering PR #823 squash merged at `9948aeadc562b6188453872f09bd3afb754dd0c0`.
- Post-merge verification confirmed the previous canonical `27bcd7fbd9c13a88a28544e3fd26e4dfe46354c0` to engineering squash delta is exactly the frozen 14-path envelope.
- Operational NO-GO remained unchanged after engineering merge.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`.

Sprint196 performs target qualification only. It does not activate Technical Preview, consume activation authority/readiness, execute post-activation health, execute migrations, provision permissions, enable business persistence/updater, activate Production, grant deployment authority, select the durable Final Shift Close target, or dispatch a producer.

## Next position

Begin Sprint197 business-first bounded discovery from fully reconciled Sprint196. Verify the smallest material blocker after a successful target preflight before implementing any guarded activation executor. Any future activation must preserve atomic fail-closed behavior and mandatory post-activation health/rollback, and remains separately governed.

Author by Lab | zefry

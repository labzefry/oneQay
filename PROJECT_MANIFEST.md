# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint192
**Objective:** `RUNTIME_CONFIGURATION_POST_PROMOTION_VERIFICATION`
**Canonical engineering commit:** `2baf1c7de2c269688effa5a2fa7f6f7ce60d3940`
**Engineering PR:** #815 — `Sprint192: verify promoted runtime configuration`
**Final engineering head:** `b48057d634bd9e8915358f25050fa395494adffb`
**Exact-head qualification:** 80/80 successful
**Canonical main-push M7.5 qualification:** run `35419043608` — SUCCESS
**Engineering envelope:** 10 paths — `d4cf1e82d1a3abd9cc23fd222ebb7232483b35460b9af5bba30167086ff4def5`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint191 reconciliation `d78d7ebee9a591ebeed9d816986c5aea53bea18f`
**Next position:** Sprint193 bounded discovery from the fully reconciled Sprint192 checkpoint.

> `2baf1c7de2c269688effa5a2fa7f6f7ce60d3940` is the canonical Sprint192 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint192 closes the post-promotion verification gap after Sprint191 delivered a guarded operator promotion flow. The installation path can now prove that the active runtime configuration is still exact-bound to the governed execution receipt before any later application activation step.

## Delivered capability

- Added `PrebootRuntimeConfigurationPostPromotionVerification`.
- Requires private active `.env`, absent `.env.pending`, and a valid private promotion execution receipt.
- Binds exact release, request ID, authority ID, execution receipt SHA-256, and active environment SHA-256.
- Re-validates fail-closed runtime markers including Technical Preview, persistence, updater/control-plane, and installation activation flags.
- Persists private 0600 evidence with state `RUNTIME_CONFIGURATION_VERIFIED_NOT_ACTIVATED`.
- Exact replay is idempotent only while active configuration and execution receipt remain unchanged.
- Active-environment tamper, execution-receipt tamper, wrong release, symlink, or unsafe permissions fail closed.
- Successful operator promotion immediately seals post-promotion verification evidence.
- If evidence sealing needs retry, installer exposes explicit `VERIFY_RUNTIME_CONFIGURATION` confirmation without re-running promotion.
- Operator UI reports `VERIFIED / NOT ACTIVATED`.
- Governed M7.5 release packages verifier source/schema and records build-time verification as `NOT_PERFORMED_AT_BUILD`.

## Qualification evidence

- Initial Sprint192 head exposed a historical Sprint191 UI-literal compatibility failure only; product verification source was unaffected.
- The promoted-state label `PROMOTED / NOT ACTIVATED` was restored in the same installer path without changing the ten-path envelope.
- Final head `b48057d634bd9e8915358f25050fa395494adffb` completed 80/80 workflows successfully.
- Dedicated Sprint192 regression proved exact binding, private evidence, tamper/wrong-release denial, idempotency, secret non-disclosure, verified-not-activated UI, governed artifact packaging, and preserved Sprint184-Sprint191 semantics.
- PR #815 squash merged at `2baf1c7de2c269688effa5a2fa7f6f7ce60d3940`.
- Canonical main-push M7.5 run `35419043608` succeeded.
- Post-merge cPanel `35419043582` and Sprint155 `35419043581` succeeded.
- Shared-runtime run `35419043673` initially hit an external Packagist security-advisory HTTP/2 502; failed job rerun attempt 2 succeeded without source changes.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`.

Sprint192 verifies promoted configuration only. It does not execute migrations, activate Technical Preview/Production, enable persistence/updater, grant deployment authority, or select a durable target.

## Next position

Begin Sprint193 bounded discovery from fully reconciled Sprint192. Select the next material installation/onboarding blocker after verified active configuration without crossing operational activation authority implicitly.

Author by Lab | zefry

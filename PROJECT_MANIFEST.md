# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint197
**Objective:** `TECHNICAL_PREVIEW_ATOMIC_ACTIVATION_HEALTH_ROLLBACK`
**Canonical engineering commit:** `0c74e535cfeb281edaff5a2967752baee0db5227`
**Engineering PR:** #825 — `Sprint197: add atomic Technical Preview activation health rollback`
**Final engineering head:** `027c84bb282aefd314d8da3d270c925ba5837841`
**Exact-head qualification:** 85/85 successful
**Dedicated Sprint197 qualification:** run `35428627303` — SUCCESS
**Exact-head M7.5 qualification:** run `35428627419` — SUCCESS
**M7.1 qualification:** run `35428627367` — SUCCESS
**Governance qualification:** run `35428627552` — SUCCESS
**PHP Foundation qualification:** run `35428627503` — SUCCESS
**cPanel qualification:** run `35428627529` — SUCCESS
**Shared-runtime qualification:** run `35428627836` — SUCCESS
**Engineering envelope:** 11 paths — `08a73cc8338a51da3ed294b1a6c6a62986e0527100213414036d55b839b10a44`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint196 reconciliation `fafc5692697f2aa4ef2b23baff2adff52b205feb`
**Next position:** Sprint198 business-first bounded discovery after canonical Sprint197 reconciliation.

> `0c74e535cfeb281edaff5a2967752baee0db5227` is the canonical Sprint197 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint197 closes the next material Technical Preview readiness gap after Sprint196: a governed, atomic activation executor with bounded post-activation health qualification and automatic fail-closed rollback. This is repository source capability only; merging it did not activate a target host.

## Delivered capability

- Added `PrebootTechnicalPreviewActivationExecution`.
- Requires a valid Sprint196 target preflight, exact release/request/authority/readiness bindings, unexpired authority, and the separately provisioned approval token.
- Mutates only the exact disabled Preview runtime flag to enabled using an atomic environment-file replacement.
- Performs bounded in-process HTTPS checks for liveness, readiness, and the Preview surface on the exact configured host.
- Revalidates runtime policy and the private encrypted Secure file-session contract.
- On any failed post-activation check or activation-commit error, restores the original environment byte-for-byte, verifies rollback, removes partial success evidence, and stores private recovery evidence.
- On success, stores a private receipt bound to the exact evidence digests and records conceptual authority/readiness/preflight consumption.
- Rejects wrong tokens, replay, tampered receipts/evidence, expired authority, invalid runtime envelopes, and unsafe lifecycle drift.
- Packages the executor and success/recovery schemas into the governed M7.5 Technical Preview artifact.
- Installer exposes `execute_technical_preview_activation` with exact confirmation `ACTIVATE_TECHNICAL_PREVIEW`.
- Canonical repository NO-GO display remains distinct from a future host-local `ACTIVE / HEALTHY` execution receipt.

## Qualification evidence

- Final head `027c84bb282aefd314d8da3d270c925ba5837841` completed 85/85 PR-triggered workflows successfully.
- Dedicated Sprint197, M7.5, M7.1, Governance, PHP Foundation, cPanel, shared-runtime, and historical preservation workflows succeeded.
- Engineering PR #825 squash merged at `0c74e535cfeb281edaff5a2967752baee0db5227`.
- Post-merge verification confirmed exactly one squash commit above `fafc5692697f2aa4ef2b23baff2adff52b205feb` with the frozen 11-path engineering envelope.
- Operational NO-GO remained unchanged after engineering merge.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; canonical Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch not performed.

Sprint197 adds a guarded activation mechanism but does not itself grant live Technical Preview authority. No repository merge, reconciliation, test, or build step activates a host.

## Next position

Begin Sprint198 from fully reconciled Sprint197. Select the smallest material P0/P1 business-completion blocker that remains after the guarded Technical Preview activation path is source-complete. Avoid splitting work into anti-granular lifecycle micro-sprints, and do not broaden operational authority without a separate explicit decision.

Author by Lab | zefry

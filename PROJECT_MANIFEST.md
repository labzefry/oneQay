# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint203
**Objective:** `DURABLE_STAGING_MERCHANT_CORE_BOUNDED_BRIDGE`
**Canonical engineering commit:** `088dc0a38d80abc776cacc7052ddb3dbfdcf0c70`
**Engineering PR:** #839 — `Sprint203: add bounded durable staging merchant core bridge`
**Final engineering head:** `a1a2eb0a3ff63edabe1c9ab06a5f8494bbd9d963`
**Exact-head qualification:** 88/88 successful
**Sprint201 bounded staging qualification:** run `35448422360` — SUCCESS
**M7.5 Technical Preview Release Artifact:** run `35448421896` — SUCCESS
**Sprint32 authentication recovery:** run `35448421898` — SUCCESS
**Sprint33 recovery-bound password reset:** run `35448421945` — SUCCESS
**Sprint34 authenticated password change:** run `35448421814` — SUCCESS
**Sprint202 authoritative receipt preservation:** run `35448422502` — SUCCESS
**Sprint200 guided operations preservation:** run `35448422030` — SUCCESS
**M7.1 qualification:** run `35448422369` — SUCCESS
**Governance qualification:** run `35448421657` — SUCCESS
**PHP Foundation qualification:** run `35448421843` — SUCCESS
**Product Owner merge authority:** run `35448835144` — SUCCESS
**Engineering envelope:** 12 paths — `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint202 reconciliation `942af6be0a3a42d821235b32a0d5670292768f04`
**Next position:** Sprint204 durable non-synthetic staging target qualification/readiness discovery after canonical Sprint203 reconciliation.

> `088dc0a38d80abc776cacc7052ddb3dbfdcf0c70` is the canonical Sprint203 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint203 establishes the smallest maintainable source bridge needed to run the already-qualified merchant core against a separately governed non-production staging runtime without widening historical repository runtime guards or silently authorizing Production.

## Delivered capability

- External runtime `staging` is eligible only when `ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED=true`.
- Legacy durable repositories retain their historical Local/Test/CI guards unchanged.
- The compatibility runtime is projected to `ci` only inside the exact allowed merchant-core HTTP request or staging bootstrap command, then restored.
- A staging-only wrapper delegates merchant bootstrap to the existing preauthorized, hidden-password, atomic bootstrap authority.
- The staged merchant-core surface covers sign-in/session, existing password/MFA/recovery controls when enabled, POS Operations Hub, Catalog & Opening Stock, Shift Start/opening cash, Cashier, and durable sale completion.
- Sale void, refund, closing-cash mutation, Final Shift Close, deployment, updater, Technical Preview activation, Production activation, target selection, and producer dispatch are not bridged.
- Broader rejected designs PR #837 and PR #838 were closed superseded before merge; they are not canonical source.

## Qualification evidence

- Final head `a1a2eb0a3ff63edabe1c9ab06a5f8494bbd9d963` completed 88/88 PR-triggered workflows successfully.
- Exact bounded compatibility qualification and the wider regression matrix were green.
- Product Owner merge authority resolved SUCCESS before guarded squash merge.
- Engineering PR #839 squash merged at `088dc0a38d80abc776cacc7052ddb3dbfdcf0c70`.
- Post-merge verification confirmed exactly one squash commit above `942af6be0a3a42d821235b32a0d5670292768f04` with the frozen 12-path engineering envelope.
- No external staging/production target was selected or mutated.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch not performed.

Sprint203 creates source eligibility only. It does not constitute staging deployment, durable-target qualification, Technical Preview activation, Production activation, or permission to execute migration #27.

## Next position

Sprint204 should focus on the next material production-readiness blocker: a deterministic, read-only qualification/readiness contract for a real non-synthetic durable staging target that can consume the Sprint203 merchant-core bridge. The result should reduce the blocker `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET` without selecting, deploying, mutating, or activating any target unless separate operational authority is explicitly granted.

Author by Lab | zefry

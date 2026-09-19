# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint199
**Objective:** `MERCHANT_ACCOUNT_SECURITY_SELF_SERVICE_WORKSPACE`
**Canonical engineering commit:** `f2692018b261a723b9b360efe650969926adb2d2`
**Engineering PR:** #829 — `Sprint199: add merchant account security self-service`
**Final engineering head:** `b7be9bc6c268aa6a332c0e709e417c6384d08800`
**Exact-head qualification:** 85/85 successful
**Dedicated Sprint199 qualification:** run `35438535934` — SUCCESS
**M7.5 Technical Preview Release Artifact:** run `35438535597` — SUCCESS
**Sprint32 authentication recovery:** run `35438535499` — SUCCESS
**Sprint33 recovery-bound password reset:** run `35438535463` — SUCCESS
**Sprint34 authenticated password change:** run `35438535446` — SUCCESS
**M7.1 qualification:** run `35438535440` — SUCCESS
**Governance qualification:** run `35438535437` — SUCCESS
**PHP Foundation qualification:** run `35438535925` — SUCCESS
**Sprint162 POS Operations Hub:** run `35438535268` — SUCCESS
**Sprint180 Merchant Initial Context Assisted Sign-In:** run `35438536093` — SUCCESS
**Product Owner merge authority:** run `35439039225` — SUCCESS
**Engineering envelope:** 10 paths — `2aba38a7f80dcc6178ce39f865789b4e20b26f2d3ebb96b1d307af0c628e77e4`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint198 reconciliation `5d3777025b149693ba1c3784659397522401cdf7`
**Next position:** Sprint200 business-first bounded discovery after canonical Sprint199 reconciliation.

> `f2692018b261a723b9b360efe650969926adb2d2` is the canonical Sprint199 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint199 closes a material merchant usability and security gap after Sprint198 integrated the guarded POS business workspace surface. Existing first-party password, recovery-code, MFA-recovery, and logout authority is now exposed through a coherent merchant self-service experience without creating a parallel authentication system or widening permissions.

## Delivered capability

- Added Account & Security controls to the POS Operations Hub using existing authenticated password-change, recovery-code, privileged TOTP recovery, and logout endpoints.
- Password change requires a fresh sign-in after success.
- Password recovery-code rotation displays newly issued codes only in response state.
- Authenticator recovery-code rotation requires the existing password plus current MFA proof.
- Foundation sign-in now supports password recovery and lost-authenticator replacement through existing restricted recovery-session flows.
- Multi-step recovery refreshes XSRF from the current cookie after session regeneration.
- Security capabilities remain server-derived from registered routes and governed configuration.
- Recovery material is not persisted to localStorage or sessionStorage.
- No schema change, permission grant, authentication-engine replacement, migration execution, live deployment, Preview activation, Production activation, updater activation, or durable-target selection was introduced.

## Qualification evidence

- Final head `b7be9bc6c268aa6a332c0e709e417c6384d08800` completed 85/85 PR-triggered workflows successfully.
- Dedicated Sprint199, M7.5 release, Sprint32, Sprint33, Sprint34, M7.1, Governance, PHP Foundation, Sprint162, Sprint180, Sprint198 preservation, and historical compatibility workflows succeeded.
- Product Owner merge authority resolved SUCCESS before the guarded squash merge.
- Engineering PR #829 squash merged at `f2692018b261a723b9b360efe650969926adb2d2`.
- Post-merge verification confirmed exactly one squash commit above `5d3777025b149693ba1c3784659397522401cdf7` with the frozen 10-path engineering envelope.
- Operational NO-GO remained unchanged after engineering merge.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; canonical Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch not performed.

Sprint199 delivers merchant account-security self-service only. It does not grant or imply operational activation authority.

## Next position

Begin Sprint200 from fully reconciled Sprint199. Select the smallest material P0/P1 business-completion blocker that advances end-to-end merchant usability and eventual authorized Technical Preview/Production readiness. Prefer a coherent bounded business slice over lifecycle-only micro-splitting, preserve tenant isolation and deny-by-default behavior, and keep all canonical operational boundaries intact.

Author by Lab | zefry

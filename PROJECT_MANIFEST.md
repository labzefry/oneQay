# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint201
**Objective:** `MERCHANT_POS_STATE_AWARE_GUIDED_OPERATIONS`
**Canonical engineering commit:** `cec54de9ff3d056f5981165616584c343b0152c2`
**Engineering PR:** #833 — `Sprint201: add state-aware guided merchant POS operations`
**Final engineering head:** `d5a1fb81ed725052cd89f98a72c0eefeba93a946`
**Exact-head qualification:** 87/87 successful
**Dedicated Sprint201 qualification:** run `35442031183` — SUCCESS
**Sprint200 guided-home preservation:** run `35442031497` — SUCCESS
**Sprint199 account-security preservation:** run `35442031296` — SUCCESS
**M7.5 Technical Preview Release Artifact:** run `35442031110` — SUCCESS
**Sprint32 authentication recovery:** run `35442030463` — SUCCESS
**Sprint33 recovery-bound password reset:** run `35442030778` — SUCCESS
**Sprint34 authenticated password change:** run `35442030406` — SUCCESS
**M7.1 qualification:** run `35442030479` — SUCCESS
**Governance qualification:** run `35442030465` — SUCCESS
**PHP Foundation qualification:** run `35442030320` — SUCCESS
**Sprint162 POS Operations Hub:** run `35442030524` — SUCCESS
**Product Owner merge authority:** run `35442955614` — SUCCESS
**Engineering envelope:** 12 paths — `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint200 reconciliation `ed05e730b5bf65d394a0c1550fdc34ec399db773`
**Next position:** Sprint202 business-first bounded discovery after canonical Sprint201 reconciliation.

> `cec54de9ff3d056f5981165616584c343b0152c2` is the canonical Sprint201 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint201 makes the merchant POS landing experience operationally aware without creating a second source of business truth. Guidance is derived from existing authorized read models and is withheld whenever the evidence cannot be verified safely.

## Delivered capability

- Uses Catalog & Opening Stock state to identify setup requirements.
- Uses exact-device Shift Start state and opening-cash evidence to identify shift readiness.
- Uses Cashier state to verify sellable inventory and the active shift.
- Recommends Cashier only after the relevant readiness evidence is verified.
- Uses Sales Summary as a read-only review fallback when appropriate.
- Recommends only destinations already delivered to the current context.
- Preserves the authorized workspace list when state-aware guidance is unavailable.
- Keeps all destination-level authorization, prerequisite, persistence, and mutation gates authoritative.
- Introduces no new route, permission, role, schema, migration, persistence authority, bootstrap authority, or operational activation authority.

## Qualification evidence

- Final head `d5a1fb81ed725052cd89f98a72c0eefeba93a946` completed 87/87 PR-triggered workflows successfully.
- Dedicated Sprint201, Sprint200 preservation, Sprint199 preservation, M7.5 release, Sprint32, Sprint33, Sprint34, M7.1, Governance, PHP Foundation, Sprint162, and historical compatibility workflows succeeded.
- Product Owner merge authority resolved SUCCESS before the guarded squash merge.
- Engineering PR #833 squash merged at `cec54de9ff3d056f5981165616584c343b0152c2`.
- Post-merge verification confirmed exactly one squash commit above `ed05e730b5bf65d394a0c1550fdc34ec399db773` with the frozen 12-path engineering envelope.
- Operational NO-GO remained unchanged after engineering merge.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; canonical Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch not performed.

Sprint201 changes merchant guidance and presentation only. It does not grant or imply operational activation authority.

## Next position

Begin Sprint202 from fully reconciled Sprint201. Select the smallest material P0/P1 business-completion blocker that advances end-to-end merchant usability and eventual authorized Technical Preview/Production readiness. Prefer a coherent bounded business slice over lifecycle-only micro-splitting, preserve tenant isolation and deny-by-default behavior, and keep all canonical operational boundaries intact.

Author by Lab | zefry

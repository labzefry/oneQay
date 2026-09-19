# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint200
**Objective:** `MERCHANT_POS_GUIDED_OPERATIONS_HOME`
**Canonical engineering commit:** `9c9c211416d216a396880d6e439e6d13c1438b73`
**Engineering PR:** #831 — `Sprint200: add guided merchant POS operations home`
**Final engineering head:** `cf9d49063d9040b83729a48eaa298e5f67f98a45`
**Exact-head qualification:** 86/86 successful
**Dedicated Sprint200 qualification:** run `35439890798` — SUCCESS
**M7.5 Technical Preview Release Artifact:** run `35439890767` — SUCCESS
**Sprint32 authentication recovery:** run `35439891065` — SUCCESS
**Sprint33 recovery-bound password reset:** run `35439890470` — SUCCESS
**Sprint34 authenticated password change:** run `35439890729` — SUCCESS
**M7.1 qualification:** run `35439890803` — SUCCESS
**Governance qualification:** run `35439890548` — SUCCESS
**PHP Foundation qualification:** run `35439890784` — SUCCESS
**Sprint162 POS Operations Hub:** run `35439891289` — SUCCESS
**Sprint199 merchant account-security preservation:** run `35439890492` — SUCCESS
**Product Owner merge authority:** run `35440079418` — SUCCESS
**Engineering envelope:** 7 paths — `c8c07a41fba8ae22eabde78ceaeeed8ae88d0a5906bcc1429348d384a061682f`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint199 reconciliation `f62c6d123976199e11caa6bedc209d3b5a745fc2`
**Next position:** Sprint201 business-first bounded discovery after canonical Sprint200 reconciliation.

> `9c9c211416d216a396880d6e439e6d13c1438b73` is the canonical Sprint200 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint200 improves the first merchant-facing operational experience after secure sign-in. The POS Operations Hub now behaves as a guided enterprise operations home instead of exposing an undifferentiated destination grid and raw internal context identifiers as the primary interaction model.

## Delivered capability

- Added a compact command summary for delivered workspaces, business lanes, and active outlet context.
- Moved complete tenant, organization, outlet, and device identifiers behind explicit technical-context disclosure.
- Added a suggested starting workspace derived only from the already server-delivered destination list.
- Grouped delivered workspaces into setup, shift, sell, stock, review, and control lanes.
- Preserved Sprint199 Account & Security self-service.
- Kept guidance advisory-only: it does not infer setup completion, transaction state, mutation eligibility, or authorization.
- Preserved each destination's independent authorization, prerequisite, persistence, and mutation controls.
- Introduced no new route, permission, role, schema, persistence authority, bootstrap authority, or operational activation authority.

## Qualification evidence

- Final head `cf9d49063d9040b83729a48eaa298e5f67f98a45` completed 86/86 PR-triggered workflows successfully.
- Dedicated Sprint200, M7.5 release, Sprint32, Sprint33, Sprint34, M7.1, Governance, PHP Foundation, Sprint162, Sprint199 preservation, and historical compatibility workflows succeeded.
- Product Owner merge authority resolved SUCCESS before the guarded squash merge.
- Engineering PR #831 squash merged at `9c9c211416d216a396880d6e439e6d13c1438b73`.
- Post-merge verification confirmed exactly one squash commit above `f62c6d123976199e11caa6bedc209d3b5a745fc2` with the frozen 7-path engineering envelope.
- Operational NO-GO remained unchanged after engineering merge.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; canonical Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch not performed.

Sprint200 changes merchant navigation and information architecture only. It does not grant or imply operational activation authority.

## Next position

Begin Sprint201 from fully reconciled Sprint200. Select the smallest material P0/P1 business-completion blocker that advances end-to-end merchant usability and eventual authorized Technical Preview/Production readiness. Prefer a coherent bounded business slice over lifecycle-only micro-splitting, preserve tenant isolation and deny-by-default behavior, and keep all canonical operational boundaries intact.

Author by Lab | zefry

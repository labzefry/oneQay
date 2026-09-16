# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint176 — Merchant Context Atomic Bootstrap Foundation**.

- Canonical engineering commit: `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`
- Engineering PR: #777
- Final engineering head: `775343389659754d85f870eca55f808a0b28eea5`
- Sprint176 regression `35043179125`: successful
- Governance `35043179113`: successful
- PHP Foundation `35043179183`: successful
- M7.1 `35043178985`: successful
- Engineering envelope: 8 paths, SHA-256 `f1b48efc2a25623ae55c72b95407a19ef60b63f8dcb933f9d7f137f09a34b974`
- Reconciliation envelope: 6 paths, SHA-256 `4cc815fdb1c6489ab34334a14033acfe1452b50874f48c9e867e6f6da3858b03`
- Post-engineering compatibility correction: PR #779, squash `f745348e130adeef272c24c742e082305c940130`; workflow-only Sprint175 successor preservation correction, not Sprint176 engineering evidence.

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint176 — Atomic merchant-context bootstrap foundation

Sprint176 composes the existing durable context graph, initial tenant administrator provisioning, and first control principal credential bootstrap into one bounded merchant bootstrap foundation. The exact tenant/identity/organization/outlet/device/provisioning tuple must be preauthorized, the target tenant must be fresh, and graph/admin/credential creation is protected by one outer durable transaction.

The focused regression proves successful materialization, exact-tuple denial, existing-tenant denial, invalid-password denial before mutation, Preview-runtime denial, password hashing, and total rollback when the downstream credential stage fails.

The capability remains source-only and Local/Test/CI only. No provider binding, route, controller, UI, installer exposure, config activation, runtime widening, deployment, or operational activation was added.

## Product progression

oneQay now combines tenant/security/API/POS foundations, secure installation-readiness controls, and an atomic merchant-context bootstrap foundation that can create the foundational tenant-to-device graph together with the first protected administrator and credential under fail-closed authorization.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint177 begins only after Sprint176 canonical reconciliation. Select the smallest material P0/P1 blocker to a real merchant end-to-end journey proven by live canonical evidence. No real merchant provisioning, public onboarding exposure, production runtime widening, migration execution, deployment, updater activation, Technical Preview, Production, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry

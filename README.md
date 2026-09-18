# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint177 — Merchant Context Guarded Bootstrap Delivery Foundation**.

- Canonical engineering commit: `6752af1eb957993a6080206d9a40f7163bd24be6`
- Engineering PR: #781
- Final engineering head: `5a1b790414e2616ad6337224dc06392ee1154ec2`
- Exact-head surfaced qualification: 59/59 successful
- Engineering envelope: 4 paths, SHA-256 `de509f025c78e8f2ed7d0335b81b6f423deb621c3f1d6bc54bba4a312635d872`
- Reconciliation envelope: 6 paths, SHA-256 `cf8df3335c6b40d148a86b3b9ad7a565400b727a88c62b26869f8a4a5481e78c`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint177 — Guarded merchant-context bootstrap delivery

Sprint177 makes the Sprint176 atomic merchant-context bootstrap executable through a dedicated console command while preserving strict authorization boundaries. The command takes no merchant tuple arguments; the exact tenant/identity/organization/outlet/device/provisioning tuple must come from separately configured preauthorization material.

Execution remains default-deny. Merchant bootstrap, first-control credential bootstrap, and persistence must all be armed, and only Local/Test/CI runtime classes are accepted. Password inputs are hidden, failures are sanitized, replay fails closed, and no secret or configured merchant tuple is emitted.

No HTTP onboarding route, controller, public UI, installer exposure, real merchant provisioning, production runtime widening, deployment, migration execution, updater activation, Technical Preview/Production authorization, durable-target selection, or producer dispatch was added.

## Product progression

oneQay now combines tenant/security/API/POS foundations, governed installation readiness, atomic merchant-context creation, and a guarded non-public delivery mechanism for establishing that context under explicit Local/Test/CI authorization.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

After Sprint177 canonical reconciliation closes, Sprint178 begins with bounded discovery of the smallest material P0/P1 blocker still preventing the real merchant end-to-end journey. No operational authority is implied or pre-authorized.

Author by Lab | zefry

# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint199 — Merchant Account Security Self-Service Workspace**.

- Canonical engineering commit: `f2692018b261a723b9b360efe650969926adb2d2`
- Engineering PR: #829
- Final engineering head: `b7be9bc6c268aa6a332c0e709e417c6384d08800`
- Exact-head qualification: 85/85 successful
- Dedicated Sprint199 run `35438535934`: SUCCESS
- M7.5 release run `35438535597`: SUCCESS
- M7.1 run `35438535440`: SUCCESS
- Governance run `35438535437`: SUCCESS
- Engineering envelope SHA-256: `2aba38a7f80dcc6178ce39f865789b4e20b26f2d3ebb96b1d307af0c628e77e4`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint199 capability

oneQay now exposes existing first-party account-security authority as a coherent merchant self-service experience. The POS Operations Hub provides password change, recovery-code rotation, authenticator recovery-code rotation, and sign-out, while the Foundation sign-in surface provides password recovery and lost-authenticator replacement.

The implementation reuses existing application services and routes, keeps capability discovery server-derived, refreshes CSRF correctly after recovery-session regeneration, and does not persist recovery material in browser storage.

## Product progression

Governed release → installation/readiness lifecycle → guarded Technical Preview activation capability → integrated POS business workspace delivery → merchant account-security self-service.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; canonical Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains blocked; selected target remains `null`; no producer dispatch occurred.

## Next

Sprint200 starts from fully reconciled Sprint199 and selects the next material P0/P1 end-to-end business-completion blocker. Prefer a coherent merchant-facing outcome over another thin lifecycle-only step while preserving tenant isolation, deny-by-default behavior, deterministic qualification, and all operational NO-GO boundaries.

Author by Lab | zefry

# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint200 — Merchant POS Guided Operations Home**.

- Canonical engineering commit: `9c9c211416d216a396880d6e439e6d13c1438b73`
- Engineering PR: #831
- Final engineering head: `cf9d49063d9040b83729a48eaa298e5f67f98a45`
- Exact-head qualification: 86/86 successful
- Dedicated Sprint200 run `35439890798`: SUCCESS
- M7.5 release run `35439890767`: SUCCESS
- M7.1 run `35439890803`: SUCCESS
- Governance run `35439890548`: SUCCESS
- Engineering envelope SHA-256: `c8c07a41fba8ae22eabde78ceaeeed8ae88d0a5906bcc1429348d384a061682f`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint200 capability

oneQay now presents the POS Operations Hub as a guided merchant operations home. Delivered workspaces are summarized, grouped into business lanes, and exposed through a suggested starting action derived only from server-delivered destinations.

Full technical tenant/organization/outlet/device identifiers remain available when explicitly requested, but no longer dominate the primary merchant experience. Sprint199 Account & Security self-service remains integrated.

Guidance is advisory only. Each workspace continues to enforce its own authorization, prerequisites, persistence, and mutation eligibility.

## Product progression

Governed release → installation/readiness lifecycle → guarded Technical Preview activation capability → integrated POS business delivery → merchant account-security self-service → guided merchant operations home.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; canonical Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains blocked; selected target remains `null`; no producer dispatch occurred.

## Next

Sprint201 starts from fully reconciled Sprint200 and selects the next material P0/P1 end-to-end business-completion blocker. Prefer a coherent merchant-facing outcome over another thin lifecycle-only step while preserving tenant isolation, deny-by-default behavior, deterministic qualification, and all operational NO-GO boundaries.

Author by Lab | zefry

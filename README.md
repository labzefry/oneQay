# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint201 — Merchant POS State-Aware Guided Operations**.

- Canonical engineering commit: `cec54de9ff3d056f5981165616584c343b0152c2`
- Engineering PR: #833
- Final engineering head: `d5a1fb81ed725052cd89f98a72c0eefeba93a946`
- Exact-head qualification: 87/87 successful
- Dedicated Sprint201 run `35442031183`: SUCCESS
- Sprint200 preservation run `35442031497`: SUCCESS
- M7.5 release run `35442031110`: SUCCESS
- M7.1 run `35442030479`: SUCCESS
- Governance run `35442030465`: SUCCESS
- Engineering envelope SHA-256: `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint201 capability

oneQay now derives POS guidance from existing authorized business-state read models rather than route ordering alone.

The merchant operations home can identify when catalog/opening stock needs preparation, when the exact device shift/opening cash is not ready, when the cashier is ready to operate, and when a read-only reporting path is the appropriate fallback.

When readiness evidence cannot be verified safely, oneQay does not invent or infer a next mutation step. The separately authorized workspace list remains available, and each destination retains its own authorization, prerequisite, persistence, and mutation checks.

## Product progression

Governed release → installation/readiness lifecycle → guarded Technical Preview activation capability → integrated POS business delivery → merchant account-security self-service → guided merchant operations home → state-aware merchant operational guidance.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; canonical Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains blocked; selected target remains `null`; no producer dispatch occurred.

## Next

Sprint202 starts from fully reconciled Sprint201 and selects the next material P0/P1 end-to-end business-completion blocker. Prefer a coherent merchant-facing outcome over another thin lifecycle-only step while preserving tenant isolation, deny-by-default behavior, deterministic qualification, and all operational NO-GO boundaries.

Author by Lab | zefry

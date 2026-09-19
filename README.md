# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint208 — Durable Staging Deployment Authority Binding Foundation**.

- Canonical engineering commit: `20e835262f8163d45101ab00a818881421085d2e`
- Engineering PR: #849
- Final engineering head: `67aa2e73a0433585a34b76df4c7bec97b578aefe`
- Exact-head qualification: 87/87 successful
- Sprint208 run `35460395244`: SUCCESS
- Sprint207 preservation `35460395614`: SUCCESS
- Sprint206 preservation `35460396295`: SUCCESS
- M7.1 `35460395688`: SUCCESS
- Governance `35460395483`: SUCCESS
- PHP Foundation `35460396111`: SUCCESS
- Engineering envelope SHA-256: `5852e772a37b334987cdf5bcca0327a4e1b90d18a6affa02d6a9c3f44b6bf616`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint208 capability

oneQay now has a governed request/authority binding chain for a real durable-staging deployment target. A deterministic request binds the exact artifact and target candidate; a separately issued short-lived authority must match that request and is verified with an approval token before a qualified operator target can be emitted.

The Sprint207 deployment planner now also rejects expired/not-yet-valid authority and target-descriptor drift.

This chain remains non-operational by itself: no environment is created, no artifact is extracted, no runtime configuration is changed, no active release pointer is switched, and no migration or activation is performed.

## Product progression

Governed release → installation/readiness lifecycle → guarded Technical Preview → merchant POS journey → durable staging merchant core → authenticated durable-runtime readiness → governed durable artifact → validated handoff → deterministic operator plan → exact request-bound deployment authority qualification.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

A real isolated non-production durable-staging target and separate operational authority are required. Prepare the target candidate, generate the exact authority request, obtain matching short-lived authority, qualify it, then execute the generated operator plan externally and collect provenance/readback/health/rollback evidence.

Author by Lab | zefry

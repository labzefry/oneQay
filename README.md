# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint207 — Durable Staging Operator Deployment Planning**.

- Canonical engineering commit: `2cfc55cf3dde9304b713ba9fb6f70509dd4dafda`
- Engineering PR: #847
- Final engineering head: `b770e86b1a33e70a9272643f5abf04e1c150648c`
- Exact-head qualification: 86/86 successful
- Sprint207 plan run `35458897532`: SUCCESS
- Sprint206 preservation run `35458897643`: SUCCESS
- M7.1 run `35458897685`: SUCCESS
- Governance run `35458897999`: SUCCESS
- PHP Foundation run `35458897673`: SUCCESS
- Engineering envelope SHA-256: `bd138c83785e61f45b0b3066f3e1904942af23df8a34f6392b67dd966a5fa19a`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint207 capability

oneQay can now bind an exact Sprint206 validated durable-staging handoff to an exact externally authorized isolated non-production target and generate a deterministic, secret-free, non-mutating operator deployment plan.

The plan validates target/runtime/filesystem/capability/authority constraints and defines mandatory preflight, provenance readback, configuration readback, health, rollback, and deployment-evidence requirements.

The planner itself performs no deployment or runtime mutation. Existing Technical Preview/SystemUpdate behavior remains separate and `NO_SCHEMA_CHANGE`.

## Product progression

Governed release → installation/readiness lifecycle → guarded Technical Preview → merchant POS journey → durable staging merchant core → authenticated durable-runtime readiness → governed durable-staging artifact → validated handoff → deterministic operator deployment planning.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

A real isolated non-production `durable-staging` environment and separate operational authority are now required. Execute the exact generated operator plan externally, then verify provenance, configuration readback, non-mutating health, and rollback evidence before any protected producer dispatch or target selection.

Author by Lab | zefry

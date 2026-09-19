# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint205 — Governed Durable Staging Release Artifact Foundation**.

- Canonical engineering commit: `5d9826e96adfb31d1e9b9389d222db180f84935c`
- Engineering PR: #843
- Final engineering head: `c5b560a03bfec152f2860e7612b18517fb75434b`
- Exact-head qualification: 89/89 successful
- Sprint205 artifact run `35453077896`: SUCCESS
- M7.5 Preview preservation run `35453077950`: SUCCESS
- M7.1 run `35453078265`: SUCCESS
- Governance run `35453077832`: SUCCESS
- Engineering envelope SHA-256: `958492e789d583ddd73f803b5a82fa857e25692f372117492d04e25ac82a5a65`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint205 capability

oneQay now has a dedicated governed artifact for the canonical `durable-staging` runtime. The package is deterministic, exact-source-bound, exact-SHA-256-bound, includes durable migration source #1–#27, and carries the provenance contract needed by the Sprint204 readiness endpoint.

Artifact creation does not execute migrations or create/deploy an environment. It embeds no runtime secret values and grants no operational authority.

The existing Technical Preview package remains separate, synthetic/no-schema-change, and still excludes durable migrations.

## Product progression

Governed release → installation/readiness lifecycle → guarded Technical Preview → integrated merchant POS journey → bounded durable staging merchant core → authenticated durable-runtime readiness → governed durable-staging release artifact.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

The remaining blocker is no longer repository packaging. A real isolated non-production `durable-staging` environment must be provisioned/deployed under separate operational authority and must satisfy the existing durability, provenance, authenticated configuration, health/readback, and rollback contracts before qualification and target selection.

Author by Lab | zefry

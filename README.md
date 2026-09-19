# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint206 — Durable Staging Deployment Handoff Foundation**.

- Canonical engineering commit: `9fa3af317485fadd8844115260483c4926447695`
- Engineering PR: #845
- Final engineering head: `d8946bac37dd6a4c6b84f1a800ee1361f65aac23`
- Exact-head qualification: 90/90 successful
- Sprint206 handoff run `35454628797`: SUCCESS
- M7.5 Preview preservation run `35454629597`: SUCCESS
- M7.1 run `35454628771`: SUCCESS
- Governance run `35454629599`: SUCCESS
- Engineering envelope SHA-256: `2afad04ec60d7bce178795c8606922ee0dc38c7e672f16350902fe33b5760e3c`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint206 capability

oneQay can now transform an exact governed Sprint205 `durable-staging` artifact into a deterministic, machine-readable, secret-free deployment handoff after validating artifact/source/manifest identity and archive safety.

The handoff is deliberately non-operational: it does not extract into a runtime, configure a host, switch an active release pointer, execute migrations, deploy an environment, select a target, or dispatch the protected readiness producer.

The existing Technical Preview/SystemUpdate path remains separate and `NO_SCHEMA_CHANGE`.

## Product progression

Governed release → installation/readiness lifecycle → guarded Technical Preview → merchant POS journey → bounded durable staging merchant core → authenticated durable-runtime readiness → governed durable-staging artifact → validated external deployment handoff.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

The remaining blocker is operational, not repository packaging/handoff. A real isolated non-production `durable-staging` environment must be created and deployed under separate authority using the governed artifact and validated handoff, then qualified through the existing readiness/producer/ingestion contracts.

Author by Lab | zefry

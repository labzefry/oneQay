# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint204 — Durable Staging Runtime Readiness Attestation Delivery**.

- Canonical engineering commit: `a5672b315a320092c6fa8cc74d984cb70f4e18ae`
- Engineering PR: #841
- Final engineering head: `f849d3902c026105ae9e21088b45a05b6d71dcaa`
- Exact-head qualification: 88/88 successful
- Sprint204 bounded readiness run `35450150420`: SUCCESS
- M7.5 release run `35450150933`: SUCCESS
- M7.1 run `35450150370`: SUCCESS
- Governance run `35450150921`: SUCCESS
- Engineering envelope SHA-256: `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint204 capability

oneQay now exposes an authenticated, read-only durable-runtime readiness attestation surface for the canonical isolated non-production runtime class `durable-staging`.

`GET /internal/oneqay/durable-runtime/readiness` is present only when `ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED=true` and the external runtime class is exactly `durable-staging`. The endpoint emits the existing governed readiness shape, requires a bearer token, never returns the token, defaults external capability declarations to false, and prevents caching.

The prior `staging` alias remains available only for the bounded merchant-core compatibility bridge; it is not durable-target qualification identity.

## Product progression

Governed release → installation/readiness lifecycle → guarded Technical Preview capability → integrated merchant POS journey → authoritative checkout receipt → bounded durable staging merchant-core bridge → authenticated durable-staging readiness attestation.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

Do not invent another source-only lifecycle step. The next material requirement is a real isolated non-synthetic `durable-staging` environment satisfying the existing durability, provenance, authenticated configuration, health, readback, and rollback contracts. Protected producer dispatch, evidence ingestion, selection, deployment, migration, permission provisioning, and activation still require their own governed operational prerequisites and authority.

Author by Lab | zefry

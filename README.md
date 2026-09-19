# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint203 — Durable Staging Merchant Core Bounded Bridge**.

- Canonical engineering commit: `088dc0a38d80abc776cacc7052ddb3dbfdcf0c70`
- Engineering PR: #839
- Final engineering head: `a1a2eb0a3ff63edabe1c9ab06a5f8494bbd9d963`
- Exact-head qualification: 88/88 successful
- Staging bridge qualification run `35448422360`: SUCCESS
- M7.5 release run `35448421896`: SUCCESS
- M7.1 run `35448422369`: SUCCESS
- Governance run `35448421657`: SUCCESS
- Engineering envelope SHA-256: `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint203 capability

oneQay now has a bounded source path for the already-qualified merchant core to run in an explicitly armed, non-production `staging` runtime. The bridge keeps historical durable repositories unchanged and projects the legacy non-production compatibility runtime only inside the exact merchant-core request/bootstrap boundary.

The staging bridge covers merchant bootstrap, first-party authentication/session, existing account-security/MFA/recovery controls when enabled, POS Operations Hub, Catalog & Opening Stock, Shift Start/opening cash, Cashier, and durable sale completion.

Production remains source-denied by this bridge. Sale void, refund, closing-cash mutation, Final Shift Close, deployment, updater, target selection, and live activation remain outside Sprint203.

## Product progression

Governed release → installation/readiness lifecycle → guarded Technical Preview capability → integrated merchant POS journey → authoritative checkout receipt → bounded durable staging merchant-core source bridge.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains blocked; selected target remains `null`; no producer dispatch occurred.

## Next

Sprint204 should move directly toward qualifying a real non-synthetic durable staging target through a deterministic read-only readiness contract. Do not select, deploy, mutate, or activate a target without separate operational authority.

Author by Lab | zefry

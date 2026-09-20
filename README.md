# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint218 — Production Dark Deployment Execution Foundation**.

- Canonical engineering commit: `bca1957a61da0794737325438bd39e903ed7da19`
- Engineering PR: #871
- Final engineering head: `5d7e013cd941a01c7d44ac0940114ba852e354f2`
- Exact-head qualification: 97/97 SUCCESS
- Sprint218 regression: `35507277284` — SUCCESS
- Engineering envelope SHA-256: `8049c4b0b9dce606c9b66d464f99255a1d17043659fb20142d2c3d6a7984a5fa`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

Sprint218 adds the guarded Production **dark-deployment** executor and operator kit without changing application business source, running migration #27, or enabling Production traffic.

## Governed deployment inputs

Durable staging:
- application artifact `10603323419`
- cPanel no-SSH kit `10603569410`
- source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`

Production:
- Production candidate `10603358335`
- Production operator kit `10604307277`
- Production candidate source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- Production archive SHA-256 `cd1f96346d310aab3ac59398116499dae3e3df84a6af9ec1a13515d1e46568f1`
- Production operator-kit inner ZIP SHA-256 `d39ee6768462a2ee71466afb6a7b2bbdc08a74c49c449e5a1d3ff4cfdc96c895`

## Production dark-deployment boundary

A separately authorized Production execution can now:
- verify exact target/artifact/plan/authority;
- extract an immutable release;
- bind private runtime configuration;
- atomically activate the release pointer;
- verify `/health/live`;
- rehearse rollback;
- restore the candidate;
- qualify evidence only to `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.

It does not enable `/health/ready`, first-party Production business routes, migration #27, or Production traffic.

## Operational boundary

No real durable-staging or Production deployment has been performed. Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment authority `NOT_GRANTED`; Production traffic activation `NOT_AUTHORIZED`; updater `INACTIVE`; selected target `null`; producer dispatch `NOT_PERFORMED`.

## Next

Issue #856 is the single operational handoff. The next valid step is real same-source durable-staging qualification/deployment and evidence. Production dark deployment follows only after that evidence exists.

Author by Lab | zefry

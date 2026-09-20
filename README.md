# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint219 — cPanel Fixed Public Document Root Bridge**.

- Canonical engineering commit: `d1f832c42ae6e4b2705e9b1c295d031880cc0113`
- Engineering PR: #873
- Final engineering head: `20df712296f179e46d46937a8c965ae05675a686`
- Exact-head qualification: 98/98 SUCCESS
- Sprint219 regression: `35510403647` — SUCCESS
- Engineering envelope SHA-256: `476dcc4c82fe5b1a4924a3678797e32a28b838ab84bcbab79f36ccf2c33189e4`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

Sprint219 removes the remaining cPanel fixed-document-root source incompatibility without changing application business source or granting runtime authority.

## Governed deployment inputs

Durable staging:
- application artifact `10603323419`
- current cPanel no-SSH kit `10606042478`
- staging application source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- cPanel kit source `d1f832c42ae6e4b2705e9b1c295d031880cc0113`
- cPanel kit inner ZIP SHA-256 `9213ff3eaf1949d72787e3d5522321f93963ce623eef829f9e9e90e34fa2399d`

Production:
- Production candidate `10603358335`
- Production operator kit `10604307277`
- Production candidate source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- Production archive SHA-256 `cd1f96346d310aab3ac59398116499dae3e3df84a6af9ec1a13515d1e46568f1`

## cPanel fixed-public boundary

A live cPanel target may now qualify through either:
- `ACTIVE_RELEASE_PUBLIC`; or
- `FIXED_PUBLIC_BRIDGE`.

Fixed-public mode keeps the application and runtime material private and exposes only the generated bridge front controller plus current build assets. Its exact presentation path is authority-bound and rollback is rehearsed across both public surface and private active-release pointer.

Historical `oneqay.n07.my.id` paths are not current qualification evidence; the current kit must observe the live host again.

## Production dark-deployment boundary

A separately authorized Production execution can verify exact target/artifact/plan/authority, deploy an immutable candidate, verify `/health/live`, rehearse rollback, restore the candidate, and qualify evidence only to `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.

It does not enable `/health/ready`, first-party Production business routes, migration #27, or Production traffic.

## Operational boundary

No real durable-staging or Production deployment has been performed. Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment authority `NOT_GRANTED`; Production traffic activation `NOT_AUTHORIZED`; updater `INACTIVE`; selected target `null`; producer dispatch `NOT_PERFORMED`.

## Next

Issue #856 is the single operational handoff. Use cPanel kit `10606042478` to requalify the real staging host and produce same-source staging deployment evidence. Production dark deployment follows only after that evidence exists.

Author by Lab | zefry

# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint216 — Production Release Deployment Governance Foundation**.

- Canonical engineering commit: `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- Engineering PR: #867
- Final engineering head: `3a9f4a9189094a16be2abf1939319b6807227634`
- Exact-head qualification: 94/94 SUCCESS
- Sprint216 regression: `35504883655` — SUCCESS
- Engineering envelope SHA-256: `e1f0133337a2050cd789f2943101ab1acf7b29684c99d32ff3908ae6122fedd4`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

Sprint216 preserves application runtime source while adding a governed dark Production deployment path and publishing staging/Production candidates from the same canonical source.

## Current governed artifacts

Durable staging:
- artifact ID `10603323419`
- release `durable-staging-d0b5becbf945`
- source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- archive SHA-256 `e32a7de2c07c35306c03edff5d7d762782ef58449c92d4b88e5dd04b50d489a2`

Production candidate:
- artifact ID `10603358335`
- release `production-d0b5becbf945`
- source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- archive SHA-256 `cd1f96346d310aab3ac59398116499dae3e3df84a6af9ec1a13515d1e46568f1`
- business runtime activation ready: `false`
- Production traffic activation: `NOT_AUTHORIZED`

Independent verification confirms staging and Production candidate contain byte-identical application payload files.

## Promotion rule

Production promotion requires verified same-source durable-staging deployment evidence, an exact Production target candidate, and separate short-lived deployment authority. Production traffic activation remains a later separate gate.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production activation `NOT_AUTHORIZED`; updater `INACTIVE`; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

Issue #856 is the single operational handoff. Materialize and qualify the real durable-staging target using Sprint216 artifact `10603323419`, then obtain verified deployment evidence before any Production promotion.

Author by Lab | zefry

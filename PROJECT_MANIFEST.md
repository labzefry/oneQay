# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint218
**Objective:** `PRODUCTION_DARK_DEPLOYMENT_EXECUTION_FOUNDATION`
**Canonical engineering commit:** `bca1957a61da0794737325438bd39e903ed7da19`
**Engineering PR:** #871 — `Sprint218: add Production dark deployment execution foundation`
**Final engineering head:** `5d7e013cd941a01c7d44ac0940114ba852e354f2`
**Exact-head qualification:** 97/97 successful
**Sprint218 qualification:** run `35507277284` — SUCCESS
**Product Owner merge authority:** comment `5749467340`; `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 15 paths — `8049c4b0b9dce606c9b66d464f99255a1d17043659fb20142d2c3d6a7984a5fa`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint217 reconciliation `431bcc6f9dd62c1153785549dae9f28d921b2c62`

> `bca1957a61da0794737325438bd39e903ed7da19` is the permanent Sprint218 engineering evidence. The reconciliation squash must not replace it.

## Current durable-staging inputs

- application artifact ID: `10603323419`
- release: `durable-staging-d0b5becbf945`
- source: `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- archive SHA-256: `e32a7de2c07c35306c03edff5d7d762782ef58449c92d4b88e5dd04b50d489a2`
- cPanel operator kit ID: `10603569410`
- cPanel kit inner ZIP SHA-256: `910e1ec3f01d0dc0be515daefc7fd72648c35b37f08d7f3635fdfc380362a222`

## Current Production candidate

- publication run: `35505077185`
- artifact ID: `10603358335`
- release: `production-d0b5becbf945`
- source: `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- archive SHA-256: `cd1f96346d310aab3ac59398116499dae3e3df84a6af9ec1a13515d1e46568f1`
- manifest SHA-256: `6b871b954e5a31b5ae7c0bb43afb0e5c8386d5bc6e6818494ed75597d5307b86`
- business runtime activation ready: `false`
- Production traffic activation: `NOT_AUTHORIZED`

## Production operator kit

- publication run: `35507555918` — SUCCESS
- artifact ID: `10604307277`
- artifact name: `oneqay-production-operator-kit-bca1957a61da`
- kit source: `bca1957a61da0794737325438bd39e903ed7da19`
- Actions digest: `sha256:7163d5e85c5aaab189a18a3b3004ef7d0b6250fe2f278e4c723a136683acc756`
- inner ZIP SHA-256: `d39ee6768462a2ee71466afb6a7b2bbdc08a74c49c449e5a1d3ff4cfdc96c895`
- kit manifest SHA-256: `665f43102f6844fea463cc4ba64c5e459fcb1ff30cd1cfca1bf4154a2eadd22c`
- expiry: `2026-10-20T11:20:39Z`
- application runtime entries: 0
- forbidden secret-bearing file shapes: 0
- deployment authority: `NOT_GRANTED`
- Production traffic activation: `NOT_AUTHORIZED`

## Delivered Production execution capability

Sprint218 provides:
- Production target observation and target-candidate bridge;
- exact same-source staging-evidence prerequisite;
- exact Production release/target authority binding;
- authority lifetime <=900 seconds;
- deterministic Production deployment plan;
- guarded dark-deployment executor;
- immutable release extraction and private runtime binding;
- atomic active-release switching;
- `/health/live` verification;
- rollback rehearsal and candidate restoration;
- evidence qualification capped at `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.

Application business source remains unchanged. `/health/ready` and first-party business routes are not enabled for Production by Sprint218.

## Operational NO-GO

Real durable-staging deployment remains unperformed; verified real staging evidence is not materialized; real Production deployment remains unperformed; migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; general deployment authority `NOT_GRANTED`; Technical Preview/Production traffic activation `NOT_AUTHORIZED`; updater `INACTIVE`; target selection blocked; selected target `null`; producer dispatch `NOT_PERFORMED`.

## Next position

Issue #856 remains the single operational handoff.

The shortest valid route toward Production is now operational:
1. qualify and deploy the exact Sprint216 durable-staging artifact on a real isolated target;
2. obtain verified same-source staging evidence;
3. materialize a real isolated Production target;
4. use Production candidate `10603358335` and Production operator kit `10604307277`;
5. obtain separate Production deployment authority <=900 seconds;
6. dark-deploy and qualify only to `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.

Do not open a successor sprint merely for activity. Production business readiness/traffic activation remains a later gate after real deployment evidence proves the next source requirement.

Author by Lab | zefry

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint217
**Objective:** `CPANEL_SAME_SOURCE_STAGING_REBIND`
**Canonical engineering commit:** `afb048c9b7edc53d13ad8f5fc1197a8874966450`
**Engineering PR:** #869 — `Sprint217: rebind cPanel kit to same-source staging release`
**Final engineering head:** `21d0a4056c370e7e5358c43b3e7f5647cc7a9848`
**Exact-head qualification:** 96/96 successful
**Sprint217 qualification:** run `35505935195` — SUCCESS
**Product Owner merge authority:** comment `5749323944`; `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 6 paths — `d741d767942ac32eb8e0e1faeaf1bf2c2eade693b5ebdd142fc7d6ab5ff5fff9`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint216 reconciliation `9f66e763f1b40abaab9a9529d1bafa4777280b5b`

> `afb048c9b7edc53d13ad8f5fc1197a8874966450` is the permanent Sprint217 engineering evidence. The reconciliation squash must not replace it.

## Current same-source durable-staging artifact

- publication run: `35505077172`
- artifact ID: `10603323419`
- artifact name: `oneqay-durable-staging-d0b5becbf945-operator-bundle`
- source: `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- release: `durable-staging-d0b5becbf945`
- archive SHA-256: `e32a7de2c07c35306c03edff5d7d762782ef58449c92d4b88e5dd04b50d489a2`
- manifest SHA-256: `6d8099ef3f0b18003c8e362cc1f315d01777b70f3a05b3948f16ff4a44092b9c`
- deployment handoff SHA-256: `a9f33e8e5fa28abefdfb6397f350307e8954c486fbd43540a879b9c340c7ccad`
- handoff: `VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED`
- expiry: `2026-10-20T10:27:02Z`

## Current cPanel no-SSH operator kit

- publication run: `35506129034` — SUCCESS
- artifact ID: `10603569410`
- artifact name: `oneqay-cpanel-no-ssh-operator-kit-afb048c9b7ed`
- Actions digest: `sha256:49914d2d469df5707f131a5ae35ef16071e432f3970da7bab32046ebccd59c69`
- inner ZIP SHA-256: `910e1ec3f01d0dc0be515daefc7fd72648c35b37f08d7f3635fdfc380362a222`
- kit manifest SHA-256: `fb1ed300f339cef03ef3b5f22aaecff63368feddd29e9300aa9e9053c7739147`
- expiry: `2026-10-20T10:49:29Z`

The kit is bound to the current Sprint216 staging artifact identity and contains no application bytes, no stale Sprint211 release reference, no granted deployment authority, and no secret-bearing file shape.

## Same-source Production candidate

- publication run: `35505077185`
- artifact ID: `10603358335`
- release: `production-d0b5becbf945`
- source: `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- archive SHA-256: `cd1f96346d310aab3ac59398116499dae3e3df84a6af9ec1a13515d1e46568f1`
- business runtime activation ready: `false`
- Production traffic activation: `NOT_AUTHORIZED`

## Delivered capability

- cPanel target qualification, Sprint208 authority request, Sprint207 plan generation, Sprint214 guarded deployment execution, and Sprint209 evidence qualification now bind to the correct same-source Sprint216 staging release.
- Operator instructions derive current release identity from `KIT.json`; historical Sprint numbers are not release authority.
- Application source remains unchanged by Sprint217.
- Verified staging deployment evidence remains required before Production promotion.

## Next proven source blocker

Repository inspection confirms Production currently has:
- deterministic Production candidate release;
- Production target schema;
- authority request and qualifier;
- deployment-plan builder;
- deployment-evidence qualifier.

No Production execution tool exists yet. This is the next material repository-side blocker and justifies a bounded successor focused on Production dark-deployment execution without traffic activation.

## Operational NO-GO

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production activation `NOT_AUTHORIZED`; updater `INACTIVE`; target selection blocked; selected target `null`; producer dispatch `NOT_PERFORMED`.

Author by Lab | zefry

# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint217 — cPanel Same-Source Staging Rebind**.

- Canonical engineering commit: `afb048c9b7edc53d13ad8f5fc1197a8874966450`
- Engineering PR: #869
- Final engineering head: `21d0a4056c370e7e5358c43b3e7f5647cc7a9848`
- Exact-head qualification: 96/96 SUCCESS
- Sprint217 regression: `35505935195` — SUCCESS
- Engineering envelope SHA-256: `d741d767942ac32eb8e0e1faeaf1bf2c2eade693b5ebdd142fc7d6ab5ff5fff9`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

Sprint217 makes the cPanel no-SSH operator path consume the current same-source Sprint216 durable-staging release instead of the stale Sprint211 bundle.

## Current governed staging deployment inputs

Application:
- artifact ID `10603323419`
- release `durable-staging-d0b5becbf945`
- source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- archive SHA-256 `e32a7de2c07c35306c03edff5d7d762782ef58449c92d4b88e5dd04b50d489a2`

cPanel kit:
- artifact ID `10603569410`
- kit source `afb048c9b7edc53d13ad8f5fc1197a8874966450`
- inner ZIP SHA-256 `910e1ec3f01d0dc0be515daefc7fd72648c35b37f08d7f3635fdfc380362a222`
- manifest SHA-256 `fb1ed300f339cef03ef3b5f22aaecff63368feddd29e9300aa9e9053c7739147`

The kit contains zero application payload entries and is bound to the staging identity above.

## Production continuity

Production candidate artifact `10603358335` is from the same application source `d0b5becbf945c5192e797d512a704eb5aecc6eaa`. Verified durable-staging deployment evidence from that source is required before Production promotion.

Production still has no executable deployment adapter/tool in source; only artifact, target, authority, plan, and evidence contracts exist. The next bounded engineering step closes that dark-deployment execution gap without activating Production traffic.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production activation `NOT_AUTHORIZED`; updater `INACTIVE`; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

Author by Lab | zefry

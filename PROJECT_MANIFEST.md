# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint219
**Objective:** `CPANEL_FIXED_PUBLIC_DOCROOT_BRIDGE`
**Canonical engineering commit:** `d1f832c42ae6e4b2705e9b1c295d031880cc0113`
**Engineering PR:** #873 — `Sprint219: support fixed public cPanel document roots`
**Final engineering head:** `20df712296f179e46d46937a8c965ae05675a686`
**Exact-head qualification:** 98/98 successful
**Sprint219 qualification:** run `35510403647` — SUCCESS
**Product Owner merge authority:** comment `5749943007`; `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 18 paths — `476dcc4c82fe5b1a4924a3678797e32a28b838ab84bcbab79f36ccf2c33189e4`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint218 reconciliation `3cbf1af7ae56b893e5957b579c3a95645e553ea3`

> `d1f832c42ae6e4b2705e9b1c295d031880cc0113` is the permanent Sprint219 engineering evidence. The reconciliation squash must not replace it.

## Current durable-staging inputs

- application publication run: `35505077172`
- application artifact ID: `10603323419`
- release: `durable-staging-d0b5becbf945`
- source: `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
- archive SHA-256: `e32a7de2c07c35306c03edff5d7d762782ef58449c92d4b88e5dd04b50d489a2`
- cPanel operator-kit publication run: `35512151565` — SUCCESS
- cPanel operator kit ID: `10606042478`
- cPanel kit name: `oneqay-cpanel-no-ssh-operator-kit-d1f832c42ae6`
- cPanel kit source: `d1f832c42ae6e4b2705e9b1c295d031880cc0113`
- cPanel kit Actions outer digest: `sha256:24d64361ad4509cdd9ba2e69b688ae3ad1c0ee6d33d0c7a8c275815d620b5953`
- cPanel kit inner ZIP SHA-256: `9213ff3eaf1949d72787e3d5522321f93963ce623eef829f9e9e90e34fa2399d`
- cPanel kit expiry: `2026-10-20T12:58:53Z`
- deployment authority: `NOT_GRANTED`
- selected target: `null`

The Actions outer digest and governed inner kit ZIP SHA-256 are distinct integrity layers and must not be substituted for each other.

## Delivered cPanel target compatibility

Sprint219 provides two explicit presentation modes:

- `ACTIVE_RELEASE_PUBLIC`: domain document root equals `<active-release-pointer>/apps/web/public`.
- `FIXED_PUBLIC_BRIDGE`: private releases remain outside the public tree while the public root receives only the governed bridge `index.php` and current `build/` assets; the existing operator-managed `.htaccess` is preserved.

For fixed-public mode:
- public/private paths must be disjoint;
- rewrite-to-`index.php` must be observed;
- atomic public file/directory rename must work;
- exact presentation mode and public path are authority-bound;
- front-controller backup remains private;
- rollback rehearses both the public surface and private active-release pointer.

Historical cPanel observations for `oneqay.n07.my.id` are only requalification inputs. The live host must run the current Sprint219 kit before it can become a current target candidate.

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
- Production traffic activation: `NOT_AUTHORIZED`

## Operational NO-GO

Real durable-staging deployment remains unperformed; verified real staging evidence is not materialized; real Production deployment remains unperformed; migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; general deployment authority `NOT_GRANTED`; Technical Preview/Production traffic activation `NOT_AUTHORIZED`; updater `INACTIVE`; target selection blocked; selected target `null`; producer dispatch `NOT_PERFORMED`.

## Next position

Issue #856 remains the single operational handoff.

The shortest valid route toward Production is now:

1. retrieve cPanel kit artifact `10606042478` and durable-staging application artifact `10603323419`;
2. requalify the live cPanel host with current on-host facts; use `FIXED_PUBLIC_BRIDGE` only when its real fixed public root is observed;
3. obtain separate Sprint208 deployment authority <=900 seconds for the exact target/release;
4. deploy the same-source staging artifact and qualify `DEPLOYED_VERIFIED_NOT_SELECTED` evidence;
5. only after verified staging evidence exists, materialize/qualify the isolated Production target;
6. use Production candidate `10603358335` and Production operator kit `10604307277`;
7. dark-deploy and qualify only to `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`;
8. close business-readiness/migration/traffic-activation gates only from real deployment evidence.

Do not open Sprint220 merely for activity. Open another bounded engineering sprint only if real-host qualification or execution proves a concrete repository-side missing capability or defect.

Author by Lab | zefry

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint215
**Objective:** `DURABLE_STAGING_PERSISTENT_OPERATOR_RELEASE_PUBLICATION_FOUNDATION`
**Canonical engineering commit:** `759ba3d5d05d2bead51580be8d778b6f987b95c4`
**Engineering PR:** #865 — `Sprint215: add persistent operator release publication foundation`
**Final engineering head:** `c996794af12bc085213d91c1961bb71b3d351394`
**Exact-head qualification:** 94/94 successful
**Sprint215 qualification:** run `35502047468` — SUCCESS
**Product Owner merge authority:** comment `5748933469`; `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 5 paths — `fb3923f0bce30e06695776cb75bdce6d649dd43dc183bc75df0f1b4fd6e828e7`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint214 reconciliation `3eacfc54aca1c1d45217eb226e84f1a0d70aee50`

> `759ba3d5d05d2bead51580be8d778b6f987b95c4` is the permanent canonical Sprint215 engineering evidence. The reconciliation squash must not replace it.

## Persistent operator handoff publication foundation

Sprint215 adds an authority-gated persistent GitHub prerelease publication path for the exact existing governed operator assets.

**Reserved release tag:** `operator-handoff-e37300d5d1be-270e8e954e38`
**Publication trigger:** manual `workflow_dispatch` only
**Automatic push publication:** forbidden
**Separate issue #856 publication authority:** required
**Persistent release publication state:** `NOT_PERFORMED`
**GitHub Releases at engineering closure:** none

The workflow is draft-first and refuses existing release/tag reuse or asset overwrite. It verifies asset identities before draft publication and does not perform deployment.

## Governed application bundle

- Actions artifact ID: `10597712890`
- Actions artifact name: `oneqay-durable-staging-e37300d5d1be-operator-bundle`
- Actions digest: `sha256:a0020b5378d3b2dedc0d447abd8dd09b896048243aabab329184e424281a0be3`
- Release ID: `durable-staging-e37300d5d1be`
- Source: `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
- Archive SHA-256: `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`
- Manifest SHA-256: `ef968562f76801e425d02c33d3ecfc13556f271f05bff1ff9441c5a147b588f0`
- Deployment handoff state: `VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED`

## Governed cPanel no-SSH operator kit

- Actions artifact ID: `10601606508`
- Actions artifact name: `oneqay-cpanel-no-ssh-operator-kit-270e8e954e38`
- Actions digest: `sha256:58d3f85c262dadb2b2f9e37ab1852eec9a250f9075558cfa6ce3dd3756895f22`
- Source: `270e8e954e389f61d32f49b89b87bed571866867`
- Inner ZIP SHA-256: `e572c93f1a8fc55b1c67f0b1f8744a2b6dd3f3c811d0c83a91767fef8df1ae82`
- Manifest SHA-256: `750375c512c3eab35530da902fbbf30eff63fb7dc59ad141f0a4e33fce5c97f9`

## Delivered capability

- Persistent repository-retention publication can preserve the exact Sprint211/Sprint214 governed bytes beyond Actions retention.
- Publication is bound to exact current main and exact issue #856 Product Owner authorization.
- No payload rebuild is permitted in the persistence workflow.
- Draft-first publication and no-overwrite semantics preserve immutability.
- Publication authority remains separate from deployment/runtime authority.

## Operational NO-GO

Machine-readable state remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; general deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch `NOT_PERFORMED`.

Persistent GitHub prerelease publication is also `NOT_PERFORMED` until separately authorized.

## Next position

Issue #856 remains the single operational handoff.

Two independent external actions remain:
1. separately authorize and run the Sprint215 persistent repository publication before the Actions artifacts expire; and
2. materialize/qualify a real isolated durable-staging target, then continue Sprint208 → Sprint207 → Sprint214 → Sprint209 under their separate authorities.

Do not open Sprint216 merely to continue activity. Open another bounded source sprint only if persistent publication execution or real target qualification/execution proves a concrete repository-side defect.

Author by Lab | zefry

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint214
**Objective:** `CPANEL_NO_SSH_GUARDED_DEPLOYMENT_EXECUTION`
**Canonical engineering commit:** `270e8e954e389f61d32f49b89b87bed571866867`
**Engineering PR:** #863 — `Sprint214: add guarded cPanel no-SSH deployment execution`
**Final engineering head:** `d8822e6f2e3aee3b8550424c2e36c342b09fe101`
**Exact-head qualification:** 93/93 successful
**Sprint214 qualification:** run `35497192974` — SUCCESS
**M7.1 qualification:** run `35497193597` — SUCCESS
**Governance qualification:** run `35497192918` — SUCCESS
**PHP Foundation qualification:** run `35497192904` — SUCCESS
**Product Owner merge authority:** comment `5748437685`; `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 8 paths — `dd1abbfbd9c21d2372cc1b3957ea42bed438b224b88284ceb870df81e8b46347`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint213 reconciliation `99be46de7366395109edf23db8cc8068d4ca41d5`

> `270e8e954e389f61d32f49b89b87bed571866867` is the permanent canonical Sprint214 engineering evidence. The reconciliation squash must not replace it.

## Published cPanel no-SSH operator kit

**Publication run:** `35497396424` — SUCCESS
**Actions artifact ID:** `10601606508`
**Artifact name:** `oneqay-cpanel-no-ssh-operator-kit-270e8e954e38`
**Kit source commit:** `270e8e954e389f61d32f49b89b87bed571866867`
**Actions artifact size:** 61,862 bytes
**Actions artifact digest:** `sha256:58d3f85c262dadb2b2f9e37ab1852eec9a250f9075558cfa6ce3dd3756895f22`
**Inner operator ZIP SHA-256:** `e572c93f1a8fc55b1c67f0b1f8744a2b6dd3f3c811d0c83a91767fef8df1ae82`
**Kit manifest SHA-256:** `750375c512c3eab35530da902fbbf30eff63fb7dc59ad141f0a4e33fce5c97f9`
**Manifest payload file count:** 22
**ZIP regular file count:** 23 including `kit.manifest.json`
**Expiry:** 2026-10-20T07:38:10Z

Independent downloaded-artifact verification confirmed the Actions digest, ZIP sidecar, ZIP integrity, Sprint214 executor and execution contract, absence of `apps/web` application bytes, absence of secret-bearing file shapes, `secret_values_embedded = false`, `deployment_authority = NOT_GRANTED`, `migration27_execution = NOT_PERFORMED`, `selected_target = null`, and `producer_dispatch = NOT_PERFORMED`.

## Current application release binding

Sprint214 changes deployment tooling and publication content, not application runtime source. The governed application release therefore remains Sprint211:

**Application Actions artifact ID:** `10597712890`
**Release ID:** `durable-staging-e37300d5d1be`
**Application source:** `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
**Application artifact SHA-256:** `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`
**Application manifest SHA-256:** `ef968562f76801e425d02c33d3ecfc13556f271f05bff1ff9441c5a147b588f0`
**Deployment handoff state:** `VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED`

## Delivered capability

- cPanel no-SSH qualification, authority preparation/qualification, exact operator planning, guarded deployment execution, and Sprint209 evidence qualification are now distributed together.
- The executor rejects stale/expired authority, plan fingerprint drift, target/release drift, archive mismatch, unsafe active-pointer state, failed readiness, and failed rollback verification.
- Deployment uses immutable release directories and atomic active-release pointer switching.
- Initial and rolling deployment paths have explicit rollback semantics.
- Runtime secrets remain private host material and are excluded from repository and kit evidence.
- No deployment authority is embedded in the kit and no real host execution occurred during engineering/publication.

## Operational NO-GO

Machine-readable state remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; general deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch `NOT_PERFORMED`.

## Next position

Issue #856 remains the single operational handoff. The next material work is a real isolated target, not another speculative source sprint. For cPanel without SSH, use the Sprint214 kit to qualify the host, produce the canonical Sprint208 target candidate, obtain separately issued short-lived authority, generate the exact Sprint207 plan, upload the governed Sprint211 application archive, and run the Sprint214 executor while authority is valid. Then qualify the emitted Sprint209 evidence and continue protected runtime attestation/ingestion only under their separate authorities.

Open Sprint215 only if this real sequence proves a concrete repository-side defect or missing capability.

Author by Lab | zefry

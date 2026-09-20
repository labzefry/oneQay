# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint214 — cPanel No-SSH Guarded Deployment Execution**.

- Canonical engineering commit: `270e8e954e389f61d32f49b89b87bed571866867`
- Engineering PR: #863
- Final engineering head: `d8822e6f2e3aee3b8550424c2e36c342b09fe101`
- Exact-head qualification: 93/93 successful
- Engineering envelope SHA-256: `dd1abbfbd9c21d2372cc1b3957ea42bed438b224b88284ceb870df81e8b46347`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

Sprint214 completes the source-side cPanel no-SSH execution chain: the published kit now contains an authority-bound PHP CLI executor that can turn the exact Sprint207 plan into Sprint209-compatible deployment evidence while remaining migration-free and fail-closed.

## Published cPanel operator kit

- Publication run: `35497396424` — SUCCESS
- Artifact ID: `10601606508`
- Artifact name: `oneqay-cpanel-no-ssh-operator-kit-270e8e954e38`
- Actions digest: `sha256:58d3f85c262dadb2b2f9e37ab1852eec9a250f9075558cfa6ce3dd3756895f22`
- Inner ZIP SHA-256: `e572c93f1a8fc55b1c67f0b1f8744a2b6dd3f3c811d0c83a91767fef8df1ae82`
- Manifest SHA-256: `750375c512c3eab35530da902fbbf30eff63fb7dc59ad141f0a4e33fce5c97f9`
- Manifest payload files: 22; ZIP regular files: 23 including `kit.manifest.json`
- Expiry: 20 October 2026

The kit contains the Sprint214 executor and contract, but no application runtime bytes, real secrets, granted deployment authority, selected target, or producer dispatch.

## Governed application bundle

The application release remains the Sprint211 durable-staging bundle:

- Artifact ID: `10597712890`
- Release ID: `durable-staging-e37300d5d1be`
- Source: `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
- Artifact SHA-256: `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`
- Manifest SHA-256: `ef968562f76801e425d02c33d3ecfc13556f271f05bff1ff9441c5a147b588f0`
- Handoff: `VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED`

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection remains blocked; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

Issue #856 is the operational gate. Qualify a real isolated VM/VPS or cPanel no-SSH host. For cPanel, use the Sprint214 kit and governed Sprint211 application bundle, then request separate short-lived Sprint208 deployment authority before running the exact Sprint207/Sprint214/Sprint209 chain. Do not open another source sprint unless real execution exposes a concrete source blocker.

Author by Lab | zefry

# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint212 — cPanel No-SSH Durable Staging Target Qualification**.

- Canonical engineering commit: `7adc0f34bbf1646400c344e7c6d1f89324db61d1`
- Engineering PR: #859
- Final engineering head: `d3f771daabcf9263069e6b7b23af6302b06dab45`
- Exact-head qualification: 91/91 successful
- Engineering envelope SHA-256: `73f2978a1a748f8314c58078b62653ee331a8b21dd08bef971952a795ed9550e`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

Sprint212 allows a real cPanel/shared-hosting target without SSH to be qualified through File Manager + one-shot Cron/PHP CLI and bridged into the existing Sprint208 target-candidate contract. It does not create a parallel deployment authority model.

## Published operator bundle

The current application bundle remains the Sprint211 durable-staging publication:

- Publication run: `35487670967` — SUCCESS
- Artifact ID: `10597712890`
- Release ID: `durable-staging-e37300d5d1be`
- Source: `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
- Artifact SHA-256: `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`
- Manifest SHA-256: `ef968562f76801e425d02c33d3ecfc13556f271f05bff1ff9441c5a147b588f0`
- Handoff: `VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED`

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains blocked; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

Issue #856 remains the consolidated operational gate. Qualify a real host next: either an operator-managed POSIX VM/VPS or a cPanel no-SSH host that passes Sprint212. Then request separate short-lived Sprint208 deployment authority and continue the canonical Sprint207/Sprint209 evidence chain.

Author by Lab | zefry

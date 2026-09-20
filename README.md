# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint213 — cPanel No-SSH Operator Qualification Kit Publication**.

- Canonical engineering commit: `c8998c00e19c177ac535921dbc0ef1fa96b06583`
- Engineering PR: #861
- Final engineering head: `4c2ef5d73436e3660d0f6700a9bb3cfe46438289`
- Exact-head qualification: 92/92 successful
- Engineering envelope SHA-256: `7ccb0d202041e8968e7d526d6ee3fc7d76fd68048fc062993a8172b53a95e6be`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

Sprint213 publishes a deterministic, secret-free ZIP containing the operator-side cPanel no-SSH qualification/governance chain, so a shared-hosting operator does not need Git or SSH to collect repository tools.

## Published cPanel operator kit

- Publication run: `35494805706` — SUCCESS
- Artifact ID: `10600262872`
- Artifact name: `oneqay-cpanel-no-ssh-operator-kit-c8998c00e19c`
- Inner ZIP SHA-256: `de485d82c7960683a90c605f4d220b09f85b6cc2282e18f52c43b82006949011`
- Manifest SHA-256: `d9a0300c24e260d3d87533af6808185a269471cfdeea0e69b2310188b30f3fde`
- Internal files: 20
- Expiry: 20 October 2026

The kit contains no application runtime bytes and no granted deployment authority.

## Governed application bundle

The application release remains the Sprint211 durable-staging bundle:

- Artifact ID: `10597712890`
- Release ID: `durable-staging-e37300d5d1be`
- Source: `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
- Artifact SHA-256: `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`
- Manifest SHA-256: `ef968562f76801e425d02c33d3ecfc13556f271f05bff1ff9441c5a147b588f0`
- Handoff: `VALIDATED_FOR_EXTERNAL_DEPLOYMENT_NOT_AUTHORIZED`

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains blocked; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

Issue #856 remains the operational gate. A cPanel no-SSH operator now retrieves the Sprint213 qualification kit plus the Sprint211 application bundle, qualifies the real host through File Manager + one-shot Cron/PHP CLI, then enters the existing Sprint208/Sprint207/Sprint209 chain. A POSIX VM/VPS remains a supported alternative.

Author by Lab | zefry

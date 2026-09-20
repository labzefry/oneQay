# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint215 — Durable Staging Persistent Operator Release Publication Foundation**.

- Canonical engineering commit: `759ba3d5d05d2bead51580be8d778b6f987b95c4`
- Engineering PR: #865
- Final engineering head: `c996794af12bc085213d91c1961bb71b3d351394`
- Exact-head qualification: 94/94 successful
- Sprint215 regression: `35502047468` — SUCCESS
- Engineering envelope SHA-256: `fb3923f0bce30e06695776cb75bdce6d649dd43dc183bc75df0f1b4fd6e828e7`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

Sprint215 closes the repository-side retention gap that could otherwise make the exact Sprint211/Sprint214 operator inputs unavailable after 30-day Actions retention.

## Persistent operator handoff

Reserved prerelease tag:

`operator-handoff-e37300d5d1be-270e8e954e38`

The workflow is manual-only, authority-gated, draft-first, and no-overwrite. Merge does not publish the release.

Persistent publication state: **NOT_PERFORMED**.

## Governed application bundle

- Artifact ID: `10597712890`
- Release ID: `durable-staging-e37300d5d1be`
- Source: `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
- Artifact SHA-256: `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`
- Manifest SHA-256: `ef968562f76801e425d02c33d3ecfc13556f271f05bff1ff9441c5a147b588f0`

## Governed cPanel operator kit

- Artifact ID: `10601606508`
- Source: `270e8e954e389f61d32f49b89b87bed571866867`
- Inner ZIP SHA-256: `e572c93f1a8fc55b1c67f0b1f8744a2b6dd3f3c811d0c83a91767fef8df1ae82`
- Manifest SHA-256: `750375c512c3eab35530da902fbbf30eff63fb7dc59ad141f0a4e33fce5c97f9`

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; target selection remains blocked; selected target remains `null`; producer dispatch remains `NOT_PERFORMED`.

## Next

Issue #856 is the single handoff.

Separately authorize persistent repository publication before artifact expiry, and materialize/qualify a real isolated target for the Sprint208/Sprint207/Sprint214/Sprint209 deployment-evidence chain.

Do not open another source sprint unless either real publication execution or real target execution exposes a concrete source blocker.

Author by Lab | zefry
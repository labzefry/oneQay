# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint186 — Governed Activation-Readiness Handoff**.

- Canonical engineering commit: `7cb9e59ede2908f44f4f0d7b2d1c855d885bf3ae`
- Engineering PR: #802
- Final engineering head: `3c6453cd54ac0bc907d85ec01d7410d2c48e19fb`
- Exact-head qualification: 74/74 successful
- Canonical main-push M7.5 run `35387074508`: SUCCESS
- Engineering envelope: 10 paths, SHA-256 `6a948cea5e7d88f95abec930a0681f851df9d04a1eaaa835f4aac3de1e0c8102`
- Reconciliation envelope: 8 paths, SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint186 — Sealed activation-readiness handoff

The governed installer now seals a verified pending configuration to the exact governed release.

After Sprint185 database compatibility succeeds, oneQay binds the release ID into `.env.pending` and writes a private `activation-readiness.json` containing the exact pending SHA-256/byte length and safe database evidence. Tampering or a release mismatch invalidates readiness.

The operator UI exposes the handoff as **SEALED / NOT AUTHORIZED**. This is deliberately evidence only: it does not activate the runtime.

## Product progression

oneQay now has a governed chain from deterministic release artifact → installation readiness → operator preflight → secure configuration → live DB verification → verified pending configuration → tamper-evident exact-release activation handoff.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint187 begins from the fully reconciled Sprint186 checkpoint. Select the smallest material P0/P1 blocker that advances the sealed handoff toward a usable governed installation/onboarding journey without implicitly granting operational activation.

Author by Lab | zefry

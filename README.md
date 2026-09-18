# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint178 — Merchant First-Party Application Entry Foundation**.

- Canonical engineering commit: `8992c2ed1b6278d113e24e38a847bedeac345161`
- Engineering PR: #783
- Final engineering head: `ee0e2e8acc5238fa0cb2e3be56b6e58cf2092239`
- Exact-head surfaced qualification: 60/60 successful
- Engineering envelope: 4 paths, SHA-256 `9d27ecd0802230d3484aa7ca424064313595e8174172eb889c2736250e2815c5`
- Reconciliation envelope: 6 paths, SHA-256 `d994709453d1415d23d2bdfc8ecade257d8baa8b07b9aff98654a2b4cb6bb0f5`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint178 — Merchant first-party application entry

Sprint178 connects the existing first-party login/session-authority stack to the already-delivered POS Operations Hub through a guarded merchant-facing entry experience.

The final implementation deliberately reuses the existing Foundation surface. Server-rendered metadata enables merchant entry only for Local/Test/CI with persistence and session control available. Login, TOTP enrollment/challenge, session authority, and `/pos` remain owned by their existing canonical components.

No public registration, implicit permission provisioning, new authentication architecture, new POS capability, production runtime widening, migration execution, deployment, updater activation, Technical Preview/Production authorization, durable-target selection, or producer dispatch was added.

## Product progression

oneQay now combines governed installation readiness, atomic merchant-context bootstrap, guarded bootstrap delivery, first-party identity/session security, and a usable guarded browser entry into existing permission-filtered POS operations.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint179 begins with bounded discovery of the smallest material P0/P1 blocker still preventing a complete merchant journey. No operational authority is implied or pre-authorized.

Author by Lab | zefry

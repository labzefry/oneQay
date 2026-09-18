# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint185 — Preboot Database Compatibility Verification**.

- Canonical engineering commit: `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`
- Engineering PR: #799
- Final engineering head: `bf17397f739eab4aae531ed6b6a1b0b5430ae98e`
- Exact-head qualification: 71/71 successful
- Canonical main-push M7.5 run `35382800589`: SUCCESS
- Engineering envelope: 10 paths, SHA-256 `775caa7723278af855b888f2c6bac592d9187e7b988619799f90e2e7a6950e99`
- Reconciliation envelope: 8 paths, SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint185 — Verified pending database configuration

The governed pre-boot installer now proves database compatibility before accepting pending runtime configuration.

The verification uses the submitted database credentials to perform read-only checks for MySQL/MariaDB connectivity, server/version shape, `utf8mb4`, UTC, schema state, and database-scoped least privilege. A foreign or incompatible database fails closed and does not create `.env.pending`.

When verification succeeds, safe evidence is bound into the private pending configuration and the installer exposes `PENDING_CONFIGURATION_VERIFIED`. The step remains non-activating.

## Product progression

oneQay now has a governed chain from deterministic release artifact → installation readiness → operator preflight → secure pre-boot configuration → live database compatibility verification → verified pending runtime configuration.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint186 begins from the fully reconciled Sprint185 checkpoint. Select the smallest material P0/P1 blocker that advances verified pending configuration toward a usable governed installation/onboarding journey without implicitly granting operational activation.

Author by Lab | zefry

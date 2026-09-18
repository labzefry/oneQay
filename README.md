# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint181 — Installation Readiness Wizard Delivery Foundation**.

- Canonical engineering commit: `deb999fcd0694ba85c132d1b490bd90c0dc86309`
- Engineering PR: #789
- Final engineering head: `c12595cd88938adaea3f470b74c315b34732bf6b`
- Exact-head surfaced qualification: 66/66 successful
- Engineering envelope: 4 paths, SHA-256 `dffcd90da1967e207fe5b65007c354ce0decb1f5d781db9733623cb9ca807e04`
- Reconciliation envelope: 6 paths, SHA-256 `4fd83153da1067e5fa7a3f8ed145af7e8cbde3c3cba24149a51cd10770d3d955`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint181 — Installation readiness wizard

Sprint181 turns the existing secure installation-readiness engine into an operator-visible, read-only preflight wizard on the existing system operations surface.

The wizard shows readiness for runtime/host, secure configuration, database compatibility, filesystem, governed release manifest, and release artifact integrity. Evidence is observed rather than invented: absent release package evidence remains BLOCKED, and host capabilities that cannot be proven safely remain unresolved.

The updater remains hard-disabled. The wizard does not execute migrations, seeders, environment writes, downloads, deployment, activation, or any installation mutation.

## Product progression

oneQay now combines governed release/readiness contracts, operator-visible installation preflight, atomic POS-ready merchant bootstrap, context-assisted first-party sign-in, and permission-filtered POS operations.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint182 begins with bounded discovery of the smallest material P0/P1 blocker that advances installation/onboarding/merchant completeness without operational activation.

Author by Lab | zefry

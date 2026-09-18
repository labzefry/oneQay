# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint183 — Governed M7.5 Release Workflow Execution Restoration**.

- Canonical engineering commit: `cd3facf81e3b1734353656da3ba5800607900fcb`
- Initial engineering PR: #793
- Corrective engineering PR: #794
- Final engineering head: `f3527999a500463e9eea3f8b9b22dec24a34e93e`
- Exact-head corrective qualification: 69/69 successful
- Canonical main-push M7.5 run `35369464318`: SUCCESS
- Engineering envelope: 1 path, SHA-256 `bcec6fc13a26f5c88f4408d76d362195ca9d546cc2df6d6c388a67640b93cce2`
- Reconciliation envelope: 6 paths, SHA-256 `09fc0a9ae283def9c130b48fa756b17624ae0fbf3f87ad988c40605fcaf362c6`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint183 — Governed M7.5 release automation

Sprint183 restores the canonical M7.5 release workflow as executable GitHub Actions automation.

The oversized historical Web regression shell command was split without changing release behavior. Historical compatibility state is carried across the split, post-M7.4 POS persistence successors are isolated only during the legacy synthetic regression, and non-PR execution now enters the same schema-free historical lane required by current main.

The final canonical main-push M7.5 run completed successfully through release packaging, Sprint182 installer-readiness sidecar generation, deterministic reproduction, artifact upload, and source-cleanliness verification.

## Product progression

oneQay now has an executable governed release pipeline feeding the trusted installer-readiness evidence bridge and the operator-visible installation preflight, while preserving the merchant bootstrap/sign-in/POS progression.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint184 begins from the fully reconciled Sprint183 checkpoint and should select the smallest material P0/P1 blocker remaining in the installation/onboarding journey without crossing operational activation boundaries.

Author by Lab | zefry

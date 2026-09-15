# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint171 — Installation Governed Release Artifact Readiness**.

- Canonical engineering commit: `cbf26a53c1a784b8e6eda65cc1d047c90ea590e8`
- Engineering PR: #760
- Final engineering head: `b2dbd36edb75174a944a77cc547811938e772ed0`
- Sprint171 regression `34968787888`: successful
- Governance `34968787247`: successful
- PHP Foundation `34968787525`: successful
- M7.1 `34968787729`: successful
- Engineering envelope: 3 paths, SHA-256 `cf2f56c6ba42404726fe225a559b7262580191a9bf1c8b03c4ebf39fe2f29d88`
- Workflow compatibility correction: PR #762, squash `1e568085d6b9743eb05c73fb9a78c019b8d82f04`
- Reconciliation envelope: 6 paths, SHA-256 `a8627a615280e7592638964a5d7f496f77310ef28f085439f2ba584d6a97d301`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint171 — Governed release artifact installation readiness

Sprint171 extends the canonical installation-readiness owner with a deterministic, fail-closed boundary for the minimum Governed Release Manifest v1 identity and an observed release artifact identity check before any future download, extraction, staging, or activation work.

The readiness contract requires canonical `oneQay` / `labzefry/oneQay` identity, supported release channel, immutable source-commit form, safe artifact filename/type/positive byte size/SHA-256, `NO_SCHEMA_CHANGE`, and attribution `Lab | zefry`. Observed artifact filename, size, and digest must exactly match the manifest. Invalid values fail closed without being echoed.

Existing runtime, configuration, HTTPS, key posture, filesystem-write, and redaction checks remain preserved. Sprint171 does not download artifacts, extract archives, verify external signatures/provenance, mutate configuration, execute migrations, expose an installer, activate the updater, or deploy anything.

A workflow-only successor-compatibility correction was merged through PR #762 after the initial reconciliation attempt correctly exposed stale historical workflow assumptions. This did not change Sprint171 application source or its canonical engineering evidence.

## Product progression

The canonical product chain includes tenant isolation, deny-by-default authorization, API/session governance, POS register/shift/sale operations, immutable payment/receipt evidence, catalog and inventory controls, operational reporting and reconciliation, guarded POS workspaces, and a bounded secure installation-readiness foundation covering runtime, configuration, filesystem, and governed release artifact identity/integrity prerequisites.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint172 begins only after Sprint171 canonical reconciliation. Select the smallest material P0/P1 business-completeness or production-readiness gap and reuse canonical owners. No artifact download/extraction, installer exposure, migration execution, updater activation, deployment, or operational activation is pre-authorized.

Author by Lab | zefry

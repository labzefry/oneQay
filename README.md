# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint169 — Secure Installation Readiness Foundation**.

- Canonical engineering commit: `2a53d9db9547340bd4d791b34fabb80ec600fc8b`
- Engineering PR: #756
- Final engineering head: `5d2b27404352da7f81723f08977de34aef00faf7`
- Sprint169 regression `34936197402`: successful
- Governance `34936197759`: successful
- PHP Foundation `34936197571`: successful
- M7.1 `34936197394`: successful
- Engineering envelope: 3 paths, SHA-256 `2bfeedcabc1a09617876a6ce0719cb31a3246c157ad78472feda5db18f9c4fd1`
- Reconciliation envelope: 6 paths, SHA-256 `7f6b2b61e87d8891dc3c4d1a047ac92f9209d3286c445e77518f796179ce4d93`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint169 — Secure installation readiness foundation

Sprint169 establishes the first executable canonical installation-readiness owner while remaining strictly source-only and non-destructive. It performs deterministic fail-closed checks for supported PHP runtime, required extensions, required environment configuration, application-key readiness, production debug posture, and HTTPS production URL posture.

The readiness result is redacted and never returns supplied database credentials. Missing or placeholder configuration, unsupported runtime, missing extensions, production debug mode, or insecure production URL prevents readiness.

No installer route is exposed. No `.env` mutation, administrator bootstrap, migration execution, permission provisioning, deployment, Technical Preview/Production activation, durable-target selection, producer dispatch, runtime allowlist widening, Final Shift Close activation, or updater activation is authorized by Sprint169.

## Product progression

The canonical product chain includes tenant isolation, deny-by-default authorization, API/session governance, POS register/shift/sale operations, immutable payment/receipt evidence, catalog and inventory controls, operational reporting and reconciliation, guarded POS workspaces, and now the first bounded secure installation-readiness source foundation.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint170 starts only after Sprint169 canonical reconciliation. Select the smallest material P0/P1 business-completeness or production-readiness gap and reuse canonical owners. Installation work must remain bounded and must not infer deployment or operational activation authority.

Author by Lab | zefry

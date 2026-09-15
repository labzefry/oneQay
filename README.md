# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint170 — Installation Filesystem Readiness**.

- Canonical engineering commit: `aa46ad0752f8ae2da145eb0c0f6324a3c5be6f25`
- Engineering PR: #758
- Final engineering head: `00acde00e301f3f8aa3bf83b6b04f864d71d0dcd`
- Sprint170 regression `34944847584`: successful
- Governance `34944847461`: successful
- PHP Foundation `34944847737`: successful
- M7.1 `34944847844`: successful
- Engineering envelope: 3 paths, SHA-256 `7a86c9fbe8d4e87bbcdc3bf72ad618649d9d2cebfe20e2eba51f841ef685b877`
- Reconciliation envelope: 6 paths, SHA-256 `76de10f095cf53b630f96da884ebf6c1f4e581c4dc0f773312415c7788669b98`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint170 — Installation filesystem readiness

Sprint170 extends the canonical installation-readiness owner with deterministic, fail-closed verification that the minimum Laravel runtime directories required by oneQay are present and writable: `bootstrap/cache`, `storage/framework/cache`, `storage/framework/sessions`, `storage/framework/views`, and `storage/logs`.

The check is deliberately read-only. It does not attempt `chmod`, `chown`, directory creation, environment mutation, migration execution, administrator bootstrap, or any other installation-side mutation. Missing or non-writable runtime paths prevent readiness while returning only non-secret relative-path evidence.

Existing Sprint169 readiness for PHP runtime, extensions, required configuration, application-key posture, production debug posture, HTTPS, and secret redaction remains preserved.

## Product progression

The canonical product chain includes tenant isolation, deny-by-default authorization, API/session governance, POS register/shift/sale operations, immutable payment/receipt evidence, catalog and inventory controls, operational reporting and reconciliation, guarded POS workspaces, and now a bounded secure installation-readiness foundation that also qualifies the required filesystem write surface.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint171 begins only after Sprint170 canonical reconciliation. Select the smallest material P0/P1 business-completeness or production-readiness gap and reuse canonical owners. Installer work must remain bounded; package/application prerequisites may be evaluated next, but no Sprint171 objective is preselected and no deployment or operational activation authority is implied.

Author by Lab | zefry

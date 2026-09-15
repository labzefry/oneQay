# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint172 — Installation Database Configuration Compatibility Readiness**.

- Canonical engineering commit: `636a07130650f2d3119d450f35cfcfaf2868898e`
- Engineering PR: #764
- Final engineering head: `ab9716dc64c77a69da2c20fbafcc800d1114ef93`
- Sprint172 regression `34979406742`: successful
- Governance `34979406633`: successful
- PHP Foundation `34979406379`: successful
- M7.1 `34979406335`: successful
- Engineering envelope: 3 paths, SHA-256 `c0a1726ad384717f91889af2f2ce0f433cfdae7a64b3be62dc6fadde16104d16`
- Reconciliation envelope: 6 paths, SHA-256 `d43b48ac559bdee72e785c9187f4dec04bdce55be84d76baa0d76d28b5a6f304`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint172 — Database configuration compatibility readiness

Sprint172 corrects a material installation-readiness mismatch: the application consumes canonical `ONEQAY_DB_*` configuration, while preflight previously required stale `DB_*` keys. The canonical readiness owner now validates the configuration names the application actually uses and accepts deterministic observed database compatibility facts before any future database execution work.

The readiness contract requires configured `mysql`, observed MySQL or MariaDB engine identity, valid server-version form, `utf8mb4`, UTC / `+00:00`, an `empty` or `recognized` schema state, and least-privilege evidence. Missing or incompatible facts fail closed without echoing credentials or arbitrary server evidence.

Existing Sprint169–Sprint171 runtime, configuration, HTTPS, filesystem, governed release manifest, artifact integrity, and redaction checks remain preserved. Sprint172 does not open a database connection, mutate schema/configuration, execute migrations, provision credentials, expose the installer, activate the updater, or deploy anything.

## Product progression

The canonical product chain includes tenant isolation, deny-by-default authorization, API/session governance, POS register/shift/sale operations, immutable payment/receipt evidence, catalog and inventory controls, operational reporting and reconciliation, guarded POS workspaces, and a bounded secure installation-readiness foundation covering runtime, canonical configuration, filesystem, governed release artifact identity/integrity, and deterministic database compatibility prerequisites.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint173 begins only after Sprint172 canonical reconciliation. Select the smallest material P0/P1 business-completeness or production-readiness gap and reuse canonical owners. No database execution, migration/seeder execution, administrator creation, environment mutation, installer exposure, updater activation, deployment, or operational activation is pre-authorized.

Author by Lab | zefry

# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint174 — Installation Release Compatibility Policy Readiness**.

- Canonical engineering commit: `3937cfc56d2615262160c9592d204715eb80ec89`
- Engineering PR: #773
- Final engineering head: `6db7c94a7eef99f5f1281f11c524a7d7e32d1968`
- Sprint174 regression `34996139756`: successful
- Governance `34996139846`: successful
- PHP Foundation `34996138931`: successful
- M7.1 `34996140085`: successful
- Engineering envelope: 3 paths, SHA-256 `1f4a5333d32f79e57ac51d8c9ea8b1a1b3b0342d58b6439fbfe127d3a235aa91`
- Reconciliation envelope: 6 paths, SHA-256 `c620402ddfb186f47fd0994f5751c3453811f08ca870400478442a1f9095075b`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint174 — Release compatibility policy readiness

Sprint174 extends the governed release manifest so installation readiness cannot accept an immutable artifact without the compatibility metadata required by the release contract. The manifest now requires a bounded `compatibility_policy` covering release version, build/provenance reference, supported-current-version range, deployment compatibility, rollback compatibility, public-bootstrap/layout compatibility, and release-notes reference.

Compatibility policy validates fail closed. Semantic versions and version ranges must be bounded and ordered; references must be safe; deployment and public-layout values must use controlled policy tokens; rollback compatibility remains constrained to `NO_SCHEMA_CHANGE_ROLLBACK_SAFE`. Missing or malformed policy also prevents artifact-integrity readiness, and failure output does not echo untrusted policy values.

Existing secure environment, application-key, production-debug/HTTPS, filesystem-write, governed runtime requirements, artifact identity/integrity, deterministic database compatibility, and redaction checks remain preserved. Sprint174 does not publish, download, extract, install, migrate, provision, expose, update, deploy, or activate anything.

## Product progression

The product combines tenant/security/API/POS operational foundations with a canonical secure installation-readiness owner covering governed release identity, runtime requirements, compatibility policy, canonical configuration, filesystem write surfaces, immutable artifact identity/integrity, and deterministic database compatibility prerequisites.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint175 begins only after Sprint174 canonical reconciliation. Select the smallest material P0/P1 business-completeness or production-readiness gap and reuse canonical owners. Host/platform capability readiness remains a candidate but is not preselected. No artifact transport/extraction, database execution, migration/seeder execution, administrator creation, environment mutation, installer exposure, updater activation, deployment, or operational activation is pre-authorized.

Author by Lab | zefry

# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint173 — Installation Release Runtime Requirements Readiness**.

- Canonical engineering commit: `933b06d0790834fb830ca9a55b443db69eca65f0`
- Engineering PR: #771
- Final engineering head: `494503122ebf273fbfcc0791c5afca0e24bb5b29`
- Sprint173 regression `34991613421`: successful
- Governance `34991613429`: successful
- PHP Foundation `34991613314`: successful
- M7.1 `34991613882`: successful
- Engineering envelope: 3 paths, SHA-256 `83593746e455ea2aa7353482e6b1c35faac4c740b2b9bb897e93bcc71fc1748b`
- Sprint169 successor-compatibility correction: PR #769, squash `e28c2b01aa76ad770896c6eb21b398e8cb188fdb`
- Reconciliation envelope: 6 paths, SHA-256 `319054712753f696394ff98959688230e9091065fcdae394f7231bd9b6a01ab6`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint173 — Release runtime requirements readiness

Sprint173 moves PHP runtime compatibility ownership into the governed release manifest. `runtime_requirements.php_min` and `runtime_requirements.php_extensions` now define the release-specific PHP minimum and extension set used by canonical installation readiness instead of an installer-owned hardcoded extension list.

Runtime requirements are validated fail-closed: malformed PHP versions, empty or oversized extension sets, invalid extension identifiers, case-insensitive duplicates, missing requirements, or unsatisfied observed runtime facts prevent readiness. Failure output does not echo untrusted runtime values.

Existing secure environment, application-key, production-debug/HTTPS, filesystem-write, governed release artifact identity/integrity, database compatibility, and redaction checks remain preserved. Sprint173 does not download/extract artifacts, open a database connection, mutate configuration/schema, execute migrations/seeders, create an administrator, expose the installer, activate the updater, or deploy anything.

Superseded PR #768 correctly exposed stale Sprint169 workflow coupling to `REQUIRED_EXTENSIONS`. Workflow-only correction PR #769 repaired that historical preservation invariant before Sprint173 was rebuilt and qualified through PR #771.

## Product progression

The product combines tenant/security/API/POS operational foundations with a canonical secure installation-readiness owner covering governed release runtime requirements, canonical configuration, filesystem write surfaces, immutable artifact identity/integrity, and deterministic database compatibility prerequisites.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint174 begins only after Sprint173 canonical reconciliation. Select the smallest material P0/P1 business-completeness or production-readiness gap and reuse canonical owners. No artifact transport/extraction, database execution, migration/seeder execution, administrator creation, environment mutation, installer exposure, updater activation, deployment, or operational activation is pre-authorized.

Author by Lab | zefry

# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint175 — Installation Governed Host Platform Requirements Readiness**.

- Canonical engineering commit: `6357d883fe04ec0515f9d21c6adc0ee907787cde`
- Engineering PR: #775
- Final engineering head: `7eebbdb2a71b4cb35dafac58e6df2c955866264c`
- Sprint175 regression `34998600504`: successful
- Governance `34998600362`: successful
- PHP Foundation `34998600326`: successful
- M7.1 `34998600367`: successful
- Engineering envelope: 3 paths, SHA-256 `3b06f39902fda4e43096b622cffad62ad4308652f760a300c44d5a54616ef8e0`
- Reconciliation envelope: 6 paths, SHA-256 `be033502c6e72d211415751966e6dc48e3453ce6fae6003d57b3da807cee1460`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint175 — Governed host/platform requirements readiness

Sprint175 moves installer Step 2 host/platform requirements into the governed release manifest. The release contract now defines supported OS families and web-server interfaces, minimum memory/execution-time/free-disk requirements, plus the required capability set for HTTPS, DNS, time synchronization, outbound allowlisting, scheduler, archive support, temporary-directory readiness, and required tools.

The canonical readiness owner evaluates deterministic observed host facts and fails closed when requirements are missing, malformed, unsupported, or insufficient. It does not probe DNS/network services, execute shell commands, mutate cron/scheduler/filesystem/configuration, install packages, or extract artifacts. Failure output does not echo untrusted host values.

Existing secure environment, application-key, production-debug/HTTPS, filesystem-write, governed PHP/runtime requirements, artifact identity/integrity, release compatibility policy, database compatibility, and redaction checks remain preserved.

## Product progression

The product combines tenant/security/API/POS operational foundations with a canonical secure installation-readiness owner covering governed release identity, runtime requirements, host/platform requirements, compatibility policy, canonical configuration, filesystem write surfaces, immutable artifact identity/integrity, and deterministic database compatibility prerequisites.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint176 begins only after Sprint175 canonical reconciliation. Select the smallest material P0/P1 business-completeness or production-readiness blocker proven by live canonical evidence. No host probing, artifact transport/extraction, production database execution, migration/seeder execution, administrator creation, environment mutation, installer exposure, updater activation, deployment, or operational activation is pre-authorized.

Author by Lab | zefry

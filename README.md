# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint180 — Merchant Initial Context Assisted Sign-In Foundation**.

- Canonical engineering commit: `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`
- Engineering PR: #787
- Final engineering head: `e4844d47cc99c8f655c09e358b224f45c29513e1`
- Exact-head surfaced qualification: 62/62 successful
- Engineering envelope: 4 paths, SHA-256 `2c122014511daaeb8ca1d5cbd2ee4bb184733ed1154e08c8e0000f3b758c9c6c`
- Reconciliation envelope: 6 paths, SHA-256 `eb9b36e214455c09714f9d03f034e85aec06506d43b64be314b2b95b00b4f41b`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint180 — Assisted initial merchant sign-in

Sprint180 removes manual entry of opaque tenant, identity, organization, outlet, and device IDs from the initial merchant sign-in experience.

The server supplies only the exact non-secret context fields already present in the guarded installation grant. `provisioning_id` is never serialized to the browser. Password verification, MFA, session authority, tenant isolation, organizational verification, and the `/pos` transition continue to use the existing canonical first-party authentication stack.

If the exact context is absent, malformed, or not permitted by the Local/Test/CI + persistence + session-control gates, merchant sign-in fails closed to the Foundation posture.

## Product progression

oneQay now combines governed installation readiness, atomic POS-ready merchant bootstrap, first-party session security, and an initial merchant login journey that no longer requires copying internal platform identifiers.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint181 begins with bounded discovery of the smallest material P0/P1 blocker still preventing a complete merchant journey. No operational authority is implied or pre-authorized.

Author by Lab | zefry

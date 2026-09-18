# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented business-management platform built with Modular Monolith First, Clean Architecture, DDD, first-class tenant context, deny-by-default authorization, module-owned schema, and fail-closed engineering controls.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

The latest completed engineering sprint is **Sprint179 — Merchant Bootstrap Initial POS Operation Authorization Foundation**.

- Canonical engineering commit: `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`
- Engineering PR: #785
- Final engineering head: `94d1f2937a3ab71803738c2a2408170e63b6fcf1`
- Exact-head surfaced qualification: 61/61 successful
- Engineering envelope: 4 paths, SHA-256 `33206447002d40b489742fdb7b0c50670705400d16c184e352aa64aeb1534feb`
- Reconciliation envelope: 6 paths, SHA-256 `72d21048381af6505f8b6315141efef93f909e407d54377e3726d49f0c38ccff`

For the full project state, use [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md) as the canonical human-readable source of truth.

## Sprint179 — POS-ready merchant bootstrap authorization

Sprint179 closes the gap where a bootstrapped merchant could authenticate but still lacked the durable outlet/device access and POS permissions required by the existing Operations Hub.

The final implementation keeps Sprint176 atomic bootstrap semantics authoritative, then wraps them in one outer transaction that records exact outlet/device access, creates a separate least-privilege initial POS operator role, grants only the first-operation POS permissions, and assigns that role at the exact bootstrapped device.

The protected control-administrator role is not widened. Sale void, refund, Final Shift Close, Production activation, migration execution, deployment, and updater activation remain outside Sprint179.

## Product progression

oneQay now combines governed installation readiness, atomic merchant-context bootstrap, guarded first-party browser entry, durable exact-device access, and a minimally authorized path into real POS operations under Local/Test/CI qualification.

## Operational status remains intentionally gated

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; durable activation target `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview and Production `NOT_AUTHORIZED`; updater `INACTIVE`.

## Next engineering position

Sprint180 begins with bounded discovery of the smallest material P0/P1 blocker still preventing a complete merchant journey. No operational authority is implied or pre-authorized.

Author by Lab | zefry

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management  
**Repository owner / attribution:** Lab | zefry  
**Default branch:** `main`  
**Canonical engineering checkpoint:** Sprint147  
**Canonical engineering commit:** `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`  
**Latest engineering PR:** #711  
**Status date:** 2026-09-13

This file is the canonical human-readable project status. Machine-readable operational gates under `ops/final-shift-close/` remain authoritative.

## Current state

oneQay continues on the accepted Modular Monolith First / Clean Architecture / DDD baseline with first-class tenant context, deny-by-default authorization, Laravel/PHP, Vue 3 + Inertia + Vite, and a MySQL-compatible database.

Sprint147 is closed. PR #711 was squash merged at `50a3ba99b8f5628381d9df63f4f6a0e1020d550a` from exact authorized head `513d95dbb4d5ab95a8f6c3282f8911cf339a9697`. Exact-head CI was 31/31 successful and Product Owner authority run `34752002084` succeeded.

Sprint147 objective: `CANONICAL_CONTROL_PLANE_THROTTLE_REJECTION_METADATA_IDENTITY_RESPONSE_HARDENING_REGRESSION`.

The Final Shift Close throttle-response hardener now requires canonical request identity, the exact canonical per-route `X-RateLimit-Limit`, exact `X-RateLimit-Remaining: 0`, and non-empty decimal `Retry-After` plus `X-RateLimit-Reset`. Missing or malformed rejection metadata remains framework-owned. The Sprint144 named-route fixture was updated only for successor compatibility with the canonical rejection metadata shape proved by Sprint142.

Engineering envelope: six paths. Frozen SHA-256: `ffd176da808eda16fccdf0375fcae2fd5bcc1cfc4b931aca6492ca31eb9b1d40`.

## Operational NO-GO

- migration #27: `NOT_EXECUTED`
- permission provisioning: `NONE`
- feature activation: `INACTIVE`
- deployment authority: `NOT_GRANTED`
- Technical Preview: `NOT_AUTHORIZED`
- Production: `NOT_AUTHORIZED`
- updater: `INACTIVE`
- durable target: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`
- selected target: `null`

No operational migration, permission provisioning, activation, runtime-token provisioning, durable-target activation, operational manifest/DB invocation, deployment/release, preview, production, or updater action occurred in Sprint147.

## Next position

The next engineering position is **Sprint148 bounded discovery from canonical post-Sprint147**. No Sprint148 objective, implementation, or source envelope is preselected.

## Documentation rule

Every material sprint records Purpose / Why, Objective / Gap, What changed, Evidence / Qualification, Operational boundaries / NO-GO, and Next position. Root summaries must be reconciled after closure, and the just-closed sprint workflow must become successor-compatible while preserving its owned historical regression.

Author by Lab | zefry

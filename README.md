# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint197 — Atomic Technical Preview Activation Health Rollback**.

- Canonical engineering commit: `0c74e535cfeb281edaff5a2967752baee0db5227`
- Engineering PR: #825
- Final engineering head: `027c84bb282aefd314d8da3d270c925ba5837841`
- Exact-head qualification: 85/85 successful
- Dedicated Sprint197 run `35428627303`: SUCCESS
- Exact-head M7.5 run `35428627419`: SUCCESS
- Engineering envelope SHA-256: `08a73cc8338a51da3ed294b1a6c6a62986e0527100213414036d55b839b10a44`
- Reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint197 capability

oneQay now has a guarded atomic Synthetic Technical Preview activation executor. After exact authority/token/preflight validation, it can change only the Preview enable flag, immediately qualify liveness/readiness/Preview surface/runtime/session health, and automatically restore the original environment byte-for-byte if health fails. Success and rollback evidence are private and digest-bound.

This is source capability, not live activation authority.

## Product progression

Governed release → installation readiness → secure configuration → DB verification → pending config → sealed handoff → promotion request → authority qualification → execution readiness → atomic promotion → post-promotion verification → installation completion → Technical Preview activation request → activation authority qualification → activation execution readiness → target-environment preflight → guarded atomic activation + health rollback capability.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; canonical Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains blocked; selected target remains `null`; no producer dispatch occurred.

## Next

Sprint198 starts from fully reconciled Sprint197 and selects the next material P0/P1 business-completion blocker without micro-splitting the lifecycle or implicitly granting operational authority.

Author by Lab | zefry

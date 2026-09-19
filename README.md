# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint196 — Technical Preview Target Environment Preflight**.

- Canonical engineering commit: `9948aeadc562b6188453872f09bd3afb754dd0c0`
- Engineering PR: #823
- Final engineering head: `afc443420ddef9283c7f575ce311b97fc026e658`
- Exact-head qualification: 84/84 successful
- Dedicated Sprint196 run `35426685626`: SUCCESS
- Exact-head M7.5 run `35426685578`: SUCCESS
- Engineering envelope SHA-256: `bb875f82e7cfc7a8347bf6d92f916f6124eb5b095e27f9582189ebec5c74e904`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint196 capability

oneQay now prepares the complete bounded single-instance Synthetic Technical Preview session envelope and can run a guarded read-only target-environment preflight over the exact HTTPS host. Successful preflight produces private non-secret evidence and stops at **PREFLIGHT PASSED / NOT ACTIVATED**.

## Product progression

Governed release → installation readiness → secure configuration → DB verification → pending config → sealed handoff → promotion request → authority qualification → execution readiness → atomic promotion → post-promotion verification → installation completion → Technical Preview activation request → activation authority qualification → activation execution readiness → target-environment preflight.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains blocked and selected target remains `null`.

## Next

Sprint197 starts from fully reconciled Sprint196 and verifies the next material blocker after target preflight. Technical Preview activation remains a separate governed action and is not pre-authorized.

Author by Lab | zefry

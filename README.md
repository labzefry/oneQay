# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint194 — Guarded Technical Preview Activation Request**.

- Canonical engineering commit: `7e3e58d7be012ee5d797acb879cfb9f9a1e829dc`
- Engineering PR: #819
- Final engineering head: `af1027156209977a75d24f54fec031f86bedf8f6`
- Exact-head qualification: 82/82 successful
- Canonical M7.5 main-push run `35424129329`: SUCCESS
- Engineering envelope SHA-256: `a85b8ceffbf533ea5f3aff7555dba90129c2c851bb8d9bb8ed427ce2e572f804`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint194 capability

oneQay can now create a private, exact-bound request for separate Technical Preview operational approval only after installation configuration is complete. The request is tamper-evident, replay-safe, operator-confirmed, packaged by the governed release, and remains explicitly **PENDING APPROVAL / NOT AUTHORIZED**.

## Product progression

Governed release → installation readiness → secure configuration → DB verification → pending config → sealed handoff → promotion request → authority qualification → execution readiness → atomic promotion → post-promotion verification → installation completion handoff → Technical Preview activation request.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; selected durable target remains `null`.

## Next

Sprint195 begins from fully reconciled Sprint194 and targets the next material blocker toward safe application operation without implicitly granting operational activation authority.

Author by Lab | zefry

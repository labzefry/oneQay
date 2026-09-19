# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Latest completed engineering sprint: **Sprint195 — Technical Preview Activation Authority Readiness**.

- Canonical engineering commit: `022b1667ce25a9f4b86a71c95b2c59ad37793d4e`
- Engineering PR: #821
- Final engineering head: `11ec883590d05d28aeeac08a0286a3e149db00e7`
- Exact-head qualification: 83/83 successful
- Dedicated Sprint195 run `35425205497`: SUCCESS
- Exact-head M7.5 run `35425205248`: SUCCESS
- Engineering envelope SHA-256: `b6b06d99b300fa67cc6341f098eff73c430b11c6d98444ebb75a352d3e7589c2`

See `PROJECT_MANIFEST.md` for canonical project state.

## Sprint195 capability

oneQay can now validate an exact-bound, separately provisioned Synthetic Technical Preview activation authority and seal private durable execution-readiness evidence. Qualification requires an out-of-band one-time token, preserves the Sprint85 target-environment preflight contract, and stops at **QUALIFIED / READY / NOT ACTIVATED**.

## Product progression

Governed release → installation readiness → secure configuration → DB verification → pending config → sealed handoff → promotion request → authority qualification → execution readiness → atomic promotion → post-promotion verification → installation completion handoff → Technical Preview activation request → Technical Preview authority qualification → activation execution readiness.

## Operational boundary

Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close feature activation `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target remains blocked and selected target remains `null`.

## Next

Sprint196 begins after canonical Sprint195 reconciliation and targets the next material blocker toward safe first application operation. Target-environment preflight and any later activation execution remain separately governed and require live-repository proof before selection.

Author by Lab | zefry

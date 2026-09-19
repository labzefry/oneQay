# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint208
**Objective:** `DURABLE_STAGING_DEPLOYMENT_AUTHORITY_BINDING_FOUNDATION`
**Canonical engineering commit:** `20e835262f8163d45101ab00a818881421085d2e`
**Engineering PR:** #849 — `Sprint208: bind durable staging deployment authority`
**Final engineering head:** `67aa2e73a0433585a34b76df4c7bec97b578aefe`
**Exact-head qualification:** 87/87 successful
**Sprint208 authority binding qualification:** run `35460395244` — SUCCESS
**Sprint207 operator-plan preservation:** run `35460395614` — SUCCESS
**Sprint206 handoff preservation:** run `35460396295` — SUCCESS
**M7.1 qualification:** run `35460395688` — SUCCESS
**Governance qualification:** run `35460395483` — SUCCESS
**PHP Foundation qualification:** run `35460396111` — SUCCESS
**Product Owner merge authority:** `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 12 paths — `5852e772a37b334987cdf5bcca0327a4e1b90d18a6affa02d6a9c3f44b6bf616`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint207 reconciliation `4f4b1b50726d1a45133ab2075ff57c3764dc3c23`

> `20e835262f8163d45101ab00a818881421085d2e` is the permanent canonical Sprint208 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint208 closes the source-side governance gap between Sprint207 deterministic operator deployment planning and separately issued operational authority. It gives oneQay a deterministic request → external authority → token verification → qualified exact target chain without performing deployment.

## Delivered capability

- Added `DURABLE_STAGING_DEPLOYMENT_AUTHORITY_BINDING_CONTRACT.json`.
- Added `durable-staging-operator-target-candidate.schema.json`.
- Added deterministic deployment-authority request schema and preparer.
- Added strict external deployment-authority schema and qualifier.
- Approval token is consumed from STDIN and compared only by SHA-256; it is never emitted to output.
- Authority binds request hash, target-descriptor hash, environment ID, release ID, source commit, and artifact SHA-256.
- Authority lifetime is at most 900 seconds and must be current during qualification.
- Qualified targets carry exact request/authority evidence into the Sprint207 planner.
- Sprint207 planner now reconstructs target-candidate identity and rejects authority expiry or target drift.
- No deployment/runtime mutation occurs.

## Qualification evidence

- Final head `67aa2e73a0433585a34b76df4c7bec97b578aefe` completed 87/87 PR-triggered workflows successfully.
- Dedicated Sprint208 run `35460395244` proved request determinism, token validation, expiry enforcement, authority scope denial, target binding, qualified-target emission, and downstream Sprint207 plan compatibility.
- Sprint207 preservation run `35460395614` and Sprint206 preservation run `35460396295` succeeded.
- Engineering PR #849 squash merged at `20e835262f8163d45101ab00a818881421085d2e`.
- Post-merge comparison confirmed exactly one squash commit above Sprint207 reconciliation `4f4b1b50726d1a45133ab2075ff57c3764dc3c23`.

## Operational NO-GO

Machine-readable state remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch `NOT_PERFORMED`.

Sprint208 creates no real target and grants no real operational deployment authority.

## Next position

The next prerequisite is operational rather than another assumed source sprint: identify/materialize a real isolated non-production durable-staging environment, prepare its exact target candidate, issue separate authority for the generated request, then execute the resulting Sprint207 operator plan externally. Proven deployment/readback/health/rollback evidence is required before protected attestation producer → ingestion → target selection.

Author by Lab | zefry

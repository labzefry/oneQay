# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint209
**Objective:** `DURABLE_STAGING_DEPLOYMENT_EVIDENCE_BINDING_FOUNDATION`
**Canonical engineering commit:** `55b652f8b62e05cb8254ec74bb10f2e507abb641`
**Engineering PR:** #852 — `Sprint209: bind durable staging deployment execution evidence`
**Final engineering head:** `3372abb10eb5f883ff410d25e0defbaad827207c`
**Exact-head qualification:** 89/89 successful
**Sprint209 deployment evidence binding qualification:** run `35462884340` — SUCCESS
**Sprint113 protected attestation producer preservation:** run `35462884439` — SUCCESS
**Sprint208 authority binding preservation:** run `35462885132` — SUCCESS
**Sprint207 operator-plan preservation:** run `35462884363` — SUCCESS
**M7.1 qualification:** run `35462884334` — SUCCESS
**Governance qualification:** run `35462884291` — SUCCESS
**PHP Foundation qualification:** run `35462884380` — SUCCESS
**Product Owner merge authority:** `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 9 paths — `ebff1a00d0b06f16925cc0f0849b3c7cd38216ccccd659f0447f307d71face10`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint208 reconciliation `4c3d176c6faa8785a1baee46ac2534862171dbc0`

> `55b652f8b62e05cb8254ec74bb10f2e507abb641` is the permanent canonical Sprint209 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint209 closes the source-side trust gap between external durable-staging deployment execution and the existing protected runtime attestation producer. A runtime readiness endpoint can no longer qualify by itself; exact deployment execution evidence must first bind the target to the Sprint207 plan and Sprint208 authority.

## Delivered capability

- Added `DURABLE_STAGING_DEPLOYMENT_EVIDENCE_BINDING_CONTRACT.json`.
- Added `tools/deployment/durable-staging-deployment-evidence.schema.json`.
- Added `tools/qualify-durable-staging-deployment-evidence.php`.
- Deployment evidence is bound to exact environment ID, runtime class, running source commit, running artifact SHA-256, deployment-plan fingerprint, deployment-authority SHA-256, and deployment-request identity.
- Evidence must prove preflight, immutable extraction, public-document-root verification, external configuration binding, provenance readback, read-before-write/read-after verification, non-mutating health attestation, previous-release preservation, and rollback-path verification.
- The protected durable-runtime attestation workflow requires protected deployment evidence before any runtime endpoint request.
- Runtime attestation source/artifact identity must match the protected deployment-evidence binding.
- Existing Sprint112 ingestion remains unchanged and continues to trust only the canonical protected producer workflow and exact success evidence status.
- No operational mutation is performed by Sprint209 source.

## Qualification evidence

- Final head `3372abb10eb5f883ff410d25e0defbaad827207c` completed 89/89 PR-triggered workflows successfully.
- Dedicated Sprint209 run `35462884340` proved exact 9-path scope, strict evidence qualification, target/source/artifact/plan/authority mismatch rejection, NO-GO preservation, and producer gate ordering.
- Sprint113 preservation `35462884439`, Sprint208 preservation `35462885132`, and Sprint207 preservation `35462884363` succeeded.
- Engineering PR #852 squash merged at `55b652f8b62e05cb8254ec74bb10f2e507abb641`.
- Post-merge comparison confirmed exactly one squash commit above Sprint208 reconciliation `4c3d176c6faa8785a1baee46ac2534862171dbc0`.

## Operational NO-GO

Machine-readable state remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch `NOT_PERFORMED`.

Sprint209 does not create or deploy a target and does not dispatch the protected producer.

## Next position

The next prerequisite is operational: materialize a real isolated non-production durable-staging target under separate authority, execute the exact Sprint207 operator plan, create deployment evidence conforming to Sprint209, configure that evidence and exact bindings in the protected GitHub Environment, then dispatch the protected attestation producer. Only successfully produced and ingested real attestation evidence may proceed toward target selection.

Author by Lab | zefry

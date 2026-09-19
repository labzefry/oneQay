# oneQay Roadmap

**Roadmap checkpoint:** Sprint208 closed canonically
**Canonical engineering baseline:** `20e835262f8163d45101ab00a818881421085d2e`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint208 horizon

Sprint208 closed `DURABLE_STAGING_DEPLOYMENT_AUTHORITY_BINDING_FOUNDATION`.

The repository now has an auditable path from governed durable-staging artifact/handoff through exact-target planning to a request-bound, short-lived, separately issued deployment authority. The authority chain remains fail-closed and non-operational until an external operator executes the plan on a real isolated target.

Engineering PR #849 qualified at 87/87 on final head `67aa2e73a0433585a34b76df4c7bec97b578aefe` and squash merged at `20e835262f8163d45101ab00a818881421085d2e`.

Engineering envelope SHA-256: `5852e772a37b334987cdf5bcca0327a4e1b90d18a6affa02d6a9c3f44b6bf616`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Production-readiness progression

Sprint203 supplied the bounded merchant-core staging bridge, Sprint204 authenticated durable-runtime readiness, Sprint205 the governed durable-staging artifact, Sprint206 validated handoff, Sprint207 deterministic operator planning, and Sprint208 exact request-bound deployment authority qualification.

Source packaging, handoff, plan generation, and authority binding are no longer the immediate blocker. A real non-production durable-staging environment remains required.

## Operational boundary

Canonical state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`; producer dispatch not performed.

## Next material horizon

Under separate operational authority, materialize the real durable-staging target, generate its exact authority request, qualify matching short-lived authority, execute the operator plan externally, and prove running source/artifact identity, configuration readback, non-mutating health, and rollback.

Only after real target qualification should protected attestation producer → ingestion → target selection run. Open another engineering sprint only if targeted discovery or real deployment exposes a concrete missing source capability.

Author by Lab | zefry

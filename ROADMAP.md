# oneQay Roadmap

**Roadmap checkpoint:** Sprint210 closed canonically
**Canonical engineering baseline:** `bf00980add7e557e3fccf6683f916ac5389feffe`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint210 horizon

Sprint210 closed `DURABLE_STAGING_DEPLOYMENT_EVIDENCE_PROVENANCE_CONTINUITY`.

Deployment evidence qualified before producer execution is now carried as exact non-secret provenance through producer publication and trusted ingestion. Deterministic candidate fingerprints bind deployment evidence, plan, authority, attestation, and provenance.

Engineering PR #854 qualified at 96/96 on final head `18f32c5746a7fc08b49d432a6a63e01d8f14e059` and squash merged at `bf00980add7e557e3fccf6683f916ac5389feffe`.

Engineering envelope SHA-256: `0525c55ff45baf2893a22983a4ea53e50f1c195d92a49efc2a41ce92d2749205`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Production-readiness progression

Sprint203 supplied the merchant-core staging bridge, Sprint204 runtime readiness, Sprint205 governed durable artifact, Sprint206 validated handoff, Sprint207 deterministic operator planning, Sprint208 request-bound deployment authority, Sprint209 deployment-execution evidence qualification, and Sprint210 provenance continuity into trusted ingestion.

The remaining blocker is no longer an unproven source-chain gap. It is real isolated durable-staging execution under separate operational authority.

## Next material horizon

Materialize the real durable-staging target, execute the exact governed deployment plan, produce real deployment evidence, run the protected producer, and ingest real attestation evidence. Open another engineering sprint only if actual execution proves another missing source capability.

Author by Lab | zefry

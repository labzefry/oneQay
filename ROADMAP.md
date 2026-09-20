# oneQay Roadmap

**Roadmap checkpoint:** Sprint211 closed canonically
**Canonical engineering baseline:** `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint211 horizon

Sprint211 closed `DURABLE_STAGING_OPERATOR_ARTIFACT_PUBLICATION`.

A deterministic durable-staging release is now published from canonical main as an operator-retrievable GitHub Actions bundle after reproducibility, manifest validation, secret checks, and Sprint206 handoff validation.

Engineering PR #857 qualified at 94/94 on final head `276acc9ab8fe61cb65632bce8e5f2dda9d411fc8` and squash merged at `e37300d5d1be6727cdb5d818b6365c6429f2af9d`.

Published release:
- `durable-staging-e37300d5d1be`
- artifact SHA-256 `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`
- manifest SHA-256 `ef968562f76801e425d02c33d3ecfc13556f271f05bff1ff9441c5a147b588f0`
- publication run `35487670967`
- Actions artifact ID `10597712890`

## Production-readiness progression

Sprint203 supplied the merchant-core staging bridge, Sprint204 runtime readiness, Sprint205 reproducible durable artifact, Sprint206 validated handoff, Sprint207 deterministic operator planning, Sprint208 request-bound deployment authority, Sprint209 deployment-execution evidence qualification, Sprint210 provenance continuity, and Sprint211 operator artifact publication.

## Next material horizon

Issue #856 is now the operational path. Materialize the real target candidate, obtain separate deployment authority, generate the exact operator plan, execute it externally, produce deployment evidence, then run protected attestation and trusted ingestion.

Open another source sprint only if that real sequence exposes a concrete repository-side blocker.

Author by Lab | zefry

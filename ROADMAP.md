# oneQay Roadmap

**Roadmap checkpoint:** Sprint204 closed canonically
**Canonical engineering baseline:** `a5672b315a320092c6fa8cc74d984cb70f4e18ae`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint204 horizon

Sprint204 closed `DURABLE_STAGING_RUNTIME_READINESS_ATTESTATION_DELIVERY`.

The Sprint203 merchant-core staging bridge can now be paired with a canonical `durable-staging` runtime that exposes a deterministic, authenticated, secret-free readiness attestation for the existing governed durable-runtime qualification pipeline.

Engineering PR #841 qualified at 88/88 on final head `f849d3902c026105ae9e21088b45a05b6d71dcaa` and squash merged at `a5672b315a320092c6fa8cc74d984cb70f4e18ae`.

Engineering envelope SHA-256: `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Production-readiness progression

The source path now reaches the point where a real isolated non-production durable runtime can prove its identity, non-synthetic posture, durable data/session/auth/transaction/POS envelope, exact source/artifact provenance, and operational control capabilities without activating Final Shift Close.

This remains source readiness. No real target has yet been qualified or selected.

## Operational boundary

Canonical repository state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`; producer dispatch not performed.

## Next material horizon

The next meaningful step is external target realization and governed qualification, not another artificial source micro-sprint. A real `durable-staging` environment must supply durable persistence/session/authorization/transaction/POS behavior, exact running commit and artifact hash, authenticated configuration mutation with read-before-write/read-after, non-mutating health, and verified rollback. Only after those prerequisites and separate operational authority exist should the protected producer/ingestion/selection sequence run.

If real-environment qualification exposes a concrete missing source capability, address that gap as the next bounded engineering sprint; otherwise preserve the operational blocker honestly.

Author by Lab | zefry

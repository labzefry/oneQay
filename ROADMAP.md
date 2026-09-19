# oneQay Roadmap

**Roadmap checkpoint:** Sprint205 closed canonically
**Canonical engineering baseline:** `5d9826e96adfb31d1e9b9389d222db180f84935c`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint205 horizon

Sprint205 closed `GOVERNED_DURABLE_STAGING_RELEASE_ARTIFACT_FOUNDATION`.

The repository can now build a deterministic `durable-staging` artifact that contains application runtime dependencies, compiled assets, and the canonical migration source set #1–#27 while preserving migration execution and deployment as separately governed operations.

Engineering PR #843 qualified at 89/89 on final head `c5b560a03bfec152f2860e7612b18517fb75434b` and squash merged at `5d9826e96adfb31d1e9b9389d222db180f84935c`.

Engineering envelope SHA-256: `958492e789d583ddd73f803b5a82fa857e25692f372117492d04e25ac82a5a65`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Production-readiness progression

Sprint203 supplied the bounded merchant-core staging bridge, Sprint204 supplied authenticated readiness attestation, and Sprint205 supplied the governed deployable artifact needed to materialize the same exact source on an external durable staging runtime.

Technical Preview remains independent and no-schema-change.

## Operational boundary

Canonical state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`; producer dispatch not performed.

## Next material horizon

The next blocker is external environment realization, not another packaging abstraction. Provisioning/deployment must remain separately authorized. Once a real isolated `durable-staging` runtime exists, validate artifact/source identity, durable data/session/auth/transaction/POS behavior, configuration readback, health, and rollback. Only then may the protected producer/ingestion/selection chain be considered under separate operational authority.

If deployment discovery exposes a concrete missing repository capability, address that capability as the next bounded engineering sprint. Otherwise keep the operational blocker explicit rather than manufacturing source work.

Author by Lab | zefry

# oneQay Roadmap

**Roadmap checkpoint:** Sprint206 closed canonically
**Canonical engineering baseline:** `9fa3af317485fadd8844115260483c4926447695`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint206 horizon

Sprint206 closed `DURABLE_STAGING_DEPLOYMENT_HANDOFF_FOUNDATION`.

The repository now has a complete source-side path from durable-staging artifact construction to validated operator handoff. The handoff binds exact source/artifact identity, validates archive safety and migration source shape, and remains secret-free and non-mutating.

Engineering PR #845 qualified at 90/90 on final head `d8946bac37dd6a4c6b84f1a800ee1361f65aac23` and squash merged at `9fa3af317485fadd8844115260483c4926447695`.

Engineering envelope SHA-256: `2afad04ec60d7bce178795c8606922ee0dc38c7e672f16350902fe33b5760e3c`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Production-readiness progression

Sprint203 supplied the bounded merchant-core staging bridge, Sprint204 supplied authenticated runtime readiness, Sprint205 supplied the governed durable-staging artifact, and Sprint206 supplied validated operator handoff.

At this point, source packaging and handoff are no longer the blocker. The remaining step requires a real isolated non-production runtime and separate operational authority.

## Operational boundary

Canonical state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`; producer dispatch not performed.

## Next material horizon

Materialize the real `durable-staging` environment only under separate operational authority. Deploy the exact validated artifact, externalize runtime configuration and secrets, establish durable persistence/session/authorization/transaction/POS prerequisites, verify source/artifact provenance, health/readback/rollback, then run the protected readiness producer and downstream ingestion/selection sequence.

Do not create another source micro-sprint unless real environment realization exposes a concrete missing repository capability.

Author by Lab | zefry

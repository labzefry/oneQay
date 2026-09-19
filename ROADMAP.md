# oneQay Roadmap

**Roadmap checkpoint:** Sprint207 closed canonically
**Canonical engineering baseline:** `2cfc55cf3dde9304b713ba9fb6f70509dd4dafda`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint207 horizon

Sprint207 closed `DURABLE_STAGING_OPERATOR_DEPLOYMENT_PLANNING`.

The repository now carries a deterministic operator planning bridge from the Sprint206 validated handoff to an exact externally authorized durable-staging target. It binds artifact/source identity, target environment, filesystem scope, capability prerequisites, authority fingerprint, preflight, readback, health, rollback, and evidence requirements without performing deployment.

Engineering PR #847 qualified at 86/86 on final head `b770e86b1a33e70a9272643f5abf04e1c150648c` and squash merged at `2cfc55cf3dde9304b713ba9fb6f70509dd4dafda`.

Engineering envelope SHA-256: `bd138c83785e61f45b0b3066f3e1904942af23df8a34f6392b67dd966a5fa19a`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Production-readiness progression

Sprint203 supplied the bounded merchant-core staging bridge, Sprint204 supplied authenticated runtime readiness, Sprint205 supplied the governed durable-staging artifact, Sprint206 supplied validated handoff, and Sprint207 supplied deterministic external operator deployment planning.

Source packaging, handoff, and exact-target planning are no longer the immediate blocker. The next step requires a real isolated non-production runtime plus separate operational authority.

## Operational boundary

Canonical state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`; producer dispatch not performed.

## Next material horizon

Under separate operational authority, materialize the real `durable-staging` runtime using the exact artifact and generated operator plan. Establish external runtime bindings and durable persistence/session/authorization/transaction/POS prerequisites, then verify exact source/artifact provenance, configuration readback, non-mutating health, and rollback evidence.

Only after real target qualification should the protected readiness producer and downstream ingestion/selection sequence run. Do not create another source sprint unless targeted discovery proves an additional repository-side blocker.

Author by Lab | zefry

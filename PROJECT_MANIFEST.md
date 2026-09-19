# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Canonical engineering checkpoint:** Sprint204
**Objective:** `DURABLE_STAGING_RUNTIME_READINESS_ATTESTATION_DELIVERY`
**Canonical engineering commit:** `a5672b315a320092c6fa8cc74d984cb70f4e18ae`
**Engineering PR:** #841 — `Sprint204: deliver durable staging readiness attestation endpoint`
**Final engineering head:** `f849d3902c026105ae9e21088b45a05b6d71dcaa`
**Exact-head qualification:** 88/88 successful
**Sprint204 bounded readiness qualification:** run `35450150420` — SUCCESS
**M7.5 Technical Preview Release Artifact:** run `35450150933` — SUCCESS
**Sprint32 authentication recovery:** run `35450150321` — SUCCESS
**Sprint33 recovery-bound password reset:** run `35450150417` — SUCCESS
**Sprint34 authenticated password change:** run `35450150397` — SUCCESS
**M7.1 qualification:** run `35450150370` — SUCCESS
**Governance qualification:** run `35450150921` — SUCCESS
**PHP Foundation qualification:** run `35450150402` — SUCCESS
**Product Owner merge authority:** `product-owner-merge-authority` — SUCCESS
**Engineering envelope:** 12 paths — `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint203 reconciliation `10350ddcd552595308c7603134cb12a8e7f751d1`

> `a5672b315a320092c6fa8cc74d984cb70f4e18ae` is the permanent canonical Sprint204 engineering evidence. The reconciliation squash must not replace it.

## Purpose

Sprint204 closes the remaining source-level attestation gap between the Sprint203 bounded merchant-core staging bridge and the already-existing governed durable-runtime qualification chain. A real isolated non-production staging runtime can now expose a deterministic, authenticated, secret-free readiness document without selecting or activating itself.

## Delivered capability

- Canonical attestable runtime identity is `durable-staging`.
- The readiness route exists only when the runtime identity is exactly `durable-staging` and `ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED=true`.
- Historical `staging` remains a compatibility alias for the bounded merchant-core bridge, but is not qualification identity.
- The endpoint is read-only, bearer-authenticated, and emits `Cache-Control: no-store, private`.
- The token source `ONEQAY_DURABLE_RUNTIME_ATTESTATION_TOKEN` is never returned.
- The payload covers environment identity, non-synthetic/non-production posture, persistence/session/authorization/transaction/POS durability, exact source commit, exact artifact SHA-256, configuration mutation/readback/rollback capabilities, non-mutating health support, inactive feature state, and secret-free declaration.
- Capability declarations fail closed unless explicitly supplied by the real staging environment.
- The existing protected producer/ingestion/selector chain remains the authority for actual external qualification evidence.

## Qualification evidence

- Final head `f849d3902c026105ae9e21088b45a05b6d71dcaa` completed 88/88 PR-triggered workflows successfully.
- Product Owner merge authority status was SUCCESS before guarded squash merge.
- Engineering PR #841 squash merged at `a5672b315a320092c6fa8cc74d984cb70f4e18ae`.
- Post-merge comparison confirms exactly one squash commit above Sprint203 reconciliation `10350ddcd552595308c7603134cb12a8e7f751d1` with the frozen 12-path engineering envelope.

## Operational NO-GO

Machine-readable state under `ops/final-shift-close/` remains authoritative and unchanged: migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; durable target selection `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`; selected target `null`; producer dispatch `NOT_PERFORMED`.

Sprint204 makes a future real target attestable. It does not create that target, provide operational secrets, dispatch a producer, ingest evidence, persist selection, deploy, mutate configuration, execute migration #27, provision permissions, or activate any feature.

## Next position

The next material blocker is external rather than cosmetic: an actual isolated non-synthetic `durable-staging` environment must exist with durable persistence/session/authorization/transaction/POS behavior, exact source/artifact provenance, authenticated configuration mutation with read-before-write/read-after, non-mutating health attestation, and verified rollback. Once those prerequisites exist and separate operational authority permits protected interaction, the already-materialized producer/ingestion/selection chain can qualify it. Source work should resume only for a concrete missing capability discovered against that real environment.

Author by Lab | zefry

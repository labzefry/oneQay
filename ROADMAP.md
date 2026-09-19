# oneQay Roadmap

**Roadmap checkpoint:** Sprint203 closed canonically
**Canonical engineering baseline:** `088dc0a38d80abc776cacc7052ddb3dbfdcf0c70`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint203 horizon

Sprint203 closed `DURABLE_STAGING_MERCHANT_CORE_BOUNDED_BRIDGE`.

The already-qualified merchant core can now be composed for an explicitly armed non-production staging runtime without modifying the historical Local/Test/CI guards inside legacy repositories.

Engineering PR #839 qualified at 88/88 on final head `a1a2eb0a3ff63edabe1c9ab06a5f8494bbd9d963` and squash merged at `088dc0a38d80abc776cacc7052ddb3dbfdcf0c70`.

Engineering envelope SHA-256: `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Production-readiness progression

The source path now extends beyond synthetic-only/local qualification into a bounded non-production staging compatibility surface for the merchant core. This is intentionally source readiness, not target deployment or activation.

Two broader Sprint203 attempts were rejected before merge because exact-head CI showed excessive historical regression cost. The final design keeps staging compatibility concentrated at an existing composition boundary and preserves repository-level historical guards.

## Operational boundary

Canonical repository state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`.

## Sprint204 selection rule

Prioritize the durable-target blocker directly. Build the smallest coherent read-only qualification/readiness contract for a real non-synthetic staging target capable of supporting Sprint203. Qualification must be deterministic, non-secret, fail-closed, and evidence-producing, while target selection, deployment, migration execution, activation, and Production remain outside authority.

Author by Lab | zefry

# oneQay Roadmap

**Roadmap checkpoint:** Sprint164 engineering merged; canonical reconciliation in progress
**Canonical engineering baseline:** `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint164 horizon

Sprint164 closed the material post-baseline inventory gap with `POS_INVENTORY_REPLENISHMENT_FOUNDATION_WORKSPACE`.

The capability is intentionally bounded to positive receiving/replenishment. It requires an existing canonical opening baseline and active exact tenant/outlet catalog product, records immutable before/received/after evidence, uses stable idempotent operation identity, and is protected by dedicated deny-by-default `pos.inventory.replenish` authority with no automatic grant.

Migration #28 is module-owned under `apps/web/database/module-migrations/pos/` and is loaded by the bounded replenishment provider. The canonical global migration directory remains unchanged through migration #27.

Engineering PR #746 squash merged at `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`. Engineering envelope: 22 paths, SHA-256 `b826b746e18bc39025735e10fe645ad559eceab992801190a654624462811f8e`.

Canonical reconciliation remains exactly six paths with SHA-256 `19c035b85a4698f60a78cbb70ccd0c1b82835c47073f5dcb2e2443f49c88f0fa`.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. No roadmap text grants operational authority. Selected durable target remains `null`; migration #27 execution remains `NOT_EXECUTED`; permission provisioning remains `NONE`; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint165 selection rule

Begin Sprint165 bounded discovery only from the fully reconciled Sprint164 canonical checkpoint. Do not preselect the objective.

Prioritize the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap. Reuse canonical shift, sale, catalog, inventory, void/refund, authorization, reporting, reconciliation, and replenishment owners. Any future inventory correction/decrement/transfer authority must be separately justified; Sprint164 grants only positive replenishment and does not authorize arbitrary stock adjustment.

Preserve fail-closed behavior, deny-by-default authorization, tenant/outlet isolation, exact-head CI, source-only operational posture, and the six-path canonical reconciliation model.

Author by Lab | zefry

# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint164 closed
**Canonical engineering commit:** `d37ecdfa16d3f024de840871aa202af4fb5ee7d1`
**Latest engineering PR:** #746 — `Sprint164: add POS inventory replenishment foundation workspace`
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint164 state

Sprint164 materialized `POS_INVENTORY_REPLENISHMENT_FOUNDATION_WORKSPACE` as positive-only ongoing receiving after the canonical one-time inventory baseline.

Engineering PR #746 squash merged at `d37ecdfa16d3f024de840871aa202af4fb5ee7d1` after complete exact-head qualification and repository-native Product Owner merge authorization.

Engineering envelope: exactly 22 paths with sorted newline SHA-256 `b826b746e18bc39025735e10fe645ad559eceab992801190a654624462811f8e`.

Canonical reconciliation envelope: exactly six paths with sorted newline SHA-256 `19c035b85a4698f60a78cbb70ccd0c1b82835c47073f5dcb2e2443f49c88f0fa`.

## Preserved product boundaries

- Replenishment accepts positive received quantity only; it is not a general stock adjustment/decrement authority.
- A canonical opening inventory baseline must already exist for the exact tenant/outlet/product.
- The catalog item must remain active.
- `pos.inventory.replenish` is deny-by-default and is not auto-granted or provisioned.
- Migration #28 is module-owned under `database/module-migrations/pos` and is discovered by the bounded provider; the canonical global migration horizon remains through #27.
- Sale completion remains stock decrement owner; full-sale void remains stock restoration owner; cash refund does not restore stock twice.

## Lifecycle state

Machine-readable operational state under `ops/final-shift-close/` remains authoritative and unchanged: selected target `null`, migration #27 `NOT_EXECUTED`, permission provisioning `NONE`, feature activation `INACTIVE`, deployment authority `NOT_GRANTED`, Technical Preview/Production `NOT_AUTHORIZED`, updater `INACTIVE`.

## Next engineering position

Begin **Sprint165 bounded discovery** from the canonical post-Sprint164 checkpoint. Do not preselect the objective. Select the smallest material non-duplicative P0/P1 production-readiness or business-completeness gap from current source/contracts/regressions.

Author by Lab | zefry

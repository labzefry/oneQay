# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint201 closed canonically
**Canonical engineering commit:** `cec54de9ff3d056f5981165616584c343b0152c2`
**Engineering PR:** #833
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint201 completed

- [x] Replace route-order-only POS guidance with state-aware operational guidance.
- [x] Reuse existing authorized Catalog & Opening Stock, Shift Start, and Cashier read models.
- [x] Recommend catalog setup when catalog/sellable inventory is not ready.
- [x] Recommend Shift Start when the exact device shift or opening-cash evidence is incomplete.
- [x] Recommend Cashier only after sellable inventory, active shift, and opening-cash readiness are verified.
- [x] Use Sales Summary only as a read-only review fallback.
- [x] Withhold the next-action recommendation when readiness evidence cannot be read safely.
- [x] Recommend only destinations already delivered to the current merchant context.
- [x] Preserve the separately authorized workspace list and destination-level authorization.
- [x] Preserve Sprint200 guided-home regression through an explicit canonical successor contract instead of a generic bypass.
- [x] Introduce no new route, permission, role, schema, bootstrap authority, persistence authority, or activation authority.
- [x] Complete 87/87 exact-head engineering qualification.
- [x] Product Owner merge authority SUCCESS.
- [x] Engineering squash `cec54de9ff3d056f5981165616584c343b0152c2`.
- [x] Operational NO-GO preserved.

Engineering envelope SHA-256: `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`.

Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Next

After canonical reconciliation, begin **Sprint202 business-first bounded discovery**. Select the next material P0/P1 merchant end-to-end blocker and avoid anti-granular lifecycle chaining. Do not assume migration, permission, updater, deployment, durable-target, live Technical Preview, Production, persistence, Final Shift Close, or producer-dispatch authority.

Author by Lab | zefry

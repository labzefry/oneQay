# oneQay Roadmap

**Roadmap checkpoint:** Sprint201 closed canonically
**Canonical engineering baseline:** `cec54de9ff3d056f5981165616584c343b0152c2`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint201 horizon

Sprint201 closed `MERCHANT_POS_STATE_AWARE_GUIDED_OPERATIONS`.

The merchant POS home now derives its suggested next action from existing authorized Catalog & Opening Stock, Shift Start, and Cashier business-state read models instead of route ordering alone.

Engineering PR #833 qualified at 87/87 on final head `d5a1fb81ed725052cd89f98a72c0eefeba93a946` and squash merged at `cec54de9ff3d056f5981165616584c343b0152c2`.

Engineering envelope SHA-256: `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Business progression

The merchant experience has progressed from authenticated access to a guided operations home and now to state-aware operational guidance. oneQay can distinguish setup-required, shift-required, cashier-ready, review-available, routes-only, and guidance-unavailable states without minting new business-state authority.

The guidance layer is read-only. It recommends only already delivered destinations, and every destination remains authoritative for authorization, prerequisites, persistence, and mutation eligibility.

## Operational boundary

Source delivery capability is not operational activation. Canonical repository state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`.

## Sprint202 selection rule

Choose the smallest material P0/P1 blocker that moves oneQay toward complete merchant end-to-end usability and eventual authorized Technical Preview/Production readiness. Prefer a bounded business outcome over anti-granular lifecycle chaining. Preserve tenant isolation, deny-by-default behavior, deterministic qualification, and all canonical NO-GO boundaries.

Author by Lab | zefry

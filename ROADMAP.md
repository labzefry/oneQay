# oneQay Roadmap

**Roadmap checkpoint:** Sprint202 closed canonically
**Canonical engineering baseline:** `09df0a239d894284a62dec2b5cc39754406eab5c`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint202 horizon

Sprint202 closed `MERCHANT_POS_AUTHORITATIVE_SALE_RECEIPT_CONTINUITY`.

The cashier now receives authoritative completed-sale line data from the canonical sale receipt and presents a professional receipt with print and next-sale continuity.

Engineering PR #835 qualified at 89/89 on final head `33c8ecd3852b5507fada858cfe6de3fb3924cd35` and squash merged at `09df0a239d894284a62dec2b5cc39754406eab5c`.

Engineering envelope SHA-256: `d49048acc4a472d919471c56083ca4f2ac77e988ef67f6de99fb4a601cbbd684`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Business progression

The merchant path now covers guided readiness into cashier operation and a usable post-checkout receipt. Financial receipt values are projected from the server-completed sale, while catalog display names remain presentation-only labels.

The implementation does not create a payment-provider integration, new persistence authority, browser receipt store, automatic sale retry, or operational activation path.

## Operational boundary

Source delivery capability is not operational activation. Canonical repository state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`.

## Sprint203 selection rule

Choose the smallest material P0/P1 blocker that moves oneQay toward complete merchant end-to-end usability and eventual separately authorized Technical Preview/Production readiness. Prefer a bounded business outcome over anti-granular lifecycle chaining. Preserve tenant isolation, deny-by-default behavior, deterministic qualification, and all canonical NO-GO boundaries.

Author by Lab | zefry

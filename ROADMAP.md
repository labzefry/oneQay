# oneQay Roadmap

**Roadmap checkpoint:** Sprint200 closed canonically
**Canonical engineering baseline:** `9c9c211416d216a396880d6e439e6d13c1438b73`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint200 horizon

Sprint200 closed `MERCHANT_POS_GUIDED_OPERATIONS_HOME`.

The merchant landing experience after secure sign-in now behaves as a guided enterprise POS operations home. Delivered destinations are summarized and grouped into clear business lanes, while the suggested starting workspace is derived only from routes already delivered by the server.

Engineering PR #831 qualified at 86/86 on final head `cf9d49063d9040b83729a48eaa298e5f67f98a45` and squash merged at `9c9c211416d216a396880d6e439e6d13c1438b73`.

Engineering envelope SHA-256: `c8c07a41fba8ae22eabde78ceaeeed8ae88d0a5906bcc1429348d384a061682f`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Business progression

The project has progressed from source-complete Technical Preview lifecycle controls into integrated merchant-facing POS delivery, account-security self-service, and now a more usable operations home. Internal context identifiers remain accessible but are secondary to business-oriented navigation.

Guided navigation does not create business-state authority. Every workspace remains responsible for its own authorization, prerequisites, persistence, and mutation checks.

## Operational boundary

Source delivery capability is not operational activation. Canonical repository state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`.

## Sprint201 selection rule

Choose the smallest material P0/P1 blocker that moves oneQay toward complete merchant end-to-end usability and eventual authorized Technical Preview/Production readiness. Prefer a bounded business outcome over anti-granular lifecycle chaining. Preserve tenant isolation, deny-by-default behavior, deterministic qualification, and all canonical NO-GO boundaries.

Author by Lab | zefry

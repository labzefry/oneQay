# oneQay Roadmap

**Roadmap checkpoint:** Sprint194 closed canonically
**Canonical engineering baseline:** `7e3e58d7be012ee5d797acb879cfb9f9a1e829dc`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint194 horizon

Sprint194 closed `GUARDED_TECHNICAL_PREVIEW_ACTIVATION_REQUEST`.

The installation journey now progresses from complete/not-activated configuration to a private exact-bound Technical Preview activation request. This creates the governed approval handoff needed for a later separately authorized activation capability without crossing the current operational NO-GO.

Engineering PR #819 qualified at 82/82 on final head `af1027156209977a75d24f54fec031f86bedf8f6` and squash merged at `7e3e58d7be012ee5d797acb879cfb9f9a1e829dc`.

Canonical M7.5 main-push run `35424129329` succeeded.

Engineering envelope SHA-256: `a85b8ceffbf533ea5f3aff7555dba90129c2c851bb8d9bb8ed427ce2e572f804`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Product progression

Governed release → readiness → secure setup → DB verification → pending config → sealed handoff → promotion request → authority qualification → execution readiness → atomic promotion → post-promotion verification → installation completion handoff → guarded Technical Preview activation request.

## Operational boundary

The activation request exists only as approval evidence. Migration #27 remains `NOT_EXECUTED`; permission provisioning `NONE`; feature activation `INACTIVE`; deployment/Technical Preview/Production remain unauthorized; updater inactive; selected durable target `null`.

## Sprint195 selection rule

Select the smallest material P0/P1 blocker after the guarded activation request. Prefer a capability that advances safe application boot/readiness while preserving the separate operational authority boundary and without executing migration #27 or activating Technical Preview.

Author by Lab | zefry

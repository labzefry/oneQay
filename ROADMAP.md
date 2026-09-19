# oneQay Roadmap

**Roadmap checkpoint:** Sprint199 closed canonically
**Canonical engineering baseline:** `f2692018b261a723b9b360efe650969926adb2d2`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint199 horizon

Sprint199 closed `MERCHANT_ACCOUNT_SECURITY_SELF_SERVICE_WORKSPACE`.

The merchant experience now exposes existing first-party password change, password recovery, recovery-code rotation, privileged authenticator recovery, and logout capabilities without introducing a second authentication authority or widening permissions.

Engineering PR #829 qualified at 85/85 on final head `b7be9bc6c268aa6a332c0e709e417c6384d08800` and squash merged at `f2692018b261a723b9b360efe650969926adb2d2`.

Engineering envelope SHA-256: `2aba38a7f80dcc6178ce39f865789b4e20b26f2d3ebb96b1d307af0c628e77e4`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Business progression

The project has progressed from source-complete Technical Preview lifecycle controls into integrated merchant-facing business delivery and now into coherent account-security self-service. Existing identity authority remains centralized; the merchant UI only orchestrates already-governed endpoints and server-derived capabilities.

## Operational boundary

Source delivery capability is not operational activation. Canonical repository state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`.

## Sprint200 selection rule

Choose the smallest material P0/P1 blocker that moves oneQay toward complete merchant end-to-end usability and eventual authorized Technical Preview/Production readiness. Prefer a bounded business outcome over anti-granular lifecycle chaining. Preserve tenant isolation, deny-by-default behavior, deterministic qualification, and all canonical NO-GO boundaries.

Author by Lab | zefry

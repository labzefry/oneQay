# oneQay Roadmap

**Roadmap checkpoint:** Sprint198 closed canonically
**Canonical engineering baseline:** `da8b0a0579e7788b22a1ee1cd78130ff29cdd99a`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint198 horizon

Sprint198 closed `POS_BUSINESS_WORKSPACE_GUARDED_DELIVERY_INTEGRATION`.

The application now registers one guarded POS aggregate delivery provider that composes the already-qualified business workspace providers into the normal Laravel bootstrap while preserving each workspace's independent runtime, persistence, session, feature, permission, and prerequisite gates.

Engineering PR #827 qualified at 100/100 on final head `4c6b5ba627bf8bf0d28b4360d10c8f249b66b73f` and squash merged at `da8b0a0579e7788b22a1ee1cd78130ff29cdd99a`.

Engineering envelope SHA-256: `4e04f75c0df2b340b0a66ad5d2fa545d364a088740e0af92861909c4848d0a45`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Business progression

The project has moved from source-complete Technical Preview lifecycle controls into business-first application delivery. POS operational workspaces are now integrated into application bootstrap without bypassing their fail-closed gates.

Close-dependent Shift History and Cash Variance Reconciliation remain intentionally unavailable until their existing Final Shift Close prerequisite is separately authorized.

## Operational boundary

Source delivery capability is not operational activation. Canonical repository state remains migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater inactive; durable target blocked; selected target `null`.

## Sprint199 selection rule

Choose the smallest material P0/P1 blocker that moves oneQay toward complete merchant end-to-end usability and eventual authorized Technical Preview/Production readiness. Prefer a bounded business outcome over anti-granular lifecycle chaining. Preserve tenant isolation, deny-by-default behavior, deterministic qualification, and all canonical NO-GO boundaries.

Author by Lab | zefry

# oneQay Roadmap

**Roadmap checkpoint:** Sprint177 engineering closed; canonical reconciliation in progress  
**Canonical engineering baseline:** `6752af1eb957993a6080206d9a40f7163bd24be6`  
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint177 engineering horizon

Sprint177 closed `MERCHANT_CONTEXT_GUARDED_BOOTSTRAP_DELIVERY_FOUNDATION`, addressing the proven delivery gap between Sprint176 atomic merchant-context orchestration and a deliberately guarded executable surface.

The dedicated console command accepts no self-authorizing merchant tuple arguments. Exact authorization comes from configured preauthorization material, all relevant enablement gates are default-deny, runtime remains Local/Test/CI only, secret input is hidden, output is sanitized, and replay fails closed.

Engineering PR #781 squash merged at `6752af1eb957993a6080206d9a40f7163bd24be6`. Engineering envelope: 4 paths, SHA-256 `de509f025c78e8f2ed7d0335b81b6f423deb621c3f1d6bc54bba4a312635d872`.

Canonical reconciliation envelope: 6 paths, SHA-256 `cf8df3335c6b40d148a86b3b9ad7a565400b727a88c62b26869f8a4a5481e78c`.

## Product progression through Sprint177

The product now combines tenant/security/API/POS foundations, governed installation-readiness controls, atomic merchant-context creation, and a guarded Local/Test/CI-only console delivery mechanism. Sprint177 deliberately does not create public onboarding or real-environment activation.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint178 selection rule

Begin Sprint178 bounded discovery only after Sprint177 canonical reconciliation closes. Determine from live canonical evidence what smallest material P0/P1 gap still blocks a real merchant end-to-end journey now that atomic context bootstrap has a guarded delivery mechanism. Do not preselect an objective.

No public onboarding exposure, real merchant provisioning, runtime widening, migration execution, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry

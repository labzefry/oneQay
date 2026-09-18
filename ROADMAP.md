# oneQay Roadmap

**Roadmap checkpoint:** Sprint179 closed canonically
**Canonical engineering baseline:** `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint179 horizon

Sprint179 closed `MERCHANT_BOOTSTRAP_INITIAL_POS_OPERATION_AUTHORIZATION_FOUNDATION`, addressing the concrete gap where a newly bootstrapped merchant could authenticate but still lacked durable outlet/device access and POS permission authority.

The final implementation keeps Sprint176 atomic merchant-context bootstrap unchanged, then adds exact-device access and a separate least-privilege initial POS operator role inside one outer transaction.

Engineering PR #785 squash merged at `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`. Engineering envelope: 4 paths, SHA-256 `33206447002d40b489742fdb7b0c50670705400d16c184e352aa64aeb1534feb`.

Canonical reconciliation envelope: 6 paths, SHA-256 `72d21048381af6505f8b6315141efef93f909e407d54377e3726d49f0c38ccff`.

## Product progression through Sprint179

The product now combines tenant/security/API/POS foundations, governed installation readiness, atomic merchant bootstrap, first-party browser entry, exact outlet/device access, and initial permission-filtered POS authority. The operational role remains least-privilege and device-scoped.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint180 selection rule

Begin Sprint180 bounded discovery from fully reconciled Sprint179. Determine from live canonical evidence the smallest material P0/P1 blocker remaining in the real merchant end-to-end journey. Do not preselect an objective and do not return to technical micro-sprints unless required inside a meaningful vertical business increment.

No real merchant provisioning, runtime widening, migration execution, updater activation, deployment, Technical Preview, Production, durable-target selection, producer dispatch, or operational permission provisioning is pre-authorized.

Author by Lab | zefry

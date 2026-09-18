# oneQay Roadmap

**Roadmap checkpoint:** Sprint180 closed canonically
**Canonical engineering baseline:** `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint180 horizon

Sprint180 closed `MERCHANT_INITIAL_CONTEXT_ASSISTED_SIGN_IN_FOUNDATION`, removing the usability gap where the initial merchant had to know and manually copy internal tenant/identity/organization/outlet/device identifiers.

The server now supplies the exact installation-bound login context while the existing first-party login controller, MFA lifecycle, session authority, and POS transition remain unchanged. The provisioning identifier is not browser-delivered.

Engineering PR #787 squash merged at `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`. Engineering envelope: 4 paths, SHA-256 `2c122014511daaeb8ca1d5cbd2ee4bb184733ed1154e08c8e0000f3b758c9c6c`.

Canonical reconciliation envelope: 6 paths, SHA-256 `eb9b36e214455c09714f9d03f034e85aec06506d43b64be314b2b95b00b4f41b`.

## Product progression through Sprint180

The product now combines tenant/security/API/POS foundations, governed installation readiness, atomic POS-ready merchant bootstrap, exact-device operational authority, and a usable initial merchant sign-in journey that requires only credential/MFA interaction rather than internal platform IDs.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint181 selection rule

Begin Sprint181 bounded discovery from fully reconciled Sprint180. Determine from live canonical evidence the smallest material P0/P1 blocker remaining in the real merchant end-to-end journey. Do not preselect an objective and do not return to technical micro-sprints unless required inside a meaningful vertical business increment.

No real merchant provisioning, runtime widening, migration execution, updater activation, deployment, Technical Preview, Production, durable-target selection, producer dispatch, or operational permission provisioning is pre-authorized.

Author by Lab | zefry

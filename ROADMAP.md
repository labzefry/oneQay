# oneQay Roadmap

**Roadmap checkpoint:** Sprint178 closed canonically
**Canonical engineering baseline:** `8992c2ed1b6278d113e24e38a847bedeac345161`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint178 horizon

Sprint178 closed `MERCHANT_FIRST_PARTY_APPLICATION_ENTRY_FOUNDATION`, removing the proven usability gap between the existing first-party authentication/session-authority stack and the existing POS Operations Hub.

The final implementation reuses the existing Foundation surface and enables merchant entry only under server-rendered Local/Test/CI + persistence + session-control gates. Existing login, TOTP enrollment/challenge, session authority, and `/pos` capabilities remain authoritative rather than being duplicated.

Engineering PR #783 squash merged at `8992c2ed1b6278d113e24e38a847bedeac345161`. Engineering envelope: 4 paths, SHA-256 `9d27ecd0802230d3484aa7ca424064313595e8174172eb889c2736250e2815c5`.

Canonical reconciliation envelope: 6 paths, SHA-256 `d994709453d1415d23d2bdfc8ecade257d8baa8b07b9aff98654a2b4cb6bb0f5`.

## Product progression through Sprint178

The product now combines tenant/security/API/POS foundations, governed installation readiness, atomic and guarded merchant-context bootstrap, and a usable first-party merchant browser entry that reaches permission-filtered POS operations. Public self-registration and real-environment activation remain intentionally absent.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint179 selection rule

Begin Sprint179 bounded discovery from fully reconciled Sprint178. Determine from live canonical evidence the smallest material P0/P1 blocker remaining in the real merchant end-to-end journey. Do not preselect an objective and do not return to technical micro-sprints unless they are necessary inside a meaningful vertical business increment.

No public self-registration, real merchant provisioning, runtime widening, migration execution, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry

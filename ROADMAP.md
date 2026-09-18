# oneQay Roadmap

**Roadmap checkpoint:** Sprint181 closed canonically
**Canonical engineering baseline:** `deb999fcd0694ba85c132d1b490bd90c0dc86309`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint181 horizon

Sprint181 closed `INSTALLATION_READINESS_WIZARD_DELIVERY_FOUNDATION`, converting the earlier installation-readiness source foundations into a visible operator preflight without introducing installation mutation authority.

The existing system operations page now renders deterministic readiness checks for runtime/host, secure configuration, database, filesystem, release manifest, and artifact integrity. Missing or unprovable evidence remains BLOCKED.

Engineering PR #789 squash merged at `deb999fcd0694ba85c132d1b490bd90c0dc86309`. Engineering envelope: 4 paths, SHA-256 `dffcd90da1967e207fe5b65007c354ce0decb1f5d781db9733623cb9ca807e04`.

Canonical reconciliation envelope: 6 paths, SHA-256 `4fd83153da1067e5fa7a3f8ed145af7e8cbde3c3cba24149a51cd10770d3d955`.

## Product progression through Sprint181

The product now combines governed installer/readiness policy with an operator-usable read-only preflight, while preserving the secure merchant bootstrap/sign-in/POS progression delivered through Sprint180.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint182 selection rule

Begin Sprint182 bounded discovery from fully reconciled Sprint181. Determine the smallest material P0/P1 blocker remaining in the installation/onboarding/merchant journey. Prefer a meaningful operator vertical slice over another isolated technical foundation.

No environment writes, migration execution, real permission provisioning, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry

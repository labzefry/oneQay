# oneQay Roadmap

**Roadmap checkpoint:** Sprint219 closed canonically
**Canonical engineering baseline:** `d1f832c42ae6e4b2705e9b1c295d031880cc0113`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint219 horizon

Sprint219 closed `CPANEL_FIXED_PUBLIC_DOCROOT_BRIDGE`.

The repository now supports both direct active-release public roots and fixed cPanel public roots while preserving private immutable releases, exact authority binding, deterministic rollback, and canonical deployment evidence.

Current staging execution inputs:
- application artifact `10603323419`;
- cPanel operator kit `10606042478`;
- fixed-public bridge capability `FIXED_PUBLIC_BRIDGE`;
- legacy/direct capability `ACTIVE_RELEASE_PUBLIC`.

Production inputs remain:
- Production candidate `10603358335`;
- Production operator kit `10604307277`.

## Production-readiness progression

Sprint203–Sprint219 cover durable-staging readiness, deterministic releases, authority-bound planning, deployment evidence, cPanel no-SSH qualification/execution, same-source promotion governance, Production dark-deployment execution, and fixed-public cPanel compatibility.

The remaining immediate blocker is truthful real-target execution evidence, not missing generic repository deployment tooling.

## Next material horizon

1. Retrieve cPanel kit `10606042478` and staging application artifact `10603323419`.
2. Requalify the actual live cPanel host; do not substitute historical path observations for current evidence.
3. Obtain exact short-lived staging deployment authority <=900 seconds.
4. Execute guarded same-source staging deployment and qualify deployment evidence.
5. Materialize/qualify a real isolated Production target.
6. Use Production candidate `10603358335` and operator kit `10604307277`.
7. Obtain separate Production deployment authority <=900 seconds.
8. Dark-deploy and qualify to `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.
9. Only after real deployment evidence exists, close remaining business-readiness, migration, and Production traffic-activation gates.

Issue #856 remains the single operational handoff.

Do not open Sprint220 merely to continue activity. A successor engineering sprint requires a concrete defect or missing capability proven by real-host qualification/execution.

Author by Lab | zefry

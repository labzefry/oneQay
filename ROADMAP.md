# oneQay Roadmap

**Roadmap checkpoint:** Sprint184 closed canonically
**Canonical engineering baseline:** `dfb65d2782a580108d8ccd9a6f9203720fa036b7`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint184 horizon

Sprint184 closed `PREBOOT_INSTALLATION_CONFIGURATION_PREPARATION`.

The governed installation journey now advances beyond read-only readiness into secure pre-boot runtime configuration preparation. The exact governed release carries an operator-facing installer that is release-bound, one-time-authority gated, fail-closed, replay resistant, and capable of generating a private pending runtime configuration before Laravel runtime boot.

The prepared configuration is intentionally non-active: it creates only `.env.pending`, keeps persistence and Technical Preview disabled, and does not execute migrations or grant deployment/Production authority.

Engineering PR #797 qualified at 72/72 on final head `a22d98f18618be3ccf5ff8274ebf5528b33f01d7` and squash merged at `dfb65d2782a580108d8ccd9a6f9203720fa036b7`.

Canonical main-push M7.5 run `35378584615` completed successfully. Final engineering envelope: nine paths, SHA-256 `e2537851052c57b1ec3b7d2e99e1b5d0208fb3c54da207d5280682eb85c686a0`.

Canonical reconciliation envelope: eight paths, SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Product progression through Sprint184

The product now has a governed chain from release automation through trusted installation-readiness evidence and operator preflight to secure pending runtime configuration preparation.

## Operational boundary

Machine-readable operational state under `ops/final-shift-close/` remains authoritative. Selected durable target remains `null`; migration #27 remains `NOT_EXECUTED`; permission provisioning remains `NONE`; producer dispatch remains not performed; runtime allowlist remains Local/Test/CI; Final Shift Close remains inactive; deployment/Technical Preview/Production remain unauthorized; updater remains inactive.

## Sprint185 selection rule

Begin Sprint185 bounded discovery from fully reconciled Sprint184. Select the smallest material P0/P1 blocker after pending configuration preparation that most directly advances toward a usable governed installation/onboarding experience.

No environment activation, migration execution, real permission provisioning, updater activation, deployment, Technical Preview, Production, durable-target selection, or producer dispatch is pre-authorized.

Author by Lab | zefry

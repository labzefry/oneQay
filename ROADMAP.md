# oneQay Roadmap

**Roadmap checkpoint:** Sprint196 closed canonically
**Canonical engineering baseline:** `9948aeadc562b6188453872f09bd3afb754dd0c0`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint196 horizon

Sprint196 closed `TECHNICAL_PREVIEW_TARGET_ENVIRONMENT_PREFLIGHT`.

The installation journey now progresses from Technical Preview authority/readiness to a guarded target-host qualification that validates the bounded HTTPS single-instance Synthetic Preview envelope without activating the application.

Engineering PR #823 qualified at 84/84 on final head `afc443420ddef9283c7f575ce311b97fc026e658` and squash merged at `9948aeadc562b6188453872f09bd3afb754dd0c0`.

Engineering envelope SHA-256: `bb875f82e7cfc7a8347bf6d92f916f6124eb5b095e27f9582189ebec5c74e904`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Product progression

Governed release → readiness → secure setup → DB verification → pending config → sealed handoff → promotion request → authority qualification → execution readiness → atomic promotion → post-promotion verification → installation completion → Technical Preview activation request → Technical Preview authority qualification → activation execution readiness → target-environment preflight.

## Operational boundary

A passed preflight is not activation. Canonical repository state remains Technical Preview `NOT_AUTHORIZED`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment/Production unauthorized; updater inactive; durable target blocked; selected target `null`.

## Sprint197 selection rule

Select the smallest material P0/P1 blocker after target preflight. Verify repository-native activation, post-activation health, and rollback boundaries before choosing the next implementation. Do not execute or pre-authorize live Technical Preview activation.

Author by Lab | zefry

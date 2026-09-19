# oneQay Roadmap

**Roadmap checkpoint:** Sprint195 closed canonically  
**Canonical engineering baseline:** `022b1667ce25a9f4b86a71c95b2c59ad37793d4e`  
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint195 horizon

Sprint195 closed engineering for `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY_READINESS`.

The installation/activation journey now progresses from a guarded activation request to exact-bound authority qualification and durable execution-readiness while preserving a hard stop before mandatory target-environment preflight and Technical Preview activation execution.

Engineering PR #821 qualified at 83/83 on final head `11ec883590d05d28aeeac08a0286a3e149db00e7` and squash merged at `022b1667ce25a9f4b86a71c95b2c59ad37793d4e`.

Dedicated Sprint195 run `35425205497` and exact-head M7.5 run `35425205248` succeeded.

Engineering envelope SHA-256: `b6b06d99b300fa67cc6341f098eff73c430b11c6d98444ebb75a352d3e7589c2`.  
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Product progression

Governed release → readiness → secure setup → DB verification → pending config → sealed handoff → promotion request → authority qualification → execution readiness → atomic promotion → post-promotion verification → installation completion → Technical Preview activation request → Technical Preview authority qualification → Technical Preview activation execution readiness.

## Operational boundary

Execution-readiness is not activation. Canonical repository state remains Technical Preview `NOT_AUTHORIZED`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment/Production unauthorized; updater inactive; durable target blocked; selected target `null`.

## Sprint196 selection rule

Select the smallest material P0/P1 blocker after activation execution-readiness. Verify whether the mandatory target-environment preflight gate is the next missing capability before considering any activation executor. Do not perform live-host mutation or activation without separate operational authority.

Author by Lab | zefry

# oneQay Roadmap

**Roadmap checkpoint:** Sprint197 closed canonically
**Canonical engineering baseline:** `0c74e535cfeb281edaff5a2967752baee0db5227`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint197 horizon

Sprint197 closed `TECHNICAL_PREVIEW_ATOMIC_ACTIVATION_HEALTH_ROLLBACK`.

The governed Technical Preview path now extends through an atomic activation executor that validates exact authority/preflight state, performs bounded post-activation health qualification, and restores the prior environment on any unhealthy result.

Engineering PR #825 qualified at 85/85 on final head `027c84bb282aefd314d8da3d270c925ba5837841` and squash merged at `0c74e535cfeb281edaff5a2967752baee0db5227`.

Engineering envelope SHA-256: `08a73cc8338a51da3ed294b1a6c6a62986e0527100213414036d55b839b10a44`.
Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Product progression

Governed release → readiness → secure setup → DB verification → pending config → sealed handoff → promotion request → authority qualification → execution readiness → atomic promotion → post-promotion verification → installation completion → Technical Preview request → Preview authority/readiness → target preflight → guarded atomic activation + bounded health rollback capability.

## Operational boundary

Source capability is not operational activation. Canonical repository state remains Technical Preview `NOT_AUTHORIZED`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; Final Shift Close `INACTIVE`; deployment/Production unauthorized; updater inactive; durable target blocked; selected target `null`.

## Sprint198 selection rule

Choose the smallest material P0/P1 blocker that moves oneQay toward complete enterprise business usability and eventual authorized Technical Preview/Production readiness. Prefer an end-to-end bounded capability over another thin lifecycle-only micro-step. Preserve tenant isolation, deny-by-default behavior, rollback safety, and all canonical NO-GO boundaries.

Author by Lab | zefry

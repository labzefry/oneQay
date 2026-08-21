# Sprint35 Preservation Compatibility / Successor-Envelope Correction Gate

Attribution: **Lab | zefry**

## Purpose

This gate authorizes only a bounded compatibility correction for historical GitHub Actions workflows that reject the already-qualified Sprint35 Privileged TOTP Recovery & Factor Replacement source PR because they identify successor stages using historical whole-diff counts and fingerprints.

This gate does **not** authorize application runtime mutation, schema mutation, migration mutation, Production activation, updater activation, or merge of Sprint35 source PR #221.

## Canonical basis

- Canonical `main`: `221dedfb468728599c02eb0c0a06e9b9a9296fa8`
- Sprint35 source PR: `#221`
- Qualified Sprint35 exact head observed by this gate: `93cc490e34705a237b5e97f3ff8d3fdb6102a89e`
- Sprint35 source changed files: 17 semantic paths, all within the published 19-path source envelope.
- Dedicated Sprint35 regression on that exact head: SUCCESS.
- PHP Foundation, Governance Required Checks, M7.1 Application Regression, Backend Updater Control Plane Regression, Privileged Update Security Regression, and Read-Only Update Deployment UI Regression were also successful.

## Failure classification

The red historical workflows are compatibility failures, not evidence of a Sprint35 runtime defect. Representative evidence from the Sprint30 workflow shows that exact Sprint31/Sprint32/Sprint33 successor fingerprints no longer match the Sprint35 source diff, after which the workflow falls back to its historical Sprint30 source-envelope count/fingerprint and exits before executable preservation.

The correction MUST preserve historical executable behavior and MUST NOT weaken fail-closed semantics generally. It may recognize only the exact bounded Sprint35 successor shape or a strictly equivalent deterministic successor predicate.

## Frozen compatibility-correction envelope

Exactly the following 18 workflow paths may be changed by the future correction implementation:

1. `.github/workflows/m7-2-tenant-isolation-regression.yml`
2. `.github/workflows/m7-3-identity-org-context-regression.yml`
3. `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`
4. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
5. `.github/workflows/m7-5-preview-release-artifact.yml`
6. `.github/workflows/sprint21-role-permission-policy-regression.yml`
7. `.github/workflows/sprint22-policy-administration-regression.yml`
8. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`
9. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`
10. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`
11. `.github/workflows/sprint26-identity-credential-verification-regression.yml`
12. `.github/workflows/sprint27-first-party-session-establishment-regression.yml`
13. `.github/workflows/sprint28-initial-password-enrollment-regression.yml`
14. `.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml`
15. `.github/workflows/sprint30-privileged-totp-mfa-regression.yml`
16. `.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml`
17. `.github/workflows/sprint33-recovery-bound-password-reset-regression.yml`
18. `.github/workflows/sprint34-authenticated-password-change-regression.yml`

Sorted-path SHA-256: `25dbbd94087eba4157fa9c209f09174a127154a98067abbfbeec233bbe9398cd`

No other file is authorized by this compatibility gate.

## Required correction semantics

The future correction MUST:

- recognize Sprint35 only through deterministic source-envelope evidence, not through a permissive branch-name bypass;
- retain exact-head checkout semantics;
- keep historical workflow scopes fail-closed for unknown successors;
- preserve historical migration fixtures by hiding/removing only migrations newer than the workflow under test when necessary for executable historical regression;
- never rewrite migrations #1 through #12;
- never modify Sprint35 runtime/application source;
- continue executing the historical regression itself after successor recognition rather than converting a red workflow into a shape-only green workflow;
- preserve dependency/advisory, syntax, tenant-isolation, authentication, authorization, and disabled-default checks already present in each workflow;
- reject any path outside this frozen 18-path compatibility envelope.

## Sprint35 source invariants to preserve

The compatibility correction MUST NOT weaken or alter these Sprint35 invariants:

- dedicated `mq1` privileged-TOTP recovery namespace remains separate from Sprint32 password-recovery `rq1` authority;
- separate monotonic TOTP `factor_epoch` remains distinct from password `credential_epoch`;
- migration #12 remains forward-only;
- restricted recovery authority remains exactly 600 seconds;
- factor replacement remains atomic and requires proof of the newly generated TOTP factor;
- remaining dedicated recovery codes are revoked after replacement;
- audit remains secret-free;
- successful factor replacement requires a fresh normal login and must not synthesize MFA or step-up authority.

## Activation and lifecycle boundaries

- Technical Preview: `NO_SCHEMA_CHANGE` remains unchanged.
- Production: `NO-GO / NOT AUTHORIZED` remains unchanged.
- Updater: `DISABLED / UNWIRED` remains unchanged.
- This document authorizes only the future 18-path compatibility correction implementation.
- This document does not itself authorize squash merge of source PR #221.

## Exit criteria for the correction implementation

The future compatibility correction is qualified only when:

1. changed-file set is a subset of, and semantically limited to, the frozen 18 workflow paths;
2. unknown successor shapes still fail closed;
3. exact Sprint35 source PR shape is recognized deterministically;
4. every historical workflow that was red solely because of successor-envelope mismatch reaches and passes its executable preservation regression;
5. Sprint35 dedicated regression remains green;
6. Governance Required Checks and PHP Foundation remain green;
7. `main` has not raced from the canonical base without an explicit rebase/requalification decision.

# Sprint 31 — Preservation Compatibility Source Envelope Correction Gate

> **Status:** CORRECTION GATE / DOCUMENTATION-ONLY / NO MERGE AUTHORITY
> **Canonical baseline:** `d23da48a5d76f3d11ba8354575e50a3bc56e1a53`
> **Canonical baseline tree:** `164a8a8599867ce3c7f577fc76f17889194a8d0e`
> **Supersedes source-envelope path set:** 12-path envelope published by PR #202 only where this document explicitly expands it
> **Attribution:** Lab | zefry

## 1. Purpose

PR #202 correctly froze the Sprint 31 product/security implementation semantics, but executable qualification of the first exact 12-path source candidate exposed a bounded preservation-compatibility omission in its path envelope.

The omission is not a request to weaken historical checks. Historical M7/Sprint workflows contain exact successor recognition for Sprint 30. For a Sprint 31 source diff they therefore fall back to older envelope and migration-count assumptions even though canonical `main` already validly contains migration #9 from Sprint 30.

Representative observed failures include:

- Sprint 30 stopping at its exact Sprint 30 source-envelope guard because the Sprint 31 candidate is not the 46-path Sprint 30 envelope;
- M7.2 recognizing only the exact Sprint 30 successor and then falling back to its historical Sprint 28 envelope/eight-migration guard;
- Sprint 21 recognizing only the exact Sprint 30 successor and then applying its historical eight-migration expectation while canonical `main` contains migrations #1 through #9.

This document corrects only that preservation-compatibility omission. It does not change the Sprint 31 authentication/security semantics frozen by PR #201 and PR #202.

## 2. Existing Sprint 31 source semantics remain unchanged

All PR #202 implementation constraints remain authoritative, including:

- privileged operation scope is `policy_administration` only;
- `ONEQAY_PRIVILEGED_STEP_UP_ENABLED` remains source-default-disabled;
- Local/Test/CI only;
- fixed 300-second freshness;
- fresh first-party password plus existing Sprint 30 replay-safe TOTP challenge;
- no client-selected identity/tenant/organization/outlet/device context;
- session rotation and CSRF regeneration on successful step-up;
- login-level `mfa_verified_at` remains separate from operation-level step-up evidence;
- current durable authorization remains required for every policy mutation;
- no migration #10 and no schema change;
- no Composer/npm dependency change;
- no new password verifier, TOTP cryptography, factor store, recovery flow, or factor lifecycle;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- JRN-003 remains unresolved.

## 3. Corrected exact later-source envelope

The corrected Sprint 31 source candidate must contain **exactly 26 changed paths** relative to the then-current canonical `main` publication of this correction gate.

Sorted exact paths:

1. `.github/workflows/m7-2-tenant-isolation-regression.yml`
2. `.github/workflows/m7-3-identity-org-context-regression.yml`
3. `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`
4. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
5. `.github/workflows/sprint21-role-permission-policy-regression.yml`
6. `.github/workflows/sprint22-policy-administration-regression.yml`
7. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`
8. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`
9. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`
10. `.github/workflows/sprint26-identity-credential-verification-regression.yml`
11. `.github/workflows/sprint27-first-party-session-establishment-regression.yml`
12. `.github/workflows/sprint28-initial-password-enrollment-regression.yml`
13. `.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml`
14. `.github/workflows/sprint30-privileged-totp-mfa-regression.yml`
15. `.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml`
16. `apps/web/app/Application/Identity/PrivilegedStepUpClock.php`
17. `apps/web/app/Application/Identity/PrivilegedStepUpService.php`
18. `apps/web/app/Application/Identity/PrivilegedStepUpViolation.php`
19. `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`
20. `apps/web/app/Delivery/Http/Identity/PrivilegedReauthenticationController.php`
21. `apps/web/app/Delivery/Http/Middleware/RequirePolicyAdministrationSessionContextMiddleware.php`
22. `apps/web/app/Providers/AppServiceProvider.php`
23. `apps/web/config/oneqay.php`
24. `apps/web/routes/web.php`
25. `apps/web/tests/privileged-reauthentication-step-up.php`
26. `docs/PRIVILEGED_REAUTHENTICATION_STEP_UP_FOUNDATION.md`

The SHA-256 of the newline-terminated sorted path list is:

`8136b57a5c9949ec5020a3d5ae497f34431a704900eb0447d42fdb791efb6e39`

No other path is authorized by this correction gate.

## 4. Exact compatibility-only authority for fourteen historical workflows

The fourteen newly added historical workflow paths may change only to recognize the exact corrected Sprint 31 successor envelope and preserve executable regression behavior.

Required rules for each applicable workflow:

1. derive the PR changed-file list from the actual base-to-head diff;
2. recognize Sprint 31 only when the list contains exactly 26 paths and its sorted-path SHA-256 equals `8136b57a5c9949ec5020a3d5ae497f34431a704900eb0447d42fdb791efb6e39`;
3. use a dedicated exact-successor flag such as `ONEQAY_SPRINT31_SUCCESSOR=true` only for that exact match;
4. never recognize Sprint 31 by branch name, PR number, commit-message text, wildcard, path prefix, file-count alone, or feature/environment input;
5. preserve the existing Sprint 30 exact-successor behavior unchanged;
6. preserve legacy behavior unchanged for every diff that is neither the exact Sprint 30 successor nor the exact Sprint 31 successor;
7. when the exact Sprint 31 successor is recognized, stale historical shape/envelope/migration-count assertions may defer to the dedicated Sprint 31 workflow only where those assertions would otherwise reject the already-published Sprint 30 canonical state;
8. executable historical regressions and security checks must continue to run where they remain applicable;
9. if a historical workflow needs application dependencies to run its executable regressions for the exact Sprint 31 successor, it must install the already-locked existing dependencies; no dependency manifest or lock mutation is permitted;
10. no historical workflow may gain a bypass for failing application tests, secret scans, dependency audits, authorization regressions, tenant-isolation regressions, Preview/Production separation, or updater controls.

## 5. Sprint 30 workflow compatibility

`.github/workflows/sprint30-privileged-totp-mfa-regression.yml` must continue to enforce the exact 46-path Sprint 30 envelope for a Sprint 30 source candidate.

For the exact 26-path Sprint 31 successor only, it may defer the obsolete assertion that the current PR itself must equal the historical 46-path Sprint 30 envelope, while continuing to prove preservation of the published Sprint 30 invariants that remain relevant to Sprint 31.

This is successor compatibility, not retroactive expansion of Sprint 30 authority.

## 6. Sprint 31 dedicated workflow remains authoritative for the new delta

`.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml` remains responsible for proving the new Sprint 31 delta, including:

- exact corrected 26-path envelope/fingerprint;
- migrations exactly #1 through #9 and migration #10 absent;
- source-default-disabled step-up feature arm;
- fixed 300-second freshness;
- reuse of existing password verification and Sprint 30 replay-safe TOTP challenge;
- session/context evidence invariants;
- generic failure and no secret leakage;
- dedicated Sprint 31 executable regression;
- preservation regressions through Sprint 30;
- Technical Preview / Production / updater separation.

## 7. No schema or dependency expansion

This correction remains **NO_SCHEMA_CHANGE**.

Forbidden:

- migration #10;
- any migration mutation;
- Composer or npm manifest/lock mutation;
- new cryptography or TOTP implementation;
- new durable step-up table/column;
- recovery/factor replacement/reset/deletion;
- passkeys/federation/API tokens;
- Preview or Production activation;
- updater/release activation.

## 8. Source-candidate lineage requirement

After this correction gate is published, the Sprint 31 source candidate must be rebuilt from that exact correction publication so that:

- it is one clean commit directly above the correction publication;
- it is `ahead_by=1` and `behind_by=0` before merge authority;
- its changed-file set is exactly the 26 paths above;
- its sorted-path fingerprint is exactly `8136b57a5c9949ec5020a3d5ae497f34431a704900eb0447d42fdb791efb6e39`;
- temporary/materialization commits are absent from the final lineage.

The existing Draft source PR may be rebuilt onto that publication, but this correction gate itself creates no source merge authority.

## 9. Explicit non-authority

Publication of this correction gate would authorize only the bounded corrected source-envelope shape and exact historical-workflow compatibility described above.

It would not itself authorize merging the later Sprint 31 source PR. The later source PR must receive a separate Product Owner merge authorization for its exact final head.

It also does not authorize any recovery, factor lifecycle, schema, dependency, Preview, Production, updater, deployment, Release, or unrelated feature work.

## 10. Publication result

If this document is later Product Owner-authorized and merged, the canonical Sprint 31 source envelope becomes:

**EXACT 26 PATHS / FINGERPRINT `8136b57a5c9949ec5020a3d5ae497f34431a704900eb0447d42fdb791efb6e39`**

and supersedes the PR #202 12-path envelope only with respect to the fourteen explicitly listed preservation-compatibility workflow additions.

# Sprint 32 Preservation Compatibility Source Envelope Correction Gate

Attribution: Lab | zefry

## Status

**DOCUMENTATION-ONLY CORRECTION GATE — SOURCE IMPLEMENTATION NOT AUTHORIZED BY THIS FILE ALONE**

This gate corrects only the later Sprint 32 source-envelope path set required to preserve historical and Technical Preview release qualification behavior after introduction of migration #10.

It does not change the Sprint 32 Authentication Recovery / JRN-003 recovery-proof semantics already frozen by the published entry/source-envelope gates.

## Canonical baseline

This correction is based on exact canonical `main` publication:

- commit: `1d231d6ba9c48cc0dc4391d6111161653eb92c54`;
- tree: `e1408e0785bc49a9c5892f06b0953b6128dc7a62`;
- parent: `2769af3005839666e85681dfcf649ba22b0cffd4`;
- signature: verified / valid.

PR #207 published the original Sprint 32 source-envelope gate with exactly 31 paths and sorted-path SHA-256:

`6238b9b30da395c7b48c81b63fcf66446720d2611b68f9e90d5223e4c0be61b9`

## Qualification evidence requiring correction

The first Sprint 32 source candidate preserved the bounded recovery-proof implementation and passed its new dedicated checks, including:

- exact Sprint 32 source-envelope recognition;
- migration #10 additive-only checks;
- recovery security boundary checks;
- PHP lint;
- dedicated authentication-recovery regression;
- Governance Required Checks;
- PHP Foundation Regression;
- M7.1 Application Regression;
- individual M7.2, M7.3, M7.4A, M7.5 Preview Database Qualification, and Sprint 21 through Sprint 31 regressions.

The remaining aggregate failure was isolated to historical migration-shape visibility rather than recovery business logic.

The M7.5 Technical Preview Release Artifact workflow failed in `Run first-party Web regressions` because `apps/web/tests/tenant-isolation.php` intentionally preserves the historical exact nine-migration contract through Sprint 30 and saw the newly added migration #10.

A diagnostic Sprint 32 matrix then isolated migration #10 while running the same preserved executable regressions. Under that exact isolation:

- release-equivalent `composer test` passed;
- tenant-isolation passed;
- identity organizational-context passed;
- POS synthetic passed;
- Technical Preview interaction passed;
- Preview background execution passed;
- the preserved Sprint 21 through Sprint 31 regression chain passed.

The diagnostic jobs were intentionally terminated by sentinel failure steps after all diagnostic regressions completed successfully. The diagnostic state itself is not a merge candidate.

Therefore the compatibility defect is narrowly located in the M7.5 Technical Preview Release Artifact workflow, which was not included in the original 31-path Sprint 32 source envelope.

## Corrected exact later source envelope

The corrected later Sprint 32 source candidate may change exactly the following **32 paths** and no others:

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
17. `.github/workflows/sprint32-authentication-recovery-proof-regression.yml`
18. `apps/web/app/Application/Identity/IssuedRecoveryCodeSet.php`
19. `apps/web/app/Application/Identity/RecoveryCodeClock.php`
20. `apps/web/app/Application/Identity/RecoveryCodeRepository.php`
21. `apps/web/app/Application/Identity/RecoveryCodeService.php`
22. `apps/web/app/Application/Identity/RecoveryCodeViolation.php`
23. `apps/web/app/Application/Identity/VerifiedRecoveryProof.php`
24. `apps/web/app/Delivery/Http/Identity/FirstPartySessionKeys.php`
25. `apps/web/app/Delivery/Http/Identity/RecoveryCodeController.php`
26. `apps/web/app/Infrastructure/Identity/LaravelRecoveryCodeRepository.php`
27. `apps/web/app/Providers/AppServiceProvider.php`
28. `apps/web/config/oneqay.php`
29. `apps/web/database/migrations/0000_00_00_000010_create_identity_recovery_codes.php`
30. `apps/web/routes/web.php`
31. `apps/web/tests/authentication-recovery-proof.php`
32. `docs/AUTHENTICATION_RECOVERY_PROOF_FOUNDATION.md`

The sorted-path SHA-256 for this exact 32-path set is:

`db230ab3b77fff67f0bd12d7d7b615146d9d9df9a0af12014214e1862e9f6867`

Recognition based on path count alone, branch name, PR number, commit message, wildcard, prefix matching, or partial path matching is forbidden.

## M7.5 Technical Preview Release Artifact compatibility rule

The later source candidate may modify `.github/workflows/m7-5-preview-release-artifact.yml` only to preserve its historical Technical Preview release qualification behavior for the exact corrected Sprint 32 successor envelope.

The workflow must:

- derive the actual PR base-to-head changed-file list;
- recognize Sprint 32 only when the set is exactly 32 paths and its sorted-path SHA-256 is exactly `db230ab3b77fff67f0bd12d7d7b615146d9d9df9a0af12014214e1862e9f6867`;
- keep legacy behavior unchanged for every non-Sprint32 diff;
- keep the first-party Web regression suite executable;
- isolate migration #10 only after exact Sprint 32 successor recognition and only for historical tests that still assert the pre-Sprint32 nine-migration shape;
- keep release packaging, deterministic reproduction, manifest validation, public/private path checks, dependency audits, frontend type-check/build, and source-cleanliness checks active;
- preserve the Technical Preview `NO_SCHEMA_CHANGE` boundary;
- preserve the rule that Technical Preview release artifacts contain no durable migration files;
- preserve Production `NO-GO / NOT AUTHORIZED`;
- preserve updater `DISABLED / UNWIRED`.

This correction does not authorize weakening, skipping, or globally bypassing the M7.5 release workflow.

## Historical workflow preservation rule

The fifteen historical workflow compatibility paths already present in the original Sprint 32 envelope remain governed by exact-successor recognition.

For the corrected source candidate they must recognize only the exact 32-path fingerprint above. Migration #10 may be isolated from stale historical migration-shape guards only after that exact recognition. Executable authorization, tenant-isolation, identity, credential, session, MFA, step-up, and security regressions must remain active.

Sprint 30 and Sprint 31 exact-successor recognition must remain preserved for their historical exact fingerprints. Legacy behavior must remain unchanged for all other diffs.

## Sprint 32 dedicated workflow rule

The final `.github/workflows/sprint32-authentication-recovery-proof-regression.yml` must not retain temporary diagnostic-only sentinel behavior.

It must be restored as the authoritative Sprint 32 regression workflow and updated to:

- enforce exactly 32 changed paths;
- enforce exact fingerprint `db230ab3b77fff67f0bd12d7d7b615146d9d9df9a0af12014214e1862e9f6867`;
- preserve migration #1 through #9 byte-for-byte;
- require migration #10 as the only migration addition;
- run the dedicated authentication-recovery regression;
- run the preserved Sprint 21 through Sprint 31 executable regression chain with the exact Sprint 32 compatibility isolation required for legacy migration-shape tests;
- prove Preview and Production recovery routes remain inactive;
- prove source cleanliness at completion.

## Source reconstruction requirement

After publication of this documentation-only correction gate, the Sprint 32 source PR must be reconstructed from the exact correction publication so that:

- its parent is the correction publication on canonical `main`;
- it contains one clean source commit;
- compare is exactly `ahead 1 / behind 0`;
- it changes exactly the 32 corrected paths;
- its sorted-path SHA-256 is exactly `db230ab3b77fff67f0bd12d7d7b615146d9d9df9a0af12014214e1862e9f6867`;
- temporary diagnostic commits are absent from final lineage;
- temporary diagnostic sentinel behavior is absent from the final Sprint 32 workflow.

## Security and product boundaries unchanged

This correction does not authorize any change to the frozen Sprint 32 recovery semantics. In particular it does not authorize:

- password reset/change/overwrite;
- automatic or full login from a recovery proof;
- MFA/TOTP recovery;
- TOTP factor reset, replacement, deletion, or secret disclosure;
- protected-control recovery;
- support/admin bypass;
- email or SMS recovery;
- passkeys or federation;
- API-token authentication;
- Technical Preview authentication or schema activation;
- Production authentication or schema activation;
- updater activation;
- deployment or release authority beyond preservation of existing release qualification checks.

Migration #10 remains the only authorized candidate schema addition for Sprint 32. Migrations #1 through #9 remain immutable.

## Merge authority boundary

Publication of this correction gate authorizes only the corrected later source-envelope definition after separate Product Owner merge authorization for the correction PR.

It does **not** authorize merging the Sprint 32 source PR. The final source PR and its final exact head require a separate Product Owner merge authorization.

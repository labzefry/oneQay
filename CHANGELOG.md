# Changelog

## Canonical post-Sprint 32 program-state reconciliation — 2026-08-19

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint30/post-Sprint31 wording retained below as historical provenance.

- Sprint 21 through Sprint 32 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 31 Privileged Reauthentication / Step-Up Session Freshness Foundation remains published with exact **300-second** freshness for the `policy_administration` scope and its source-default-disabled Local/Test/CI boundary.
- Sprint 32 Authentication Recovery / JRN-003 Recovery Proof Foundation is published through source PR #208 as `914f93f8636bbd0901c61d8a8f14ad69c2c8fbfe` with tree `89f8dcea209ea912ba2539f3c6224a3a0519c8f7`, parent `7f2cc64e5a85158fb24cf03b61d2b36ead73190a`, and a verified/valid publication signature after **24/24** exact-head pull-request workflows succeeded.
- Sprint 32 source remained within the exact **32-path** envelope whose sorted-path SHA-256 is `db230ab3b77fff67f0bd12d7d7b615146d9d9df9a0af12014214e1862e9f6867`.
- Canonical source migrations are exactly **#1 through #10**. Migrations #1–#9 remain immutable. Migration #10 creates only `oneqay_identity_recovery_codes` and `oneqay_identity_recovery_audit`. No migration #11 is authorized.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default and Sprint 32 recovery execution remains bounded to **Local/Test/CI**.
- Successful recovery-code rotation issues exactly **8** `rq1.<22-char selector>.<43-char secret>` codes, persists no plaintext recovery secret/code, and uses SHA-256 digest verification with `hash_equals` plus secret-free audit evidence.
- Recovery-code rotation and proof are atomic; same-code replay/concurrency is fail-closed with at most one winner.
- Successful recovery proof establishes only the restricted `password_reset_required` session for exactly **600 seconds**. It does **not** establish a normal/full authenticated session, does not populate the five canonical Sprint27 full-session keys, and does not read/decrypt the TOTP secret.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Password reset/change/overwrite, automatic/full login from recovery proof, MFA/TOTP recovery, factor replacement/deletion, protected-control recovery, support/admin bypass, email/SMS recovery, passkeys, federation, API-token authentication, Preview/Production auth/schema activation, updater activation, deployment, and release authority remain separately governed and **NOT AUTHORIZED** by Sprint 32 or this reconciliation.
- Sprint 32 publishes the JRN-003 **recovery-proof foundation** only; this reconciliation does not claim end-to-end password recovery completion because password reset/change/overwrite remain excluded.
- This reconciliation selects **no new post-Sprint32 implementation concern** and grants no Sprint33, migration #11, source, Preview, Production, updater, deployment, or release authority. Any subsequent source work requires a separately bounded Product Owner entry gate.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_32_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 30 program-state reconciliation — 2026-08-19

For current identity, security, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint28/post-Sprint29 wording retained below as historical provenance.

- Sprint 21 through Sprint 30 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 29 First-Control-Principal Bootstrap Credential Foundation is published through source PR #195 and closes the first protected-control credential circular dependency without credential overwrite, password recovery, or session creation.
- Sprint 30 Privileged TOTP MFA Foundation is published through PR #199 as `6d41755eba4030c2b0b7c4f3b7a5806b761b0ad7` with tree `bf1d56af5524e77919833bd64b585cdca84af55d` after **22/22** exact-head workflows succeeded.
- Sprint 30 source remained within the exact **46-path** envelope whose sorted-path SHA-256 is `95daaf86ba93ae797fccf3825d65d27acd4f71ee58916898a16fbc83d432a5ce`.
- Canonical source migrations are exactly **#1 through #9**. Migration #9 adds one tenant-scoped TOTP-factor row per identity with encrypted secret ciphertext and monotonic accepted-time-step replay state.
- The direct TOTP dependency is pinned to `spomky-labs/otphp` **11.5.0**; oneQay does not implement custom TOTP/HMAC/Base32 cryptography.
- `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED` remains source-default **false** and Sprint 29–30 delivery remains bounded to **Local/Test/CI**.
- For an armed protected-control principal, password verification alone does not establish the full privileged session. Restricted enrollment/challenge state is used until successful confirmed TOTP challenge establishes full session MFA evidence.
- TOTP secrets are Restricted, encrypted at rest, context-bound to tenant + identity, and never stored as plaintext. Accepted TOTP time steps advance monotonically to deny replay.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Password change/reset/recovery, MFA recovery, factor replacement/deletion, multiple factors, WebAuthn/passkeys, federation, API-token authentication, Preview auth activation, and Production auth activation remain separately governed.
- **JRN-003 remains UNRESOLVED**; this reconciliation creates no password/MFA recovery path.
- The next logical governed identity/security concern is **Privileged Reauthentication / Step-Up Session Freshness Foundation**. DEC-006 already requires risk-based reauthentication/step-up for sensitive operations. This concern is **CANDIDATE / NOT AUTHORIZED** until a separate bounded entry gate freezes semantics, freshness evidence, session transitions, routes, exact source envelope, schema decision, and preservation tests.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_30_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 28 program-state reconciliation — 2026-08-18

The current `[Unreleased]` program state now includes the governed control/identity publication sequence through Sprint 28 and the documentation-only post-Sprint28 canonical reconciliation.

Attribution: **Lab | zefry**

## [Unreleased]

### Added

- Published Sprint 21 — durable tenant-scoped role/permission policy foundation.
- Published Sprint 22 — governed policy-administration foundation.
- Published Sprint 23 — initial tenant-administrator provisioning foundation.
- Published Sprint 24 — protected-control administrator lifecycle foundation.
- Published Sprint 25 — policy-administration delivery foundation with durable session-context re-verification.
- Published Sprint 26 — first-party password credential verification foundation for exact `(tenant_id, identity_id)` ownership, Local/Test/CI only, using canonical migration #7.
- Published Sprint 27 — first-party login/logout and server-side session establishment foundation for Local/Test/CI only, with session fixation protection, CSRF rotation, durable tenant/organizational verification, and no credential mutation.
- Published Sprint 28 — governed two-step first-party initial password enrollment foundation for Local/Test/CI only. Administrator authorization is separated from target password selection; enrollment tokens are generated from `random_bytes(32)`, persisted only as SHA-256 digests, bounded to a 900-second TTL, and credentials are created insert-only with `PASSWORD_DEFAULT`.
- Added canonical migration #8, `0000_00_00_000008_create_initial_password_enrollments.php`, as the only Sprint 28 schema addition. Migrations #1–#7 remain immutable.
- Published Sprint 28 source PR #188 as `b012262b0028c21c7662d5a9edec3cbf249bba5e` after all 19 triggered exact-head workflows completed successfully.
- Published post-Sprint28 canonical reconciliation PR #189 as `68a9b5736a3fc169b50984857954322b169bc42e`.

### Changed

- Reconciled current-facing `README.md`, `PROJECT_MANIFEST.md`, `ROADMAP.md`, `TASKS.md`, `CHANGELOG.md`, `docs/ai/AI_NEXT_TASK.md`, `docs/ai/AI_PROJECT_STATE.md`, `docs/ai/AI_SESSION_STATE.md`, `ARCHITECTURE.md`, `DATABASE.md`, `DEPLOYMENT.md`, `SECURITY.md`, and `.github/workflows/README.md` to the published post-Sprint28 canonical state.
- Updated the current next-work direction from historical Secure Web Updater wording to the separately governed **First-Control-Principal Bootstrap Credential Foundation** concern.
- Updated current workflow inventory to recognize the governed Sprint 21–28 regression chain and Sprint 28's 19/19 exact-head qualification result.
- Updated current database-state wording to recognize canonical migrations exactly #1–#8 while preserving Technical Preview `NO_SCHEMA_CHANGE` and Production schema non-authority.
- Updated current identity/security wording to recognize published credential verification, login/session establishment, and initial-password-enrollment controls without broadening their Local/Test/CI runtime boundary.

### Security

- Preserved exact tenant-scoped credential ownership and deny-by-default tenant/control boundaries.
- Preserved generic credential/login/enrollment failure behavior and anti-enumeration principles.
- Preserved session invalidation/regeneration and CSRF-token rotation on first-party login/logout.
- Preserved one-time digest-only initial-password-enrollment tokens and insert-only credential creation.
- Preserved explicit exclusion of password change, reset, recovery, rotation, revocation, credential overwrite, MFA/passkey/federation delivery, and first-control-principal bootstrap implementation.

### Runtime and release boundaries

- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Durable application persistence remains default-disabled with **`ONEQAY_PERSISTENCE_ENABLED=false`**.
- Sprint 26–28 credential/login/enrollment delivery remains Local/Test/CI-only and absent from Preview and Production.

### Next governed concern

The next logical identity concern is **First-Control-Principal Bootstrap Credential Foundation**. It remains **UNRESOLVED / NOT AUTHORIZED** and requires a separately published bounded entry gate before any source implementation.

This changelog reconciliation does **not** authorize Sprint 29 implementation, migration #9, any assumption that migration #9 is required, new source/dependency/workflow/runtime/schema mutation, password lifecycle expansion, Technical Preview credential activation, Production authentication/enrollment, updater activation, deployment, Release, Phase 0 Exit, Sprint 14, or Production.

## Historical changelog provenance

Detailed historical changelog entries through M7.5, prior decisions, governance recurrences, and earlier publication snapshots remain immutable in Git history. Current-state interpretation must follow this post-Sprint28 section together with `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`.

No product release exists yet. A dated/tagged product version will be added only through the separately governed release process.

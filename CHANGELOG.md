# Changelog

## Canonical post-Sprint40 M7.5 preservation closure — 2026-08-27

This current-facing section supersedes older pre-Sprint40/current-state wording retained below as historical provenance. It records repository state only and creates no new implementation or lifecycle authority.

- Canonical `main`: `fe502ee40471633e292606ef203a2f0e90754175`; tree `6b494a9a152539a0e922bb564ff96930ff82d86c`; GitHub signature **verified / valid**.
- Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** source is **IMPLEMENTED / PUBLISHED** through PR #286 as `03e86d4e677632a7516c8f4ed2c34045647b774a`, from qualified source head `c8d0f1ab6477f1c743247a519cbc1e6996365199`.
- The Sprint40 source envelope remains exactly **8 paths** with SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- Canonical source migration files are exactly **#1–#14**. Migration #14 exists in source and adds only `first_party_authentication_enabled`; this does **not** authorize or imply schema application in Technical Preview or Production.
- Post-Sprint40 historical-regression preservation is published through PR #295 (Sprint32 horizon) and PR #296 (Sprint39 horizon). The bounded M7.5 seven-workflow correction is published through PR #297 and corrected for canonical-main push behavior through PR #298.
- The governed seven-workflow changed-path fingerprint remains `4784ffca1c940d3fa54a2a3988ead07e2de993bde8d3af2bd41014dbdf905be0`.
- Canonical main-push oracle **M7.5 Technical Preview Release Artifact #307** (run `33040247339`) completed **SUCCESS** on `fe502ee40471633e292606ef203a2f0e90754175`. Full-source tests, historical M7.2/M7.3 fixtures with temporary migration #10–#14 isolation, restoration verification, POS/Preview/background regressions, manifest/checksum validation, deterministic archive reproduction, artifact upload, and tracked-source cleanliness all succeeded.
- The oracle and generated qualification artifact are CI evidence only. **Technical Preview remains `NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`; Production remains `NO-GO / NOT AUTHORIZED`; updater remains `DISABLED / UNWIRED`; deployment and release remain `NOT AUTHORIZED`.**
- PR #295–#298 changed workflow-governance/preservation behavior only; they did not add application source, apply schema, activate runtime, or grant standing successor authority.
- No post-Sprint40 successor implementation concern is selected or authorized by this reconciliation. Any next concern requires fresh canonical-main verification and separate bounded Product Owner authority.

Attribution: **Lab | zefry**


## Canonical Sprint40 pre-source documentation synchronization — 2026-08-25

This current-facing entry records the governed pre-source Sprint40 state. Historical changelog sections remain provenance.

### Added

- Recorded Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** as the selected concern after published entry-gate PR #268 and schema/source-envelope gate PR #270.
- Recorded the future migration #14 selection: `apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`, adding only non-null boolean `first_party_authentication_enabled` default `true` to `oneqay_identities` when the later source stage is separately authorized.
- Recorded the frozen exact eight-path future Sprint40 source envelope with SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- Recorded source-preservation publication PR #271, compatibility corrections PR #272/#273, and documentation-sync preservation predecessor PR #274.

### Changed

- Synchronized exactly 13 canonical documentation paths to the current pre-source Sprint40 state under preserved fingerprint `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.
- Current canonical source migrations remain exactly **#1–#13**; migration #14 is selected for later implementation but does not yet exist or apply on canonical `main`.
- Current next-work direction is the separately governed frozen Sprint40 source implementation after this documentation synchronization is published and canonical `main` is freshly verified.

### Security and lifecycle boundaries

- Sprint40 identity eligibility semantics remain request-time, server-authoritative, and fail-closed; existing credential/factor epochs, session revocation/inventory, idle/absolute lifetime, tenant membership, and organization/outlet/device revalidation remain independent controls.
- This documentation synchronization creates no source/dependency/workflow YAML/runtime mutation, no migration #14 creation or execution, no route/API/payload/audit-event/feature-arm addition, and no Preview or Production activation.
- Technical Preview remains **`NO_SCHEMA_CHANGE` / Sprint40 not activated**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.

Attribution: **Lab | zefry**

## Canonical post-Sprint 33 program-state reconciliation — 2026-08-20

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint32 wording retained below as historical provenance.

- Sprint 21 through Sprint 33 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 33 Recovery-Bound Password Reset Completion Foundation is published through source PR #213 as `9eba56d92b4b714225d677990ffed93687b0b2cb` with tree `492e723b6343dab518b43645883976ad20f0054c`, parent `c89baa55318dca230cd0ef792df80e3d54b8165d`, and a verified/valid publication signature after **24/24** exact-head pull-request workflows succeeded.
- The qualified Sprint33 source head was `a7a50644cbe67e6f08138c79cf50a9350e8e220d`; source remained exactly **39 paths** with sorted-path SHA-256 `04a1177c12712183a7dda4ae81be1356c0e41294533336c9f999d376c224712a`.
- Sprint33 entry-gate PR #211 and source-envelope gate PR #212 remain published provenance; their authorities and PR #213 merge authority are consumed and grant no standing successor authority.
- Canonical source migrations remain exactly **#1 through #10** and are unchanged by Sprint33. No migration #11 is authorized.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default and recovery execution remains bounded to **Local/Test/CI**.
- Sprint32 proof still establishes only `password_reset_required` restricted state for exactly **600 seconds**; Sprint33 binds the consumed server-owned recovery `code_id` into that restricted evidence and exposes only `POST /auth/recovery/password-reset` inside the same bounded recovery arm.
- Reset accepts only opaque `password` input of **12–4096 bytes**, performs no trim/normalization, hashes with `PASSWORD_DEFAULT`, updates only the existing exact credential row, revokes remaining unused recovery codes, and appends exactly one secret-free `password_reset_completed` audit event atomically.
- Credential epoch is derived without schema change from the durable count of `password_reset_completed` rows. Fresh normal login captures the epoch; stale, malformed, negative, future, or post-reset legacy-missing epoch evidence fails closed as applicable.
- Protected-control principals and identities with confirmed privileged TOTP remain ineligible for recovery completion; TOTP secret material is not read, decrypted, replaced, deleted, or mutated.
- Successful reset invalidates the restricted session and regenerates CSRF but establishes no normal/full login, MFA evidence, step-up evidence, or epoch evidence; fresh normal login remains mandatory.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Authenticated in-session password change, administrative password overwrite, MFA/TOTP recovery and factor lifecycle, protected-control recovery bypass, support/admin bypass, email/SMS recovery delivery, passkeys/WebAuthn, federation, API-token authentication, Preview/Production auth/schema activation, updater activation, deployment, and release remain separately governed.
- Sprint32 + Sprint33 now form a bounded Local/Test/CI end-to-end recovery sequence for eligible non-protected identities without confirmed privileged TOTP, but this does not activate recovery in Technical Preview or Production.
- This reconciliation selects **no new post-Sprint33 implementation concern** and grants no Sprint34, migration #11, source, Preview, Production, updater, deployment, or release authority.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_33_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

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

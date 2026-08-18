# Changelog

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

# AI Project State

## Canonical post-Sprint40 M7.5 preservation closure — 2026-08-27

This current-facing section supersedes older pre-Sprint40/current-state wording retained below as historical provenance. It records repository state only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `fe502ee40471633e292606ef203a2f0e90754175`; tree `6b494a9a152539a0e922bb564ff96930ff82d86c`; GitHub signature **verified / valid**.
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


## Canonical Sprint40 pre-source project state — 2026-08-25

For current program-state interpretation, this section supersedes older current-facing sections retained below as historical provenance.

- Sprint 21 through Sprint 39 governed control/identity/session foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within bounded authority.
- Sprint40 selected concern is **First-Party Session Identity Disablement Revalidation Foundation**.
- Sprint40 entry-gate PR #268 and schema/source-envelope gate PR #270 are **PUBLISHED**.
- Source-preservation predecessor PR #271, compatibility corrections PR #272/#273, and documentation-sync preservation predecessor PR #274 are **PUBLISHED**.
- Pre-synchronization canonical main is `7be563d66e2f8441cf28dd2ed9ce5d6c0704098f`, tree `adbbce29218e312b243076dc3ee984e68ce79b65`, with verified/valid signature.
- Current canonical source migrations remain exactly **#1 through #13**. Migration #14 is selected for the later Sprint40 source stage but **does not yet exist or apply on canonical `main`**.
- The selected future migration is `apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`, adding only non-null boolean `first_party_authentication_enabled` default `true` to `oneqay_identities`.
- Frozen future Sprint40 source envelope is exactly **8 paths**, SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- This documentation synchronization is exactly **13 paths**, SHA-256 `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.
- Sprint40 source semantics are server-authoritative and fail-closed: exact current identity eligibility must remain valid at request time in addition to credential/factor epoch, durable logical-session authority, revocation/inventory, idle/absolute lifetime, tenant membership, and exact organization/outlet/device access controls.
- Sprint40 source is **NOT YET IMPLEMENTED** by this project-state synchronization. No route/API/payload/audit-event/feature-arm/runtime change is introduced here.
- Technical Preview remains **`NO_SCHEMA_CHANGE` / Sprint40 not activated**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.
- After this documentation synchronization is published, the next logical governed stage is the already-frozen eight-path Sprint40 source implementation against a freshly verified canonical `main`, requiring separate source authority.

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

## Canonical post-Sprint 28 project state — 2026-08-18

For current project-state interpretation, this section supersedes all older current-facing M7.5/updater-next-work assertions retained below as historical publication provenance.

- Sprint 21 through Sprint 28 governed foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 28 source publication PR #188 was squash-published as `b012262b0028c21c7662d5a9edec3cbf249bba5e`; post-Sprint28 reconciliation PR #189 was squash-published as `68a9b5736a3fc169b50984857954322b169bc42e`.
- Current published control/identity chain includes durable role/permission policy, policy administration, initial tenant-admin provisioning, protected-control admin lifecycle, policy-administration delivery, tenant-scoped credential verification, first-party login/session establishment, and two-step initial password enrollment.
- Canonical migrations are exactly **#1–#8**. Migrations #1–#7 remain immutable; migration #8 is additive and forward-only.
- Sprint 26–28 credential verification/login/enrollment remain bounded to **Local/Test/CI** and do not activate in Preview or Production.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Durable application persistence remains default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- First-control-principal bootstrap credential creation remains **UNRESOLVED / NOT AUTHORIZED** and is the next logical separately governed identity concern.
- Password change/reset/recovery/rotation/revocation, Production authentication/enrollment activation, and updater activation remain separately governed.

Canonical detailed publication evidence is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. This checkpoint does not claim a permanently current live SHA; fresh GitHub verification remains mandatory before any future lifecycle mutation.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current project-state interpretation, this section supersedes older current-facing M7.5/P1/P2 assertions below while preserving all historical publication and decision provenance.

- M7.5 Preview Runtime Qualification: **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**.
- Canonical evaluator after PR #129 and cleanup PR #130: **29 VERIFIED / 0 BLOCKED**; `lifecycle_authority_created=false`.
- Mandatory M7.5 blockers: **NONE**.
- `ENGINE:TENANT_ISOLATION`, `ENGINE:RESTORE_VERIFIED`, and `RUNTIME:BACKUP_RESTORE` are **VERIFIED** within the bounded non-Production Technical Preview evidence catalog.
- M7.6 and M7.7 remain **NOT AUTHORIZED**.
- Phase 0 remains **IN PROGRESS**; Phase 0 Exit remains **NOT APPROVED**.
- Sprint 14, Release, and Production remain **NOT AUTHORIZED**; Production readiness remains **NO-GO**.
- The next candidate engineering direction is separately gated Secure Web Updater architecture/release-control-plane foundation.

This is semantic state consolidation only. It creates no application source, dependency, workflow YAML, database/schema/SQL/migration, cPanel, deployment, restore, Release, Production, or later-milestone authority.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current project-state interpretation, this section supersedes older current-facing M7.5/P1/P2 assertions below while preserving all historical publication and decision provenance.

- M7.5: **IN PROGRESS / QUALIFICATION MATERIAL PROGRESS**.
- Canonical evaluator after PR #124: **26 VERIFIED / 3 BLOCKED**; overall qualification **BLOCKED / INCOMPLETE**; `lifecycle_authority_created=false`.
- Only three blockers remain: `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`, `ENGINE:TENANT_ISOLATION:PARTIAL`, and `RUNTIME:BACKUP_RESTORE:PARTIAL`.
- Bounded P1/cPanel Technical Preview evidence materially verifies the web/runtime, relational, security, observability, resource, scheduler, rollback/deployment-recovery, background-execution, and Preview queue controls already governed as `VERIFIED`; older snapshots below must not regress them.
- Existing application-level and bounded relational tenant-scoping evidence remains material, but complete durable database-backed tenant-isolation semantics are not yet proven.
- Backup/export evidence and application-release rollback evidence do not prove successful database restore.
- M7.6 and M7.7 remain **NOT AUTHORIZED**.
- Phase 0 remains **IN PROGRESS**; Phase 0 Exit remains **NOT APPROVED**.
- Sprint 14, Release, and Production remain **NOT AUTHORIZED**; Production readiness remains **NO-GO**.

This is a semantic state consolidation only. It does not store a new hard-coded SHA as permanently current GitHub state and creates no application source, dependency, database, schema, SQL, migration, restore, deployment, Release, Production, or later-milestone authority.

## Project identity

- Project: oneQay
- Tagline: The Future of Intelligent Business Management
- Developer and Product Engineering Entity: Lab | zefry
- Repository: `labzefry/oneQay`
- GitHub repository role: Single Source of Truth
- Product attribution: Lab | zefry
- Canonical product name: `oneQay`

Engineering AI/tooling identity is governance metadata only and is not product authorship attribution.

## Canonical checkpoint semantics

This tracked checkpoint does **not** store a hard-coded SHA as the permanently current live GitHub `main` or live tree.

Stable provenance fields in this document describe published milestone identity or the verified baseline that existed before the checkpoint work began. They are historical evidence, not a claim that the referenced SHA remains the live repository head forever.

Before any lifecycle mutation, branch creation, implementation decision, Ready transition, or Merge transition, the live repository state MUST be obtained by Minimal Delta Verification from GitHub because GitHub is the Single Source of Truth.

The required live verification includes, as applicable:

- default-branch head;
- default-branch tree;
- active PR state;
- active PR exact head and tree;
- required checks;
- reviewer state;
- unresolved review threads.

A checkpoint update must not be created merely to replace a stored `current main` SHA after every publication.

## Canonical delivery state

- Canonical delivery phase: Phase 0 — Governance and Discovery
- Canonical Phase 0 status: In Progress
- Current engineering workstream: M7 — Technical Preview Implementation Enablement
- M5.1: PUBLISHED / COMPLETE through PR #66
- M5.2: PUBLISHED / ENFORCEMENT COMPLETE through PR #67
- M5.3: PUBLISHED / COMPLETE through PR #68
- M6 Enterprise Vision Canonicalization: PUBLISHED / PUBLICATION COMPLETE through PR #69
- M6 Post-Publication State Reconciliation: PUBLISHED through PR #70
- M6 Closure — Checkpoint Semantics Correction: PUBLISHED / COMPLETE through PR #71
- GOV-051 Enterprise Vision substantive decision: APPROVED / DECISION COMPLETE
- Enterprise Vision decision status: Approved
- M7.0 Controlled Implementation Bridge: DONE / PUBLISHED
- M7.1 Application Skeleton & Configuration Boundary: DONE / PUBLISHED through PR #92
- M7.2 Tenant Kernel & Isolation Foundation: DONE / PUBLISHED through PR #93
- M7.3 Identity / Organization / Outlet / Device Minimum: DONE / PUBLISHED through PR #94
- M7.4 POS Core Synthetic Vertical Slice: DONE / PUBLISHED through PR #96
- M7.4A Technical Preview Interaction Layer: DONE / PUBLISHED through PR #98
- DEC-005R Portable Relational Persistence Architecture: APPROVED / DECISION COMPLETE / PUBLISHED through PR #100
- Canonical next gated micro-milestone: M7.5 — Preview Runtime Qualification
- M7.5 Preview Runtime Qualification: BLOCKED / NOT AUTHORIZED; actual sanitized P2 target evidence, DEC-009 capability verification, and selected relational engine-profile qualification under DEC-005R required
- M7.6 Preview Deployment / Recovery Rehearsal: BLOCKED / NOT AUTHORIZED
- M7.7 Technical Preview Acceptance: BLOCKED / NOT AUTHORIZED
- Sprint 12: Published
- Sprint 13: Published
- Sprint 14: Not Authorized
- Production readiness: NO-GO
- Deployment: None / Not Authorized
- Release: None / Not Authorized
- Production migration: Not Performed

Track A Controlled Application Engineering has published the bounded M7.4 POS core and M7.4A Technical Preview interaction layer. Track B Preview Runtime Qualification remains separately gated; M7.5 cannot begin until actual sanitized P2 target evidence is supplied, verified against DEC-009 mandatory capabilities, and the selected relational engine profile is qualified under DEC-005R, followed by separate Product Owner authority. Both tracks converge before Technical Preview deployment/acceptance.

Issue #23 contains historical pre-M7.0 planning language. That historical wording is not current authority and must not override the later governed Phase 0 Controlled Implementation Bridge. Issue #23 mutation remains separately gated.

## Current canonical decision state

- DEC-000 Product Vision and Decision Rights: **APPROVED / DECISION COMPLETE**; GD-003 is Approved through DEC-000; no implementation authority.
- DEC-001 MVP Scope and Non-Scope: **APPROVED / DECISION COMPLETE**; first bounded MVP delivery slice is **POS CORE TRANSACTION & OUTLET OPERATIONS**; no implementation authority.
- DEC-002 Backend Language / Application Framework: **APPROVED / DECISION COMPLETE**; ADR-001 Accepted through its governed reconciliation.
- DEC-003 Frontend / PWA Stack: **APPROVED / DECISION COMPLETE**; ADR-002 Accepted through its governed reconciliation.
- DEC-004 Android Approach: **APPROVED / DECISION COMPLETE**; ADR-008 is the Accepted representation of DEC-004.
- DEC-005 Database Engine and Physical Tenancy Model: **APPROVED HISTORICAL DECISION / PARTIALLY SUPERSEDED BY DEC-005R**; historical MySQL Server selection remains preserved, while shared database/shared schema, tenant-isolation, Infrastructure ownership of vendor-specific behavior, schema-evolution, and recoverability principles remain preserved according to DEC-005R dispositions.
- DEC-005R Portable Relational Persistence Architecture: **APPROVED / DECISION COMPLETE / PUBLISHED through PR #100**; current architecture requires database-engine-neutral Domain/Application, zero database-vendor dependency in business rules, qualified MariaDB/MySQL/PostgreSQL engine-profile direction, Database Portability Contract direction, and no implementation authority.
- DEC-006 Authentication / MFA / Session Architecture: **APPROVED / DECISION COMPLETE**; ADR-004 Accepted through its governed reconciliation; JRN-003 remains Unresolved.
- DEC-007 Payment Provider and Compliance Boundary: **APPROVED / DECISION COMPLETE**; ADR-005 Accepted through its governed reconciliation.
- DEC-008 Offline POS Semantics and Conflict Resolution: **APPROVED / DECISION COMPLETE**; ADR-006 Accepted through its governed reconciliation.
- DEC-009 Deployment Stage 1 Runtime Requirements: **APPROVED / DECISION COMPLETE**; ADR-007 Accepted through its governed reconciliation; current database dependency requires an authorized and runtime-qualified relational engine profile under DEC-005R rather than sole canonical MySQL Server.
- DEC-010 Product License and Third-Party Notice Policy: **APPROVED / DECISION COMPLETE**; oneQay remains **PROPRIETARY / ALL RIGHTS RESERVED**.
- DEC-011 Data Retention, Privacy, and Jurisdiction: **APPROVED / DECISION COMPLETE**; initial commercial/launch jurisdiction remains not yet canonically selected.
- DEC-012 RPO/RTO and Support Objectives: **APPROVED / DECISION COMPLETE**; final numerical Production RPO/RTO/SLO and customer-contractual SLA remain deferred.
- DEC-010 Supplement — Dependency Cost Baseline, Open-Source Preference, and Visualization Technology Direction: **APPROVED / DECISION COMPLETE / PUBLISHED through PR #87**; Apache ECharts remains a default Web/PWA visualization technology candidate / approved technology direction only; dependency/package adoption and implementation remain separately gated.

## M7 publication facts

M7.0–M7.4A are governed repository facts and do not create standing future authority:

- M7.0 — Controlled Implementation Bridge: governed publication complete before M7.1 source work.
- M7.1 — Application Skeleton & Configuration Boundary: PR #92 CLOSED / MERGED; resulting main `82b2bffb3b087aa818c2a229d2b7e0c07ea158ec`.
- M7.2 — Tenant Kernel & Isolation Foundation: PR #93 CLOSED / MERGED; resulting main `ba95f745869092d251230fb5a3db2c08e42f4941`.
- M7.3 — Identity / Organization / Outlet / Device Minimum: PR #94 CLOSED / MERGED; source head `67d7b890fe95db9c32d4e2dbc432be193bb064a9`; source tree `3cb925e9234bc28b64aec3a1f6efd1a03756221c`; resulting main `9b43f6be520b64e47bfa9a66be577dab20f69bd9`.
- M7.4 — POS Core Synthetic Vertical Slice: PR #96 CLOSED / MERGED; source head `0659e0e3c2ab7f8ec9f12653b773aaa4391e931b`; source tree `f67f9b75a11b2710b58a9928f5b73f876cba2cef`; resulting main `4981fca92e7de028ca55e746b36af6afe0d3e7f2`.
- M7.4A — Technical Preview Interaction Layer: PR #98 CLOSED / MERGED; source head `893b73b8f20b2ede0d3a8896b3a015df5370dbed`; source tree `cdc140e5061481bec4b6b691b02b2b234181c2fb`; published commit `c0bdf8ad7539a5c83de2e5183fbf2eda9f17f02b`; published tree `cdc140e5061481bec4b6b691b02b2b234181c2fb`; source tree equals published tree: Yes.

Those SHAs are stable publication provenance only and are never substitutes for fresh live GitHub verification.

M7.1 preserves the application/configuration foundation. M7.2 preserves tenant context, server-verified membership, and cross-tenant isolation controls. M7.3 preserves first-party identity separation, tenant membership separation, server-controlled organization/outlet/device authority, and deny-by-default organizational context. M7.4 preserves server-authoritative bounded POS transaction behavior. M7.4A preserves the synthetic interaction journey while reusing M7.4 `CompleteSyntheticSale`; it does not authorize M7.5, durable Production persistence, deployment, release, Phase 0 Exit, Sprint 14, or Production.

## DEC-005R publication provenance

DEC-005R publication is stable historical provenance and not a permanently current live-head claim:

- publication PR: #100 CLOSED / MERGED;
- source head: `8ec7069b08c9127e402fa80e5e79ca26be2b63d6`;
- source tree: `0862c851d30c11c37c39d13aa5660d042da91989`;
- published squash commit: `b5cbdeb6ea45d4f159f3d1cd39cadc561605c5ff`;
- published tree: `0862c851d30c11c37c39d13aa5660d042da91989`;
- source tree equals published tree: Yes;
- current decision: **DEC-005R — Portable Relational Persistence Architecture — APPROVED / DECISION COMPLETE**;
- DEC-005 remains an approved historical decision and is partially superseded only as recorded by DEC-005R;
- no source, schema, SQL, migration, cross-engine CI, DBME implementation, M7.5, deployment, release, or Production authority was created by publication.

## Verified baseline before GOV-051 decision record

Fresh Minimal Delta Verification before the bounded GOV-051 decision-record branch confirmed:

- verified repository baseline: `762149757e4bc1fa79cc16bc4761f4147be0f7ea`;
- verified baseline tree: `4d16f322b1bc8f2b666eef87ce4a1caaa6755e4f`;
- publication PR: #71;
- PR #71 state: CLOSED / MERGED;
- PR #71 source head: `19c723f32c62c982a80e1d8a520ab6ff5a189e2c`;
- PR #71 source tree: `4d16f322b1bc8f2b666eef87ce4a1caaa6755e4f`;
- PR #71 published commit: `762149757e4bc1fa79cc16bc4761f4147be0f7ea`;
- PR #71 published tree: `4d16f322b1bc8f2b666eef87ce4a1caaa6755e4f`;
- PR #71 published parent: `b26c4690d68db61118ee1c4cecbb87e9418d791f`;
- source tree equals published tree: Yes;
- canonical Enterprise Vision artifact: `docs/handbook/ENTERPRISE_VISION.md`;
- approved artifact blob: `bb1cace72a6fdb359e15e22467443d9f3916c336`.

These values are stable publication and decision provenance, not permanently current-live-head declarations.

## Canonical naming

The canonical product name is **oneQay**.

Current and future canonical product identity must use exactly `oneQay`. Non-canonical active variants include `OneQay`, `ONEQAY`, `Oneqay`, and `oneqay`.

Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, historical commit messages, historical branch names, and quoted historical evidence are preserved as recorded.

## M6 Enterprise Vision publication

Product Owner authorized M6 with:

**START M6 — ENTERPRISE VISION CANONICALIZATION**

M6 publication lifecycle completed through PR #69.

Publication identity:

- base: `e45f5b4c0f143abc6e255e4e8550bf3504348aae`;
- base tree: `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`;
- source branch: `agent/m6-enterprise-vision-canonicalization`;
- source head: `e6a3345b09a6b270ac7e09abd78c6356f426e363`;
- source tree: `567df997bae70090b19465c75e4cc3b1e23b6579`;
- published commit: `0b7b28028966ac38af0f32960054210c3a083916`;
- published tree: `567df997bae70090b19465c75e4cc3b1e23b6579`;
- source tree equals published tree: Yes.

The published canonical Enterprise Vision representation defines oneQay as:

**Enterprise Intelligent Business Management Platform**

The canonical M6 document is:

`docs/handbook/ENTERPRISE_VISION.md`

Publication canonicalized the representation and location of the Enterprise Vision. PR #69 did **not** by itself promote the Enterprise Vision decision status from Proposed to Approved; GOV-051 later provided that separate substantive Product Owner approval. Neither publication nor GOV-051 creates implementation authority or implies production readiness.

## M6 post-publication reconciliation publication

PR #70 published the bounded reconciliation of mutable program-state documentation after PR #69.

PR #70 publication established at that point that:

- M6 Enterprise Vision publication was complete;
- A-09 was resolved at canonical representation/publication level only;
- A-10 was resolved for current/future-facing canonical product naming;
- Enterprise Vision substantive decision remained Proposed at the time of PR #70;
- GOV-047 through GOV-050 represented completed publication/reconciliation work;
- GOV-051 remained the separate substantive Enterprise Vision Product Owner decision;
- Phase 0 remained In Progress;
- Sprint 14 remained Not Authorized;
- production readiness remained NO-GO.

PR #71 subsequently published M6 Closure — Checkpoint Semantics Correction and removed the self-referential live-head reconciliation pattern.

## GOV-051 substantive Enterprise Vision decision

The Product Owner explicitly approved GOV-051 after review of the canonical artifact on the verified PR #71 publication baseline.

Decision facts:

- decision: GOV-051 — Enterprise Vision substantive Product Owner decision;
- result: **APPROVED**;
- approved Enterprise Vision: **Enterprise Intelligent Business Management Platform**;
- approved statement: **oneQay is an Enterprise Intelligent Business Management Platform.**;
- verified baseline: `762149757e4bc1fa79cc16bc4761f4147be0f7ea`;
- verified baseline tree: `4d16f322b1bc8f2b666eef87ce4a1caaa6755e4f`;
- canonical artifact: `docs/handbook/ENTERPRISE_VISION.md`;
- approved artifact blob: `bb1cace72a6fdb359e15e22467443d9f3916c336`.

GOV-051 establishes the Enterprise Vision boundary, enterprise design qualities, directional Enterprise Capability Map, and conceptual E0–E5 evolution as binding long-term product direction.

GOV-051 does not approve MVP scope, Sprint 14, implementation, bounded contexts, GD-003, GD-007, ADR-001 through ADR-007, framework/provider choices, SQL/migration, production database changes, deployment, release, JRN resolution, or production-readiness promotion.

## Enterprise capability direction

The Approved Enterprise Vision groups the directional capability map into:

1. Core Business Platform — Tenant & Organization, Identity & Access, Master Data, POS / Commerce, Inventory, Procurement, Finance / Accounting, CRM, HRM, Reporting & Business Intelligence.
2. Platform Capabilities — Workflow, Notification, Audit, File / Document, Search, API, Integration, Webhook / Event Integration, Configuration, Localization, Observability, Recovery & Operational Control.
3. Extensibility — Marketplace, Plugin / Extension, Public API, Partner Integration, Developer / Integration Experience.
4. AI Platform — AI Assistant, AI Insight, AI Automation, AI Recommendation, AI Analytics, AI Gateway / Policy Boundary.
5. Channels — Web Application, PWA, Mobile / Android, Admin Platform, Public / Customer-facing surfaces, and API/partner consumers.

Capability-map presence is not implementation authority.

## Staged product evolution

The Approved Enterprise Vision defines conceptual stages only:

- E0 — Foundation
- E1 — Core Transaction Platform
- E2 — Business Management
- E3 — Enterprise Management
- E4 — Intelligence
- E5 — Ecosystem

These stages are not release commitments and do not start without separate Product Owner authority and applicable gates.

## Canonical Phase 0 semantics

Phase 0 **In Progress** describes the governance/discovery program state. It does not mean that the repository contains no technical source code.

Bounded Platform Foundation implementation has been published through Sprint 13, followed by separately governed bounded M7.0–M7.4A Technical Preview work. These publications are separate from Phase 0 exit and must not be interpreted as final/business application approval, automatic Phase 1 completion, Sprint 14 authority, production readiness, deployment authority, release authority, or migration authority.

The canonical blocked boundary remains:

**Final/business/production application implementation: Blocked unless separately authorized.**

## Published Platform Foundation through Sprint 13

Canonical Sprint 13 capability:

**Schema Change Review and Approval Envelope Foundation**

Canonical identity:

- PR: #64
- Base: `de3c8c73c0002915c735dc1dfa29828e1781e71d`
- Source head: `4a2e44cc31361954b126e8857de65fcccca30445`
- Source tree: `5a0adb0d2ce80338f9f9d782f0871fb2115afd5d`
- Published commit: `ebe6abcf77263bf644565ca2fbe2b2844416d49b`
- Published tree: `5a0adb0d2ce80338f9f9d782f0871fb2115afd5d`
- Source tree equals published tree: Yes

PR #65 reconciled the canonical Sprint 13 publication state and was published as `7a9def560466fc8bf81529c2b5125c6ac19a96b5`.

## M5 publication facts

### M5.1

M5.1 — Canonical State Reconciliation is PUBLISHED / COMPLETE through PR #66 and published commit `153a33a4a2b5edb4a31285eca7d3491f9589b778`.

### M5.2

M5.2 — CI & Lifecycle Control Hardening is PUBLISHED / ENFORCEMENT COMPLETE through PR #67.

Published identity:

- commit: `512344d0497787c729242cb1fd2d7d02ecfc40c2`;
- tree: `0f0af1c1acab208c704fbdf05b19014127abddbb`.

Required protected contexts:

1. `governance-validation`
2. `markdown-lint`
3. `secret-scan`
4. `php-foundation-regression`
5. `product-owner-merge-authority`

### M5.3

M5.3 — Governance & Program State Synchronization is PUBLISHED / COMPLETE through PR #68.

Published identity:

- source head: `aa799e657070a7d3283110a73a411f54a73b972c`;
- source tree: `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`;
- published commit: `e45f5b4c0f143abc6e255e4e8550bf3504348aae`;
- published tree: `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`;
- source tree equals published tree: Yes.

M5.3 resolved A-06, A-07, and A-08 for current canonical program state.

## Canonical checkpoint authority

Canonical mutable AI checkpoint files are located only under `docs/ai/`:

1. `docs/ai/AI_SESSION_STATE.md`
2. `docs/ai/AI_PROJECT_STATE.md`
3. `docs/ai/AI_NEXT_TASK.md`

Root files with matching names remain deprecated pointer stubs only.

## Governance preservation

- Phase 0: In Progress
- Phase 0 Exit: Not Approved
- Enterprise Vision decision status: Approved through GOV-051
- ADR-001 through ADR-007: Accepted through their separately governed DEC reconciliations
- ADR-008: Accepted representation of DEC-004
- GD-003: Approved through DEC-000
- GD-007: Proposed
- JRN-003 and JRN-013: Unresolved
- Actual P2 target: Pending external input unless fresh evidence proves otherwise
- Final tenant data model: Not Started
- Final business schema: Not Started
- Production migration: Not Performed
- Production database usage: None
- Production table: None
- Sprint 14: Not Authorized
- Deployment: Not Authorized
- Release: Not Authorized
- Production: Not Authorized
- Production readiness: NO-GO

## M5/M6 anomaly status

- A-01 stale canonical AI checkpoint: Resolved by M5.1 and subsequent material checkpoint updates.
- A-02 duplicate root AI checkpoint: Resolved by M5.1 using pointer stubs.
- A-03 lifecycle authority not technically enforced: Resolved by M5.2 enforcement.
- A-04 review history contamination: canonical disposition recorded; historical evidence preserved.
- A-05 PHP regression not in GitHub CI: Resolved by M5.2 enforcement.
- A-06 Phase 0 semantic ambiguity: Resolved through M5.3 publication.
- A-07 ROADMAP / TASKS synchronization: Resolved through M5.3 publication.
- A-08 attribution/collaboration metadata supersession: Resolved through M5.3 publication; canonical product attribution is Lab | zefry.
- A-09 Enterprise Vision canonicalization: Resolved at representation/publication level through PR #69; separate substantive Enterprise Vision decision Approved through GOV-051.
- A-10 product-name capitalization inconsistency: Resolved for current/future-facing canonical material through PR #69; immutable historical evidence remains preserved.

## Current authority boundary

**NO STANDING IMPLEMENTATION OR MILESTONE AUTHORITY.**

M7.4A is **DONE / PUBLISHED** through PR #98. The canonical next gated micro-milestone is M7.5 — Preview Runtime Qualification, but it is **BLOCKED / NOT AUTHORIZED**. It requires actual sanitized P2 target evidence, DEC-009 capability verification, selected relational engine-profile qualification under DEC-005R, fresh GitHub Minimal Delta Verification, and separate explicit Product Owner authority before any M7.5 work can begin.

No standing Phase 0 exit, Sprint 14, deployment, release, or Production authority is stored in this checkpoint. Substantive decision authority, preparation authority, independent exact-head review, Product Owner READY authority, and Product Owner MERGE authority remain separate whenever applicable.

This checkpoint creates no source/application implementation, dependency/package adoption, database/schema/SQL/migration, infrastructure, runtime qualification, deployment, release, Production, Phase 0 exit, or Sprint 14 authority.

Attribution: Lab | zefry

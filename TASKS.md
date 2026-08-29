# oneQay Tasks

## Canonical post-Sprint43 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint42/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `034c3dcc86d202bb076c3b03d5cb933f856ec308`; tree `51d05048de8d842465baa7999cb4b68b2b4444c7`; GitHub signature **verified / valid**.
- Sprint43 **First-Party Identity Authentication Eligibility Reactivation Foundation** is **IMPLEMENTED / PUBLISHED** through PR #350 as `034c3dcc86d202bb076c3b03d5cb933f856ec308`, from qualified exact source head `7036fbf7613d086a25bb36bc63c33607036b87e3`.
- Sprint43 source remains exactly **9 paths** with sorted newline-terminated SHA-256 `3d0293362f451fe4bf472d0d2c38c3eec3d67df75b451ba5b273e8dbdb0f2eed`.
- Canonical source migrations remain exactly **#1–#15**. Sprint43 is **NO_SCHEMA_CHANGE**; migration #16 is **NOT SELECTED** and does not exist.
- Sprint43 adds only server-authorized reactivation for one exact same-tenant ordinary identity through the dedicated `reactivate` operation and the exact transition `first_party_authentication_enabled: false -> true`. Authority remains server-derived tenant-scoped policy administration plus `AdministrationPermission::MANAGE`; self, protected-control, missing, unauthorized, malformed, and cross-tenant targets remain fail closed.
- The reactivation API is separate from Sprint41 disablement: `POST /administration/identities/{identity_id}/authentication-reactivation`; the request body remains exactly `mutation_id`. No generic toggle, caller-selected operation, boolean setter, bulk mutation, timed reactivation, or cross-tenant authority is introduced.
- Durable evidence reuses `oneqay_identity_authentication_eligibility_mutations` with the exact operation value `reactivate`, deterministic fingerprint binding, `applied` / `no_change` outcomes, exact replay, mutation-ID conflict denial, and deterministic convergence.
- Reactivation never creates or restores framework/logical sessions, never clears `revoked_at_unix`, never revives Sprint42-terminated or expired sessions, and never synthesizes MFA, step-up, enrollment, or recovery authority. Fresh authentication remains mandatory. Sprint36–Sprint42 session, revocation, lifetime, organizational-access, request-time eligibility, disablement, and disablement-triggered termination semantics remain preserved.
- Reactivation does not restore or mutate password/hash, credential epoch, TOTP/recovery factors, factor epoch, tenant/organization/outlet/device membership, role assignments, permissions, grants, or protected-control state.
- The bounded Sprint43 publication chain includes entry gate PR #340, schema/source-envelope preservation and publication PRs #341–#342, bounded compatibility publications #345–#347 and #349, and final source PR #350. Closed unmerged compatibility attempts do not constitute canonical authority.
- The final Sprint43 exact source head completed **36 materially triggered pull-request checks / 36 success / 0 non-success**, and the exact-head `product-owner-merge-authority` status completed **success** before squash publication.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; migration #15 and Sprint42/Sprint43 source remain unactivated/unapplied in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment, release, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint43 successor implementation concern is selected by this reconciliation. Any Sprint44 or other successor concern must begin with a separately bounded Product Owner entry gate; migration #16, new source/schema/runtime authority, Preview/Production activation, updater wiring, deployment, release, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


## Canonical post-Sprint42 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint41/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `6507927a258ab1378d6d3878e807b54cc9e6c5b2`; tree `69e165705483ebdca15e7ad66832d353beb390d8`; GitHub signature **verified / valid**.
- Sprint42 **First-Party Identity Disablement Session Termination Foundation** is **IMPLEMENTED / PUBLISHED** through PR #334 as `6507927a258ab1378d6d3878e807b54cc9e6c5b2`, from qualified exact source head `8fd104d817e2b473502f142198b24788e13afe41`.
- Sprint42 source remains exactly **8 paths** with sorted newline-terminated SHA-256 `6315890d318c3cdfca549bfacef6cb8d1ca66a4421416b49b4978095a98b6729`.
- Canonical source migrations remain exactly **#1–#15**. Sprint42 is **NO_SCHEMA_CHANGE**; migration #16 is not selected and does not exist.
- Sprint42 composes the existing Sprint41 authorized disable-only identity eligibility mutation with exact-tenant + exact-target active logical-session termination inside the canonical `PersistenceTransaction`. Successful fresh `applied`, fresh `no_change`, and exact replay outcomes re-enforce zero active target logical sessions before success without adding a public route or widening the Sprint41 payload.
- Sprint40 request-time identity authentication-eligibility revalidation remains an independent mandatory defense. Sprint36–Sprint39 session ownership, revocation, lifetime, tenant-membership, and organization/outlet/device revalidation semantics remain preserved.
- Sprint42 inserts no self-service session audit event, introduces no credential/factor/membership/grant mutation, and creates no enable/reactivation path.
- The bounded Sprint42 entry/schema/source-preservation and compatibility chain is published through PR #326–#333, #336, and #335. The final 19-workflow historical compatibility predecessor PR #335 is published as `fdbf30e2637dc71be16ac3f374f5973f104a3a9c`; PR #336 is published as `dea436904e56ed71353b56ab9b792762db2d95b7`.
- The final Sprint42 exact source head completed **29 materially triggered pull-request workflows / 29 success / 0 non-success**, and the exact-head `product-owner-merge-authority` status completed **success** before squash publication.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; Sprint41 migration #15 and Sprint42 source remain unactivated/unapplied in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **`NOT AUTHORIZED`**.
- No post-Sprint42 successor implementation concern is selected by this reconciliation. Any Sprint43 concern must begin with a separately bounded Product Owner entry gate; migration #16, new source/schema/runtime authority, reactivation, Preview/Production activation, updater wiring, deployment, and release are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


## Canonical post-Sprint41 source publication reconciliation — 2026-08-27

This current-facing section supersedes older post-Sprint40/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `1994a7821846db9f872edb62a984c4248f766c1e`; tree `1eb7a9294eed86c6e3333f0db25ef9e3793aaaf0`; GitHub signature **verified / valid**.
- Sprint41 **First-Party Identity Authentication Eligibility Administration Foundation** is **IMPLEMENTED / PUBLISHED** through PR #315 as `1994a7821846db9f872edb62a984c4248f766c1e`, from qualified source head `fadd0c5bba83e4a2e2e209e1750de2224b7f3b68`.
- Sprint41 source remains exactly **12 paths** with sorted newline-terminated SHA-256 `b2c5fc10a8baa2d56991d6dbd36b0407159d70953654ef322a9a11d23660489b`.
- Canonical source migrations are exactly **#1–#15**. Migration #15 creates only the tenant-scoped `oneqay_identity_authentication_eligibility_mutations` journal; migrations #1–#14 remain immutable.
- Sprint41 implements only server-authorized `first_party_authentication_enabled: true -> false` administration for eligible ordinary same-tenant identities. No enable/reactivation, bulk mutation, protected-control disablement, administrator session revocation, credential mutation, factor mutation, membership mutation, or grant mutation authority exists.
- Sprint40 remains the independent request-time consumer of current authentication eligibility. Sprint41 does not weaken Sprint36–Sprint40 session, lifetime, organizational-access, or eligibility revalidation controls.
- Bounded historical/source compatibility closure required for source publication is merged through PR #316–#323, and the post-Sprint41 canonical-document preservation predecessor is published through PR #325. These PRs changed preservation/governance behavior only where applicable and created no runtime, deployment, updater, Preview, or Production authority.
- Canonical main-push oracle **M7.5 Technical Preview Release Artifact #338** (run `33095155642`) completed **SUCCESS** on `1994a7821846db9f872edb62a984c4248f766c1e`.
- Main-push **Backend Updater Control Plane Regression #121**, **Privileged Update Security Regression #123**, and **Read-Only Update Deployment UI Regression #106** also completed **SUCCESS** on the same canonical commit; these are regression evidence only.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; Sprint41 source and migration #15 are **not activated/applied** in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **`NOT AUTHORIZED`**.
- No post-Sprint41 successor implementation concern is selected or authorized by this reconciliation. Any next concern must begin with a separately bounded Product Owner entry gate; no Sprint42, migration #16, source, schema, runtime, Preview, Production, updater, deployment, or release authority is implied.

Attribution: **Lab | zefry**


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


## Canonical Sprint40 pre-source task checkpoint — 2026-08-25

For current task-state and next-work interpretation, this section supersedes older current-facing task statements retained below as historical provenance.

- Sprint 21 through Sprint 39 governed identity/control foundations: **Done / COMPLETE / IMPLEMENTED / PUBLISHED** within bounded authority.
- Sprint40 concern selection and entry gate: **Done / PUBLISHED** through PR #268.
- Sprint40 schema/source-envelope gate: **Done / PUBLISHED** through PR #270.
- Sprint40 source-preservation predecessor and compatibility prerequisites: **Done / PUBLISHED** through PR #271, PR #272, and PR #273.
- Sprint40 documentation-synchronization preservation predecessor: **Done / PUBLISHED** through PR #274 as `7be563d66e2f8441cf28dd2ed9ce5d6c0704098f` / tree `adbbce29218e312b243076dc3ee984e68ce79b65`.
- Current bounded task: exact **13-document canonical documentation synchronization**, fingerprint `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.
- Canonical source migrations remain exactly **#1–#13**. Migration #14 is **selected for later Sprint40 source implementation but does not yet exist/apply on canonical `main`**.
- Future Sprint40 source implementation: **Blocked / Separately Governed** until this documentation synchronization is published and a fresh exact-main source authority is granted. Its frozen envelope is exactly 8 paths with fingerprint `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- Technical Preview Sprint40 activation, Production, updater, deployment, release, and migration #14 execution remain **Blocked / Not Authorized**.

This task checkpoint changes documentation only. It does not create source, workflow YAML, dependency, migration, schema, route/API, runtime, Preview, Production, updater, deployment, or release behavior.

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

## Canonical post-Sprint 28 task-state reconciliation — 2026-08-18

For current task-state and next-work interpretation, this section supersedes older current-facing M7.5/updater task statements retained below as historical task snapshots.

- Sprint 21 through Sprint 28 governed foundations are **Done / COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 28 source publication PR #188 and post-Sprint28 reconciliation PR #189 are published.
- Canonical migrations are exactly **#1–#8**; migrations #1–#7 remain immutable and migration #8 is additive/forward-only.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Persistence remains default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.

Current bounded task is this documentation-only post-Sprint28 program-state synchronization. It changes no application source, workflow YAML, migration, schema, dependency, route, runtime, Preview, Production, updater, or credential behavior.

After this documentation synchronization is published, the next logical task is **not implementation**. It is to establish a separately governed entry gate for **First-Control-Principal Bootstrap Credential Foundation**. That concern remains **Blocked / Not Authorized** until the gate defines authority, threat/security boundary, exact file envelope, regression requirements, and any schema decision. **Migration #9 is not authorized or assumed.**

Password change/reset/recovery/rotation/revocation, MFA/passkey/federation delivery, Production authentication/enrollment activation, updater activation, deployment, Release, and Production remain separately governed.

Detailed factual Sprint 28 state is recorded in `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Historical task rows below remain provenance and must not override this section for current task interpretation.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current task-state interpretation, this section supersedes older current-facing M7.5/P0-TP blocker lists and evaluator counts retained below as historical task snapshots.

- M7.5 Preview Runtime Qualification: **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**.
- Canonical evaluator after PR #129 and cleanup PR #130: **29 VERIFIED / 0 BLOCKED**; `lifecycle_authority_created=false`.
- Mandatory M7.5 blockers: **NONE**.
- `ENGINE:TENANT_ISOLATION`, `ENGINE:RESTORE_VERIFIED`, and `RUNTIME:BACKUP_RESTORE` are **VERIFIED** within the bounded non-Production Technical Preview evidence catalog.
- `P0-TP-002` current capability interpretation must use the completed 29/0 M7.5 evidence state; older blocker rows below are historical snapshots only and do not authorize Phase 0 Exit or Production.
- M7.6 and M7.7 remain **NOT AUTHORIZED**.
- Phase 0 remains **IN PROGRESS**; Phase 0 Exit remains **NOT APPROVED**.
- Sprint 14, Release, and Production remain **NOT AUTHORIZED**; production readiness remains **NO-GO**.

Current bounded task is this canonical lifecycle-closure publication. After closure publication, the preferred next engineering direction is separately gated Secure Web Updater architecture/release-control-plane design. No updater source, workflow mutation, deployment, cPanel/database action, M7.6, or later lifecycle authority is created here.

Historical task rows, governance recurrences, SHAs, and earlier evaluator snapshots below remain preserved as provenance.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current task-state interpretation, this section supersedes older current-facing M7.5/P0-TP blocker lists and evaluator counts retained below as historical task snapshots.

- M7.5: **IN PROGRESS / QUALIFICATION MATERIAL PROGRESS**.
- Canonical evaluator after PR #124: **26 VERIFIED / 3 BLOCKED**; overall **BLOCKED / INCOMPLETE**; `lifecycle_authority_created=false`.
- Only three blockers remain: `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`, `ENGINE:TENANT_ISOLATION:PARTIAL`, and `RUNTIME:BACKUP_RESTORE:PARTIAL`.
- `P0-TP-002` current capability assessment must be interpreted against this 26/3 evidence state rather than the older blocker list below.
- M7.6 and M7.7 remain **NOT AUTHORIZED**.
- Phase 0 remains **IN PROGRESS**; Phase 0 Exit remains **NOT APPROVED**.
- Sprint 14, Release, and Production remain **NOT AUTHORIZED**; production readiness remains **NO-GO**.

Preferred next bounded work after this documentation consolidation is a fresh read-only gap analysis of `ENGINE:TENANT_ISOLATION`, followed only later by separately authorized restore/backup-restore qualification design. Historical task rows, governance recurrences, SHAs, and earlier evaluator snapshots below are preserved as provenance and must not override this consolidation.

## Status legend

| Status | Meaning |
| --- | --- |
| Backlog | Belum diprioritaskan |
| Ready | Scope dan acceptance criteria siap |
| In Progress | Sedang dikerjakan |
| Blocked | Menunggu dependency/decision/authority |
| Review | Menunggu review/approval |
| Done | Evidence dan Definition of Done lengkap |

## Canonical naming rule

Nama produk canonical adalah **oneQay**. Current/future-facing task text harus menggunakan `oneQay`; immutable GitHub identifiers, repository path `labzefry/oneQay`, SHAs, historical commit messages, branch names, dan quoted historical evidence tidak ditulis ulang hanya untuk brand normalization.

## Handbook 1.0

| ID | Task | Status | Evidence / next action |
| --- | --- | --- | --- |
| GOV-001 | README project orientation | Done | `README.md` |
| GOV-002 | Project manifest | Done | `PROJECT_MANIFEST.md`; PR #1 |
| GOV-003 | AI constitution | Done | `AI_CONSTITUTION.md`; PR #1 |
| GOV-004 | Architecture baseline | Done | `ARCHITECTURE.md`; handbook baseline approval |
| GOV-005 | Product/engineering roadmap | Done | `ROADMAP.md`; handbook baseline approval |
| GOV-006 | Coding standards | Done | `CODING_STANDARDS.md`; handbook baseline approval |
| GOV-007 | Database handbook | Done | `DATABASE.md`; handbook baseline approval |
| GOV-008 | API governance | Done | `API_SPEC.md`; handbook baseline approval |
| GOV-009 | Security handbook | Done | `SECURITY.md`; handbook baseline approval |
| GOV-010 | Deployment handbook | Done | `DEPLOYMENT.md`; handbook baseline approval |
| GOV-011 | Testing strategy | Done | `TESTING.md`; handbook baseline approval |
| GOV-012 | UI/UX guideline | Done | `UI_GUIDELINE.md`; handbook baseline approval |
| GOV-013 | Installer specification | Done | `INSTALLER.md`; handbook baseline approval |
| GOV-014 | Updater specification | Done | `UPDATER.md`; handbook baseline approval |
| GOV-015 | Contribution workflow | Done | `CONTRIBUTING.md`; PR #1 |
| GOV-016 | Release management | Done | `RELEASE.md`; handbook baseline approval |
| GOV-017 | Task governance | Done | `TASKS.md`; PR #1 |
| GOV-018 | Changelog baseline | Done | `CHANGELOG.md`; PR #1 |
| GOV-019 | Markdown/link/security consistency validation | Done | 35 Markdown files linted; links and secret scan passed on PR #1 |
| GOV-020 | Publish handbook branch and draft PR | Done | PR #1 merged as `642437b` |
| GOV-021 | Product Owner handbook review | Done | Product Owner approved and merged PR #1 |
| GOV-022 | Phase 0 governance and discovery kickoff pack | Done | `docs/handbook/PHASE_0_KICKOFF.md`; PR #1 |
| GOV-023 | Standardize engineering collaboration to ChatGPT + GitHub only | Done | `AI_CONSTITUTION.md`; PR #1 |
| GOV-024 | Product vision and decision rights | Done | DEC-000 substantive Product Owner decision APPROVED on baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`; `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md`; `docs/handbook/DEC_000_DECISION_RECORD.md`; GD-003 Approved within DEC-000 boundary only |
| GOV-025 | Stakeholder and actor map | Review | Issue #4; PR #5 menunggu review dan persetujuan Product Owner |
| GOV-026 | Current process and user journeys | Review | Issue #6; draft document prepared for Product Owner review |
| GOV-027 | Domain event storming | Review | Issue #8 dan PR #9 merged; GD-007 tetap Proposed |
| GOV-028 | Correct approved Domain Event Storming review findings | Review | Issue #10 dan PR #11 merged tanpa approval substantif; audit lanjutan tercatat pada PR #11 |
| GOV-029 | Correct approved PR #11 Domain Event Storming audit findings | Review | Issue #12 dibuka kembali; empat koreksi pada head PR #13 `e4a3b7b` diratifikasi setelah merge; closure diblokir Issue #16/#18/#20 |
| GOV-030 | Reconcile PR #13 merge-before-approval | Review | Issue #14 dibuka kembali; recurrence berlanjut pada PR #15/#17/#19; completion diblokir Issue #16/#18/#20 |
| GOV-031 | Harden exact-head approval and issue closure controls | Review | Issue #16 dibuka kembali; protection control kembali dilanggar pada PR #17/#19; effectiveness diblokir Issue #18/#20 |
| GOV-032 | Reconcile PR #17 recurrence and enforce protection gate | Review | Issue #18 dibuka kembali; PR #19 merged tanpa required evidence/authority; completion diblokir Issue #20 |
| GOV-033 | Reconcile PR #19 recurrence and separate formal risk acceptance | Review | Issue #20; exact-head post-merge decision, protection evidence/risk acceptance, dan enforcement evidence masih pending |
| GOV-034 | Reconcile PR #25 recurrence and premature Issue #23 closure | Review | PR #25 head `ca2157096b310b114203d919cb8182e55a6fa5f9` merged as `93c8b8d4d8dae399c0d3f758c50460cf086e2322` without available separate exact-head lifecycle authority or published checks; Issue #23 closure is not completion evidence |
| GOV-035 | Reconcile PR #26 post-merge recurrence | Review | PR #26 head `63223b9b856bd67e739651a1e23cc071971998c3` merged as `294fe24381e88b61701868567cda4be532640ab0`; Product Owner approved content accuracy only, while lifecycle authority, protection disposition, independent review, and Issue #23 state alignment remain pending |
| GOV-036 | Reconcile PR #27 post-merge recurrence | Review | PR #27 head `c6adb55a9a6cd2ebedd78668ccaf5fd64c041d94` merged as `3c4bcfe9797a3ae7f4deb124568ef361d74125e5`; Product Owner approved content accuracy only, while lifecycle authority, repository-control disposition, protection evidence/risk acceptance, independent review, Issue #23 state alignment, and effectiveness evidence remain pending |
| GOV-037 | Reconcile PR #28 post-merge recurrence | Review | PR #28 head `0597d784f63cf6d5967cedae17ca8d0b5a2e4dc9` merged as `1009af84ec0ee7d7731890e379dde25279280c3a`; Product Owner approved content accuracy only, while lifecycle authority, repository-control disposition, protection evidence/risk acceptance, independent review, Issue #23 state alignment, and effectiveness evidence remain pending |
| GOV-038 | Reconcile PR #29 post-merge recurrence | Review | PR #29 head `54a5773c3ab65a33e35ef2646089727490a0ff8d` merged as `f55d86f1a3d89a6bcbbbcf7800851b9c61f8c047`; repository-native operational authority explicitly excluded ready/merge, while lifecycle authority, repository-control disposition, direct protection evidence or formal scoped risk acceptance, independent review, Issue #23 state alignment, root-cause analysis, and effectiveness evidence remain pending |
| GOV-039 | Reconcile PR #30 post-merge recurrence | Review | PR #30 head `f3703650f98e5d6abfdb21d9b67ac7c5567ea9f6` merged as `54bc51a7a150394748dcc5f6a2fb8e376206feba`; repository-native operational authority explicitly excluded ready/merge/auto-merge, while lifecycle authority, repository-control disposition, direct protection evidence or formal scoped risk acceptance, independent review, Issue #23 state alignment, root-cause analysis, and effectiveness evidence remain pending |
| GOV-040 | Reconcile PR #31 post-merge recurrence | Review | PR #31 head `10b5179b16c104e1877153b066e96a937ece9c9b` merged as `67059e563de26cee26cefd64cf9e7d5c4436ffc6`; repository-native operational authority explicitly excluded ready/merge/auto-merge/approval review, while repository-control disposition, direct protection evidence or formal scoped risk acceptance, independent exact-head review, actor or bypass identification, root-cause analysis, corrective/preventive action, effectiveness evidence, and Issue #23 state alignment remain pending |
| GOV-041 | Reconcile PR #32 post-merge recurrence | Review | PR #32 head `beb7b35aa718a746ad5dad9d5574c2293bd0ab40` merged as `d1a6160b37250bda691e906fc4ee06e37dd0c847`; repository-native operational authority explicitly excluded ready/merge/auto-merge/approval review and branch-protection/ruleset changes, while repository-control disposition, direct protection evidence or formal scoped risk acceptance, independent exact-head review, actor or bypass identification, root-cause analysis, corrective/preventive action, effectiveness evidence, and Issue #23 state alignment remain pending |
| GOV-042 | Reconcile PR #33 post-merge recurrence | Review | PR #33 head `28c776abf6ab7832dbdf61ea49203c6e9c13a55c` merged as `68df196efdf38919d73a6b6345b973d2c3698b29` without retrospective lifecycle authority; repository-control investigation completed, `main-protected-governance` containment and sentinel PR #34 effectiveness evidence are available, while GOV-042 remains Review and Issue #23 state alignment remains pending |
| GOV-043 | Restore stable required-check producers | Done | PR #38 published as `a59521ad31d8153198bb80dd7985142cb21e3775`; stable `governance-validation`, `markdown-lint`, and `secret-scan` contexts restored before M5 |

Historical GOV-029 through GOV-042 items remain Review where their historical lifecycle discrepancies have not been substantively closed. Current prospective enforcement improvements must not rewrite those historical records.

## M5 — Engineering State, CI & Governance Stabilization

| ID | Task | Status | Evidence / next action |
| --- | --- | --- | --- |
| GOV-044 | M5.1 — Canonical State Reconciliation | Done | PR #66 published as `153a33a4a2b5edb4a31285eca7d3491f9589b778`; canonical mutable AI checkpoints live under `docs/ai/`; root duplicates are pointer stubs |
| GOV-045 | M5.2 — CI & Lifecycle Control Hardening | Done | PR #67 published as `512344d0497787c729242cb1fd2d7d02ecfc40c2`; A-03 and A-05 resolved; five required contexts active on protected `main` |
| GOV-046 | M5.3 — Governance & Program State Synchronization | Done | PR #68 source head `aa799e657070a7d3283110a73a411f54a73b972c` published as `e45f5b4c0f143abc6e255e4e8550bf3504348aae`; source/published tree `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`; A-06/A-07/A-08 reconciled |

## M6 — Enterprise Vision Canonicalization

| ID | Task | Status | Evidence / next action |
| --- | --- | --- | --- |
| GOV-047 | M6 Enterprise Vision canonicalization publication | Done | PR #69 source head `e6a3345b09a6b270ac7e09abd78c6356f426e363` published as `0b7b28028966ac38af0f32960054210c3a083916`; source/published tree `567df997bae70090b19465c75e4cc3b1e23b6579`; publication itself did not grant substantive approval; GOV-051 later approved the Enterprise Vision separately |
| GOV-048 | Normalize canonical product name to `oneQay` | Done | Current/future-facing canonical product identity normalized through PR #69; immutable identifiers and historical evidence preserved |
| GOV-049 | Synchronize Enterprise Capability Map and conceptual evolution representation | Done | Published through PR #69: Core Business Platform, Platform Capabilities, Extensibility, AI Platform, Channels; evolution E0–E5; no implementation authority implied |
| GOV-050 | Reconcile A-09 Enterprise Vision anomaly at representation/publication level | Done | PR #69 publication verified; A-09 resolved at canonical representation/publication level; GOV-051 later completed the separate substantive decision |
| GOV-051 | Enterprise Vision substantive Product Owner decision | Done | Product Owner explicitly APPROVED `Enterprise Intelligent Business Management Platform` on verified baseline `762149757e4bc1fa79cc16bc4761f4147be0f7ea` and canonical artifact blob `bb1cace72a6fdb359e15e22467443d9f3916c336`; approval is product direction only and grants no implementation authority |

M6 publication itself did not grant substantive Enterprise Vision approval. GOV-051 separately approved the Enterprise Vision as binding long-term product direction, but does not authorize Sprint 14, final/business/production application implementation, new business source code, database/schema implementation, SQL/migration execution, production database modification, deployment, release, ADR/GD promotion, JRN resolution, or production-readiness transition.

## Decisions required before final/business application implementation

Published bounded Platform Foundation source through Sprint 13 is an existing repository fact. The decisions below remain gates for broader final/business/production application implementation and must not be read as retroactively invalidating published Sprint 12 or Sprint 13 foundation work.

| ID | Decision | Status | Required output |
| --- | --- | --- | --- |
| DEC-000 | Product Owner, delegates, and decision rights | Done | Product Owner substantive decision APPROVED on baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`; Approved `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md`; `docs/handbook/DEC_000_DECISION_RECORD.md`; GD-003 Approved only within DEC-000 boundary |
| DEC-001 | MVP scope and non-scope | Done | Product Owner substantive decision APPROVED on baseline `17f156b9861972b4924a5ed01bfabd5a1a79461a`; `docs/handbook/DEC_001_DECISION_RECORD.md`; implementation authority NOT GRANTED |
| DEC-002 | Backend language/framework | Done | Product Owner substantive decision APPROVED on baseline `504b10be44d45dfcfec9b6cfed4f72ed5748b564`; PHP + Laravel; `docs/handbook/DEC_002_DECISION_RECORD.md`; ADR-001 Accepted after reconciliation; implementation/dependency authority NOT GRANTED |
| DEC-003 | Frontend/PWA stack | Done | Product Owner substantive decision APPROVED on baseline `dcb7e3f8de890530a00a0dd4fd310bc10762c72f`; Vue 3 + Inertia + Vite with TypeScript-first, explicit API/mobile boundaries, bounded PWA direction; `docs/handbook/DEC_003_DECISION_RECORD.md`; ADR-002 Accepted after reconciliation; implementation/dependency authority NOT GRANTED |
| DEC-004 | Android approach | Done | Product Owner substantive DEC-004 decision APPROVED on baseline `97b2e5066118af2b3e9467afc71e84dce228eb38`; Hybrid Staged Approach; Native Android with Kotlin + Jetpack Compose; `docs/handbook/DEC_004_DECISION_RECORD.md`; `docs/adr/ADR-008-android-delivery-approach.md`; implementation/dependency authority NOT GRANTED |
| DEC-005 | Database engine and physical tenancy model | Done | **APPROVED HISTORICAL DECISION / PARTIALLY SUPERSEDED BY DEC-005R**; original Product Owner decision on baseline `63646e1cccc611a1911c452397059983030dfe66` selected MySQL Server and shared database/shared schema; historical provenance remains preserved; current engine/portability precedence is DEC-005R; no database/schema/SQL/migration/implementation authority |
| DEC-005R | Portable Relational Persistence Architecture | Done | Product Owner substantive decision **APPROVED — OPTION C**; published through PR #100 from source head `8ec7069b08c9127e402fa80e5e79ca26be2b63d6`, source/published tree `0862c851d30c11c37c39d13aa5660d042da91989`, squash commit `b5cbdeb6ea45d4f159f3d1cd39cadc561605c5ff`; database-neutral Domain/Application, qualified MariaDB/MySQL/PostgreSQL profile direction, Database Portability Contract and DBME/cross-engine qualification directions; no source/schema/SQL/migration/DBME/M7.5/deployment authority |
| DEC-006 | Authentication/MFA/session architecture | Done | Product Owner substantive DEC-006 APPROVED on baseline `c495f1b1f6e4e624bf3669ee94a786a1d7e865ce`; first-party oneQay identity; Web/PWA server-side session; explicit Android/API token boundary; TOTP privileged MFA baseline; WebAuthn/passkey evolution; global identity + tenant memberships; reconciled ADR-004; no implementation/package/schema/migration authority |
| DEC-007 | Payment provider and compliance boundary | Done | Product Owner substantive DEC-007 APPROVED on baseline `50955d101c455c6af7356197d9e06d6d76e753bb`; cash-first + configurable manual/external recorded tenders; operator-recorded versus provider-verified evidence separation; provider-abstracted future electronic architecture; provider selection deferred; sale-level payment sufficiency; idempotency/refund/settlement/PCI/jurisdiction boundaries; `docs/handbook/DEC_007_DECISION_RECORD.md`; materially reconciled ADR-005; no payment/provider/schema/SQL/implementation authority |
| DEC-008 | Offline POS semantics and conflict resolution | Done | Product Owner substantive DEC-008 APPROVED on baseline `21f1b2e938d9f6a42b1dd2ff717c4e049b5119d7`; Staged / Hybrid Offline Architecture; first-MVP online-authoritative transactions; future provisional server-validated offline operations; bounded replay/idempotency/conflict/security/reconciliation semantics; `docs/handbook/DEC_008_DECISION_RECORD.md`; materially reconciled ADR-006; no offline/source/schema/package/implementation authority |
| DEC-009 | Deployment stage 1 runtime requirements | Done | Product Owner substantive DEC-009 APPROVED on baseline `0fdc0a53403f16fbc6908630ea350af2c0de466b`; Capability-Based Staged / Hybrid Portability Model; P1 cPanel conditional/not selected; P2 managed/hardened VPS/server fallback class; current database dependency requires an authorized and runtime-qualified relational engine profile under DEC-005R; Stage-1 Preview environment; `docs/handbook/DEC_009_DECISION_RECORD.md`; materially reconciled ADR-007; no deployment/implementation authority |
| DEC-010 | Product license and third-party notice policy | Done | Product Owner substantive DEC-010 APPROVED on baseline `5cc572675dd7871a3ca841cedf06fbc8ea74f839`; Proprietary / All Rights Reserved product policy; repository visibility/rights separation; external contributions legally gated; dependency-license pre-adoption matrix; NOTICE/SBOM/trademark/plugin/AI/asset boundaries; `docs/handbook/DEC_010_DECISION_RECORD.md`; final legal text remains Legal Review Required; no dependency adoption/implementation/distribution/deployment authority |
| DEC-011 | Data retention, privacy, and jurisdiction | Done | Product Owner substantive DEC-011 APPROVED on baseline `6c6af7f99d25f177c91f92cdd163a277affc5153`; Bounded Privacy-by-Design + Hybrid Bounded Retention + Jurisdiction-Profile Architecture; initial jurisdiction NOT YET CANONICALLY SELECTED; qualified legal review required for jurisdiction-specific implementation; `docs/handbook/DEC_011_DECISION_RECORD.md`; no implementation/schema/provider/jurisdiction/deployment authority |
| DEC-012 | RPO/RTO and support objectives | Done | Product Owner substantive DEC-012 APPROVED on baseline `a7821517a03cf868adf56bfa7d91c878d8c364ac`; Capability-Tiered / Evidence-Based Recovery & Support Policy; final numerical Production RPO/RTO and customer SLA deferred; recovery claims evidence-gated; `docs/handbook/DEC_012_DECISION_RECORD.md`; no backup/DR/infrastructure/implementation/deployment/release/Production authority |

DEC-000 through DEC-012 and DEC-005R completion do not authorize final/business/production implementation. DEC-005R changes current relational architecture governance only; DEC-012 approves only bounded recovery/support policy. Neither decision promotes REC-1/SLO-1 Technical Preview values into Production commitments, establishes numerical Production RPO/RTO or customer SLA, selects provider/cloud/region/HA technology, resolves GD-007/JRN-003/JRN-013, exits Phase 0, starts Sprint 14, or grants implementation, deployment, release, or Production authority.

## Phase 0 discovery backlog

| ID | Task | Priority | Dependency |
| --- | --- | --- | --- |
| DSC-000 | Product vision and decision-rights workshop | P0 | Handbook approval; Issue #2 |
| DSC-001 | Stakeholder and actor map | P0 | Handbook approval; Issue #4 |
| DSC-002 | POS/ERP domain event storming | P0 | Stakeholder availability; Issue #8; correction Issue #10/#12; governance Issue #14/#16/#18/#20 |
| DSC-003 | Current process, user journeys, and service blueprint | P0 | DSC-001; Issue #6 |
| DSC-004 | Data inventory and classification | P0 | DSC-002 |
| DSC-005 | Threat model critical flows | P0 | DSC-002/004 |
| DSC-006 | MVP success metrics and SLO proposal | P0 | DEC-001 |
| DSC-007 | Shared-hosting capability assessment | P0 | Hosting facts |
| DSC-008 | Vendor and dependency evaluation rubric | P1 | Security/licensing policy; DEC-010 |

## Phase 1 candidate backlog

Items ini tidak memperoleh source-code authority baru dari M6, GOV-051, DEC-000, DEC-001, DEC-002, DEC-003, DEC-004, DEC-005, DEC-005R, DEC-006, DEC-007, DEC-008, DEC-009, DEC-010, DEC-011, atau DEC-012. Published bounded Platform Foundation through Sprint 13 must be preserved, tetapi pekerjaan baru untuk final/business application atau Sprint 14 tetap membutuhkan Product Owner authority dan gate yang berlaku.

- PLT-001 repository/application skeleton;
- PLT-002 tenant context and isolation enforcement;
- PLT-003 identity/MFA/authorization;
- PLT-004 organization/outlet/device;
- PLT-005 audit/correlation/error tracking;
- PLT-006 migration/seeder foundation;
- PLT-007 configuration and secret boundary;
- PLT-008 installer baseline;
- PLT-009 CI quality/security gates;
- PLT-010 backup/restore rehearsal.

## Task maintenance rules

- Setiap task memiliki owner sebelum In Progress.
- Blocked task mencantumkan blocker dan next action.
- Done membutuhkan evidence, bukan hanya implementasi.
- Scope baru tidak disisipkan diam-diam; buat task/issue baru.
- Perubahan status capability/decision memperbarui PROJECT_MANIFEST dan CHANGELOG.

## Phase 0 Accelerated Technical Preview

| ID | Task | Status | Dependency/evidence |
| --- | --- | --- | --- |
| P0-TP-001 | Record B1/F1/D1/A1 and PAY-1/OFF-1/TEN-1/REC-1/SLO-1/DATA-1 | Review | Issue #23; backend B1 later approved through DEC-002/ADR-001; frontend F1 later approved through DEC-003/ADR-002; database D1 provenance was later reconciled through DEC-005/ADR-003 and its current relational-engine precedence is now governed by DEC-005R while preserving that history; authentication A1 provenance later reconciled through DEC-006/ADR-004; PAY-1 provenance later superseded as current bounded payment direction by substantive DEC-007/reconciled ADR-005; OFF-1 provenance later reconciled through substantive DEC-008/reconciled ADR-006; remaining exact-head approvals pending |
| P0-TP-002 | Complete P1 shared-hosting capability assessment | Blocked | Actual P1 cPanel Preview now has verified PHP/web runtime, safe document root, rewrite, filesystem separation, HTTPS, Preview isolation, MariaDB application connectivity, least privilege, rollback transaction semantics, and a deny-by-capability migration boundary; remaining blockers include DB connection-limit visibility, full durable tenant isolation, verified restore, DEC-005R portability, background/queue/scheduler execution, rollback/recovery rehearsal, and remaining environment/observability/resource/security/outbound controls |
| P0-TP-003 | Review ADR-001 through ADR-007 | Review | ADR-001 Accepted via DEC-002; ADR-002 Accepted via DEC-003; ADR-003 Accepted historically via DEC-005 and current representation reconciled to DEC-005R while preserving D1/DEC-005 provenance; ADR-004 Accepted via DEC-006; ADR-005 Accepted as DEC-007 representation after governed publication; ADR-006 Accepted as DEC-008 representation after governed publication; ADR-007 Accepted as DEC-009 representation with its current database dependency reconciled to DEC-005R |
| P0-TP-004 | Review data inventory/classification baseline | Review | Product Owner and security exact-head review |
| P0-TP-005 | Review Technical Preview threat model | Review | Critical/High threats require mapped verification |
| P0-TP-006 | Review REC-1 recovery plan | Review | Target-environment capability and rehearsal pending |
| P0-TP-007 | Approve Phase 0 preview exit | Blocked | P0-TP-002 through P0-TP-006 and explicit exact-head decision; remains separately gated and is not a prerequisite for separately authorized bounded Local/Test/CI source preparation |
| P0-TP-008 | Authorize application skeleton | Done | M7.1 source authority was separately granted and the governed Application Skeleton & Configuration Boundary was published through PR #92; this historical task completion does not grant further source authority |
| P0-TP-009 | Execute T+5 Technical Preview | Blocked | Bounded P1 cPanel Technical Preview runtime activation is now evidenced, but complete M7.5 qualification plus applicable security, recovery, deployment-rehearsal, and acceptance gates remain incomplete; M7.6/M7.7 and Production remain separately gated |

PR #24 through PR #33 technical merges and Issue #23 closure do not themselves set any task above to Done, accept an ADR, approve Phase 0 exit, grant general application source-code authority, ratify prior lifecycle actions, complete GOV-034 through GOV-042, or provide substantive approval or completion evidence. Later independent Product Owner decisions separately Accept ADR-001 via DEC-002, ADR-002 via DEC-003, ADR-003 historically via DEC-005 with current representation reconciled to DEC-005R, ADR-004 via DEC-006, ADR-005 via DEC-007, ADR-006 via DEC-008, and ADR-007 via DEC-009 with its database dependency later reconciled to DEC-005R. DEC-012 recovery/support policy does not promote REC-1 or SLO-1 Technical Preview values or create successful rehearsal evidence. Phase 0 remains In Progress; final/business/production application implementation remains Blocked; Phase 0 preview exit remains Not Approved; bounded Local/Test/CI source preparation may occur only under separate Product Owner source authority; P1 remains conditional and Not Selected; P2 actual target remains pending external input unless fresh evidence proves otherwise; GD-007 remains Proposed; JRN-003 and JRN-013 remain unresolved; TEN-1, REC-1, SLO-1, and DATA-1 remain Proposed.

Issue #23 remains an open source of historical pre-M7.0 planning language. That historical wording must not override the later governed Phase 0 Controlled Implementation Bridge for bounded Local/Test/CI source preparation. Issue #23 mutation is outside this reconciliation authority.

## M7 — Technical Preview Implementation Enablement

M7 is the current bounded Technical Preview engineering workstream created by the Product Owner-approved Phase 0 Controlled Implementation Bridge. These labels sequence work; they do not independently grant source, deployment, release, or Production authority and do not authorize Sprint 14.

| ID | Task | Status | Dependency/evidence |
| --- | --- | --- | --- |
| M7.0 | Controlled Implementation Bridge | Done | Product Owner substantive bridge decision and governed publication completed; historical bridge authority does not grant standing future source authority |
| M7.1 | Application Skeleton & Configuration Boundary | Done | Governed publication completed through PR #92; no standing successor authority |
| M7.2 | Tenant Kernel & Isolation Foundation | Done | Governed publication completed through PR #93; M7.1 foundation preserved |
| M7.3 | Identity / Organization / Outlet / Device Minimum | Done | Governed publication completed through PR #94; M7.2 tenant isolation and server-controlled identity/organizational boundaries preserved |
| M7.4 | POS Core Synthetic Vertical Slice | Done | Governed publication completed through PR #96; bounded synthetic Local/Test/CI POS transaction evidence published; no standing M7.5, deployment, release, or Production authority |
| M7.4A | Technical Preview Interaction Layer | Done | Governed publication completed through PR #98; source head `893b73b8f20b2ede0d3a8896b3a015df5370dbed`, source/published tree `cdc140e5061481bec4b6b691b02b2b234181c2fb`, published commit `c0bdf8ad7539a5c83de2e5183fbf2eda9f17f02b`; synthetic-only interaction journey reuses M7.4 `CompleteSyntheticSale`; no standing M7.5, deployment, release, or Production authority |
| M7.5 | Preview Runtime Qualification | Blocked | M7.5 execution has materially progressed under bounded Product Owner authority: live P1 web-runtime evidence is verified and PR #111 published the bounded MariaDB relational probe; active exact-main release `m75-preview-0edea8cdcc0c` reports relational probe `qualified`, `persistent_schema_created=false`, and `production_ready=false`; deterministic 29-control reconciliation currently has 13 VERIFIED / 16 BLOCKED, so M7.5 overall remains incomplete and non-Production |
| M7.6 | Preview Deployment / Recovery Rehearsal | Blocked | NOT AUTHORIZED; qualified target, applicable source/security evidence, and separate deployment authority required |
| M7.7 | Technical Preview Acceptance | Blocked | NOT AUTHORIZED; combined source, security, runtime, recovery, and operational evidence required |

Track A Local/Test/CI engineering has published M7.4 and M7.4A. Track B now has actual P1 cPanel Technical Preview evidence: the web-runtime subset and bounded MariaDB relational probe are materially verified, while the complete fail-closed M7.5 evaluator remains BLOCKED on 16 runtime/engine controls recorded in `docs/evidence/runtime/p1-cpanel-live-relational-20260815.report.json`. M7.6/M7.7, Phase 0 Exit, Release, and Production remain separately gated.

## DEC-010 Supplement publication state

- Status: **Done** as the intended successfully published state for the approved substantive supplement.
- Decision record: `docs/handbook/DEC_010_SUPPLEMENT_DECISION_RECORD.md`.
- D10S-01: **ZERO MANDATORY COMMERCIAL SOFTWARE-LICENSE COST — CORE BASELINE**.
- D10S-02: **FREE / OPEN-SOURCE FIRST PREFERENCE — NOT FOSS-ONLY**.
- D10S-03: **APACHE ECHARTS — DEFAULT WEB/PWA VISUALIZATION TECHNOLOGY CANDIDATE / APPROVED TECHNOLOGY DIRECTION**.
- D10S-04 preserves **Technology Policy Approval != Dependency Adoption Authority != Implementation Authority**.
- No package/version, package manager, frontend lockfile, ECharts/Vue wrapper, source implementation, deployment, release, Production, Phase 0 exit, or Sprint 14 authority is created.

# oneQay Database Handbook

## Canonical post-Sprint44 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint43/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `0c74a28b470238250439d7dde10518529a39b90e`; tree `4ee89603c5793f38d6ce89d7056bc33ff159e8eb`; GitHub signature **verified / valid**.
- Sprint44 **First-Party Identity Reactivation Fresh Authentication Re-entry Foundation** is **IMPLEMENTED / PUBLISHED** through PR #365 as `0c74a28b470238250439d7dde10518529a39b90e`, from qualified exact source head `e6729db82e554a433507a1c176166dfeb184bf76`.
- Sprint44 source remains exactly **4 paths** with sorted newline-terminated SHA-256 `ff01f1355de6c7fdfd28c2d359eb70787dd8448f0b1fc6d9cb73c1a0fb76580a`.
- Canonical source migrations remain exactly **#1–#15**. Sprint44 is **NO_SCHEMA_CHANGE**; migration #16 is **NOT SELECTED** and does not exist.
- Sprint44 enforces current first-party identity authentication eligibility in the canonical fresh-login path after credential verification and before organizational context entry, MFA pending state, credential-epoch capture, logical authority issuance, or framework-session establishment. Disabled or otherwise ineligible identity evidence fails closed with the existing safe `AUTHENTICATION_FAILED` envelope.
- Sprint43 reactivation remains eligibility-only. Reactivation creates no framework session, logical session, session authority, public session handle, or restored authority. A reactivated identity must complete canonical fresh authentication with current credentials and current organizational validity before any new authority can be issued.
- Fresh authentication after valid reactivation issues new logical authority and a new public session handle. Historical revoked, expired, idle-expired, epoch-invalid, membership-invalid, organization/outlet/device-invalid, or otherwise terminated authority remains invalid and is never resurrected or reused.
- Sprint44 does not clear historical `revoked_at_unix`, does not restore old authority/session identifiers, does not add `login_after_reactivate`, restore, resume, automatic-login, self-service reactivation-login, protected-control bypass, break-glass, or caller-selected tenant/role/permission/session authority.
- Cross-tenant credential borrowing and invalid organizational re-entry remain denied. Later Sprint41 disablement plus Sprint42 termination semantics revoke the newly issued target authority while preserving unrelated active sessions and historical revocation evidence.
- The final Sprint44 dedicated regression proves the exact source envelope, NO_SCHEMA_CHANGE and migration #16 lock, canonical composition/route non-widening, fail-closed ordering, PHP syntax, fresh-authentication re-entry semantics, Sprint41–Sprint43 preservation, full application regression, lifecycle locks, and tracked-source cleanliness.
- The final Sprint44 exact source head completed **21 materially triggered pull-request workflows / 21 success / 0 non-success**, and the exact-head `product-owner-merge-authority` status completed **success** before squash publication.
- The bounded Sprint44 publication chain includes entry/schema gates PR #353 and #356, bounded compatibility publications including PR #352, #355, #357, #359, #363, and #364, and final source PR #365. Closed unmerged attempts such as PR #354 and #358 do not constitute canonical authority.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; Sprint41 migration #15 and Sprint42/Sprint43/Sprint44 source remain unactivated/unapplied in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment, release, migration execution, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint44 successor implementation concern is selected by this reconciliation. Any Sprint45 or other successor concern must begin with a separately bounded Product Owner entry gate; migration #16, new source/schema/runtime authority, Preview/Production activation, updater wiring, deployment, release, migration execution, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


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
- Bounded historical/source compatibility closure required for source publication is merged through PR #316–#323, and the post-Sprint41 canonical-document preservation predecessor is published through PR #325. These PRs changed preservation/governance/preservation behavior only where applicable and created no runtime, deployment, updater, Preview, or Production authority.
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


## Canonical Sprint40 pre-source database state — 2026-08-25

For current persistence, schema, identity-eligibility, runtime, and next-work interpretation, this section supersedes older current-facing wording retained below as historical provenance.

- Canonical source migrations remain exactly **#1 through #13** at this documentation-synchronization stage. Migrations #1-#13 remain immutable.
- Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** has selected a minimal forward-only migration #14 through the published schema/source-envelope gate PR #270, but migration #14 is **NOT YET CREATED / NOT APPLIED / NOT PUBLISHED AS SOURCE**.
- The selected migration path is `apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`.
- The only selected schema mutation is a non-null boolean `first_party_authentication_enabled` with default `true` on `oneqay_identities`. No index, timestamp, reason, actor, lifecycle journal, trigger, auxiliary table, credential epoch mutation, factor epoch mutation, or organizational-access schema mutation is selected.
- Identity authentication eligibility remains a server-owned concern distinct from credential existence, credential epoch, TOTP factor state/epoch, tenant membership, and organization/outlet/device relationships.
- The future Sprint40 source envelope is exactly eight paths with sorted newline-terminated SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`. Its preservation lineage is published; source implementation itself remains **NOT YET IMPLEMENTED / NOT YET PUBLISHED**.
- This canonical documentation synchronization changes exactly 13 documentation paths with sorted newline-terminated SHA-256 `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`; it creates no database object and executes no migration.
- Technical Preview receives no Sprint40 schema activation from this stage. Production remains **NO-GO / NOT AUTHORIZED**. Updater remains **DISABLED / UNWIRED**. Deployment, release, SQL execution, migration execution, and Production database mutation remain **NOT AUTHORIZED**.

Historical database sections below remain preserved as provenance and must not override this section for current-state interpretation.

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

## Canonical post-Sprint 28 database-state reconciliation — 2026-08-18

For current database/schema interpretation, this section supersedes older current-facing M7.5 database-state wording retained below as historical qualification provenance.

Canonical source now contains exactly migrations **#1 through #8**. Migrations #1–#7 remain immutable; migration #8, `0000_00_00_000008_create_initial_password_enrollments.php`, is the only Sprint 28 schema addition and is additive/forward-only.

Current published credential/control schema progression includes:

1. foundational context graph;
2. organizational access grants;
3. scoped role/permission policy;
4. policy mutation journal;
5. initial tenant-administrator provisioning journal;
6. protected-control administrator mutation journal;
7. identity password credentials; and
8. initial password enrollments.

Migration #7 stores exact tenant-scoped password credential ownership `(tenant_id, identity_id)` using one-way hashes. Migration #8 stores secret-minimal enrollment lifecycle evidence and persists only the enrollment token digest, never plaintext enrollment tokens or plaintext passwords.

Sprint 28 does not authorize credential update/upsert/delete, password reset/change/recovery/rotation/revocation, Production schema execution, or Technical Preview schema application. Technical Preview remains **`NO_SCHEMA_CHANGE`**, Production remains **`NO-GO / NOT AUTHORIZED`**, updater remains **`DISABLED / UNWIRED`**, and `ONEQAY_PERSISTENCE_ENABLED=false` remains the repository default.

The next logical identity concern, First-Control-Principal Bootstrap Credential Foundation, is separately governed and does not gain schema or migration authority from this documentation reconciliation.

The detailed canonical publication record is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Earlier M7.5 and pre-schema current-state statements below remain historical provenance.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current database/runtime interpretation, this section supersedes the older current-facing M7.5 consolidation retained below as historical architecture/checkpoint text.

The bounded non-Production Technical Preview database qualification is now **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**. The mandatory evaluator is **29 VERIFIED / 0 BLOCKED** after PR #129, and PR #130 records secure retirement of the disposable restore-rehearsal database without changing that evaluator.

Current bounded evidence therefore includes verified application connectivity, least privilege, transaction semantics, migration boundary, connection/resource visibility, Database Portability Contract conformance, database-backed tenant isolation, and successful isolated backup/restore rehearsal. Specifically:

- `ENGINE:TENANT_ISOLATION = VERIFIED`;
- `ENGINE:RESTORE_VERIFIED = VERIFIED`;
- `RUNTIME:BACKUP_RESTORE = VERIFIED`.

These Technical Preview facts do **not** establish a permanent Production business schema, Production disaster-recovery SLA, tenant-selective Production restore capability, general Production readiness, or permission to execute new database/schema/migration work. Exact numerical Production RPO/RTO remain outside this evidence claim.

`lifecycle_authority_created=false` remains true for the M7.5 evidence package. M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, and Production remain separately gated and **NOT AUTHORIZED**; production readiness remains **NO-GO**.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current database/runtime interpretation, this section supersedes older M7.5 qualification wording retained below as historical architecture/checkpoint text.

The bounded non-Production P1/cPanel MariaDB qualification has materially progressed: application connectivity, least privilege, transaction semantics, migration boundary, connection-limit visibility, backup export, and Database Portability Contract controls are now governed `VERIFIED` evidence. The complete M7.5 evaluator is **26 VERIFIED / 3 BLOCKED**, outcome **BLOCKED / INCOMPLETE**, with `lifecycle_authority_created=false`.

The three remaining blockers are:

- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`.

This does **not** establish durable Production business persistence, a permanent business schema, full durable two-tenant database-backed isolation, successful restore, Production recoverability, or Production readiness. Existing backup/export evidence must not be interpreted as verified restore. M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, and Production remain not authorized.

Historical DEC-005/DEC-005R provenance and prior qualification snapshots below remain preserved.

## Goals

Database harus menjaga integritas transaksi, isolasi tenant, auditability, compatibility migration, backup/restore, portability, dan performance predictable. Current canonical direction melalui substantive **DEC-005R — Portable Relational Persistence Architecture** adalah engine-neutral Domain/Application dengan qualified relational engine profiles dan target **ZERO BUSINESS-CODE CHANGE** antar profile yang resmi dikualifikasi.

Historical DEC-005 tetap Approved historical decision dan partially superseded oleh DEC-005R. Shared database/shared schema, immutable tenant isolation key, Application-authoritative tenant authorization, Infrastructure-owned vendor behavior, schema-evolution, dan recoverability principles tetap dipertahankan.

## Canonical relational persistence and physical tenancy direction

Substantive DEC-005R menetapkan:

- Domain dan Application: **database-engine-neutral**;
- business rules: tidak boleh bergantung pada relational-engine vendor identity;
- portability target: **ZERO BUSINESS-CODE CHANGE** antar officially qualified relational engine profiles;
- engine-specific behavior dan physical mapping: **Infrastructure concern**;
- canonical logical schema/contract: engine-neutral;
- relational engine-profile directions: **MariaDB, MySQL, PostgreSQL**;
- MariaDB 11.4 family: Stage-1 profile direction, **subject to runtime qualification**;
- formal **Database Portability Contract** direction;
- cross-engine qualification/CI direction;
- oneQay **Database Mobility & Migration Engine — DBME** direction;
- automatic physical adaptation hanya bila semantic equivalence terbukti;
- unsafe, lossy, atau ambiguous conversion: **fail closed**;
- default physical tenancy: **shared database + shared schema + mandatory immutable tenant isolation key**;
- future stronger physical isolation: bounded hybrid evolution path hanya melalui separate authority dan material evidence;
- tenant authorization: **Application-authoritative** dengan database integrity/security sebagai defense-in-depth;
- migration/schema evolution: versioned, deterministic, compatible, recoverable, dan reconcilable;
- recoverability: backup success bukan bukti recoverability tanpa successful restore evidence.

DEC-005R tidak menetapkan actual physical schema, SQL, DDL, executable migration, database credentials, live database connection, provider, replication topology, DBME implementation, cross-engine CI implementation, atau Production implementation.

## Engine-profile qualification

Engine/profile dianggap qualified hanya berdasarkan evidence, bukan berdasarkan nama produk, compatibility claim, atau kemampuan driver untuk terkoneksi.

Qualification yang kelak diotorisasi harus mencakup secara proporsional:

- logical contract mapping;
- transaction behavior;
- exact-money semantics;
- tenant-aware uniqueness dan referential integrity;
- UUID/identifier mapping;
- JSON semantics;
- date/time semantics;
- collation/case-sensitivity behavior;
- migration/schema evolution behavior;
- backup/verified restore;
- operational limits;
- representative performance/query-plan evidence;
- Database Portability Contract conformance.

MariaDB 11.4 evidence pada hosting saat ini adalah **engine-family/version evidence**, bukan runtime qualification.

## Data ownership

Setiap tabel memiliki owning module. Modul lain mengakses data melalui application contract atau event, bukan join/write langsung. Shared reference data harus memiliki owner dan lifecycle yang jelas.

## Tenant isolation

Baseline yang disetujui dan dipertahankan oleh DEC-005R:

- tenant ID immutable menjadi isolation key;
- domain/subdomain hanya routing hint;
- tenant-scoped table memiliki tenant ID non-null;
- unique constraint tenant-scoped menyertakan tenant ID;
- foreign key tenant-scoped mencegah referensi lintas tenant;
- query enforcement berada pada repository/data-access boundary;
- privileged cross-tenant access menggunakan interface terpisah dan audit.

Default physical isolation adalah **shared database + shared schema** dengan mandatory tenant identity. Dedicated database atau stronger physical storage boundary hanya merupakan bounded future evolution path untuk requirement enterprise/regulatory/jurisdiction/scale/recovery/security yang separately verified dan separately authorized.

Tenant authorization tetap Application-authoritative. Database constraint dan database-native security mechanism berfungsi sebagai integrity enforcement dan defense-in-depth, bukan pengganti Application authorization ownership.

## Identifier strategy

- Public identifier harus sulit ditebak bila enumeration berisiko.
- Internal identifier tidak boleh dipakai sebagai authorization control.
- Tenant ID tidak dapat berubah setelah dibuat.
- External provider ID disimpan bersama provider dan tenant context.
- Natural key bisnis dapat berubah dan bukan default primary key.
- Logical identifier contract harus dapat dipetakan secara deterministic ke setiap officially qualified engine profile.

## Data types

Canonical logical data vocabulary harus engine-neutral. Existing foundation direction mencakup `STRING`, `INTEGER`, `DECIMAL`, `BOOLEAN`, `UUID`, `DATE`, `DATETIME`, dan `JSON`.

- Money menggunakan fixed precision decimal dan currency code.
- Quantity menggunakan precision sesuai domain dan unit eksplisit.
- Time disimpan sebagai UTC instant; local business date disimpan bila memiliki makna domain.
- Boolean tidak digunakan bila state lebih dari dua; gunakan explicit status.
- JSON hanya untuk data fleksibel yang tidak membutuhkan relational constraint/query kritis.
- Sensitive value memiliki classification dan encryption/tokenization policy.
- Perbedaan physical type antar engine tidak boleh mengubah canonical business semantics.

## Schema conventions

- Nama konsisten, eksplisit, dan mengikuti ubiquitous language.
- `created_at`, `updated_at`, actor/audit field, dan version field digunakan sesuai kebutuhan.
- Soft delete bukan default; gunakan bila retention dan restore semantics jelas.
- Status transition dijaga application/domain invariant dan audit.
- Index dibuat berdasarkan access pattern dan diverifikasi dengan execution plan.
- Vendor-specific behavior atau optimization tidak boleh menjadi dependency Domain/Application; detail vendor ditempatkan pada Infrastructure engine-profile boundary.
- Physical mapping harus berasal dari canonical logical contract dan tidak boleh silently coerce semantic differences.

## Migration policy

Setiap perubahan schema yang kelak diotorisasi wajib memiliki:

- unique version dan descriptive name;
- forward migration;
- rollback/recovery strategy;
- compatibility window;
- estimated duration/lock impact;
- backup requirement;
- test pada snapshot representatif;
- owner dan monitoring signal;
- engine-profile applicability/compatibility evidence.

Destructive change menggunakan **expand → migrate → verify → contract**. Application versi lama dan baru harus dapat berjalan selama compatibility window bila rolling/staged deployment digunakan.

DEC-005R menambahkan future DBME architecture direction: preflight/dry-run, compatibility analysis, physical adaptation hanya jika equivalent, fail-closed unsafe conversion, reconciliation, controlled cutover, source retention, dan rollback hanya jika genuinely safe. Tidak ada executable DBME/migration yang diotorisasi oleh handbook ini.

## Data migration

Untuk future separately authorized migration/DBME execution:

- Batch besar resumable dan idempotent bila semantics memungkinkan.
- Simpan checkpoint, progress, failure count, dan correlation ID.
- Rate dibatasi agar OLTP tetap sehat.
- Rekonsiliasi count, total, checksum, dan domain invariant setelah migrasi.
- Source data dipertahankan sampai controlled acceptance/cutover policy terpenuhi.
- Unsafe/lossy conversion harus gagal sebelum cutover.
- Raw sensitive data tidak boleh diekspor ke workstation tanpa masking dan approval.
- Privileged migration operations memerlukan least privilege, explicit authority, dan audit evidence.

## Transaction and concurrency

- Transaction boundary mengikuti use case.
- Gunakan optimistic concurrency/versioning untuk conflicting edit bila sesuai.
- Lock explicit harus bounded dan memiliki deadlock handling.
- Distributed side effect menggunakan outbox/saga-like compensation, bukan transaction lintas vendor.
- Financial posting dan inventory movement harus idempotent dan auditable.
- Engine-profile implementation tidak boleh mengubah externally observable business transaction semantics.

## Audit and history

Audit minimum mencatat actor, tenant, action, resource, before/after yang aman, timestamp, source, correlation ID, dan outcome. Secret, password, token, dan sensitive payment payload tidak boleh masuk audit. Retention serta immutability ditetapkan berdasarkan classification dan compliance.

DBME/mobility operations yang kelak diimplementasikan harus menghasilkan audit evidence untuk preflight, source/target profile, plan identity, reconciliation, cutover, failure, dan recovery outcome.

## Backup and restore

- Backup terenkripsi, access-controlled, monitored, dan memiliki retention.
- Backup tenant harus dapat ditemukan dan dipulihkan sesuai isolation model.
- Restore test dilakukan berkala pada environment terisolasi.
- Keberhasilan job backup bukan bukti recoverability; hanya restore rehearsal yang lulus.
- Shared-schema physical backup tidak otomatis membuktikan tenant-scoped recoverability; tenant recovery memerlukan separately designed and verified procedure.
- RPO/RTO ditetapkan per capability sebelum production melalui DEC-012.
- Engine-profile qualification harus menyertakan relevant backup/restore evidence sebelum runtime acceptance.

## Performance

- Semua collection query memiliki limit.
- N+1 dan full scan pada hot path dilarang.
- Index mempertimbangkan tenant ID sebagai leading component sesuai access pattern.
- Reporting berat dipindahkan ke read model/warehouse saat threshold tercapai.
- Slow query budget, connection pool, storage growth, dan engine-specific maintenance dipantau melalui Infrastructure profile.
- Portability tidak berarti mengabaikan engine-specific optimization; optimization harus tetap berada di Infrastructure dan tidak mengubah business contract.

## Privacy lifecycle

Setiap data class memiliki purpose, owner, legal basis bila relevan, retention, deletion/anonymization, export, dan access policy. Tenant deletion harus aman, terotorisasi, bertahap, dapat diaudit, dan menghormati retention obligation.

Cross-engine mobility tidak boleh menurunkan privacy classification, tenant isolation, encryption/security boundary, atau retention obligations.

## Required tests

Future separately authorized persistence implementation harus mencakup secara applicable:

- migration forward dan recovery;
- tenant isolation dan foreign-key tampering;
- monetary precision dan rounding;
- concurrency/idempotency;
- backup/restore;
- data retention/deletion;
- performance query kritis;
- compatibility antara application version selama rollout;
- Database Portability Contract tests;
- cross-engine behavioral qualification untuk officially supported profiles;
- fail-closed unsupported/lossy mapping tests;
- migration reconciliation tests.

## Current implementation boundary

Current `apps/web` POS/business persistence masih synthetic/in-memory. Bounded Infrastructure coupling tetap ada pada `src/Persistence` dan `src/PhysicalMapping`; existing logical DataDefinition foundation sudah memiliki portable logical vocabulary direction. Migration foundation saat ini adalah governance/planning/dry-run foundation, bukan live DBME.

Publication DEC-005R ini tidak mengubah source tersebut.

## Database change checklist

1. Owner module dan business purpose jelas.
2. Tenant scope, key, constraint, dan index benar.
3. Canonical logical semantics dan engine-profile mapping jelas.
4. Classification/encryption/retention ditetapkan.
5. Migration dan rollback/recovery direhearsal.
6. Lock, size, duration, dan load impact dianalisis.
7. Database Portability Contract impact diperiksa.
8. API/event/report consumers diperiksa.
9. Tests, monitoring, documentation, task, dan changelog diperbarui.
10. Tidak ada database/vendor dependency yang bocor ke Domain/Application.

## Authority boundary

Dokumen ini tidak mengotorisasi physical schema, SQL, DDL, executable migrations, seeders, database drivers/adapters, DBME, cross-engine CI, live database access, credentials, data movement, M7.5, Sprint 14, deployment, release, atau Production.

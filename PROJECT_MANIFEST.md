# oneQay Project Manifest

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


## Canonical Sprint40 pre-source delivery state — 2026-08-25

This current-facing section supersedes older status summaries retained below as historical provenance.

| Item | Canonical state |
| --- | --- |
| Sprint 21–Sprint 39 governed identity/control foundations | **COMPLETE / IMPLEMENTED / PUBLISHED** within bounded authority |
| Sprint40 selected concern | **First-Party Session Identity Disablement Revalidation Foundation** |
| Sprint40 entry gate | **PUBLISHED** — PR #268 |
| Sprint40 schema/source-envelope gate | **PUBLISHED** — PR #270 |
| Sprint40 source-preservation predecessor | **PUBLISHED** — PR #271; compatibility prerequisites PR #272/#273 |
| Sprint40 documentation-sync preservation predecessor | **PUBLISHED** — PR #274 → `7be563d66e2f8441cf28dd2ed9ce5d6c0704098f`, tree `adbbce29218e312b243076dc3ee984e68ce79b65` |
| Canonical source migrations on `main` | **Exactly #1–#13** |
| Migration #14 | **SELECTED FOR LATER SPRINT40 SOURCE STAGE / DOES NOT YET EXIST OR APPLY ON CANONICAL `main`** |
| Frozen future Sprint40 source envelope | **8 paths** / SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8` |
| Exact 13-document synchronization envelope | SHA-256 `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d` |
| Sprint40 application source implementation | **NOT YET IMPLEMENTED / SEPARATELY GOVERNED** |
| Technical Preview | **`NO_SCHEMA_CHANGE` / SPRINT40 NOT ACTIVATED** |
| Production | **`NO-GO / NOT AUTHORIZED`** |
| Updater | **`DISABLED / UNWIRED`** |
| Deployment / Release | **NOT AUTHORIZED** |

The Sprint40 schema gate selects only one future additive eligibility field: non-null boolean `first_party_authentication_enabled` with default `true` on `oneqay_identities`, via the separately governed future migration `apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`. Selection is not creation, execution, Preview application, or Production application.

Sprint40 source semantics are frozen as request-time, fail-closed, server-authoritative identity eligibility revalidation for the exact identity already carried by verified logical session authority. Credential/factor epochs, session inventory/revocation, idle/absolute lifetime, tenant membership, and exact organization/outlet/device revalidation remain independent controls and are not replaced by identity eligibility.

This 13-document synchronization is documentation-only. It creates no source, dependency, route/API, migration, schema application, runtime activation, Technical Preview, Production, updater, deployment, or release authority. After publication, the next logical governed stage is the frozen eight-path Sprint40 source implementation against a freshly verified canonical `main`, subject to separate authority.

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

## Canonical post-Sprint 28 delivery-state reconciliation — 2026-08-18

For current delivery-state, capability, and next-work interpretation, this section supersedes all older current-facing M7.5/updater assertions retained below as historical checkpoints and governance provenance.

| Item | Canonical state |
| --- | --- |
| Sprint 21–Sprint 28 governed foundations | **COMPLETE / IMPLEMENTED / PUBLISHED** within bounded authority |
| Sprint 28 source publication | PR #188 → `b012262b0028c21c7662d5a9edec3cbf249bba5e` |
| Post-Sprint28 canonical reconciliation | PR #189 → `68a9b5736a3fc169b50984857954322b169bc42e` |
| Canonical migrations | **Exactly #1–#8** |
| Migrations #1–#7 | **IMMUTABLE** |
| Migration #8 | **ADDITIVE / FORWARD-ONLY** initial-password-enrollment schema |
| Sprint 26 credential verification | **PUBLISHED — Local/Test/CI only** |
| Sprint 27 first-party login/session establishment | **PUBLISHED — Local/Test/CI only** |
| Sprint 28 initial password enrollment | **PUBLISHED — Local/Test/CI only** |
| Technical Preview | **`NO_SCHEMA_CHANGE`** |
| Production | **`NO-GO / NOT AUTHORIZED`** |
| Updater | **`DISABLED / UNWIRED`** |
| Persistence default | **`ONEQAY_PERSISTENCE_ENABLED=false`** |
| Next logical identity concern | **First-Control-Principal Bootstrap Credential Foundation — UNRESOLVED / NOT AUTHORIZED** |

The published Sprint 21–28 control/identity progression includes durable scoped role/permission policy, policy administration, initial tenant-administrator provisioning, protected-control administrator lifecycle, policy-administration delivery, exact tenant-scoped password credential verification, first-party session establishment, and two-step initial password enrollment.

Sprint 28 separates administrator authorization from target password selection. Initial password enrollment persists only token digests, creates credentials insert-only using `PASSWORD_DEFAULT`, and does not establish a login session. Password change/reset/recovery/rotation/revocation remain separately governed.

This documentation reconciliation creates no Sprint 29 implementation authority, no migration #9 authority, no new workflow YAML, no source/dependency/schema/runtime mutation, no Preview credential delivery, no Production authentication/enrollment activation, no updater activation, no deployment, and no Release/Production authority. A future First-Control-Principal Bootstrap Credential Foundation requires its own bounded entry gate.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current delivery-state interpretation, this section supersedes older current-facing M7.5/P1/P2 assertions retained below as historical checkpoints and governance provenance.

| Item | Canonical state |
| --- | --- |
| M7.0–M7.4A | **DONE / PUBLISHED** |
| M7.5 Preview Runtime Qualification | **CLOSED / EVIDENCE_COMPLETE / PUBLISHED** |
| M7.5 evaluator after PR #129 / cleanup PR #130 | **29 VERIFIED / 0 BLOCKED** |
| Mandatory M7.5 blockers | **NONE** |
| Lifecycle authority created by M7.5 evidence | `false` |
| M7.6 | **NOT AUTHORIZED** |
| M7.7 | **NOT AUTHORIZED** |
| Phase 0 | **IN PROGRESS** |
| Phase 0 Exit | **NOT APPROVED** |
| Sprint 14 | **NOT AUTHORIZED** |
| Release | **NOT AUTHORIZED** |
| Production | **NOT AUTHORIZED** |
| Production readiness | **NO-GO** |

PR #129 published the final successful isolated backup/restore evidence and promoted `ENGINE:RESTORE_VERIFIED` and `RUNTIME:BACKUP_RESTORE` to `VERIFIED`. PR #130 published secure cleanup of the disposable rehearsal environment. Together with the already-published database-backed tenant-isolation evidence, M7.5 mandatory runtime/engine evidence is complete at **29 VERIFIED / 0 BLOCKED** with no remaining blocker.

This closure is canonical state synchronization only. It creates no application source, test source, dependency, workflow YAML, database/schema/migration, cPanel, deployment, restore, M7.6, M7.7, Phase 0 Exit, Release, or Production authority. The next candidate engineering direction is separately gated Secure Web Updater architecture/release-control-plane work.

Historical SHAs, PRs, decision records, governance recurrence evidence, and prior evaluator snapshots below remain preserved as provenance.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current delivery-state interpretation, this section supersedes older current-facing M7.5/P1/P2 assertions retained below as historical checkpoints and governance provenance.

| Item | Canonical state |
| --- | --- |
| M7.0–M7.4A | **DONE / PUBLISHED** |
| M7.5 Preview Runtime Qualification | **IN PROGRESS / QUALIFICATION MATERIAL PROGRESS** |
| M7.5 evaluator after PR #124 | **26 VERIFIED / 3 BLOCKED** |
| M7.5 overall | **BLOCKED / INCOMPLETE** |
| Remaining blockers | `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`; `ENGINE:TENANT_ISOLATION:PARTIAL`; `RUNTIME:BACKUP_RESTORE:PARTIAL` |
| Lifecycle authority created | `false` |
| M7.6 | **NOT AUTHORIZED** |
| M7.7 | **NOT AUTHORIZED** |
| Phase 0 | **IN PROGRESS** |
| Phase 0 Exit | **NOT APPROVED** |
| Sprint 14 | **NOT AUTHORIZED** |
| Release | **NOT AUTHORIZED** |
| Production | **NOT AUTHORIZED** |
| Production readiness | **NO-GO** |

Published PR #102–#124 and sanitized evidence supersede older current-state statements that M7.5 had not begun or that controls later promoted to `VERIFIED` remained partial/unverified. Historical SHAs, PRs, decision records, governance recurrence evidence, and prior evaluator snapshots below are preserved and remain historical provenance. This consolidation creates no application, database, migration, schema, restore, deployment, Release, Production, or later-milestone authority.

> Dokumen identitas teknis kanonis oneQay. Bila informasi di dokumen lain bertentangan, keputusan berstatus **Approved** di manifest ini berlaku sampai digantikan melalui ADR atau pull request yang disetujui.

## Metadata

| Atribut | Nilai | Status |
| --- | --- | --- |
| Product | oneQay | Approved |
| Tagline | The Future of Intelligent Business Management | Approved |
| Developer & Product Engineering Entity | Lab \| zefry | Approved |
| Category | Enterprise SaaS POS & ERP Platform | Approved |
| Enterprise Vision | Enterprise Intelligent Business Management Platform | Approved — GOV-051 substantive Product Owner decision; canonical representation published through PR #69 |
| Repository | `labzefry/oneQay` | Approved |
| Source of Truth | GitHub | Approved |
| Delivery model | Multi-tenant SaaS | Approved |
| Architecture baseline | Modular Monolith, Clean Architecture | Approved |
| Handbook version | 1.0 | Approved |
| Product version | Belum ditetapkan | Under Review |
| License | Proprietary / All Rights Reserved | Approved — DEC-010 product policy; final legal text remains Legal Review Required |

Engineering collaboration tooling is governed separately by `AI_CONSTITUTION.md` and is not product authorship or product attribution metadata. Canonical product/development attribution is **Lab | zefry**.

## Canonical product naming

The canonical product name is **oneQay**.

Current and future canonical references must use `oneQay`. Non-canonical current-brand forms include `OneQay`, `ONEQAY`, `Oneqay`, and `oneqay`.

Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, commit messages, branch names, and quoted historical evidence are preserved as recorded and are not rewritten merely for brand normalization.

## Status definitions

| Status | Arti |
| --- | --- |
| Approved | Telah disetujui dan mengikat implementasi sesuai scope keputusan; status Approved tidak boleh dibaca melampaui boundary keputusan pemiliknya |
| Proposed | Usulan siap direview, belum mengikat |
| Under Review | Sedang dianalisis atau membutuhkan keputusan |
| Deferred | Sengaja ditunda sampai entry criteria terpenuhi |
| Deprecated | Tidak boleh digunakan untuk pekerjaan baru |

## Product intent

oneQay diarahkan menjadi platform intelligent business management yang menyatukan fungsi transaksi, POS, ERP, administrasi tenant, integrasi, marketplace, plugin, insight, dan AI-assisted capabilities dalam fondasi yang aman serta dapat berkembang dari shared hosting menuju Kubernetes tanpa mengubah business logic.

M6 telah mempublikasikan representasi canonical Enterprise Vision **Enterprise Intelligent Business Management Platform** melalui PR #69. Publication tersebut tidak dengan sendirinya mempromosikan substantive Enterprise Vision decision; Product Owner kemudian memberikan keputusan substantif terpisah GOV-051 yang **APPROVED** pada verified repository baseline `762149757e4bc1fa79cc16bc4761f4147be0f7ea` dan canonical artifact blob `bb1cace72a6fdb359e15e22467443d9f3916c336`. Approval tersebut menetapkan long-term product direction, tetapi tidak menyatakan bahwa seluruh capability telah terimplementasi atau production-ready dan tidak memberikan Sprint 14 atau implementation authority.

Product Owner kemudian memberikan substantive DEC-000 Product Vision and Decision Rights decision yang **APPROVED** pada decision baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`, tree `08f03b895d5e2ae7ca402e9866384990e126add3`, canonical owner artifact blob `843544b9e31dd4c47638b88dd204f4e594295df4`, dan readiness artifact blob `b493a5d66edc1bbffab0126bdacf2ca1ce14fa8f`. DEC-000 mengesahkan GD-003 dalam boundary product/discovery governance saja dan tidak memberikan implementation authority.

Product Owner kemudian memberikan substantive DEC-001 MVP Scope and Non-Scope decision yang **APPROVED** pada decision baseline `17f156b9861972b4924a5ed01bfabd5a1a79461a` dan verified tree `33241c18a1b7da2efc7dd2889c13c25c6e8526d5`. DEC-001 menetapkan **POS CORE TRANSACTION & OUTLET OPERATIONS** sebagai first bounded MVP delivery slice dengan actor, journey, dependency, explicit non-scope, outcome, guardrail, dan open-item boundary yang direkam di `docs/handbook/DEC_001_DECISION_RECORD.md`. DEC-001 tidak memberi Sprint 14, application/business implementation, SQL/schema/migration, production database, deployment, release, ADR acceptance, JRN resolution, atau production-readiness authority.

Product Owner kemudian memberikan substantive DEC-002 Backend Language / Application Framework decision yang **APPROVED** pada decision baseline `504b10be44d45dfcfec9b6cfed4f72ed5748b564` dan verified tree `e4622a45f9f298b95358b3d662be3cd48607e4d9`. DEC-002 menetapkan PHP sebagai backend language, Laravel sebagai application framework, Modular Monolith First + Clean Architecture sebagai architecture direction, Domain/Application tetap framework-independent, dan framework berperan sebagai delivery/composition/infrastructure. Keputusan direkam di `docs/handbook/DEC_002_DECISION_RECORD.md` dan direkonsiliasi melalui ADR-001 tanpa memberi implementation, dependency-change, Sprint 14, deployment, release, atau production-readiness authority.

Product Owner kemudian memberikan substantive DEC-003 Frontend / PWA Stack decision yang **APPROVED** pada decision baseline `dcb7e3f8de890530a00a0dd4fd310bc10762c72f` dan verified tree `b78d1f1452469a8ba856092e647bef92410f2517`. DEC-003 menetapkan Vue 3, Vue Composition API, TypeScript-first, Inertia untuk first-party authenticated Web/PWA delivery, Vite, local-first state dengan Pinia secara bounded, Modern Monolith Web Delivery + Explicit API Boundaries, dan PWA foundation dengan service-worker/cache security boundary. Keputusan direkam di `docs/handbook/DEC_003_DECISION_RECORD.md` dan direkonsiliasi melalui ADR-002 tanpa memberi package/dependency installation, frontend/backend implementation, Sprint 14, deployment, release, atau production-readiness authority.

Product Owner kemudian memberikan substantive DEC-004 Android Approach decision yang **APPROVED** pada decision baseline `97b2e5066118af2b3e9467afc71e84dce228eb38` dan verified tree `2f979948184f475b52cf87b2d105c56364ebe883`. DEC-004 menetapkan **Hybrid Staged Approach**, Native Android menggunakan Kotlin, Jetpack Compose, PWA sebagai preferred general mobile-capable channel, explicit API/mobile boundary, bounded native device adapters, DEC-006 authentication boundary, DEC-008 offline boundary, minimal tenant/session-scoped local state, dan distribution compatibility direction. Keputusan direkam di `docs/handbook/DEC_004_DECISION_RECORD.md` dan direpresentasikan melalui ADR-008 tanpa memberi Android project/source, Gradle/dependency, API implementation, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-005 Database Engine and Physical Tenancy Model decision yang **APPROVED** pada decision baseline `63646e1cccc611a1911c452397059983030dfe66` dan verified tree `80cd3bbf1a0c1d454e73c89f17d8896941f369cd`. DEC-005 menetapkan **MySQL Server** sebagai canonical relational database engine family, supported MySQL LTS-family boundary dengan exact version deferred, shared database/shared schema sebagai default physical tenancy dengan immutable tenant isolation key, bounded future hybrid isolation path, Application-authoritative tenant authorization with database defense-in-depth, database/vendor behavior sebagai Infrastructure concern, compatible/recoverable schema-evolution principle, dan verified-restoration recoverability principle. Keputusan direkam di `docs/handbook/DEC_005_DECISION_RECORD.md` dan direpresentasikan melalui reconciled ADR-003 tanpa memberi schema/SQL/migration/database implementation, dependency, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-006 Authentication / MFA / Session Architecture decision yang **APPROVED** pada decision baseline `c495f1b1f6e4e624bf3669ee94a786a1d7e865ce` dan verified tree `795d53f326e6ee2ee52474f79b284dea1ce744da`. DEC-006 menetapkan first-party oneQay platform identity, server-side first-party Web/PWA session, explicit Android/API token boundary, server-authoritative rotation/revocation, TOTP privileged MFA baseline, WebAuthn/passkey evolution direction, adaptive password/credential security principles, high-risk recovery dengan JRN-003 tetap unresolved, global identity + tenant memberships, controlled support impersonation/break-glass separation, dan future OIDC-compatible federation. Keputusan direkam di `docs/handbook/DEC_006_DECISION_RECORD.md` dan direpresentasikan melalui reconciled ADR-004 tanpa memberi authentication implementation, package/dependency, identity schema, SQL/migration, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-007 Payment Provider and Compliance Boundary decision yang **APPROVED** pada decision baseline `50955d101c455c6af7356197d9e06d6d76e753bb` dan verified tree `2987eccc6bf4ba8ece23ee2343b178e518a454b3`. DEC-007 menetapkan **CASH-FIRST + CONFIGURABLE MANUAL / EXTERNAL RECORDED TENDERS** untuk first bounded MVP, memisahkan `CASH_COUNTED`, `OPERATOR_RECORDED`, dan future `PROVIDER_VERIFIED` evidence, menetapkan future provider-abstracted electronic-payment boundary, menunda provider selection, mempertahankan sale-level payment sufficiency dan idempotency/replay controls, memisahkan refund/reversal/dispute dan settlement/reconciliation, meminimalkan restricted payment-account-data exposure, mempertahankan jurisdiction-neutral architecture serta DEC-008 offline ownership, dan merekonsiliasi ADR-005 sambil menjaga historical PAY-1 provenance. Keputusan direkam di `docs/handbook/DEC_007_DECISION_RECORD.md` dan direpresentasikan melalui materially reconciled ADR-005 tanpa memberi provider selection/account/contract, payment source, package, schema/SQL/migration, real-money processing, offline, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-008 Offline POS Semantics and Conflict Resolution decision yang **APPROVED** pada decision baseline `21f1b2e938d9f6a42b1dd2ff717c4e049b5119d7` dan verified tree `8cf993f0c82c84bdc46a18aa70c4cb5425b89ac6`. DEC-008 menetapkan **STAGED / HYBRID OFFLINE ARCHITECTURE** dengan first bounded MVP tetap **ONLINE-AUTHORITATIVE TRANSACTIONS**, O1 bounded degraded/read-only direction, future O2 provisional client operations yang memerlukan server validation/acceptance, stable operation identity, deterministic replay/idempotency, explicit conflict classification/resolution, server-authoritative inventory/payment/tenant/shift correctness, bounded local-data security, bounded causal ordering, offline reconciliation/failure recovery/audit, dan Native Android sebagai preferred initial future O2 transactional-offline channel. Keputusan direkam di `docs/handbook/DEC_008_DECISION_RECORD.md` dan direpresentasikan melalui materially reconciled ADR-006 tanpa memberi offline transaction source, Android/PWA implementation, local database/queue technology, schema/SQL/migration, package/dependency, Sprint 14, deployment, release, atau production authority.

Product Owner kemudian memberikan substantive DEC-009 Deployment Stage 1 Runtime Requirements decision yang **APPROVED** pada decision baseline `0fdc0a53403f16fbc6908630ea350af2c0de466b` dan verified tree `45c0aa49657db8f95ca08e662ec641e6d9d5f25a`. DEC-009 menetapkan **CAPABILITY-BASED STAGED / HYBRID PORTABILITY MODEL**, P1 Shared Hosting/cPanel sebagai conditional candidate yang **NOT SELECTED**, P2 Managed/Hardened VPS or Server sebagai fallback execution class, Stage-1 `Preview` environment, PHP `>=8.2` baseline, Build Once / Deploy Trusted Artifact, DEC-005 canonical MySQL Server requirement, bounded scheduler/worker/session/cache/storage/HTTPS/secrets/observability/backup/restore/release/rollback requirements, dan provider-neutral Domain/Application portability. Keputusan direkam di `docs/handbook/DEC_009_DECISION_RECORD.md` dan direpresentasikan melalui materially reconciled ADR-007 tanpa memberi hosting procurement, infrastructure provisioning, dependency installation, schema/SQL/migration, deployment, release, Sprint 14, atau production authority.

Product Owner kemudian memberikan substantive DEC-011 Data Retention, Privacy, and Jurisdiction decision yang **APPROVED** pada decision baseline `6c6af7f99d25f177c91f92cdd163a277affc5153` dan verified tree `efa336169e902e6bddd7f3fff47a0e91d15b5a19`. DEC-011 menetapkan **BOUNDED PRIVACY-BY-DESIGN + HYBRID BOUNDED RETENTION + JURISDICTION-PROFILE ARCHITECTURE**, mempertahankan DEC-005 tenant-isolation architecture, memisahkan final RPO/RTO ke DEC-012, menetapkan initial commercial/launch jurisdiction sebagai **NOT YET CANONICALLY SELECTED**, dan mewajibkan qualified legal review sebelum jurisdiction-specific implementation. Keputusan direkam di `docs/handbook/DEC_011_DECISION_RECORD.md` tanpa memberi jurisdiction/provider/hosting-region selection, production/customer data, schema/SQL/migration, implementation, deployment, release, Sprint 14, atau production authority; JRN-003 dan JRN-013 tetap unresolved.

## Current delivery gate

| Item | Status | Gate |
| --- | --- | --- |
| Handbook 1.0 governance baseline | Approved | PR #1 disetujui dan di-merge ke `main` |
| Phase 0 governance and discovery | In Progress | Phase 0 exit belum disetujui secara eksplisit |
| Bounded Platform Foundation through Sprint 13 | Published | Sprint 12 dan Sprint 13 adalah fakta repository yang telah dipublikasikan melalui authority terpisah |
| M5.1 Canonical State Reconciliation | Published / Complete | PR #66 |
| M5.2 CI & Lifecycle Control Hardening | Published / Enforcement Complete | PR #67 |
| M5.3 Governance & Program State Synchronization | Published / Complete | PR #68; published commit `e45f5b4c0f143abc6e255e4e8550bf3504348aae` |
| M6 Enterprise Vision Canonicalization | Published / Publication Complete | PR #69; source head `e6a3345b09a6b270ac7e09abd78c6356f426e363`; published commit `0b7b28028966ac38af0f32960054210c3a083916`; source/published tree `567df997bae70090b19465c75e4cc3b1e23b6579` |
| M7.0 Controlled Implementation Bridge | Done / Published | Governed publication complete; no standing later-milestone authority |
| M7.1 Application Skeleton & Configuration Boundary | Done / Published | PR #92 |
| M7.2 Tenant Kernel & Isolation Foundation | Done / Published | PR #93 |
| M7.3 Identity / Organization / Outlet / Device Minimum | Done / Published | PR #94 |
| M7.4 POS Core Synthetic Vertical Slice | Done / Published | PR #96 |
| M7.4A Technical Preview Interaction Layer | Done / Published | PR #98; source head `893b73b8f20b2ede0d3a8896b3a015df5370dbed`; source/published tree `cdc140e5061481bec4b6b691b02b2b234181c2fb`; published commit `c0bdf8ad7539a5c83de2e5183fbf2eda9f17f02b` |
| M7.5 Preview Runtime Qualification | Blocked / Not Authorized | Actual sanitized P2 target evidence, DEC-009 capability verification, and selected relational engine-profile qualification under DEC-005R required before separate Product Owner authority |
| M7.6 Preview Deployment / Recovery Rehearsal | Blocked / Not Authorized | Qualified runtime target plus separate deployment authority required |
| M7.7 Technical Preview Acceptance | Blocked / Not Authorized | Combined source, security, runtime, recovery, and operational evidence required |
| GOV-051 Enterprise Vision substantive decision | Approved / Decision Complete | Product Owner APPROVED `Enterprise Intelligent Business Management Platform` on verified baseline `762149757e4bc1fa79cc16bc4761f4147be0f7ea`; approval is product direction only, not implementation authority |
| DEC-000 Product Vision and Decision Rights | Approved / Decision Complete | Product Owner APPROVED D-000-01 through D-000-06 on baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`; `docs/handbook/DEC_000_DECISION_RECORD.md`; GD-003 Approved within DEC-000 boundary only |
| DEC-001 MVP Scope and Non-Scope | Approved / Decision Complete | Product Owner APPROVED **POS CORE TRANSACTION & OUTLET OPERATIONS** on baseline `17f156b9861972b4924a5ed01bfabd5a1a79461a`; `docs/handbook/DEC_001_DECISION_RECORD.md`; no implementation authority |
| DEC-002 Backend Language / Application Framework | Approved / Decision Complete | Product Owner APPROVED PHP + Laravel on baseline `504b10be44d45dfcfec9b6cfed4f72ed5748b564`; `docs/handbook/DEC_002_DECISION_RECORD.md`; ADR-001 Accepted after reconciliation; no implementation authority |
| DEC-003 Frontend / PWA Stack | Approved / Decision Complete | Product Owner APPROVED Vue 3 + Inertia + Vite with TypeScript-first, explicit API/mobile boundaries, local-first state, and bounded PWA direction on baseline `dcb7e3f8de890530a00a0dd4fd310bc10762c72f`; `docs/handbook/DEC_003_DECISION_RECORD.md`; ADR-002 Accepted after reconciliation; no implementation authority |
| DEC-004 Android Approach | Approved / Decision Complete | Product Owner APPROVED Hybrid Staged Approach with Kotlin + Jetpack Compose on baseline `97b2e5066118af2b3e9467afc71e84dce228eb38`; `docs/handbook/DEC_004_DECISION_RECORD.md`; ADR-008; no implementation authority |
| DEC-005 Database Engine and Physical Tenancy Model | Approved Historical / Partially Superseded by DEC-005R | Product Owner originally APPROVED MySQL Server + shared database/shared schema default on baseline `63646e1cccc611a1911c452397059983030dfe66`; historical fact and preserved tenancy/isolation/recoverability principles remain binding according to DEC-005R dispositions; no database/schema/SQL/migration implementation authority |
| DEC-005R Portable Relational Persistence Architecture | Approved / Decision Complete / Published | Product Owner **APPROVED — OPTION C**; PR #100 source head `8ec7069b08c9127e402fa80e5e79ca26be2b63d6`; source/published tree `0862c851d30c11c37c39d13aa5660d042da91989`; published squash commit `b5cbdeb6ea45d4f159f3d1cd39cadc561605c5ff`; current engine-neutral relational architecture; no source/schema/SQL/migration/cross-engine-CI/DBME/M7.5/deployment authority |
| DEC-006 Authentication / MFA / Session Architecture | Approved / Decision Complete | Product Owner APPROVED first-party identity + Web/PWA session + Android/API token boundary on baseline `c495f1b1f6e4e624bf3669ee94a786a1d7e865ce`; `docs/handbook/DEC_006_DECISION_RECORD.md`; reconciled ADR-004; JRN-003 unresolved; no implementation/package/schema/migration authority |
| DEC-007 Payment Provider and Compliance Boundary | Approved / Decision Complete | Product Owner APPROVED cash-first + configurable manual/external recorded tenders and provider-abstracted future electronic-payment direction on baseline `50955d101c455c6af7356197d9e06d6d76e753bb`; `docs/handbook/DEC_007_DECISION_RECORD.md`; reconciled ADR-005; provider selection deferred; no payment/provider/package/schema/SQL/implementation authority |
| DEC-008 Offline POS Semantics and Conflict Resolution | Approved / Decision Complete | Product Owner APPROVED Staged / Hybrid Offline Architecture on baseline `21f1b2e938d9f6a42b1dd2ff717c4e049b5119d7`; first-MVP online-authoritative transactions; future provisional server-validated offline direction; `docs/handbook/DEC_008_DECISION_RECORD.md`; reconciled ADR-006; no offline/source/schema/package implementation authority |
| DEC-009 Deployment Stage 1 Runtime Requirements | Approved / Decision Complete | Product Owner APPROVED capability-based Stage-1 runtime architecture on baseline `0fdc0a53403f16fbc6908630ea350af2c0de466b`; `docs/handbook/DEC_009_DECISION_RECORD.md`; reconciled ADR-007; P1 conditional/not selected; current database dependency requires an authorized and runtime-qualified relational engine profile under DEC-005R; no deployment/implementation authority |
| DEC-011 Data Retention, Privacy, and Jurisdiction | Approved / Decision Complete | Product Owner APPROVED bounded privacy-by-design + hybrid bounded retention + jurisdiction-profile architecture on baseline `6c6af7f99d25f177c91f92cdd163a277affc5153`; initial jurisdiction NOT YET CANONICALLY SELECTED; qualified legal review required; `docs/handbook/DEC_011_DECISION_RECORD.md`; no implementation/jurisdiction/provider/deployment authority |
| Final/business application implementation | Blocked | Tidak ada authority untuk implementasi business/final/production application baru |
| Sprint 14 | Not Authorized | Memerlukan Product Owner authority terpisah |
| Production readiness | NO-GO | Tidak ada deployment, release, atau production-migration authority |

Rencana kickoff berada di `docs/handbook/PHASE_0_KICKOFF.md`.

## Canonical Phase 0 semantics

**Phase 0 — Governance and Discovery: In Progress** adalah status program governance/discovery. Status tersebut tidak berarti repository tidak memiliki source code teknis dan tidak menghapus source yang telah dipublikasikan secara sah sebagai bounded Platform Foundation.

Published Sprint 12 dan Sprint 13 serta governed M7.0–M7.4A publication tetap merupakan fakta repository. Publikasi itu tidak berarti Phase 0 telah selesai, tidak otomatis memulai Phase 1 secara penuh, tidak menyetujui final business application, tidak memberi authority untuk Sprint 14, dan tidak memberi M7.5/deployment/release/Production authority.

Mulai M5.3, frasa **application implementation Blocked** harus dibaca sebagai **final/business/production application implementation Blocked**. Tidak ada source authority baru yang diberikan oleh klarifikasi ini.

Historical Phase 0 no-code language dan lifecycle discrepancies tetap dipertahankan sebagai fakta historis. M5.3 hanya menyelaraskan makna kanonik saat ini; M5.3 tidak menulis ulang approval, merge, review, atau sequencing masa lalu.

## M6 Enterprise Vision boundary

M6 memisahkan secara tegas:

1. Product Vision;
2. Product Capability Map;
3. Product Architecture Direction;
4. Delivery Roadmap;
5. Implementation Authority.

Published canonical Enterprise Vision representation oneQay adalah:

**Enterprise Intelligent Business Management Platform**.

High-level capability families mencakup Core Business Platform, Platform Capabilities, Extensibility, AI Platform, dan Channels. Detailnya berada di `docs/handbook/ENTERPRISE_VISION.md`. PR #69 mengesahkan representasi dan provenance; GOV-051 kemudian secara terpisah mengesahkan substantive Enterprise Vision sebagai binding long-term product direction.

Capability-map presence tidak memberikan implementation authority. GOV-051 sendiri tidak mempromosikan bounded context Proposed, ADR, GD-003, GD-007, JRN, Sprint 14, final/business application implementation, deployment, release, SQL/migration, production database modification, atau production readiness. GD-003 kemudian secara terpisah **Approved** melalui substantive DEC-000, tetap tanpa implementation authority.

## Product identity and engineering-tooling boundary

- Canonical product attribution: **Lab | zefry**.
- Canonical product name: **oneQay**.
- Nama alat atau model AI yang dipakai dalam engineering adalah governance/tooling metadata, bukan identitas produk, bukan author produk, dan bukan attribution source code.
- Collaboration model tetap diatur melalui `AI_CONSTITUTION.md` dan GD-002.
- Tidak boleh menambahkan attribution yang menyatakan source code atau produk dibuat oleh AI.
- AI Assistant sebagai capability produk tetap merupakan domain produk tersendiri dan statusnya tidak dipromosikan hanya karena Enterprise Vision memetakan AI Platform secara directional.

## Governance decision register

| ID | Keputusan | Status | Dokumen pemilik |
| --- | --- | --- | --- |
| GD-001 | GitHub sebagai Single Source of Truth | Approved | `AI_CONSTITUTION.md` |
| GD-002 | ChatGPT + GitHub sebagai collaboration model eksklusif | Approved | `AI_CONSTITUTION.md` |
| GD-003 | Product vision dan decision rights | **Approved — DEC-000** | `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md`; `docs/handbook/DEC_000_DECISION_RECORD.md` |
| GD-004 | Final/business application implementation tetap diblokir sampai gate yang berlaku disetujui | Approved | `docs/handbook/PHASE_0_KICKOFF.md` |
| GD-005 | Stakeholder and actor map | Proposed | `docs/handbook/STAKEHOLDER_AND_ACTOR_MAP.md` |
| GD-006 | Current process and user journeys | Proposed | `docs/handbook/CURRENT_PROCESS_AND_USER_JOURNEYS.md` |
| GD-007 | Domain event storming | Proposed | `docs/handbook/DOMAIN_EVENT_STORMING.md`; corrections tracked in Issue #10/#12; governance controls tracked in Issue #14/#16/#18/#20 |

GD-003 is Approved only through the explicit Product Owner substantive DEC-000 decision on baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4` and the corresponding bounded decision record. The approval governs Product Vision and Decision Rights only. It does not approve DEC-001 through DEC-012, accept an ADR, promote GD-007, resolve JRN-003/JRN-013, start Sprint 14, or grant final/business/production implementation, SQL/migration, production DB, deployment, release, or production-readiness authority.

Persetujuan Product Owner atas lima koreksi review PR #9 hanya mengotorisasi correction scope pada Issue #10. Persetujuan tersebut tidak mempromosikan GD-007 atau event, aggregate, bounded context, dan policy hypothesis dari **Proposed**.

Persetujuan Product Owner atas empat koreksi audit PR #11 hanya mengotorisasi correction scope pada Issue #12. Merge PR #11 dan penutupan Issue #10 tidak dianggap sebagai approval substantif GD-007.

Product Owner meratifikasi empat koreksi tersebut pada exact head PR #13 `e4a3b7ba9f94b429b6e50e2856a11b953a336ac0` setelah audit pasca-merge. Merge commit `66865c3c79fc46e7ec67b0576097143288a73ed5` terjadi sebelum approval tercatat dan dilacak melalui Issue #14. Ratifikasi terbatas ini tidak mempromosikan GD-007, event, policy, aggregate candidate, atau bounded-context candidate dari **Proposed**.

PR #15 pada exact head `4ad28a4e8ad5740e6f55f4563a32d09e7bba631a` juga di-merge sebelum approval melalui merge commit `b34f99ea3c5471cfcd6ae82bc6abeb9a3e78441a`; Issue #12 dan Issue #14 kembali ditutup tanpa completion evidence lalu dibuka kembali. Recurrence dan hardening exact-head approval, pre-merge verification, serta issue closure gate dilacak melalui Issue #16. Tidak ada ratifikasi substantif PR #15 dan seluruh status Phase 0/application/domain tetap tidak berubah.

Audit read-only menunjukkan required review dan required status checks tidak efektif atau dapat dibypass pada PR #13/#15. Stale-approval dismissal dan conversation-resolution settings tidak dapat diverifikasi karena tidak ada approval/thread serta GitHub App tidak mengekspos konfigurasi protection. Repository Owner harus memberi direct settings evidence atau formal risk acceptance sebelum Issue #16 ditutup.

PR #17 pada exact head `aaa7510759925c0c62ba5424c93e2356d18c9d3d` kembali di-merge sebelum exact-head approval, review, checks/approved deferral, protection evidence/risk acceptance, dan separate merge authority melalui merge commit `82b45820a67c274bd96866bb048f3f320d6cbe70`. Issue #12, Issue #14, dan Issue #16 juga ditutup prematur lalu dibuka kembali. Recurrence, corrective action, dan prevention action dilacak melalui Issue #18. Product Owner hanya menyetujui temuan audit High/Medium; PR #17 tidak diratifikasi secara substantif dan tidak ada status domain atau delivery yang dipromosikan.

Audit read-only lanjutan menunjukkan PR #17 tidak memiliki review submission, approval comment, atau published commit status ketika merged. GitHub App tetap tidak mengekspos direct branch-protection/ruleset configuration; configured control karena itu tidak diklaim. Untuk perubahan governance berisiko High/Critical, direct settings evidence atau formal risk acceptance yang lengkap dan mengikat nomor PR serta exact head kini menjadi blocking precondition sebelum ready transition, merge, dan issue closure.

PR #19 pada exact head `483fcf3dbe2c5a418ea7aad97bcfcbf26124b631` diubah dari draft dan di-merge tanpa exact-head approval, review, checks/approved deferral, direct protection evidence/formal risk acceptance, atau separate lifecycle authority melalui merge commit `f68c01e85660409fac6c4e85f2f6545dca08f1d7`. Issue #12, Issue #14, Issue #16, dan Issue #18 ditutup sebelum merge tanpa completion evidence lalu dibuka kembali. Recurrence dan pemisahan Product Owner formal-risk-acceptance evidence dilacak melalui Issue #20. Product Owner hanya menyetujui temuan audit High/Medium; PR #19 tidak diratifikasi secara substantif.

Audit read-only PR #19 kembali menunjukkan tidak adanya review submission, approval comment, dan published commit status saat merge. GitHub App tidak mengekspos configured branch-protection/ruleset controls, sehingga configured state tidak diklaim. Direct settings evidence atau formal risk acceptance Product Owner yang lengkap tetap menjadi blocking precondition; risk acceptance hanya menggantikan protection-evidence requirement dalam scope dan tidak memberi lifecycle authority.

## Target platforms

| Platform | Target | Status awal |
| --- | --- | --- |
| Web Application | Operasional utama | Approved |
| Progressive Web App | Akses mobile dan offline-terkendali | Approved |
| Android Native | Kapabilitas perangkat dan pengalaman native | Approved — DEC-004 bounded complementary delivery direction; implementation not authorized |
| REST API | Kontrak internal dan integrasi | Approved |
| Public API | Ekosistem eksternal | Deferred |
| Admin Dashboard | Administrasi platform dan tenant | Approved |
| Landing Website | Akuisisi dan informasi produk | Approved |
| CMS | Konten publik dan operasional | Proposed |
| Marketplace | Distribusi extension/integration | Deferred |
| Plugin System | Ekstensibilitas terkontrol | Deferred |
| AI Assistant | Bantuan operasional dan insight | Proposed |

## Architecture guardrails

- Business logic tidak boleh mengimpor detail framework, database, transport, filesystem, vendor cloud, atau UI.
- Tenant context wajib tersedia sebelum akses data tenant.
- Semua perubahan skema menggunakan migration maju dan rollback plan.
- Semua perubahan API mengikuti versioning serta compatibility policy.
- Modul berkomunikasi melalui application contract dan domain event; akses tabel lintas modul dilarang tanpa keputusan arsitektur.
- Konfigurasi berasal dari environment atau configuration service; secret tidak boleh berada di repository.
- Side effect eksternal harus idempotent, dapat diaudit, dan memiliki timeout serta retry policy.

## Proposed bounded contexts

Daftar berikut adalah hipotesis arsitektur dan harus divalidasi melalui domain discovery sebelum implementasi:

1. Tenant & Subscription
2. Identity & Access Management
3. Organization, Outlet & Device
4. Catalog & Pricing
5. Inventory & Warehousing
6. Sales & Point of Sale
7. Purchasing & Supplier
8. Customer & Loyalty
9. Finance & Accounting
10. Reporting & Analytics
11. Content Management
12. Integration Hub
13. Marketplace & Plugin Management
14. AI Assistance
15. Platform Operations & Audit

Status seluruh bounded context: **Proposed**.

## Multi-tenant baseline

| Keputusan | Nilai | Status |
| --- | --- | --- |
| Isolation key | Immutable Tenant ID | Approved |
| Access hostname | Domain/subdomain sebagai routing, bukan otorisasi | Approved |
| Default isolation model | Shared database + shared schema dengan tenant-scoped data | Approved — preserved from DEC-005 by DEC-005R; no schema/implementation authority |
| Dedicated deployment option | Bounded future dedicated database/storage isolation untuk justified tenant | Approved direction — preserved from DEC-005 by DEC-005R; implementation separately gated |
| Tenant timezone/currency/locale | Wajib tersimpan sebagai konfigurasi tenant | Approved |
| Cross-tenant query | Deny by default; hanya platform operation terotorisasi | Approved |

## Deployment evolution

| Stage | Environment | Status | Exit criteria utama |
| ---: | --- | --- | --- |
| 1 | Capability-Based Preview | Approved direction — DEC-009; P1 cPanel conditional/not selected | Mandatory runtime/security/database/deployment/recovery/observability capabilities verified |
| 2 | Managed / Hardened VPS or Server | Planned / P2 fallback execution class | Used when P1 cannot satisfy a mandatory Stage-1 capability; no provider selected |
| 3 | Dedicated Server | Planned | Beban dan isolasi memerlukan host khusus |
| 4 | Docker | Planned | Pipeline, observability, dan state externalization siap |
| 5 | Cloud | Planned | Autoscaling, managed services, dan DR layak biaya |
| 6 | Kubernetes | Deferred | Skala dan kompleksitas operasional membenarkan orkestrasi |

Perpindahan stage tidak boleh mengubah domain atau business logic.

## Technology decision register

| ID | Keputusan | Status | Dokumen pemilik |
| --- | --- | --- | --- |
| TD-001 | Bahasa dan framework backend | Approved — DEC-002 | `docs/handbook/DEC_002_DECISION_RECORD.md`; `docs/adr/ADR-001-technical-preview-backend.md` |
| TD-002 | Framework web frontend | Approved — DEC-003 | `docs/handbook/DEC_003_DECISION_RECORD.md`; `docs/adr/ADR-002-technical-preview-frontend-pwa.md` |
| TD-003 | Android native stack | Approved — DEC-004 | `docs/handbook/DEC_004_DECISION_RECORD.md`; `docs/adr/ADR-008-android-delivery-approach.md` |
| TD-004 | Relational database engine | Approved — DEC-005R current; DEC-005 historical | `docs/handbook/DEC_005R_DECISION_RECORD.md`; `docs/handbook/DEC_005_DECISION_RECORD.md`; `docs/adr/ADR-003-technical-preview-database-tenancy.md`; `DATABASE.md` |
| TD-005 | Cache dan queue technology | Deferred | ADR |
| TD-006 | Authentication protocol/provider | Approved — DEC-006 | `docs/handbook/DEC_006_DECISION_RECORD.md`; `docs/adr/ADR-004-technical-preview-authentication.md`; `SECURITY.md` |
| TD-007 | Observability stack | Deferred | DEPLOYMENT.md / ADR |
| TD-008 | Payment gateway strategy | Approved — DEC-007 | `docs/handbook/DEC_007_DECISION_RECORD.md`; `docs/adr/ADR-005-technical-preview-payment-boundary.md`; provider selection deferred |
| TD-009 | AI provider and data boundary | Under Review | SECURITY.md / ADR |
| TD-010 | Deployment Stage 1 runtime requirements | Approved — DEC-009 | `docs/handbook/DEC_009_DECISION_RECORD.md`; `docs/adr/ADR-007-technical-preview-deployment.md`; `DEPLOYMENT.md` |

No framework or vendor is selected merely because it appears in historical candidate material. PHP/Laravel is binding only through the explicit Product Owner substantive DEC-002 decision and Accepted ADR-001 within that exact boundary. Vue 3/Inertia/Vite is binding only through the explicit Product Owner substantive DEC-003 decision and Accepted ADR-002 within that exact boundary. Kotlin/Jetpack Compose and the Hybrid Staged Android direction are binding only through the explicit Product Owner substantive DEC-004 decision and ADR-008 within that exact boundary; this does not create Android implementation authority. Historical DEC-005 selected MySQL Server and shared database/shared schema; DEC-005R now owns current relational-engine/portability precedence while preserving the shared-tenancy/isolation principles recorded by DEC-005. MariaDB, MySQL, and PostgreSQL are authorized engine-profile directions subject to qualification, and no schema/SQL/migration/database implementation authority is created. First-party oneQay identity, server-side Web/PWA session, explicit Android/API token boundary, TOTP privileged MFA baseline, passkey evolution, and tenant-aware membership are binding only through substantive DEC-006 and reconciled ADR-004 within that exact boundary; this does not create authentication/package/schema/migration implementation authority. Cash-first + configurable manual/external recorded tenders, explicit operator-recorded/provider-verified evidence semantics, and provider-abstracted future electronic-payment architecture are binding only through substantive DEC-007 and reconciled ADR-005 within that exact boundary; no specific provider, payment implementation, package, schema/SQL/migration, real-money processing, or offline authority is created. Staged / Hybrid Offline Architecture, first-MVP online-authoritative transactions, and future provisional server-validated offline operation semantics are binding only through substantive DEC-008 and reconciled ADR-006 within that exact boundary; no offline source implementation, Android/PWA transactional-offline implementation, queue/local-database technology, schema/SQL/migration, Sprint 14, deployment, release, or production authority is created. Capability-Based Staged / Hybrid Portability, Stage-1 Preview runtime requirements, P1 conditional/not-selected status, P2 fallback class, Build Once / Deploy Trusted Artifact, and the current requirement for an authorized and runtime-qualified relational engine profile under DEC-005R are binding through substantive DEC-009 and reconciled ADR-007 within that exact boundary; no infrastructure procurement/provisioning, source/dependency/schema/SQL/migration implementation, deployment, release, Sprint 14, or production authority is created.

## Environment classes

| Environment | Data policy | Deployment source | Approval |
| --- | --- | --- | --- |
| Local | Synthetic | Developer branch | Tidak untuk produksi |
| Test / CI | Synthetic by default | CI artifact | Otomatis sesuai quality gate |
| Preview | Synthetic or separately approved masked data | Release candidate | Release Manager / governed Preview authority |
| Production | Real classified data only after separate production authority | Signed release artifact | Authorized approver |

Masked-data use requires an approved process and residual-risk review. Raw production/customer/credential/payment-sensitive data must not be copied into non-production merely for convenience. Historical human-facing `Staging` terminology must be mapped to runtime `Preview` and must not create a fifth implicit environment class.

## Dependency policy

- Dependency baru membutuhkan tujuan, owner, license, maintenance status, security review, dan exit strategy.
- Version harus dikunci secara reproducible melalui lockfile.
- Dependency tidak boleh mengakses data, jaringan, atau filesystem melebihi kebutuhan.
- Critical vulnerability memblokir release kecuali exception terdokumentasi dan memiliki expiry.
- Fork permanen dihindari; bila diperlukan wajib memiliki ownership dan upstream strategy.

## Canonical documents

| Dokumen | Otoritas |
| --- | --- |
| README.md | Orientasi proyek |
| PROJECT_MANIFEST.md | Identitas dan status keputusan |
| AI_CONSTITUTION.md | Aturan permanen ChatGPT pada proyek |
| ARCHITECTURE.md | Arsitektur dan boundary |
| ROADMAP.md | Urutan delivery |
| TASKS.md | Backlog operasional |
| CHANGELOG.md | Riwayat perubahan versi |
| `docs/handbook/ENTERPRISE_VISION.md` | Published canonical Enterprise Vision representation, capability map, dan conceptual product evolution; substantive status Approved through GOV-051; no implementation authority implied |
| `docs/handbook/PRODUCT_VISION_AND_DECISION_RIGHTS.md` | Approved Product Vision and Decision Rights through DEC-000; no implementation authority implied |
| `docs/handbook/DEC_000_DECISION_RECORD.md` | DEC-000 substantive Product Owner decision provenance, dispositions, and boundaries |
| `docs/handbook/DEC_001_DECISION_RECORD.md` | DEC-001 substantive Product Owner MVP scope/non-scope decision provenance, approved bounded slice, deferred boundaries, and no-implementation authority |
| `docs/handbook/DEC_002_DECISION_RECORD.md` | DEC-002 substantive Product Owner backend language/application framework decision provenance, approved PHP/Laravel boundary, and no-implementation authority |
| `docs/handbook/DEC_003_DECISION_RECORD.md` | DEC-003 substantive Product Owner frontend/Web-PWA decision provenance, approved Vue/Inertia/Vite boundary, explicit API/mobile independence, PWA guardrails, and no-implementation authority |
| `docs/handbook/DEC_004_DECISION_RECORD.md` | DEC-004 substantive Product Owner Android delivery decision provenance, Hybrid Staged Approach, Kotlin/Jetpack Compose direction, explicit API/device/offline boundaries, and no-implementation authority |
| `docs/handbook/DEC_005_DECISION_RECORD.md` | Approved historical DEC-005 database-engine/physical-tenancy decision provenance; preserves original MySQL Server selection, shared-schema default, tenant-isolation/recoverability boundaries; partially superseded by DEC-005R; no implementation authority |
| `docs/handbook/DEC_005R_DECISION_RECORD.md` | Current Approved Portable Relational Persistence Architecture; engine-neutral Domain/Application, qualified MariaDB/MySQL/PostgreSQL profile direction, Database Portability Contract, cross-engine qualification and DBME architecture directions; no implementation authority |
| `docs/handbook/DEC_006_DECISION_RECORD.md` | DEC-006 substantive Product Owner identity/authentication architecture provenance, Web/PWA session, Android/API token boundary, MFA/passkey/recovery/tenant-membership/federation directions, and no-implementation authority |
| `docs/handbook/DEC_007_DECISION_RECORD.md` | DEC-007 substantive Product Owner payment architecture/compliance provenance, cash/manual/external tender direction, provider abstraction/deferral, evidence/sufficiency/idempotency/refund/settlement/PCI/jurisdiction/offline boundaries, and no-implementation authority |
| `docs/handbook/DEC_008_DECISION_RECORD.md` | DEC-008 substantive Product Owner offline architecture provenance, Staged / Hybrid Offline Architecture, online-authoritative first MVP, future provisional server-validated operations, replay/conflict/security/reconciliation boundaries, and no-implementation authority |
| `docs/handbook/DEC_009_DECISION_RECORD.md` | DEC-009 substantive Product Owner Stage-1 runtime provenance; capability-based portability model, Preview environment, P1 conditional/not-selected status, P2 fallback class; current database dependency reconciled to DEC-005R qualified engine profiles; no-deployment/no-implementation authority |
| `docs/handbook/DEC_011_DECISION_RECORD.md` | DEC-011 substantive Product Owner privacy/retention/jurisdiction architecture provenance, bounded privacy-by-design, hybrid bounded retention, jurisdiction-profile architecture, initial jurisdiction not yet canonically selected, qualified-legal-review boundary, and no-implementation authority |
| `docs/adr/ADR-001-technical-preview-backend.md` | Accepted representation of DEC-002 with preserved Technical Preview provenance and framework-independence guardrails |
| `docs/adr/ADR-002-technical-preview-frontend-pwa.md` | Accepted representation of DEC-003 with preserved F1 Technical Preview provenance, explicit API/mobile boundaries, PWA/offline guardrails, and implementation-authority separation |
| `docs/adr/ADR-003-technical-preview-database-tenancy.md` | Accepted database-tenancy representation preserving historical D1 and DEC-005 MySQL/shared-tenancy provenance; current relational-engine precedence reconciled to DEC-005R; no schema/SQL/migration authority |
| `docs/adr/ADR-004-technical-preview-authentication.md` | Accepted representation of DEC-006 after publication; preserves A1 Technical Preview provenance while establishing hybrid Web/PWA session + Android/API authentication, privileged MFA, recovery/JRN-003, tenant-aware identity, and no implementation authority |
| `docs/adr/ADR-005-technical-preview-payment-boundary.md` | Accepted representation of DEC-007 after governed publication; preserves historical PAY-1 synthetic cash-only provenance while establishing cash-first + manual/external recorded tenders and provider-abstracted future electronic-payment architecture with no implementation/provider-selection authority |
| `docs/adr/ADR-006-technical-preview-offline-boundary.md` | Accepted representation of DEC-008 after governed publication; preserves historical OFF-1 online-only Technical Preview provenance while establishing staged/hybrid offline architecture and no offline implementation authority |
| `docs/adr/ADR-007-technical-preview-deployment.md` | Accepted representation of DEC-009; preserves historical P1/P2 Technical Preview provenance, current database dependency reconciled to DEC-005R qualified engine profiles, and no deployment authority |
| `docs/adr/ADR-008-android-delivery-approach.md` | Accepted representation of DEC-004 after successful publication; Hybrid Staged Android direction with Kotlin/Jetpack Compose, PWA complementarity, DEC-006/DEC-008 boundaries, and no-implementation authority |
| API_SPEC.md | Governance API |
| DATABASE.md | Governance data dan skema; current Portable Relational Persistence Architecture owned by DEC-005R; shared-tenancy/isolation/recoverability principles preserved from DEC-005 |
| SECURITY.md | Security baseline |
| DEPLOYMENT.md | Operasi dan deployment; Stage-1 capability architecture owned by DEC-009 |
| TESTING.md | Quality strategy |

## Initial risks

| ID | Risiko | Severity | Mitigasi awal |
| --- | --- | --- | --- |
| R-001 | Kebocoran data lintas tenant | Critical | Tenant context enforcement dan isolation tests |
| R-002 | Scope POS/ERP terlalu luas | High | MVP boundary, capability-map semantics, dan phased roadmap |
| R-003 | Ketergantungan shared hosting | High | DEC-009 capability-based portability, P1 evidence gates, P2 fallback, and infrastructure abstraction |
| R-004 | Plugin merusak keamanan/stabilitas | High | Signed package, capability policy, sandbox strategy |
| R-005 | Update gagal dan merusak tenant | Critical | Backup, integrity check, health gate, rollback |
| R-006 | AI memproses data sensitif | High | Data classification, redaction, consent, provider policy |
| R-007 | Enterprise Vision disalahartikan sebagai implementation authority | High | Pisahkan Vision, Capability Map, Architecture Direction, Roadmap, dan explicit Product Owner implementation authority |
| R-008 | Inconsistent product capitalization creates identity drift | Medium | Canonical form `oneQay`; normalize current canonical docs without rewriting immutable history |

## Mandatory update rule

Setiap perubahan resmi minimal memperbarui manifest, task, dan changelog bila status, scope, capability, keputusan, atau risiko proyek berubah. Perubahan arsitektur, API, database, deployment, security, testing, dan UI/UX juga harus memperbarui dokumen pemiliknya.

## Approval

Baseline governance Handbook 1.0 disetujui melalui PR #1. Item berstatus Approved mengikat seluruh pekerjaan berikutnya hanya sesuai scope keputusan masing-masing; item Proposed, Under Review, dan Deferred tidak boleh diperlakukan sebagai keputusan final.

M6 publication lifecycle selesai melalui PR #69. Publication tersebut mengesahkan representasi canonical dan provenance M6, tetapi tidak dengan sendirinya mempromosikan Enterprise Vision dari Proposed menjadi Approved. Product Owner kemudian memberikan keputusan substantif terpisah GOV-051 yang **APPROVED** pada verified repository baseline `762149757e4bc1fa79cc16bc4761f4147be0f7ea` dan canonical artifact blob `bb1cace72a6fdb359e15e22467443d9f3916c336`. Keputusan tersebut mengikat long-term product direction dan tidak memberi Sprint 14, implementation, deployment, release, SQL/migration, production DB, ADR/GD/JRN, atau production-readiness authority.

Product Owner kemudian memberikan substantive DEC-000 Product Vision and Decision Rights decision yang **APPROVED** pada baseline `792b2dc30636bc53baa7d66b43cf2dab4a348dd4`, tree `08f03b895d5e2ae7ca402e9866384990e126add3`, canonical artifact blob `843544b9e31dd4c47638b88dd204f4e594295df4`, dan readiness artifact blob `b493a5d66edc1bbffab0126bdacf2ca1ce14fa8f`. Keputusan tersebut mengesahkan GD-003 hanya dalam boundary Product Vision and Decision Rights, mempertahankan PV-002 melalui PV-006 sebagai Open / Not Resolved, dan tidak memberi Sprint 14, final/business/production implementation, ADR acceptance, GD-007/JRN resolution, SQL/migration, production DB, deployment, release, atau production-readiness authority.

Product Owner kemudian memberikan substantive DEC-001 MVP Scope and Non-Scope decision yang **APPROVED** pada baseline `17f156b9861972b4924a5ed01bfabd5a1a79461a` dan verified tree `33241c18a1b7da2efc7dd2889c13c25c6e8526d5`. Keputusan tersebut menyetujui first bounded MVP slice **POS CORE TRANSACTION & OUTLET OPERATIONS** dan seluruh actor/journey/dependency/non-scope/outcome boundary pada `docs/handbook/DEC_001_DECISION_RECORD.md`, sambil mempertahankan GD-005/GD-006/GD-007 Proposed, JRN-003/JRN-013 Unresolved, Phase 0 In Progress, Sprint 14 Not Authorized, production readiness NO-GO, dan tanpa application/business implementation, SQL/schema/migration, production database, deployment, release, ADR acceptance, atau DEC-002 through DEC-012 approval authority.

Product Owner kemudian memberikan substantive DEC-002 Backend Language / Application Framework decision yang **APPROVED** pada baseline `504b10be44d45dfcfec9b6cfed4f72ed5748b564` dan verified tree `e4622a45f9f298b95358b3d662be3cd48607e4d9`. Keputusan tersebut menyetujui PHP, Laravel, Modular Monolith First + Clean Architecture, framework-independent Domain/Application, dan framework role delivery/composition/infrastructure. DEC-002 direkam di `docs/handbook/DEC_002_DECISION_RECORD.md`, ADR-001 direframe dan Accepted sebagai representasinya, sementara exact runtime/framework version, dependency installation, DEC-003 through DEC-012, Sprint 14, application/business implementation, SQL/schema/migration, production database, deployment, release, dan production readiness tetap tidak diotorisasi.

Product Owner kemudian memberikan substantive DEC-003 Frontend / PWA Stack decision yang **APPROVED** pada baseline `dcb7e3f8de890530a00a0dd4fd310bc10762c72f` dan verified tree `b78d1f1452469a8ba856092e647bef92410f2517`. Keputusan tersebut menyetujui Vue 3, Vue Composition API, TypeScript-first, Inertia sebagai first-party authenticated Web/PWA delivery integration, Vite, local-first state dengan Pinia secara bounded, Modern Monolith Web Delivery + Explicit API Boundaries, UI token-first/accessibility/locale/tenant direction, dan PWA foundation dengan explicit service-worker/cache security boundary. DEC-003 direkam di `docs/handbook/DEC_003_DECISION_RECORD.md`, ADR-002 direframe dan Accepted sebagai representasinya, sementara exact package versions, package manager, `package.json`, lockfile, dependency installation, frontend/backend implementation, offline transaction semantics (DEC-008), Sprint 14, deployment, release, dan production readiness tetap tidak diotorisasi.

Product Owner kemudian memberikan substantive DEC-004 Android Approach decision yang **APPROVED** pada baseline `97b2e5066118af2b3e9467afc71e84dce228eb38` dan verified tree `2f979948184f475b52cf87b2d105c56364ebe883`. Keputusan tersebut menyetujui Hybrid Staged Approach, Native Android dengan Kotlin, Jetpack Compose, PWA complementarity, explicit application/API contracts, native device adapters sebagai Interface/Infrastructure concern, DEC-006 ownership untuk authentication/session, DEC-008 ownership untuk offline semantics, tenant/session-scoped non-authoritative local state, dan Play/enterprise distribution compatibility direction. DEC-004 direkam di `docs/handbook/DEC_004_DECISION_RECORD.md` dan direpresentasikan melalui `docs/adr/ADR-008-android-delivery-approach.md`, sementara Android project/source, Gradle/dependency installation, exact API/auth/storage details, Sprint 14, deployment, release, dan production authority tetap tidak diotorisasi.

Product Owner kemudian memberikan substantive DEC-005 Database Engine and Physical Tenancy Model decision yang **APPROVED** pada baseline `63646e1cccc611a1911c452397059983030dfe66` dan verified tree `80cd3bbf1a0c1d454e73c89f17d8896941f369cd`. Keputusan tersebut menyetujui MySQL Server sebagai canonical relational database engine family, supported MySQL LTS-family boundary dengan exact version deferred, shared database/shared schema sebagai default physical tenancy, immutable tenant isolation key, bounded hybrid evolution path, Application-authoritative tenant authorization dengan database defense-in-depth, database/vendor-specific behavior sebagai Infrastructure concern, compatible/recoverable schema evolution, dan recoverability berbasis verified restoration. DEC-005 direkam di `docs/handbook/DEC_005_DECISION_RECORD.md` dan direpresentasikan melalui reconciled `docs/adr/ADR-003-technical-preview-database-tenancy.md`, sementara schema/SQL/DDL/migration, database implementation, exact runtime/provider/version, Sprint 14, deployment, release, dan production authority tetap tidak diotorisasi.

Product Owner kemudian memberikan substantive DEC-006 Authentication / MFA / Session Architecture decision yang **APPROVED** pada baseline `c495f1b1f6e4e624bf3669ee94a786a1d7e865ce` dan verified tree `795d53f326e6ee2ee52474f79b284dea1ce744da`. Keputusan tersebut menyetujui first-party oneQay platform identity, server-side Web/PWA authentication session, explicit Android/API token authentication boundary, server-authoritative rotation/revocation, TOTP privileged MFA baseline, WebAuthn/passkey compatibility, adaptive password/credential security, high-risk recovery dengan JRN-003 tetap unresolved, global identity + separate tenant memberships, controlled support impersonation, break-glass separation, dan future OIDC federation direction. DEC-006 direkam di `docs/handbook/DEC_006_DECISION_RECORD.md` dan direpresentasikan melalui reconciled `docs/adr/ADR-004-technical-preview-authentication.md`, sementara authentication source implementation, package selection, user/account/membership/session schema, SQL/migration, JRN-003 resolution, Sprint 14, deployment, release, dan production authority tetap tidak diotorisasi.

Product Owner kemudian memberikan substantive DEC-007 Payment Provider and Compliance Boundary decision yang **APPROVED** pada baseline `50955d101c455c6af7356197d9e06d6d76e753bb` dan verified tree `2987eccc6bf4ba8ece23ee2343b178e518a454b3`. Keputusan tersebut menyetujui canonical payment-domain separation, **CASH-FIRST + CONFIGURABLE MANUAL / EXTERNAL RECORDED TENDERS**, operator-recorded versus provider-verified evidence separation, future provider abstraction, provider-selection deferral, authoritative server-side payment evidence, sale-level payment sufficiency, idempotency/replay protection, distinct refund/reversal/dispute and settlement/reconciliation boundaries, restricted-card/payment-credential minimization, exact-money representation, tenant/outlet/merchant context, jurisdiction-neutral architecture, serta DEC-008 offline separation. DEC-007 direkam di `docs/handbook/DEC_007_DECISION_RECORD.md` dan direpresentasikan melalui materially reconciled `docs/adr/ADR-005-technical-preview-payment-boundary.md`, sementara specific provider/account/contract, payment/manual-tender implementation, package/dependency, physical schema, SQL/migration, real-money processing, QR/QRIS/card/wallet implementation, DEC-008 offline authority, Sprint 14, deployment, release, dan production tetap tidak diotorisasi.

Product Owner kemudian memberikan substantive DEC-008 Offline POS Semantics and Conflict Resolution decision yang **APPROVED** pada baseline `21f1b2e938d9f6a42b1dd2ff717c4e049b5119d7` dan verified tree `8cf993f0c82c84bdc46a18aa70c4cb5425b89ac6`. Keputusan tersebut menyetujui Staged / Hybrid Offline Architecture, first-MVP online-authoritative transactions, O1 bounded degraded/read-only direction, future O2 provisional client operations yang membutuhkan server validation/acceptance, stable operation identity, deterministic replay/idempotency, explicit conflict classification/resolution, server-authoritative inventory/payment/tenant/shift correctness, staged PWA/Android capability dengan Native Android sebagai preferred initial future O2 transactional channel, bounded local-data security, causal ordering/reference boundary, reconciliation, failure recovery, audit, dan client-clock boundary. DEC-008 direkam di `docs/handbook/DEC_008_DECISION_RECORD.md` dan direpresentasikan melalui materially reconciled `docs/adr/ADR-006-technical-preview-offline-boundary.md`, sementara offline source implementation, Android/PWA transactional-offline implementation, local database/queue/encryption technology, schema/SQL/migration, Sprint 14, deployment, release, dan production tetap tidak diotorisasi.

Product Owner kemudian memberikan substantive DEC-009 Deployment Stage 1 Runtime Requirements decision yang **APPROVED** pada baseline `0fdc0a53403f16fbc6908630ea350af2c0de466b` dan verified tree `45c0aa49657db8f95ca08e662ec641e6d9d5f25a`. Keputusan tersebut menyetujui Capability-Based Staged / Hybrid Portability Model, P1 cPanel conditional/not selected, P2 managed/hardened VPS/server fallback class, Stage-1 Preview environment, PHP `>=8.2`, Build Once / Deploy Trusted Artifact, canonical MySQL Server under DEC-005, runtime/security/scheduler/worker/session/cache/storage/observability/recovery/release capability requirements, and provider-neutral portability. DEC-009 direkam di `docs/handbook/DEC_009_DECISION_RECORD.md` dan direpresentasikan melalui materially reconciled `docs/adr/ADR-007-technical-preview-deployment.md`, sementara provider procurement, infrastructure provisioning, application/dependency/schema/SQL/migration implementation, deployment, release, Sprint 14, dan production tetap tidak diotorisasi.

Product Owner kemudian memberikan substantive DEC-011 Data Retention, Privacy, and Jurisdiction decision yang **APPROVED** pada baseline `6c6af7f99d25f177c91f92cdd163a277affc5153` dan verified tree `efa336169e902e6bddd7f3fff47a0e91d15b5a19`. Keputusan tersebut menyetujui **Bounded Privacy-by-Design + Hybrid Bounded Retention + Jurisdiction-Profile Architecture**, activity-based privacy roles, server-authoritative data lifecycle, jurisdiction-aware incident/privacy boundaries, synthetic-by-default non-production data, privacy-aware backup/restore, and pre-adoption processor/subprocessor/AI privacy gates. Initial commercial/launch jurisdiction remains **NOT YET CANONICALLY SELECTED**, qualified legal review remains required for jurisdiction-specific implementation, DEC-012 retains final RPO/RTO/service objectives, JRN-003/JRN-013 remain unresolved, and no jurisdiction/provider/hosting-region selection, production/customer-data processing, schema/SQL/migration, source implementation, deployment, release, Sprint 14, or production authority is created.

## Technical Preview v0.0.1 decision package

Issue #23 records the accelerated T+5 planning scope and Product Owner selections. PR #24 was technically merged before this canonical synchronization; that merge does not accept an ADR, approve Phase 0 exit, or grant source-code authority.

| Decision package item | Candidate selection | Status | Evidence/gate |
| --- | --- | --- | --- |
| Backend | B1 Laravel/PHP modular monolith | Accepted via DEC-002 | Historical Technical Preview provenance; substantive authority is DEC-002 and reconciled ADR-001 |
| Frontend/PWA | F1 Vue 3 + Inertia + Vite | Accepted via DEC-003 | Historical Technical Preview provenance; substantive authority is DEC-003 and reconciled ADR-002; TypeScript-first, explicit API/mobile boundaries, and bounded PWA direction apply |
| Database/tenancy | D1 MySQL-compatible shared schema | Accepted via DEC-005; current precedence DEC-005R | Historical D1 and DEC-005 provenance retained; current portable relational precedence is DEC-005R; shared database/shared schema and tenant-isolation principles remain preserved; selected runtime engine profile remains separately qualified |
| Authentication | A1 first-party session and privileged TOTP | Accepted via DEC-006 | Historical A1 provenance retained; current canonical DEC-006 adds first-party identity, Web/PWA server-side session, Android/API token boundary, privileged TOTP baseline, passkey evolution, tenant memberships, and high-risk recovery while JRN-003 remains unresolved; implementation separately gated |
| Payment preview | PAY-1 synthetic cash-only | Accepted via DEC-007 | Historical PAY-1 provenance retained; current DEC-007 direction is cash-first + configurable manual/external recorded tenders with future provider abstraction; provider selection and implementation remain separately gated |
| Offline preview | OFF-1 online-only | Accepted via DEC-008 | Historical OFF-1 provenance retained; current DEC-008 direction is Staged / Hybrid Offline Architecture with first-MVP online-authoritative transactions and future provisional server-validated operations; implementation separately gated |
| Deployment | P1 cPanel conditional; P2 fallback hypothesis | Accepted via DEC-009 / P1 Not Selected | Historical P1/P2 provenance retained; current DEC-009 uses capability-based Stage-1 Preview architecture and its database dependency is reconciled to DEC-005R qualified engine profiles; P1 remains conditional/not selected and P2 is fallback execution class; no deployment authority |
| Tenant boundary | TEN-1 two synthetic tenants | Proposed | Isolation evidence pending |
| Recovery | REC-1 provisional RPO 24h/RTO 4h | Proposed | Capability and rehearsal pending |
| Preview SLO | SLO-1 | Proposed | Measurement evidence pending |
| Data boundary | DATA-1 synthetic only | Proposed | Data baseline exact-head approval pending |

Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. GD-007 and Domain Event Storming remain **Proposed**. JRN-003 and JRN-013 remain unresolved blockers. P1 remains conditional/not selected; partial hosting evidence is recorded but missing mandatory capabilities must not be inferred.

Published bounded Platform Foundation work through Sprint 12 and Sprint 13 is preserved separately from this unresolved Technical Preview decision package and does not promote any item in this package except where a later explicit substantive decision, such as DEC-002, DEC-003, DEC-005, DEC-005R, DEC-006, DEC-007, DEC-008, or DEC-009, independently changes that item's status.

## PR #25 and Issue #23 governance recurrence

PR #25 was created from base `a3efdd17e69590bd4aaf60c0f9da3ecf6773e31f` at exact head `ca2157096b310b114203d919cb8182e55a6fa5f9`. Its recorded lifecycle authority was draft creation only, but it was changed from draft and technically merged as `93c8b8d4d8dae399c0d3f758c50460cf086e2322` without available separate exact-head lifecycle authority.

Read-only evidence for the PR #25 exact head shows no review submission, PR conversation comment, published commit status, or GitHub Actions workflow run. The local validation statements recorded in the PR body remain distinct from independent GitHub check evidence and do not supply lifecycle authority.

Issue #23 was closed with reason `completed` before its evidence, hosting, ADR acceptance, recovery, Technical Preview acceptance, and Phase 0 preview-exit conditions were complete. That closure is a technical repository state only and is not completion evidence.

The PR #25 technical merge and Issue #23 closure do not constitute substantive approval, ADR acceptance, Phase 0 exit, source-code authority, ratification, or completion evidence. Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. JRN-003 and JRN-013 remain unresolved. Hosting evidence not supplied remains Pending, Not supplied, or Unverified.

## PR #26 post-merge governance recurrence

PR #26 was created from original base `93c8b8d4d8dae399c0d3f758c50460cf086e2322` at exact head `63223b9b856bd67e739651a1e23cc071971998c3`. Its body limited lifecycle authority to draft creation and required the PR to remain draft, but it was changed from draft and technically merged as `294fe24381e88b61701868567cda4be532640ab0` without available separate exact-head ready or merge authority.

Read-only evidence for the PR #26 exact head shows no review submission, PR conversation comment, published commit status, or GitHub Actions workflow run. The local and static validation statements in the PR body remain distinct from independent GitHub check evidence and do not supply lifecycle authority.

The Product Owner issued a post-merge content decision approving only the accuracy of the three-file corrective content on PR #26 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #26.

The PR #26 technical merge does not ratify PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034 or GOV-035, or provide completion evidence. Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #27 post-merge governance recurrence

PR #27 was created from original base `294fe24381e88b61701868567cda4be532640ab0` at exact head `c6adb55a9a6cd2ebedd78668ccaf5fd64c041d94`. Its body limited lifecycle authority to draft creation and required the PR to remain draft, but it was changed from draft and technically merged as `3c4bcfe9797a3ae7f4deb124568ef361d74125e5` without available separate exact-head ready or merge authority.

Read-only evidence for the PR #27 exact head shows no review submission, PR conversation comment, published commit status, or GitHub Actions workflow run. The local and static validation statements in the PR body remain distinct from independent GitHub check evidence and do not supply lifecycle authority.

The Product Owner issued a post-merge exact-head content decision approving only the accuracy of the three-file corrective content on PR #27 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #27.

The PR #27 technical merge does not ratify PR #26 or PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034, GOV-035, or GOV-036, or provide substantive approval or completion evidence. Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional dan **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034, GOV-035, and GOV-036 remain **Review**. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #28 post-merge governance recurrence

PR #28 was created from original base `3c4bcfe9797a3ae7f4deb124568ef361d74125e5` at exact head `0597d784f63cf6d5967cedae17ca8d0b5a2e4dc9`. Its body limited lifecycle authority to draft creation and required the PR to remain draft, but it was changed from draft and technically merged as `1009af84ec0ee7d7731890e379dde25279280c3a` without available separate exact-head ready or merge authority.

Read-only evidence for the PR #28 exact head shows no review submission, PR conversation comment, published commit status, or GitHub Actions workflow run. The local and static validation statements in the PR body remain distinct from independent GitHub check evidence and do not supply lifecycle authority.

The Product Owner issued a post-merge exact-head decision approving only the accuracy of the three-file corrective content on PR #28 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #28.

The PR #28 technical merge does not ratify PR #27, PR #26, or PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034, GOV-035, GOV-036, or GOV-037, or provide substantive approval or completion evidence. Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034, GOV-035, GOV-036, and GOV-037 remain **Review**. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #29 post-merge governance recurrence

PR #29 was created from original base `1009af84ec0ee7d7731890e379dde25279280c3a` at exact head `54a5773c3ab65a33e35ef2646089727490a0ff8d`. Its body required the PR to remain draft, but it was changed from draft and technically merged as `f55d86f1a3d89a6bcbbbcf7800851b9c61f8c047`.

A repository-native operational authority comment was present on PR #29 and explicitly authorized branch creation, the three corrective Markdown changes, draft PR creation, comments, review submissions, and separately scoped issue actions. That authority explicitly excluded draft-to-ready transition, merge or auto-merge, ADR acceptance, Phase 0 preview exit, source-code implementation, Issue #23 state change, hosting-evidence completion, governance-task completion, ratification, release, deployment, and status promotion.

Read-only evidence for the PR #29 exact head shows no separate exact-head ready authority, no separate exact-head merge authority, no review submission, no published commit status, and no GitHub Actions workflow run. The operational authority comment and local/static validation statements do not supply lifecycle authority.

The Product Owner issued a post-merge exact-head content decision approving only the accuracy of PR #29 three-file corrective content, without retrospective lifecycle authority or ratification of PR #29 lifecycle action.

The PR #29 technical merge does not ratify PR #28, PR #27, PR #26, or PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034, GOV-035, GOV-036, GOV-037, or GOV-038, or provide substantive approval or completion evidence. Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034, GOV-035, GOV-036, GOV-037, and GOV-038 remain **Review**. JRN-003 dan JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #30 post-merge governance recurrence

PR #30 was created from original base `f55d86f1a3d89a6bcbbbcf7800851b9c61f8c047` at exact head `f3703650f98e5d6abfdb21d9b67ac7c5567ea9f6`. Its body required the PR to remain draft, but it was changed from draft and technically merged as `54bc51a7a150394748dcc5f6a2fb8e376206feba`.

A repository-native operational authority comment was present on PR #30 and explicitly authorized current-main verification, corrective branch creation, the three corrective Markdown changes, draft PR creation, the authority comment, and read-only checks. That authority explicitly excluded draft-to-ready transition, merge or auto-merge, approval review, ADR acceptance, Phase 0 preview exit, source-code implementation, Issue #23 state change, governance-task completion, release, deployment, and status promotion.

Read-only evidence for the PR #30 exact head shows no separate exact-head ready authority, no separate exact-head merge authority, no review submission, no review thread, no published commit status, or GitHub Actions workflow run. The operational authority comment and local/static validation statements do not supply lifecycle authority.

The Product Owner issued a post-merge exact-head decision approving only the accuracy of the three-file corrective content on PR #30 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #30.

The PR #30 technical merge does not ratify PR #29, PR #28, PR #27, PR #26, or PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034, GOV-035, GOV-036, GOV-037, GOV-038, or GOV-039, or provide substantive approval or completion evidence. Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034, GOV-035, GOV-036, GOV-037, GOV-038, and GOV-039 remain **Review**. JRN-003 dan JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #31 post-merge governance recurrence

PR #31 was created from original base `54bc51a7a150394748dcc5f6a2fb8e376206feba` at exact head `10b5179b16c104e1877153b066e96a937ece9c9b`. Its body required the PR to remain draft, but it was changed from draft and technically merged as `67059e563de26cee26fd64cf9e7d5c4436ffc6`.

A repository-native operational authority comment was present on PR #31 and explicitly authorized current-main verification, corrective branch creation, the three corrective Markdown changes, adding GOV-039 as Review, draft PR creation, the authority comment, and read-only checks. That authority explicitly excluded draft-to-ready transition, merge or auto-merge, approval review, ADR acceptance, Phase 0 preview exit, source-code implementation, Issue #23 state change, hosting-evidence completion, governance-task completion, ratification, release, deployment, and status promotion.

Read-only evidence for the PR #31 exact head shows no separate exact-head ready authority, no separate exact-head merge authority, no review submission, no review thread, no published commit status, or GitHub Actions workflow run. The operational authority comment and local/static validation statements do not supply lifecycle authority.

The Product Owner issued a post-merge exact-head decision approving only the accuracy of the three-file corrective content on PR #31 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #31.

The PR #31 technical merge does not ratify PR #30, PR #29, PR #28, PR #27, PR #26, or PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034, GOV-035, GOV-036, GOV-037, GOV-038, GOV-039, or GOV-040, or provide substantive approval or completion evidence. Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034, GOV-035, GOV-036, GOV-037, GOV-038, GOV-039, and GOV-040 remain **Review**. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #32 post-merge governance recurrence

PR #32 was created from original base `67059e563de26cee26fd64cf9e7d5c4436ffc6` at exact head `beb7b35aa718a746ad5dad9d5574c2293bd0ab40`. Its body required the PR to remain draft, but it was changed from draft and technically merged as `d1a6160b37250bda691e906fc4ee06e37dd0c847` without available separate exact-head ready or merge authority.

A repository-native operational authority comment was present on PR #32 and explicitly authorized current-main verification, corrective branch creation, the three corrective Markdown changes, adding GOV-040 as Review, draft PR creation, the authority comment, and read-only checks. That authority explicitly excluded draft-to-ready transition, merge or auto-merge, approval review and branch-protection/ruleset changes, ADR acceptance, Phase 0 exit, source-code implementation, Issue #23 state change, hosting-evidence completion, governance-task completion, ratification, release, deployment, and status promotion.

Read-only evidence for the PR #32 exact head shows no separate exact-head ready authority, no separate exact-head merge authority, no review submission, no review thread, no published commit status, or GitHub Actions workflow run. The operational authority comment and local/static validation statements do not supply lifecycle authority.

The Product Owner issued a post-merge exact-head decision approving only the accuracy of the three-file corrective content on PR #32 exact head. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #32.

The PR #32 technical merge does not ratify PR #31, PR #30, PR #29, PR #28, PR #27, PR #26, or PR #25, validate or complete the closure of Issue #23, accept ADR-001 through ADR-007, approve Phase 0 exit, grant source-code authority, complete GOV-034, GOV-035, GOV-036, GOV-037, GOV-038, GOV-039, GOV-040, or GOV-041, or provide substantive approval or completion evidence. Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034, GOV-035, GOV-036, GOV-037, GOV-038, GOV-039, GOV-040, and GOV-041 remain **Review**. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## PR #33 post-merge governance recurrence and containment

PR #33 was created from original base `d1a6160b37250bda691e906fc4ee06e37dd0c847` at exact head `28c776abf6ab7832dbdf61ea49203c6e9c13a55c`. Its body required the PR to remain draft, but it was changed from draft and technically merged as `68df196efdf38919d73a6b6345b973d2c3698b29`.

The Product Owner issued a post-merge exact-head content decision approving only the accuracy of the three-file corrective content on PR #33. That decision does not provide retrospective lifecycle authority and does not ratify the draft-to-ready transition or merge of PR #33.

A read-only repository-control incident investigation attributed the PR #25 through PR #33 merge commits to Git author `labzefry` with GitHub-hosted committer `web-flow`, while account security-log, token, OAuth, session, IP, and user-agent evidence remained unavailable through the connector. The recurrence mechanism was assessed as a GitHub web/API path operating with repository-owner authority rather than a GitHub Actions workflow.

Repository Owner containment established the active `main-protected-governance` ruleset on the public repository with an empty bypass list, required pull request, one independent approval, stale-approval dismissal, latest-reviewable-push approval, conversation resolution, required status checks, deletion restriction, and force-push blocking.

Sentinel PR #34 used exact head `be4182a7f918da043e71fe9af3626a1bb027372b`. Its first approval by `@zefriansyah` was automatically **DISMISSED** after a new push. A new independent latest-head approval was then recorded as **APPROVED**. Required checks `governance-validation`, `markdown-lint`, and `secret-scan` completed successfully. PR #34 was closed without merge, and `main` remained at `68df196efdf38919d73a6b6345b973d2c3698b29`.

This effectiveness evidence contains the corrective PR workflow but does not ratify PR #25 through PR #33, validate Issue #23 closure, accept ADR-001 through ADR-007, approve Phase 0 preview exit, grant source-code authority, complete GOV-034 through GOV-042, release, deploy, or promote any status.

Phase 0 remains **In Progress**. Final/business application implementation remains **Blocked**. Phase 0 preview exit remains **Not Ready**. P1 remains conditional and **Unverified**. ADR-001 through ADR-007, GD-007, PAY-1, OFF-1, TEN-1, REC-1, SLO-1, and DATA-1 remain **Proposed** selections or boundaries. GOV-034 through GOV-042 remain **Review**. JRN-003 and JRN-013 remain unresolved. Hosting evidence remains Pending, Not supplied, or Unverified.

## M5/M6 canonical stabilization state

- M5.1 — Canonical State Reconciliation: **PUBLISHED / COMPLETE** through PR #66, published commit `153a33a4a2b5edb4a31285eca7d3491f9589b778`.
- M5.2 — CI & Lifecycle Control Hardening: **PUBLISHED / ENFORCEMENT COMPLETE** through PR #67, published commit `512344d0497787c729242cb1fd2d7d02ecfc40c2`, published tree `0f0af1c1acab208c704fbdf05b19014127abddbb`.
- M5.3 — Governance & Program State Synchronization: **PUBLISHED / COMPLETE** through PR #68, source head `aa799e657070a7d3283110a73a411f54a73b972c`, published commit `e45f5b4c0f143abc6e255e4e8550bf3504348aae`, source/published tree `e2bc0505f5abd98a7283b3cd3cd2c4c02ef23ece`.
- A-03 — Lifecycle Authority Not Enforced: **Resolved**.
- A-05 — PHP Regression Not in GitHub CI: **Resolved**.
- A-06 — Phase 0 Semantic Ambiguity: **Resolved through M5.3 publication**.
- A-07 — ROADMAP / TASKS Out of Sync: **Resolved through M5.3 publication**.
- A-08 — AI-specific Product Metadata / Attribution: **Resolved through M5.3 publication**.
- Active protected contexts: `governance-validation`, `markdown-lint`, `secret-scan`, `php-foundation-regression`, and `product-owner-merge-authority`.
- M6 — Enterprise Vision Canonicalization: **PUBLISHED / PUBLICATION COMPLETE** through PR #69; substantive Enterprise Vision decision is **APPROVED** through GOV-051.
- A-09 — Enterprise Vision Not Yet Canonical: **Resolved at canonical representation/publication level through PR #69**; separate substantive Enterprise Vision decision **APPROVED** through GOV-051.
- A-10 — Product-name capitalization inconsistency: **Resolved for current/future-facing canonical material through PR #69**; canonical form is `oneQay` and immutable historical evidence remains preserved.
- DEC-000 — Product Vision and Decision Rights: **APPROVED / DECISION COMPLETE**; GD-003 Approved within DEC-000 boundary; PV-002 through PV-006 remain Open / Not Resolved.
- DEC-001 — MVP Scope and Non-Scope: **APPROVED / DECISION COMPLETE**; first bounded delivery slice is **POS CORE TRANSACTION & OUTLET OPERATIONS**; no implementation authority granted.
- DEC-002 — Backend Language / Application Framework: **APPROVED / DECISION COMPLETE**; PHP + Laravel with Modular Monolith First + Clean Architecture and framework-independent Domain/Application; ADR-001 Accepted after reconciliation; no implementation authority granted.
- DEC-003 — Frontend / PWA Stack: **APPROVED / DECISION COMPLETE**; Vue 3 + Inertia + Vite with TypeScript-first, Modern Monolith Web Delivery + Explicit API Boundaries, local-first state, and bounded PWA direction; ADR-002 Accepted after reconciliation; no implementation authority granted.
- DEC-004 — Android Approach: **APPROVED / DECISION COMPLETE**; Hybrid Staged Approach with Native Android using Kotlin + Jetpack Compose, PWA complementarity, explicit API/device boundaries, DEC-006 authentication boundary, DEC-008 offline boundary, and no implementation authority granted.
- DEC-005 — Database Engine and Physical Tenancy Model: **APPROVED HISTORICAL DECISION / PARTIALLY SUPERSEDED BY DEC-005R**; original MySQL Server selection and shared database/shared schema default remain historical provenance; applicable shared-tenancy/isolation/recoverability principles remain preserved; no database/schema/SQL/migration implementation authority granted.
- DEC-005R — Portable Relational Persistence Architecture: **APPROVED / DECISION COMPLETE / PUBLISHED through PR #100**; database-engine-neutral Domain/Application, zero database-vendor dependency in business rules, qualified MariaDB/MySQL/PostgreSQL engine-profile direction, Database Portability Contract, cross-engine qualification/CI and DBME architecture directions; no implementation authority granted.
- DEC-006 — Authentication / MFA / Session Architecture: **APPROVED / DECISION COMPLETE**; first-party oneQay identity, Web/PWA server-side session, Android/API token boundary, server-authoritative revocation, TOTP privileged MFA, passkey evolution, tenant-aware memberships, recovery/JRN-003 boundary, reconciled ADR-004, and no authentication/package/schema/migration implementation authority granted.
- DEC-007 — Payment Provider and Compliance Boundary: **APPROVED / DECISION COMPLETE**; cash-first + configurable manual/external recorded tenders, operator-recorded/provider-verified evidence separation, provider-abstracted future electronic-payment direction, provider selection deferred, sale-level sufficiency, idempotency/refund/settlement/PCI/jurisdiction/offline boundaries, reconciled ADR-005, and no payment/provider/package/schema/SQL/migration implementation authority granted.
- DEC-008 — Offline POS Semantics and Conflict Resolution: **APPROVED / DECISION COMPLETE**; Staged / Hybrid Offline Architecture, first-MVP online-authoritative transactions, future provisional server-validated offline operation direction, replay/idempotency/conflict/security/reconciliation boundaries, reconciled ADR-006, and no offline/source/schema/package implementation authority granted.
- DEC-009 — Deployment Stage 1 Runtime Requirements: **APPROVED / DECISION COMPLETE**; Capability-Based Staged / Hybrid Portability Model, Stage-1 Preview, P1 conditional/not selected, P2 fallback execution class, PHP/runtime/build/scheduler/session/cache/storage/security/observability/recovery/release boundaries, current database dependency reconciled to an authorized and runtime-qualified relational engine profile under DEC-005R, reconciled ADR-007, and no deployment/implementation authority granted.
- DEC-011 — Data Retention, Privacy, and Jurisdiction: **APPROVED / DECISION COMPLETE**; Bounded Privacy-by-Design + Hybrid Bounded Retention + Jurisdiction-Profile Architecture; initial jurisdiction NOT YET CANONICALLY SELECTED; qualified legal review required; DEC-012 retains final RPO/RTO/service-objective ownership; no implementation/jurisdiction/provider/deployment authority granted.
- Phase 0 remains **In Progress**.
- Sprint 14 remains **Not Authorized**.
- Production readiness remains **NO-GO**.
- No final/business/production implementation, Android project/source, PWA transactional-offline implementation, database/schema/SQL/migration implementation, authentication implementation, payment/provider/manual-tender implementation, offline transaction/synchronization implementation, local database/queue technology, package/dependency installation, real-money processing, production/customer-data processing, jurisdiction/provider/hosting-region selection, infrastructure provisioning, hosting procurement, cross-engine CI implementation, DBME implementation, deployment, release, SQL execution, migration execution, or production database modification is authorized by GOV-051, DEC-000, DEC-001, DEC-002, DEC-003, DEC-004, DEC-005, DEC-005R, DEC-006, DEC-007, DEC-008, DEC-009, or DEC-011.

## DEC-012 canonical recovery and support state

- Status: **APPROVED / DECISION COMPLETE**.
- Decision baseline: `a7821517a03cf868adf56bfa7d91c878d8c364ac`; verified baseline tree: `aa81d2f071725abc91f2cf9f71a2498832e47cd2`.
- Direction: **CAPABILITY-TIERED / EVIDENCE-BASED RECOVERY & SUPPORT POLICY**.
- Final numerical Production RPO, final numerical Production RTO, final numerical Production SLO, and customer-contractual SLA remain **NOT APPROVED / DEFERRED**.
- Recovery verification remains **EVIDENCE-GATED**; backup success alone is not verified recoverability.
- Historical REC-1 RPO 24h/RTO 4h and SLO-1 remain Technical Preview provenance only and are not promoted into Production commitments.
- Canonical record: `docs/handbook/DEC_012_DECISION_RECORD.md`.
- DEC-012 grants no backup/restore/DR implementation, provider/cloud/region/HA selection, infrastructure provisioning, source/schema/SQL/migration implementation, deployment, release, Production, Phase 0 exit, or Sprint 14 authority; JRN-003/JRN-013 remain unresolved and GD-007 remains Proposed.

Attribution: Lab | zefry

## DEC-010 Supplement canonical dependency and visualization state

- Status: **APPROVED / DECISION COMPLETE**.
- Decision baseline: `4a56cad359ab5e4e59b7a5b3e342869339c8f6a8`; verified baseline tree: `205bcbdbb746e9959a3d18b90b5992c303033192`.
- D10S-01: **ZERO MANDATORY COMMERCIAL SOFTWARE-LICENSE COST — CORE BASELINE**; this is not a zero-total-cost claim.
- D10S-02: **FREE / OPEN-SOURCE FIRST PREFERENCE — NOT FOSS-ONLY**; DEC-010 license classes remain binding and commercial/proprietary exceptions remain separately gated.
- D10S-03: **APACHE ECHARTS — DEFAULT WEB/PWA VISUALIZATION TECHNOLOGY CANDIDATE / APPROVED TECHNOLOGY DIRECTION**; not mandatory/exclusive and no exact package/version is adopted.
- D10S-04 preserves **Technology Policy Approval ≠ Dependency Adoption Authority ≠ Implementation Authority**.
- Canonical supplement record: `docs/handbook/DEC_010_SUPPLEMENT_DECISION_RECORD.md`.
- oneQay product/source remains **PROPRIETARY / ALL RIGHTS RESERVED**; DEC-003 frontend boundaries remain preserved; no package manager, `package.json`, frontend lockfile, dependency installation, chart/dashboard/source implementation, deployment, release, Production, Phase 0 exit, or Sprint 14 authority is created.

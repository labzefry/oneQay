# oneQay Roadmap

## Canonical post-Sprint46 JRN-006 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint45/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `437e463d4e862b1a1ba26cd500ea7ec23e352878`; tree `05fa7b8b6fa1cb95ef45728824c855ded43e205a`; GitHub signature **verified / valid**.
- Sprint46 JRN-006 **POS Sale Completion / Payment Recording / Receipt Foundation** is **IMPLEMENTED / PUBLISHED** through PR #419 as `437e463d4e862b1a1ba26cd500ea7ec23e352878`, from qualified exact source head `22b5ea04ad9abc742ed0c14b5c18cd0d00b57446`.
- The published JRN-006 source envelope remains exactly **14 paths** with sorted newline-terminated SHA-256 `ed29b6128c193f0efd6359748e220a37aefaec856acc4bc3b90f445ce3ccb674`.
- Canonical source now contains migrations exactly **#1–#16**. Migration #16 is **SOURCE-PUBLISHED / SELECTED IN SOURCE DESIGN / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**. This reconciliation grants no migration execution authority.
- JRN-006 implements only tenant/outlet-scoped sale completion, bounded payment recording, and deterministic receipt evidence. It does not complete the broader POS MVP and does not add catalog administration, shift/register lifecycle, void/refund/return, purchasing, supplier lifecycle, CRM, accounting, external payment providers, offline POS, or broad reporting.
- Runtime authority is reconstructed from the current first-party server session as verified tenant, identity, organization, outlet, and device context. The caller cannot select tenant, organization, outlet, device, actor, role, permission, session authority, product price, stock quantity, or sale identity.
- `pos.sale.complete` is required through the existing durable scoped authorization policy. No default grant is created.
- Server-owned durable catalog state supplies price and availability. Sale, line snapshots, stock decrement, payment/receipt evidence, and sale-event evidence remain tenant+outlet scoped and execute inside the canonical persistence transaction.
- Exact `tenant_id + operation_id` is the durable idempotency boundary. The semantic fingerprint binds actor/context, cart, tender category, and tendered amount. Exact replay returns the original receipt without a second stock decrement; conflicting replay fails closed.
- `ONEQAY_POS_SALE_COMPLETION_ENABLED` remains **default false**. The HTTP boundary is created only for **Local/Test/CI** when session control is enabled and the feature is explicitly armed. Technical Preview and Production remain unactivated.
- The final Sprint46 exact source head completed **36 materially triggered pull-request workflows / 36 success / 0 non-success**, including the dedicated Sprint46 JRN-006 regression, M7.2, M7.3, M7.4, M7.4A, M7.5, Sprint21–Sprint43 preservation, Governance, PHP Foundation, and updater/deployment-control regressions.
- Exact Product Owner merge authorization for PR #419 was recorded for head `22b5ea04ad9abc742ed0c14b5c18cd0d00b57446`; the post-authorization `issue_comment` evaluator completed **success** before squash publication.
- The bounded Sprint46 publication chain includes Business MVP entry gate PR #397, JRN-006 schema/source gate PR #399, bounded compatibility publications through PR #401–#418 as required by exact failure evidence, and final source PR #419. Closed or superseded unmerged probes do not constitute canonical authority.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**. Migration #15 and migration #16 remain unapplied/unactivated in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment, release, migration execution, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint46 successor implementation concern is selected by this reconciliation. Any Sprint47 or other successor concern must begin with a separately bounded Product Owner entry gate; Preview/Production activation, updater wiring, deployment, release, migration execution, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


## Canonical post-Sprint45 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint44/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `031d2379565a9b5cb5f1e6bc9e02957f8291206d`; tree `4beda30c01ffcc3f371c1460fc2caaa8fe4adea0`; GitHub signature **verified / valid**.
- Sprint45 **First-Party Pending MFA Identity Eligibility Revalidation Foundation** is **IMPLEMENTED / PUBLISHED** through PR #387 as `031d2379565a9b5cb5f1e6bc9e02957f8291206d`, from qualified exact source head `0f1e7db2193254171ac2ac3794ec0a8fd5a5140e`.
- Sprint45 source remains exactly **4 paths** with sorted newline-terminated SHA-256 `5dfaecf9be5c584b431606a7253515ab623ad9a11b4ff74062e794a1f40917c7`.
- Canonical source migrations remain exactly **#1–#15**. Sprint45 is **NO_SCHEMA_CHANGE**; migration #16 is **NOT SELECTED** and does not exist.
- Sprint45 revalidates current authentication eligibility for the exact pending tenant+identity before privileged pending-MFA enrollment start, enrollment confirmation, or challenge completion may advance authentication state.
- If the pending identity is disabled or otherwise ineligible, the pending framework authentication state is invalidated fail closed and the CSRF token is regenerated. Later reactivation cannot resume or restore that burned pending state; canonical fresh primary authentication remains mandatory.
- Sprint45 does not create a reactivation-login path, does not restore old framework/logical session authority, does not reuse historical public session handles or authority identifiers, and does not weaken Sprint41–Sprint44 disablement, termination, reactivation, or fresh-authentication no-resurrection semantics.
- Tenant, identity, organization, outlet, device, credential/factor epoch, privileged-factor, and current eligibility boundaries remain server-derived and deny-by-default. Caller-selected tenant/role/permission/session authority is not introduced.
- Sprint45 creates no credential, TOTP/recovery-factor, role, permission, grant, membership, organization/outlet/device, protected-control, deployment, release, updater, or migration-execution authority.
- The final Sprint45 exact source head completed **19 materially triggered pull-request workflows / 19 success / 0 non-success**, and the exact-head `product-owner-merge-authority` status completed **success** before squash publication.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; migration #15 and Sprint42/Sprint43/Sprint44/Sprint45 source remain unactivated/unapplied in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment, release, migration execution, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint45 successor implementation concern is selected by this reconciliation. Any Sprint46 or other successor concern must begin with a separately bounded Product Owner entry gate; migration #16, new source/schema/runtime authority, Preview/Production activation, updater wiring, deployment, release, migration execution, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**

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


## Canonical Sprint40 pre-source roadmap checkpoint — 2026-08-25

For current roadmap sequencing, this checkpoint supersedes older current-facing next-work statements retained below as historical provenance.

- Sprint 21 through Sprint 39 governed identity/control foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** is the selected governed concern.
- Entry-gate PR #268 and schema/source-envelope gate PR #270 are **PUBLISHED**.
- Source-preservation predecessor PR #271 and its compatibility prerequisites PR #272/#273 are **PUBLISHED**.
- Documentation-synchronization preservation predecessor PR #274 is **PUBLISHED** as `7be563d66e2f8441cf28dd2ed9ce5d6c0704098f` / tree `adbbce29218e312b243076dc3ee984e68ce79b65`.
- The present roadmap stage is the exact **13-document canonical synchronization** with fingerprint `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.
- Canonical source migrations remain exactly **#1–#13**. Migration #14 is selected for the later Sprint40 source stage but does not yet exist or apply on canonical `main`.
- The next logical stage after this documentation synchronization is the already-frozen exact **8-path Sprint40 source implementation**, fingerprint `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`, subject to a fresh canonical-main check and separate source authority.
- Technical Preview remains **`NO_SCHEMA_CHANGE` / Sprint40 not activated**; Production remains **`NO-GO / NOT AUTHORIZED`**; updater remains **`DISABLED / UNWIRED`**; deployment and release remain **NOT AUTHORIZED**.
- This documentation checkpoint does not itself implement Sprint40 source, create or execute migration #14, change runtime behavior, or grant Preview/Production authority.

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

## Canonical post-Sprint 28 roadmap reconciliation — 2026-08-18

For current roadmap and next-work interpretation, this section supersedes every older current-facing M7.5/updater milestone statement retained below as historical planning provenance.

- M7.5 Preview Runtime Qualification is **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**.
- Governed successor foundations through Sprint 28 are also published: Sprint 21 role/permission policy; Sprint 22 policy administration; Sprint 23 initial tenant-administrator provisioning; Sprint 24 protected-control administrator lifecycle; Sprint 25 policy-administration delivery; Sprint 26 first-party credential verification; Sprint 27 first-party login/session establishment; Sprint 28 first-party initial password enrollment.
- Sprint 28 is **COMPLETE / IMPLEMENTED / PUBLISHED** through source PR #188 and post-publication reconciliation PR #189.
- Canonical migrations are exactly **#1–#8**. Migrations #1–#7 remain immutable; migration #8 is additive and forward-only.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Persistence remains default-disabled.
- The next logical governed identity milestone is **First-Control-Principal Bootstrap Credential Foundation**. It remains **UNRESOLVED / NOT AUTHORIZED** until a separately published bounded entry gate exists.
- Password change/reset/recovery/rotation/revocation, Production authentication/enrollment activation, updater activation, and any Production schema execution remain separately governed and are not authorized by this reconciliation.

The detailed canonical baseline is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Historical roadmap sections below remain useful provenance but do not override this section.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current roadmap interpretation, this section supersedes older current-facing M7.5 wording retained below as historical planning/provenance.

- M7.0–M7.4A: **DONE / PUBLISHED**.
- M7.5 Preview Runtime Qualification: **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**.
- Canonical evaluator after PR #129 and cleanup PR #130: **29 VERIFIED / 0 BLOCKED**; `lifecycle_authority_created=false`.
- Mandatory M7.5 blockers: **NONE**.
- M7.6 and M7.7: **NOT AUTHORIZED**.
- Phase 0: **IN PROGRESS**; Phase 0 Exit: **NOT APPROVED**.
- Sprint 14, Release, and Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

The preferred next bounded engineering direction after this closure is the separately gated **Secure Web Updater architecture foundation / release control plane**. Architecture and design may proceed only under the relevant authority; this closure does not authorize updater source implementation, workflow mutation, deployment, cPanel/database action, M7.6, or any later lifecycle stage.

Historical roadmap language, SHAs, PRs, and evidence snapshots below remain preserved as provenance.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current roadmap interpretation, this section supersedes older current-facing M7.5 wording retained below as historical planning/provenance.

- M7.0–M7.4A: **DONE / PUBLISHED**.
- M7.5: **IN PROGRESS / QUALIFICATION MATERIAL PROGRESS**.
- Canonical evaluator after PR #124: **26 VERIFIED / 3 BLOCKED**; overall outcome **BLOCKED / INCOMPLETE**; `lifecycle_authority_created=false`.
- Remaining blockers: `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`, `ENGINE:TENANT_ISOLATION:PARTIAL`, and `RUNTIME:BACKUP_RESTORE:PARTIAL`.
- M7.6 and M7.7: **NOT AUTHORIZED**.
- Phase 0: **IN PROGRESS**; Phase 0 Exit: **NOT APPROVED**.
- Sprint 14, Release, and Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

The preferred next analysis after this state consolidation is a fresh read-only gap analysis of the three remaining M7.5 blockers, with tenant isolation investigated before restore because restore activity is higher-risk and separately authorized.

Historical roadmap language, SHAs, PRs, and evidence snapshots below remain preserved and must not override this consolidation.

## Roadmap principles

- Outcome lebih penting daripada jumlah fitur.
- Security, tenant isolation, migration, observability, backup, dan rollback adalah bagian fitur.
- Setiap fase memiliki entry/exit criteria dan tidak otomatis terikat tanggal sebelum kapasitas disetujui.
- Marketplace, plugin, AI automation, cloud, dan Kubernetes tidak dipercepat sebelum fondasi siap.
- Product Vision, Capability Map, Architecture Direction, Delivery Roadmap, dan Implementation Authority harus tetap dibedakan.
- Keberadaan capability pada roadmap atau Enterprise Capability Map tidak memberikan implementation authority.

## Canonical product identity

Nama produk canonical adalah **oneQay**.

Dokumen current-state dan future-facing harus menggunakan `oneQay`. Immutable GitHub identifiers, repository path `labzefry/oneQay`, SHAs, historical commit messages, branch names, dan quoted historical evidence tidak ditulis ulang hanya untuk normalisasi branding.

## Phase 0 — Governance and discovery

**Outcome:** konstitusi engineering, scope, domain, risiko, dan keputusan teknologi siap untuk implementasi.

Deliverables:

- 18 dokumen AI Engineering Handbook;
- project manifest dan decision register;
- stakeholder, actor, journey, dan domain discovery;
- MVP scope/non-scope;
- data classification dan threat model awal;
- ADR technology stack, database, tenancy, auth, dan deployment stage 1;
- backlog prioritas serta acceptance criteria.

Exit criteria:

- handbook direview dan disetujui;
- tidak ada Critical open decision untuk memulai skeleton;
- MVP dan success metrics disetujui;
- risiko Critical memiliki owner dan mitigation.

### Canonical Phase 0 semantics

Phase 0 tetap **In Progress** sampai exit criteria-nya disetujui secara eksplisit. Status ini adalah status program governance/discovery dan tidak boleh dibaca sebagai pernyataan bahwa repository tidak memiliki source code teknis.

Repository telah mempublikasikan bounded Platform Foundation melalui Sprint 12 dan Sprint 13 melalui lifecycle authority yang terpisah. Fakta publikasi tersebut tidak berarti Phase 0 telah selesai, tidak memulai Phase 1 secara otomatis, tidak menyetujui final business application, dan tidak memberi authority untuk Sprint 14.

Mulai M5.3, istilah **application implementation Blocked** berarti final/business/production application implementation yang belum diotorisasi. Istilah tersebut tidak membatalkan atau menghapus bounded Platform Foundation source yang telah dipublikasikan melalui Sprint 13.

Phase 0 Controlled Implementation Bridge menegaskan bahwa final Phase 0 Exit bukan prerequisite untuk setiap bounded Local/Test/CI source file. Bounded Technical Preview source preparation dapat berlangsung sebelum final Phase 0 Exit hanya setelah bridge dipublikasikan dan Product Owner memberikan source authority yang terpisah. Preview runtime/deployment tetap memerlukan actual target qualification sesuai DEC-009 dan authority deployment terpisah.

Sprint 14 tetap **Not Authorized** dan production readiness tetap **NO-GO**.

## Phase 1 — Platform foundation

**Outcome:** fondasi multi-tenant yang aman, dapat diuji, diinstal, dan dioperasikan.

Scope candidate:

- tenant lifecycle dan configuration;
- identity, MFA, role/permission, session;
- organization, outlet, device;
- audit log dan error correlation;
- migration/seeder framework;
- configuration/secret handling;
- installer baseline;
- CI quality gate dan deployment stage 1;
- backup/restore rehearsal.

Exit criteria:

- tenant-isolation test lulus;
- privileged access dan audit tervalidasi;
- installation clean-room lulus;
- backup restore lulus;
- Critical/High security issue nol.

Published bounded Platform Foundation work through Sprint 13 is preserved as repository fact and must not be reclassified retroactively as evidence that all Phase 1 entry or exit criteria were satisfied.

## Phase 2 — POS minimum viable product

**Outcome:** transaksi penjualan inti dapat dilakukan aman dan konsisten.

Scope candidate:

- catalog, price, tax configuration;
- outlet inventory baseline;
- cart, sale, payment recording, receipt;
- shift/register lifecycle;
- void/refund dengan authorization;
- basic customer;
- daily reconciliation dan operational report;
- PWA experience untuk flow utama.

Gate khusus:

- idempotency transaksi;
- monetary precision dan rounding tests;
- offline requirement diputuskan melalui ADR;
- audit untuk void/refund;
- performance budget kasir.

## Phase 3 — ERP operations

**Outcome:** operasi persediaan dan procurement terintegrasi.

Scope candidate:

- purchasing dan supplier;
- receiving, transfer, adjustment, stock count;
- warehouse/outlet policy;
- cost and valuation strategy;
- accounts receivable/payable foundation;
- management reporting.

Exit criteria meliputi reconciliation, migration safety, permission separation, dan period-close controls.

## Phase 4 — SaaS commercial platform

**Outcome:** oneQay dapat mengelola subscription dan tenant lifecycle secara komersial.

Scope candidate:

- plan, entitlement, quota;
- subscription/billing integration;
- trial, upgrade, downgrade, suspension;
- tenant domain dan Cloudflare automation;
- customer portal;
- support and operational controls.

## Phase 5 — Public ecosystem

**Outcome:** integrasi eksternal aman dan terkendali.

Scope candidate:

- public API and developer portal;
- API keys/OAuth decision;
- webhook management;
- integration catalog;
- marketplace governance;
- plugin signing, permission, compatibility, sandbox, kill switch.

## Phase 6 — Intelligent operations

**Outcome:** AI Assistant memberi insight dan bantuan tanpa mengorbankan data boundary.

Scope candidate:

- AI Gateway dan provider abstraction;
- tenant-authorized retrieval;
- explainable analytics;
- assisted workflow dengan human confirmation;
- evaluation suite, red-team, cost and latency budget;
- prompt/model versioning dan safe fallback.

## Enterprise product evolution

M6 menambahkan peta evolusi konseptual yang melengkapi phase delivery di atas. Ini bukan release commitment dan tidak mengubah authority phase/sprint.

| Evolution stage | Directional purpose | Authority implication |
| --- | --- | --- |
| E0 — Foundation | Governance, tenancy, identity, configuration, audit, data safety, quality, recovery | Existing Sprint 12/13 publication preserved; no new source authority |
| E1 — Core Transaction Platform | Controlled business transactions and approved POS/commerce slice | Requires separate Product Owner implementation authority |
| E2 — Business Management | Inventory, procurement, CRM, finance/accounting foundation, reporting, workflow | Directional only until separately authorized |
| E3 — Enterprise Management | Multi-unit governance, advanced administration, configurable process and control | Directional only until separately authorized |
| E4 — Intelligence | Business Intelligence, AI insight, recommendation, bounded automation | Directional only; AI/data/security gates remain mandatory |
| E5 — Ecosystem | Public API, partner integration, marketplace, plugin/extension ecosystem | Directional only; trust/compatibility/security gates remain mandatory |

Canonical detail resides in `docs/handbook/ENTERPRISE_VISION.md`. M6 representation/publication completed through PR #69, M6 closure completed through PR #71, and the substantive Enterprise Vision was separately Approved through GOV-051.

## Infrastructure evolution track

| Stage | Trigger | Required readiness |
| --- | --- | --- |
| Shared Hosting | Initial controlled launch | scheduler, backup, secure config, monitoring baseline |
| VPS | Resource/control limit reached | automated deploy, externalized state, hardening |
| Dedicated | Sustained workload/isolation need | capacity model, HA/DR decision |
| Docker | Reproducibility and portability need | container-safe state and jobs |
| Cloud | Managed service/autoscaling value proven | cost, IAM, network, DR governance |
| Kubernetes | Operational complexity justified | SRE ownership, observability, platform maturity |

## Cross-cutting workstreams

Security, performance, accessibility, localization, privacy, observability, testing, documentation, installer/updater, data migration, dan release engineering berjalan pada setiap fase, bukan sebagai fase terakhir.

## Prioritization

Gunakan urutan: legal/security necessity, tenant/data integrity, revenue/operational value, risk reduction, dependency enablement, user experience, optimization. Setiap item roadmap diturunkan menjadi task dengan owner, acceptance criteria, dependency, risk, dan evidence.

## Roadmap change control

Perubahan fase harus memperbarui PROJECT_MANIFEST.md, ROADMAP.md, TASKS.md, CHANGELOG.md, serta ADR/dokumen domain yang terdampak. Tanggal hanya ditambahkan setelah kapasitas, dependency, dan risk buffer tersedia.

## Accelerated Technical Preview track

Technical Preview v0.0.1 is a gated T+5 workstream tracked by Issue #23. It is a synthetic sandbox preview, not a production or pilot release.

| Working day | Planned outcome | Entry gate |
| ---: | --- | --- |
| 1 | Exact-head review of ADR, data, threat, hosting, recovery, and exit evidence | Product Owner decision package recorded |
| 2 | Application skeleton, configuration boundary, CI, tenant context | M7.0 bridge published and separate Product Owner M7.1 source-code authority explicitly granted; final Phase 0 Exit and actual P2 qualification are not prerequisites for bounded Local/Test/CI preparation |
| 3 | Identity, organization/outlet/device, catalog/cart, cash-sale vertical slice | Separate bounded authority plus applicable Day 2 quality and isolation gates |
| 4 | Migration/seeder, installer, deployment, backup/restore/rollback rehearsal | Actual target environment capability verified and separate deployment/rehearsal authority granted where required |
| 5 | Security, isolation, smoke, recovery, and staging acceptance | Combined source/security/runtime/recovery gates pass with no unresolved Critical/High Preview defect |

The source-engineering clock may begin only after the applicable bounded source authority is granted. Preview deployment/operational acceptance may not begin until the actual target is identified and DEC-009 mandatory capability evidence is sufficient. P1 remains conditional/not selected and P2 actual target evidence must not be invented. A missed mandatory gate moves the target; quality, tenant isolation, audit, security, or recovery controls must not be removed to preserve the date.

This track does not promote GD-007, resolve JRN-003/JRN-013, authorize production data or real payment, or change Phase 0 from In Progress before an explicit exact-head exit decision.

Historical Technical Preview planning language is preserved as planning history. Later bounded Platform Foundation publications through Sprint 13 are repository facts but do not retroactively rewrite historical lifecycle events.

## M7 — Technical Preview Implementation Enablement

M7 is the current bounded Technical Preview engineering workstream. Its labels describe sequencing and do not independently grant implementation authority or convert M7 into Sprint 14.

Current progression after governed publication through PR #98:

- M7.0: **DONE / PUBLISHED**;
- M7.1: **DONE / PUBLISHED** through PR #92;
- M7.2: **DONE / PUBLISHED** through PR #93;
- M7.3: **DONE / PUBLISHED** through PR #94;
- M7.4: **DONE / PUBLISHED** through PR #96;
- M7.4A: **DONE / PUBLISHED** through PR #98;
- M7.5: **BLOCKED / NOT AUTHORIZED — ACTUAL SANITIZED P2 TARGET EVIDENCE AND DEC-009 CAPABILITY VERIFICATION REQUIRED**;
- M7.6–M7.7: **BLOCKED / NOT AUTHORIZED** according to their separate evidence and authority gates.

| Micro-milestone | Controlled outcome | Canonical state / gate |
| --- | --- | --- |
| M7.0 — Controlled Implementation Bridge | Separate Local/Test/CI source readiness from Preview runtime/deployment readiness | **DONE / PUBLISHED**; bridge publication completed through its governed lifecycle |
| M7.1 — Application Skeleton & Configuration Boundary | Laravel/Vue/Inertia/Vite/TypeScript-first skeleton, config/secret boundary, health/readiness/correlation foundations, Local/Test/CI baseline | **DONE / PUBLISHED** through PR #92 |
| M7.2 — Tenant Kernel & Isolation Foundation | Tenant context and isolation primitives with negative verification | **DONE / PUBLISHED** through PR #93 |
| M7.3 — Identity / Organization / Outlet / Device Minimum | Minimum first-party identity and organizational context | **DONE / PUBLISHED** through PR #94 |
| M7.4 — POS Core Synthetic Vertical Slice | Synthetic bounded POS core flow | **DONE / PUBLISHED** through PR #96; publication does not grant M7.5, deployment, release, or Production authority |
| M7.4A — Technical Preview Interaction Layer | Synthetic sign-in → server-verified tenant/outlet context → synthetic catalog → cart → CASH / MANUAL_EXTERNAL → existing M7.4 `CompleteSyntheticSale` → receipt preview | **DONE / PUBLISHED** through PR #98; synthetic-only Local/Test/CI/explicit Preview boundary; not Production Ready; no durable Production persistence, deployment, release, or Production authority |
| M7.5 — Preview Runtime Qualification | Qualify the actual P2 target under DEC-009 | **BLOCKED / NOT AUTHORIZED**; actual sanitized P2 target evidence and DEC-009 capability verification required |
| M7.6 — Preview Deployment / Recovery Rehearsal | Deploy/recover/rollback on qualified target | **BLOCKED / NOT AUTHORIZED**; qualified target plus separate deployment authority required |
| M7.7 — Technical Preview Acceptance | Combined technical acceptance | **BLOCKED / NOT AUTHORIZED**; combined source, security, runtime, recovery, and operational evidence required |

Track A Controlled Application Engineering has published the bounded M7.4 POS core and M7.4A Technical Preview interaction layer. Track B Preview Runtime Qualification remains separately gated: M7.5 cannot begin until actual sanitized P2 target evidence is supplied and verified against DEC-009 and separate Product Owner authority is granted. Both tracks still converge before Technical Preview deployment/acceptance.

## M5 — Engineering State, CI & Governance Stabilization

M5 was a control-plane and canonical-state stabilization track. It did not authorize Enterprise Vision, Sprint 14, production deployment, release, SQL execution, migration execution, or production database modification.

| Micro-milestone | Canonical state | Result |
| --- | --- | --- |
| M5.1 — Canonical State Reconciliation | PUBLISHED / COMPLETE | Canonical `docs/ai/` checkpoint location established and stale duplicate root state reduced to pointer stubs; published through PR #66 |
| M5.2 — CI & Lifecycle Control Hardening | PUBLISHED / ENFORCEMENT COMPLETE | A-03 and A-05 resolved; protected contexts include PHP foundation regression and exact-head Product Owner merge authority; published through PR #67 |
| M5.3 — Governance & Program State Synchronization | PUBLISHED / COMPLETE | A-06, A-07, and A-08 reconciled; published through PR #68 as `e45f5b4c0f143abc6e255e4e8550bf3504348aae` |

M5 publication facts remain immutable repository history.

## M6 — Enterprise Vision Canonicalization

**State:** PUBLISHED / PUBLICATION COMPLETE.

M6 publication scope covered Enterprise Vision analysis/documentation, capability-map and conceptual evolution definition, current program-state synchronization, brand normalization to `oneQay`, bounded publication preparation, validation, and independent review. The canonical representation was published through PR #69; M6 closure was completed through PR #71; substantive Enterprise Vision approval was separately granted through GOV-051.

M6 publication outcome preserved:

- Phase 0: In Progress;
- Sprint 12: Published;
- Sprint 13: Published;
- Sprint 14: Not Authorized;
- final/business/production application implementation: Blocked unless separately authorized;
- production readiness: NO-GO;
- ADR/GD/JRN statuses unless separately decided;
- historical lifecycle discrepancies as historical facts.

M6 publication did not and does not create Ready, Merge, deployment, release, SQL/migration execution, production database modification, Sprint 14, or new business/application source implementation authority.

Attribution: Lab | zefry

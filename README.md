# oneQay

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


## Canonical Sprint40 pre-source program state — 2026-08-25

For current identity/session governance, schema selection, workflow preservation, runtime activation, and next-work interpretation, this section supersedes older current-facing sections retained below as historical provenance.

- Sprint 21 through Sprint 39 governed identity/control foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint40 has selected **First-Party Session Identity Disablement Revalidation Foundation** as the next governed concern, but its application source implementation is **NOT YET IMPLEMENTED / NOT AUTHORIZED BY THIS DOCUMENTATION SYNCHRONIZATION**.
- Sprint40 entry-gate PR #268 and schema/source-envelope gate PR #270 are **PUBLISHED**. The gate selects a minimal forward-only migration #14 for later source implementation: `apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`, adding only non-null boolean `first_party_authentication_enabled` with default `true` to `oneqay_identities`.
- Canonical source migrations on `main` remain exactly **#1 through #13**. Migration #14 is **SELECTED FOR THE LATER SPRINT40 SOURCE STAGE BUT DOES NOT YET EXIST OR APPLY ON CANONICAL `main`**.
- The frozen future Sprint40 source implementation envelope is exactly eight paths with sorted newline-terminated SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- Sprint40 source-preservation predecessor PR #271 is **PUBLISHED**; its historical compatibility prerequisites/corrections were published through PR #272 and PR #273. Canonical source-preservation publication commit is `31fe2214312618448356fdae668d6bace215b1a7`.
- Documentation-synchronization preservation predecessor PR #274 is **PUBLISHED** as canonical commit `7be563d66e2f8441cf28dd2ed9ce5d6c0704098f`, tree `adbbce29218e312b243076dc3ee984e68ce79b65`, with verified/valid Git signature. It recognizes only the exact 13-document synchronization fingerprint `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.
- Sprint40 request-time semantics remain fail-closed and server-authoritative: the exact session identity must remain currently eligible; missing, disabled, malformed, or contradictory eligibility evidence must deny access without converting credential/factor epoch, tenant membership, organization/outlet/device access, or caller-supplied selectors into identity authority.
- Technical Preview remains **`NO_SCHEMA_CHANGE` / NOT ACTIVATED FOR SPRINT40**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.
- This synchronization publishes documentation only. It grants no Sprint40 source mutation, migration creation/execution, schema application, route/API addition, runtime activation, Preview/Production activation, updater, deployment, or release authority.
- After this 13-document synchronization is published, the next logical governed stage is the already-frozen Sprint40 eight-path source implementation against a freshly verified canonical `main`; that source stage still requires its own separately bounded authority.

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

## Canonical post-Sprint 28 program-state consolidation — 2026-08-18

For current project, lifecycle, and next-work interpretation, this section supersedes every older current-facing milestone/status/next-work statement retained below as historical provenance.

- Sprint 21 through Sprint 28 governed foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 28 source publication PR #188 was squash-published as `b012262b0028c21c7662d5a9edec3cbf249bba5e`; post-Sprint28 canonical reconciliation PR #189 was squash-published as `68a9b5736a3fc169b50984857954322b169bc42e`.
- Published identity/control progression now includes durable role/permission policy, policy administration, initial tenant-administrator provisioning, protected-control administrator lifecycle, policy-administration delivery, first-party credential verification, first-party login/session establishment, and first-party initial password enrollment.
- Canonical source migrations are exactly **#1 through #8**; migrations #1–#7 remain immutable and migration #8 is the additive forward-only initial-password-enrollment migration.
- First-party credential verification, login/session establishment, and initial password enrollment remain bounded to **Local/Test/CI** under their published runtime and persistence gates.
- Technical Preview remains **`NO_SCHEMA_CHANGE`** and does not receive Sprint 26–28 credential/login/enrollment authority.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Durable application persistence remains default-disabled with **`ONEQAY_PERSISTENCE_ENABLED=false`**.
- The next logical governed identity concern is **First-Control-Principal Bootstrap Credential Foundation**. It requires a new bounded entry gate before any source implementation and is **NOT AUTHORIZED** by this documentation consolidation.

The authoritative detailed post-Sprint28 publication record is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Historical M7.5/updater and earlier milestone sections below remain preserved as provenance but must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current M7/lifecycle interpretation, this section supersedes the older current-facing consolidation retained below as historical checkpoint/provenance.

- M7.0–M7.4A: **DONE / PUBLISHED**.
- M7.5 Preview Runtime Qualification: **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**.
- Canonical M7.5 evaluator after PR #129 and cleanup publication PR #130: **29 VERIFIED / 0 BLOCKED**.
- `lifecycle_authority_created=false` remains true for the M7.5 evidence package; this documentation closure does not authorize any later lifecycle stage.
- `ENGINE:TENANT_ISOLATION`, `ENGINE:RESTORE_VERIFIED`, and `RUNTIME:BACKUP_RESTORE` are **VERIFIED** within the bounded non-Production Technical Preview evidence catalog.
- M7.6: **NOT AUTHORIZED**.
- M7.7: **NOT AUTHORIZED**.
- Phase 0: **IN PROGRESS**; Phase 0 Exit: **NOT APPROVED**.
- Sprint 14, Release, and Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

The next engineering direction after this closure is the separately gated Secure Web Updater architecture foundation and release-control-plane design. No source, workflow, deployment, cPanel, database/schema/migration, restore, M7.6, M7.7, Release, or Production authority is created by this closure.

Historical SHAs, PRs, evidence snapshots, and prior checkpoint wording below remain preserved and must be interpreted as historical where superseded by this closure.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current M7/lifecycle interpretation, this section supersedes older current-facing wording retained below as historical checkpoint/provenance.

- M7.0: **DONE / PUBLISHED**.
- M7.1: **DONE / PUBLISHED** through PR #92.
- M7.2: **DONE / PUBLISHED** through PR #93.
- M7.3: **DONE / PUBLISHED** through PR #94.
- M7.4: **DONE / PUBLISHED** through PR #96.
- M7.4A: **DONE / PUBLISHED** through PR #98.
- M7.5: **IN PROGRESS / QUALIFICATION MATERIAL PROGRESS**.
- Canonical M7.5 evaluator after PR #124: **26 VERIFIED / 3 BLOCKED**.
- M7.5 overall qualification: **BLOCKED / INCOMPLETE**.
- Remaining blockers: `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`, `ENGINE:TENANT_ISOLATION:PARTIAL`, and `RUNTIME:BACKUP_RESTORE:PARTIAL`.
- `lifecycle_authority_created=false`.
- M7.6: **NOT AUTHORIZED**.
- M7.7: **NOT AUTHORIZED**.
- Phase 0: **IN PROGRESS**; Phase 0 Exit: **NOT APPROVED**.
- Sprint 14, Release, and Production: **NOT AUTHORIZED**.
- Production readiness: **NO-GO**.

Historical SHAs, PRs, evidence snapshots, and prior checkpoint wording below remain preserved and must not be reinterpreted as newer than this consolidation.

> **The Future of Intelligent Business Management**

**oneQay** adalah platform business management multi-tenant dengan Enterprise Vision **Enterprise Intelligent Business Management Platform** yang telah disetujui melalui GOV-051. Persetujuan visi tersebut tidak berarti seluruh capability telah diimplementasikan, disetujui untuk delivery, atau production-ready.

| Informasi | Nilai |
| --- | --- |
| Produk | oneQay |
| Kategori | Enterprise SaaS POS & ERP Platform |
| Enterprise Vision | Approved — Enterprise Intelligent Business Management Platform |
| Developer & Product Engineering Entity | Lab \| zefry |
| Repository | `labzefry/oneQay` |
| Source of Truth | GitHub |
| Current delivery phase | Phase 0 — Governance and Discovery: In Progress |
| Current engineering workstream | M7 — Technical Preview Implementation Enablement |
| Latest completed micro-milestone | M7.4A — Technical Preview Interaction Layer |
| Next gated micro-milestone | M7.5 — Preview Runtime Qualification — Blocked pending actual sanitized P2 target evidence and DEC-009 capability verification |
| Sprint 14 | Not Authorized |
| Production readiness | NO-GO |

Engineering collaboration tooling is governed separately by `AI_CONSTITUTION.md` and is not product authorship attribution.

## Canonical product name

Nama produk wajib ditulis **oneQay** pada current/future-facing canonical material.

Bentuk `OneQay`, `ONEQAY`, `Oneqay`, dan `oneqay` bukan canonical current product identity. Repository identifier `labzefry/oneQay`, immutable GitHub URLs, SHAs, historical commit messages, branch names, dan quoted historical evidence tidak ditulis ulang hanya untuk normalisasi branding.

## Visi

oneQay adalah **Enterprise Intelligent Business Management Platform** yang dapat digunakan mulai dari usaha tunggal hingga organisasi multi-cabang dan multi-tenant, lalu berkembang bertahap dari fondasi transaksi dan operasional menjadi business management, enterprise management, intelligence, dan ecosystem platform tanpa mengganti fondasi business logic ketika infrastruktur bertumbuh.

Tujuan arah produk adalah menghadirkan platform yang:

- mudah digunakan untuk operasional harian;
- aman untuk data bisnis dan transaksi;
- modular tanpa kehilangan konsistensi domain;
- dapat dikembangkan tanpa ketergantungan berlebihan pada infrastruktur;
- API-first dan integration-ready;
- dapat diobservasi, diuji, dipulihkan, dan diperbarui secara terkendali;
- extensible melalui boundary yang disetujui;
- AI-ready dengan deterministic controls dan human accountability;
- memiliki tata kelola pengembangan yang dapat dibuktikan melalui GitHub.

Detail canonical Enterprise Vision berada di `docs/handbook/ENTERPRISE_VISION.md`. M6 adalah historical completed work; substantive Enterprise Vision kemudian Approved melalui GOV-051.

## Enterprise Capability Map direction

M6 mengelompokkan capability directional ke dalam:

- **Core Business Platform:** Tenant & Organization, Identity & Access, Master Data, POS / Commerce, Inventory, Procurement, Finance / Accounting, CRM, HRM, Reporting & Business Intelligence;
- **Platform Capabilities:** Workflow, Notification, Audit, File / Document, Search, API, Integration, Webhook / Event Integration, Configuration, Localization, Observability, Recovery & Operational Control;
- **Extensibility:** Marketplace, Plugin / Extension, Public API, Partner Integration;
- **AI Platform:** AI Assistant, AI Insight, AI Automation, AI Recommendation, AI Analytics, AI Gateway / Policy Boundary;
- **Channels:** Web Application, PWA, Mobile / Android, Admin Platform, public/customer-facing surfaces, dan API/partner consumers.

Capability-map presence tidak memberikan implementation authority.

## Product evolution

M6 menetapkan enam evolution stages konseptual:

1. **E0 — Foundation**
2. **E1 — Core Transaction Platform**
3. **E2 — Business Management**
4. **E3 — Enterprise Management**
5. **E4 — Intelligence**
6. **E5 — Ecosystem**

Stage tersebut bukan release commitment. Setiap bounded implementation tetap memerlukan Product Owner authority dan gate yang berlaku.

## Target platform

oneQay diarahkan untuk mendukung secara bertahap:

- Web Application
- Progressive Web App (PWA)
- Android / Mobile
- REST API
- Public API
- Admin Platform
- Landing Website
- Content Management System (CMS)
- Marketplace
- Plugin / Extension System
- AI Platform capabilities

Status masing-masing capability mengikuti `PROJECT_MANIFEST.md`, ADR, roadmap, dan lifecycle authority; daftar tersebut bukan bukti implementation readiness atau janji seluruh platform tersedia pada rilis pertama.

## Status proyek

Current canonical state:

- Phase 0 — Governance and Discovery: **In Progress**;
- bounded Platform Foundation Sprint 12: **Published**;
- bounded Platform Foundation Sprint 13: **Published**;
- M5.1: **PUBLISHED / COMPLETE**;
- M5.2: **PUBLISHED / ENFORCEMENT COMPLETE**;
- M5.3: **PUBLISHED / COMPLETE** through PR #68;
- M6: **PUBLISHED / COMPLETE** as historical Enterprise Vision canonicalization work;
- GOV-051 Enterprise Vision: **APPROVED / DECISION COMPLETE**;
- M7.0 — Controlled Implementation Bridge: **DONE / PUBLISHED**;
- M7.1 — Application Skeleton & Configuration Boundary: **DONE / PUBLISHED** through PR #92;
- M7.2 — Tenant Kernel & Isolation Foundation: **DONE / PUBLISHED** through PR #93;
- M7.3 — Identity / Organization / Outlet / Device Minimum: **DONE / PUBLISHED** through PR #94;
- M7.4 — POS Core Synthetic Vertical Slice: **DONE / PUBLISHED** through PR #96;
- M7.4A — Technical Preview Interaction Layer: **DONE / PUBLISHED** through PR #98;
- M7.5 — Preview Runtime Qualification: **BLOCKED / NOT AUTHORIZED** pending actual sanitized P2 target evidence and DEC-009 capability verification;
- M7.6 — Preview Deployment / Recovery Rehearsal: **BLOCKED**;
- M7.7 — Technical Preview Acceptance: **BLOCKED**;
- Sprint 14: **Not Authorized**;
- final/business/production application implementation: **Blocked unless separately authorized**;
- deployment/release/production migration: **Not Authorized**;
- production readiness: **NO-GO**.

M7.0–M7.4A publication facts do not imply Phase 0 exit, Sprint 14 authority, M7.5 runtime-qualification authority, deployment, release, or Production authority. M7.5 remains gated by actual sanitized P2 target evidence and DEC-009 capability verification.

Broader final/business application implementation tetap memerlukan keputusan minimum yang relevan untuk scope-nya, termasuk MVP boundary, domain/architecture decisions, multi-tenant/data controls, security baseline, database/migration governance, API contracts, testing/quality gates, deployment environment, dan release/recovery controls.

## Prinsip arsitektur

Pengembangan oneQay mengikuti prinsip berikut:

- **Modular Monolith First** — mengutamakan kesederhanaan operasional dengan batas modul yang tegas dan jalur evolusi yang jelas.
- **Clean Architecture** — business logic tidak bergantung pada framework, database, UI, atau penyedia infrastruktur.
- **Domain-Driven Design** — model dan bahasa sistem mengikuti domain bisnis.
- **SOLID** — komponen memiliki tanggung jawab yang jelas dan dapat dikembangkan secara aman.
- **API First** — kontrak API dirancang, direview, dan diversi sebelum implementasi konsumen.
- **Multi-Tenant by Design** — setiap data tenant memiliki konteks tenant yang tervalidasi dan tidak boleh bocor lintas tenant.
- **Secure by Default** — autentikasi, otorisasi, validasi, audit, secret management, dan perlindungan data menjadi bagian desain.
- **Observable and Testable** — logging, metrics, tracing, health check, serta automated testing direncanakan sejak awal.
- **Cloud Ready, Infrastructure Independent** — perpindahan lingkungan tidak mengubah business logic.
- **Event-Driven Ready** — modul dapat menerbitkan dan mengonsumsi domain event tanpa mewajibkan microservices pada fase awal.
- **Human Accountable AI** — AI tidak boleh menjadi sumber otorisasi atau mutation irreversible tanpa deterministic controls dan human accountability.

Detail dan keputusan yang mengikat berada di `ARCHITECTURE.md`, `PROJECT_MANIFEST.md`, serta Architecture Decision Records di `docs/adr/`.

## Multi-tenant

Setiap tenant sekurang-kurangnya memiliki:

- Tenant ID
- nama perusahaan
- nama toko atau unit bisnis
- domain atau subdomain akses
- subscription
- configuration
- timezone
- currency
- locale

**Tenant ID adalah batas isolasi data utama.** Domain dan subdomain hanya menjadi media akses, bukan sumber otorisasi tunggal. Setiap request, query, cache key, job, file, event, log yang relevan, dan operasi administratif wajib mempertahankan tenant context.

Model isolasi, indeks, constraint, backup, restore, serta pengujian anti-kebocoran lintas tenant dirinci melalui `DATABASE.md`, `SECURITY.md`, dan ADR yang berlaku.

## GitHub sebagai Single Source of Truth

Seluruh artefak resmi dikelola melalui GitHub, termasuk:

- source code;
- dokumentasi;
- roadmap dan backlog;
- issue dan diskusi teknis;
- pull request dan review;
- CI/CD;
- release, tag, dan changelog;
- keputusan arsitektur;
- lifecycle authority;
- kontrol perubahan dan audit history.

Perubahan yang tidak terlacak di GitHub tidak dianggap sebagai bagian resmi proyek.

### Branch strategy

| Branch | Kegunaan |
| --- | --- |
| `main` | Kondisi stabil dan dapat dirilis sesuai gate |
| `develop` | Integrasi bila diaktifkan oleh release policy |
| `feature/*` | Pengembangan fitur yang diotorisasi |
| `release/*` | Stabilisasi kandidat rilis |
| `hotfix/*` | Perbaikan kritis dari versi produksi |
| `bugfix/*` | Perbaikan defect non-darurat |
| `experiment/*` | Eksperimen yang belum menjadi komitmen produk |
| `agent/*` | Bounded ChatGPT-assisted work |

Protection rules, kebutuhan `develop`, dan release flow mengikuti `CONTRIBUTING.md`, `RELEASE.md`, serta repository ruleset yang aktif.

### Conventional Commits

Commit menggunakan format:

```text
<type>(optional-scope): deskripsi singkat
```

Type yang diizinkan:

- `feat:`
- `fix:`
- `docs:`
- `refactor:`
- `perf:`
- `test:`
- `build:`
- `ci:`
- `security:`
- `chore:`

Setiap commit harus atomik, dapat ditinjau, dan menjelaskan satu tujuan perubahan yang koheren.

## Governance lifecycle

Perubahan material mengikuti bounded lifecycle:

1. Product Owner START authority untuk scope kerja bila diperlukan;
2. bounded branch;
3. Draft PR;
4. exact-head validation;
5. independent review;
6. separate Product Owner READY authority;
7. separate exact-head Product Owner MERGE authority;
8. repository protection dan required checks;
9. publication verification.

Reviewer approval bukan Product Owner lifecycle authority.

## Required protected checks

Current protected contexts published through M5.2:

1. `governance-validation`
2. `markdown-lint`
3. `secret-scan`
4. `php-foundation-regression`
5. `product-owner-merge-authority`

## Tata kelola perubahan

Sebelum perubahan material, gunakan dokumen sesuai scope:

1. `PROJECT_MANIFEST.md`
2. `AI_CONSTITUTION.md`
3. `ARCHITECTURE.md`
4. `ROADMAP.md`
5. `TASKS.md`
6. `CHANGELOG.md`
7. `docs/handbook/ENTERPRISE_VISION.md` untuk canonical Enterprise Vision
8. canonical current-state files di `docs/ai/`

Root `AI_SESSION_STATE.md`, `AI_PROJECT_STATE.md`, dan `AI_NEXT_TASK.md` adalah deprecated pointer stubs; canonical mutable state berada di `docs/ai/`.

Setiap perubahan wajib memperbarui dokumentasi yang terdampak. Minimal manifest, tasks, dan changelog diperiksa; dokumen architecture/API/database/security/deployment/testing/UI/installer/updater/release diperbarui sesuai dampak.

Breaking change, penghapusan modul, perubahan skema tanpa migration, perubahan API tanpa versioning, hardcoded secret, dan pengabaian dokumentasi tidak diperbolehkan.

## Engineering handbook

Handbook tetap living documentation. Daftar berikut adalah baseline document set yang telah menjadi bagian governance repository; status delivery/proyek aktual harus dibaca dari manifest, roadmap, tasks, changelog, dan `docs/ai/`.

| Urutan | Dokumen | Tujuan |
| ---: | --- | --- |
| 1 | `README.md` | Orientasi, visi, ruang lingkup, dan navigasi proyek |
| 2 | `PROJECT_MANIFEST.md` | Identitas teknis dan inventaris kapabilitas proyek |
| 3 | `AI_CONSTITUTION.md` | Aturan permanen untuk ChatGPT pada proyek |
| 4 | `ARCHITECTURE.md` | Arsitektur logis, deployment, dan batas modul |
| 5 | `ROADMAP.md` | Tahapan produk dan engineering |
| 6 | `CODING_STANDARDS.md` | Standar implementasi lintas platform |
| 7 | `DATABASE.md` | Model data, tenancy, migration, dan integritas |
| 8 | `API_SPEC.md` | Kontrak, versioning, error, dan governance API |
| 9 | `SECURITY.md` | Baseline keamanan dan respons insiden |
| 10 | `DEPLOYMENT.md` | Environment, CI/CD, backup, dan rollback |
| 11 | `TESTING.md` | Strategi testing dan quality gate |
| 12 | `UI_GUIDELINE.md` | Design system, aksesibilitas, dan UX |
| 13 | `INSTALLER.md` | Spesifikasi Installer Wizard |
| 14 | `UPDATER.md` | Spesifikasi Auto Updater yang aman |
| 15 | `CONTRIBUTING.md` | Workflow kontribusi dan pull request |
| 16 | `RELEASE.md` | Versioning, release, rollback, dan EOL |
| 17 | `TASKS.md` | Backlog dan status pekerjaan terkontrol |
| 18 | `CHANGELOG.md` | Riwayat perubahan berbasis versi |
| 19 | `docs/handbook/ENTERPRISE_VISION.md` | Approved Enterprise Vision, capability map, dan conceptual evolution |

Struktur dokumentasi lanjutan:

```text
docs/
├── architecture/
├── diagrams/
├── database/
├── api/
├── deployment/
├── uiux/
├── adr/
└── handbook/
```

File kosong dan placeholder tanpa nilai informasi harus dihindari.

## Deployment evolution

oneQay harus dapat berevolusi melalui tahapan berikut tanpa mengubah business logic:

```text
Shared Hosting (cPanel)
    ↓
VPS
    ↓
Dedicated Server
    ↓
Docker
    ↓
Cloud
    ↓
Kubernetes
```

Setiap tahap harus memiliki entry criteria, exit criteria, backup, rollback, observability, security controls, dan perkiraan beban operasional. Perpindahan stage membutuhkan evidence serta authority yang sesuai. Historical M6 work tidak memberikan deployment authority, dan M7.0–M7.4A publication juga tidak memberikan deployment authority.

## Integrasi Cloudflare

Arsitektur dapat menyediakan controlled Cloudflare integration apabila scope dan decision yang berlaku mengotorisasinya, misalnya untuk DNS record tenant, wildcard DNS, SSL, cache purge, zone validation, serta audit operation.

API token dan secret wajib disimpan melalui environment variable atau secret manager. Secret dilarang disimpan di source code, repository, log, database tanpa proteksi yang disetujui, atau response API. Tidak ada authority implementasi provider baru dari reconciliation ini.

## Installer dan updater

oneQay mempertahankan spesifikasi:

- **Installer Wizard** untuk pemeriksaan environment, konfigurasi database, pembuatan administrator, environment generation, migration, seeding, optimization, dan installation report;
- **Auto Updater** untuk version check, release download, backup, integrity verification, maintenance mode, installation, migration, optimization, health verification, serta recovery/rollback.

Executable migration, production deployment, release, dan production database modification tetap mengikuti gate terpisah dan tidak diotorisasi oleh M7.0–M7.4A publication.

## Cara berkontribusi

1. pilih satu issue/task dengan scope dan authority yang jelas;
2. gunakan bounded branch sesuai jenis pekerjaan;
3. pertahankan exact-head review dan lifecycle evidence;
4. ubah hanya file yang diperlukan oleh scope;
5. sertakan alasan, dampak, risiko, dan validasi pada pull request;
6. pastikan tautan, istilah, dan canonical brand `oneQay` konsisten;
7. minta independent review sesuai risk;
8. jangan mark Ready atau merge tanpa Product Owner lifecycle authority yang berlaku;
9. perbarui living documentation yang terdampak.

Detail final berada di `CONTRIBUTING.md`.

## Definition of Done untuk dokumentasi

Dokumen dianggap selesai apabila:

- tujuan dan audiensnya jelas;
- nama produk menggunakan canonical `oneQay` untuk current/future-facing text;
- istilah konsisten dengan dokumen kanonis;
- aturan normatif menggunakan bahasa yang tegas;
- asumsi dan keputusan yang belum final ditandai;
- tidak mengandung secret atau informasi sensitif;
- tautan internal dan struktur heading valid;
- dampak keamanan, multi-tenancy, operasional, testing, dan kompatibilitas dipertimbangkan;
- perubahan dapat ditelusuri melalui commit atau pull request;
- dokumen terkait diperbarui bila diperlukan;
- telah direview oleh pemilik keputusan yang relevan.

## Lisensi

Lisensi produk mengikuti status pada `PROJECT_MANIFEST.md` dan file `LICENSE`. Seluruh dependency dan aset pihak ketiga wajib mematuhi lisensi asalnya serta kebutuhan kepatuhan proyek.

Attribution: Lab | zefry

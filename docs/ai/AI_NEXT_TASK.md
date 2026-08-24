# AI Next Task

## Canonical post-Sprint 38 program-state reconciliation — 2026-08-25

For current identity, security, recovery, session-control, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint37 wording retained below as historical provenance.

- Sprint 21 through Sprint 38 governed control/identity/session foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint38 **First-Party Session Absolute Lifetime Foundation** is published through source PR #254 as `1443749c9c69b6a2f9bcfa37534d51b92b6a8de6` with tree `1562f95d6ae05f2dea432365bdbb87748e2718e2`, parent `99095a16f881084fef5937dfbcc0ec9ee6ac97ee`, and a verified/valid publication signature.
- The exact qualified Sprint38 source head was `1aad005fc52fd2d7160c73b81fcdadb38ea4a499`; the semantic source diff is exactly **10 paths** with sorted-path SHA-256 `411950d5602dc7160668c88e08a3941ebccc8bdc82d20bee77ce4004f039d216` after **29/29** exact-head pull-request workflows succeeded.
- Sprint38 entry-gate preservation PR #248, entry gate PR #249, schema/source-gate preservation PR #250, schema/source-envelope gate PR #251, source preservation bridge PR #252, historical preservation predecessor PR #253, and source PR #254 are published provenance. All consumed lifecycle authorities grant no standing successor authority.
- Post-Sprint38 reconciliation preservation is published through PR #255 as `767a762701ad6dccb53428ff9976e9c22631bc16` with tree `ad78349c5313d930166625f79987b032ce136c00`, parent `1443749c9c69b6a2f9bcfa37534d51b92b6a8de6`, and a verified/valid signature. The predecessor changes exactly Sprint35/Sprint36/Sprint37/Sprint38 preservation workflows with sorted-path SHA-256 `f6064e6563ec987fc487162fc29285870fab4869509a626c1b9a9280dc6ebafe` and recognizes the exact two-document reconciliation successor fingerprint `e1bd5f1acb858e3b8e91f3b57dfe8505c4142564142bf9a32b98240c1dd9c472`.
- Canonical source migrations remain exactly **#1 through #13** and are immutable for this reconciliation. Migration #14 is **NOT REQUIRED / NOT SELECTED / NOT AUTHORIZED**.
- Sprint38 preserves the existing fixed idle authority lifetime of **7200 seconds** and adds a fixed absolute logical authority lifetime of **43200 seconds / 12 hours** from durable server-owned `issued_at_unix`.
- Effective authority expiry is bounded by `min(now + 7200, issued_at_unix + 43200)`. Activity, Laravel session rotation, inventory access, and privileged step-up do not reset the durable issuance origin and cannot extend the same logical authority beyond the hard deadline.
- The exact absolute deadline remains a valid equality boundary. At equality the authority may remain valid, but the service performs no non-advancing repository touch when effective expiry is no longer greater than the current time. At deadline + 1 second the authority fails closed.
- Inventory reports effective capped expiry; an inflated historical `expires_at_unix` cannot grant authority beyond `issued_at_unix + 43200`, and absolute-expired authorities are denied or omitted from active inventory.
- Clock rollback before durable issuance, malformed timestamp state, and idle/absolute TTL configuration mismatch fail closed.
- Canonical lifetime configuration remains source-controlled as `idle_ttl_seconds => 7200` and `absolute_ttl_seconds => 43200`; Sprint38 introduces no environment-controlled absolute TTL.
- Sprint36 inventory/revoke-one/revoke-others/canonical logout semantics and Sprint37 tenant-scoped revoke-all remain preserved. Exact tenant + identity ownership, opaque `public_handle`, non-public `authority_id`, credential/factor epoch validation, and `session_control` step-up freshness of **300 seconds** remain unchanged.
- Sprint38 adds no public route, API, request payload, repository method, audit event, middleware authority, or schema change. Existing session audit vocabulary, including `all_sessions_revoked`, remains preserved and secret-free.
- `ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` remains the source default. Sprint38 delivery remains **Local/Test/CI only**. Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.
- This reconciliation selects **no Sprint39 implementation concern** and grants no Sprint39 source, schema/migration, Preview, Production, updater, deployment, or release authority. The next governed action is Product Owner selection of a future concern and its separately authorized bounded entry gate.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_38_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 37 program-state reconciliation — 2026-08-23

For current identity, security, recovery, session-control, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint36 wording retained below as historical provenance.

- Sprint 21 through Sprint 37 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint37 **First-Party All-Session Termination (Tenant-Scoped Revoke-All) Foundation** is published through source PR #245 as `0b2ac0ea18b1ad28e6b46ba952d2ac260b297643` with tree `fe9fd64e7f9dda2e24c4fb573f73b5d5441a5d50`, parent `d8f9ffe52773c44f256a4721dc2390bd734cf797`, and a verified/valid publication signature.
- The exact qualified Sprint37 source head was `f6b3b18e16d68f91a8e12dbd567a06571add1daf`; the semantic source diff is exactly **11 paths** with sorted-path SHA-256 `a221779e05b1e8ab220610b5068be5d1eb01bc08b516b338ba8f22373e7d89d0`.
- Sprint37 entry-gate preservation PR #238, entry gate PR #239, schema/source-envelope preservation PR #240, schema/source-envelope gate PR #241, historical-preservation bridge PR #243, historical-preservation predecessor PR #244, and source PR #245 are published provenance. PR #242 was closed without merge and grants no authority. All consumed lifecycle authorities grant no standing successor authority.
- Post-Sprint37 reconciliation successor preservation is published through PR #246 as `deadaeacd665cb62e5c59e4fe33c70d5162ec992` with tree `7ba8b9366dab15f1dd45053220c4540234b77f3f`, parent `0b2ac0ea18b1ad28e6b46ba952d2ac260b297643`, and a verified/valid signature. The predecessor changes exactly Sprint35/Sprint36/Sprint37 preservation workflows with sorted-path SHA-256 `93fe19acad52a31981ecea81ab1dc65c730f0f01729959ca6eaedb0de5b128b7` and recognizes only the exact two-document reconciliation successor fingerprint `2b3a711f5cb921dea0fb51a005c0bc742186bce526a0a7e87b10215733215dbe`.
- Canonical source migrations remain exactly **#1 through #13** and are immutable for this reconciliation. Migration #14 is **NOT REQUIRED / NOT SELECTED / NOT AUTHORIZED**.
- Sprint37 adds `POST /auth/sessions/revoke-all` / `auth.sessions.revoke-all` under the existing session-control boundary. Exact tenant + identity ownership is derived server-side; caller-supplied owner/authority selectors are not accepted.
- Revoke-all monotonically revokes every active logical first-party session authority for the exact current tenant + identity, including current authority, while another tenant or identity remains untouched. Durable revocation completes before local Laravel session invalidation and CSRF regeneration.
- Protected privileged identities reuse canonical `session_control` step-up with **300-second** freshness; ordinary identities receive no invented privileged challenge. Credential and factor epochs remain validation evidence and are not mutated by revoke-all.
- The only new bounded audit event is `all_sessions_revoked`; audit evidence remains server-derived and secret-free. Replay/concurrency cannot resurrect authority or create duplicate transition evidence after the active owner set has already converged to revoked.
- Existing inventory, revoke-one, revoke-others, canonical logout, 7200-second idle lifetime, tenant/identity isolation, credential/factor epoch enforcement, and disabled-by-default session-control semantics remain preserved.
- `ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` remains the source default. Sprint37 delivery remains **Local/Test/CI only**. Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.
- This reconciliation selects **no Sprint38 implementation concern** and grants no Sprint38 source, schema/migration, Preview, Production, updater, deployment, or release authority. The next governed action is Product Owner selection of a future concern and its separately authorized bounded entry gate.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_37_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 36 program-state reconciliation — 2026-08-22

For current identity, security, recovery, session-control, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint35 wording retained below as historical provenance.

- Sprint 21 through Sprint 36 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint36 **First-Party Session Inventory & Revocation Foundation** is published through source PR #230 as `6b653d669c8b5474c806ecd5d61d854f25821cdf` with tree `7608a1dd7acaa4d6c7f72b2f14bc82d5b45b8d9a`, parent `0f21f021244b4260e2b712e064218fa186cd2142`, and a verified/valid publication signature.
- The exact qualified Sprint36 source head was `bf95e06ec0097f2d515f9337b1ef3b2c69360888`; the semantic source diff is exactly **23 paths** with sorted-path SHA-256 `ea735f8f5ee06d480863f9d1ba7ae58a91642109963c3f340e8453f3205bb7ae`.
- Sprint36 entry gate PR #226, entry successor compatibility PR #227, schema/source-envelope gate PR #228, schema successor compatibility PR #229, preservation compatibility PR #231, requalification preservation corrections PR #232/#233, and source PR #230 are published provenance; their lifecycle authorities are consumed and grant no standing successor authority.
- Post-Sprint36 reconciliation successor compatibility is published through PR #235 as `6358e28b2502ea46fc5f19f8cb4fd52ffdcba14f`; the correction changes exactly Sprint35/Sprint36 preservation workflows with sorted-path SHA-256 `c333c5a6ac31588aa9b0520160a1a1080a22af2971658d742ba9456eff94cbc8`. Unknown successor shapes remain fail-closed.
- Canonical source migrations are now exactly **#1 through #13**. Migrations #1–#12 remain immutable. Migration #13 creates durable server-owned first-party session authority plus secret-free audit evidence.
- Sprint36 publishes durable logical session authority separate from raw Laravel session identifiers. Internal `authority_id` is never exposed; external inventory/revocation uses opaque `public_handle`, which is not an authentication authority.
- Full session authority is issued only after successful authentication/MFA. Restricted recovery/pending sessions do not become inventoried authorities. Laravel session rotation preserves the current logical authority rather than creating a new durable authority.
- Session authority captures exact tenant + identity ownership plus `credential_epoch` and, where applicable, `factor_epoch`. Request-time enforcement is fail-closed for revoked, expired, stale-epoch, malformed, missing, or mismatched authority evidence.
- Fixed idle authority TTL remains **7200 seconds**. Privileged remote revocation uses canonical step-up scope `session_control` with **300-second** freshness.
- Published Local/Test/CI session-control routes are `GET /auth/sessions`, `DELETE /auth/sessions/{public_handle}`, `POST /auth/sessions/revoke-others`, and `POST /auth/reauthenticate/session-control`; canonical `POST /auth/logout` revokes the current durable authority. Sprint36 introduces no revoke-all operation.
- `ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` remains the source default. Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.
- This reconciliation selects **no Sprint37 implementation concern**, assumes no migration #14, and grants no Sprint37 source, Preview, Production, updater, deployment, or release authority.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_36_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 35 program-state reconciliation — 2026-08-21

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint34 wording retained below as historical provenance.

- Sprint 21 through Sprint 35 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint35 **Privileged TOTP Recovery & Factor Replacement Foundation** is published through source PR #221 as `0bc4204badd05c45e729116937fef44448a91e59` with tree `d108098077fa5221b90e0de8d503424080138a9b`, parent `b6e8335610943216b293f6f6275bbe7dc5c6498e`, and a verified/valid publication signature.
- The exact qualified Sprint35 source head was `7b2d46bcd8d1301eca67540f38fd263f9a86cc68`; the semantic source diff is exactly **17 paths** with sorted-path SHA-256 `e889db1c7eaa22b3ed008f8781ab35652ca950a3f009c309e5c478d01d368f11`, inside the published 19-path source envelope from PR #220.
- Sprint35 entry gate PR #219, schema/source-envelope gate PR #220, preservation-compatibility gate PR #222, compatibility correction PR #223, and source PR #221 are published provenance; their lifecycle authorities are consumed and grant no standing successor authority.
- PR #223 preserves the historical workflow matrix through exactly **18 legacy workflow paths** with sorted-path SHA-256 `25dbbd94087eba4157fa9c209f09174a127154a98067abbfbeec233bbe9398cd`; unknown successor shapes remain fail-closed.
- Canonical source migrations are now exactly **#1 through #12**. Migrations #1–#11 remain immutable. Migration #12 adds separate privileged-TOTP `factor_epoch` authority plus dedicated privileged-TOTP recovery-code and secret-free audit tables. Migration #13 is **NOT SELECTED / DOES NOT EXIST**.
- Password `credential_epoch` and TOTP `factor_epoch` remain separate monotonic authorities and must not substitute for one another. Sprint32 password-recovery `rq1` authority remains separate from Sprint35 privileged-TOTP recovery authority.
- Sprint35 privileged-TOTP recovery remains **Local/Test/CI only** under the existing disabled-by-default authentication-recovery and privileged-TOTP runtime boundaries. Published routes cover recovery-code rotation, recovery proof, replacement start, and replacement confirm, each with `throttle:5,1` and `throttle:20,60`.
- Successful privileged recovery proof establishes only restricted recovery state for exactly **600 seconds**. Factor replacement is update-only, requires proof of the newly generated factor, checks the old factor epoch, increments factor epoch exactly once, revokes remaining dedicated recovery codes, records secret-free evidence, invalidates restricted state/session authority, and requires fresh normal login plus canonical TOTP challenge. It does not auto-login or synthesize MFA/step-up authority.
- The exact clean-rebased Sprint35 head completed the dedicated Sprint35 recovery regression and the full triggered preservation/cross-cutting matrix successfully before publication.
- `ONEQAY_PERSISTENCE_ENABLED=false` and `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remain source defaults. Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.
- This reconciliation selects **no Sprint36 implementation concern**, assumes no migration #13, and grants no Sprint36 source, Preview, Production, updater, deployment, or release authority.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_35_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 34 program-state reconciliation — 2026-08-21

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint33 wording retained below as historical provenance.

- Sprint 21 through Sprint 34 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint34 Authenticated In-Session Password Change Foundation is published through source PR #217 as `4420ad423c27ea30ebe58307a68a547a6115d1bf` with tree `d9f133eaa37b1ebf635f6611e70409d7ffa133a3`, parent `8b4fc5425ba8d98f35f02c39bd1880ce50c4759b`, and a verified/valid publication signature after **24/24** exact-head pull-request workflows succeeded.
- The qualified Sprint34 source head was `dc35373a43ce59c59c9e0a71f66b49e4f0aabd9e`; source remained exactly **35 paths** with sorted-path SHA-256 `e3b724002cfc0be1ef890d1b5594a2a5179123f949f6f486354e21950c7328eb`.
- Sprint34 entry-gate PR #215 and schema/source-envelope gate PR #216 remain published provenance; their authorities and PR #217 merge authority are consumed and grant no standing successor authority.
- Canonical source migrations are exactly **#1 through #11**. Migrations #1–#10 remain immutable. Migration #11 adds durable `credential_epoch` to `oneqay_identity_password_credentials`; migration #12 is **NOT SELECTED / DOES NOT EXIST**.
- Generic credential epoch authority now comes from `oneqay_identity_password_credentials.credential_epoch`; recovery audit remains recovery-specific evidence rather than generic runtime epoch authority.
- `POST /auth/password/change` is published for **Local/Test/CI only**, uses server-owned authenticated tenant/identity context, current-password re-verification, same-password rejection, `PASSWORD_DEFAULT`, privileged canonical TOTP when required, update-only locked mutation, exact old+1 epoch increment, recovery-code revocation, and fresh-login-required session invalidation.
- Sprint33 recovery-bound reset now increments the same durable credential epoch exactly once while preserving its recovery-specific evidence; authenticated normal password change fabricates no recovery proof/audit events and consumes no recovery code.
- The Sprint34 verifier wiring correction preserves synthetic Technical Preview verification only when Preview is explicitly armed and normal durable verification otherwise.
- `ONEQAY_PERSISTENCE_ENABLED=false` and `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remain source defaults. Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.
- This reconciliation selects **no Sprint35 implementation concern**, assumes no migration #12, and grants no Sprint35 source, Preview, Production, updater, deployment, or release authority.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_34_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

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

## Canonical post-Sprint 28 next task — 2026-08-18

This file is the current-facing next-work checkpoint. Earlier M7.5 and Secure Web Updater next-task checkpoints remain immutable in Git history and are historical provenance only.

Attribution: **Lab | zefry**

## Completed boundary

- Sprint 21 through Sprint 28 governed foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 28 source publication PR #188 was squash-published as `b012262b0028c21c7662d5a9edec3cbf249bba5e`.
- Post-Sprint28 canonical reconciliation PR #189 was squash-published as `68a9b5736a3fc169b50984857954322b169bc42e`.
- Canonical source migrations are exactly **#1 through #8**.
- Migrations #1 through #7 remain immutable.
- Migration #8 is additive and forward-only for initial password enrollment.
- Sprint 26 first-party credential verification is published for Local/Test/CI only.
- Sprint 27 first-party login/session establishment is published for Local/Test/CI only.
- Sprint 28 first-party initial password enrollment is published for Local/Test/CI only.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Durable application persistence remains default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`.

## Next governed concern

The canonical next logical identity concern is:

**First-Control-Principal Bootstrap Credential Foundation**

Authority state:

**UNRESOLVED / NOT AUTHORIZED**

Sprint 28 assumes an already-authenticated tenant-control administrator can issue an initial-password enrollment capability for another existing identity. The first protected/control principal therefore still needs a separately governed bootstrap path that does not create a circular dependency on an already-authenticated control administrator.

## Required entry gate before implementation

Before any implementation may begin, a separately published bounded entry gate must at minimum:

1. perform fresh GitHub Minimal Delta Verification against the live canonical repository;
2. define the exact bootstrap authority and trust root without relying on an already-authenticated tenant-control administrator;
3. prevent public self-bootstrap, arbitrary identity creation, arbitrary tenant selection, or protected-control bypass;
4. preserve exact tenant and identity scoping, deny-by-default authorization, and protected-control invariants;
5. preserve secret-minimal handling and ensure no plaintext password, reusable secret, token digest, or credential hash is logged or exposed;
6. define bounded one-time/replay-safe semantics if a bootstrap capability or token is selected;
7. prevent bootstrap scope from silently becoming password change, reset, recovery, rotation, revocation, or credential overwrite;
8. preserve generic failure and anti-enumeration principles where credential state can be observed;
9. preserve Local/Test/CI-only delivery unless a separate runtime authority explicitly says otherwise;
10. keep Technical Preview and Production denied unless separately authorized;
11. determine independently whether any schema addition is actually necessary;
12. define the exact changed-file envelope and dedicated regression/preservation chain before source mutation.

## Explicit non-authority

This checkpoint does **not** authorize:

- Sprint 29 implementation;
- any source-code mutation for first-control-principal bootstrap;
- migration #9;
- any assumption that migration #9 will be needed;
- credential overwrite or administrative password setting;
- authenticated password change;
- forgot-password or password reset/recovery;
- password rotation/revocation/deletion;
- MFA/TOTP/passkey/WebAuthn implementation;
- OAuth/OIDC/SAML/federation implementation;
- API/bearer token authentication implementation;
- Production credential storage or authentication activation;
- Technical Preview credential/login/enrollment activation;
- updater activation or wiring;
- deployment, Release, Phase 0 Exit, Sprint 14, or Production authority.

A future sprint number, exact source envelope, schema/migration authority, workflow authority, and implementation scope exist only after a separately published Product Owner-governed entry gate.

## Live GitHub rule

No hard-coded SHA in this checkpoint is a permanently current live-head claim. The SHAs above are publication provenance. Before any new branch, lifecycle mutation, implementation decision, Ready transition, or Merge transition, obtain the live repository state from GitHub again.

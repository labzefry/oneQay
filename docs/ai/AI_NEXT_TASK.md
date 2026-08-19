# AI Next Task

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

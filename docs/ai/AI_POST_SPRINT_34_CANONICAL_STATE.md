# AI Post-Sprint 34 Canonical State

## Canonical checkpoint

This document records the post-Sprint34 canonical program state after the published authenticated in-session password-change foundation. It is documentation-only and creates no application/source, schema, Preview, Production, updater, deployment, or release authority.

Attribution: **Lab | zefry**

## Publication identity

- Repository: `labzefry/oneQay`.
- Sprint34 concern: **Authenticated In-Session Password Change Foundation**.
- Sprint34 source PR: **#217** — `feat(auth): Sprint34 authenticated in-session password change foundation`.
- Qualified source head: `dc35373a43ce59c59c9e0a71f66b49e4f0aabd9e`.
- Qualified source tree: `d9f133eaa37b1ebf635f6611e70409d7ffa133a3`.
- Exact source parent: `8b4fc5425ba8d98f35f02c39bd1880ce50c4759b`.
- Qualified source changed paths: exactly **35**.
- Frozen sorted-path SHA-256: `e3b724002cfc0be1ef890d1b5594a2a5179123f949f6f486354e21950c7328eb`.
- Published squash main: `4420ad423c27ea30ebe58307a68a547a6115d1bf`.
- Published tree: `d9f133eaa37b1ebf635f6611e70409d7ffa133a3`.
- Published parent: `8b4fc5425ba8d98f35f02c39bd1880ce50c4759b`.
- Merged at: `2026-08-21T04:49:35Z`.
- Published GitHub signature: **verified / valid**.
- PR #217 Product Owner merge authority is **CONSUMED** and grants no standing successor authority.

Sprint34 entry-gate PR #215 and schema/source-envelope gate PR #216 remain historical publication provenance. Their authorities are also consumed.

## Canonical migration state

Canonical source migrations are now exactly **#1 through #11**.

- Migrations #1–#10 remain immutable.
- Migration #11 is published as `apps/web/database/migrations/0000_00_00_000011_add_credential_epoch_to_identity_password_credentials.php`.
- Migration #11 adds non-null, default-0 unsigned 64-bit `credential_epoch` to `oneqay_identity_password_credentials`.
- Migration #11 creates no new table and no new index.
- Migration #11 is forward-only; `down()` fails using the repository-standard `LogicException` boundary.
- Migration #11 backfills each exact tenant/identity credential row from historical recovery audit evidence where `event_type = password_reset_completed`.
- Generic durable credential epoch authority is now `oneqay_identity_password_credentials.credential_epoch`.
- Recovery audit remains recovery-specific evidence and is no longer generic runtime epoch authority.
- Migration #12 is **NOT SELECTED / DOES NOT EXIST**.

Technical Preview remains **`NO_SCHEMA_CHANGE`**. This canonical source migration publication does not authorize Preview or Production migration execution.

## Authenticated password-change contract

Published route:

`POST /auth/password/change`

Runtime scope:

**Local/Test/CI only**.

There is no new Sprint34 feature flag. Normal Laravel web/CSRF semantics apply. The route is throttled by `throttle:5,1` and `throttle:20,60` and remains unavailable in Technical Preview and Production.

Closed business payload:

- `current_password`;
- `new_password`;
- `totp_code` only when server policy requires it.

The caller cannot select tenant, identity, organization, outlet, device, role, permission, protected state, credential epoch, or recovery identifiers. Identity, tenant, and organization are derived from the full authenticated server-side session.

Current password handling is opaque, non-empty, capped at 4096 bytes, and has no trim or normalization. Authoritative re-verification occurs against the locked exact credential row inside the transaction.

New password handling is opaque, 12–4096 bytes, has no trim or normalization, uses `PASSWORD_DEFAULT`, and rejects same-password replacement.

Protected-control or confirmed privileged-TOTP identity requires the existing canonical fresh TOTP challenge through `PrivilegedTotpMfaService`; no custom TOTP cryptography is introduced and TOTP secret material is not changed. Ordinary identity must not submit `totp_code`.

## Atomic mutation and concurrency

Authenticated password change is **UPDATE-ONLY**.

The transaction:

1. locks the exact credential by server-owned tenant + identity;
2. requires the existing credential row/current hash;
3. requires durable credential epoch to equal the session epoch;
4. re-verifies the current password;
5. rejects same-password replacement;
6. validates the new password;
7. hashes with `PASSWORD_DEFAULT`;
8. updates exactly one existing credential row;
9. increments credential epoch from old to old+1 exactly once;
10. revokes unused/unrevoked recovery codes;
11. returns secret-free success.

Insert, upsert, bootstrap fallback, and credential deletion are prohibited. Missing credentials fail closed. Concurrent mutations from the same starting epoch have at most one winner.

## Recovery and session semantics

Sprint33 recovery reset now increments the same generic durable credential epoch exactly once in its locked credential update. Sprint33 still produces its recovery-specific audit evidence correctly; `password_reset_completed` is not generic epoch authority.

Authenticated password change does not fabricate recovery proof/audit semantics: it does not create `proof_succeeded`, does not create `password_reset_completed`, does not consume a recovery code, does not extend recovery TTL, and does not create new recovery codes.

Successful authenticated password change invalidates the current full session and regenerates CSRF. The old session is not advanced to the new epoch and no automatic login occurs. A fresh login is required and captures the current durable credential epoch. Other pre-change sessions retain the old epoch and fail closed when an epoch-protected operation re-verifies them.

## Technical Preview verifier boundary preservation

Sprint34 published a bounded wiring correction for `TechnicalPreviewServiceProvider` interaction with durable identity/organizational verification.

- When Technical Preview is explicitly armed, the synthetic Preview verifier remains in use.
- When Preview is not armed, normal durable authentication verification remains in use.

This boundary must not be removed or reversed. Technical Preview regression evidence confirmed the synthetic Preview remains healthy after the correction.

## Runtime and security boundaries preserved

- `ONEQAY_PERSISTENCE_ENABLED=false` remains the source default.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default.
- Authentication recovery remains Local/Test/CI-only.
- Authenticated in-session password change remains Local/Test/CI-only.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Deployment remains **NOT AUTHORIZED**.
- Release remains **NOT AUTHORIZED**.
- No Production activation authority exists.
- No Preview schema activation authority exists.

## Qualification evidence

At exact final qualified source head `dc35373a43ce59c59c9e0a71f66b49e4f0aabd9e`, all **24/24** pull-request-triggered workflows completed successfully. The successful chain included Governance Required Checks, PHP Foundation Regression, M7.1, M7.2, M7.3, M7.4A, M7.5 Preview Database Qualification, M7.5 Technical Preview Release Artifact, updater/security preservation regressions, Sprint21 through Sprint31, Sprint33, and Sprint34. Sprint32 preservation was executed by the Sprint34/Sprint33 preservation chain.

Product Owner merge authority succeeded on the exact final source head before squash merge, and merge used exact-head race safety.

## Post-Sprint34 next-work boundary

This reconciliation selects **no Sprint35 implementation concern** and grants no Sprint35 source authority.

Migration #12 is not assumed. Administrative password overwrite, MFA recovery/factor lifecycle, passkey/WebAuthn, federation, API-token authentication, Preview/Production authentication or schema activation, updater activation, deployment, and release remain separately governed concerns.

After this reconciliation is published, the next bounded activity is a fresh repository-evidence-backed **NEXT-CONCERN SELECTION / SPRINT35 ENTRY GATE**. Selection must be made from current repository gaps and architecture rather than by inheriting any previously mentioned candidate. Any selected Sprint35 concern requires new Product Owner authority and its own exact scope/envelope/governance evidence.

## Authority semantics

This canonical reconciliation records publication facts only. Historical Product Owner authorities for PR #215, PR #216, and PR #217 are consumed. They do not transfer to this reconciliation, Sprint35, migration #12, Preview schema, Production, updater, deployment, or release.

Future Ready/Merge action must remain exact-head safe and satisfy repository-native CI, ruleset, branch protection, and Product Owner merge-authority evaluation.

Attribution: **Lab | zefry**

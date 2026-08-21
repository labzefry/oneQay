# Post-Sprint35 Canonical Program State

Attribution: **Lab | zefry**

## Canonical publication checkpoint

Sprint35 — **Privileged TOTP Recovery & Factor Replacement Foundation** — is **PUBLISHED / COMPLETE**.

Canonical publication identity:

- source PR: **#221**;
- exact qualified source head: `7b2d46bcd8d1301eca67540f38fd263f9a86cc68`;
- canonical publication commit / current base for this reconciliation: `0bc4204badd05c45e729116937fef44448a91e59`;
- canonical publication tree: `d108098077fa5221b90e0de8d503424080138a9b`;
- canonical publication parent: `b6e8335610943216b293f6f6275bbe7dc5c6498e`;
- publication signature: **verified / valid**;
- semantic source diff: exactly **17 changed paths**;
- semantic sorted-path SHA-256: `e889db1c7eaa22b3ed008f8781ab35652ca950a3f009c309e5c478d01d368f11`.

The semantic 17-path implementation remained inside the previously published Sprint35 19-path source envelope. The two historical regression paths reserved by that envelope remained byte-identical and were preservation inputs rather than semantic source mutations.

## Governed Sprint35 publication chain

The published Sprint35 lifecycle is represented by the following governed sequence:

1. PR #219 selected **Privileged TOTP Recovery & Factor Replacement Foundation** as the bounded Sprint35 concern and published the entry gate.
2. PR #220 selected migration #12 for later source implementation, separated TOTP `factor_epoch` from password `credential_epoch`, and froze the future source envelope to exactly **19 paths** with sorted-path SHA-256 `aaf7fb11490250d29c68dc7b46b62d2ee2239707ca53e004f9c0652878928e3f`.
3. During source qualification, historical workflow successor-shape guards exposed a compatibility mismatch rather than a Sprint35 runtime defect.
4. PR #222 published the preservation-compatibility correction gate.
5. PR #223 published the bounded correction across exactly **18 legacy workflow paths**, sorted-path SHA-256 `25dbbd94087eba4157fa9c209f09174a127154a98067abbfbeec233bbe9398cd`, preserving unknown-successor fail-closed behavior and historical executable regressions.
6. PR #221 was then clean-rebased onto canonical `b6e8335610943216b293f6f6275bbe7dc5c6498e`, requalified on exact head `7b2d46bcd8d1301eca67540f38fd263f9a86cc68`, authorized by the Product Owner on that exact head, and squash-published as `0bc4204badd05c45e729116937fef44448a91e59`.

Authorities used by PRs #219, #220, #222, #223, and #221 are publication-specific and **CONSUMED**. They provide no standing authority for this reconciliation, Sprint36, migration #13, Preview, Production, updater, deployment, or release work.

## Canonical migration state

Canonical source migrations are now exactly **#1 through #12**.

- Migrations #1–#11 remain immutable historical published source.
- Migration #12 is published as:
  `apps/web/database/migrations/0000_00_00_000012_add_totp_factor_epoch_and_recovery_authority.php`.
- Migration #12 is additive and forward-only.
- `down()` fails using the repository-standard `LogicException`; rollback is not authorized.

Migration #12 adds:

1. unsigned 64-bit `factor_epoch` with default `0` on `oneqay_identity_totp_factors`;
2. `oneqay_identity_totp_recovery_codes`, scoped to tenant + identity + factor epoch, with dedicated selector/digest, issued/consumed/revoked timestamps, uniqueness, ownership indexes, and tenant/identity foreign-key protection;
3. `oneqay_identity_totp_recovery_audit`, carrying secret-free event evidence, factor epoch, correlation ID, and tenant/identity ownership indexes.

Password `credential_epoch` remains the authority for password credential freshness. TOTP `factor_epoch` is a separate monotonic authority for privileged TOTP factor lifecycle. Neither epoch may be used as a substitute for the other.

Migration #13 is **NOT SELECTED / DOES NOT EXIST**.

## Privileged TOTP recovery authority

Sprint35 introduces a dedicated privileged-TOTP recovery authority rather than reusing Sprint32 password recovery.

The password-recovery `rq1` authority remains separate. Sprint35 uses its own dedicated privileged-TOTP recovery code/proof model and recovery audit evidence. Password recovery codes are not privileged-MFA recovery codes and cannot be silently repurposed.

Privileged recovery remains a high-risk, fail-closed path. Recovery proof does not become normal authentication, does not grant a protected-control bypass, and does not synthesize privileged MFA or step-up evidence.

## Published Local/Test/CI delivery contract

Sprint35 delivery is available only inside the existing first-party Local/Test/CI runtime arm and only when both privileged TOTP MFA and authentication recovery are explicitly armed under their existing configuration boundaries.

Published POST routes are:

- `/auth/mfa/recovery/codes/rotate`;
- `/auth/mfa/recovery/proof`;
- `/auth/mfa/recovery/totp/replace/start`;
- `/auth/mfa/recovery/totp/replace/confirm`.

Each route uses the existing bounded throttles:

- `throttle:5,1`;
- `throttle:20,60`.

The recovery restricted-session TTL remains exactly **600 seconds** through the existing authentication-recovery configuration boundary.

No Sprint35 route is activated for Technical Preview or Production.

## Recovery and factor-replacement semantics

The published Sprint35 contract preserves the following security properties:

- recovery authority is tenant/identity scoped and factor-epoch bound;
- recovery proof is single-use and replay/concurrency fail-closed;
- restricted recovery state is not a normal/full authenticated session;
- caller input does not become authority for tenant, identity, factor epoch, role, permission, organization, outlet, device, protected state, or recovery ownership;
- replacement uses the existing canonical privileged TOTP cryptographic engine rather than custom TOTP/HMAC/Base32 implementation;
- a newly generated replacement factor must be proven before durable activation;
- durable replacement is update-only;
- the old `factor_epoch` is checked and the epoch advances exactly once on successful replacement;
- remaining dedicated privileged-TOTP recovery codes are revoked after successful replacement;
- secret-free factor-replacement audit evidence is recorded;
- successful replacement invalidates restricted recovery/session state and requires a fresh normal login followed by the canonical TOTP challenge;
- replacement does not auto-login and does not synthesize MFA or privileged step-up authority.

TOTP secret material remains Restricted. Plaintext TOTP secrets or recovery secrets are not durable audit material.

## Password and recovery separation

Sprint35 does not alter the published Sprint34 password credential authority:

- password `credential_epoch` remains in `oneqay_identity_password_credentials`;
- authenticated password change remains update-only and fresh-login-required;
- Sprint33 password recovery remains recovery-specific;
- privileged-TOTP recovery does not fabricate password recovery proof or `password_reset_completed` evidence;
- password recovery does not become a TOTP factor-replacement path.

## Qualification and preservation state

The clean-rebased Sprint35 source head completed the dedicated Sprint35 recovery regression and the full triggered cross-cutting preservation matrix successfully before Ready and merge authority.

The dedicated qualification verifies, among other things:

- frozen Sprint35 source-envelope compliance;
- byte-preservation of the historical regression inputs reserved by the 19-path gate;
- immutability of migrations #1–#11;
- PHP syntax/runtime viability;
- executable Sprint35 recovery behavior;
- preservation of canonical Sprint30 TOTP behavior;
- full application regression;
- disabled activation defaults.

The separately published PR #223 compatibility correction remains part of canonical CI/governance history and must not be reverted casually. It exists so historical workflows can recognize the exact Sprint35 successor shape while remaining fail-closed for unknown successor shapes and while isolating only migrations newer than the historical regression under test where necessary.

## Runtime and production boundaries preserved

The following boundaries remain unchanged by Sprint35 and by this reconciliation:

- `ONEQAY_PERSISTENCE_ENABLED=false` remains the source default;
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default;
- privileged TOTP MFA remains source-default disabled unless separately armed in the bounded runtime;
- Technical Preview remains **`NO_SCHEMA_CHANGE`**;
- Production remains **`NO-GO / NOT AUTHORIZED`**;
- updater remains **`DISABLED / UNWIRED`**;
- deployment remains **NOT AUTHORIZED**;
- release remains **NOT AUTHORIZED**.

Sprint35 publication does not activate schema, authentication recovery, privileged TOTP recovery, or factor replacement in Technical Preview or Production.

## Post-Sprint35 next-stage boundary

This canonical reconciliation selects **no Sprint36 implementation concern**.

It does not assume migration #13 is needed. It does not select admin password overwrite, additional MFA factors, passkeys/WebAuthn, federation, API-token authentication, support bypass, impersonation, email/SMS recovery, Preview activation, Production activation, updater activation, deployment, or release as the next concern.

After this reconciliation is separately qualified and published, the next governed activity is **NEXT-CONCERN SELECTION / SPRINT36 ENTRY GATE** based on the then-current repository evidence.

Any Sprint36 source work, migration #13 decision, workflow correction, Preview/Production change, updater change, deployment, or release requires new separately bounded Product Owner authority.

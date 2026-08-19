# Sprint 32 — Authentication Recovery / JRN-003 Recovery Proof Foundation — Entry Gate

> **Status:** ENTRY GATE / DOCUMENTATION-ONLY / NO SOURCE AUTHORITY
> **Canonical baseline:** `1a61dbc35a8c945066aa7b12c5e5177839973396`
> **Canonical baseline tree:** `df1e7b54cd9a658d0557c9c6e3b766e5b2c859be`
> **Canonical product:** oneQay
> **Repository:** `labzefry/oneQay`
> **Attribution:** Lab | zefry

## 1. Purpose

This gate establishes the bounded Sprint 32 candidate **Authentication Recovery / JRN-003 Recovery Proof Foundation** after publication of Sprint 31 Privileged Reauthentication / Step-Up Session Freshness Foundation and the post-Sprint31 canonical checkpoint.

DEC-006 classifies identity/MFA recovery as a high-risk security flow and explicitly leaves JRN-003 unresolved for separate Product Owner resolution. The current canonical schema also has no verified email/phone recovery channel and no dedicated durable recovery-capability record.

Sprint 32 therefore does **not** attempt a complete password-reset or MFA-recovery implementation. Its first slice is limited to a user-held, single-use recovery proof that can establish a restricted recovery session without creating a full authenticated session and without changing a password or MFA factor.

This document creates **no application-source authority**. A separate source-envelope gate must be published before any implementation.

## 2. Canonical facts preserved through Sprint 31

Sprint 32 must preserve all of the following without reinterpretation:

- Sprint 21–31 governed identity/control foundations are published within bounded authority.
- Canonical source migrations are exactly **#1 through #9**.
- `ONEQAY_PERSISTENCE_ENABLED=false` remains the source default.
- `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED=false` remains the source default.
- `ONEQAY_PRIVILEGED_STEP_UP_ENABLED=false` remains the source default.
- Sprint 30 TOTP verification remains replay-safe and uses `spomky-labs/otphp` **11.5.0** rather than custom TOTP/HMAC/Base32 cryptography.
- Sprint 31 privileged step-up remains Local/Test/CI only, scoped to `policy_administration`, and fixed at 300 seconds.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Password change/reset/recovery, MFA recovery, factor replacement/deletion, multiple factors, passkeys, federation, API-token authentication, support impersonation, Preview auth activation, and Production auth activation remain separately governed unless explicitly selected below.

## 3. JRN-003 decomposition

JRN-003 covers user invitation, role delegation, and access recovery.

Published Sprint 23–25 foundations already provide bounded technical foundations for tenant administration, protected-control administrator lifecycle, and policy-administration delivery. Sprint 32 addresses only the still-unresolved **access-recovery proof** portion.

Sprint 32 does not reinterpret or broaden invitation, role, permission, tenant-membership, organization, outlet, device, or policy-administration authority.

JRN-003 as a whole remains **PARTIALLY RESOLVED / NOT COMPLETE** after this entry gate because password reset execution, MFA-factor recovery, privileged-account recovery, and post-recovery session revocation remain separately gated.

## 4. Selected first recovery slice

The first Sprint 32 recovery slice is:

**User-held single-use recovery code -> restricted recovery session.**

It is intentionally not a password-reset operation.

Eligible identity state for this first slice:

- existing tenant-scoped identity;
- existing first-party password credential;
- not currently a protected-control principal;
- no confirmed privileged TOTP factor;
- identity and required durable access state remain valid when checked by the server.

If an identity is protected-control, has a confirmed privileged TOTP factor, or otherwise falls into privileged recovery, Sprint 32 must fail closed with generic recovery failure. Privileged recovery remains separately governed.

## 5. Recovery-code trust model

Sprint 32 selects **pre-provisioned user-held recovery codes** because the canonical schema does not currently provide a verified email/phone recovery channel and DEC-006 requires recovery not to depend on an informal support bypass.

Required semantics:

- codes are generated server-side using a cryptographically secure random source;
- codes have high entropy and are treated as Restricted authentication material;
- plaintext codes are shown only at issuance/rotation time and are never persisted;
- durable storage contains only non-reversible code-verification material and bounded metadata;
- every code is single-use;
- successful verification atomically consumes exactly one code;
- concurrent use of the same code has at most one winner;
- codes are not tenant authority, role authority, organization authority, or full-session authority;
- support personnel cannot mint, read, retrieve, or bypass recovery codes through an informal administrative path;
- security questions, shared secrets, static master codes, hidden superuser recovery, and plaintext recovery-code storage are prohibited.

Exact code count, byte length, printable encoding, selector format, and digest construction must be frozen by the future source-envelope gate. The implementation must reuse approved framework/runtime primitives or the published one-time-token digest pattern rather than inventing custom cryptography.

## 6. Recovery-code issuance and rotation boundary

A future Sprint 32 source candidate may expose an authenticated recovery-code issuance/rotation operation only for the identity represented by the current full first-party session.

Minimum controls:

- Local/Test/CI only;
- full authenticated first-party session required;
- identity/tenant/context are server-derived and cannot be selected by request input;
- fresh password re-verification required before issuing or rotating codes;
- protected-control or confirmed-TOTP identity is ineligible in Sprint 32;
- rotating codes atomically invalidates all previously unused recovery codes for that identity;
- returned plaintext codes are emitted once only;
- logs, exceptions, audit payloads, fixtures, and responses other than the one-time issuance response must never contain plaintext recovery codes.

The exact route is not source-authorized by this entry gate. The source-envelope gate must freeze it before implementation.

## 7. Recovery verification boundary

A future Sprint 32 recovery verification operation must accept only the opaque recovery capability material required by the selected code format.

It must not accept client-controlled role, permission, protected-control status, organization, outlet, device, or recovery scope.

Successful verification must:

1. locate the intended durable recovery-code record using the frozen opaque selector semantics;
2. verify the secret portion without plaintext durable storage;
3. re-check that the target identity remains eligible for this non-privileged first slice;
4. atomically consume the selected code;
5. rotate/invalidate the current browser session state;
6. establish a **restricted recovery session** only; and
7. return generic bounded success metadata without establishing a full login.

Failure must be generic and must not disclose whether an identity exists, whether a code exists, whether the account is protected-control, whether TOTP exists, or which validation failed.

## 8. Restricted recovery session

Sprint 32 recovery proof creates restricted server-side session evidence only.

Candidate semantics to be frozen by the source-envelope gate are equivalent to:

- pending recovery tenant identity binding;
- pending recovery identity binding;
- recovery state exactly `password_reset_required`;
- recovery proof timestamp written only by the server;
- bounded expiry not longer than **10 minutes**.

The restricted recovery session must contain no canonical full-session organization/outlet/device context and must not write the five Sprint 27 full-session keys.

It must not write `mfa_verified_at` or Sprint 31 step-up evidence.

It grants no role, permission, tenant membership, protected-control status, policy-administration access, updater authority, deployment authority, or application business access.

## 9. No password reset in Sprint 32

Sprint 32 intentionally stops after restricted recovery proof.

This sprint does **not** authorize:

- setting or replacing the password credential;
- password overwrite;
- password change;
- password reset completion;
- automatic login after recovery proof;
- full-session establishment after recovery proof;
- password credential deletion;
- credential rotation outside the future separately governed reset flow.

A later recovery-execution gate must independently define password-reset semantics and prove the DEC-006 requirement for material-recovery session revocation or mandatory re-evaluation before any password is changed.

## 10. Privileged and MFA recovery remain excluded

Sprint 32 first slice does not recover protected-control identities and does not recover MFA factors.

The following remain separately governed:

- TOTP recovery codes used as an MFA replacement;
- TOTP secret replacement;
- TOTP reset/deletion;
- multiple-factor lifecycle;
- protected-control account recovery;
- tenant owner / high-value account recovery;
- support-assisted recovery;
- break-glass recovery;
- cooling periods or multi-party approval for privileged recovery;
- passkeys/security keys;
- federation recovery.

A recovery code from Sprint 32 must never silently become a substitute for privileged TOTP or step-up authentication.

## 11. Schema classification

Fresh canonical schema verification shows no dedicated recovery-capability table and no verified email/phone recovery channel.

The selected durable single-use recovery-code capability therefore requires **new additive durable state** if source implementation is later authorized.

Entry-gate classification:

**SCHEMA CHANGE REQUIRED FOR THE SELECTED RECOVERY-PROOF SOURCE TARGET / NOT YET AUTHORIZED.**

Rules:

- migrations #1–#9 remain immutable;
- migration #10 is only the next candidate migration identifier and does not exist yet;
- this entry gate does not authorize creating migration #10;
- the future source-envelope gate must freeze the exact additive recovery table/columns/indexes/FKs and forward-only behavior before any migration file is created;
- existing initial-password-enrollment or TOTP-factor tables must not be repurposed for recovery-code storage merely to avoid a new migration;
- Technical Preview remains `NO_SCHEMA_CHANGE`; any Sprint 32 migration, if later authorized, remains Local/Test/CI only and must not be applied to Technical Preview or Production.

## 12. Security and abuse invariants

Any future Sprint 32 source candidate must prove at minimum:

- plaintext recovery codes are never persisted;
- code verification is single-use and replay-safe;
- concurrent same-code use has at most one winner;
- rotating codes invalidates prior unused codes atomically;
- a code cannot redirect recovery to another tenant or identity;
- protected-control identities cannot enter this first-slice recovery path;
- confirmed-TOTP identities cannot enter this first-slice recovery path;
- successful code proof never establishes full authentication;
- restricted recovery session cannot access protected application routes;
- restricted recovery session cannot mutate password or MFA state in Sprint 32;
- generic failure and anti-enumeration behavior is preserved;
- throttling applies to issuance/rotation and recovery verification;
- recovery codes, password values, credential hashes, TOTP codes/secrets, encryption keys, and reusable secrets are absent from logs and tracked fixtures;
- Technical Preview and Production remain unaffected;
- updater remains disabled/unwired.

## 13. Audit boundary

Sprint 32 must create security audit evidence without storing the secret itself.

Audit data may include bounded facts such as event type, tenant/identity references, server timestamp, correlation ID, success/failure class where safe, and recovery-code record identifier or generation identifier.

Audit data must not include plaintext code, code secret portion, password, password hash, TOTP code, TOTP secret, provisioning URI, encryption key, or hidden bypass material.

Exact audit persistence must be frozen by the source-envelope gate and must not silently reuse unrelated mutation journals with incompatible semantics.

## 14. Runtime boundary

Sprint 32, if later implemented, remains bounded to:

**Local / Test / CI only.**

Required preservation:

- source-default-disabled dedicated feature arm to be named and frozen by the source-envelope gate;
- false preserves Sprint 31 behavior exactly;
- Preview route exposure is prohibited;
- Production route exposure is prohibited;
- Preview schema mutation is prohibited;
- Production schema/runtime activation is prohibited;
- updater remains disabled/unwired;
- no email/SMS/provider dependency is introduced by this first slice.

## 15. Required source-envelope gate

Before any Sprint 32 source or migration mutation, a separate documentation-only **Sprint 32 source-envelope gate** must be published from the then-current canonical `main`.

That gate must:

1. perform fresh Minimal Delta Verification;
2. enumerate every exact changed path;
3. publish a sorted-path SHA-256 fingerprint;
4. freeze the dedicated feature/config arm and Local/Test/CI runtime behavior;
5. freeze exact issuance/rotation and recovery-verification routes;
6. freeze the exact recovery-code format, entropy, count, selector, digest-verification, expiry, and atomic consumption semantics;
7. freeze the exact restricted recovery-session keys and expiry;
8. freeze the exact eligibility check that excludes protected-control and confirmed-TOTP identities;
9. freeze the exact additive migration #10 schema if migration #10 is selected;
10. freeze audit storage and secret-minimal logging behavior;
11. freeze throttling and generic anti-enumeration responses;
12. freeze dedicated regression and historical preservation workflows;
13. prove that password reset, full login, MFA recovery, factor replacement, support bypass, Preview, Production, updater, passkeys, federation, and API-token scope remain excluded; and
14. create no merge authority for the later source PR.

## 16. Preservation requirements

A future Sprint 32 source candidate must preserve the executable regression chain through Sprint 31, including at minimum:

- tenant isolation;
- organizational context;
- Sprint 21 role/permission policy;
- Sprint 22 policy administration;
- Sprint 23 initial tenant administration;
- Sprint 24 protected-control administrator lifecycle;
- Sprint 25 policy-administration delivery;
- Sprint 26 credential verification;
- Sprint 27 first-party session establishment;
- Sprint 28 initial password enrollment;
- Sprint 29 first-control-principal bootstrap credential foundation;
- Sprint 30 privileged TOTP MFA foundation;
- Sprint 31 privileged reauthentication / step-up foundation;
- Governance Required Checks;
- PHP Foundation Regression;
- M7.1 Application Regression;
- Technical Preview / Production / updater separation.

Historical workflows must not be weakened through wildcard, branch-name, PR-number, file-count-only, or broad successor bypasses.

## 17. Explicit non-authority

This entry gate does **not** authorize:

- Sprint 32 application/source implementation;
- migration #10 creation;
- database/schema mutation;
- password reset/change/overwrite;
- automatic login or full-session establishment from recovery;
- MFA/TOTP recovery;
- factor replacement/reset/deletion or multiple factors;
- protected-control recovery;
- support-assisted recovery or break-glass;
- verified-email/phone schema or email/SMS provider integration;
- passkeys/WebAuthn;
- federation/OIDC/SAML;
- Android/API-token authentication;
- support impersonation;
- updater/release activation;
- Technical Preview recovery/auth activation;
- Production recovery/auth activation;
- deployment, Release, Phase 0 Exit, Sprint 14, or Production authority.

## 18. Publication result if this gate is later authorized and merged

Publication of this document would mean only:

**Sprint 32 Authentication Recovery / JRN-003 Recovery Proof Foundation is SELECTED and its bounded first-slice semantics are frozen for source-envelope design.**

It would not mean source implementation, migration #10, password reset, MFA recovery, privileged recovery, or Production/Preview activation is authorized.

The next action after publication would be a fresh documentation-only Sprint 32 source-envelope gate.

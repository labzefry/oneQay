# Sprint 35 — Privileged TOTP Recovery & Factor Replacement Foundation — Entry Gate

Attribution: **Lab | zefry**

## 1. Product Owner direction and exact canonical base

This documentation-only entry gate is prepared as the next governed lifecycle step after the published post-Sprint34 canonical reconciliation.

Exact canonical baseline at preparation time:

- canonical `main`: `e20473402d37f00f729c6ecaefc735e8e838e03c`;
- canonical tree: `eb4de6cd17462d3c25c6b1738d1ae72e7c5bc2f4`;
- selected concern: **Privileged TOTP Recovery & Factor Replacement Foundation**;
- entry-gate preparation: **AUTHORIZED by the instruction to continue to the next stage**;
- application/source implementation: **NOT AUTHORIZED by this gate**;
- workflow-YAML mutation: **NOT AUTHORIZED by this gate**;
- migration #12: **NOT AUTHORIZED / NOT ASSUMED by this gate**;
- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**;
- deployment and release: **NOT AUTHORIZED**.

This gate changes documentation only. It does not authorize source implementation, schema execution, dependency mutation, Preview/Production activation, deployment, release, Ready transition, or merge.

## 2. Why this is the next bounded concern

The current identity/security chain already publishes first-party credentials, login/session establishment, protected-control bootstrap, privileged TOTP MFA, privileged step-up freshness, non-privileged recovery proof/password reset, and authenticated in-session password change.

A remaining high-impact lockout gap is explicit in the published boundaries:

- Sprint30 provides one confirmed TOTP factor per identity but explicitly provides no factor recovery, reset, replacement, deletion, or revocation;
- Sprint32 recovery eligibility excludes an identity assigned to `authorization-policy-administrator` and excludes any identity with a confirmed TOTP factor;
- Sprint33 password-reset completion likewise requires that the identity is not a protected-control principal and has no confirmed privileged TOTP factor;
- therefore the existing password recovery path cannot be repurposed as a privileged MFA bypass.

The selected Sprint35 concern is only the recovery/replacement lifecycle for an already-published privileged TOTP factor. It does not select administrative password overwrite, support master codes, public self-service bypass, multi-factor collections, passkeys, federation, API tokens, Preview/Production authentication activation, updater activation, deployment, or release.

## 3. Target security outcome

The bounded Sprint35 target is:

**provide a separately governed, replay-safe path by which an eligible protected/privileged identity can recover from loss of its confirmed TOTP factor and establish a replacement factor without weakening password authority, tenant/identity binding, protected-control policy, or the requirement for fresh normal login before full privileged authority is restored.**

The recovery path must not become a generic authentication bypass. Possession of one recovery artifact alone must never establish a full session, privileged MFA evidence, step-up evidence, or policy-administration authority.

## 4. Trust-root requirement — blocking design decision

A privileged factor cannot safely be replaced merely because the caller claims the old device is lost.

The later schema/source-envelope gate must freeze an exact recovery trust root before any source mutation. At minimum, the selected design must combine independently governed authority such as:

1. exact tenant + identity binding derived server-side;
2. fresh verification of the existing password credential;
3. a pre-provisioned, one-time, high-entropy recovery capability that was created while the identity still had valid privileged authority; and
4. restricted-session state that cannot be confused with normal authentication, pending ordinary MFA enrollment, password recovery, or step-up state.

The gate must not introduce a support master code, global administrator override, caller-selected tenant/identity, reusable static bypass secret, email/SMS fallback without a separately approved provider/security model, or a path that depends solely on knowledge-based questions.

Whether existing Sprint32 recovery-code infrastructure can be safely generalized for privileged factor recovery, or whether a distinct recovery capability is required, remains **UNRESOLVED by this entry gate**.

## 5. Recovery capability properties

If a user-held privileged MFA recovery capability is selected later, it must be:

- generated only under a fully authenticated privileged session with currently satisfied MFA policy;
- tenant- and identity-bound;
- high entropy;
- persisted only as a non-reversible verifier/digest or otherwise equivalently protected material;
- returned in plaintext only once at generation time;
- single-use for proof;
- individually revocable or wholly rotated through a bounded atomic operation;
- non-logging and non-observable through generic failure behavior;
- unusable to select another tenant, identity, role, permission, factor, recovery scope, or credential row;
- unusable for password reset unless a separately governed authority explicitly says otherwise.

The later design must decide whether privileged recovery capabilities share storage with Sprint32 recovery codes. Silent semantic reuse is forbidden.

## 6. Restricted privileged-recovery session

Successful recovery proof must establish only a restricted server-side state whose sole purpose is replacement-factor enrollment.

A later source-envelope gate must freeze exact session keys and TTL, but the state must conceptually represent only:

- exact server-bound tenant;
- exact server-bound identity;
- recovery purpose = privileged TOTP factor replacement;
- one consumed server-owned recovery capability identifier or equivalent durable proof reference;
- proof time;
- fixed expiration time.

It must contain none of the canonical full-session authority, no `mfa_verified_at`, no privileged step-up evidence, no password-reset authority, and no automatic authorization to policy administration.

Session collision with a normal full session, ordinary pending-MFA state, password-recovery state, or privileged step-up state must fail closed.

## 7. Replacement-factor enrollment boundary

The replacement path must reuse the existing canonical TOTP cryptographic/provider boundary. No custom HMAC, Base32, dynamic truncation, or OTP comparison is authorized.

A replacement factor must use the existing governed TOTP profile unless a separate future decision changes it.

The new raw TOTP secret and provisioning material are Restricted authentication material. They must never be logged, persisted as plaintext, included in audit details, written to CI output, or returned outside the exact restricted replacement-enrollment session.

The replacement factor must not become active merely because a new secret was generated. Confirmation with a valid code from the newly provisioned factor is mandatory before replacement completion.

## 8. Atomic replacement semantics

The later implementation must make final replacement one atomic durable transition.

At minimum it must:

1. revalidate the exact restricted recovery session and its fixed expiry;
2. revalidate the exact tenant, identity, password credential, protected-control state, and current confirmed factor state;
3. revalidate the consumed recovery authority against durable evidence;
4. verify the candidate code against the new replacement secret through the existing TOTP provider boundary;
5. lock all mutable factor/recovery authority needed to prevent double completion;
6. replace or transition the exact existing factor state without creating duplicate active factors;
7. initialize replay protection for the accepted confirmation time step;
8. revoke every remaining privileged factor-recovery capability issued before completion, if that capability model is selected;
9. invalidate any stale privileged authentication/session evidence that could survive factor replacement;
10. emit only secret-free audit/security evidence;
11. invalidate the restricted recovery session and regenerate CSRF;
12. require a fresh normal password login followed by the canonical TOTP challenge using the replacement factor before full privileged authority is restored.

At most one concurrent replacement may succeed for the same starting recovery authority/factor state.

## 9. Password and credential-epoch preservation

Sprint35 is not a password-reset or password-change concern.

The path must not mutate `password_hash` merely because MFA recovery is performed. It must not fabricate `password_reset_completed`, authenticated-password-change semantics, or recovery-password proof.

The later source design must decide how factor replacement invalidates privileged sessions. It may reuse the published credential/session authority only if doing so is semantically correct and does not falsely record a password mutation.

If a distinct MFA/factor epoch or other durable session-invalidation primitive is required, that is a separate schema decision to be frozen before source implementation. Security semantics must not be weakened to avoid schema work.

## 10. Schema decision — migration #12 remains unresolved

Canonical migrations are exactly **#1 through #11** at this gate.

Migration #12 does not exist and is not authorized by this file.

The later schema/source-envelope gate must determine from repository evidence whether the selected privileged recovery design can be implemented safely using existing durable structures or requires an additive migration #12.

Potential schema needs may include, but are not limited to:

- purpose-distinct privileged factor-recovery capability storage;
- secret-free recovery/factor lifecycle audit evidence;
- monotonic factor generation/epoch state for stale-session invalidation;
- replacement lifecycle state that preserves one-active-factor semantics.

No option is selected merely because it is listed here. Existing migrations #1–#11 remain immutable.

## 11. Runtime and feature boundary

Any eventual Sprint35 source delivery remains bounded to **Local/Test/CI** unless a separate lifecycle authority explicitly changes that boundary.

The later gate must freeze whether Sprint35 uses a new source-default-disabled feature arm or extends an existing recovery/MFA feature arm. The design must remain fail closed when required configuration is absent or disabled.

Technical Preview remains **`NO_SCHEMA_CHANGE`** and must not gain Sprint35 factor-recovery routes, factor-replacement execution, or migration #12.

Production remains **`NO-GO / NOT AUTHORIZED`**.

Updater remains **`DISABLED / UNWIRED`**.

## 12. Mandatory threat and abuse cases

The later source-envelope gate must require executable proof against at least:

- recovery artifact brute force and enumeration;
- replay of a consumed artifact;
- concurrent proof/replacement with at most one winner;
- caller-selected tenant/identity or cross-tenant factor replacement;
- public self-bootstrap into protected-control authority;
- replacement without fresh password verification;
- automatic full login after recovery proof or replacement;
- creation of privileged MFA evidence from restricted recovery state;
- recovery-session fixation/collision;
- stale or expired recovery sessions;
- reuse of old TOTP secret after successful replacement;
- reuse of accepted TOTP time steps;
- exposure of recovery codes, new TOTP secret, provisioning URI, password, session id, CSRF token, or factor ciphertext;
- support/admin master-code bypass;
- password mutation as an unintended side effect;
- multiple concurrently active TOTP factors when the canonical model remains one-factor-per-identity;
- Preview/Production route or schema leakage;
- historical Sprint21–34 regression breakage.

## 13. Explicit non-authority

This entry gate does **not** authorize:

- Sprint35 application/source implementation;
- Sprint35 source-envelope fingerprint;
- workflow YAML mutation;
- migration #12 creation, modification, execution, or publication;
- modification of migrations #1–#11;
- privileged factor recovery in Technical Preview or Production;
- password reset/change/overwrite;
- protected-control authorization bypass;
- support/operator master recovery;
- email/SMS recovery delivery;
- factor secret disclosure;
- more than one active TOTP factor per identity;
- passkeys/WebAuthn;
- OAuth/OIDC/SAML/federation;
- API/bearer-token authentication;
- updater activation/wiring;
- deployment or release;
- Phase 0 Exit;
- Ready transition for this entry-gate PR;
- merge authority for this entry-gate PR.

## 14. Next governed lifecycle step

This file is the **Sprint35 selection/entry-gate preparation artifact only**.

If and only if this documentation-only gate is technically qualified and later published under a new exact-head Product Owner Ready/Merge authorization, the next bounded lifecycle step is a separate documentation-only **Sprint35 schema/source-envelope gate**.

That later gate must:

1. freeze the exact privileged recovery trust root and proof factors;
2. freeze whether recovery capabilities are distinct from or safely extend Sprint32 recovery-code infrastructure;
3. freeze exact restricted-session vocabulary and TTL;
4. freeze exact replacement-enrollment and confirmation semantics;
5. resolve session invalidation / factor-generation authority;
6. determine whether migration #12 is required and, if so, freeze its exact additive semantics;
7. freeze exact route, middleware, feature-arm, throttling, and generic-error contracts;
8. freeze exact Application/Infrastructure/Delivery responsibilities;
9. freeze exact changed-file envelope and sorted-path fingerprint;
10. freeze the dedicated Sprint35 regression and full historical preservation chain;
11. preserve Technical Preview, Production, updater, deployment, and release boundaries;
12. explicitly state whether source implementation authority is granted only after that gate is separately published.

PR #215–#218 and their authorities are historical/consumed and grant no standing Sprint35 source, Ready, or merge authority.

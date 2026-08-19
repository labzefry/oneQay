# AI Post-Sprint 31 Canonical State

Status: **CANONICAL STATE CANDIDATE / DOCUMENTATION-ONLY / NO NEW IMPLEMENTATION AUTHORITY**

Date: 2026-08-19

Attribution: **Lab | zefry**

## Purpose

This record captures the factual repository state immediately after publication of Sprint 31 Privileged Reauthentication / Step-Up Session Freshness Foundation. It does not authorize any new source, schema, dependency, runtime, deployment, Release, Technical Preview authentication, Production authentication, recovery, factor-lifecycle, passkey, federation, or API-token implementation.

## Canonical publication

- Sprint 31 source PR: **#203**.
- Authorized exact source head: `f019bf9ca0e1b9375a2750dcdf0169c0d9ae30de`.
- Canonical squash publication on `main`: `fba7b862ff0022de05c9f0ba98b153bd7e399621`.
- Publication tree: `a31d367fc03b836a0266daf92e23f3b9ce3ce995`.
- Publication parent: `ed4ab6ab5798828832c2f9680499242a3b44a5de`.
- GitHub commit verification: **verified / valid**.
- Sprint 31 source envelope: exactly **26 paths**.
- Sorted-path SHA-256: `8136b57a5c9949ec5020a3d5ae497f34431a704900eb0447d42fdb791efb6e39`.
- Exact-head qualification before publication: **23/23 workflows SUCCESS**.

## Published identity and control progression

The governed published progression now includes:

1. Sprint 21 — Durable Role / Permission / Policy Foundation.
2. Sprint 22 — Governed Policy Administration.
3. Sprint 23 — Initial Tenant Administrator Provisioning.
4. Sprint 24 — Protected-Control Administrator Lifecycle.
5. Sprint 25 — Policy Administration Delivery.
6. Sprint 26 — Identity Credential Verification.
7. Sprint 27 — First-Party Login / Session Establishment.
8. Sprint 28 — Initial Password Enrollment.
9. Sprint 29 — First-Control-Principal Credential Bootstrap Foundation.
10. Sprint 30 — Privileged TOTP MFA Foundation.
11. Sprint 31 — Privileged Reauthentication / Step-Up Session Freshness Foundation.

Sprint 21 through Sprint 31 are **COMPLETE / IMPLEMENTED / PUBLISHED** only within their respective bounded authorities.

## Sprint 31 security contract

Sprint 31 publishes a source-default-disabled privileged step-up boundary for the existing policy-administration mutation surface.

- Feature arm: `ONEQAY_PRIVILEGED_STEP_UP_ENABLED=false` by source default.
- Delivery remains bounded to **Local/Test/CI**.
- Freshness is fixed in source at **300 seconds**.
- Successful privileged reauthentication requires both:
  - fresh first-party password verification using the existing tenant-scoped verifier; and
  - the existing Sprint 30 replay-safe confirmed TOTP challenge.
- The reauthentication request accepts only password and six-digit TOTP code after framework CSRF handling; identity, tenant, organization, outlet, device, role, permission, and scope are server-derived rather than client-selected.
- Successful reauthentication rotates the browser session, regenerates CSRF state, rewrites the canonical full-session context, preserves login-level `oneqay.auth.mfa_verified_at`, and writes separate step-up evidence.
- Step-up scope is exactly `policy_administration`.
- Step-up context is bound to the server-derived identity, tenant, organization, outlet, and device context.
- Policy-administration enforcement rechecks current protected-control confirmed-factor state, exact scope/context binding, no-future-clock evidence, freshness not older than 300 seconds, and durable authorization.
- Generic failure semantics remain in force.
- No password hashing, TOTP cryptography, secret storage, or factor lifecycle is duplicated by Sprint 31.

## Session evidence

Published Sprint 31 session evidence is separate from ordinary login MFA evidence:

- `oneqay.auth.step_up_verified_at`
- `oneqay.auth.step_up_scope`
- `oneqay.auth.step_up_context`

`FirstPartySessionKeys::all()` remains the Sprint 27 canonical five-key full-session context contract. Sprint 31 does not redefine identity, tenant, organization, outlet, or device selection.

## Schema and dependency state

- Canonical source migrations remain exactly **#1 through #9**.
- **Migration #10 does not exist and is not authorized by Sprint 31.**
- Sprint 31 introduces no schema change.
- Sprint 31 introduces no Composer or npm dependency mutation.
- The Sprint 30 TOTP dependency remains `spomky-labs/otphp` **11.5.0**.
- oneQay still does not implement custom TOTP/HMAC/Base32 cryptography.

## Preservation and CI state

The Sprint 31 publication also publishes exact-fingerprint successor compatibility in historical M7.2/M7.3/M7.4A/M7.5 and Sprint 21 through Sprint 30 workflows.

- Sprint 30 exact-successor behavior remains unchanged for the exact Sprint 30 envelope.
- Sprint 31 is recognized only by exact **26-path count + canonical fingerprint**.
- No branch-name, PR-number, wildcard, file-count-only, or broad bypass recognition is authorized.
- Historical executable regression, security, dependency-audit, isolation, Technical Preview, Production, and updater checks remain active.
- Dedicated Sprint 31 CI proved the privileged reauthentication route can be exposed only in the intended CI runtime when both MFA and step-up feature arms are enabled, while remaining absent in Technical Preview and Production.

## Runtime and lifecycle boundaries

The following canonical boundaries remain unchanged:

- Technical Preview: **`NO_SCHEMA_CHANGE`**.
- Production: **`NO-GO / NOT AUTHORIZED`**.
- Updater: **`DISABLED / UNWIRED`**.
- Durable persistence source default: `ONEQAY_PERSISTENCE_ENABLED=false`.
- Sprint 29, Sprint 30, and Sprint 31 identity/security delivery remains bounded to **Local/Test/CI** unless separately authorized.

Sprint 31 publication creates no deployment, Release, Production, cPanel, updater, or Technical Preview authentication authority.

## Explicit non-authority

This state record does **not** authorize:

- authenticated password change;
- forgot-password, password reset, or password recovery;
- MFA/TOTP recovery or recovery codes;
- factor replacement, deletion, reset, or multiple factors;
- WebAuthn/passkeys;
- federation or external identity providers;
- API-token authentication;
- support impersonation;
- migration #10 or any schema change;
- dependency changes;
- Preview authentication activation;
- Production authentication activation;
- updater activation;
- deployment, Release, or Production authority.

## JRN-003 and next governed concern

**JRN-003 remains UNRESOLVED.** Sprint 31 intentionally does not create a password-recovery or MFA-recovery path.

Based on the now-published password, session, TOTP MFA, and privileged step-up foundations, the next logical identity/security concern is recorded only as the following **candidate**:

**Authentication Recovery / JRN-003 Resolution Gate**

Authority state: **CANDIDATE / NOT AUTHORIZED**.

This is a sequencing inference for governance planning, not an implementation decision. Before any recovery implementation may begin, a separate bounded gate must resolve the recovery trust model and at minimum freeze:

1. which identities and protected-control principals are eligible for recovery;
2. recovery authority and trust roots without introducing a hidden administrator bypass;
3. password-recovery versus MFA-factor-recovery separation;
4. anti-enumeration and generic failure behavior;
5. one-time/replay-safe recovery capability semantics if any capability is selected;
6. tenant, identity, session, and device/context binding requirements;
7. reauthentication and session invalidation requirements after successful recovery;
8. factor replacement/reset/deletion semantics, if any, as explicit separately governed operations;
9. audit requirements that do not log credentials, TOTP secrets, recovery secrets, or reusable tokens;
10. rate limits, abuse controls, and support-operational boundaries;
11. whether any schema addition is actually necessary rather than assuming migration #10;
12. exact changed-file envelope and dedicated regression/preservation chain;
13. Local/Test/CI versus Technical Preview/Production runtime boundary;
14. explicit exclusions for passkeys, federation, API tokens, support impersonation, updater, deployment, Release, and Production unless separately authorized.

Until such a gate is separately published and authorized, **no recovery implementation is authorized**.

## Live-state rule

For future work, GitHub `main` remains the source of truth. This file is a factual post-Sprint31 checkpoint. Any later canonical publication supersedes its current-state and next-work interpretation while preserving this record as provenance.

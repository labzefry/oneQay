# Sprint 36 — First-Party Session Inventory & Revocation Foundation — Entry Gate

Attribution: **Lab | zefry**

## 1. Product Owner direction and exact canonical base

This documentation-only entry gate is prepared as the next governed lifecycle step after the published post-Sprint35 canonical reconciliation.

Exact canonical baseline at preparation time:

- canonical `main`: `2c1b70de71710d6b3433a5d05bf4f0dc43a116e4`;
- canonical tree: `c5a5532b4d8c3976ccafc071ae36ecf6993a8644`;
- selected Sprint36 concern: **First-Party Session Inventory & Revocation Foundation**;
- entry-gate preparation: **AUTHORIZED** by the Product Owner on the exact canonical base above;
- application/source implementation: **NOT AUTHORIZED by this gate**;
- workflow-YAML mutation: **NOT AUTHORIZED by this gate**;
- migration #13: **NOT AUTHORIZED / NOT ASSUMED by this gate**;
- migrations #1–#12: **immutable published source for this gate**;
- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**;
- deployment and release: **NOT AUTHORIZED**.

This gate changes documentation only. It does not authorize source implementation, schema execution, dependency mutation, Preview/Production activation, updater activation, deployment, release, Ready transition, or merge.

## 2. Why this is the next bounded concern

The published identity/security chain through Sprint35 already provides bounded foundations for:

- durable tenant-scoped role/permission policy;
- protected-control administrator lifecycle;
- first-party credential verification;
- first-party login/session establishment;
- initial password enrollment and first-control-principal bootstrap;
- privileged TOTP MFA;
- privileged step-up freshness;
- password recovery proof and password-reset completion;
- authenticated in-session password change with durable credential epoch;
- privileged TOTP recovery and factor replacement with separate factor epoch.

A remaining security lifecycle gap is session inventory and revocation beyond the current browser/session instance.

Repository evidence at this gate shows:

- the Security Handbook requires logout/revocation and device/session listing for privileged users;
- the published first-party route set exposes `POST /auth/logout`, but no session-inventory endpoint and no server-owned cross-session revocation endpoint;
- current logout invalidates only the request's current Laravel session and regenerates CSRF;
- the first-party login contract stores verified tenant/identity/organization/outlet/device facts in the current session;
- current source-default Laravel session configuration uses `SESSION_DRIVER=array`, so no durable cross-request session inventory authority is established merely by framework configuration;
- Sprint34 `credential_epoch` and Sprint35 `factor_epoch` provide credential/factor freshness authorities, but neither is a general-purpose first-party session registry.

Therefore the selected Sprint36 concern is the bounded lifecycle foundation for **safe first-party session inventory and revocation**. It is not an administrative password overwrite concern, not a support impersonation feature, not a passkey/federation/API-token concern, and not Production authentication activation.

## 3. Target security outcome

The bounded Sprint36 target is:

**provide a server-authoritative way for an authenticated identity to inspect a privacy-safe inventory of its own active first-party sessions and to revoke bounded session authority without trusting caller-selected tenant/identity ownership, while preserving existing credential epoch, factor epoch, MFA, step-up, tenant isolation, and fail-closed semantics.**

The foundation must prevent session inventory from becoming an information-disclosure channel or an administrative impersonation surface.

## 4. Session authority and ownership requirement

The later schema/source-envelope gate must freeze an exact session-authority model before source mutation.

At minimum:

1. every inventoried/revocable session must be bound server-side to exact tenant + identity ownership;
2. caller-provided tenant or identity must never become ownership authority;
3. a session handle exposed to the browser must be opaque and must not expose raw storage identifiers when that would increase attack surface;
4. current-session determination must be server-derived;
5. a caller may not enumerate or revoke another identity's session by guessing an identifier;
6. tenant membership and organizational context cannot substitute for exact session ownership;
7. revocation state must be authoritative at request time for any protected session operation selected later.

Whether the implementation uses framework-backed durable sessions, a separate first-party session registry, a revocation generation, or another durable primitive remains **UNRESOLVED by this entry gate**.

## 5. Inventory privacy contract

A later implementation may expose only privacy-safe metadata necessary for a user to recognize its own sessions.

Candidate safe metadata may include:

- opaque public session handle;
- server-derived `current` boolean;
- issued/authenticated time;
- last-seen time with bounded semantics;
- expiry time where authoritative;
- safe device label or device identifier already governed by the first-party session contract;
- safe outlet/organization context where appropriate and non-sensitive;
- revocation state.

The later gate must define exact fields and retention.

The inventory must not expose:

- raw Laravel/session-store IDs;
- session cookies or bearer-equivalent secrets;
- CSRF tokens;
- passwords or credential hashes;
- TOTP secrets, provisioning URIs, or recovery codes;
- credential/factor ciphertext;
- unrestricted IP history or fingerprinting data without a separately governed privacy purpose;
- roles/permissions as caller-authoritative state;
- another tenant's or identity's sessions.

## 6. Revocation operations

The later design must separately define at least these semantics:

### 6.1 Revoke one owned non-current session

A fully authenticated caller may request revocation of one exact server-owned session handle belonging to the same tenant + identity.

The operation must be idempotent or otherwise have a stable fail-closed replay contract. It must not disclose whether a guessed foreign session exists.

### 6.2 Current-session revocation

The later gate must explicitly decide whether the single-session revocation endpoint may target the current session.

If current-session revocation is supported, success must invalidate the current session and regenerate CSRF in a deterministic way and must not return a response that assumes the old session remains authoritative.

Ambiguous current-session behavior is forbidden.

### 6.3 Revoke other sessions

A bounded `revoke-other-sessions` operation may be selected later. If selected, it must preserve the exact current session and revoke only other owned sessions atomically or with an equivalent deterministic contract.

For privileged identities this is a high-risk mutation and must reuse existing password/TOTP/privileged-step-up authorities as frozen by the later gate. No new custom authentication primitive is authorized here.

### 6.4 Global sign-out

A true `revoke-all`, including the current session, is a separate semantic choice. This entry gate does not assume it is required. If selected later, current-session invalidation, concurrency, audit, and response behavior must be frozen before source implementation.

## 7. Privileged-session requirements

Privileged identities and protected-control principals require stronger safeguards.

The later gate must decide which revocation mutations require:

- fresh current-password re-verification;
- canonical privileged TOTP challenge;
- existing Sprint31 privileged step-up evidence with the published 300-second freshness boundary;
- or a specific combination based on risk.

The implementation must reuse existing credential verification, TOTP, and step-up services. No custom password comparison, TOTP cryptography, support master code, or caller-asserted privileged flag is authorized.

Session inventory itself must not manufacture MFA or step-up evidence.

## 8. Credential epoch and factor epoch preservation

Sprint36 is not a password or TOTP mutation concern.

Published authorities remain distinct:

- password `credential_epoch` is the durable password credential-freshness authority;
- privileged TOTP `factor_epoch` is the durable TOTP factor-lifecycle authority.

Sprint36 must not increment either epoch merely because a session is listed or revoked unless a later separately governed design proves that such coupling is semantically required. Session revocation must not fabricate password-change, password-reset, TOTP-replacement, MFA, or step-up evidence.

The later design must determine how an inventoried session records/captures credential/factor freshness and how stale sessions are handled without conflating the authorities.

## 9. Concurrency and stale-session behavior

The later source design must be fail-closed under concurrency.

Mandatory semantics to resolve include:

- two concurrent attempts to revoke the same session;
- concurrent revoke-one and revoke-others operations;
- revocation racing with a request using the target session;
- revocation racing with password change/reset;
- revocation racing with TOTP factor replacement;
- a session whose captured credential epoch is stale;
- a privileged session whose factor epoch is stale;
- already-expired or already-revoked sessions;
- current-session rotation occurring while inventory is fetched.

A revoked session must not regain authority through replay, stale cache, or session-store resurrection.

## 10. Durable audit requirement

Session revocation is security-sensitive and must produce secret-free evidence where repository policy requires durable audit.

A later gate must freeze exact audit events and determine whether existing audit structures are sufficient.

Audit must never contain:

- raw session cookie/session ID;
- password;
- TOTP/recovery secret;
- CSRF token;
- credential/factor ciphertext;
- private host paths;
- unrelated tenant data.

Audit correlation must remain tenant-safe and identity-bound.

## 11. Schema decision — migration #13 remains unresolved

Canonical source migrations are exactly **#1 through #12** at this gate.

Migration #13 does not exist and is not authorized by this file.

The later schema/source-envelope gate must determine whether safe session inventory/revocation can be implemented with existing durable primitives or requires an additive migration #13.

Potential schema needs may include, but are not selected merely by being listed:

- a first-party session registry with opaque public handle;
- exact tenant + identity ownership columns;
- issued/last-seen/expiry/revoked timestamps;
- captured credential/factor epoch evidence;
- revocation generation/version state;
- secret-free session lifecycle audit linkage;
- uniqueness/indexing required for bounded ownership queries.

If migration #13 is selected later, migrations #1–#12 remain immutable, migration #13 must be additive/forward-only, rollback behavior must follow repository standards, and Preview/Production execution remains separately unauthorized.

## 12. Runtime and feature boundary

Any eventual Sprint36 source delivery remains bounded to **Local/Test/CI** unless a separate lifecycle authority explicitly changes that boundary.

The later gate must determine whether Sprint36 uses a new source-default-disabled feature arm or extends an existing first-party authentication arm. Absence or disablement of required configuration must fail closed.

Technical Preview remains **`NO_SCHEMA_CHANGE`** and must not gain Sprint36 inventory/revocation routes or migration #13.

Production remains **`NO-GO / NOT AUTHORIZED`**.

Updater remains **`DISABLED / UNWIRED`**.

Deployment and release remain **NOT AUTHORIZED**.

## 13. Candidate delivery surface — not yet frozen

The later schema/source-envelope gate may evaluate a small first-party route set such as:

- authenticated session inventory;
- revoke one owned session;
- revoke other owned sessions;
- optionally revoke all sessions if explicitly selected.

Exact HTTP methods, paths, request/response payloads, throttling, middleware, feature-arm semantics, error codes, CSRF behavior, and privileged step-up requirements are **NOT selected by this entry gate**.

The later gate must freeze a closed payload contract and generic failure behavior before implementation.

## 14. Mandatory threat and abuse cases

The later source-envelope gate must require executable proof against at least:

- caller-selected tenant/identity ownership;
- cross-tenant and cross-identity session enumeration;
- raw session-ID disclosure;
- guessed opaque session-handle probing;
- revoking another identity's session;
- replay of a revocation command;
- concurrent revocation with at most the intended winner/effect;
- stale session use after revocation;
- revoked-session resurrection;
- stale credential-epoch session behavior;
- stale factor-epoch privileged session behavior;
- bypass of privileged reauthentication/step-up requirements;
- current-session ambiguity;
- accidental revocation of all sessions by a revoke-one operation;
- preservation failure when revoke-other-sessions should keep the current session;
- information leakage through status/error differences;
- session inventory leakage of Restricted or Confidential values;
- CSRF bypass on state-changing revocation operations;
- missing or excessive rate limiting;
- Preview/Production route or schema leakage;
- historical Sprint21–35 regression breakage.

## 15. Explicit non-authority

This entry gate does **not** authorize:

- Sprint36 application/source implementation;
- Sprint36 source-envelope fingerprint;
- workflow YAML mutation;
- migration #13 creation, modification, execution, or publication;
- modification of migrations #1–#12;
- database-backed Laravel session activation;
- Technical Preview authentication/session activation;
- Technical Preview schema change;
- Production authentication/session activation;
- Production schema change;
- administrative password overwrite;
- support/admin session takeover or impersonation;
- password reset/change behavior beyond preservation of published semantics;
- TOTP secret mutation or factor replacement beyond preservation of published semantics;
- passkeys/WebAuthn;
- OAuth/OIDC/SAML/federation;
- API/bearer-token authentication;
- updater activation/wiring;
- deployment or release;
- Phase 0 Exit;
- Ready transition for this entry-gate PR;
- merge authority for this entry-gate PR.

## 16. Next governed lifecycle step

This file is the **Sprint36 selection/entry-gate preparation artifact only**.

If and only if this documentation-only gate is technically qualified and later published under a new exact-head Product Owner Ready/Merge authorization, the next bounded lifecycle step is a separate documentation-only **Sprint36 schema/source-envelope gate**.

That later gate must:

1. freeze the exact first-party session authority/ownership model;
2. freeze the opaque public session-handle contract;
3. freeze exact inventory metadata and privacy/retention boundaries;
4. freeze revoke-one/current/revoke-others semantics and decide whether revoke-all exists;
5. freeze privileged reauthentication/TOTP/step-up requirements;
6. freeze credential-epoch and factor-epoch interaction without conflating authorities;
7. resolve concurrency and request-time revocation enforcement;
8. determine whether migration #13 is required and, if so, freeze exact additive semantics;
9. freeze exact routes, closed payloads, middleware, throttling, generic error, CSRF, and feature-arm contracts;
10. freeze exact Application/Infrastructure/Delivery responsibilities;
11. freeze exact changed-file envelope and sorted-path fingerprint;
12. freeze dedicated Sprint36 regression and full historical preservation chain;
13. preserve Technical Preview, Production, updater, deployment, and release boundaries;
14. explicitly state that source implementation requires separate Product Owner authority after that gate is independently published.

PR #224 and all earlier lifecycle authorities are historical/consumed and grant no standing Sprint36 source, Ready, or merge authority.

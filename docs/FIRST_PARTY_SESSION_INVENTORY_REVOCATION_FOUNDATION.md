# First-Party Session Inventory & Revocation Foundation

Attribution: **Lab | zefry**

## Status

Sprint36 implements the bounded Local/Test/CI first-party session inventory and revocation foundation selected by the published entry gate and schema/source-envelope gate.

Canonical source baseline for this implementation is:

`727e667a50b6a38bcb28115f607c5b61a59fd1be`

The implementation is bounded to the exact 23-path source envelope fingerprint:

`ea735f8f5ee06d480863f9d1ba7ae58a91642109963c3f340e8453f3205bb7ae`

Technical Preview remains `NO_SCHEMA_CHANGE`.

Production remains `NO-GO / NOT AUTHORIZED`.

Updater remains `DISABLED / UNWIRED`.

Deployment and release remain unauthorized.

## Durable authority model

Migration #13 creates two additive, forward-only tables:

- `oneqay_identity_first_party_sessions`;
- `oneqay_identity_first_party_session_audit`.

The session registry is the server-owned logical first-party session authority. Laravel's framework session ID remains an implementation detail and is never exposed as an inventory selector or accepted as revocation authority.

Each logical session receives:

- a server-generated internal 32-hex `authority_id`;
- a separate opaque 43-character unpadded base64url `public_handle` generated from 32 random bytes;
- exact tenant and identity ownership;
- organization and optional outlet/device context;
- captured password `credential_epoch`;
- captured privileged TOTP `factor_epoch` when the identity requires confirmed privileged TOTP;
- issued, last-seen, expiry, and monotonic revocation timestamps.

The public handle is only an opaque selector. Possession of a handle never proves ownership or authentication.

## Full-session issuance

Only a canonical full first-party session receives a Sprint36 authority.

Ordinary identities receive an authority only after successful password authentication and server-verified organizational context selection.

Protected privileged identities do not receive an authority at the password-only pending-MFA stage. Their authority is issued only after a confirmed TOTP challenge succeeds. The authority captures both current credential and factor epochs.

Anonymous, pending-MFA, password-recovery restricted, and TOTP-recovery restricted states do not receive full-session authority rows.

## Framework-session separation

The Laravel session stores only the internal logical authority identifier under:

`oneqay.auth.session_authority_id`

A framework session rotation does not create a new logical session automatically. Successful privileged step-up preserves the same logical authority identifier and epoch evidence while rotating the Laravel session.

Successful pending-MFA to full-session TOTP challenge is different: that transition issues a new logical session authority.

## Lifetime and request-time enforcement

The Sprint36 feature arm is:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

The source default is fail-closed. The fixed logical idle lifetime is exactly 7200 seconds and is not environment-configurable.

Durable last-seen/expiry touch is bounded to at most once per 60 seconds per authority.

When the feature is armed, full-session routes use `EnforceActiveFirstPartySessionAuthorityMiddleware`. The middleware requires exact server-owned tenant + identity + authority + context binding and validates:

- authority row exists for the exact owner;
- authority is not revoked;
- current time has not exceeded `expires_at_unix`;
- session credential epoch matches the authority and the current durable credential epoch;
- protected privileged sessions have confirmed TOTP evidence and matching current factor epoch;
- restricted/pending states are absent.

Invalid authority causes local Laravel session invalidation and CSRF regeneration. The response is generic and does not reveal whether invalidation was caused by revocation, expiry, stale credential/factor epoch, malformed local state, or ownership mismatch.

Canonical logout remains safe for stale local state; stale authority enforcement clears the local session rather than preserving authority.

## Inventory contract

Exact endpoint:

`GET /auth/sessions`

The endpoint accepts no caller-selected tenant or identity and returns only active sessions belonging to the exact authenticated tenant + identity.

Each response item contains only:

- opaque `handle`;
- server-derived `current` boolean;
- `organization_id`;
- nullable `outlet_id`;
- nullable `device_id`;
- `issued_at_unix`;
- `last_seen_at_unix`;
- `expires_at_unix`.

The response never exposes internal authority IDs, raw framework session IDs, session cookies, CSRF material, credential/factor epochs, password/TOTP/recovery material, IP history, or browser fingerprint data.

Inventory responses are `Cache-Control: no-store, private`.

## Remote revocation

Exact selective endpoint:

`DELETE /auth/sessions/{public_handle}`

Only an active non-current session owned by the exact authenticated tenant + identity can be selectively revoked. Foreign, guessed, stale, expired, malformed, or already-revoked handles use enumeration-safe behavior.

The exact current session cannot be targeted by the selective endpoint. The stable response semantic directs current-session termination to canonical logout instead.

Exact revoke-others endpoint:

`POST /auth/sessions/revoke-others`

The operation preserves the exact current logical authority and monotonically revokes other active owner authorities. Repeated and concurrent revocation cannot return a revoked authority to active state.

Sprint36 intentionally does not expose `revoke-all`.

## Canonical logout

`POST /auth/logout` remains the only intentional current-session termination operation.

When Sprint36 is armed and a current logical authority exists, logout conditionally marks that authority revoked, records `session_logout`, invalidates the Laravel session, and regenerates CSRF.

## Privileged mutation step-up

Read-only inventory requires only an active full session.

Remote revocation by a protected privileged identity additionally requires canonical fresh step-up scoped exactly to:

`session_control`

Freshness is exactly 300 seconds.

The server-selected reauthentication endpoint is:

`POST /auth/reauthenticate/session-control`

The request accepts only canonical `password` and `code` proof fields. The caller cannot submit a scope field.

`policy_administration` evidence cannot satisfy `session_control`, and `session_control` evidence cannot satisfy policy administration.

Ordinary identities are not assigned an invented privileged proof requirement.

## Credential and factor epoch semantics

Sprint36 observes but never advances password `credential_epoch` or privileged TOTP `factor_epoch`.

A password change/reset that advances credential epoch makes prior session authorities stale at the next enforcement point.

A privileged TOTP replacement that advances factor epoch makes prior protected session authorities stale at the next enforcement point.

Session revocation never masquerades as credential or factor mutation.

## Secret-free audit

Durable audit events are bounded to:

- `session_issued`;
- `session_revoked`;
- `other_sessions_revoked`;
- `session_logout`.

Audit records contain internal authority references, exact owner identity, bounded correlation ID, event type, and timestamp. They do not contain public handles, Laravel session IDs, cookies, CSRF tokens, passwords, TOTP secrets, recovery codes, ciphertext, IP history, or browser fingerprints.

## Migration governance

Migration #13 is additive and forward-only. Its `down()` method throws the repository-standard `LogicException` rollback prohibition.

Migrations #1 through #12 are immutable and are not modified by Sprint36.

Technical Preview schema activation is not authorized. Production schema activation is not authorized.

## Qualification

The dedicated Sprint36 workflow enforces the exact 23-path changed-file envelope and fingerprint, historical migration immutability, PHP syntax, locked Composer dependencies, migration #13 shape, fail-closed feature defaults, standalone Sprint36 behavioral regression, full application regression, and runtime activation boundaries.

The behavioral regression proves ordinary authority issuance, opaque selector separation, inventory privacy/current marking, bounded touch, selective revocation, current-session protection, idempotent effect, revoke-others preservation, credential-epoch invalidation, logout revocation, expiry denial, secret-free bounded audit events, required routes, absence of revoke-all, and forward-only migration behavior.

## Explicit non-goals

Sprint36 does not introduce support/admin impersonation, trusted-device bypass, passkeys, federation, bearer-token authentication, IP-history tracking, browser fingerprinting, password overwrite expansion, TOTP secret replacement expansion, Technical Preview activation, Production activation, updater activation, deployment, release, or Phase 0 Exit.

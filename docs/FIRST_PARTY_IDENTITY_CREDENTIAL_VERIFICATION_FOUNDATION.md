# First-Party Identity Credential Verification Foundation

> Attribution: **Lab | zefry**

## Status

Sprint 26 establishes a bounded **Local/Test/CI-only, read-only first-party password credential verification foundation**.

It does **not** authorize interactive login, session establishment, registration, password enrollment/change/reset, MFA, OAuth/OIDC/SAML, API tokens, recovery flows, or any Production/Technical Preview credential delivery.

## Exact credential ownership

A password credential is owned by the exact composite identity:

```text
(tenant_id, identity_id)
```

`identity_id` is never globally authoritative on its own. The same textual identity ID may exist in multiple tenants and each tenant may have an independent credential.

## Storage contract

Migration `0000_00_00_000007_create_identity_password_credentials.php` adds only:

- `tenant_id` (`string`, length 64)
- `identity_id` (`string`, length 96)
- `password_hash` (`string`, length 255)

The primary key is exactly `(tenant_id, identity_id)` and the same pair is a composite foreign key to `oneqay_identities(tenant_id, id)`.

Only one-way password hashes are permitted. Plaintext passwords, reversible encrypted passwords, hints, recovery answers, tokens, sessions, API keys, OAuth tokens, TOTP secrets, recovery codes, and arbitrary credential metadata are outside this foundation.

Migration #7 is forward-only. Migrations #1-#6 remain immutable. Canonical source recognizes exactly migrations #1-#7 after Sprint 26 publication.

## Application contract

`FirstPartyIdentityCredentialVerifier` exposes only a boolean verification result. `VerifyFirstPartyIdentityCredential` remains framework- and database-independent and rejects empty or unreasonably large password input fail-closed.

Password values are sensitive parameters. They must not be trimmed, lowercased, normalized, logged, echoed, serialized into diagnostics, or returned to callers.

## Infrastructure verifier

`LaravelFirstPartyIdentityCredentialVerifier` is read-only. It performs an exact lookup using both:

- `tenant_id`
- `identity_id`

The verifier contains no credential insert, update, upsert, delete, truncate, schema mutation, or password writer behavior.

`password_verify()` is the verification primitive. Production verifier source does not call `password_hash()`; disposable test setup may create synthetic hashes only for isolated qualification fixtures.

## Generic boolean semantics

The public verification outcome is intentionally generic:

- `true` — the exact tenant + identity credential matches the supplied password.
- `false` — every other outcome.

A `false` result does not reveal whether the identity was absent, the tenant differed, the credential was missing, the password was wrong, the stored hash was malformed, persistence was disabled, or the runtime class was denied.

This foundation therefore does not expose an authentication enumeration oracle through distinct result types or error messages.

## Dummy-hash anti-enumeration path

When no usable credential row is available, the normal verification path still performs one bounded `password_verify()` operation against a fixed, valid, non-authoritative dummy hash before returning `false`.

The same bounded work principle also applies when persistence/runtime access is denied. This is an anti-enumeration hardening measure; it is **not** a claim of perfect constant-time execution.

## Runtime and persistence boundary

Credential verification is authorized only when both conditions are true:

1. `database.oneqay_persistence_enabled == true`; and
2. `oneqay.runtime_class` is one of `local`, `test`, or `ci`.

The repository default remains:

```text
ONEQAY_PERSISTENCE_ENABLED=false
```

`preview` and `production` are denied. Denied runtime/persistence paths fail closed and create no credential authority.

## Provider binding

`AppServiceProvider` binds:

```text
FirstPartyIdentityCredentialVerifier
-> LaravelFirstPartyIdentityCredentialVerifier
```

using the existing application database connection plus:

- `database.oneqay_persistence_enabled`
- `oneqay.runtime_class`

No login provider, session writer, credential writer, protected-control delivery authority, Production bypass, updater authority, or superadmin shortcut is introduced.

## Disposable qualification

`apps/web/tests/identity-credential-verification.php` uses disposable SQLite and synthetic data only. It runs migrations #1-#7 and verifies, at minimum:

- exact tenant + identity + correct password succeeds;
- wrong password fails;
- absent identity fails generically;
- missing credential fails generically;
- identical textual identity IDs remain tenant-isolated;
- alpha and beta credentials cannot cross-authenticate;
- persistence-disabled verification fails;
- Preview fails;
- Production fails;
- malformed stored hash fails;
- empty and oversized passwords fail;
- verification leaves credential rows unchanged;
- production verifier source remains read-only and tenant-scoped.

No real customer credentials are used.

## Technical Preview boundary

Technical Preview remains **NO_SCHEMA_CHANGE**.

Canonical source may contain migration #7 after Sprint 26, but that does not authorize applying migration #7 to the Technical Preview database. Preview release packaging continues to exclude the migration directory, and the credential verifier is not wired into Preview Application/Delivery.

## Production boundary

Production remains **NO-GO / NOT AUTHORIZED**.

Sprint 26 does not authorize Production login, Production credential verification, persistence-by-default, schema deployment, cPanel deployment, or any inference of Production readiness.

## Updater separation

Updater remains **DISABLED / UNWIRED**.

Credential verification grants no update, release, deployment, rollback, host, infrastructure, or platform-superadmin authority.

## Explicitly unresolved future lifecycle

The following concerns remain future, separately governed work:

- interactive first-party login;
- server-side session establishment;
- password enrollment;
- password change/reset/recovery;
- credential lifecycle administration;
- MFA/TOTP/passkeys;
- external identity providers;
- Production credential storage and operational controls.

No future capability is implicitly authorized by this foundation.

# oneQay — Post-Sprint 26 Canonical Program State

## Purpose and authority

This document is the canonical current-state supersession record after Sprint 26 publication.

Older repository documents remain valid historical provenance, but they are not current authority where they conflict with this record or newer GitHub publication evidence.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Verified repository baseline

Fresh post-merge verification established:

- repository: `labzefry/oneQay`;
- canonical branch: `main`;
- verified main SHA: `8048769e7f39ebced372dc9aa945b766e699a00b`;
- verified tree: `891d4eeb9484d91dd8ac626ee68420624c23e666`;
- parent: `b5557138d61563164be601347510a39ec8bb1766`;
- commit: `feat(sprint26): add governed first-party identity credential verification foundation (#182)`;
- GitHub signature: **VERIFIED / VALID**;
- PR #182: **CLOSED / MERGED**;
- final Sprint 26 source head: `95600683cff8bbf78dd04f897cf4eaabefece130`;
- Sprint 26 source envelope: exactly **24 changed files**;
- final source relation before merge: **ahead 26 / behind 0**.

These values are publication provenance for this reconciliation baseline. Future lifecycle mutations still require fresh GitHub verification.

## Sprint 26 publication proof

The final exact Sprint 26 source head passed all fourteen triggered pull-request workflows:

- Governance Required Checks — **SUCCESS**;
- PHP Foundation Regression — **SUCCESS**;
- M7.1 Application Regression — **SUCCESS**;
- M7.2 Tenant Isolation Regression — **SUCCESS**;
- M7.3 Identity Organizational Context Regression — **SUCCESS**;
- M7.4A Technical Preview Interaction Regression — **SUCCESS**;
- M7.5 Preview Database Qualification Regression — **SUCCESS**;
- M7.5 Technical Preview Release Artifact — **SUCCESS**;
- Sprint 21 Role Permission Policy Regression — **SUCCESS**;
- Sprint 22 Policy Administration Regression — **SUCCESS**;
- Sprint 23 Initial Tenant Administrator Provisioning Regression — **SUCCESS**;
- Sprint 24 Protected Control Administrator Lifecycle Regression — **SUCCESS**;
- Sprint 25 Policy Administration Delivery Regression — **SUCCESS**;
- Sprint 26 Identity Credential Verification Regression — **SUCCESS**.

The dedicated Sprint 26 workflow proved the exact final 24-file envelope, zero dependency changes, migrations #1–#6 immutability, migration #7 as the only additive migration, Application framework/database independence, the credential schema contract, exact tenant-plus-identity lookup, read-only infrastructure behavior, `password_verify()` use, fixed dummy-hash verification work, Local/Test/CI runtime restriction, persistence default-off, Preview denial, Production denial, no route/login/session writing, and preservation of Sprint 21 through Sprint 25.

Exact-head `product-owner-merge-authority` was **SUCCESS** for `95600683cff8bbf78dd04f897cf4eaabefece130`. The valid authorization comment was authored by repository owner `labzefry` for that exact head.

PR #182 was squash-merged using expected-head protection. No independent review was required under the current Product Owner continuation model.

## Sprint 26 canonical state

**Sprint 26 — Governed First-Party Identity Credential Verification Foundation** is now:

**COMPLETE / IMPLEMENTED / PUBLISHED**.

Sprint 26 provides a Local/Test/CI-only, read-only first-party password credential verification foundation.

Sprint 26 does **not** provide interactive login or session establishment.

## Exact credential ownership

The canonical credential identity is exactly:

`(tenant_id, identity_id)`

`identity_id` is not globally authoritative by itself.

The same textual identity ID may exist under different tenants and each tenant may have an independent credential.

## Credential storage contract

Canonical migration #7 is:

`0000_00_00_000007_create_identity_password_credentials.php`

It creates only:

`oneqay_identity_password_credentials`

with the minimum credential facts:

- `tenant_id` — string length 64;
- `identity_id` — string length 96;
- `password_hash` — string length 255.

The primary key is exactly:

`(tenant_id, identity_id)`

The same pair forms the composite foreign key to:

`oneqay_identities(tenant_id, id)`

The credential relation stores one-way password hashes only.

Plaintext passwords, reversible encrypted passwords, password hints, recovery answers, tokens, sessions, API keys, OAuth tokens, TOTP secrets, recovery codes, and arbitrary credential metadata are outside Sprint 26 authority.

Migration #7 is forward-only.

## Canonical migration set

The canonical repository now contains exactly seven forward-only migrations:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`;
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`;
7. `0000_00_00_000007_create_identity_password_credentials.php`.

Migrations #1–#6 remained immutable during Sprint 26 publication.

No migration #8 exists or is authorized by this reconciliation.

## Application-layer credential contract

`FirstPartyIdentityCredentialVerifier` is the Application contract.

`VerifyFirstPartyIdentityCredential` is the Application verification service.

These Application classes remain framework- and database-independent.

They do not contain Laravel/Illuminate database mechanics, SQL, PDO, HTTP, controller, session, or logging behavior.

Password parameters use PHP `SensitiveParameter` where appropriate.

Password input is not trimmed, lowercased, normalized, echoed, logged, or serialized into diagnostics.

Empty and unreasonably large password input fails closed.

## Infrastructure verifier

`LaravelFirstPartyIdentityCredentialVerifier` is the guarded Infrastructure implementation.

Its credential lookup requires both:

- exact `tenant_id`;
- exact `identity_id`.

It does not query `identity_id` alone.

It remains read-only and contains no credential insert, update, upsert, delete, truncate, schema mutation, or raw SQL write behavior.

The verification primitive is `password_verify()`.

Production verifier source does not call `password_hash()` to persist credentials.

Synthetic disposable tests may create password hashes solely for isolated qualification fixtures.

## Generic boolean and anti-enumeration boundary

The credential verification contract returns only a generic boolean outcome.

`true` means the exact tenant plus identity credential matched the supplied password.

`false` covers all other outcomes, including:

- identity absent;
- wrong tenant;
- credential absent;
- wrong password;
- malformed stored credential;
- persistence disabled;
- runtime denied.

No distinct failure result exposes which credential fact failed.

When no usable credential exists, the normal path still performs one bounded password verification operation against a fixed, valid, non-authoritative dummy password hash before returning `false`.

This is an anti-enumeration hardening measure and is not a claim of perfect constant-time execution.

## Runtime and persistence boundary

Credential verification is authorized only when:

- persistence is enabled; and
- runtime class is `local`, `test`, or `ci`.

The repository default remains:

`ONEQAY_PERSISTENCE_ENABLED=false`

Preview is denied.

Production is denied.

Denied runtime or persistence paths fail closed and create no credential authority.

## No login or session-writing delivery

Sprint 26 added zero routes.

`apps/web/routes/web.php` was not changed by Sprint 26.

Sprint 26 added no:

- login controller;
- login route;
- session writer;
- authentication middleware;
- registration flow;
- password enrollment endpoint;
- password change/reset/recovery endpoint;
- remember-me flow;
- API token flow;
- OAuth/OIDC/SAML flow;
- passkey/WebAuthn flow;
- MFA/TOTP flow;
- credential CLI writer;
- credential background job;
- public credential API;
- credential UI form.

Interactive first-party login/session establishment remains a future, separately governed concern.

## Sprint 21–25 preservation

Sprint 26 preserved the already-published boundaries of Sprint 21 through Sprint 25.

In particular:

- Sprint 21 role/permission evaluation remains read-only, tenant-scoped, exact-match, and deny-by-default;
- Sprint 22 ordinary policy administration remains the mutation authority for its existing vocabulary;
- Sprint 23 initial tenant administrator provisioning remains a separate one-time bootstrap mechanism;
- Sprint 24 protected-control administrator lifecycle remains separate and retains last-control-principal protection;
- Sprint 25 ordinary policy administration delivery remains bounded to its existing server-owned session and CSRF-protected delivery contract;
- protected-control remains unreachable through ordinary Sprint 25 delivery.

Seven preservation tests were updated only as required to recognize canonical migration #7 while retaining their existing assertions.

Nine preservation workflows were updated only as required to recognize the Sprint 26 envelope, migration #7, and the new disposable credential regression while retaining prior security assertions.

## Technical Preview preservation

Technical Preview remains:

**NO_SCHEMA_CHANGE**.

The presence of migration #7 in canonical source does not authorize applying migration #7 to the Technical Preview database.

The final Technical Preview Release Artifact workflow passed deterministic packaging, manifest/checksum, reproduction, first-party regressions, and `NO_SCHEMA_CHANGE` verification.

Preview Application/Delivery source does not wire the Sprint 26 credential verifier.

## Production boundary

Production remains:

**NO-GO / NOT AUTHORIZED**.

Sprint 26 does not authorize:

- Production login;
- Production credential verification;
- persistence-by-default;
- Production schema deployment;
- cPanel deployment;
- release activation;
- Production readiness inference.

## Updater boundary

Updater remains:

**DISABLED / UNWIRED**.

Credential verification grants no update, release, deployment, rollback, host, infrastructure, or platform-superadmin authority.

## Closure audit

Fresh bounded closure verification after PR #182 established:

- PR #182 is merged;
- canonical main is `8048769e7f39ebced372dc9aa945b766e699a00b`;
- canonical tree is `891d4eeb9484d91dd8ac626ee68420624c23e666`;
- canonical parent is `b5557138d61563164be601347510a39ec8bb1766`;
- GitHub signature is verified and valid;
- source publication envelope is exactly 24 files;
- canonical migration directory is exactly #1–#7;
- migration #7 uses `identity_id` length 96 and the same-tenant composite key/foreign-key contract;
- no open issue or pull request matching `Sprint 26` remains;
- canonical code search returned zero `TODO` findings;
- canonical code search returned zero `FIXME` findings;
- canonical code search returned zero `bypass` findings;
- Product Owner merge authority was exact-head and successful;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains separate and disabled/unwired.

## Remaining authentication gap after Sprint 26

Sprint 26 closes the missing first-party credential verification prerequisite identified after Sprint 25.

The repository now has a governed, tenant-bound, read-only password verification primitive for Local/Test/CI qualification.

However, canonical source still deliberately does not establish an authenticated first-party session from those verified credentials.

Therefore the next safe concern is **not** to add broad authentication features, registration, password lifecycle, MFA, OAuth, or Production credential delivery automatically.

## Next bounded engineering concern

The next logical concern is a separately governed **Interactive First-Party Login / Session Establishment Foundation**.

A future entry gate may consider a narrowly bounded Local/Test/CI-only flow that:

- accepts credentials only through a separately authorized first-party login delivery surface;
- delegates credential checking to the published Sprint 26 verifier rather than duplicating password verification;
- establishes only server-owned first-party session state after successful verification;
- preserves CSRF/session fixation protections appropriate to the chosen Laravel session lifecycle;
- preserves exact tenant identity ownership and durable tenant/organization re-verification;
- returns generic authentication failure semantics;
- does not expose credential enumeration facts;
- remains separate from Sprint 23/24 protected-control authority;
- remains separate from updater/platform authority;
- remains Local/Test/CI-only unless a later gate explicitly changes runtime authority;
- keeps Technical Preview `NO_SCHEMA_CHANGE` unless separately authorized;
- keeps Production `NO-GO / NOT AUTHORIZED`.

This reconciliation document does **not** authorize that login/session implementation.

It also does not authorize password enrollment, change, reset, recovery, MFA/TOTP, remember-me, API tokens, external identity providers, Production schema deployment, or migration #8.

Any such source work requires a separately published entry gate with exact source, migration, route, test, workflow, runtime, and security authority.

## Canonical declaration

As of this reconciliation:

- Sprint 21 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 22 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 23 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 24 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 25 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 26 is **COMPLETE / IMPLEMENTED / PUBLISHED**;
- canonical migrations are exactly #1–#7;
- first-party tenant identity password credential verification exists as a read-only Local/Test/CI foundation;
- interactive first-party login/session establishment remains unresolved and unauthorized;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- emergency protected-control recovery remains unresolved and unauthorized;
- next source work requires a separately published entry gate.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

# Sprint 26 Entry Gate — Governed First-Party Identity Credential Verification Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact base: `41f5accb771af36acfa3f45513b664c9fe9357b1`
- Exact base tree: `fffbad7827c81530684ce5b238baa57cc5270f4d`
- Sprint 25: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Post-Sprint 25 canonical reconciliation: **PUBLISHED**
- Production readiness: **NO-GO / NOT AUTHORIZED**

GitHub remains the Single Source of Truth.

This document authorizes **Sprint 26 — Governed First-Party Identity Credential Verification Foundation** for bounded Local/Test/CI implementation after this documentation-only entry gate is published.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless explicitly reactivated. Exact-head Product Owner authority, exact changed-file scope, required CI, tenant isolation, fail-closed runtime controls, and repository protection remain mandatory.

Attribution: **Lab | zefry**

## Why Sprint 26

Sprint 25 established a first-party session-consuming ordinary policy-administration delivery boundary, but intentionally did not create any login or session-establishment path.

Canonical tenant identities currently consist only of exact tenant/identity keys. No first-party password credential relation or verification primitive exists.

A safe interactive login cannot be built by trusting request headers, arbitrary session seeding, environment ownership, hard-coded administrators, Technical Preview synthetic identities, updater authority, or a platform-superadmin shortcut.

Sprint 26 therefore establishes credential **storage and verification only**. Interactive login/session establishment remains a later, separately governed concern.

## Scope decision

Sprint 26 may introduce exactly one first-party credential type:

**password credential**

It does not authorize:

- login/session-writing delivery;
- user registration;
- credential enrollment UI/API;
- password change;
- password reset;
- forgot-password flows;
- email verification;
- remember-me tokens;
- API tokens;
- passkeys/WebAuthn;
- TOTP/MFA enrollment or verification;
- OAuth/OIDC/SAML/external identity providers;
- social login;
- emergency administrator recovery;
- Production activation.

## Exact credential ownership

Every stored credential is bound to exactly:

- `tenant_id`;
- `identity_id`.

The identity must already exist in canonical `oneqay_identities` for the same tenant.

The same textual identity ID may exist in multiple tenants and must have completely independent credentials.

No credential may be globally keyed by identity ID alone.

## Credential storage boundary

Sprint 26 authorizes one additive forward-only migration #7:

`0000_00_00_000007_create_identity_password_credentials.php`

It may create exactly one table:

`oneqay_identity_password_credentials`

The table must contain only the minimum durable credential facts:

- `tenant_id`;
- `identity_id`;
- `password_hash`.

The composite primary key must be:

`(tenant_id, identity_id)`

A composite foreign key must bind `(tenant_id, identity_id)` to canonical `oneqay_identities`.

No plaintext password, reversible encrypted password, password hint, password answer, session token, CSRF token, TOTP secret, recovery code, API key, OAuth token, arbitrary metadata blob, request payload, or real customer data may be stored.

## Hash semantics

Only one-way password hashes produced by the locked PHP/runtime password hashing facility are valid qualification data.

Sprint 26 application source must never call a reversible encryption routine for credentials.

The verifier uses `password_verify()` or the equivalent locked runtime primitive. Sprint 26 does not authorize an application credential writer, so production source does not call `password_hash()` to persist credentials.

Disposable Local/Test/CI regression may generate synthetic password hashes in test setup and insert them directly into the disposable database.

## Read-only verifier

Sprint 26 introduces a dedicated read-only credential verifier.

The Infrastructure verifier must not contain or call:

- `insert`;
- `insertOrIgnore`;
- `update`;
- `updateOrInsert`;
- `upsert`;
- `delete`;
- `truncate`;
- schema mutation;
- raw SQL write statements.

The verifier may only read the exact credential row for the exact `(tenant_id, identity_id)` and perform constant-shape password verification.

## Generic verification outcome

Credential verification returns a bounded boolean outcome only:

- `true` — supplied credential matches the exact stored credential;
- `false` — every other condition.

The caller must not be able to distinguish through the verification contract between:

- identity absent;
- tenant mismatch;
- credential row absent;
- malformed credential row;
- wrong password;
- persistence disabled;
- runtime denied.

No `identity not found`, `wrong password`, `credential missing`, or foreign-tenant oracle is authorized.

## Dummy-hash anti-enumeration path

When no usable exact credential hash exists, the verifier must still execute the password-verification primitive against a fixed non-authoritative dummy hash before returning `false`.

The dummy hash:

- is not an application credential;
- grants no authority;
- is not associated with any tenant or identity;
- must be a syntactically valid password hash;
- exists only to keep the password-verification work shape similar for missing credentials.

The verifier must not return before the dummy verification call merely because the identity/credential row is absent.

This foundation does not claim strict timing equality across all database/cache/platform conditions; it requires the bounded anti-enumeration work factor above.

## Application contract

Sprint 26 authorizes exactly two new Application-layer credential primitives:

1. `FirstPartyIdentityCredentialVerifier`
2. `VerifyFirstPartyIdentityCredential`

The Application contract accepts existing canonical identity types:

- `TenantId`;
- `PlatformIdentityId`;
- a sensitive password string.

The verification service may reject empty or unreasonably large credential input fail-closed, but must not trim, lowercase, normalize, log, serialize, or echo password material.

Use of PHP's built-in `SensitiveParameter` annotation is encouraged for the password parameter.

The Application layer remains framework/database independent.

## Infrastructure verifier

The exact authorized Infrastructure adapter is:

`LaravelFirstPartyIdentityCredentialVerifier`

It must:

- receive the database connection through composition;
- receive persistence-enabled state through composition;
- receive runtime class through composition;
- permit durable credential lookup only for `local`, `test`, or `ci` runtime classes;
- fail closed before database access when persistence is disabled or runtime is denied;
- query only `oneqay_identity_password_credentials`;
- scope every credential lookup by exact tenant and identity;
- execute exactly one real-or-dummy password verification attempt for normal verification flow;
- return only boolean result to the Application contract;
- never log password/hash values;
- remain read-only.

## Composition root

`AppServiceProvider.php` may be modified only to bind:

`FirstPartyIdentityCredentialVerifier` -> `LaravelFirstPartyIdentityCredentialVerifier`

using the existing database connection, `database.oneqay_persistence_enabled`, and `oneqay.runtime_class` boundaries.

Existing provider bindings must be preserved.

No login/session provider, credential writer, Sprint 23 bootstrap authority, Sprint 24 protected-control authority, Production bypass, updater authority, or platform-superadmin binding may be added.

## No delivery surface

Sprint 26 authorizes **zero new routes**.

`apps/web/routes/web.php` is not authorized for modification.

The existing Sprint 25 ordinary policy-administration route remains unchanged.

No controller, middleware, CLI command, job, webhook, GraphQL resolver, REST endpoint, or UI component is authorized to accept passwords during Sprint 26.

## Runtime boundary

The Infrastructure credential verifier must be fail-closed unless both are true:

1. persistence is explicitly enabled;
2. runtime class is one of `local`, `test`, or `ci`.

Preview and Production verification are not authorized.

`ONEQAY_PERSISTENCE_ENABLED=false` remains the default repository environment boundary.

## Tenant isolation

Credential lookup must always use both tenant and identity predicates.

Qualification must prove:

- correct tenant + correct identity + correct password -> `true`;
- same identity text in another tenant with different credential remains independent;
- foreign tenant + otherwise correct password -> `false`;
- absent identity -> `false`;
- missing credential -> `false`;
- no foreign credential facts are exposed.

## No credential mutation authority

Sprint 26 does not define who is authorized to create, replace, rotate, revoke, or reset a password credential in a live system.

That lifecycle is intentionally unresolved.

The disposable regression may seed synthetic credential hashes directly into a disposable SQLite database solely to qualify read-only verification.

A later credential-enrollment/rotation concern requires its own governance decision and must not be inferred from this verifier foundation.

## Canonical migration set after implementation

A successful Sprint 26 source publication will make the canonical forward-only migration set exactly #1–#7:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`;
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`;
7. `0000_00_00_000007_create_identity_password_credentials.php`.

Migrations #1–#6 remain immutable. Sprint 26 may add migration #7 only.

## Technical Preview preservation

Technical Preview remains:

**NO_SCHEMA_CHANGE**.

The existence of canonical migration #7 in source does not authorize schema application to Technical Preview.

The governed Technical Preview release artifact must continue to exclude the migration directory and retain deterministic `NO_SCHEMA_CHANGE` proof.

Sprint 26 credential verification source must not be referenced by Technical Preview Application or Delivery classes.

M7.4A must preserve its corrected canonical migration rule: exact canonical migration set plus zero unauthorized migration diff for sprints that authorize no migrations; for Sprint 26 it may be updated only to recognize the Product Owner-authorized additive migration #7 while retaining the prohibition on database implementation inside Technical Preview source.

## Production and updater preservation

Production remains:

**NO-GO / NOT AUTHORIZED**.

Updater remains:

**DISABLED / UNWIRED**.

Credential verification does not grant policy administration, protected-control, updater, release, deployment, rollback, host, infrastructure, or platform authority.

## No dependency changes

Sprint 26 authorizes no Composer or npm manifest/lockfile changes.

The locked PHP password hashing primitives and existing Laravel/database stack are sufficient for this foundation.

## Exact authorized implementation envelope

After this entry gate is published, Sprint 26 implementation is authorized to change **exactly 24 paths** and no others:

1. `apps/web/app/Application/Identity/FirstPartyIdentityCredentialVerifier.php`
2. `apps/web/app/Application/Identity/VerifyFirstPartyIdentityCredential.php`
3. `apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityCredentialVerifier.php`
4. `apps/web/app/Providers/AppServiceProvider.php`
5. `apps/web/database/migrations/0000_00_00_000007_create_identity_password_credentials.php`
6. `apps/web/tests/identity-credential-verification.php`
7. `.github/workflows/sprint26-identity-credential-verification-regression.yml`
8. `docs/FIRST_PARTY_IDENTITY_CREDENTIAL_VERIFICATION_FOUNDATION.md`
9. `apps/web/tests/authorization-persistence.php`
10. `apps/web/tests/authorization-administration-persistence.php`
11. `apps/web/tests/initial-tenant-administrator-provisioning.php`
12. `apps/web/tests/protected-control-administrator-lifecycle.php`
13. `apps/web/tests/tenant-isolation.php`
14. `apps/web/tests/identity-org-context.php`
15. `apps/web/tests/policy-administration-delivery.php`
16. `.github/workflows/m7-2-tenant-isolation-regression.yml`
17. `.github/workflows/m7-3-identity-org-context-regression.yml`
18. `.github/workflows/m7-4a-technical-preview-interaction-regression.yml`
19. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
20. `.github/workflows/sprint21-role-permission-policy-regression.yml`
21. `.github/workflows/sprint22-policy-administration-regression.yml`
22. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`
23. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`
24. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`

Any newly discovered preservation dependency outside this exact envelope requires a separately published documentation-only preservation supplement before that path may be modified.

## Preservation-test rules

The seven existing tests in the authorized envelope may change only to:

- update exact migration expectations from #1–#6 to #1–#7;
- materialize migration #7 in disposable database setup where all canonical migrations are run;
- assert the credential table's tenant/identity foreign-key boundary where appropriate;
- preserve every prior Sprint 21–25 assertion.

They must not remove, skip, weaken, or convert prior assertions into non-blocking warnings.

## Preservation-workflow rules

The nine existing workflows in the authorized envelope may change only to:

- recognize the exact 24-file Sprint 26 envelope where exact-envelope checks are used;
- trigger on the credential Application/Infrastructure/migration/test/foundation/workflow paths;
- update canonical migration expectations from six to seven;
- execute the Sprint 26 credential regression in addition to prior regressions;
- assert the credential verifier is read-only and tenant scoped;
- assert no credential/login route exists;
- preserve Preview, Production, updater, dependency, Sprint 21–25, tenant-isolation, identity-context, and protected-control boundaries.

## Dedicated Sprint 26 regression

The new workflow:

`.github/workflows/sprint26-identity-credential-verification-regression.yml`

must enforce at least:

- exact 24-file changed-path envelope;
- no dependency changes;
- exactly one additive migration #7 and immutable migrations #1–#6;
- canonical seven-migration set;
- PHP syntax;
- Application credential classes remain framework/database independent;
- migration #7 creates exactly the credential table and minimum columns;
- composite primary key `(tenant_id, identity_id)`;
- same-tenant identity foreign key;
- Infrastructure verifier Local/Test/CI runtime allowlist;
- persistence default-off preservation;
- read-only repository mechanics;
- exact tenant + identity lookup;
- real-or-dummy `password_verify` path;
- no plaintext/reversible credential storage;
- no password/hash logging;
- no route/controller/middleware/session-writing addition;
- correct credential positive control;
- wrong password false;
- missing identity false;
- missing credential false;
- same textual identity across tenants isolated;
- cross-tenant false;
- disabled persistence false before storage access;
- Preview runtime false before storage access;
- Production runtime false before storage access;
- malformed/oversized credential fail closed;
- prior Sprint 21–25 regressions remain green;
- Technical Preview release artifact remains `NO_SCHEMA_CHANGE`;
- updater regressions remain preserved by existing triggering behavior.

## Disposable regression data

The credential regression may use only synthetic values in a temporary SQLite database.

It may generate password hashes using the locked PHP runtime inside the test and seed them directly into `oneqay_identity_password_credentials`.

The test must never print:

- plaintext passwords;
- password hashes;
- session secrets;
- production secrets.

Any synthetic password literal used solely inside the test must not appear in production source or foundation documentation as a real credential.

## Source acceptance gate

A future Sprint 26 source PR is merge-eligible only when all of the following hold on one exact final head:

- canonical base lineage is preserved;
- changed files are exactly the authorized 24 paths;
- branch is behind 0;
- migration diff is exactly migration #7 plus preservation references, with migrations #1–#6 byte-preserved;
- no dependency changes exist;
- all required repository workflows are `SUCCESS`;
- dedicated Sprint 26 workflow is `SUCCESS`;
- no prior assertion is skipped or weakened;
- `product-owner-merge-authority` is `SUCCESS` for the exact final head;
- Product Owner authorization comment names the exact PR and head SHA;
- squash merge uses expected-head protection.

## Explicit exclusions

Sprint 26 does **not** authorize:

- login endpoint;
- session establishment;
- registration;
- password enrollment/change/reset/recovery;
- password writer repository;
- credential administration UI;
- API authentication;
- remember-me tokens;
- passkeys/WebAuthn;
- TOTP/MFA;
- OAuth/OIDC/SAML;
- external identity provider;
- Sprint 23 bootstrap delivery;
- Sprint 24 protected-control delivery;
- emergency recovery;
- new policy authority;
- Preview schema application;
- Production activation;
- updater activation;
- dependency changes;
- migration #8;
- real customer data.

## Entry-gate declaration

After this documentation-only gate is itself published:

- Sprint 26 source authority becomes active only for the exact 24 paths above;
- credential verification is read-only and Local/Test/CI only;
- migration #7 becomes the only authorized schema addition;
- migrations #1–#6 remain immutable;
- interactive login/session establishment remains unresolved and unauthorized;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- emergency protected-control recovery remains unresolved and unauthorized.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

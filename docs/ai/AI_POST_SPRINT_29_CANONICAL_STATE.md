# Post-Sprint 29 Canonical State

## Status

This document is the bounded one-file canonical reconciliation after publication of Sprint 29 — First-Control-Principal Bootstrap Credential Foundation.

Attribution: **Lab | zefry**

Upon publication of this reconciliation, Sprint 29 is:

**COMPLETE / IMPLEMENTED / PUBLISHED**

This reconciliation changes documentation only. It does not change application source, workflow YAML, dependency manifests or lockfiles, routes, migrations, schema, runtime behavior, Technical Preview behavior, Production behavior, updater behavior, credential behavior, or session behavior.

## Canonical publication chain

The governed Sprint 29 publication chain is:

1. PR #191 — Sprint 29 entry gate:
   - `docs(sprint29): define first control principal credential bootstrap gate`
   - publication commit: `54bf91d393dcd99dea1ca7402cab932669da99c4`
2. PR #192 — env-file/source-envelope correction:
   - `docs(sprint29): reconcile gate envelope with env-file governance`
   - publication commit: `5431a186bb7a87b48841c454db121b632a133549`
3. PR #193 — first source candidate:
   - superseded / closed without merge;
   - qualification evidence only;
   - exposed historical preservation-workflow envelope drift and the historical `PersistenceTransaction` domain-violation wrapping behavior.
4. PR #194 — bounded compatibility and implementation correction gate:
   - `docs(sprint29): authorize preservation workflow compatibility`
   - publication commit: `0a346de2686426324e10a5c85b7bc883e1f5d339`
   - authoritative corrected Sprint 29 source envelope: exactly 20 paths.
5. PR #195 — final Sprint 29 source publication:
   - `feat(sprint29): publish first control principal credential bootstrap foundation`
   - exact source head: `7d48ad8a9affb543b6fcce6c5e56b9c6a59c9510`
   - squash publication commit: `9f6e9529aa9fe87e74de9da962a5533e04922e7f`
   - publication tree: `155700629532cd9fc1794c0566afcbf03771d39b`
   - publication parent: `0a346de2686426324e10a5c85b7bc883e1f5d339`
   - GitHub signature: `verified = true`, `reason = valid`.

PR #193 is not canonical source and must remain treated as superseded evidence only.

## Final exact Sprint 29 source envelope

PR #195 published exactly these 20 changed paths relative to the PR #194 correction publication:

1. `.github/workflows/m7-2-tenant-isolation-regression.yml`
2. `.github/workflows/m7-3-identity-org-context-regression.yml`
3. `.github/workflows/m7-5-preview-db-qualification-regression.yml`
4. `.github/workflows/sprint22-policy-administration-regression.yml`
5. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`
6. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml`
7. `.github/workflows/sprint25-policy-administration-delivery-regression.yml`
8. `.github/workflows/sprint26-identity-credential-verification-regression.yml`
9. `.github/workflows/sprint27-first-party-session-establishment-regression.yml`
10. `.github/workflows/sprint28-initial-password-enrollment-regression.yml`
11. `.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml`
12. `apps/web/app/Application/Identity/FirstControlPrincipalCredentialBootstrapRepository.php`
13. `apps/web/app/Application/Identity/FirstControlPrincipalCredentialBootstrapService.php`
14. `apps/web/app/Application/Identity/FirstControlPrincipalCredentialBootstrapViolation.php`
15. `apps/web/app/Infrastructure/Identity/LaravelFirstControlPrincipalCredentialBootstrapRepository.php`
16. `apps/web/app/Providers/AppServiceProvider.php`
17. `apps/web/config/oneqay.php`
18. `apps/web/routes/console.php`
19. `apps/web/tests/first-control-principal-credential-bootstrap.php`
20. `docs/FIRST_CONTROL_PRINCIPAL_CREDENTIAL_BOOTSTRAP_FOUNDATION.md`

The sorted exact-path SHA-256 fingerprint used by the bounded successor compatibility gates is:

`29ab63415c913c80aed0f19eb97d547cc31c60bd38a2484a0e6dc0f3c8341d03`

No `.env` / `.env.*`, dependency manifest/lockfile, migration, Production, Technical Preview activation, deployment, release, or updater source path was part of the source publication.

## Exact-head CI qualification

The final PR #195 source head `7d48ad8a9affb543b6fcce6c5e56b9c6a59c9510` completed every triggered pull-request workflow successfully before merge.

Exact-head result: **21 / 21 SUCCESS**.

Successful workflows were:

1. Sprint 29 First Control Principal Credential Bootstrap Regression
2. PHP Foundation Regression
3. Sprint 26 Identity Credential Verification Regression
4. Sprint 21 Role Permission Policy Regression
5. Sprint 28 Initial Password Enrollment Regression
6. Sprint 25 Policy Administration Delivery Regression
7. Sprint 23 Initial Tenant Administrator Provisioning Regression
8. M7.6 Preview Deployment Recovery Rehearsal Regression
9. Privileged Update Security Regression
10. Sprint 22 Policy Administration Regression
11. M7.1 Application Regression
12. M7.4A Technical Preview Interaction Regression
13. M7.3 Identity Organizational Context Regression
14. M7.5 Technical Preview Release Artifact
15. Governance Required Checks
16. Sprint 24 Protected Control Administrator Lifecycle Regression
17. M7.5 Preview Database Qualification Regression
18. Read-Only Update Deployment UI Regression
19. Backend Updater Control Plane Regression
20. Sprint 27 First-Party Session Establishment Regression
21. M7.2 Tenant Isolation Regression

There were no pending, cancelled, or failed triggered pull-request workflows at final qualification.

The dedicated Sprint 29 workflow proved, among other things:

- exact 20-path envelope;
- zero schema change and exact migrations #1–#8;
- PHP syntax and framework-independent Application boundaries;
- fail-closed feature arming;
- Local/Test/CI-only command registration;
- Sprint 23 durable target derivation;
- transactional revalidation and insert-only credential semantics;
- disposable bootstrap behavior;
- Sprint 21–24 preservation through the proper application harness;
- M7 tenant/identity isolation preservation;
- Sprint 25–28 preservation;
- Technical Preview, Production, and updater separation.

## Product Owner exact-head authority and merge safety

Authenticated Product Owner: `labzefry`.

Exact Product Owner merge authorization was recorded for:

- PR: `#195`
- exact head: `7d48ad8a9affb543b6fcce6c5e56b9c6a59c9510`
- merge authority: `GRANTED`

The repository `product-owner-merge-authority` status became `success` for that exact head before merge.

Immediately before merge, race checks confirmed:

- current `main` still equaled the PR base `0a346de2686426324e10a5c85b7bc883e1f5d339`;
- PR head remained exactly `7d48ad8a9affb543b6fcce6c5e56b9c6a59c9510`;
- `behind_by = 0`;
- PR was mergeable and non-draft;
- changed-file envelope remained exactly the authorized 20 paths;
- all 21 triggered workflows remained successful;
- Product Owner merge authority remained successful.

PR #195 was squash-merged using expected-head race protection against the exact authorized source head.

## Sprint 29 bootstrap security model

Sprint 29 closes only the first-control-principal credential bootstrap circular dependency.

Exact command:

`oneqay:identity:first-control-credential-bootstrap {tenant_id}`

The command is available only when both conditions are true:

1. runtime class is one of `local`, `test`, or `ci`;
2. `ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED=true`.

Source default for the feature arm remains `false`.

No `.env` or `.env.*` file mutation was introduced.

The operator supplies only `tenant_id`. The operator does not supply:

- `identity_id`;
- `role_id`;
- `permission_id`;
- password through a CLI argument;
- bootstrap token;
- alternate target selector.

Password entry is hidden interactive input plus confirmation. Password values remain opaque:

- no trimming;
- no lowercasing;
- no normalization;
- minimum 12 bytes;
- maximum 4096 bytes;
- persisted only as a `PASSWORD_DEFAULT` hash.

Password plaintext is not persisted and must not be logged or exposed.

## Sprint 23 target derivation and protected-control validation

The bootstrap target is derived from durable successful Sprint 23 evidence in:

`oneqay_initial_tenant_admin_provisionings`

for the supplied tenant.

The protected control state is tied to:

- role: `authorization-policy-administrator`;
- permission: `authorization.policy.manage`.

Eligibility revalidates:

- tenant identity binding;
- successful Sprint 23 provisioning evidence;
- exact control role state;
- exact management permission state;
- target protected-control assignment;
- absence of an existing Sprint 26 credential;
- absence of an active Sprint 28 initial-password enrollment.

Arbitrary identity selection and cross-tenant bootstrap are not authorized.

## Transaction and race-safety model

The final implementation corrects the behavior exposed by PR #193 without modifying the shared historical `PersistenceTransaction` adapter.

The approved and published model is:

1. deterministic read-only eligibility/preflight validation runs before `PersistenceTransaction`, preserving bounded Sprint 29 denial semantics;
2. every security-critical eligibility condition is repeated inside the durable transaction immediately before credential insertion;
3. stale/racing transaction-time ineligibility is collapsed to a bounded generic bootstrap failure rather than leaking raw persistence exceptions;
4. credential establishment remains atomic and insert-only;
5. the existing Sprint 26 `(tenant_id, identity_id)` primary key remains the final structural race boundary.

No credential overwrite, update, upsert, delete, truncate, reset, recovery, or alternate mutation path was introduced.

## Sprint 26 credential insertion and Sprint 27 continuation

Successful bootstrap performs exactly one credential insert into:

`oneqay_identity_password_credentials`

using:

`password_hash($password, PASSWORD_DEFAULT)`

A successful bootstrap does not:

- create an authenticated session;
- alter the identity;
- alter roles or permissions;
- alter organizational context;
- create a Sprint 28 enrollment;
- create a recovery credential;
- activate Technical Preview authentication;
- activate Production authentication.

After bootstrap, the target authenticates through the separately published normal Sprint 27 first-party login flow.

Sprint 28 initial-password enrollment remains a separate flow for a different existing same-tenant identity and is not used as the first-control-principal bootstrap mechanism.

## Canonical schema state

Sprint 29 is **NO_SCHEMA_CHANGE**.

Canonical migrations remain exactly:

1. `0000_00_00_000001_create_foundational_context_graph.php`
2. `0000_00_00_000002_create_organizational_access_grants.php`
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`
4. `0000_00_00_000004_create_policy_mutation_journal.php`
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`
7. `0000_00_00_000007_create_identity_password_credentials.php`
8. `0000_00_00_000008_create_initial_password_enrollments.php`

There is no migration #9.

Migrations #1–#7 remain immutable. Migration #8 remains the already-published additive/forward-only Sprint 28 migration.

## Runtime and release boundaries after Sprint 29

The following global boundaries remain unchanged:

- Technical Preview: **`NO_SCHEMA_CHANGE`**;
- Production: **`NO-GO / NOT AUTHORIZED`**;
- updater: **`DISABLED / UNWIRED`**;
- durable application persistence default: `ONEQAY_PERSISTENCE_ENABLED=false`.

Sprint 29 creates no Production credential bootstrap authority, no Technical Preview credential activation, no deployment authority, no GitHub Release authority, and no updater-install authority.

## Historical preservation workflow compatibility

Ten historical preservation workflows gained only bounded successor-envelope awareness for the exact Sprint 29 20-path candidate.

They continue to fail closed for other changed-file sets and retain substantive preservation tests.

The compatibility change does not authorize:

- wildcard future-path acceptance;
- workflow disablement;
- `continue-on-error`;
- status-check renaming;
- dependency-lock weakening;
- migration-boundary weakening;
- Preview/Production/updater weakening;
- Sprint 29 business logic inside historical workflows.

## Remaining explicit non-authority

Sprint 29 does not authorize or implement:

- authenticated password change;
- password rotation;
- password revocation/deletion;
- forgot-password or password reset/recovery;
- emergency protected-control recovery;
- administrative arbitrary password setting;
- credential overwrite/upsert/update/delete;
- MFA/TOTP;
- passkeys/WebAuthn;
- OAuth/OIDC/SAML/federation;
- API/bearer token authentication;
- Production authentication activation;
- Technical Preview credential/login/enrollment activation;
- migration #9 or any assumption that migration #9 is required;
- updater activation;
- deployment or Production release authority.

Each capability remains separately governed and would require its own bounded entry gate before implementation unless a later canonical governance publication explicitly decides otherwise.

## Next governed concern

The source publication closes the previously canonical First-Control-Principal Bootstrap Credential Foundation concern.

The repository does **not yet canonically select or authorize a Sprint 30 concern** in the current published roadmap/task state.

Existing canonical non-authority lists identify multiple future identity/security concerns, including credential change/rotation, reset/recovery, emergency protected-control recovery, and MFA/TOTP, but no one of those is selected by this reconciliation as the next implementation milestone.

Therefore the next-work state after this reconciliation is:

**NEXT GOVERNED CONCERN: UNRESOLVED / NOT YET SELECTED / NOT AUTHORIZED**

A future milestone number, exact trust model, schema decision, changed-file envelope, workflow authority, and implementation scope require a separate Product Owner-governed entry gate after fresh canonical-state verification.

## Canonical closure statement

After this one-file reconciliation is qualified, exact-head Product Owner-authorized, race-checked, and squash-published, the canonical lifecycle statement is:

**Sprint 29 — First-Control-Principal Bootstrap Credential Foundation — COMPLETE / IMPLEMENTED / PUBLISHED.**

Attribution: **Lab | zefry**

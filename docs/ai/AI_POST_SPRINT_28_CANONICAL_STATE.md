# Post-Sprint 28 Canonical State

Attribution: **Lab | zefry**

## Status

**Sprint 28 — Governed First-Party Initial Password Enrollment Foundation: COMPLETE / IMPLEMENTED / PUBLISHED**

This document is the documentation-only canonical reconciliation after publication of Sprint 28. It records the exact source publication boundary and does not authorize additional application, schema, deployment, Production, updater, recovery, reset, rotation, or credential-management work.

## Canonical source publication

- Repository: `labzefry/oneQay`
- Source publication PR: `#188`
- Source publication merge commit: `b012262b0028c21c7662d5a9edec3cbf249bba5e`
- Source publication tree: `e9c68b7f76c8c116c2faed9785dbdc6eb676ea40`
- Source publication parent / Sprint 28 entry-gate publication: `8efd7725e19eb1f15523a8dc2bff7ec8705aa1ce`
- Final qualified source head before squash publication: `40dde76888f67467d3880e03d34a9299573b7175`
- Source changed-file envelope: exactly **33 paths**
- Source publication signature: GitHub verified / valid

The Sprint 28 source branch was ahead of the entry-gate baseline and not behind it at final qualification. No dependency manifest or lockfile change was authorized or published.

## Exact-head qualification

The final candidate head `40dde76888f67467d3880e03d34a9299573b7175` completed all **19 triggered workflows successfully** before Product Owner merge authority was granted:

1. PHP Foundation Regression
2. Read-Only Update Deployment UI Regression
3. Sprint 24 Protected Control Administrator Lifecycle Regression
4. Backend Updater Control Plane Regression
5. Sprint 23 Initial Tenant Administrator Provisioning Regression
6. Sprint 21 Role Permission Policy Regression
7. Sprint 22 Policy Administration Regression
8. M7.5 Preview Database Qualification Regression
9. Sprint 26 Identity Credential Verification Regression
10. Sprint 25 Policy Administration Delivery Regression
11. Privileged Update Security Regression
12. Governance Required Checks
13. M7.2 Tenant Isolation Regression
14. M7.1 Application Regression
15. Sprint 28 Initial Password Enrollment Regression
16. M7.3 Identity Organizational Context Regression
17. Sprint 27 First-Party Session Establishment Regression
18. M7.4A Technical Preview Interaction Regression
19. M7.5 Technical Preview Release Artifact

The dedicated Sprint 28 regression also completed all bounded enforcement steps successfully, including:

- exact 33-path source-envelope enforcement;
- migrations #1 through #7 immutability and migration #8 additive-only enforcement;
- root Platform Foundation regression;
- locked dependency installation without dependency changes;
- PHP syntax validation;
- exact eight-migration set validation;
- Application-layer enrollment boundary enforcement;
- migration #8 secret-minimal schema enforcement;
- secure token and insert-only credential repository enforcement;
- bounded HTTP route and response privacy enforcement;
- Local/Test/CI-only route registration proof;
- Technical Preview, Production, updater, and persistence-default separation;
- disposable initial-password-enrollment regression;
- Sprint 21 through Sprint 27 preservation regressions; and
- tracked-source cleanliness.

## Product Owner merge authority

After exact-head CI was complete, Product Owner merge authority was bound to the exact source head:

```text
PRODUCT OWNER MERGE AUTHORIZATION
PR: #188
EXACT HEAD: 40dde76888f67467d3880e03d34a9299573b7175
MERGE AUTHORITY: GRANTED
```

The repository governance evaluator reported `product-owner-merge-authority = SUCCESS` before merge. A final race-check confirmed that `main` remained on the exact Sprint 28 entry-gate publication, the PR head remained unchanged, the PR was mergeable and non-draft, and the changed-file envelope remained exactly 33 paths. The squash merge used expected-head protection.

## Sprint 28 capability published

Sprint 28 publishes a bounded first-party **initial password enrollment** foundation. It intentionally separates administrator authorization from password selection.

The lifecycle is two-step:

1. an authenticated same-tenant control administrator may issue a short-lived enrollment capability for a different existing target identity; and
2. the target identity redeems that capability and chooses its own initial password.

An administrator does **not** choose or learn the target identity's final password through this foundation.

## Enrollment token security boundary

The published implementation establishes these controls:

- enrollment token entropy is sourced from `random_bytes(32)`;
- the plaintext enrollment token is returned only at issuance time;
- HTTP issuance responses use no-store/no-cache controls;
- only the SHA-256 token digest is persisted;
- supplied token verification is digest-based with constant-time equality checks;
- enrollment tokens are short-lived with a bounded 900-second TTL;
- at most one active initial-password enrollment is permitted for a target identity under the durable invariant;
- consumed enrollment evidence is retained without retaining the plaintext token;
- enrollment delivery contains no token/password logging mechanics.

## Password security boundary

The published implementation establishes these controls:

- password input is marked sensitive at Application/repository boundaries;
- password length is bounded to 12 through 4096 bytes;
- the target chooses its own password during redemption;
- credentials are persisted as `PASSWORD_DEFAULT` password hashes;
- Sprint 28 credential mutation is **insert-only**;
- no password update, upsert, delete, reset, rotation, recovery, or revocation lifecycle is published by Sprint 28;
- redemption does not create or regenerate a login session.

The separately published Sprint 27 first-party login/session establishment flow remains the login mechanism after successful enrollment.

## Authorization and tenant isolation

Enrollment issuance remains same-tenant and protected-control governed:

- the actor must be an authenticated tenant-control administrator;
- authorization is derived from the durable role/permission control state;
- the target must be a different existing identity in the same tenant;
- cross-tenant enrollment is denied;
- self-issuance is denied;
- a target that already has a credential is not eligible for initial enrollment;
- protected-control invariants from earlier sprints remain blocking.

## HTTP delivery boundary

Sprint 28 publishes exactly two bounded enrollment POST endpoints within the authorized runtime class:

- `/administration/identity/password-enrollments` for authenticated control-administrator issuance; and
- `/auth/password-enrollment` for target redemption.

Both remain under normal Laravel web/CSRF processing and throttling. Source-level assertions preserve the exact route names and controller separation. Runtime qualification proves enrollment routes are registered for Local/Test/CI and absent from Preview and Production.

## Canonical migration state

The canonical migration directory after Sprint 28 contains exactly migrations **#1 through #8**:

1. `0000_00_00_000001_create_foundational_context_graph.php`
2. `0000_00_00_000002_create_organizational_access_grants.php`
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`
4. `0000_00_00_000004_create_policy_mutation_journal.php`
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`
7. `0000_00_00_000007_create_identity_password_credentials.php`
8. `0000_00_00_000008_create_initial_password_enrollments.php`

Migrations #1 through #7 remain immutable. Migration #8 is the only Sprint 28 schema addition and is forward-only.

Migration #8 materializes `oneqay_initial_password_enrollments` with tenant-scoped enrollment identity, actor and target identity references, digest-only token material, issuance/expiration/consumption evidence, and the active-enrollment invariant. It does not store plaintext enrollment tokens, plaintext passwords, profile metadata, email, phone, or session data.

## Preservation state

Sprint 28 preserved the earlier governed foundations and their blocking regressions, including:

- durable tenant isolation;
- identity and organizational context;
- durable role/permission policy;
- governed policy administration;
- initial tenant administrator provisioning;
- protected control administrator lifecycle;
- policy administration delivery;
- first-party credential verification;
- first-party login/session establishment;
- Technical Preview interaction and database qualification boundaries;
- privileged updater security boundaries; and
- application/platform foundation regressions.

No prior protected-control assertion was intentionally weakened to publish Sprint 28.

## Technical Preview state

Technical Preview remains:

**`NO_SCHEMA_CHANGE`**

Sprint 28's migration #8 is canonical source schema work, but the Technical Preview release artifact continues to exclude application migrations under its established no-schema-change boundary. Sprint 28 enrollment routes are not active in the Preview runtime class.

Sprint 28 does not expand Technical Preview into real credential enrollment.

## Production state

Production remains:

**`NO-GO / NOT AUTHORIZED`**

Sprint 28 does not authorize Production credential enrollment, Production migration execution, Production authentication activation, Production session activation, real-user rollout, or Production persistence activation.

## Updater state

Updater remains:

**`DISABLED / UNWIRED`**

Sprint 28 does not activate or wire the updater and does not expand updater authority.

## Persistence default

Durable application persistence remains default-disabled:

**`ONEQAY_PERSISTENCE_ENABLED=false`**

Sprint 28's durable enrollment capability remains subject to the established persistence and runtime gates.

## Explicitly unresolved / not authorized

The following concerns remain unresolved or separately governed and are **not** authorized by this reconciliation:

1. **First-control-principal bootstrap credential creation.** A separately governed mechanism is still required for establishing the credential of the first protected/control principal without weakening the tenant-control model.
2. **Password change lifecycle.** Authenticated password changes are not part of Sprint 28.
3. **Password reset/recovery lifecycle.** Forgotten-password recovery, recovery channels, reset tokens, help-desk reset, and emergency credential recovery are not part of Sprint 28.
4. **Password rotation/revocation lifecycle.** Administrative rotation, forced rotation, credential revocation, and credential deletion are not part of Sprint 28.
5. **Production authentication/enrollment activation.** Production remains NO-GO / NOT AUTHORIZED.
6. **Updater activation.** The updater remains DISABLED / UNWIRED.

## Next logical governed concern

The next logical identity concern is **First-Control-Principal Bootstrap Credential Foundation** because Sprint 28 now provides governed initial enrollment for an existing target under an already-authenticated tenant-control administrator, while the first protected/control principal still requires a secure bootstrap path.

That concern requires a new, separately published bounded entry gate before source implementation. This post-Sprint28 reconciliation does not itself authorize that implementation.

## Canonical declaration

With source publication PR #188 merged and this reconciliation lifecycle published, Sprint 28 is intended to be recorded as:

**`COMPLETE / IMPLEMENTED / PUBLISHED`**

The canonical guarantees remain:

- governed two-step first-party initial password enrollment exists for Local/Test/CI only;
- administrator authorization and target password selection are separated;
- plaintext enrollment tokens are not persisted;
- credentials are initial-insert-only;
- canonical migrations are exactly #1 through #8;
- Sprint 21 through Sprint 27 foundations remain preserved;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- persistence remains default-disabled; and
- first-control-principal bootstrap credential creation remains unresolved and separately governed.

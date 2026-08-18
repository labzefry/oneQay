# Sprint 29 Entry Gate — Governed First-Control-Principal Bootstrap Credential Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact base: `4605fd1675938dcce15a46f56209c9eecda9ff7c`
- Exact base tree: `48ae171f2ebcdc3bffac5909734c8b521a1e1d3b`
- Sprint 28: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Post-Sprint28 program-state consolidation: **PUBLISHED**
- Technical Preview: **NO_SCHEMA_CHANGE**
- Production readiness: **NO-GO / NOT AUTHORIZED**
- Updater: **DISABLED / UNWIRED**
- Persistence default: `ONEQAY_PERSISTENCE_ENABLED=false`

GitHub remains the Single Source of Truth.

This documentation-only gate authorizes **Sprint 29 — Governed First-Control-Principal Bootstrap Credential Foundation** for bounded Local/Test/CI source implementation only after this gate is successfully published.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless the Product Owner explicitly reactivates it. Exact-head Product Owner authority, required CI, exact changed-file scope, tenant isolation, fail-closed runtime controls, and repository protection remain mandatory.

Attribution: **Lab | zefry**

## Why Sprint 29 exists

Sprint 23 can create exactly one initial tenant-scoped protected control principal through its separately governed out-of-band provisioning authority, but it intentionally creates no password credential.

Sprint 24 can delegate or revoke protected control assignments only when a tenant-scoped control principal already exists and never removes the final control principal.

Sprint 26 can verify an existing password credential but is read-only.

Sprint 27 can establish a first-party session only after a password credential already exists.

Sprint 28 can issue initial-password enrollment only from an already-authenticated tenant-control administrator and explicitly denies self-enrollment.

Therefore the exact Sprint 23 initial control principal still has a credential bootstrap circular dependency:

1. it needs a credential to log in through Sprint 27;
2. it needs to log in before Sprint 28 can issue enrollment;
3. Sprint 28 cannot self-issue enrollment for that principal.

Sprint 29 closes only that bounded first-credential bootstrap gap.

## Chosen trust model

Sprint 29 uses an **explicitly armed, interactive Local/Test/CI console ceremony**.

It does not add a public bootstrap endpoint, unauthenticated HTTP issuance endpoint, bootstrap URL, email/SMS delivery, static default password, reusable bearer secret, or environment-stored password.

The trust root is the combination of:

- trusted out-of-band console access to the Local/Test/CI application runtime;
- explicit feature arming with `ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED=true`;
- the existing Sprint 23 immutable successful initial-provisioning journal for the exact tenant;
- the existing canonical Sprint 23/24 protected-role graph;
- the existing Sprint 26 credential table primary-key boundary;
- independent Infrastructure revalidation before credential insertion.

The feature flag is an enable/disable control only. It is not a password, bearer token, or substitute credential.

Preview and Production are not authorized console trust roots for Sprint 29.

## Exact console surface

Sprint 29 may add exactly one console command:

`oneqay:identity:first-control-credential-bootstrap {tenant_id}`

The command accepts only the tenant identifier as command input.

It must not accept any of the following as an argument, option, environment value, file value, stdin pipe payload, or configuration field:

- password;
- password hash;
- target identity ID;
- role ID;
- permission ID;
- session/token value;
- updater/release/deployment authority;
- arbitrary SQL or table name.

The password is collected only through an interactive hidden prompt and a second hidden confirmation prompt.

The command must fail closed if a usable interactive password cannot be obtained or if the two inputs differ.

The password must never appear in shell command history, process arguments, application logs, command output, exception output, environment configuration, or repository state.

## Target identity derivation

The command does not accept a target identity.

The exact target must be derived from the existing immutable Sprint 23 row in:

`oneqay_initial_tenant_admin_provisionings`

for the supplied tenant.

The row must remain canonically compatible with Sprint 23:

- `outcome = applied`;
- exact role `authorization-policy-administrator`;
- exact permission `authorization.policy.manage`;
- exact tenant-scoped initial administrator identity.

Knowledge of a tenant ID alone is not application-level authorization to choose an arbitrary identity. The target is repository-derived and cannot be substituted by request/console input.

## Protected-control revalidation

Before any credential mutation, the Infrastructure repository must independently prove all of the following for the exact target derived from Sprint 23:

1. the target identity still exists under the exact tenant;
2. the exact role `authorization-policy-administrator` exists under the tenant;
3. the role carries exactly one permission;
4. that permission is exactly `authorization.policy.manage`;
5. no alternate role carries `authorization.policy.manage` for that tenant;
6. the exact target still has the exact tenant-scoped protected control assignment;
7. the Sprint 23 journal row remains compatible and immutable;
8. no password credential already exists for `(tenant_id, identity_id)`;
9. no active Sprint 28 initial-password enrollment exists for the target.

If the original Sprint 23 principal no longer carries the protected assignment, Sprint 29 fails closed. It must not retarget another control principal and must not become emergency administrator recovery.

## Credential write model

Sprint 29 writes only the existing Sprint 26 credential table:

`oneqay_identity_password_credentials`

It performs exactly one insert for the exact composite identity:

`(tenant_id, identity_id)`

The password is hashed with:

`password_hash($password, PASSWORD_DEFAULT)`

The resulting hash must fit the existing Sprint 26 storage contract.

Sprint 29 must contain no credential update, upsert, replace, delete, rotate, reset, recovery, revocation, or overwrite behavior.

The existing primary key `(tenant_id, identity_id)` remains the final structural race boundary. Concurrent attempts may result in at most one successful insert.

A duplicate/racing attempt must be mapped to a bounded failure and must not expose a raw database uniqueness exception.

## Password policy and secret handling

Sprint 29 reuses the Sprint 28 initial-password bounds:

- minimum 12 bytes;
- maximum 4096 bytes;
- case-sensitive opaque input;
- no trimming;
- no lowercasing;
- no application Unicode normalization;
- no composition/class rule;
- no logging;
- no response echo;
- no session persistence;
- no configuration persistence;
- no journal persistence.

The two hidden prompt values are sensitive parameters and must not be retained after the command completes.

## No new bootstrap token and no migration #9

Sprint 29 explicitly authorizes **NO NEW SCHEMA**.

There is no bootstrap-token table, no additional lifecycle table, and no migration #9.

Canonical source migrations remain exactly **#1 through #8** and all eight remain immutable during Sprint 29.

This is an explicit design decision rather than an omission:

- Sprint 23 already durably identifies the unique initial control principal;
- Sprint 26 already provides the exact credential ownership/storage invariant;
- the Sprint 26 composite primary key provides one-credential structural uniqueness;
- Sprint 29 has no need to persist a plaintext token, token digest, second bootstrap journal, or alternate credential state machine;
- avoiding a second credential-bootstrap table reduces replay state and attack surface.

Any future reason to add migration #9 requires a new separately published schema gate.

## Application boundary

Sprint 29 introduces a framework-independent Application contract for the one-time credential establishment ceremony.

The Application service may depend on:

- `TenantId`;
- `PersistenceTransaction`;
- a dedicated `FirstControlPrincipalCredentialBootstrapRepository`.

It must not depend on Laravel DB, query builder, PDO, `Schema::`, HTTP request/session objects, Artisan internals, filesystem paths, updater internals, or Production infrastructure.

The Application service owns password length validation and maps persistence failures into bounded Sprint 29 violations.

## Infrastructure boundary

`LaravelFirstControlPrincipalCredentialBootstrapRepository` is the only new durable Sprint 29 adapter.

It may read the existing Sprint 23/24 identity/role/permission/assignment/journal state and may insert exactly one row into the Sprint 26 credential table.

It must not mutate:

- identities;
- tenant memberships;
- organizations/outlets/devices;
- roles;
- permissions;
- role-permission relationships;
- protected-control assignments;
- Sprint 23 journal;
- Sprint 24 journal;
- Sprint 28 enrollment rows;
- sessions;
- updater/release/deployment state.

No unrestricted `upsert`, `updateOrInsert`, `replace`, raw password SQL, or direct schema mutation is authorized.

## Explicit feature arming

Sprint 29 may add configuration:

`oneqay.first_control_principal_credential_bootstrap.enabled`

backed by:

`ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED`

Repository/default value must remain `false`.

The console command must be unavailable unless:

- runtime class is exactly `local`, `test`, or `ci`; and
- the explicit Sprint 29 feature flag is true.

The Infrastructure repository independently repeats both the runtime and feature-arm checks together with existing durable persistence enablement.

No permissive default is authorized.

## Atomicity

The existing `PersistenceTransaction` boundary is reused.

Inside the transaction, the repository repeats the critical checks immediately before credential insertion:

- persistence enabled;
- Local/Test/CI runtime;
- Sprint 29 feature armed;
- Sprint 23 exact initial-provisioning evidence;
- same-tenant target identity;
- canonical protected-role state;
- exact target protected-control assignment;
- no existing credential;
- no active Sprint 28 enrollment.

If any check or insert fails, no partial Sprint 29 state exists because Sprint 29 owns no additional durable state beyond the credential row.

## Command result boundary

Successful execution may emit only a bounded sanitized result such as:

`ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP|STATE=applied`

A failure emits only a generic bounded code such as:

`ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_FAILED`

The command must not print:

- password or hash;
- target identity ID;
- internal SQL/database error;
- DSN/host/username/password;
- stack trace;
- role/permission graph diagnostics;
- environment secret values.

Exit code `0` means applied. Non-zero means not applied.

## Session and follow-on behavior

Successful Sprint 29 bootstrap does **not** create an authenticated session.

After bootstrap, the exact target must use the existing Sprint 27 login flow. Sprint 26 remains the password verification authority.

After the first control principal is authenticated, the existing Sprint 28 two-step enrollment flow may be used for a different same-tenant identity under its existing authorization rules.

Sprint 29 must not change Sprint 27 or Sprint 28 route semantics.

## Technical Preview preservation

Technical Preview remains **NO_SCHEMA_CHANGE**.

Sprint 29 must not:

- register the bootstrap command for `preview`;
- enable persistence in Preview;
- expose an HTTP bootstrap endpoint;
- modify Technical Preview sign-in behavior;
- apply or package new schema;
- create a Preview credential;
- alter the deterministic Technical Preview release artifact.

## Production preservation

Production remains **NO-GO / NOT AUTHORIZED**.

Sprint 29 must not:

- register the command in Production;
- activate Production credential persistence;
- establish a Production first control credential;
- enable Production login;
- authorize Production schema execution;
- authorize deployment or GitHub Release;
- infer Production readiness.

A future Production bootstrap mechanism requires an independently governed trust model and operational threat review.

## Updater separation

Updater remains **DISABLED / UNWIRED**.

Sprint 29 bootstrap authority grants no updater, release, deployment, rollback, host, filesystem, cPanel, infrastructure, or platform-superadmin authority.

## Emergency recovery is not Sprint 29

Sprint 29 operates only on the exact initial Sprint 23 control principal before that identity has a password credential.

It is not usable when:

- the Sprint 23 initial-provisioning evidence is absent or incompatible;
- the original initial principal no longer holds the protected control assignment;
- a credential already exists;
- an active Sprint 28 enrollment exists;
- the tenant needs recovery/replacement after loss of all usable control credentials.

Emergency administrator recovery remains unresolved and separately governed.

## Required disposable regression proof

The dedicated Sprint 29 Local/Test/CI regression must prove at minimum:

1. canonical migrations remain exactly #1–#8;
2. no migration #9 exists;
3. command absent in Preview;
4. command absent in Production;
5. command absent/disabled when feature arm is false;
6. persistence-disabled execution fails before credential mutation;
7. exact Sprint 23 initial tenant administrator can be resolved without a target identity argument;
8. missing Sprint 23 provisioning evidence fails closed;
9. incompatible Sprint 23 journal state fails closed;
10. foreign-tenant identity substitution is impossible because no target identity input exists;
11. target identity must still exist under the exact tenant;
12. exact protected role must be canonical;
13. alternate control-role state fails closed;
14. target must still hold exact tenant-scoped protected control assignment;
15. removed/delegated-away initial principal fails closed rather than retargeting another principal;
16. existing credential fails closed and remains unchanged;
17. active Sprint 28 enrollment causes denial and remains unchanged;
18. password shorter than 12 bytes fails;
19. password longer than 4096 bytes fails;
20. password confirmation mismatch fails;
21. password leading/trailing spaces are preserved;
22. valid hidden interactive password creates exactly one credential;
23. stored value is a one-way `PASSWORD_DEFAULT` hash rather than plaintext;
24. Sprint 26 verifier accepts the newly bootstrapped password;
25. Sprint 27 normal login can establish a session after bootstrap;
26. successful bootstrap itself creates no session;
27. repeat bootstrap cannot overwrite the credential;
28. concurrent/duplicate insert is bounded by the existing composite primary key and raw uniqueness errors are not surfaced;
29. Sprint 23 provisioning journal remains byte/logically unchanged;
30. Sprint 24 lifecycle journal remains unchanged;
31. Sprint 28 enrollment state remains unchanged;
32. no role/permission/assignment mutation occurs;
33. same textual identity IDs across tenants remain isolated;
34. password/hash do not appear in command output or durable non-credential tables;
35. no password argument/option/env/config contract exists;
36. no HTTP Sprint 29 route exists;
37. Sprint 21–28 preservation workflows remain green;
38. Technical Preview remains `NO_SCHEMA_CHANGE`;
39. updater remains `DISABLED / UNWIRED`;
40. Production remains denied.

Synthetic identities, passwords, and Local/Test/CI database fixtures only are permitted in regression.

## Dependency boundary

No Composer/npm manifest or lockfile change is authorized.

No new password, console, cryptography, token, identity-provider, queue, cache, mail, SMS, or external service package is required.

## Exact authorized source implementation envelope

After this gate is published, Sprint 29 source implementation is limited to exactly these 11 paths:

1. `apps/web/app/Application/Identity/FirstControlPrincipalCredentialBootstrapRepository.php` — new;
2. `apps/web/app/Application/Identity/FirstControlPrincipalCredentialBootstrapService.php` — new;
3. `apps/web/app/Application/Identity/FirstControlPrincipalCredentialBootstrapViolation.php` — new;
4. `apps/web/app/Infrastructure/Identity/LaravelFirstControlPrincipalCredentialBootstrapRepository.php` — new;
5. `apps/web/app/Providers/AppServiceProvider.php`;
6. `apps/web/config/oneqay.php`;
7. `apps/web/routes/console.php`;
8. `apps/web/.env.example`;
9. `apps/web/tests/first-control-principal-credential-bootstrap.php` — new;
10. `.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml` — new;
11. `docs/FIRST_CONTROL_PRINCIPAL_CREDENTIAL_BOOTSTRAP_FOUNDATION.md` — new.

No other application, test, workflow, configuration, route, migration, documentation, dependency, release, deployment, updater, or Technical Preview file is authorized in the source PR.

## Required preservation chain

The Sprint 29 dedicated workflow must run its disposable regression and the source PR must also preserve all triggered existing governance/application workflows.

At minimum the dedicated regression must explicitly exercise or call the established preservation scripts needed to prove:

- Sprint 21 role/permission policy remains intact;
- Sprint 22 policy administration remains intact;
- Sprint 23 initial tenant administrator provisioning remains intact;
- Sprint 24 protected-control lifecycle remains intact;
- Sprint 25 policy-administration delivery remains intact;
- Sprint 26 credential verification remains intact;
- Sprint 27 login/session establishment remains intact;
- Sprint 28 initial password enrollment remains intact;
- tenant isolation remains intact;
- Technical Preview release/schema separation remains intact.

Existing workflow YAML outside the exact Sprint 29 dedicated workflow is immutable in this source envelope.

## Lifecycle sequence

Sprint 29 must follow this sequence:

1. publish this documentation-only entry gate from the exact canonical base;
2. qualify the gate PR;
3. record exact-head Product Owner authority;
4. race-check and squash-merge the gate;
5. create the Sprint 29 source branch from the exact gate publication commit;
6. implement only the exact 11-path source envelope;
7. run dedicated and triggered preservation CI;
8. require all exact-head checks to succeed;
9. record exact-head Product Owner merge authority;
10. race-check and expected-head squash-merge the source PR;
11. publish a one-file post-Sprint29 canonical reconciliation;
12. only then declare Sprint 29 **COMPLETE / IMPLEMENTED / PUBLISHED**.

No source implementation authority exists before step 4 completes.

## Explicit exclusions

Sprint 29 does not authorize:

- migration #9 or any schema mutation;
- HTTP bootstrap issuance/redemption;
- public self-registration/self-enrollment;
- default administrator password;
- password argument, option, environment, file, or config value;
- administrator-selected password for another identity;
- credential overwrite/update/upsert/delete;
- password change/rotation/reset/recovery;
- emergency protected-control recovery;
- MFA/TOTP/passkeys/WebAuthn;
- recovery codes;
- OAuth/OIDC/SAML/social login;
- API/bearer tokens;
- email/SMS delivery;
- remember-me;
- Production credential activation;
- Preview credential activation;
- updater activation;
- deployment;
- GitHub Release;
- Phase 0 Exit;
- Production readiness promotion.

## Security invariant

> The exact Sprint 23 initial tenant control principal may establish its first password credential only through an explicitly armed Local/Test/CI interactive console ceremony, with the target derived from immutable Sprint 23 evidence, the protected-control graph independently revalidated, the password accepted only through hidden interactive input, and exactly one insert into the existing Sprint 26 tenant-scoped credential table; no HTTP bootstrap, schema change, credential overwrite, alternate target selection, Preview/Production activation, or emergency recovery path is created.

Attribution: **Lab | zefry**

# Durable Authorization Policy Administration Foundation

## Status

Sprint 22 establishes a bounded **Local/Test/CI-only** durable policy administration foundation for oneQay.

Attribution: **Lab | zefry**

Production readiness remains **NO-GO**.

## Scope

Sprint 21 made durable role/permission evaluation read-only and deny-by-default. Sprint 22 adds separately governed mutation mechanics without turning tenant policy into a Production administration product surface.

Authorized operations are limited to:

- tenant-scoped role creation;
- exact non-control permission grant/revoke on a non-protected role;
- exact non-protected role assignment/revocation at tenant, organization, outlet, or device scope.

No role deletion, role rename, inheritance, wildcard permission, negative permission, ABAC, bulk import, business-role catalog, admin UI/API, tenant bootstrap, Preview policy administration, or Production policy mutation is introduced.

## Control authority

The sole policy-administration control capability is:

`authorization.policy.manage`

It is evaluated through the existing Sprint 21 `DurableScopedAuthorizationPolicy` exact-match path.

It does not grant platform-superadmin/updater authority and cannot be substituted by platform-superadmin status.

A role carrying `authorization.policy.manage` is a **protected control role**. Sprint 22 cannot:

- grant or revoke the control permission;
- assign or revoke a protected control role;
- add or remove another permission on a protected control role;
- delete a protected control role.

This prevents the administration service from manufacturing, transferring, weakening, or removing its own authority.

## Bootstrap boundary

Sprint 22 deliberately does not solve first-administrator provisioning.

No implicit Owner/Admin/Superadmin role, first-user elevation, environment superuser, updater privilege reuse, bootstrap token/header, or allow-if-no-admin shortcut exists.

Disposable tests directly pre-provision a synthetic control principal and synthetic protected role as fixture data. That fixture is not runtime behavior and creates no Production authority.

## Trust and scope model

Every mutation requires an existing acting `VerifiedOrganizationalContext`.

Target scope is derived from that verified actor context:

- tenant scope uses actor tenant;
- organization scope uses actor tenant + organization;
- outlet scope uses actor tenant + organization + outlet;
- device scope uses actor tenant + organization + outlet + device.

The target identity is represented by canonical `PlatformIdentityId`, but relational membership/access must already exist for the target at the requested scope.

Raw tenant/organization/outlet/device hints do not establish mutation authority.

## Application components

Sprint 22 adds framework-independent Application authorization administration contracts:

- `AdministrationPermission`;
- `PolicyMutationId`;
- `PolicyMutationOperation`;
- `PolicyAssignmentScope`;
- `DurablePolicyMutation`;
- `DurablePolicyAdministrationRepository`;
- `DurablePolicyAdministrationService`;
- `DurablePolicyAdministrationViolation`;
- `PolicyAdministrationClock`.

The existing Sprint 21 `DurableRolePermissionRepository` remains read-only.

## Mutation identity and replay

Every mutation has a tenant-scoped canonical `PolicyMutationId`.

A SHA-256 payload fingerprint binds the canonical tenant, actor identity, operation, verified scope, target identity where applicable, role, and permission where applicable.

Semantics:

- same tenant + mutation ID + same fingerprint returns the prior deterministic outcome without duplicating the business mutation;
- same tenant + mutation ID + different fingerprint fails closed as a mutation conflict;
- the same textual mutation ID may independently exist under another tenant.

The fingerprint contains no password, TOTP material, token, credential, request header, or arbitrary request body.

## Durable mutation repository

`LaravelDurablePolicyAdministrationRepository` is the only Sprint 22 Laravel policy mutation adapter.

It independently enforces:

- persistence enabled;
- runtime exactly in `local`, `test`, `ci`;
- tenant predicates;
- verified actor scope match;
- protected control role/permission checks;
- target membership/access eligibility;
- idempotency journal conflict detection;
- exact scoped mutation;
- final desired-state readback.

Unrestricted `upsert` and `updateOrInsert` are prohibited.

Insert-if-absent is used only with exact scoped validation/readback. Revocation uses complete tenant + scope predicates.

## Atomic transaction model

The service reuses the existing `PersistenceTransaction` boundary.

Business mutation and durable mutation journal state are committed atomically. Transaction failure must leave neither a partial business change nor a false successful journal record.

Policy-specific preflight checks are performed before entering the generic transaction so bounded policy errors remain classifiable; critical checks are repeated in Infrastructure as defense in depth inside the transaction.

## Migration #4

Sprint 22 adds one forward-only migration:

`0000_00_00_000004_create_policy_mutation_journal.php`

It creates only:

`oneqay_policy_mutations`

The table is tenant-scoped by composite primary key:

`tenant_id + mutation_id`

It also binds `actor_identity_id` to the same tenant through a composite foreign key.

Stored fields are bounded identifiers and mutation evidence only:

- tenant and mutation identity;
- actor identity;
- operation;
- scope type and nullable organization/outlet/device IDs;
- nullable target identity;
- role and nullable permission;
- payload fingerprint;
- deterministic outcome;
- positive Unix occurrence timestamp.

The journal is append-only from Sprint 22 application code.

Migration #4 contains no seed data and `down()` throws the established forward-only rollback denial.

Migrations #1–#3 remain immutable.

## Canonical mutation vocabulary

Operations are exact and closed:

- `role.create`;
- `permission.grant`;
- `permission.revoke`;
- `role.assign.tenant`;
- `role.assign.organization`;
- `role.assign.outlet`;
- `role.assign.device`;
- `role.revoke.tenant`;
- `role.revoke.organization`;
- `role.revoke.outlet`;
- `role.revoke.device`.

Outcomes are exact:

- `applied`;
- `no_change`.

No free-form operation or outcome is accepted.

## Stable failure boundary

Application administration failures use bounded non-sensitive codes for:

- authorization denied;
- persistence disabled;
- runtime denied;
- invalid mutation;
- protected control authority;
- invalid target scope;
- target membership/access denial;
- mutation conflict;
- relationship conflict;
- storage failure;
- transaction failure.

Raw SQL/database exceptions, DSNs, credentials, secret values, and filesystem paths are not surfaced to delivery code.

## Regression model

The dedicated Sprint 22 disposable SQLite regression proves:

- disabled/Preview/Production denial before mutation schema use;
- exact four-migration execution;
- journal creation;
- synthetic control principal authorization;
- deny without exact control permission;
- role create;
- exact grant/revoke;
- tenant/org/outlet/device assign/revoke semantics;
- target membership/access requirements;
- cross-tenant denial and same-ID isolation;
- protected control authority;
- exact replay and conflicting replay behavior;
- mutation ID tenant locality;
- transaction rollback;
- tenant-scoped repository predicates;
- no unrestricted upsert/updateOrInsert;
- preservation of Sprint 21 read-only policy evaluation.

M7.2/M7.3 preserve tenant and organizational isolation. Preview DB qualification remains non-mutating. The unchanged governed Technical Preview release workflow must continue to prove `NO_SCHEMA_CHANGE` and migration exclusion.

## Preview and Production

`ONEQAY_PERSISTENCE_ENABLED` remains false by default.

Durable policy administration is allowed only under `local`, `test`, or `ci` runtime classes.

Preview and Production remain denied.

Technical Preview receives no policy administration endpoint, no durable policy wiring, and no migration execution. Canonical migrations remain excluded from its governed runtime artifact.

Production policy administration, tenant bootstrap, schema execution, deployment, GitHub Release, updater activation, and Production readiness remain **NOT AUTHORIZED**.

Attribution: **Lab | zefry**

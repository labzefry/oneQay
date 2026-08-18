# Protected Control Administrator Delegation Foundation

## Purpose

Sprint 24 introduces a governed Local/Test/CI lifecycle for delegating and revoking the exact tenant-scoped protected control role established by Sprint 23.

It provides continuity for an already-initialized tenant without weakening Sprint 22 protected-control rejection and without making the Sprint 23 one-time bootstrap reusable.

Attribution: **Lab | zefry**

## Canonical protected control identity

The protected control role remains exactly:

`authorization-policy-administrator`

Its sole permission remains exactly:

`authorization.policy.manage`

Sprint 24 may mutate only tenant-scoped assignments of that exact role. It cannot create, rename, repair, broaden, or rewrite the role or its permission relationship.

## Exact lifecycle operations

The closed operation vocabulary is:

- `control.administrator.delegate`;
- `control.administrator.revoke`.

The only deterministic outcomes are:

- `applied`;
- `no_change`.

No generic/free-form lifecycle operation exists.

## Actor authority

A lifecycle actor is represented by `VerifiedOrganizationalContext` and must currently possess durable **tenant-scoped** `authorization.policy.manage` authority for the exact tenant.

Authority is proven from the durable tenant-role assignment graph. Organization-, outlet-, or device-scoped control authority is deliberately insufficient for Sprint 24.

Actor authority is checked before transaction entry and repeated inside the Infrastructure transaction path.

There is no environment superuser, first-user shortcut, implicit Owner/Admin/Superadmin, updater privilege reuse, bearer/header/query/cookie/session authority, or platform-superadmin substitute.

## Verified target identity

Every target is supplied as `VerifiedPlatformIdentity` and canonicalized to `PlatformIdentityId`.

The durable repository independently verifies that the target identity exists under the same tenant as the actor before mutation.

Sprint 24 does not create identities, tenants, memberships, organizations, outlets, or devices.

Same textual identity identifiers may exist under different tenants, but they remain tenant-bound. A target that exists only in another tenant is denied.

## Dedicated protected-control exception

Sprint 22 generic policy administration remains unchanged and continues to reject protected-control assignment and revocation.

Sprint 24 uses the dedicated `ProtectedControlAdministratorLifecycleRepository` and `ProtectedControlAdministratorLifecycleService` only for the exact tenant assignment lifecycle of `authorization-policy-administrator`.

The dedicated path cannot:

- create or rename the role;
- grant or revoke `authorization.policy.manage`;
- add another permission to the protected role;
- assign the role at organization/outlet/device scope;
- mutate ordinary roles;
- invoke or rewrite Sprint 23 bootstrap evidence;
- perform emergency administrator recovery.

## Sprint 23 bootstrap preservation

The Sprint 23 initial-provisioning journal remains immutable historical evidence.

Sprint 24 never calls the Sprint 23 service to create additional administrators. Delegated administrators are created only by inserting the exact protected tenant-role assignment under an already-authorized tenant control principal.

A tenant with no current tenant-scoped control principal cannot invoke Sprint 24. Therefore this foundation is not a recovery mechanism.

## Last-control-principal safety

Sprint 24 never removes the final tenant-scoped durable control principal.

Before revoking an existing protected tenant assignment, the repository counts distinct tenant identities that retain tenant-scoped authority through a role carrying exact `authorization.policy.manage`.

The revocation commits only when at least one distinct tenant-scoped control principal remains afterward.

Consequences:

- sole-principal revocation is denied;
- self-revocation is permitted only when another tenant-scoped control principal remains;
- organization/outlet/device control authority does not satisfy the safety invariant;
- denied last-principal attempts leave no Sprint 24 lifecycle journal row.

The check is performed before transaction entry and repeated inside the transaction immediately before deletion.

## Mutation identifier and replay

Every lifecycle operation uses a canonical `ProtectedControlAdministratorMutationId`.

The identifier is normalized to lower case and accepts only:

`[a-z0-9][a-z0-9_-]{0,63}`

The SHA-256 lifecycle fingerprint binds exactly:

- tenant ID;
- actor identity ID;
- operation;
- target identity ID;
- role `authorization-policy-administrator`;
- permission `authorization.policy.manage`;
- assignment scope `tenant`.

Replay rules:

- same tenant + same mutation ID + same fingerprint returns the prior deterministic outcome;
- same tenant + same mutation ID + different fingerprint fails closed as conflict;
- the same textual mutation ID under another tenant is independent.

The fingerprint contains no credentials, password, TOTP material, bearer token, session value, cookie, request payload, customer contact data, DSN, or secret authority material.

## Delegation semantics

For `control.administrator.delegate`:

1. actor tenant-scoped authority is verified;
2. target same-tenant identity is verified;
3. canonical protected-role state is verified;
4. exact target tenant assignment absent → insert assignment and return `applied`;
5. exact target tenant assignment present → return `no_change` without duplicate state;
6. lifecycle journal and assignment outcome are transaction-bound.

Delegation never removes actor authority and never creates an organization/outlet/device assignment.

## Revocation semantics

For `control.administrator.revoke`:

1. actor tenant-scoped authority is verified;
2. target same-tenant identity is verified;
3. canonical protected-role state is verified;
4. exact target assignment absent → return `no_change`;
5. exact target assignment present → verify last-control-principal safety;
6. when another tenant-scoped control principal remains, delete only the exact target tenant assignment and return `applied`;
7. when the target is the final tenant control principal, fail closed before mutation;
8. lifecycle journal and assignment outcome are transaction-bound.

Revocation never deletes an identity, role, permission, membership, Sprint 23 provisioning row, Sprint 22 policy-mutation row, or unrelated role assignment.

## Canonical protected-role state

The durable lifecycle repository fails closed unless:

- `authorization-policy-administrator` exists under the exact tenant;
- it carries exactly one permission;
- that permission is exactly `authorization.policy.manage`;
- no alternate tenant role carries `authorization.policy.manage`.

Unexpected protected-role state is treated as an integrity conflict. Sprint 24 does not silently repair or normalize that state.

## Atomicity

The existing `PersistenceTransaction` boundary is reused.

Critical checks are repeated inside the transaction:

- runtime/persistence enablement;
- actor tenant-scoped authority;
- target same-tenant existence;
- canonical protected-role state;
- replay/conflict state;
- current target assignment state;
- current distinct tenant control-principal count for revocation.

Assignment mutation and lifecycle-journal insertion occur atomically. A journal failure rolls back the assignment mutation.

No unrestricted `upsert` or `updateOrInsert` is used.

## Migration #6

Sprint 24 adds one forward-only canonical migration:

`0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`

It creates only:

`oneqay_protected_control_admin_mutations`

Stored evidence is limited to:

- `tenant_id`;
- `mutation_id`;
- `actor_identity_id`;
- `operation`;
- `target_identity_id`;
- `role_id`;
- `permission_id`;
- `payload_fingerprint`;
- `outcome`;
- `occurred_at_unix`.

The primary key is composite `tenant_id + mutation_id`.

Actor and target identities are foreign-key bound to the same tenant. The protected role and exact role-permission relationship are also bound to the same tenant.

The journal contains no secrets, passwords, TOTP data, tokens, sessions, arbitrary request payloads, or real customer data.

`down()` retains forward-only rollback denial.

Migrations #1–#5 remain immutable.

## Canonical migration set after Sprint 24

The source repository must contain exactly:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`;
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`.

Migration presence in source is not Preview or Production execution authority.

## Failure boundary

Sprint 24 exposes bounded failure categories for:

- authorization denied;
- persistence disabled;
- runtime denied;
- invalid lifecycle mutation;
- target identity mismatch;
- tenant relationship denied;
- mutation conflict;
- protected-role state conflict;
- last-control-principal denial;
- storage failure;
- transaction failure.

Raw SQL/database exceptions, DSNs, credentials, framework traces, secret values, and filesystem paths do not form part of the Application failure contract.

## Regression proof

The dedicated disposable regression proves, among other cases:

- persistence-disabled, Preview, and Production denial before lifecycle schema access;
- exact six-migration materialization on disposable SQLite;
- bounded migration #6 schema;
- successful tenant-scoped delegation;
- delegated principal recognition by the Sprint 21 evaluator;
- deterministic applied replay and independent `no_change` outcome;
- mutation conflict on changed payload;
- same textual identity and mutation IDs remain tenant-bound;
- foreign-tenant target denial;
- device-scoped control actor cannot delegate tenant control;
- no organization/outlet/device assignment is created by delegation;
- no extra role or permission is created;
- safe revoke and absent-target `no_change`;
- safe self-revocation when another tenant control principal remains;
- final tenant control principal cannot be revoked;
- denied lifecycle attempts leave no lifecycle journal row;
- Sprint 22 generic policy administration still rejects protected-role mutation;
- forced lifecycle journal failure rolls back assignment mutation;
- Sprint 23 initial provisioning journal remains unchanged;
- Sprint 22 policy-mutation journal remains unchanged;
- no unrestricted ownership-rewriting upsert;
- Sprint 21, Sprint 22, Sprint 23, isolation, Preview, release, and updater regressions remain mandatory.

## Runtime and Technical Preview boundary

`ONEQAY_PERSISTENCE_ENABLED` remains `false` by default.

Durable Sprint 24 lifecycle qualification is allowed only for:

- `local`;
- `test`;
- `ci`.

Preview and Production are denied before lifecycle storage access.

Technical Preview remains **NO_SCHEMA_CHANGE**. The migration directory remains excluded from the governed Technical Preview release payload.

No Sprint 24 Preview controller, endpoint, command, authority source, or real administrator mutation surface exists.

## Production and updater preservation

Production remains **NO-GO / NOT AUTHORIZED**.

Sprint 24 does not authorize:

- Production persistence;
- Production schema execution;
- Production administrator delegation or revocation;
- emergency administrator recovery;
- real customer data;
- Production deployment;
- GitHub Release;
- updater activation.

Updater runtime remains **DISABLED / UNWIRED** and its privileged authority remains separate from tenant policy control.

## Deliberately unresolved capabilities

Sprint 24 does not solve:

- emergency administrator recovery when no tenant control principal remains;
- external trust-root recovery;
- public/API control administration delivery;
- administrator UI;
- business-role catalog;
- Production provisioning/delegation;
- tenant onboarding orchestration;
- POS persistence.

Those concerns require separately bounded future stages.

## Security invariant

> Protected tenant control authority can be delegated or revoked only by a currently verified tenant-scoped control principal, only for a verified same-tenant identity, only through the dedicated exact protected-role tenant-assignment lifecycle, and never in a way that removes the final tenant-scoped control principal or weakens Sprint 21, Sprint 22, or Sprint 23 guarantees.

Attribution: **Lab | zefry**

# Initial Tenant Administrator Provisioning Foundation

## Purpose

Sprint 23 introduces a governed, one-time foundation for provisioning the first tenant-scoped authorization policy administrator for a tenant that has not yet established durable control authority.

This foundation exists only to close the initial control-principal bootstrap gap left intentionally unresolved by Sprint 22. It does not create a general administrator lifecycle, recovery mechanism, business-role catalog, public onboarding flow, or Production bootstrap surface.

Attribution: **Lab | zefry**

## Canonical control authority

A successful provisioning ceremony creates exactly:

- tenant role: `authorization-policy-administrator`;
- permission: `authorization.policy.manage`;
- assignment scope: tenant;
- assignee: an already-existing verified platform identity belonging to that exact tenant.

No other role, permission, organization assignment, outlet assignment, device assignment, ownership marker, platform privilege, or updater capability is created.

Once created, the control role is a Sprint 22 protected control role because it carries `authorization.policy.manage`. The normal Sprint 22 policy-administration service therefore remains unable to grant, revoke, reassign, weaken, expand, or otherwise rewrite that protected authority.

## Explicit authority provenance

Sprint 23 does not infer bootstrap authority from application state.

The Application service requires `InitialTenantAdministratorProvisioningAuthority`, which authorizes one exact tuple:

- tenant ID;
- verified platform identity ID;
- canonical provisioning ID.

The bounded Local/Test/CI qualification adapter is:

`PreauthorizedInitialTenantAdministratorProvisioningAuthority`

Its authority tuples are supplied only through constructor composition. It does not read HTTP requests, headers, query parameters, cookies, sessions, filesystem secrets, environment superuser flags, updater privileges, or database state as authority.

The application composition root deliberately does **not** bind `InitialTenantAdministratorProvisioningAuthority`. Therefore repository registration alone does not create an executable or permissive bootstrap path.

Any future real Preview or Production authority provider requires a separate Product Owner gate and security review of that exact delivery/trust boundary.

## Prohibited bootstrap shortcuts

Sprint 23 provides no:

- `allow if no administrator exists` rule;
- first-user elevation;
- implicit Owner/Admin/Superadmin role;
- platform-superadmin inheritance;
- tenant-superadmin wildcard;
- environment superuser;
- updater privilege reuse;
- bootstrap HTTP header;
- query-string bootstrap token;
- cookie/session bootstrap bypass;
- hard-coded tenant administrator;
- default administrator seed;
- migration-created administrator;
- unauthenticated route/controller/command authority;
- direct authority derived only from raw tenant or identity identifiers.

Absence of an administrator is state, not authority.

## Verified identity and tenant binding

The candidate administrator must already exist as a `VerifiedPlatformIdentity`.

The durable repository independently verifies that the identity exists under the exact tenant before any role, permission, assignment, or provisioning-journal mutation occurs.

Sprint 23 does not create:

- tenants;
- identities;
- organizations;
- outlets;
- devices;
- identity-organization membership;
- organizational access grants.

A foreign-tenant identity is denied even when the same textual identity identifier exists elsewhere.

## Canonical provisioning identifier

Each ceremony uses `InitialTenantAdministratorProvisioningId`.

The identifier is canonicalized to lower case and accepts only the bounded format:

`[a-z0-9][a-z0-9_-]{0,63}`

Malformed identifiers are rejected before durable mutation.

Provisioning identifiers are tenant-local rather than globally authoritative.

## Idempotency and replay

The canonical provisioning fingerprint is SHA-256 over exactly:

- tenant ID;
- verified identity ID;
- provisioning ID;
- role `authorization-policy-administrator`;
- permission `authorization.policy.manage`;
- scope `tenant`.

Rules:

- same tenant + same provisioning ID + same fingerprint after successful initialization returns the deterministic prior outcome `applied`;
- same tenant + same provisioning ID + different payload fails closed as a provisioning conflict;
- a different provisioning ID after the tenant has already been initialized is denied as already initialized;
- the same textual provisioning ID may be used independently by another tenant.

The fingerprint contains no password, TOTP data, session value, bearer token, credential, HTTP payload, customer contact data, or secret authority material.

## One-time initialization invariant

A tenant may have at most one successful Sprint 23 initial administrator provisioning record.

Before first-time mutation the durable repository checks for:

1. an existing initial-provisioning journal record; or
2. any existing tenant role carrying exact `authorization.policy.manage`.

Either condition means the tenant is already initialized and Sprint 23 cannot be reused.

This intentionally prevents Sprint 23 from becoming a recovery, replacement, delegated-administration, or control-authority duplication mechanism.

Administrator loss/recovery remains outside Sprint 23 and requires a separately governed future stage.

## Dedicated protected-control bootstrap exception

Sprint 22 correctly prevents the general policy-administration service from manufacturing or modifying protected control roles.

Sprint 23 does not weaken that rule. Instead, it uses a dedicated narrow repository contract whose only purpose is the one-time initial transaction.

The exception is limited to:

- exact first initialization;
- exact authorized tenant;
- exact authorized verified identity;
- exact authorized provisioning ID;
- exact role `authorization-policy-administrator`;
- exact permission `authorization.policy.manage`;
- exact tenant-scoped assignment;
- Local/Test/CI runtime only.

After the initial transaction commits, normal Sprint 22 protected-control restrictions remain fully effective.

## Atomic durable mutation

The successful first-time path atomically creates exactly:

1. tenant role `authorization-policy-administrator` when absent and compatible;
2. exact role-permission relationship to `authorization.policy.manage`;
3. exact tenant-scoped assignment to the verified identity;
4. append-only initial-provisioning journal evidence.

The existing `PersistenceTransaction` boundary is reused.

Critical authority and tenant checks occur before the transaction and are repeated inside the Infrastructure mutation path as defense in depth.

Any authorization, relationship, compatibility, journal, idempotency, storage, or transaction failure must leave no partial initial-control authority.

The repository does not use unrestricted `upsert` or `updateOrInsert` ownership rewriting.

## Migration #5

Sprint 23 adds one forward-only canonical migration:

`0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`

It creates only:

`oneqay_initial_tenant_admin_provisionings`

Stored evidence is limited to:

- `tenant_id`;
- `provisioning_id`;
- `identity_id`;
- `role_id`;
- `permission_id`;
- `payload_fingerprint`;
- `outcome`;
- `occurred_at_unix`.

The primary key is `tenant_id`, structurally limiting successful Sprint 23 initialization to one durable row per tenant.

The migration binds identity, role, and role-permission facts to the same tenant through foreign-key relationships.

It contains no seed data, secrets, passwords, TOTP material, bearer tokens, sessions, arbitrary request payloads, customer contact information, or credentials.

`down()` retains the established forward-only rollback denial.

Migrations #1–#4 remain immutable.

## Canonical migration set after Sprint 23

The expected canonical source migration set is exactly:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`.

Source-repository migration presence is not runtime migration authority.

## Runtime boundary

Durable initial-administrator provisioning is allowed only when:

- persistence is explicitly enabled; and
- runtime class is exactly `local`, `test`, or `ci`.

`ONEQAY_PERSISTENCE_ENABLED` remains `false` by default.

The repository fails closed for Preview and Production before provisioning storage is accessed.

There is no default provisioning-authority binding in `AppServiceProvider`.

## Technical Preview preservation

Technical Preview remains:

**NO_SCHEMA_CHANGE**

Sprint 23 does not authorize:

- Preview migration execution;
- Preview durable administrator provisioning;
- Preview provisioning authority binding;
- Preview bootstrap endpoint/controller/command;
- migration #5 inside the governed Technical Preview release artifact.

The existing release workflow continues to prove that the entire migration directory is excluded from the Technical Preview artifact and that `RELEASE.json` retains the `NO_SCHEMA_CHANGE` classification.

## Production and updater preservation

Production remains:

**NO-GO / NOT AUTHORIZED**

Sprint 23 does not authorize:

- Production persistence;
- Production schema execution;
- Production tenant bootstrap;
- Production policy mutation;
- Production administrator recovery/replacement;
- real customer data;
- Production deployment;
- GitHub Release;
- updater activation.

Updater runtime remains **DISABLED / UNWIRED** and its privileged authority remains separate from tenant policy control.

## Failure boundary

Sprint 23 exposes bounded non-sensitive failure categories for:

- authorization denied;
- persistence disabled;
- runtime denied;
- invalid provisioning identifier;
- verified identity mismatch;
- tenant relationship denied;
- already initialized;
- provisioning conflict;
- incompatible role state;
- storage failure;
- transaction failure.

Raw SQL exceptions, DSNs, credentials, secret values, framework traces, and filesystem paths are not part of the Application failure contract.

## Regression proof

The dedicated disposable regression proves, among other cases:

- disabled/Preview/Production denial before provisioning schema access;
- exact five-migration materialization on disposable SQLite;
- exact bounded provisioning-journal schema;
- one successful first provisioning;
- deterministic exact replay without duplicate state;
- same textual provisioning ID remains tenant-local;
- tuple mismatch and missing authority deny;
- foreign-tenant identity denies;
- pre-existing control authority blocks bootstrap reuse;
- incompatible pre-existing role state is not rewritten;
- denied attempts leave no provisioning journal row;
- forced journal failure rolls back role, permission, assignment, and journal state;
- Sprint 21 evaluator recognizes the newly provisioned control authority;
- Sprint 22 general policy administration still denies mutation of the newly protected control role;
- denied protected-role mutation leaves no Sprint 22 policy-mutation journal row.

Existing M7.2, M7.3, Preview database qualification, Sprint 21, and Sprint 22 workflows are preserved and advanced only to recognize the canonical migration #5 and Sprint 23 regression.

The dedicated Sprint 23 workflow additionally enforces the exact authorized 21-file source envelope and proves no default provisioning-authority binding exists.

## Deliberately unresolved capabilities

Sprint 23 does not solve:

- administrator recovery after control-principal loss;
- replacement or transfer of protected control authority;
- multiple/delegated control administrators;
- business-role administration UX;
- public/API bootstrap delivery;
- tenant onboarding orchestration;
- external identity-provider proofing;
- Production provisioning trust-root delivery.

Those concerns require separately bounded stages and must not be inferred from this foundation.

## Security invariant

> The first tenant control principal can be created only once, for an already-existing verified same-tenant identity, under an exact pre-authorized tenant + identity + provisioning tuple; no application state, absence-of-admin condition, bearer input, updater/platform privilege, or implicit superadmin rule can manufacture bootstrap authority.

Attribution: **Lab | zefry**

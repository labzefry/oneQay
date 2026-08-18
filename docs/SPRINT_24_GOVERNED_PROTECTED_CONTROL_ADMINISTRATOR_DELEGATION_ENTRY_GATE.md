# Sprint 24 Entry Gate — Governed Protected Control Administrator Delegation Foundation

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact base: `985e86895a3e0189c546e02ed64e17e5b2b2e93b`
- Exact base tree: `e36a3aaf07c767b3dec62f53392bc516a53265ea`
- Sprint 23: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Post-Sprint 23 canonical state reconciliation: **PUBLISHED**
- Production readiness: **NO-GO**

GitHub remains the Single Source of Truth.

This document authorizes **Sprint 24 — Governed Protected Control Administrator Delegation Foundation** for bounded Local/Test/CI implementation after this documentation-only entry gate is published.

Independent review is not an additional mandatory gate under the current Product Owner continuation model unless the Product Owner explicitly reactivates it. Exact-head Product Owner authority, exact changed-file scope, required CI, tenant isolation, fail-closed runtime controls, and repository protection remain mandatory.

No Production or Preview control delegation, emergency administrator recovery, business-role catalog, UI/API administration surface, external identity-provider integration, deployment, Release, updater activation, customer-data persistence, or Production readiness is authorized.

Attribution: **Lab | zefry**

## Why Sprint 24

Sprint 23 establishes exactly one initial tenant-scoped protected control principal under an explicit one-time provisioning authority.

That safely closes bootstrap, but deliberate one-time bootstrap alone does not provide control-plane continuity. A tenant with one protected administrator needs a governed way for an already-authorized tenant control principal to delegate the same protected authority to another verified same-tenant identity and, when safe, revoke a delegated assignment.

The existing Sprint 22 policy-administration service cannot perform this lifecycle because it correctly rejects protected-control assignment and revocation.

Sprint 24 therefore introduces a dedicated protected-control lifecycle path without weakening Sprint 22 and without making Sprint 23 reusable.

## Canonical protected control identity

The canonical protected control role remains exactly:

`authorization-policy-administrator`

The sole control permission remains exactly:

`authorization.policy.manage`

Sprint 24 does not create a new control permission, control-role hierarchy, wildcard, inherited owner role, platform-superadmin concept, or updater authority.

The canonical control role must continue to carry exactly `authorization.policy.manage` and no other permission.

Sprint 24 may mutate only tenant-scoped assignments of that exact role. It may not mutate the role definition or its permission relationship.

## Exact actor authority

A Sprint 24 mutation requires an already-verified actor represented by `VerifiedOrganizationalContext` whose exact identity currently has **tenant-scoped** durable control authority for the target tenant.

Authority must be proven durably from:

- exact tenant;
- exact actor identity;
- a tenant-role assignment under that tenant;
- a role carrying exact `authorization.policy.manage`.

Organization-scoped, outlet-scoped, or device-scoped `authorization.policy.manage` is insufficient for Sprint 24.

A narrower control principal may continue to administer ordinary Sprint 22 policy within its authorized containment scope, but it cannot delegate or revoke tenant protected-control authority.

The actor authority check occurs before transaction entry and is repeated inside the Infrastructure transaction path as defense in depth.

There is no environment, request-header, bearer-token, platform-superadmin, updater, implicit-owner, or first-user substitute for tenant-scoped control authority.

## Verified target identity

Every delegation or revocation target must be supplied as an already verified `VerifiedPlatformIdentity`.

The durable repository independently proves that the target identity exists under the exact actor tenant before mutation.

Sprint 24 does not create identities, memberships, tenants, organizations, outlets, or devices.

A foreign-tenant identity is denied even if it has the same textual identifier as a valid same-tenant identity.

Raw request IDs are not authority and cannot bypass verified target semantics.

## Closed operation vocabulary

Sprint 24 authorizes exactly two operations:

- `control.administrator.delegate`
- `control.administrator.revoke`

No arbitrary/free-form operation is accepted.

The canonical outcomes are exactly:

- `applied`
- `no_change`

`delegate` may only create the exact tenant-scoped assignment of `authorization-policy-administrator` to the verified target identity.

`revoke` may only remove that exact tenant-scoped assignment from the verified target identity when the last-control-principal invariant remains satisfied.

No role creation, permission mutation, organization/outlet/device assignment, generic role assignment, generic role revocation, or policy-mutation operation is part of Sprint 24.

## Dedicated protected-control exception

Sprint 24 must not weaken `DurablePolicyAdministrationService` or `LaravelDurablePolicyAdministrationRepository`.

The Sprint 22 generic policy-administration path must continue to reject protected control roles.

Sprint 24 introduces a dedicated lifecycle repository whose mutation surface is limited to the two exact operations above.

The exception is bounded to protected-control **tenant assignment lifecycle only**. The dedicated path cannot:

- create or rename the protected role;
- grant or revoke `authorization.policy.manage`;
- add another permission to the protected role;
- remove the existing control permission;
- assign the role at organization/outlet/device scope;
- mutate any ordinary business role;
- reuse Sprint 23 bootstrap authority;
- perform emergency recovery.

## Sprint 23 bootstrap preservation

Sprint 24 must leave the Sprint 23 initial-provisioning journal immutable.

It must not call the Sprint 23 provisioning service to add a second administrator.

The one-row-per-tenant Sprint 23 bootstrap invariant remains historical evidence of initial establishment and is not rewritten when administrators are later delegated or revoked.

A tenant with zero current control principals cannot invoke Sprint 24 because no actor can satisfy the required tenant-scoped control authority.

Therefore Sprint 24 is not an emergency recovery path.

## Last-control-principal safety invariant

Sprint 24 must never remove the final durable tenant-scoped control principal.

For revocation, the repository must determine the set of distinct tenant identities that retain tenant-scoped authority through a role carrying exact `authorization.policy.manage`.

A revocation may commit only if at least one distinct tenant-scoped control principal remains afterward.

This safety check occurs before transaction entry and is repeated inside the transaction immediately before the assignment mutation.

Consequences:

- revoking the sole control principal is denied;
- an actor may revoke its own exact assignment only when another tenant-scoped control principal remains;
- revoking one of multiple control principals is allowed when all other security checks pass;
- organization/outlet/device control authority does not satisfy the last-tenant-control requirement;
- no `allow if actor is owner` or environment override exists.

A denied last-principal revocation leaves no Sprint 24 mutation-journal row.

## Canonical lifecycle mutation ID

Every operation requires a tenant-local `ProtectedControlAdministratorMutationId`.

The identifier must use deterministic canonical string semantics compatible with existing governed mutation-ID patterns.

Rules:

- same tenant + same mutation ID + same fingerprint returns the prior deterministic outcome;
- same tenant + same mutation ID + different fingerprint fails closed as a lifecycle mutation conflict;
- the same textual mutation ID under another tenant is independent;
- malformed mutation IDs are rejected before durable mutation.

## Canonical mutation fingerprint

The SHA-256 fingerprint binds exactly:

- tenant ID;
- actor identity ID;
- operation;
- target identity ID;
- role `authorization-policy-administrator`;
- permission `authorization.policy.manage`;
- assignment scope `tenant`.

It contains no password, TOTP material, bearer token, session value, cookie, request payload, secret authority material, customer contact data, DSN, or filesystem path.

## Delegation semantics

For `control.administrator.delegate`:

1. actor must have current tenant-scoped control authority;
2. target must be a verified same-tenant identity;
3. canonical protected role state must be valid;
4. exact target tenant-role assignment absent → insert assignment and return `applied`;
5. exact target tenant-role assignment already present → no assignment mutation and return `no_change`;
6. business outcome and lifecycle journal must be atomic.

Delegation must not add another permission or role.

Delegation does not transfer or remove actor authority.

## Revocation semantics

For `control.administrator.revoke`:

1. actor must have current tenant-scoped control authority;
2. target must be a verified same-tenant identity;
3. canonical protected role state must be valid;
4. exact target assignment absent → return `no_change` without deleting unrelated state;
5. exact target assignment present → verify last-control-principal safety;
6. when another tenant-scoped control principal remains, delete only the exact target assignment and return `applied`;
7. when no other tenant-scoped control principal remains, fail closed before mutation;
8. business outcome and lifecycle journal must be atomic.

Revocation must not delete identities, roles, permissions, memberships, Sprint 23 provisioning evidence, or ordinary role assignments.

## Canonical protected-role state

The dedicated repository must fail closed unless the canonical role state is compatible:

- `authorization-policy-administrator` exists under the tenant;
- it carries exact `authorization.policy.manage`;
- it carries no additional permission;
- Sprint 24 never creates or rewrites this role state.

Unexpected protected-control role/permission state is a lifecycle integrity violation, not something Sprint 24 repairs automatically.

No unrestricted `upsert`, `updateOrInsert`, ownership rewrite, or silent repair is authorized.

## Transaction and append-only lifecycle journal

Sprint 24 reuses the existing `PersistenceTransaction` boundary.

One additive forward-only migration is authorized:

`apps/web/database/migrations/0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`

Migrations #1–#5 remain immutable.

Migration #6 creates exactly:

`oneqay_protected_control_admin_mutations`

The table stores bounded lifecycle evidence only:

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

Composite primary key:

`tenant_id + mutation_id`

Actor and target identities must be foreign-key bound to the same tenant.

The exact role and exact role-permission relationship must be foreign-key bound to the same tenant.

Application code may not update or delete an existing lifecycle journal row.

The journal contains no secrets, credentials, passwords, TOTP data, tokens, sessions, arbitrary request payloads, or real customer data.

## Atomicity and concurrency

Assignment mutation and journal insertion must occur in one transaction.

Critical checks are repeated inside the transaction:

- runtime and persistence enablement;
- actor tenant-scoped control authority;
- target same-tenant existence;
- canonical protected-role state;
- replay/conflict state;
- current target assignment state;
- current distinct tenant control-principal count for revocation.

Competing mutations must preserve deterministic outcomes and may not remove the final tenant control principal.

If a race is detected through journal uniqueness, assignment state, or last-principal state, the service must reconcile current durable state and return a bounded replay/no-change/conflict/last-principal result rather than leaking raw database errors.

No partial assignment or journal state may survive a failed transaction.

## Stable failure boundary

Sprint 24 must provide bounded non-sensitive failures for at least:

- authorization denied;
- persistence disabled;
- runtime denied;
- invalid mutation ID;
- verified target identity mismatch;
- tenant relationship denied;
- lifecycle mutation conflict;
- protected-role state conflict;
- last-control-principal denial;
- storage failure;
- transaction failure.

Raw SQL/database exceptions, DSNs, credentials, secret values, framework traces, and filesystem paths must not escape the Application boundary.

## Application and Infrastructure boundaries

New Application lifecycle classes remain framework/database independent.

They may depend on:

- existing Domain tenant/identity identifiers;
- `VerifiedPlatformIdentity`;
- `VerifiedOrganizationalContext`;
- `AdministrationPermission`;
- existing role/permission identifiers when needed;
- `PolicyAdministrationClock`;
- `PersistenceTransaction`.

They may not depend on Laravel DB, `DB::`, `Schema::`, PDO, query builder, HTTP requests, sessions, routes, controllers, commands, or updater internals.

`LaravelProtectedControlAdministratorLifecycleRepository` is the only new Laravel durable lifecycle adapter.

No new delivery layer is authorized.

## Canonical migration set after Sprint 24

The exact expected source migration set becomes:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`;
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`.

Migration #6 contains no seed state and remains forward-only.

## Preview and Production preservation

Technical Preview remains synthetic and **NO_SCHEMA_CHANGE**.

Sprint 24 must not:

- enable persistence in Preview;
- execute migration #6 against Preview;
- package migrations in the Preview release payload;
- expose Preview protected-control lifecycle endpoints;
- delegate or revoke real Preview administrators;
- authorize Production protected-control mutation;
- create an emergency recovery mechanism.

`ONEQAY_PERSISTENCE_ENABLED` remains `false` by default.

Durable Sprint 24 qualification may run only when persistence is enabled and runtime class is exactly `local`, `test`, or `ci`.

Preview and Production remain denied before lifecycle storage access.

Production persistence, schema execution, administrator provisioning, administrator delegation/revocation, administrator recovery, policy mutation, deployment, GitHub Release, and updater activation remain **NOT AUTHORIZED**.

Production readiness remains **NO-GO**.

## Required regression proof

Dedicated Local/Test/CI disposable regression must prove at least:

1. persistence-disabled denial before lifecycle schema access;
2. Preview denial before lifecycle schema access;
3. Production denial before lifecycle schema access;
4. exact six migrations execute in order on disposable SQLite;
5. migration #6 creates only the bounded protected-control lifecycle journal;
6. migrations #1–#5 remain immutable;
7. tenant-scoped control actor can delegate exact control role to verified same-tenant target;
8. delegated target gains exact tenant-scoped `authorization.policy.manage` through Sprint 21 evaluation;
9. organization-scoped control actor cannot delegate tenant protected control;
10. outlet-scoped control actor cannot delegate tenant protected control;
11. device-scoped control actor cannot delegate tenant protected control;
12. foreign-tenant target identity is denied;
13. same textual target IDs across tenants remain tenant-bound;
14. delegation creates no organization/outlet/device assignment;
15. delegation creates no extra role or permission;
16. exact already-assigned delegation returns deterministic `no_change`;
17. same mutation ID + same payload replays prior outcome;
18. same mutation ID + different payload conflicts;
19. same textual mutation ID in another tenant is independent;
20. actor may delegate to another target without losing actor authority;
21. revoke exact delegated assignment succeeds when another tenant control principal remains;
22. revoke absent exact assignment returns `no_change` without unrelated deletion;
23. revoking the sole tenant control principal is denied;
24. self-revocation is denied when actor is sole control principal;
25. self-revocation is allowed only when another tenant control principal remains;
26. narrower organization/outlet/device control authority does not satisfy last-tenant-control safety;
27. denied last-principal attempt leaves no Sprint 24 journal row;
28. denied authorization/foreign-target attempts leave no Sprint 24 journal row;
29. lifecycle mutation does not modify Sprint 23 provisioning journal;
30. lifecycle mutation does not modify Sprint 22 policy-mutation journal;
31. Sprint 22 generic policy administration still rejects protected role assignment/revocation;
32. canonical protected role continues to carry only `authorization.policy.manage`;
33. incompatible protected-role state fails closed without repair;
34. forced transaction failure rolls back assignment/revocation and journal state;
35. no unrestricted `upsert`/`updateOrInsert`;
36. raw storage failures map to bounded errors;
37. Sprint 21 evaluator remains read-only;
38. Sprint 23 one-time bootstrap replay/second-init protections remain green;
39. tenant isolation and identity/organization regressions remain green;
40. privileged updater authority remains separate and unchanged;
41. Preview DB qualification remains non-mutating;
42. Technical Preview release remains `NO_SCHEMA_CHANGE` and excludes all migrations.

## Preservation-test requirement known at entry

Sprint 23 advanced several existing regressions to an exact five-migration set. Migration #6 therefore requires those preservation tests to advance to an exact six-migration set without weakening their existing assertions.

Unlike Sprint 23, this need is known before Sprint 24 source coding and is included directly in the entry-gate envelope; no preservation supplement is required unless another legitimately necessary path is discovered later.

## Dependency boundary

No Composer/npm manifest or lockfile changes are authorized. No new dependency is required.

No credential, signing library, HTTP client, queue, cache, external identity provider, or secret-store integration is introduced by Sprint 24.

## Exact authorized implementation paths

Sprint 24 source implementation is limited to exactly these **23 paths**:

1. `apps/web/app/Application/Authorization/ProtectedControlAdministratorMutationId.php` — new;
2. `apps/web/app/Application/Authorization/ProtectedControlAdministratorOperation.php` — new;
3. `apps/web/app/Application/Authorization/ProtectedControlAdministratorMutation.php` — new;
4. `apps/web/app/Application/Authorization/ProtectedControlAdministratorLifecycleRepository.php` — new;
5. `apps/web/app/Application/Authorization/ProtectedControlAdministratorLifecycleService.php` — new;
6. `apps/web/app/Application/Authorization/ProtectedControlAdministratorLifecycleViolation.php` — new;
7. `apps/web/app/Infrastructure/Authorization/LaravelProtectedControlAdministratorLifecycleRepository.php` — new;
8. `apps/web/app/Providers/AppServiceProvider.php`;
9. `apps/web/database/migrations/0000_00_00_000006_create_protected_control_administrator_mutation_journal.php` — new;
10. `apps/web/tests/protected-control-administrator-lifecycle.php` — new;
11. `apps/web/tests/initial-tenant-administrator-provisioning.php`;
12. `apps/web/tests/authorization-administration-persistence.php`;
13. `apps/web/tests/authorization-persistence.php`;
14. `apps/web/tests/identity-org-context.php`;
15. `apps/web/tests/tenant-isolation.php`;
16. `.github/workflows/m7-2-tenant-isolation-regression.yml`;
17. `.github/workflows/m7-3-identity-org-context-regression.yml`;
18. `.github/workflows/m7-5-preview-db-qualification-regression.yml`;
19. `.github/workflows/sprint21-role-permission-policy-regression.yml`;
20. `.github/workflows/sprint22-policy-administration-regression.yml`;
21. `.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml`;
22. `.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml` — new;
23. `docs/PROTECTED_CONTROL_ADMINISTRATOR_DELEGATION_FOUNDATION.md` — new.

No other source path is authorized.

If CI reveals a legitimately required preservation path outside this envelope, that path must not be mutated until a bounded documentation-only preservation supplement is published.

## Explicit exclusions

Sprint 24 does **not** authorize:

- edits to migrations #1–#5;
- changes to generic root auth/persistence/migration foundations;
- weakening Sprint 22 protected-control rejection;
- changing Sprint 23 provisioning authority or making it reusable;
- changing the exact protected control permission;
- adding another permission to the control role;
- role creation through Sprint 24;
- organization/outlet/device protected-control assignments;
- public/admin UI;
- HTTP API/controller/middleware delivery;
- console command authority;
- environment superuser flags;
- default administrator records;
- emergency administrator recovery;
- real Preview/Production administrator lifecycle;
- business-role catalog;
- POS persistence;
- updater privilege changes;
- customer PII.

## Workflow preservation rule

The six existing authorization/isolation workflows in the source envelope may be updated only to recognize:

- the exact Sprint 24 23-path source envelope;
- the canonical six-migration set;
- the new protected-control lifecycle regression;
- preservation of earlier Sprint 21/Sprint 22/Sprint 23 invariants.

They may not remove existing assertions, relax tenant predicates, reduce protected-control checks, weaken Preview/Production denial, weaken dependency preservation, or stop proving Technical Preview migration exclusion.

The dedicated Sprint 24 workflow must run the Sprint 21, Sprint 22, Sprint 23, and Sprint 24 disposable authorization regressions.

## Merge gate

Sprint 24 source implementation may merge only when:

1. canonical base is freshly verified;
2. source branch is behind 0 or safely rebuilt/synchronized on canonical main;
3. changed-file envelope is exactly the 23 authorized paths;
4. dependency manifests/locks remain unchanged;
5. migrations #1–#5 remain byte-identical;
6. migration #6 is the only new migration;
7. all required application/isolation/authorization/Preview-preservation checks succeed;
8. dedicated Sprint 24 regression succeeds;
9. Sprint 23 bootstrap regression remains green;
10. Sprint 22 protected-control regression remains green;
11. exact-head `product-owner-merge-authority` succeeds;
12. repository security/rulesets are not weakened;
13. Technical Preview remains `NO_SCHEMA_CHANGE`;
14. Production and updater boundaries remain unchanged.

Any source-head change requires fresh exact-head Product Owner authorization.

## Entry-gate decision

**AUTHORIZED FOR BOUNDED IMPLEMENTATION AFTER THIS DOCUMENTATION-ONLY ENTRY GATE IS PUBLISHED.**

Security invariant:

> Protected tenant control authority may be delegated or revoked only by a currently verified tenant-scoped control principal, only for a verified same-tenant identity, only through the dedicated exact-role tenant-assignment lifecycle, and never in a way that removes the final tenant control principal or weakens Sprint 22/Sprint 23 control invariants.

Attribution: **Lab | zefry**

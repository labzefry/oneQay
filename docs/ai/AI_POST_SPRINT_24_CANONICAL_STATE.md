# oneQay — Post-Sprint 24 Canonical Program State

## Purpose and authority

This document is the canonical current-state supersession record after Sprint 24.

Older repository documents remain valid historical provenance, but they are not current authority where they conflict with this record or newer GitHub publication evidence.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Verified repository baseline

Fresh post-merge verification established:

- repository: `labzefry/oneQay`;
- canonical branch: `main`;
- verified main SHA: `f59188bb64306a7cf5cd0e3a9e0f588cc035e4d0`;
- verified tree: `928c50082d633c1464b12b6363b05b57e5fb3082`;
- parent: `3366506811dc460d0175d04cf6bacf61f910a87b`;
- commit: `feat(sprint24): add governed protected control administrator delegation foundation (#175)`;
- GitHub signature: **VERIFIED / VALID**;
- PR #175: **CLOSED / MERGED**;
- final Sprint 24 source head: `5888814140638dbdea82eded115db9c6cdb80ff7`;
- Sprint 24 source envelope: exactly **23 changed files**;
- final source relation before merge: **ahead 25 / behind 0**.

These values are publication provenance for this reconciliation baseline. Future lifecycle mutations still require fresh GitHub verification.

## Sprint 24 publication proof

The final exact Sprint 24 source head passed all eleven pull-request-triggered workflows:

- Governance Required Checks #305 — **SUCCESS**;
- PHP Foundation Regression #246 — **SUCCESS**;
- M7.1 Application Regression #175 — **SUCCESS**;
- M7.2 Tenant Isolation Regression #44 — **SUCCESS**;
- M7.3 Identity Organizational Context Regression #38 — **SUCCESS**;
- M7.5 Preview Database Qualification Regression #36 — **SUCCESS**;
- M7.5 Technical Preview Release Artifact #96 — **SUCCESS**;
- Sprint 21 Role Permission Policy Regression #16 — **SUCCESS**;
- Sprint 22 Policy Administration Regression #14 — **SUCCESS**;
- Sprint 23 Initial Tenant Administrator Provisioning Regression #10 — **SUCCESS**;
- Sprint 24 Protected Control Administrator Lifecycle Regression #9 — **SUCCESS**.

The dedicated Sprint 24 workflow proved the exact 23-file envelope, closed lifecycle vocabulary, Application framework boundary, canonical six-migration set, tenant-scoped actor authority, verified same-tenant target boundary, protected-role immutability, last-control-principal safety, transactional lifecycle evidence, Sprint 21/22/23 preservation, Local/Test/CI runtime boundary, Technical Preview separation, updater separation, and Production denial.

Exact-head `product-owner-merge-authority` was **SUCCESS** for `5888814140638dbdea82eded115db9c6cdb80ff7`. The valid authorization comment was authored by repository owner `labzefry` for that exact head.

PR #175 was squash-merged using the expected head SHA. No independent review was required under the current Product Owner continuation model.

## Sprint 24 canonical state

**Sprint 24 — Governed Protected Control Administrator Delegation Foundation** is now:

**COMPLETE / IMPLEMENTED / PUBLISHED**.

Sprint 24 adds a dedicated protected-control tenant-assignment lifecycle without weakening the generic Sprint 22 policy-administration restrictions and without making Sprint 23 bootstrap reusable.

The canonical protected control identity remains exactly:

- role: `authorization-policy-administrator`;
- permission: `authorization.policy.manage`.

The closed Sprint 24 operation vocabulary is exactly:

- `control.administrator.delegate`;
- `control.administrator.revoke`.

No generic protected-control mutation capability was created.

## Actor authority

A Sprint 24 mutation requires an already-verified actor whose exact identity has durable **tenant-scoped** control authority for the target tenant.

Organization-, outlet-, or device-scoped `authorization.policy.manage` is insufficient for this protected-control lifecycle.

Authority is derived from durable tenant assignment and role-permission facts. Request headers, query parameters, bearer input, environment-superuser switches, updater state, platform-superadmin concepts, and implicit ownership do not substitute for tenant-scoped control authority.

The authority proof is checked before transaction entry and repeated inside the Infrastructure mutation path as defense in depth.

## Verified target and tenant isolation

Delegation and revocation targets must be represented by an already verified platform identity.

The durable repository independently proves that the target identity exists under the exact actor tenant.

The lifecycle does not create tenants, identities, organizations, outlets, devices, memberships, or access grants.

Same textual identity values in different tenants remain independent tenant-bound records. Foreign-tenant targets fail closed and denied attempts create no lifecycle journal evidence.

## Last-control-principal invariant

Sprint 24 never authorizes removal of the final durable tenant-scoped control principal.

Before a revoke that would remove an existing protected control assignment, the lifecycle counts distinct tenant-scoped control identities whose assigned role carries exact `authorization.policy.manage`.

If the resulting lifecycle would leave no tenant-scoped control principal, the mutation is denied with the canonical last-control-principal violation and no lifecycle journal row is written.

Self-revocation is therefore allowed only when another tenant-scoped control principal remains.

The same safety condition is repeated inside the transaction before deletion.

## Protected-control preservation

The Sprint 22 generic policy-administration path remains unable to:

- grant or revoke `authorization.policy.manage`;
- assign or revoke a role that carries the protected control permission;
- mutate the protected role definition;
- add another permission to the protected role;
- remove the existing control permission;
- manufacture or transfer protected control authority through the generic mutation API.

Sprint 24 is a dedicated tenant-assignment lifecycle exception only. It may not modify role or permission definitions and may not create organization/outlet/device protected assignments.

The Sprint 21 evaluator remains read-only.

## Sprint 23 bootstrap preservation

Sprint 23 initial tenant administrator provisioning remains one-time per tenant.

Sprint 24 does not call or reactivate Sprint 23 bootstrap to add administrators.

The initial-provisioning journal remains immutable historical evidence of first establishment. Delegation and revocation use the separate Sprint 24 mutation journal.

A tenant with zero current control principals cannot use Sprint 24 because no actor can satisfy the tenant-scoped authority requirement. Emergency recovery therefore remains a separate unresolved concern and is **NOT AUTHORIZED** by Sprint 24.

## Transactional lifecycle evidence

Migration #6 creates only:

`oneqay_protected_control_admin_mutations`

Each successful or deterministic no-change lifecycle attempt is bound to:

- tenant-local mutation ID;
- actor identity;
- target identity;
- exact operation;
- canonical protected role;
- canonical protected permission;
- canonical SHA-256 payload fingerprint;
- canonical outcome;
- occurrence time.

The lifecycle assignment mutation and journal evidence share the existing `PersistenceTransaction` boundary.

A forced journal failure rolls back the assignment mutation. Unrestricted `upsert` and `updateOrInsert` remain prohibited.

## Canonical migration set

The canonical repository now contains exactly six forward-only migrations:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`;
6. `0000_00_00_000006_create_protected_control_administrator_mutation_journal.php`.

Migrations #1–#5 were preserved. Sprint 24 added migration #6 only.

## Runtime and delivery boundaries

Durable Sprint 24 lifecycle execution remains permitted only when persistence is explicitly enabled and runtime class is Local/Test/CI.

`ONEQAY_PERSISTENCE_ENABLED=false` remains the default repository environment boundary.

Sprint 24 does not create a Preview delivery endpoint, Production delivery endpoint, public API, UI administration screen, command surface, webhook, background worker action, updater action, or deployment activation path.

## Technical Preview preservation

Technical Preview remains **NO_SCHEMA_CHANGE**.

The governed Technical Preview release artifact continues to exclude the migration directory and its deterministic package proof passed on the final Sprint 24 head.

No Sprint 24 lifecycle class is wired into Technical Preview Application or Delivery surfaces.

Sprint 24 publication therefore does not authorize schema application to Preview and does not expand Preview control-plane authority.

## Production and updater boundaries

Production remains:

**NO-GO / NOT AUTHORIZED**.

The updater remains:

**DISABLED / UNWIRED**.

Tenant protected-control authority is not updater authority. No Sprint 24 role or permission grants system-update installation, deployment, release, rollback, infrastructure, or platform-superadmin capability.

## Closure audit

Fresh bounded closure audit after PR #175 established:

- no open Sprint 24 lifecycle PR or issue;
- no canonical `TODO` findings;
- no canonical `FIXME` findings;
- no canonical `bypass` findings;
- exact six-migration directory;
- Sprint 24 source published from exact authorized head;
- Sprint 21, Sprint 22, Sprint 23, Preview, Production, and updater boundaries preserved.

The remaining current-state drift is resolved by this supersession record rather than rewriting historical documents.

## Next bounded engineering concern

The next logical concern is not another bootstrap mechanism and not broader protected-control authority.

The next bounded concern is an **ordinary governed policy-administration delivery foundation** for the already-existing Sprint 22 ordinary role/permission administration capability.

A future Sprint 25 entry gate may authorize a first-party authenticated Local/Test/CI delivery boundary that maps a closed request vocabulary into the existing Sprint 22 `DurablePolicyAdministrationService` under server-verified tenant/organizational context and CSRF-protected first-party session semantics.

That future gate must preserve these exclusions:

- no Sprint 23 initial bootstrap delivery;
- no Sprint 24 protected-control delegation/revocation delivery;
- no emergency recovery surface;
- no Production authority;
- no Technical Preview control-plane expansion;
- no new schema or migration unless separately authorized;
- no updater authority;
- no platform-superadmin concept;
- no arbitrary/free-form policy mutation operation.

This direction is a bounded next concern only. Source implementation is not authorized until a dedicated Sprint 25 entry gate is separately published.

## Canonical declaration

As of this reconciliation:

- Sprint 21 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 22 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 23 remains **COMPLETE / IMPLEMENTED / PUBLISHED**;
- Sprint 24 is **COMPLETE / IMPLEMENTED / PUBLISHED**;
- canonical migrations are exactly #1–#6;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- Production remains `NO-GO / NOT AUTHORIZED`;
- updater remains `DISABLED / UNWIRED`;
- emergency protected-control recovery remains unresolved and unauthorized;
- next source work requires a separately published Sprint 25 entry gate.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**
# oneQay — Post-Sprint 23 Canonical Program State

## Purpose and authority

This document is the canonical current-state supersession record after Sprint 23.

Older repository documents remain valid historical provenance, but they are not current authority where they conflict with this record or newer GitHub publication evidence.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Verified repository baseline

Fresh post-merge verification established:

- repository: `labzefry/oneQay`;
- canonical branch: `main`;
- verified main SHA: `49b3d7ada35b018f86c7cc99120ba289671b54a5`;
- verified tree: `d118393719cbf83d35b9d12ff1afdcadf23e2cf5`;
- parent: `89afa639105203c394bc6ede33c2d6922461ea69`;
- commit: `feat(sprint23): add governed initial tenant administrator provisioning foundation (#172)`;
- GitHub signature: **VERIFIED / VALID**;
- PR #172: **CLOSED / MERGED**;
- final Sprint 23 source head: `91189c5da92fa68ad116d471832c6244e22fafd5`;
- Sprint 23 source envelope: exactly **21 changed files**;
- final source relation before merge: **ahead 22 / behind 0**.

These values are publication provenance for this reconciliation baseline. Future lifecycle mutations still require fresh GitHub verification.

## Sprint 23 publication proof

The final exact Sprint 23 source head passed all ten pull-request-triggered workflows:

- Governance Required Checks #294 — **SUCCESS**;
- PHP Foundation Regression #235 — **SUCCESS**;
- M7.1 Application Regression #164 — **SUCCESS**;
- M7.2 Tenant Isolation Regression #35 — **SUCCESS**;
- M7.3 Identity Organizational Context Regression #29 — **SUCCESS**;
- M7.5 Preview Database Qualification Regression #27 — **SUCCESS**;
- M7.5 Technical Preview Release Artifact #86 — **SUCCESS**;
- Sprint 21 Role Permission Policy Regression #7 — **SUCCESS**;
- Sprint 22 Policy Administration Regression #5 — **SUCCESS**;
- Sprint 23 Initial Tenant Administrator Provisioning Regression #1 — **SUCCESS**.

The dedicated Sprint 23 workflow proved the exact 21-file source envelope, Application framework boundary, canonical five-migration set, one-time provisioning journal, Sprint 21 read-only evaluator preservation, Sprint 22 protected-control preservation, Local/Test/CI runtime boundary, no default provisioning-authority binding, Technical Preview `NO_SCHEMA_CHANGE`, updater separation, and Production denial.

Exact-head `product-owner-merge-authority` was **SUCCESS** for `91189c5da92fa68ad116d471832c6244e22fafd5`. The valid authorization comment was authored by repository owner `labzefry` for that exact head.

PR #172 was squash-merged using the expected head SHA. No independent review was required under the current Product Owner continuation model.

## Sprint 23 canonical state

**Sprint 23 — Governed Initial Tenant Administrator Provisioning Foundation** is now:

**COMPLETE / IMPLEMENTED / PUBLISHED**.

Sprint 23 closes the initial durable policy-control bootstrap gap without weakening Sprint 22 protected-control rules.

A successful qualified provisioning ceremony creates exactly:

- role `authorization-policy-administrator`;
- permission `authorization.policy.manage`;
- tenant-scoped assignment to one already-existing verified same-tenant identity;
- append-only initial-provisioning evidence.

No other authority is created.

## Explicit bootstrap authority

Sprint 23 does not use absence of an administrator as authority.

Bootstrap authorization is represented by `InitialTenantAdministratorProvisioningAuthority` and is bound to an exact tuple:

- tenant ID;
- verified platform identity ID;
- canonical provisioning ID.

The bounded `PreauthorizedInitialTenantAdministratorProvisioningAuthority` is a Local/Test/CI qualification adapter whose tuples are supplied through constructor composition.

It does not read HTTP headers, query parameters, cookies, sessions, request payloads, environment-superuser flags, updater privilege, or platform-superadmin state as authority.

`AppServiceProvider` binds the durable provisioning repository but deliberately does **not** bind a default `InitialTenantAdministratorProvisioningAuthority` provider.

Therefore Sprint 23 source presence does not create a live Preview or Production bootstrap path.

## One-time control-principal invariant

Sprint 23 is intentionally one-time per tenant.

A tenant cannot reuse Sprint 23 when either:

- an initial-provisioning journal record already exists; or
- any tenant role already carries exact `authorization.policy.manage`.

The durable journal uses `tenant_id` as its primary key, providing a structural one-successful-initialization boundary.

Same tenant + same provisioning ID + same canonical fingerprint replays the deterministic prior `applied` outcome.

Same tenant + same provisioning ID + different payload conflicts.

A different provisioning ID after initialization is denied.

The same textual provisioning ID remains independent across different tenants.

## Protected-control preservation

The Sprint 23 dedicated provisioning path is a narrow first-initialization exception only.

After creation, `authorization-policy-administrator` is a Sprint 22 protected control role because it carries `authorization.policy.manage`.

The general Sprint 22 policy-administration service remains unable to:

- grant or revoke the control permission;
- assign or revoke the protected control role;
- add another permission to the protected control role;
- remove a permission from the protected control role;
- transfer protected control authority;
- manufacture another protected control principal.

The Sprint 21 evaluator remains read-only.

## Atomic durable mutation

The successful Sprint 23 transaction creates exactly:

1. the exact control role when absent and compatible;
2. the exact role-permission relationship;
3. the exact tenant-scoped role assignment;
4. the append-only provisioning journal row.

The existing `PersistenceTransaction` boundary is reused.

Authority and tenant relationship checks occur before the transaction and critical checks are repeated in the Infrastructure path as defense in depth.

A failed transaction leaves no partial control role, permission, assignment, or journal evidence.

Unrestricted `upsert` and `updateOrInsert` remain prohibited.

## Canonical migration set

The canonical source repository now contains exactly five forward-only migrations:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`;
5. `0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`.

Migration #5 creates only:

`oneqay_initial_tenant_admin_provisionings`

It stores bounded evidence only and contains no seed administrator, password, TOTP material, bearer token, session value, arbitrary request payload, secret authority material, or real customer data.

Migrations #1–#4 remain immutable.

## Runtime and Technical Preview boundaries

`ONEQAY_PERSISTENCE_ENABLED` remains **false by default**.

Durable Sprint 23 provisioning is qualified only for runtime classes:

- `local`;
- `test`;
- `ci`.

It remains denied for:

- `preview`;
- `production`.

Technical Preview remains **NO_SCHEMA_CHANGE**.

The entire migration directory remains excluded from the governed Technical Preview release payload.

Migration #5 is a source-repository artifact and does not authorize Preview or Production schema execution.

No Preview provisioning authority or delivery surface exists.

## Updater and Production state

Updater installation runtime remains **DISABLED / UNWIRED**.

Updater privileged authority remains separate from tenant policy-control authority.

`current-release.json` is not live runtime authority.

The following remain **NOT AUTHORIZED**:

- real customer data;
- Production durable persistence;
- Production schema/migration execution;
- Production initial administrator provisioning;
- Production policy administration;
- Production administrator recovery/replacement;
- Production deployment;
- GitHub Release / product Release;
- updater activation.

Production readiness remains **NO-GO**.

These are intentional program gates, not unresolved Sprint 23 defects.

## Current Product Owner continuation model

For the current continuation model, after the Product Owner explicitly authorizes or directs continuation of a bounded stage:

- fresh canonical `main` verification remains mandatory;
- exact changed-file scope remains mandatory;
- required CI and milestone-specific checks remain mandatory;
- exact-head `product-owner-merge-authority` remains mandatory;
- a source-head change invalidates prior exact-head authorization;
- expected-head SHA must be used for merge where supported;
- repository protection/rulesets must not be weakened;
- independent review is **not an additional mandatory gate** unless the Product Owner explicitly reactivates it for a particular bounded stage or risk decision.

This continuation model does not weaken security, tenant isolation, CI, source-envelope governance, exact-head authority, or protected-branch controls.

## Post-Sprint 23 closure audit

Fresh closure inspection established:

- canonical `main` points to the verified Sprint 23 squash commit;
- tree, parent, and GitHub signature are valid;
- PR #172 is merged;
- Sprint 23 publication envelope is exactly 21 files;
- canonical migration set is exactly migrations #1–#5;
- all ten final exact-head workflows succeeded;
- exact-head Product Owner merge authority succeeded;
- no Sprint 23 pull request remains open;
- no Sprint 23 issue remains open;
- repository search found no `TODO` marker;
- repository search found no `FIXME` marker;
- repository search found no active `bypass` marker;
- default durable persistence remains false;
- no default initial-provisioning authority is bound;
- Preview and Production authority remain fail-closed.

The only discovered post-merge inconsistency is older current-facing documentation that predates Sprint 23 publication.

This record closes that semantic drift without rewriting historical evidence.

Within the bounded Sprint 23 scope, no known unresolved source, migration, bootstrap-authority, tenant-isolation, protected-control, Preview-boundary, Production-boundary, or lifecycle defect remains.

## Superseded current-facing documentation

Until a future natural documentation edit rewrites older sections in place, this record supersedes conflicting current-facing state claims in at least:

- `README.md`;
- `PROJECT_MANIFEST.md`;
- `ROADMAP.md`;
- `TASKS.md`;
- `CHANGELOG.md`;
- `.github/workflows/README.md` for current workflow inventory/lifecycle interpretation;
- `docs/ai/AI_NEXT_TASK.md`;
- `docs/ai/AI_PROJECT_STATE.md`;
- `docs/ai/AI_SESSION_STATE.md`;
- `docs/ai/AI_POST_SPRINT_21_CANONICAL_STATE.md` where it describes an earlier latest stage;
- `docs/ai/AI_POST_SPRINT_22_CANONICAL_STATE.md` where it describes Sprint 23 as the next stage or the canonical migration set as four migrations.

Historical SHAs, earlier blocker states, entry-gate decisions, preservation supplements, and prior lifecycle evidence remain valid as history.

## Next bounded engineering direction

Sprint 23 establishes exactly one initial protected control principal but intentionally does not solve protected-control continuity.

The most immediate remaining authorization-control-plane gap is safe delegation of protected control authority to another verified same-tenant identity while preserving the prohibition against generic protected-role mutation and preventing removal of the final control principal.

The next stage is therefore:

**Sprint 24 — Governed Protected Control Administrator Delegation Foundation**

Sprint 24 must begin with a **documentation-only entry gate** before any source mutation.

The gate must define a dedicated protected-control lifecycle path rather than weakening Sprint 22.

At minimum the gate must resolve:

- exact authority required to delegate protected control;
- verified same-tenant target identity proof;
- closed operation vocabulary for protected-control delegation;
- deterministic tenant-scoped idempotency;
- atomic assignment + audit evidence;
- no generic `role.assign.*` shortcut for protected roles;
- no ability to rewrite the protected role or control permission;
- no transfer to organization/outlet/device scope;
- explicit prohibition on self-created broader/platform authority;
- last-control-principal safety invariant;
- concurrency behavior for competing delegation/revocation attempts;
- separation of normal delegation from emergency recovery;
- administrator recovery remaining separately gated and unresolved;
- Local/Test/CI-only qualification;
- Preview and Production denial;
- preservation of Sprint 21 read-only evaluation, Sprint 22 policy-administration controls, and Sprint 23 one-time bootstrap invariants.

Sprint 24 must not silently convert Sprint 23 into a reusable bootstrap path.

No POS persistence, business-role catalog, public administration UI/API, Production administrator provisioning, emergency recovery authority, Preview schema mutation, GitHub Release, updater activation, or Production authority is created by this reconciliation record.

Attribution: **Lab | zefry**

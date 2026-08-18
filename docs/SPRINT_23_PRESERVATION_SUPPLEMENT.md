# Sprint 23 Preservation Supplement — Canonical Five-Migration Regression Continuity

## Identity and authority

- Product: `oneQay`
- Engineering entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Canonical branch: `main`
- Exact base: `274b630d5c0eded4744ee5795f262246b96ce6f0`
- Exact base tree: `5bdd22eaddfb34fea983674d47817c828f6391a1`
- Sprint 23 entry gate: **PUBLISHED**
- Production readiness: **NO-GO**

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Why this supplement is required

The published Sprint 23 entry gate authorizes one additive forward-only migration:

`0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php`

Fresh source inspection after gate publication found that four existing preservation regressions intentionally assert the then-canonical exact four-migration set from Sprint 22.

Those assertions are correct historical preservation controls, but adding authorized migration #5 would make them fail mechanically unless they are advanced to recognize the newly authorized canonical five-migration set.

Skipping, deleting, weakening, or bypassing those regressions is **not authorized**.

This supplement therefore authorizes only the minimum preservation edits needed to keep the existing Sprint 21/Sprint 22/M7 isolation regressions fully active after migration #5 is introduced.

## Exact newly authorized preservation paths

The Sprint 23 implementation envelope is expanded by exactly these four existing test paths:

1. `apps/web/tests/authorization-administration-persistence.php`;
2. `apps/web/tests/authorization-persistence.php`;
3. `apps/web/tests/identity-org-context.php`;
4. `apps/web/tests/tenant-isolation.php`.

The original 17-path Sprint 23 source envelope remains otherwise unchanged.

After publication of this supplement, the maximum authorized Sprint 23 source implementation envelope is therefore **21 paths**.

No other new source path is authorized by this supplement.

## Exact permitted edits

Edits to the four preservation tests are limited to:

- advance canonical migration-set assertions from migrations #1–#4 to migrations #1–#5;
- execute migration #5 where the test intentionally materializes the full canonical migration set;
- assert the Sprint 23 provisioning-journal table and its bounded constraints where relevant;
- preserve all existing Sprint 21 read-only authorization assertions;
- preserve all existing Sprint 22 protected-control, scope-containment, idempotency, tenant-isolation, and safe-journal assertions;
- add narrow Sprint 23 preservation assertions only where needed to prove that migration #5 does not weaken earlier guarantees;
- update human-readable test success text from Sprint 22 preservation to Sprint 23 preservation where appropriate.

The tests must not remove, skip, relax, invert, or conditionally disable any existing security assertion merely to make CI pass.

## Required preservation invariants

The four tests must continue to prove at least:

- Sprint 21 `DurableRolePermissionRepository` remains read-only;
- all durable authorization data remains tenant-scoped;
- same textual IDs across tenants remain independent;
- Sprint 22 exact `authorization.policy.manage` semantics remain unchanged;
- Sprint 22 protected control roles remain protected;
- narrower device/outlet authority cannot escape to broader scopes;
- denied policy mutations leave no mutation-journal evidence;
- migrations #1–#4 remain present and immutable;
- migration #5 is additive and forward-only;
- the canonical migration directory becomes exactly migrations #1–#5;
- Technical Preview remains `NO_SCHEMA_CHANGE`;
- migration presence in source does not authorize Preview or Production execution.

## Explicit exclusions

This supplement does **not** authorize:

- changes to migrations #1–#4;
- changes to generic auth or persistence foundations;
- removal of exact migration-set checking;
- removal of Sprint 21 or Sprint 22 regression execution;
- broad test rewrites unrelated to migration #5 preservation;
- UI/API/command bootstrap delivery;
- environment/bootstrap superuser flags;
- Preview or Production provisioning;
- Preview or Production schema mutation;
- Production deployment, Release, updater activation, or customer-data persistence.

## Dependency and runtime boundary

No Composer/npm manifest or lockfile changes are authorized.

`ONEQAY_PERSISTENCE_ENABLED` remains `false` by default.

Durable Sprint 23 qualification remains Local/Test/CI only. Preview and Production remain denied.

Technical Preview remains **NO_SCHEMA_CHANGE**, with the migration directory excluded from the governed release payload.

## Merge gate effect

Once this documentation-only supplement is published, the Sprint 23 source PR may include the original 17 authorized paths plus the four preservation-test paths listed above, for a maximum exact authorized envelope of **21 paths**.

All required CI remains mandatory. Exact-head Product Owner merge authority remains mandatory. Any head change invalidates prior exact-head authorization.

Independent review is not an additional mandatory gate under the current Product Owner continuation model.

## Supplement decision

**AUTHORIZED AS A BOUNDED PRESERVATION SUPPLEMENT AFTER THIS DOCUMENTATION-ONLY RECORD IS PUBLISHED.**

Security invariant:

> Migration #5 may advance canonical migration-count assertions, but it may not erase, skip, or weaken any Sprint 21/Sprint 22/M7 authorization or tenant-isolation preservation proof.

Attribution: **Lab | zefry**

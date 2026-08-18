# oneQay — Post-Sprint 22 Canonical Program State

## Purpose and authority

This document is the canonical current-state supersession record after Sprint 22.

Several older current-facing headings in repository documentation still describe oneQay at earlier M7.5, Phase 0, or pre-Sprint 22 states. Those sections remain valid historical provenance, but they are **not current authority** where they conflict with this record or newer GitHub publication evidence.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Verified repository baseline

Fresh post-merge verification established:

- repository: `labzefry/oneQay`;
- canonical branch: `main`;
- verified main SHA: `d6a341d649770e4cfa3e31e12fdce1875df63ab9`;
- verified tree: `885df67ecd5da26cf0a0d2f6a0644ad2d6bc243e`;
- parent: `0596f05b2ef928ea14423cb7b462903437e9878a`;
- commit: `feat(sprint22): add governed durable policy administration foundation (#168)`;
- GitHub signature: **VERIFIED / VALID**;
- PR #168: **CLOSED / MERGED**;
- final Sprint 22 source head: `5ffed64acf58a5f3e71aef654f006d772bf1c2b3`;
- Sprint 22 source envelope: exactly **22 changed files**;
- final source relation before merge: **ahead 8 / behind 0**.

These values are publication provenance for this reconciliation baseline. Any future lifecycle mutation still requires fresh GitHub verification.

## Sprint 22 publication proof

The final exact Sprint 22 source head passed all nine pull-request-triggered workflows:

- Governance Required Checks #290 — **SUCCESS**;
- PHP Foundation Regression #231 — **SUCCESS**;
- Sprint 22 Policy Administration Regression #4 — **SUCCESS**;
- M7.5 Preview Database Qualification Regression #26 — **SUCCESS**;
- Sprint 21 Role Permission Policy Regression #6 — **SUCCESS**;
- M7.3 Identity Organizational Context Regression #28 — **SUCCESS**;
- M7.1 Application Regression #160 — **SUCCESS**;
- M7.2 Tenant Isolation Regression #34 — **SUCCESS**;
- M7.5 Technical Preview Release Artifact #84 — **SUCCESS**.

The exact-head `product-owner-merge-authority` status was **SUCCESS** for `5ffed64acf58a5f3e71aef654f006d772bf1c2b3`, and the final Product Owner authorization record was authored by repository owner `labzefry` for that exact head.

PR #168 was then squash-merged using the expected head SHA. No independent review was required under the current Product Owner continuation model.

## Sprint 22 canonical state

**Sprint 22 — Governed Durable Authorization Policy Administration Foundation** is now:

**COMPLETE / IMPLEMENTED / PUBLISHED**.

Sprint 22 adds bounded durable policy-administration mechanics while preserving the read-only Sprint 21 evaluator and deny-by-default authorization model.

The sole administration control permission is:

`authorization.policy.manage`

This permission is not platform-superadmin, updater authority, implicit ownership, implicit tenant-admin authority, or first-user bootstrap authority.

## Protected control authority

Any role carrying `authorization.policy.manage` is a protected control role.

Sprint 22 cannot use its own administration service to:

- grant or revoke `authorization.policy.manage`;
- assign or revoke a protected control role;
- add or remove other permissions on a protected control role;
- create an implicit Owner/Admin/Superadmin path;
- transfer, manufacture, weaken, or remove its own control authority.

The existing privileged updater authority remains separate.

## Scope-contained policy administration

Control authority is scope-contained.

- tenant mutation requires tenant-scoped control authority;
- organization mutation accepts tenant authority or exact organization authority;
- outlet mutation accepts tenant, exact organization, or exact outlet authority;
- device mutation accepts tenant, exact organization, exact outlet, or exact device authority.

A narrower authority cannot escape to a broader mutation scope.

In particular, device-scoped policy administration cannot create tenant roles, mutate tenant-wide role permissions, or assign roles at tenant, organization, or outlet scope.

Scope containment is checked before idempotent replay and repeated inside the Infrastructure transaction path as defense in depth.

Denied scope-escape attempts must not leave a policy-mutation journal row.

## Canonical mutation model

Authorized operations remain a closed vocabulary:

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

Outcomes remain exactly:

- `applied`;
- `no_change`.

Every mutation uses a tenant-scoped `PolicyMutationId` and SHA-256 canonical payload fingerprint.

Same tenant + same mutation ID + same fingerprint returns the prior deterministic outcome. Same tenant + same mutation ID + different fingerprint fails closed as a mutation conflict. The same textual mutation ID remains independent across tenants.

Business mutation and mutation-journal persistence are transaction-bound.

Unrestricted `upsert` and `updateOrInsert` remain prohibited.

## Canonical durable migration set

The canonical source repository contains exactly four forward-only migrations:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`;
4. `0000_00_00_000004_create_policy_mutation_journal.php`.

Migration #4 creates only `oneqay_policy_mutations`, keyed by `tenant_id + mutation_id`, with same-tenant actor identity linkage and bounded mutation evidence.

It contains no secrets, credentials, passwords, TOTP material, tokens, arbitrary request payloads, or seed data.

Migrations #1–#3 remain immutable.

## Bootstrap boundary remains unresolved by design

Sprint 22 deliberately does **not** solve first-administrator provisioning.

There is no:

- first-user elevation;
- implicit Owner/Admin/Superadmin role;
- environment superuser;
- updater privilege reuse;
- bootstrap header/token bypass;
- allow-if-no-admin shortcut.

Disposable Local/Test/CI tests may directly pre-provision synthetic control principals as fixtures only. Fixture state is not runtime bootstrap behavior and creates no Preview or Production authority.

## Runtime and Technical Preview boundaries

`ONEQAY_PERSISTENCE_ENABLED` remains **false by default**.

Durable application persistence and durable policy administration remain allowed only for runtime classes:

- `local`;
- `test`;
- `ci`.

They remain denied for:

- `preview`;
- `production`.

Technical Preview remains **NO_SCHEMA_CHANGE**.

Canonical migrations are source-repository artifacts only and remain excluded from the governed Technical Preview release artifact. No `artisan migrate` execution is authorized for Preview or Production.

Real customer data and real Production durable persistence remain **NOT AUTHORIZED**.

## Updater and Production state

Updater installation runtime remains **DISABLED / UNWIRED**.

`current-release.json` is not live runtime authority.

The following remain **NOT AUTHORIZED**:

- Production durable persistence;
- Production schema/migration execution;
- Production policy administration;
- Production tenant bootstrap;
- Production deployment;
- GitHub Release / product Release;
- real customer data.

Production readiness remains **NO-GO**.

These are intentional program gates, not unresolved Sprint 22 defects.

## Current Product Owner continuation model

For the current continuation model, after the Product Owner explicitly authorizes or directs continuation of a bounded stage:

- fresh canonical `main` verification remains mandatory;
- exact changed-file scope remains mandatory;
- required CI and milestone-specific checks remain mandatory;
- exact-head `product-owner-merge-authority` remains mandatory;
- any source-head change invalidates the prior exact-head authorization and requires a new exact-head authorization record;
- expected-head SHA must be used for merge where supported;
- repository protection/rulesets must not be weakened;
- independent review is **not an additional mandatory gate** unless the Product Owner explicitly reactivates it for a particular bounded stage or risk decision.

This continuation model does not weaken security, tenant isolation, CI, source-envelope governance, exact-head authority, or protected-branch controls.

## Superseded current-facing documentation

Until a future natural documentation edit rewrites older sections in place, this record supersedes conflicting current-facing state claims in at least:

- `README.md`;
- `PROJECT_MANIFEST.md`;
- `ROADMAP.md`;
- `TASKS.md`;
- `CHANGELOG.md`;
- `CONTRIBUTING.md` only for the current independent-review continuation rule described above;
- `.github/workflows/README.md` for current workflow inventory/lifecycle interpretation;
- `docs/ai/AI_NEXT_TASK.md`;
- `docs/ai/AI_PROJECT_STATE.md`;
- `docs/ai/AI_SESSION_STATE.md`;
- `docs/ai/AI_POST_SPRINT_21_CANONICAL_STATE.md` only where that earlier record describes Sprint 21 as the latest completed Sprint or Sprint 22 as the next stage.

Historical evidence, older SHAs, earlier blocker states, and prior lifecycle decisions remain valid as history. They must not be interpreted as newer than the publication sequence recorded here.

## Closure assessment

Fresh post-merge verification found:

- canonical `main` points to the verified Sprint 22 squash commit;
- tree, parent, and GitHub signature are valid;
- PR #168 is merged;
- the Sprint 22 source envelope remains exactly 22 files;
- the canonical migration set is exactly migrations #1–#4;
- no Sprint 22 pull request remains open;
- no Sprint 22 issue remains open;
- all final exact-head CI was successful;
- exact-head Product Owner merge authority was successful;
- Preview and Production authority boundaries remain fail-closed;
- the only discovered post-merge inconsistency is older documentation semantic drift.

This canonical supersession record closes that documentation ambiguity without rewriting historical evidence or changing application source.

Within the bounded Sprint 22 scope, no known unresolved source, migration, authorization, isolation, Preview-boundary, Production-boundary, or lifecycle defect remains.

## Next bounded engineering direction

The next logical unresolved security dependency is first-administrator provisioning.

The next stage is therefore:

**Sprint 23 — Governed Initial Tenant Administrator Provisioning Foundation**

Sprint 23 must begin with a **documentation-only entry gate** before any source mutation.

The entry gate must define a bootstrap model that can establish the first tenant policy-control principal without introducing implicit superadmin behavior or weakening Sprint 22 protected-control rules.

At minimum the gate must resolve:

- explicit bootstrap authority provenance;
- one-time versus renewable bootstrap semantics;
- tenant binding before any durable mutation;
- identity proof and actor binding;
- replay/idempotency behavior;
- bootstrap expiration and revocation semantics;
- audit evidence for every bootstrap decision;
- concurrency and duplicate-initialization behavior;
- separation from updater/platform-superadmin authority;
- no raw header/token bypass;
- no allow-if-no-admin shortcut;
- fail-closed Local/Test/CI-only source qualification;
- Preview and Production bootstrap denial;
- preservation of Sprint 21 read-only evaluation and Sprint 22 protected-control invariants.

No business-role catalog, public/admin UI, Production bootstrap, Preview durable policy administration, POS persistence, GitHub Release, updater activation, or Production authority is created by this reconciliation record.

Attribution: **Lab | zefry**

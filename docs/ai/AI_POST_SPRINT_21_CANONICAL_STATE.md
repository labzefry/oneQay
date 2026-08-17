# oneQay — Post-Sprint 21 Canonical Program State

## Purpose and authority

This document is the canonical current-state supersession record after Sprint 21.

Several older current-facing headings in repository documentation still describe oneQay at the M7.5 closure state. Those sections remain valid historical provenance, but they are **not current authority** where they conflict with this record or newer GitHub publication evidence.

GitHub remains the Single Source of Truth.

Attribution: **Lab | zefry**

## Verified repository baseline

Fresh verification before this reconciliation established:

- repository: `labzefry/oneQay`;
- canonical branch: `main`;
- verified main SHA: `3a35b9cff83067351597fafa193ea9855eb4b152`;
- verified tree: `0909f31222ce7054ad6ffc56262adf20ee0fe29b`;
- parent: `33c6005f905af99149ecb2f4e34cdc6a4c6309e6`;
- commit: `feat(sprint21): add durable scoped role permission policy (#165)`;
- GitHub signature: **VERIFIED / VALID**;
- PR #165: **CLOSED / MERGED**;
- final Sprint 21 source head: `88552c596ccfd59dda6eb748f58bde05c5e06394`;
- Sprint 21 source envelope: exactly **17 changed files**.

These values are publication provenance for this reconciliation baseline. Any future lifecycle mutation still requires fresh GitHub verification.

## Sprint 21 closure audit

Sprint 21 was re-audited after GitHub service recovery rather than relying on transient failure results.

Earlier CI failures occurred during external GitHub/codeload dependency fetches. After service recovery, the dedicated Sprint 21 workflow reached the actual disposable authorization regression and exposed one real harness defect: the Laravel application had been created but the configuration repository had not yet been bootstrapped before `tests/authorization-persistence.php` accessed it.

The correction was bounded to the already-authorized Sprint 21 workflow: the dedicated regression now bootstraps the Laravel HTTP kernel before executing the disposable authorization persistence test. No dependency, durable schema scope, authorization semantics, Preview authority, or Production authority changed.

The corrected final source head was `88552c596ccfd59dda6eb748f58bde05c5e06394` and received a fresh exact-head Product Owner merge authorization.

Final exact-head validation was fully successful:

- Governance Required Checks #284 — **SUCCESS**;
- PHP Foundation Regression #225 — **SUCCESS**;
- M7.1 Application Regression #154 — **SUCCESS**;
- M7.2 Tenant Isolation Regression #30 — **SUCCESS**;
- M7.3 Identity Organizational Context Regression #24 — **SUCCESS**;
- M7.5 Preview Database Qualification Regression #22 — **SUCCESS**;
- M7.5 Technical Preview Release Artifact #79 — **SUCCESS**;
- Sprint 21 Role Permission Policy Regression #2 — **SUCCESS**;
- `product-owner-merge-authority` — **SUCCESS**.

The dedicated Sprint 21 workflow also completed all of its own controls successfully: dependency-lock preservation, root Platform Foundation regression, application dependency installation, PHP syntax, Application framework independence, exact governed migration set, schema boundary, read-only durable authorization repository, reserved platform namespace, M7.1 preservation, disposable authorization persistence regression, tenant/organizational isolation, privileged updater security, Preview/Production denial, and tracked-source cleanliness.

No Sprint 21 pull request or issue remains open.

## Canonical M7 and Phase 0 lifecycle state

Newer publication evidence supersedes older M7.5-only current-facing headings still present in some documents.

Canonical state is:

- M7.5 Preview Runtime Qualification: **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**, final evaluator **29 VERIFIED / 0 BLOCKED**;
- M7.6 real qualified-target Preview deployment/recovery rehearsal: **PASS / PUBLISHED** through PR #143, published commit `2b0bfb4d276299943755e738b852d205f72db0e0`;
- M7.7 Technical Preview Acceptance: **VERIFIED / PUBLISHED**, **20 VERIFIED / 0 BLOCKED**, through PR #144, published commit `549e450666b6888711439c17e657494a452b4152`;
- Phase 0 Exit: **COMPLETE / EXIT APPROVED / PUBLISHED** through PR #145, published commit `225fba522435480f5577f171d9cb1ff5c4be9a76`.

Therefore older current-facing statements saying M7.6 or M7.7 is not authorized, Phase 0 is still In Progress, or Phase 0 Exit is not approved are historical and no longer current.

## Canonical Sprint progression after Phase 0 Exit

The following bounded engineering sequence is published repository fact:

- Sprint 14: entry gate PR #146 → source PR #147, published source commit `c05dbbaf98a8094dc3d8d8a69c544cfaf8e64301`;
- Sprint 15: entry gate PR #150 → test-envelope supplement PR #151 → source PR #152, published source commit `a06ceee3886a5648fc31baec65e3fdbd8a022c8d`;
- Sprint 16: entry gate PR #153 → source PR #154, published source commit `ccfb5d0a79c55c3cfbc1c59f7826d9f00344c437`;
- Sprint 17: entry gate PR #155 → source PR #156, published source commit `5a41e4f19f024859a1ab6e60b2915dbffce897e6`;
- Sprint 18: entry gate PR #157 → source PR #158, published source commit `b60e2d881b5e3a2679067f30ea2601fde0a0dd5d`;
- Sprint 19: entry gate PR #159 → preservation supplement PR #161 → source PR #160, published source commit `d3ab5ebed9687593ca8c5a1126f8c980d35ec0b1`;
- Sprint 20: entry gate PR #162 → source PR #163, published source commit `27c2b8ca0bee81a52f3b16dc464911548b2c0329`;
- Sprint 21: entry gate PR #164 → source PR #165, published source commit `3a35b9cff83067351597fafa193ea9855eb4b152`.

Therefore older current-facing statements saying Sprint 14 or the later bounded Sprint progression is not authorized are historical and no longer current.

## Durable persistence progression

The durable persistence sequence now includes:

1. governed migration planning;
2. governed migration manifest bridge;
3. deterministic Laravel migration generation;
4. isolated migration materialization and validation;
5. governed disposable Local/Test/CI migration execution;
6. durable foundational application persistence;
7. durable identity and organizational access persistence;
8. durable scoped role and permission policy evaluation.

Current canonical durable schema contains exactly three forward-only migrations:

1. `0000_00_00_000001_create_foundational_context_graph.php`;
2. `0000_00_00_000002_create_organizational_access_grants.php`;
3. `0000_00_00_000003_create_scoped_role_permission_policy.php`.

The foundational durable graph covers:

`Tenant → Identity → Organization → Outlet → Device`

plus identity/organization membership, outlet/device access grants, tenant-scoped roles, exact role-permission facts, and role assignments at tenant/organization/outlet/device scope.

## Sprint 21 authorization semantics

Sprint 21 is **COMPLETE / IMPLEMENTED / PUBLISHED**.

Canonical semantics are:

- only an existing `VerifiedOrganizationalContext` may be evaluated;
- role scope hierarchy is exactly tenant → organization → outlet → device;
- permission matching is exact-match only;
- no wildcard permission semantics;
- no role inheritance;
- no ABAC expression language;
- no negative permission language;
- no tenant-policy superadmin bypass;
- tenant role identifiers reserve `platform-superadmin`, `platform-*`, and `platform_*`;
- tenant permission identifiers reserve `platform.*`;
- `platform.system-update.install` remains outside tenant durable policy;
- the durable role/permission Application and Infrastructure contract is read-only;
- no built-in Owner/Admin/Manager/Cashier or other business role catalog exists;
- no Production business permission catalog exists;
- no role/permission administration API or UI exists.

Every durable authorization database read is tenant-scoped. Organization/outlet/device assignments are relationally constrained to the existing Sprint 19/20 membership and access graph through composite foreign keys.

## Runtime and Preview boundaries

Durable application persistence and durable authorization remain:

- disabled by default;
- enabled only when `ONEQAY_PERSISTENCE_ENABLED=true`;
- allowed only for runtime classes `local`, `test`, or `ci`;
- denied for `preview`;
- denied for `production`;
- synthetic-only for automated qualification.

Technical Preview remains **NO_SCHEMA_CHANGE**. Canonical repository migrations are excluded from the governed Technical Preview release artifact, and Preview database qualification does not execute those migrations.

No real customer/principal data was introduced by Sprint 19–21 qualification.

## Updater state

The updater control plane, read-only UI, safe staging/activation/rollback foundations, and shared-runtime configuration boundary remain published repository facts.

Runtime updater installation remains **DISABLED / UNWIRED**.

No GitHub Release or Production update authority is created by Sprint 21 or this reconciliation.

## Production state

- Production durable persistence: **NOT AUTHORIZED**;
- Production schema/migration execution: **NOT AUTHORIZED**;
- Production policy administration: **NOT AUTHORIZED**;
- Production deployment: **NOT AUTHORIZED**;
- GitHub Release / product Release: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

These are intentional program gates, not unresolved Sprint 21 defects.

## Current Product Owner continuation model

For the current continuation model, after the Product Owner explicitly authorizes or directs continuation of a bounded stage:

- fresh canonical `main` verification remains mandatory;
- a bounded branch and changed-file envelope remain mandatory;
- required CI and applicable milestone-specific checks remain mandatory;
- exact-head `product-owner-merge-authority` remains mandatory;
- a head change invalidates prior exact-head authority and requires a fresh Product Owner authorization record;
- repository protection/rulesets must not be weakened to force merge;
- independent review is **not an additional mandatory gate** unless the Product Owner explicitly reactivates it for a particular stage or risk decision.

Accordingly, older current-facing wording in `README.md`, `CONTRIBUTING.md`, `.github/workflows/README.md`, or other historical governance sections that still says independent review is always mandatory is superseded for the current continuation model by this explicit Product Owner direction.

This does not weaken CI, tenant isolation, security, exact-head authority, changed-file scope, or protected-branch controls.

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
- `docs/ai/AI_SESSION_STATE.md`.

Historical evidence, older SHAs, prior blocker states, and old lifecycle decisions remain valid as history. They must not be interpreted as newer than the publication sequence recorded here.

## Closure assessment

After fresh repository, source, CI, schema, authorization, Preview, and lifecycle review:

**Sprint 21 has no known unresolved bounded-scope defect or open lifecycle item.**

The only discovered post-merge inconsistency was documentation semantic drift. This canonical supersession record closes that ambiguity without rewriting historical evidence or application source.

Absolute defect-freedom cannot be mathematically guaranteed, but within the authorized Sprint 21 scope all available repository-native evidence is green and no known unresolved defect remains.

## Next bounded engineering direction

The next logical stage is:

**Sprint 22 — Governed Durable Authorization Policy Administration Foundation**

Sprint 22 must begin with a documentation-only entry gate before source mutation.

The gate must resolve the security bootstrap problem before allowing any durable policy write path. At minimum it must define:

- canonical administrative permission vocabulary required to mutate tenant policy;
- how first administrative authority can exist without inventing an implicit Owner/Admin/Superadmin business role;
- strict separation from existing platform-superadmin updater authority;
- whether policy writes are append-only, replace-by-version, or explicitly revocable;
- mandatory audit/event evidence for every mutation;
- idempotency and replay semantics;
- transaction boundaries;
- tenant/organization/outlet/device scope validation;
- concurrency/conflict behavior;
- fail-closed Preview and Production mutation denial;
- exact Local/Test/CI-only regression expectations.

No business role catalog, public/admin UI, Production policy mutation, Preview durable policy, POS persistence, Release, or Production authority is created by this reconciliation record.

Attribution: **Lab | zefry**

# AI M7.5 Publication State

## Periodic canonical consolidation — after PR #124

This section is the current periodic consolidation layer and supersedes every earlier `current`, `remaining blockers`, evaluator-count, and next-work assertion below where those statements describe an older M7.5 evidence snapshot. The older sections remain intentionally preserved as historical publication provenance.

Canonical governed state after PR #124:

- M7.5: **IN PROGRESS / QUALIFICATION MATERIAL PROGRESS**;
- verified mandatory controls: **26**;
- blocking mandatory controls: **3**;
- complete evaluator outcome: **BLOCKED**;
- M7.5 overall qualification: **BLOCKED / INCOMPLETE**;
- `lifecycle_authority_created=false`.

Only three blockers remain:

- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`.

Controls previously shown below as PARTIAL/UNVERIFIED/NOT_SUPPLIED but subsequently governed as VERIFIED through PR #114–#124 must not be regressed by historical wording. This includes PHP CLI/scheduler, connection/resource visibility, outbound DNS/HTTPS, environment-secret isolation, security boundary, Database Portability Contract conformance, safe observability logging, application rollback/deployment recovery, and bounded Preview background/queue execution.

The current evidence does **not** prove successful isolated restore or complete durable database-backed two-tenant isolation. Backup/export evidence and application-release rollback are not substitutes for database restore evidence.

Lifecycle remains:

- M7.0–M7.4A: **DONE / PUBLISHED**;
- M7.5: **IN PROGRESS / BLOCKED / INCOMPLETE**;
- M7.6: **NOT AUTHORIZED**;
- M7.7: **NOT AUTHORIZED**;
- Phase 0: **IN PROGRESS**;
- Phase 0 Exit: **NOT APPROVED**;
- Sprint 14: **NOT AUTHORIZED**;
- Release: **NOT AUTHORIZED**;
- Production: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

The next preferred bounded analysis after current-state consolidation is a fresh read-only gap analysis of `ENGINE:TENANT_ISOLATION`, followed by joint restore/backup-restore qualification design. No restore, schema, migration, database mutation, cleanup, M7.6, M7.7, Release, or Production authority is created by this state record.

Attribution: **Lab | zefry**

## Purpose

This additive canonical reconciliation records repository facts published after the mutable checkpoints in `PROJECT_MANIFEST.md`, `TASKS.md`, `CHANGELOG.md`, `docs/ai/AI_NEXT_TASK.md`, `docs/ai/AI_PROJECT_STATE.md`, and `docs/ai/AI_SESSION_STATE.md` were last reconciled.

It is intentionally minimum-delta. Historical text in those files is not rewritten merely to remove stale wording. For the bounded subjects below, this file is the current reconciliation layer until a later periodic checkpoint consolidation.

GitHub remains the Single Source of Truth for live repository state.

## PR #102 publication

PR #102 — `feat(m7.5): prepare fail-closed runtime qualification evidence harness` is **CLOSED / MERGED / PUBLISHED**.

Publication provenance:

- source head: `72a5dd4d855c5e7794e6804b823945ab99a078e2`;
- source tree: `3535bf559514942870e011a222321ca14a224363`;
- published squash commit: `bb03e46e8100aaa268f3f2885ac00199485c07e0`;
- published tree: `3535bf559514942870e011a222321ca14a224363`;
- source tree equals published tree: **YES**.

Published file envelope:

1. `composer.json`
2. `docs/handbook/M7_5_PREVIEW_RUNTIME_QUALIFICATION_PREPARATION.md`
3. `src/Runtime/Qualification.php`
4. `tests/runtime-qualification.php`
5. `tools/runtime-qualification.php`

## Current M7.5 state

The correct current distinction is:

- **M7.5 PREPARATION: DONE / PUBLISHED** through PR #102.
- **M7.5 P1 LIVE WEB RUNTIME EVIDENCE: VERIFIED / MATERIAL PROGRESS** on the authorized Technical Preview hostname.
- **M7.5 RELATIONAL ENGINE PROFILE QUALIFICATION: BLOCKED / NOT SUPPLIED**.
- **M7.5 OVERALL QUALIFICATION: BLOCKED / INCOMPLETE**.

PR #102 implements a deterministic, sanitized, fail-closed runtime-evidence evaluator. It does not by itself prove that a real Preview target is qualified.

The later M7.5 release-artifact and runtime-readiness publications provide a governed exact-SHA artifact path and explicit `preview` runtime readiness support. The Product Owner-authorized cPanel session on 2026-08-15 then supplied direct live evidence for the web-runtime subset recorded in `docs/handbook/M7_5_P1_LIVE_RUNTIME_EVIDENCE_20260815.md`.

The existing older checkpoint wording `M7.5 BLOCKED / NOT AUTHORIZED` must therefore be interpreted as historical pre-execution state. It must not override the current distinction between completed preparation, materially verified live web-runtime evidence, and still-blocked overall qualification.

## Live P1 web-runtime evidence — 2026-08-15

The active Technical Preview hostname is:

`oneqay.n07.my.id`

The active Preview release is built from published source:

`9d3d5eb084842750de884da67fe0770b7104cd7e`

with release ID:

`m75-preview-9d3d5eb08484`

Sanitized direct evidence now verifies the following bounded controls on the actual cPanel target:

- PHP 8.3 web runtime for the Preview hostname;
- Laravel boot through `/health/live`;
- readiness through `/health/ready` with explicit `preview` runtime class;
- effective Laravel URL rewrite/front-controller routing;
- private application payload outside the public document root;
- bounded public surface containing generated assets/front controller rather than private source;
- `.env` stored outside the web root with restrictive runtime permission and no browser disclosure;
- tested non-disclosure of `.env`, `php.ini`, `.user.ini`, and `vendor/autoload.php`;
- active HTTPS serving on `oneqay.n07.my.id`;
- synthetic sign-in and server-verified tenant/outlet/device context selection;
- file-backed Preview session persistence across requests;
- M7.4/M7.4A synthetic POS execution from catalog/cart through server-authoritative CASH sale and receipt;
- safe correlation references in live responses;
- logout/session clearing with fail-closed direct POS access after logout.

This is material evidence improvement over the earlier screenshot-only P1 classification.

It does **not** qualify relational persistence or the complete M7.5 control set.

## Remaining blocking evidence

At minimum, the following remain incomplete or not supplied for overall M7.5 qualification:

- actual oneQay relational database connectivity on an allowed DEC-005R engine profile;
- least-privilege database account/grant evidence;
- database connection-limit visibility;
- transaction semantics on the selected engine profile;
- database-backed two-tenant negative isolation;
- successful isolated restore evidence;
- schema/migration boundary evidence where separately authorized;
- DEC-005R portability-contract qualification;
- queue/scheduler/background execution qualification where required;
- rollback/recovery rehearsal;
- remaining operational/resource-limit controls required by the fail-closed evaluator.

The active POS evidence remains explicitly synthetic/in-memory and `Not Production Ready`.

## Governance operating model

The active `main-protected-governance` ruleset has been normalized to the Product Owner operating model:

- mandatory approving review count: `0`;
- last-push approval requirement: `false`;
- review-thread resolution remains required;
- merge method remains squash-only;
- strict required status checks remain enabled;
- deletion protection remains enabled;
- non-fast-forward protection remains enabled;
- bypass actors remain empty.

Independent human review is not a mandatory merge gate under the current ruleset. Required CI/security checks and exact-head Product Owner merge authority remain mandatory controls.

## Lifecycle preservation

The M7.5 preparation/release-artifact/runtime-readiness publications and the live P1 evidence session do not authorize or claim overall runtime qualification, M7.6 recovery rehearsal, M7.7 acceptance, Release, or Production.

Current lifecycle remains:

- M7.0: **DONE / PUBLISHED**;
- M7.1: **DONE / PUBLISHED**;
- M7.2: **DONE / PUBLISHED**;
- M7.3: **DONE / PUBLISHED**;
- M7.4: **DONE / PUBLISHED**;
- M7.4A: **DONE / PUBLISHED**;
- M7.5 PREPARATION: **DONE / PUBLISHED**;
- M7.5 P1 LIVE WEB RUNTIME EVIDENCE: **VERIFIED / MATERIAL PROGRESS**;
- M7.5 RELATIONAL ENGINE PROFILE QUALIFICATION: **BLOCKED / NOT SUPPLIED**;
- M7.5 OVERALL QUALIFICATION: **BLOCKED / INCOMPLETE**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Phase 0: **IN PROGRESS**;
- Phase 0 Exit: **NOT APPROVED**;
- Sprint 14: **NOT AUTHORIZED**;
- Release: **NOT AUTHORIZED**;
- Production: **NOT AUTHORIZED**;
- Production readiness: **NO-GO**.

## Next bounded work

The next bounded M7.5 work is to convert the newly observed live P1 web-runtime facts into the deterministic qualification evidence model and then acquire only the still-missing relational-engine/recovery/operational evidence under separate bounded authority where required.

Unknown or missing runtime capabilities must remain `PARTIAL`, `UNVERIFIED`, `NOT_SUPPLIED`, or `UNAVAILABLE`; they must never be promoted to `VERIFIED` by inference.

A deterministic `BLOCKED` qualification result remains valid evidence while the required relational and recovery controls are incomplete.

## Relational qualification reconciliation — 2026-08-15

This section is the current minimum-delta reconciliation layer for relational qualification and supersedes the earlier `RELATIONAL ENGINE PROFILE QUALIFICATION: BLOCKED / NOT SUPPLIED` wording above for current-state interpretation. The earlier text is retained as historical pre-PR-#111 evidence state.

PR #111 — `feat(m7.5): add bounded Preview relational qualification probe` is **CLOSED / MERGED / PUBLISHED**. Its authorized source head was:

`93598d20bb44de74b8918a543140394be133dd0e`

Current published `main` after the squash merge is:

`0edea8cdcc0cb7f16c8e8758aa626e79b4096cf8`

with tree:

`effa878587f0175b928f92f91dc9612411c4f24c`

The active non-Production Preview release for this evidence is:

`m75-preview-0edea8cdcc0c`

Sanitized live evidence from the protected Technical Preview relational probe verifies:

- MariaDB `11.4.8` engine profile;
- application database connectivity;
- dedicated least-privilege qualification behavior;
- transaction rollback semantics;
- connection-scoped temporary-table capability;
- tenant-scoped relational query behavior within the bounded probe;
- permanent-schema mutation denied by the qualification privilege boundary;
- `persistent_schema_created = false`;
- `production_ready = false`.

The bounded endpoint result is **QUALIFIED** for `technical-preview-relational-probe`. This does not mean the complete DEC-005R engine-profile evidence package or the overall M7.5 29-control evaluator is complete.

The current machine-readable reconciliation is recorded in:

- `docs/evidence/runtime/p1-cpanel-live-relational-20260815.json`;
- `docs/evidence/runtime/p1-cpanel-live-relational-20260815.report.json`;
- `docs/handbook/M7_5_P1_RELATIONAL_QUALIFICATION_EVIDENCE_20260815.md`.

The deterministic current classification is:

- verified mandatory controls: **13**;
- blocking mandatory controls: **16**;
- complete evaluator outcome: **BLOCKED**;
- lifecycle authority created: **false**.

The remaining engine-profile blockers are:

- `ENGINE:CONNECTION_LIMIT_VISIBILITY:UNVERIFIED`;
- `ENGINE:PORTABILITY_CONTRACT:UNVERIFIED`;
- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`.

The tenant-scoped temporary relational query is real bounded evidence, but it is not promoted to complete durable two-tenant negative-isolation evidence because no permanent relational business schema exists. The qualification account's lack of permanent DDL privileges plus `persistent_schema_created = false` is recorded as a verified deny-by-capability migration boundary, not as authorization for future migrations.

The remaining runtime blockers are:

- `RUNTIME:BACKGROUND_EXECUTION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`;
- `RUNTIME:DEPLOYMENT_RECOVERY:PARTIAL`;
- `RUNTIME:ENVIRONMENT_SECRETS:PARTIAL`;
- `RUNTIME:OBSERVABILITY_LOGGING:PARTIAL`;
- `RUNTIME:OUTBOUND_DNS_HTTPS:PARTIAL`;
- `RUNTIME:PHP_CLI:PARTIAL`;
- `RUNTIME:QUEUE_EXECUTION:UNVERIFIED`;
- `RUNTIME:RESOURCE_LIMITS:PARTIAL`;
- `RUNTIME:ROLLBACK:NOT_SUPPLIED`;
- `RUNTIME:SCHEDULER_CRON:PARTIAL`;
- `RUNTIME:SECURITY_BOUNDARY:PARTIAL`.

Therefore the current distinction is:

- M7.5 preparation: **DONE / PUBLISHED**;
- M7.5 live web-runtime evidence: **VERIFIED / MATERIAL PROGRESS**;
- M7.5 bounded MariaDB relational probe: **QUALIFIED / VERIFIED**;
- M7.5 complete 29-control evidence package: **BLOCKED / INCOMPLETE**;
- M7.6: **BLOCKED / NOT AUTHORIZED**;
- M7.7: **BLOCKED / NOT AUTHORIZED**;
- Production readiness: **NO-GO**;
- Production authority: **NONE**.

No credential, raw `.env`, database identity, database username, password, screenshot, Production data, or permanent relational schema is introduced by this reconciliation.

# Sprint 13 Entry Gate — Schema Change Review and Approval Envelope Foundation

## Gate status

- Gate preparation: Authorized by Product Owner.
- Sprint 13 source implementation: Not Authorized.
- Canonical Phase 0: In Progress.
- Production readiness: NO-GO.
- Deployment: None.
- Release: None.

## Exact preparation base

- Repository: `labzefry/oneQay`.
- Base branch: `main`.
- Exact base commit: `ad4d88acb96b49141fedc125393c4caaf4384aa7`.
- Exact base tree: `3ff4e4aefbf2b0064283a29e53a144797f03ee3c`.
- Latest published technical capability: Sprint 12 — Physical Schema Plan Representation and Change Classification Foundation.

## Objective

Prepare a bounded, framework-agnostic foundation that can convert a published Sprint 12 `PhysicalSchemaPlan` into an immutable, safe, auditable review decision envelope without generating SQL, creating migration artifacts, opening a database connection, executing a migration, or defining a final business schema.

The capability exists only to answer whether a physical-schema plan is:

- `NOT_REQUIRED` when the plan disposition is `NO_CHANGES`;
- `APPROVED_FOR_MIGRATION_PLANNING` or `REJECTED` when the plan disposition is `REVIEW_REQUIRED`;
- always denied from approval when the plan disposition is `BLOCKED`.

`APPROVED_FOR_MIGRATION_PLANNING` is not migration authority and is never execution authority.

## Why this is the next bounded foundation

Sprint 12 already produces deterministic physical-schema change plans and blocks destructive, tenant-boundary, tenant-key, physical-mapping, scalar-mapping, primary-index, referential, and vendor changes. Sprint 09 already provides non-production migration governance and dry-run safety primitives. The missing boundary is an explicit, auditable review envelope between schema-change classification and any later migration-planning activity.

This gate therefore remains infrastructure/foundation work and does not start a business module, application skeleton, database implementation, or production migration.

## Proposed Sprint 13 implementation scope

If and only if the Product Owner later grants `START SPRINT 13 IMPLEMENTATION`, the implementation is limited to:

1. Introduce immutable review-decision vocabulary and validation for `PhysicalSchemaPlan`.
2. Preserve the exact source plan fingerprint and disposition in the review envelope.
3. Accept only safe reviewer reference, stable decision/reason code, and validated correlation ID; no arbitrary sensitive payload.
4. Make `BLOCKED` plans impossible to approve.
5. Make `NO_CHANGES` deterministic and non-approvable as a migration change.
6. Permit `REVIEW_REQUIRED` plans to be approved only for later migration planning or rejected.
7. Produce deterministic safe JSON that contains no raw manifest, SQL, credential, endpoint, tenant data, path, arbitrary exception text, or executable instruction.
8. Keep all behavior synthetic and in-memory.

## Exact allowed implementation paths after separate authorization

Only these paths may change during Sprint 13 implementation:

1. `src/SchemaPlanning/Foundation.php`
2. `src/SchemaPlanning/Review.php` — new
3. `tests/schema-planning.php`
4. `docs/SCHEMA_CHANGE_REVIEW_AND_APPROVAL_ENVELOPE_FOUNDATION.md` — new

Any other changed path is blocking and requires a new Product Owner scope decision.

`composer.json` is intentionally excluded. The existing `composer test` script already executes `tests/schema-planning.php`, so Sprint 13 evidence can remain inside the existing schema-planning test runner.

## Architecture boundary

- Preserve Modular Monolith and Clean Architecture.
- Stay inside the published `SchemaPlanning` foundation boundary.
- Depend only on published foundation contracts; do not add framework, database, transport, filesystem, cloud, UI, or vendor dependencies.
- Do not create a business bounded context or promote any Proposed bounded-context hypothesis.
- Do not change the semantics of Sprint 12 change classification.
- Do not turn review approval into migration execution authority.

## Security boundary

- Deny by default.
- `BLOCKED` plans can never be approved.
- Review input must reject malformed disposition, fingerprint, correlation ID, reviewer reference, and unsupported decision/reason codes with stable safe errors.
- Output must not leak raw manifests, SQL, credentials, database names, endpoints, local paths, tenant data, arbitrary exception text, or secrets.
- No network, filesystem side effect, environment mutation, database connection, or external service call.
- No free-form approval text is required by the foundation; use stable reason codes to minimize leakage risk.

## Tenancy boundary

- Tenant-boundary and tenant-key changes remain `BLOCKED` under Sprint 12 and Sprint 13 must not provide an override path.
- No tenant record, tenant data, tenant query, cross-tenant access, or final tenancy schema may be introduced.
- The review envelope may carry only safe fingerprints and identifiers already permitted by the published schema-plan boundary.

## Database and migration boundary

Sprint 13 must not create or execute:

- SQL, DDL, or DML;
- migration files or migration artifacts;
- schema renderer;
- database adapters or connections;
- metadata introspection;
- production tables;
- final tenant or business schema;
- backfill behavior;
- online schema change behavior;
- migration lock implementation;
- rollback execution;
- deployment or release behavior.

The strongest positive decision allowed is `APPROVED_FOR_MIGRATION_PLANNING`, which means only that a future separately authorized capability may translate an additive reviewed plan into non-executable migration planning material.

## Testing and evidence requirements

All evidence must be produced on the exact candidate head.

Mandatory syntax evidence:

- `php -l src/SchemaPlanning/Foundation.php`
- `php -l src/SchemaPlanning/Review.php`
- `php -l tests/schema-planning.php`

Mandatory bounded tests:

- `php tests/schema-planning.php`

Mandatory regression gate:

- `composer test`

The full `composer test` result is a pre-Ready requirement for Sprint 13 and must be explicitly evidenced on the exact candidate head. The historical Sprint 12 missing full-regression evidence remains a lifecycle exception and is not retroactively repaired by Sprint 13 testing.

Required test coverage includes:

- `NO_CHANGES` produces `NOT_REQUIRED` deterministically;
- `REVIEW_REQUIRED` can be approved for migration planning;
- `REVIEW_REQUIRED` can be rejected;
- `BLOCKED` approval is denied;
- tenant-boundary and tenant-key blocked plans cannot be overridden;
- source plan fingerprint is preserved;
- review decision is immutable;
- identical inputs produce identical canonical review output;
- invalid reviewer/correlation/decision/reason input is rejected with stable errors;
- safe JSON contains no raw manifest, SQL, credential, endpoint, tenant data, path, or arbitrary exception text;
- no network or database access occurs;
- published Sprint 12 schema-planning regressions remain green.

## Acceptance criteria

Sprint 13 implementation can be considered technically acceptable only when all of the following are true:

1. Exactly the authorized implementation paths changed.
2. The review envelope is immutable and deterministic for equivalent input.
3. `BLOCKED` cannot transition to an approved state.
4. `NO_CHANGES` cannot be misrepresented as a migration change requiring approval.
5. `REVIEW_REQUIRED` approval explicitly means migration planning only.
6. Tenant isolation protections from Sprint 12 are preserved with no override path.
7. No SQL, migration artifact, database access, business schema, deployment, release, POS, ERP, or vertical behavior exists.
8. Safe-output negative tests pass.
9. Syntax checks, bounded tests, and full `composer test` pass on the exact final head.
10. GitHub required checks pass on the exact final head.
11. Independent reviewer `zefriansyah` approves the exact final head with no unresolved review thread.
12. No push occurs after approval without re-review.

## Definition of Done

`Done` requires implementation evidence, exact-head regression evidence, required GitHub checks, independent exact-head approval, zero unresolved review threads, and separate Product Owner lifecycle authorization for publication.

Passing tests or reviewer approval alone does not authorize Ready transition, merge, deployment, release, migration, or Sprint 14 work.

## Required GitHub checks

- `governance-validation`
- `markdown-lint`
- `secret-scan`

Any required check not successful on the exact final head is blocking.

## Independent review

Reviewer: `zefriansyah`.

Review must be anchored to the exact final head and verify scope, safe-output behavior, deny-by-default semantics, tenancy protection, absence of SQL/database execution, test evidence, changed files, and unresolved threads.

Reviewer approval is not Product Owner Ready or merge authorization.

## Explicitly prohibited

- application skeleton;
- framework adoption decision;
- ADR-001 through ADR-007 promotion;
- GD-007 promotion;
- JRN-003 or JRN-013 resolution;
- POS, ERP, catalog, sales, payment, inventory, organization, outlet, device, or other business-module implementation;
- final tenant model or business schema;
- production or staging database use;
- migration execution;
- hosting deployment;
- installer/updater changes;
- workflow or ruleset changes;
- release creation.

## Decision dependencies

No substantive ADR, GD-007, JRN-003, or JRN-013 decision is required to prepare this gate because the candidate remains framework-, vendor-, database-, and business-domain agnostic and preserves all Proposed/Unresolved statuses.

Implementation still requires a separate explicit Product Owner command: `START SPRINT 13 IMPLEMENTATION`.

## Entry-gate documentation PR stop condition

The entry-gate documentation PR must remain Draft after required checks and independent review request. Do not mark Ready, merge, or implement Sprint 13 without a separate Product Owner authorization.

Attribution: Lab | zefry

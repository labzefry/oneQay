# Schema Change Review and Approval Envelope Foundation

## Purpose

Sprint 13 adds a bounded review boundary over the published Sprint 12 `PhysicalSchemaPlan`. The foundation converts an immutable physical-schema plan into an immutable review envelope without generating SQL, creating migration artifacts, opening a database connection, or authorizing execution.

The strongest positive result is `APPROVED_FOR_MIGRATION_PLANNING`. That decision authorizes only a later, separately governed migration-planning activity. It is not migration execution, SQL execution, deployment, production, or schema-modification authority.

## Scope

The capability provides:

- stable review decision vocabulary;
- stable review reason codes;
- validated reviewer references;
- validated review correlation IDs;
- deterministic source-plan fingerprinting;
- immutable review envelopes;
- deny-by-default transition validation;
- safe canonical JSON output.

The capability remains synthetic, in-memory, framework-agnostic, and infrastructure-independent.

## Architecture boundary

The implementation remains inside `OneQay\\SchemaPlanning` and depends only on the published Sprint 12 schema-planning contracts. `src/SchemaPlanning/Foundation.php` loads the review foundation after the existing planning types.

No framework, transport, filesystem, environment, cloud, database, vendor-specific adapter, or external service dependency is introduced.

## Review vocabulary

Review decisions are intentionally closed:

- `NOT_REQUIRED`;
- `APPROVED_FOR_MIGRATION_PLANNING`;
- `REJECTED`.

Reason codes are intentionally closed:

- `NO_CHANGES`;
- `REVIEW_ACCEPTED`;
- `REVIEW_REJECTED`;
- `BLOCKED_CHANGE_REJECTED`.

Free-form approval text is not authority and is not accepted by this foundation.

## Transition semantics

The transition matrix is deny-by-default:

| Source plan disposition | Allowed decision | Required reason code |
| --- | --- | --- |
| `NO_CHANGES` | `NOT_REQUIRED` | `NO_CHANGES` |
| `REVIEW_REQUIRED` | `APPROVED_FOR_MIGRATION_PLANNING` | `REVIEW_ACCEPTED` |
| `REVIEW_REQUIRED` | `REJECTED` | `REVIEW_REJECTED` |
| `BLOCKED` | `REJECTED` | `BLOCKED_CHANGE_REJECTED` |

Every other combination is rejected using a stable safe error code. A `BLOCKED` plan has no approval path. Attempting `APPROVED_FOR_MIGRATION_PLANNING` for a `BLOCKED` plan fails closed with `SCHEMA_REVIEW_APPROVAL_FORBIDDEN`.

## Deterministic behavior

The review foundation does not read wall-clock time, create random identifiers, consult environment state, use mutable global state, or make network calls.

A source-plan fingerprint is calculated as SHA-256 over the canonical JSON representation already exposed by the immutable Sprint 12 plan. Because Sprint 12 canonicalizes and stably sorts its plan content, equivalent semantic plan input produces the same source-plan fingerprint.

For identical source plan, reviewer reference, review correlation ID, decision, and reason code, the canonical review JSON is identical.

## Fingerprint preservation

The envelope preserves:

- the deterministic source-plan fingerprint;
- the Sprint 12 baseline manifest fingerprint;
- the Sprint 12 target manifest fingerprint;
- the exact source plan disposition;
- the source plan correlation ID.

The review boundary does not include raw manifest content or raw schema-change payloads.

## Reviewer reference

`ReviewerReference` is a validated safe identifier. It is trimmed, limited to 64 characters, and accepts only letters, digits, underscore, dot, and hyphen, beginning with an alphanumeric character.

This field is an auditable reference only. It does not perform authentication, authorization, identity lookup, or role resolution.

## Correlation ID

The review correlation ID reuses the published `CorrelationId` validation boundary. It is caller-provided and deterministic. Invalid values fail using the existing stable schema-planning error code.

The review correlation ID is distinct from the preserved source-plan correlation ID, allowing a later audit trail to correlate planning and review without adding time-based or random semantic input.

## Stable errors

Review-specific failures expose stable safe error codes:

- `SCHEMA_REVIEW_REVIEWER_REFERENCE_INVALID`;
- `SCHEMA_REVIEW_DECISION_INVALID`;
- `SCHEMA_REVIEW_REASON_CODE_INVALID`;
- `SCHEMA_REVIEW_TRANSITION_INVALID`;
- `SCHEMA_REVIEW_APPROVAL_FORBIDDEN`.

Existing `CorrelationId` validation continues to expose `SCHEMA_PLANNING_CORRELATION_ID_INVALID`.

Error messages are fixed implementation text. Caller-supplied values are not reflected into exception messages.

## Tenancy protections

Sprint 13 does not define a final tenancy model and does not add tenant tables, tenant queries, tenant records, cross-tenant access, or isolation policy changes.

Sprint 12 classifies tenant-scope and tenant-key changes as `BLOCKED`. Sprint 13 preserves that disposition and provides no override path. Any `BLOCKED` plan, including tenant-boundary and tenant-key changes, cannot be approved for migration planning.

## Security boundary

The review API is deny-by-default and accepts only closed enums or validated safe identifiers. It does not accept arbitrary metadata or free-form approval payloads.

The safe output contains only:

- fingerprints;
- stable dispositions and decision codes;
- correlation IDs;
- validated reviewer reference;
- stable reason code.

It does not contain raw manifests, SQL, DDL, DML, database credentials, tenant records, infrastructure endpoints, local filesystem paths, production information, secrets, tokens, or arbitrary exception text.

The JSON output is data only and is not an executable instruction.

## Migration boundary

Sprint 13 stops at the review decision envelope. It does not create:

- SQL or executable SQL;
- DDL or DML;
- migration files or migration artifacts;
- migration runners;
- schema renderers;
- metadata introspection;
- database connections;
- backfills;
- migration locks;
- online schema-change execution;
- rollback execution;
- production adapters.

`APPROVED_FOR_MIGRATION_PLANNING` means only that a future separately authorized capability may consume the reviewed plan for non-executable migration planning.

## Explicit non-authority semantics

No decision emitted by this foundation grants:

- migration execution authority;
- SQL execution authority;
- deployment authority;
- production authority;
- schema modification authority;
- release authority;
- Sprint 14 authority.

Product Owner lifecycle authority remains external to this source capability.

## Testing

Sprint 13 tests extend `tests/schema-planning.php` and preserve the Sprint 12 regression surface. Coverage includes:

- deterministic `NO_CHANGES` to `NOT_REQUIRED`;
- `REVIEW_REQUIRED` approval for migration planning;
- `REVIEW_REQUIRED` rejection;
- blocked-plan approval denial;
- tenant-scope and tenant-key blocked-plan denial;
- source-plan fingerprint preservation;
- immutable review envelope behavior;
- canonical equivalent output for equivalent input;
- invalid reviewer, correlation, decision, and reason rejection;
- safe-output negative assertions;
- absence of network and database access;
- existing Sprint 12 schema-planning behavior.

The lifecycle gate additionally requires PHP syntax validation, the bounded schema-planning test, full `composer test`, required GitHub checks, and exact-head independent review.

## Limitations

This foundation does not authenticate reviewers, persist review records, provide digital signatures, implement workflow orchestration, render migration plans, or execute any infrastructure action.

It also does not define final tenant data models, final business schemas, POS/ERP behavior, or industry-vertical behavior.

## Deferred work

Future separately authorized work may address persistence of review audit records, stronger reviewer identity binding, migration-plan representation, CI quality gates, and enterprise product architecture. None of that is implemented by Sprint 13.

## Next dependency

The next technical dependency after Sprint 13 publication is a separately authorized planning or governance milestone. No later implementation authority is implied by this document.

Attribution: Lab | zefry

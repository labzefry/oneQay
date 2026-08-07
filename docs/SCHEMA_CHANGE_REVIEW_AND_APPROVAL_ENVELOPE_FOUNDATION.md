# Schema Change Review and Approval Envelope Foundation

## Status

Sprint 13 implements a bounded, framework-agnostic review envelope over the published Sprint 12 `PhysicalSchemaPlan`.

This foundation is not migration execution authority, does not create migration artifacts, and does not define a final tenant or business schema.

## Decision model

The review envelope supports exactly three decisions:

- `NOT_REQUIRED`;
- `APPROVED_FOR_MIGRATION_PLANNING`;
- `REJECTED`.

The disposition boundary is fixed:

- `NO_CHANGES` accepts only `NOT_REQUIRED` with reason `NO_CHANGES`;
- `REVIEW_REQUIRED` accepts either `APPROVED_FOR_MIGRATION_PLANNING` with `REVIEW_APPROVED` or `REJECTED` with `REVIEW_REJECTED`;
- `BLOCKED` can never be approved and accepts only `REJECTED` with `PLAN_BLOCKED`.

`APPROVED_FOR_MIGRATION_PLANNING` means only that a future separately authorized capability may produce non-executable migration-planning material. It never authorizes SQL generation, migration execution, database access, deployment, or release.

## Immutable review envelope

`SchemaChangeReviewEnvelope` is readonly and contains only safe review metadata:

- source plan fingerprint;
- source plan disposition;
- source plan correlation ID;
- review correlation ID;
- safe reviewer reference;
- stable decision;
- stable reason code.

The source plan fingerprint is the SHA-256 fingerprint of the deterministic safe JSON representation of the published `PhysicalSchemaPlan`. Raw manifests and raw schema payloads are not copied into the review envelope.

No timestamp is embedded in the envelope because equivalent review input must produce equivalent canonical output.

## Validation and stable errors

`DeterministicSchemaChangeReviewer` validates:

- reviewer references;
- review correlation IDs through the published `CorrelationId` boundary;
- supported decisions;
- supported reason codes;
- disposition/decision/reason compatibility.

Stable review error codes include:

- `SCHEMA_REVIEW_REVIEWER_REFERENCE_INVALID`;
- `SCHEMA_REVIEW_DECISION_INVALID`;
- `SCHEMA_REVIEW_REASON_CODE_INVALID`;
- `SCHEMA_REVIEW_DECISION_NOT_ALLOWED`;
- `SCHEMA_REVIEW_REASON_CODE_NOT_ALLOWED`;
- `SCHEMA_REVIEW_BLOCKED_APPROVAL_DENIED`;
- `SCHEMA_REVIEW_PLAN_FINGERPRINT_INVALID`.

No arbitrary free-form approval text is accepted.

## Security and tenancy boundary

The foundation is deny-by-default.

A `BLOCKED` Sprint 12 plan cannot be promoted through Sprint 13. This includes plans blocked because of tenant-scope or tenant-key changes; there is no override path.

The implementation introduces no:

- SQL, DDL, or DML;
- migration files or migration execution;
- database adapters or connections;
- network calls;
- filesystem side effects;
- environment mutation;
- credentials, endpoints, database names, local paths, or tenant records in review output;
- POS, ERP, or industry-vertical behavior.

## Determinism

Equivalent `PhysicalSchemaPlan`, reviewer reference, review correlation ID, decision, and reason code produce byte-equivalent JSON review envelopes.

The envelope preserves the exact source disposition and source correlation ID while carrying only a fingerprint of the source plan content.

## Implementation paths

Sprint 13 is bounded to:

1. `src/SchemaPlanning/Foundation.php`;
2. `src/SchemaPlanning/Review.php`;
3. `tests/schema-planning.php`;
4. `docs/SCHEMA_CHANGE_REVIEW_AND_APPROVAL_ENVELOPE_FOUNDATION.md`.

No `composer.json`, workflow, ruleset, database, deployment, release, ADR/GD, JRN, or business-module change is part of this implementation.

## Acceptance evidence

The exact candidate head must provide:

- PHP syntax checks for `Foundation.php`, `Review.php`, and `tests/schema-planning.php`;
- `php tests/schema-planning.php` passing;
- full `composer test` passing;
- safe-output negative coverage;
- successful `governance-validation`, `markdown-lint`, and `secret-scan`;
- independent exact-head review by `zefriansyah`;
- zero unresolved review threads;
- no post-approval mutation without re-review.

Passing technical evidence is not Ready, merge, publication, migration, deployment, release, or Sprint 14 authority.

Attribution: Lab | zefry

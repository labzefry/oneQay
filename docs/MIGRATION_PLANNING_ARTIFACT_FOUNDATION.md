# Migration Planning Artifact Foundation

## Status

Sprint 14 implements the bounded **Migration Planning Artifact Foundation** authorized by `docs/SPRINT_14_MIGRATION_PLANNING_ARTIFACT_ENTRY_GATE.md`.

This foundation consumes a published Sprint 12 `PhysicalSchemaPlan` and its matching Sprint 13 `SchemaChangeReviewEnvelope`. It produces safe, deterministic, non-executable planning material only when that exact plan has been approved for migration planning.

Attribution: **Lab | zefry**

## Purpose

The foundation closes the deliberate boundary left by Sprint 13:

`PhysicalSchemaPlan` → exact review approval → **non-executable migration planning artifact**.

It does not add the next boundary:

migration planning artifact → framework migration → database execution.

That later transition remains separately gated.

## Approval binding

`DeterministicMigrationPlanningArtifactBuilder` accepts:

1. one `PhysicalSchemaPlan`;
2. one `SchemaChangeReviewEnvelope`;
3. one bounded planning correlation ID.

The builder fails closed unless all of the following are true:

- the plan disposition is `REVIEW_REQUIRED`;
- review decision is `APPROVED_FOR_MIGRATION_PLANNING`;
- review reason is `REVIEW_APPROVED`;
- review source disposition equals the supplied plan disposition;
- review source correlation ID equals the supplied plan correlation ID;
- SHA-256 of the deterministic supplied plan JSON equals the review source-plan fingerprint.

A review envelope for another plan cannot be reused.

## Allowed additive changes

Only these Sprint 12 additive kinds can become planning steps:

- `ENTITY_CREATED`;
- `ATTRIBUTE_ADDED`;
- `UNIQUE_INDEX_ADDED`;
- `REFERENCE_ADDED`.

Each source change must retain `REVIEW_REQUIRED` risk, have no before fingerprint, and have a target/after fingerprint.

All other kinds fail closed. In particular, there is no planning path for:

- removal;
- physical mapping mutation;
- scalar mapping mutation;
- primary-index mutation;
- unique-index removal/mutation;
- reference removal/mutation;
- tenant-scope change;
- tenant-key change;
- vendor change.

## Immutable planning step

`MigrationPlanningStep` is read-only and carries only:

- stable source change ID;
- allowed additive change kind;
- safe logical entity identifier;
- optional safe logical component identifier;
- before fingerprint, which must remain `null` for the allowed additive scope;
- after fingerprint.

No raw physical definition or executable instruction is copied into a step.

## Immutable planning artifact

`MigrationPlanningArtifact` is read-only and contains:

- exact source-plan fingerprint;
- source review correlation ID;
- planning correlation ID;
- safe reviewer reference;
- baseline manifest fingerprint;
- target manifest fingerprint;
- deterministic ordered planning steps.

The artifact includes no timestamp so equivalent bounded input remains byte-equivalent under canonical JSON encoding.

## Determinism

The builder preserves the stable ordering already enforced by `PhysicalSchemaPlan` and does not introduce random values, current time, filesystem state, database state, or network state.

Equivalent plan, review, and planning correlation inputs therefore produce equivalent JSON.

## Error boundary

Stable Sprint 14 error codes include:

- `MIGRATION_PLANNING_REVIEW_NOT_APPROVED`;
- `MIGRATION_PLANNING_SOURCE_PLAN_FINGERPRINT_MISMATCH`;
- `MIGRATION_PLANNING_SOURCE_DISPOSITION_MISMATCH`;
- `MIGRATION_PLANNING_SOURCE_CORRELATION_MISMATCH`;
- `MIGRATION_PLANNING_PLAN_DISPOSITION_INVALID`;
- `MIGRATION_PLANNING_CHANGE_KIND_NOT_ALLOWED`;
- `MIGRATION_PLANNING_CHANGE_RISK_INVALID`;
- `MIGRATION_PLANNING_CHANGE_FINGERPRINT_INVALID`.

Errors expose stable codes and bounded messages only; arbitrary database, runtime, exception, or tenant payload is not accepted.

## Security and tenant-isolation boundary

The foundation remains deny-by-default.

Sprint 12 tenant-scope and tenant-key mutations remain `BLOCKED`, Sprint 13 cannot approve them, and Sprint 14 independently rejects unsupported change kinds. This creates layered fail-closed protection rather than relying on one upstream check.

The artifact does not read or serialize tenant records. It handles only the safe identifiers and fingerprints already present in the governed schema-planning boundary.

## Database and migration-execution boundary

Sprint 14 introduces no:

- SQL, DDL, or DML generation;
- framework migration files;
- database adapter or connection;
- metadata introspection;
- migration lock;
- schema mutation;
- table creation;
- backfill;
- rollback execution;
- network call;
- filesystem side effect;
- deployment or release behavior.

`APPROVED_FOR_MIGRATION_PLANNING` plus a Sprint 14 artifact still does **not** mean approved for migration generation or execution.

## Implementation paths

Exactly the Sprint 14 entry-gate envelope is used:

1. `src/SchemaPlanning/Foundation.php`;
2. `src/SchemaPlanning/MigrationPlanning.php`;
3. `tests/schema-planning.php`;
4. `docs/MIGRATION_PLANNING_ARTIFACT_FOUNDATION.md`.

No dependency manifest, workflow, application source, database, deployment, updater, or release file is part of Sprint 14.

## Test evidence requirements

`tests/schema-planning.php` preserves Sprint 12 and Sprint 13 regression coverage and extends it with Sprint 14 assertions for:

- approved additive entity/attribute/index/reference planning;
- deterministic JSON;
- read-only artifact and step types;
- exact source-plan fingerprint binding;
- exact source correlation binding;
- rejected and no-change review denial;
- blocked-plan denial;
- unsupported tenant-boundary change denial;
- malformed additive fingerprint denial;
- stable source change ID preservation;
- safe-output negative scanning;
- absence of SQL, PDO/database access, network calls, and filesystem side effects.

The repository's existing `composer test` includes `tests/schema-planning.php`, so no Composer script mutation is required.

## Lifecycle boundary

Sprint 14 publication does not authorize:

- framework migration generation;
- migration execution;
- application durable persistence;
- final business schema;
- live database or cPanel mutation;
- deployment;
- Release or GitHub Release;
- updater installation;
- Production/customer data;
- real payment;
- Production readiness.

Production readiness remains **NO-GO**.

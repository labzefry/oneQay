# Governed Migration Artifact Bridge Foundation

## Status

Sprint 15 implements the bounded **Governed Migration Artifact Bridge Foundation** authorized by `docs/SPRINT_15_GOVERNED_MIGRATION_ARTIFACT_BRIDGE_ENTRY_GATE.md` and refined by `docs/SPRINT_15_TEST_ENVELOPE_SUPPLEMENT.md`.

The bridge connects the published Sprint 14 `MigrationPlanningArtifact` to the existing framework-agnostic Migration Foundation without introducing SQL, framework migration files, database access, schema mutation, or migration execution.

Attribution: **Lab | zefry**

## Purpose

Sprint 14 intentionally stopped at a safe, deterministic, non-executable planning artifact.

The repository separately already contained migration-governance primitives such as `MigrationDefinition`, `MigrationManifest`, deterministic checksums, dependency ordering, dry-run planning, lock abstraction, and synthetic dry-run execution.

Sprint 15 closes only the representation gap between those two boundaries:

`PhysicalSchemaPlan`

→ approved review envelope

→ Sprint 14 `MigrationPlanningArtifact`

→ **Sprint 15 governed migration manifest artifact**

It still does not add:

`governed migration manifest artifact`

→ framework migration file

→ SQL

→ database execution.

Those transitions remain separately gated.

## Bridge contract

`DeterministicGovernedMigrationArtifactBridge` accepts:

1. one valid Sprint 14 `MigrationPlanningArtifact`;
2. one bounded bridge correlation ID.

It derives a SHA-256 fingerprint from the exact deterministic JSON form of the supplied Sprint 14 artifact and preserves:

- source planning correlation ID;
- source review correlation ID;
- bridge correlation ID;
- reviewer reference;
- baseline manifest fingerprint;
- target manifest fingerprint;
- every stable source change identifier.

The resulting `GovernedMigrationManifestArtifact` is immutable/read-only and JSON serializable.

## Allowed additive scope

The bridge independently allows only the Sprint 14 additive change kinds:

- `ENTITY_CREATED`;
- `ATTRIBUTE_ADDED`;
- `UNIQUE_INDEX_ADDED`;
- `REFERENCE_ADDED`.

Any unsupported kind fails closed.

The bridge also requires additive fingerprint semantics and independently rejects duplicated source change identities.

This duplicates important upstream checks by design so a later boundary does not rely on only one earlier validation layer.

## Deterministic migration identity

The existing Migration Foundation requires migration identifiers matching its established ordered identifier contract.

Sprint 15 creates deterministic identifiers in a non-temporal sequence namespace:

`MIG_00000000_<ordinal>_<kind>_<source-change-prefix>`

Examples are representation identities only. The numeric date/time-shaped segment is **not** an execution time, deployment time, Release time, or wall-clock timestamp.

Properties:

- no current time;
- no randomness;
- no filesystem state;
- no network state;
- no database state;
- deterministic lexical ordering;
- stable source-change binding;
- no raw database/table/column payload in the identifier.

## Conservative dependency chain

Sprint 14 preserves stable ordered planning steps but does not claim to provide a full executable database dependency graph.

Sprint 15 therefore uses a deliberately conservative serial chain:

- first generated migration definition has no dependency;
- every later definition depends on the immediately preceding generated definition.

This prevents generated definitions from being interpreted as independently executable and preserves deterministic ordering under the existing `MigrationManifest` contract.

A richer dependency graph is deferred to a separately authorized framework migration generation capability.

## Safety and rollback semantics

Every generated `MigrationDefinition` is classified as:

- `MigrationSafetyClassification::CAUTION`;
- `MigrationRollbackClassification::FORWARD_ONLY`.

Sprint 15 does not claim `SAFE` because it generates no executable migration operation.

Sprint 15 does not claim `REVERSIBLE` because it generates or validates no rollback implementation.

These classifications are intentionally restrictive and do not create migration-execution authority.

## Deterministic checksums

Each generated definition receives equal declared and artifact checksums through the existing `MigrationChecksum` contract.

The checksum is derived from bounded canonical material only:

- exact Sprint 14 planning-artifact fingerprint;
- deterministic ordinal;
- stable source change identifier;
- allowed additive change kind;
- safe logical entity identifier;
- optional safe logical component identifier;
- target/after fingerprint.

The raw checksum descriptor is not emitted in the governed artifact.

## Traceability binding

Each generated definition has one immutable `GovernedMigrationBinding`:

`source_change_id → migration_identifier`

The wrapper verifies that:

- binding count equals manifest definition count;
- source change identities are unique;
- migration identities are unique;
- binding order exactly matches manifest definition order.

The full traceability direction is therefore:

`PhysicalSchemaPlan`

→ `SchemaChangeReviewEnvelope`

→ `MigrationPlanningArtifact`

→ source planning artifact fingerprint

→ `GovernedMigrationBinding`

→ `MigrationDefinition`

→ `MigrationManifest`.

## Security and tenant-isolation boundary

Sprint 15 remains deny-by-default.

It introduces no:

- tenant-record read;
- database adapter or connection;
- database metadata introspection;
- raw schema reconstruction;
- credentials or secret handling;
- endpoint handling;
- network call;
- filesystem side effect;
- runtime migration directory write;
- live-target interaction.

Tenant-scope and tenant-key mutations remain non-bridgeable because Sprint 14 cannot produce such valid steps and Sprint 15 maintains its own allowlist check.

## Database and execution boundary

Sprint 15 introduces no:

- SQL;
- DDL;
- DML;
- Laravel migration files;
- framework migration generator;
- `artisan migrate` invocation;
- PDO connection;
- table creation or alteration;
- migration journal write;
- real migration lock;
- migration execution;
- schema rollback;
- data backfill;
- cPanel/live database action;
- deployment;
- Release/GitHub Release;
- updater activation;
- Production/customer data;
- Production-readiness promotion.

The existing synthetic executor is not called by the Sprint 15 bridge.

## Error boundary

Stable bridge error codes include:

- `MIGRATION_ARTIFACT_BRIDGE_STEP_COUNT_INVALID`;
- `MIGRATION_ARTIFACT_BRIDGE_STEP_KIND_NOT_ALLOWED`;
- `MIGRATION_ARTIFACT_BRIDGE_STEP_FINGERPRINT_INVALID`;
- `MIGRATION_ARTIFACT_BRIDGE_SOURCE_CHANGE_DUPLICATE`;
- `MIGRATION_ARTIFACT_BRIDGE_BINDING_INVALID`.

Errors expose bounded messages only and do not carry database, tenant, credential, endpoint, or arbitrary sensitive payloads.

## Implementation paths

Exactly the Sprint 15 refined implementation envelope is used:

1. `src/SchemaPlanning/Foundation.php`;
2. `src/SchemaPlanning/MigrationArtifactBridge.php`;
3. `tests/migration.php`;
4. `docs/GOVERNED_MIGRATION_ARTIFACT_BRIDGE_FOUNDATION.md`.

No dependency manifest, workflow, application source, database, deployment, updater, Customer Service, localization source, or release file is part of Sprint 15.

## Lifecycle boundary

Sprint 15 publication does not authorize:

- framework/Laravel migration generation;
- SQL generation;
- migration execution;
- live schema mutation;
- durable application persistence;
- final business schema;
- cPanel/live target change;
- deployment;
- Release/GitHub Release;
- updater installation;
- Customer Service source implementation;
- localization source implementation;
- Production/customer data;
- Production readiness.

Production readiness remains **NO-GO**.

## Likely next gate

After successful Sprint 15 publication, the narrowest likely successor is a separately authorized **Framework Migration Generation Foundation**.

That future capability may consume the governed Sprint 15 artifact and generate deterministic framework-specific migration material, but should still remain non-executing until a later migration execution foundation is separately authorized and validated.

Attribution: **Lab | zefry**

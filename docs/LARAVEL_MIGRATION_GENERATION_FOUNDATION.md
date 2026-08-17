# Laravel Migration Generation Foundation

## Status

Sprint 16 implements the bounded **Laravel Migration Generation Foundation** authorized by `docs/SPRINT_16_LARAVEL_MIGRATION_GENERATION_ENTRY_GATE.md`.

Status after publication: **IMPLEMENTED / GENERATION-ONLY / NON-EXECUTING**.

Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**

## Purpose

Sprint 14 established deterministic migration-planning artifacts.

Sprint 15 established deterministic governed migration manifests with migration identities, checksums, source-change bindings, conservative dependencies, and forward-only safety classification.

Sprint 16 adds the next representation boundary:

`MigrationPlanningArtifact`

+ `GovernedMigrationManifestArtifact`

+ target `DataDefinitionManifest`

+ target `PhysicalMappingManifest`

→ **deterministic in-memory Laravel migration file artifacts**.

Sprint 16 does not materialize those files into the Laravel application tree and does not execute them.

## Framework target

The exact Sprint 16 entry base records the web skeleton on Laravel `12.64.0`.

Generated source uses the framework migration surface represented by:

- `Illuminate\Database\Migrations\Migration`;
- `Illuminate\Database\Schema\Blueprint`;
- `Illuminate\Support\Facades\Schema`;
- anonymous migration classes;
- `Schema::create`;
- `Schema::table`.

The root generation foundation does not require Laravel classes to be installed in the root process. It emits PHP source text only.

## Generator contract

`DeterministicLaravelMigrationGenerator::generate()` accepts exactly:

1. one Sprint 14 `MigrationPlanningArtifact`;
2. one Sprint 15 `GovernedMigrationManifestArtifact`;
3. one target `DataDefinitionManifest`;
4. one target `PhysicalMappingManifest`;
5. one bounded generation correlation ID.

The result is an immutable `LaravelMigrationGenerationArtifact` containing immutable `LaravelMigrationFileArtifact` values.

## Exact source binding

Before source rendering, generation verifies:

- SHA-256 of the exact Sprint 14 planning artifact equals the Sprint 15 `sourcePlanningArtifactFingerprint`;
- source planning correlation IDs match;
- source review correlation IDs match;
- baseline fingerprints match;
- target fingerprints match;
- the supplied target physical mapping canonical fingerprint equals the governed Sprint 15 target fingerprint;
- planning-step count, governed-binding count, and governed migration-definition count are identical;
- each planning source-change identity matches the governed binding in the same deterministic position;
- each governed binding migration identifier matches the corresponding governed migration definition.

Any mismatch fails closed.

## Target data-definition binding

The target physical mapping is cryptographically bound to Sprint 14 and Sprint 15 through the governed target fingerprint.

Target `DataDefinitionManifest` carries semantics that the physical mapping deliberately does not contain, especially:

- nullability;
- default-value policy;
- logical primary-key semantics;
- logical unique-constraint semantics;
- logical reference semantics.

Sprint 16 therefore:

- validates the supplied target data definition with the existing `DataDefinitionPolicyValidator`;
- verifies each affected logical entity against its physical mapping;
- verifies mapped attributes and portable scalar constraints;
- verifies logical primary-key attributes equal the mapped physical primary-key logical attributes;
- verifies logical unique-constraint attribute sets correspond to physical unique indexes;
- verifies logical reference target/map semantics correspond to physical references;
- creates a deterministic SHA-256 target-definition fingerprint in the Sprint 16 generation artifact.

That target-definition fingerprint becomes reviewable traceability evidence for any later materialization stage.

Sprint 16 still does not claim that the data-definition fingerprint is execution authority.

## Allowed schema changes

Only the additive allowlist already established in Sprint 14 and Sprint 15 is generation-eligible:

- `ENTITY_CREATED`;
- `ATTRIBUTE_ADDED`;
- `UNIQUE_INDEX_ADDED`;
- `REFERENCE_ADDED`.

No removal, destructive mutation, tenant-scope mutation, tenant-key mutation, vendor mutation, scalar remapping, physical identifier mutation, primary-key mutation, or unsupported change kind is accepted.

## After-fingerprint verification

The generator independently reproduces the canonical target component fingerprint before rendering a file.

For `ENTITY_CREATED`, the complete target physical entity canonical form must reproduce the planning step `afterFingerprint`.

For `ATTRIBUTE_ADDED`, the exact target physical attribute canonical form must reproduce the `afterFingerprint`.

For `UNIQUE_INDEX_ADDED`, the exact target unique-index canonical form must reproduce the `afterFingerprint`.

For `REFERENCE_ADDED`, the exact target reference canonical form must reproduce the `afterFingerprint`.

This prevents generation from drifting away from the exact approved planning artifact.

## Deterministic file identity

Each generated file receives a deterministic representation path:

`database/migrations/0000_00_00_<six-digit-ordinal>_<change-kind>_<source-change-prefix>.php`

The `0000_00_00` segment is intentionally **non-temporal**.

No wall-clock timestamp, random value, environment value, filesystem state, database state, or network state participates in naming.

The generation artifact enforces:

- at least one file;
- unique relative paths;
- unique governed migration identifiers;
- lexical file ordering matching governed migration ordering.

The path is metadata only. Sprint 16 performs no write into `apps/web/database/migrations/`.

## File traceability

Every `LaravelMigrationFileArtifact` contains:

- exact stable source-change identifier;
- exact governed migration identifier;
- exact generation correlation ID;
- deterministic relative path;
- SHA-256 source fingerprint;
- generated PHP source text.

The traceability chain is therefore:

`PhysicalSchemaPlan`

→ review envelope

→ Sprint 14 planning artifact

→ Sprint 15 governed migration binding

→ governed migration identifier

→ Sprint 16 generated file artifact

→ generated source fingerprint.

## Scalar rendering

The bounded renderer supports the currently published physical scalar mapping vocabulary:

- `VARCHAR` → `$table->string()` with exact length;
- `BIGINT_SIGNED` → `$table->bigInteger()`;
- `DECIMAL` → `$table->decimal()` with exact precision and scale;
- `TINYINT_BOOLEAN` → `$table->boolean()`;
- `CHAR_UUID` → `$table->char(..., 36)`;
- `DATE` → `$table->date()`;
- `DATETIME` → `$table->dateTime()`;
- `JSON_DOCUMENT` → `$table->json()`.

Published UTF8MB4 character policy is represented with `charset('utf8mb4')`.

Supported published collations are represented as:

- Unicode CI → `utf8mb4_unicode_ci`;
- binary → `utf8mb4_bin`.

Unknown scalar or collation semantics fail closed.

## Nullability and defaults

Sprint 16 supports only defaults that can be reconstructed without guessing:

- `NONE`;
- `NULL_VALUE` for nullable attributes.

It fails closed for:

- `LITERAL_FINGERPRINT`, because the original literal is intentionally not recoverable from the fingerprint;
- `GENERATED_IDENTIFIER`, because database-generated semantics are not yet governed by this stage.

For `ATTRIBUTE_ADDED`, a required non-null attribute with no reconstructible safe default is rejected because Sprint 16 has no authority to inspect or backfill existing rows.

A required attribute inside `ENTITY_CREATED` is allowed because the table is being newly represented and contains no pre-existing rows at creation time.

## Index rendering

For a new entity, Sprint 16 renders the exact published primary index and published unique indexes.

For `UNIQUE_INDEX_ADDED`, Sprint 16 renders only the exact target unique index whose canonical fingerprint matches the approved planning step.

Logical index attributes are resolved to exact physical column identifiers.

## Reference rendering

Sprint 16 renders an exact target foreign-key reference only when:

- source and target entities resolve in the target physical mapping;
- source and target logical attributes resolve to physical columns;
- the exact reference canonical fingerprint matches the approved planning step;
- any target entity that is also created by the same generation artifact appears earlier in generation order.

If a referenced entity is generated later, Sprint 16 fails closed with `LARAVEL_MIGRATION_GENERATION_REFERENCE_ORDER_UNRESOLVED`.

Sprint 16 does not invent a new dependency graph or reorder governed changes.

## Forward-only rollback boundary

Sprint 15 deliberately classified generated migration definitions as `FORWARD_ONLY`.

Sprint 16 preserves that classification in generated Laravel source.

Generated `down()` methods do not synthesize destructive rollback operations. They throw a bounded `LogicException` stating that rollback is not authorized.

Sprint 16 does not generate:

- `Schema::drop`;
- `Schema::dropIfExists`;
- `dropColumn`;
- `dropForeign`;
- `dropUnique`;
- destructive reverse schema operations.

## Security and side-effect boundary

The generator contains no:

- PDO connection;
- database connection;
- database metadata query;
- raw SQL execution;
- `DB::statement`;
- `DB::unprepared`;
- network call;
- filesystem write;
- directory creation;
- shell/process execution;
- environment-secret read;
- Artisan migration execution;
- deployment action;
- cPanel action;
- updater action.

The generated source itself contains no raw SQL, migration command, credential, DSN, or destructive rollback implementation.

## Regression coverage

`tests/migration.php` preserves the previous Migration Foundation and Sprint 15 bridge regression and extends it with Sprint 16 assertions for:

- deterministic repeated generation;
- immutable generation/file artifacts;
- target mapping fingerprint preservation;
- target definition fingerprint creation;
- unique lexically ordered generated paths;
- source-change and governed migration traceability;
- generated source SHA-256 integrity;
- entity/table creation rendering;
- primary index rendering;
- nullable/default-null column rendering;
- unique index rendering;
- reference rendering;
- forward-only `down()` behavior;
- target physical-manifest mismatch denial;
- unrecoverable literal-default denial;
- unsafe required-attribute addition denial;
- exact after-fingerprint mismatch denial;
- absence of database, network, filesystem, process-execution, and migration-execution coupling.

The full root `composer test` remains the canonical regression path.

## Stable error boundary

Sprint 16 introduces bounded error codes including:

- `LARAVEL_MIGRATION_GENERATION_SOURCE_ARTIFACT_MISMATCH`;
- `LARAVEL_MIGRATION_GENERATION_SOURCE_METADATA_MISMATCH`;
- `LARAVEL_MIGRATION_GENERATION_TARGET_MANIFEST_MISMATCH`;
- `LARAVEL_MIGRATION_GENERATION_GOVERNED_BINDING_MISMATCH`;
- `LARAVEL_MIGRATION_GENERATION_CHANGE_KIND_NOT_ALLOWED`;
- `LARAVEL_MIGRATION_GENERATION_TARGET_ENTITY_MISSING`;
- `LARAVEL_MIGRATION_GENERATION_TARGET_COMPONENT_MISSING`;
- `LARAVEL_MIGRATION_GENERATION_TARGET_DEFINITION_MISMATCH`;
- `LARAVEL_MIGRATION_GENERATION_AFTER_FINGERPRINT_MISMATCH`;
- `LARAVEL_MIGRATION_GENERATION_DEFAULT_POLICY_UNSUPPORTED`;
- `LARAVEL_MIGRATION_GENERATION_REQUIRED_ATTRIBUTE_UNSAFE`;
- `LARAVEL_MIGRATION_GENERATION_SCALAR_MAPPING_UNSUPPORTED`;
- `LARAVEL_MIGRATION_GENERATION_REFERENCE_ORDER_UNRESOLVED`;
- `LARAVEL_MIGRATION_GENERATION_ARTIFACT_INVALID`.

Messages remain bounded and do not carry credentials, database records, tenant data, or arbitrary payload content.

## Exact implementation envelope

Sprint 16 changes exactly:

1. `src/SchemaPlanning/Foundation.php`;
2. `src/SchemaPlanning/LaravelMigrationGeneration.php`;
3. `tests/migration.php`;
4. `docs/LARAVEL_MIGRATION_GENERATION_FOUNDATION.md`.

No application migration directory, dependency manifest, workflow, runtime application source, deployment file, updater file, or release file is part of Sprint 16.

## Explicit non-scope

Sprint 16 does not authorize or implement:

- migration-file materialization into the Laravel application tree;
- `apps/web/database/` creation;
- `artisan migrate`;
- rollback execution;
- database connection or schema introspection;
- schema mutation;
- data backfill;
- migration journal persistence;
- real migration lock;
- durable application persistence;
- final POS/business schema;
- cPanel/live-target action;
- deployment;
- Release/GitHub Release;
- updater activation;
- Production/customer data;
- Production-readiness promotion.

Production readiness remains **NO-GO**.

## Next candidate gate

After successful Sprint 16 publication, the narrowest safe successor is **Governed Migration Materialization & Validation Foundation**.

That future stage may materialize exact reviewed generation artifacts into Local/Test/CI application migration files and validate them against the pinned Laravel toolchain, but it should still remain non-executing until a later migration-execution gate is independently authorized.

Attribution: **Lab | zefry**

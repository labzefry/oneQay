# Sprint 16 Entry Gate — Laravel Migration Generation Foundation

## Identity

- Product: `oneQay`
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Exact entry-gate base: `a06ceee3886a5648fc31baec65e3fdbd8a022c8d`
- Exact entry-gate base tree: `fa207d1aa516c7e1f7ba1842da5fdab09a0f17c7`
- Sprint 14 Migration Planning Artifact Foundation: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Sprint 15 Governed Migration Artifact Bridge Foundation: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Web framework target observed on the exact base: Laravel `12.64.0`
- Production readiness: **NO-GO**

GitHub is the Single Source of Truth. Fresh verification is required before every lifecycle mutation.

Attribution: **Lab | zefry**

## Product Owner continuation

The Product Owner directed the project to continue to the next engineering stage after Sprint 15.

This entry gate records that continuation as authority for the bounded **Sprint 16 — Laravel Migration Generation Foundation** scope defined below, including Local/Test/CI source implementation and the ordinary Ready/Merge lifecycle after all exact-head required checks and the repository-native Product Owner merge-authority gate succeed.

Independent review is not an additional requirement under the current Product Owner continuation model.

This authority does **not** grant migration execution, database connection, database metadata inspection, schema mutation, data backfill, cPanel/live-target action, deployment, Release/GitHub Release, updater activation, Production/customer data, or Production readiness.

## Why Sprint 16 is the next bounded capability

Sprint 14 produced a deterministic non-executable `MigrationPlanningArtifact`.

Sprint 15 connected that planning artifact to the existing framework-agnostic Migration Foundation as a deterministic `GovernedMigrationManifestArtifact` with stable migration identifiers, checksums, conservative dependencies, and source-change bindings.

The repository web skeleton is pinned to Laravel `12.64.0`, but no application `database/` migration directory currently exists on the exact Sprint 15 base.

The narrowest safe successor is therefore a generator that can produce **deterministic in-memory Laravel migration file artifacts** from exact governed schema inputs while deliberately performing no filesystem write and no migration execution.

Sprint 16 must not guess physical schema details that Sprint 15 intentionally does not carry.

## Controlled outcome

Provide a deterministic, immutable generation boundary:

`MigrationPlanningArtifact`

+ `GovernedMigrationManifestArtifact`

+ target `DataDefinitionManifest`

+ target `PhysicalMappingManifest`

+ bounded generation correlation ID

→ exact cross-input verification

→ Laravel migration file artifacts in memory

→ deterministic file names, source text, source checksums, and source-change traceability.

The generated output is build evidence only. It is not filesystem state, database state, execution authority, deployment authority, Release authority, or Production authority.

## Authorized implementation paths

Sprint 16 implementation is limited to exactly these paths:

1. `src/SchemaPlanning/Foundation.php`
2. `src/SchemaPlanning/LaravelMigrationGeneration.php` — new
3. `tests/migration.php`
4. `docs/LARAVEL_MIGRATION_GENERATION_FOUNDATION.md` — new

No other path is authorized by this entry gate.

Specifically excluded:

- `src/Migration/Foundation.php`;
- `apps/web/database/**`;
- `apps/web/composer.json`;
- dependency lockfiles;
- workflow files;
- application runtime providers/routes/controllers;
- deployment files;
- updater files;
- release files.

## Exact input-binding requirements

`DeterministicLaravelMigrationGenerator` must accept exactly:

1. one Sprint 14 `MigrationPlanningArtifact`;
2. one Sprint 15 `GovernedMigrationManifestArtifact`;
3. one target `DataDefinitionManifest`;
4. one target `PhysicalMappingManifest`;
5. one bounded generation correlation ID.

Before generating any source it must fail closed unless all of the following hold:

- deterministic SHA-256 of the supplied Sprint 14 planning artifact equals `GovernedMigrationManifestArtifact::sourcePlanningArtifactFingerprint`;
- Sprint 14 planning correlation equals the Sprint 15 source planning correlation;
- Sprint 14 review correlation equals the Sprint 15 source review correlation;
- Sprint 14 baseline and target fingerprints equal the corresponding Sprint 15 fingerprints;
- canonical fingerprint of the supplied target physical mapping equals the Sprint 15 target fingerprint;
- Sprint 14 step count equals Sprint 15 binding/manifest count;
- every planning source-change identifier is present exactly once in Sprint 15 bindings;
- binding order matches governed migration-definition order;
- every target data-definition entity/attribute required by a planning step exists;
- every target physical entity/component required by a planning step exists;
- logical scalar type in the target data definition matches the target physical mapping logical type;
- every planning kind is still in the Sprint 14/Sprint 15 additive allowlist.

No mismatch may be silently normalized or guessed.

## Framework target

Sprint 16 targets the exact current web skeleton framework family:

- framework: `Laravel`;
- exact observed application dependency: `laravel/framework 12.64.0`;
- generated migration style: anonymous migration class;
- schema API: `Illuminate\Support\Facades\Schema`;
- blueprint API: `Illuminate\Database\Schema\Blueprint`;
- migration base: `Illuminate\Database\Migrations\Migration`.

The root generator must not require the Laravel package at runtime. It generates bounded PHP source text only.

A future Laravel dependency upgrade requires separate compatibility verification before changing the generation target.

## Generated file artifact contract

Each governed migration definition produces exactly one immutable `LaravelMigrationFileArtifact` containing bounded metadata such as:

- source change identifier;
- governed migration identifier;
- deterministic relative migration path;
- generation correlation ID;
- generated source SHA-256;
- generated PHP source text.

The generator itself must never call filesystem write APIs.

The deterministic relative path must remain under:

`database/migrations/`

but is representation metadata only.

No Sprint 16 code writes into `apps/web/database/migrations/`.

## Deterministic migration file naming

File names must not use current time, wall-clock time, random values, UUID generation, environment state, filesystem state, network state, or database state.

The generator must derive lexical ordering from the already-governed migration order.

Directional format:

`database/migrations/0000_00_00_<six-digit-ordinal>_<kind>_<source-change-prefix>.php`

Requirements:

- same exact inputs produce byte-identical paths;
- paths are unique within the generated artifact;
- paths are lexically ordered in governed migration order;
- the `0000_00_00` segment is explicitly non-temporal;
- no path may contain raw credentials, tenant records, customer data, endpoints, or arbitrary payload material.

## Allowed change kinds

Only these existing additive kinds may generate Laravel migration source:

- `ENTITY_CREATED`;
- `ATTRIBUTE_ADDED`;
- `UNIQUE_INDEX_ADDED`;
- `REFERENCE_ADDED`.

The generator must independently reject every other kind.

## Exact target-state reconstruction

Sprint 16 must reconstruct generation details only from the supplied exact target manifests.

For a planning step, the generator must:

- resolve the logical entity in both target data definition and target physical mapping;
- resolve the component named by the planning step when one is required;
- derive the exact canonical target component state;
- verify its SHA-256 equals the planning step `afterFingerprint`;
- only then render framework source.

For `ENTITY_CREATED`, the verified after-fingerprint covers the complete target entity mapping. The generated migration may therefore render the full entity mapping only after that complete entity fingerprint is reproduced exactly.

## Scalar rendering allowlist

The generator may render only the already-published physical scalar mappings:

- `VARCHAR` → Laravel string column with exact length;
- `BIGINT_SIGNED` → signed big integer column;
- `DECIMAL` → decimal with exact precision/scale;
- `TINYINT_BOOLEAN` → Laravel boolean-compatible column;
- `CHAR_UUID` → fixed `CHAR(36)` semantics;
- `DATE` → date column;
- `DATETIME` → datetime column;
- `JSON_DOCUMENT` → JSON column.

Character mappings must preserve the published `UTF8MB4` charset policy and supported collation semantics.

Any unrecognized physical scalar mapping fails closed.

## Nullability and default-value boundary

Target `DataDefinitionManifest` is required because the physical mapping intentionally does not carry nullability/default semantics.

Sprint 16 may render only reconstructible safe default policies:

- `NONE`;
- `NULL_VALUE` when the attribute is nullable.

Sprint 16 must fail closed for:

- `LITERAL_FINGERPRINT`, because the original literal value is intentionally not recoverable from its fingerprint;
- `GENERATED_IDENTIFIER`, because database-generation semantics are not yet separately governed.

For `ATTRIBUTE_ADDED` against an existing entity, adding a required non-null attribute without a reconstructible safe default must fail closed because Sprint 16 has no database-row preflight authority.

This restriction does not prevent required attributes from being rendered during `ENTITY_CREATED`, because the new table has no pre-existing rows at creation time.

## Index rendering

The generator may render:

- the already-published primary index for a newly created entity;
- already-published unique indexes on a newly created entity;
- an exact `UNIQUE_INDEX_ADDED` component.

Logical attribute identifiers in index definitions must be resolved to exact physical column identifiers from the target physical entity mapping.

Index names must come only from validated published physical identifiers.

## Reference rendering

The generator may render an exact published foreign-key reference only after:

- source entity exists in the target mapping;
- target entity exists in the target mapping;
- every logical source/target attribute resolves to a physical column;
- the reference component after-fingerprint is reproduced exactly;
- target ordering is not known to require a future-created table.

If a newly created entity references another entity that is also being created by the same Sprint 16 generation artifact, the referenced entity must appear earlier in governed generation order. Otherwise generation fails closed with an explicit unresolved dependency error.

Sprint 16 does not invent or rewrite the Sprint 15 dependency graph.

## Forward-only rollback representation

Sprint 15 classifies every generated migration definition as `FORWARD_ONLY`.

Sprint 16 must preserve that meaning in generated Laravel source.

Generated `down()` methods must not synthesize destructive rollback behavior such as dropping tables, columns, indexes, or constraints.

Instead the generated `down()` boundary must fail explicitly and safely to indicate that rollback generation is not yet authorized.

No `Schema::drop`, `dropColumn`, `dropForeign`, `dropUnique`, or equivalent destructive reverse operation is authorized in Sprint 16.

## Generated-source security rules

Generated PHP source must contain no:

- raw SQL;
- `DB::statement`;
- `DB::unprepared`;
- PDO/database connection;
- database credential;
- DSN;
- network request;
- filesystem write;
- shell/process execution;
- environment secret read;
- live-target identifier outside exact published physical mapping material;
- migration execution command.

The source generator itself must contain no filesystem write, network, database, deployment, or process-execution side effect.

## In-memory only boundary

Sprint 16 does not create real migration files in the Laravel application tree.

It does not create `apps/web/database/`.

It does not call:

- `file_put_contents`;
- `fopen` for write;
- `mkdir`;
- `rename`;
- `copy`;
- Laravel filesystem APIs;
- Composer scripts;
- Artisan migration commands.

A later separately authorized materialization step may decide whether and how reviewed generated artifacts become committed application migration files.

## Regression requirements

`tests/migration.php` must preserve all existing migration and Sprint 15 bridge tests and add deterministic Sprint 16 coverage for at least:

1. exact planning-to-governed fingerprint binding;
2. exact target physical-manifest fingerprint binding;
3. target data-definition/physical-mapping consistency;
4. byte-identical repeated generation;
5. immutable generation artifact and file artifacts;
6. deterministic lexical file ordering;
7. unique generated paths;
8. source-change-to-governed-migration-to-file traceability;
9. all four allowed additive change kinds;
10. scalar rendering allowlist;
11. nullability rendering;
12. nullable null-default rendering;
13. rejection of literal-fingerprint defaults;
14. rejection of generated-identifier defaults;
15. rejection of required attribute addition without safe default;
16. primary-index rendering on entity creation;
17. unique-index rendering;
18. reference rendering;
19. unresolved created-reference ordering denial;
20. exact after-fingerprint mismatch denial;
21. target-manifest fingerprint mismatch denial;
22. generated `down()` is explicitly forward-only and non-destructive;
23. no raw SQL/database/network/filesystem/process side effect;
24. no destructive rollback generation;
25. existing full repository `composer test` remains green.

## Non-goals

Sprint 16 does not implement:

- migration-file materialization into the application tree;
- automatic migration discovery;
- `artisan migrate`;
- `artisan migrate:rollback`;
- database connection;
- database preflight;
- current database-state inspection;
- migration journal persistence;
- real migration lock;
- data backfill;
- destructive migration generation;
- rollback generation;
- live schema mutation;
- durable application persistence;
- final business schema;
- cPanel database actions;
- deployment;
- Release/GitHub Release;
- updater activation;
- Production/customer data;
- Production-readiness promotion.

## Expected implementation envelope

If this entry gate is published unchanged, the subsequent Sprint 16 source implementation PR must change exactly:

1. `src/SchemaPlanning/Foundation.php`;
2. `src/SchemaPlanning/LaravelMigrationGeneration.php`;
3. `tests/migration.php`;
4. `docs/LARAVEL_MIGRATION_GENERATION_FOUNDATION.md`.

No fifth path is authorized.

## Completion condition

Sprint 16 is complete only when:

- this entry gate is canonical on `main`;
- the implementation uses exactly the authorized four-file envelope;
- exact-head required CI is successful;
- exact-head Product Owner merge authority is successful;
- the implementation PR is merged;
- canonical post-merge `main` is freshly verified;
- generated artifacts remain in-memory only;
- migration execution remains unauthorized;
- Production readiness remains **NO-GO**.

## Likely successor

After Sprint 16 publication, the next candidate must still **not** jump directly to Production database mutation.

The likely safe sequence is:

1. **Sprint 17 — Governed Migration Materialization & Validation Foundation**: controlled creation/validation of reviewed generated migration files in Local/Test/CI, still without database execution; then
2. **Sprint 18 — Governed Migration Execution Foundation**: Local/Test/CI database preflight, lock, journal, execute, verify, and failure/rollback policy; then
3. **Durable Application Persistence Foundation**.

Each successor requires a separate Product Owner gate.

Attribution: **Lab | zefry**

# Sprint 15 Entry Gate — Governed Migration Artifact Bridge Foundation

## Identity

- Product: `oneQay`
- Developer and Product Engineering Entity: **Lab | zefry**
- Repository: `labzefry/oneQay`
- Decision date: 2026-08-17
- Exact entry-gate base: `9f8443356129934298b16400ffd8626398483093`
- Phase 0: **COMPLETE / EXIT APPROVED / PUBLISHED**
- M7 Technical Preview: **COMPLETE / ACCEPTED**
- Sprint 14 Migration Planning Artifact Foundation: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Production readiness: **NO-GO**

GitHub is the Single Source of Truth. Fresh verification is required before every lifecycle mutation.

Attribution: **Lab | zefry**

## Product Owner continuation

The Product Owner directed the project to continue to the next engineering stage after Sprint 14.

This entry gate records that continuation as authority for the bounded **Sprint 15 — Governed Migration Artifact Bridge Foundation** scope defined below, including Local/Test/CI source implementation and the ordinary Ready/Merge lifecycle after all exact-head required checks and the repository-native Product Owner merge-authority gate succeed.

Independent review is not an additional requirement under the current Product Owner continuation model.

This authority does **not** grant SQL generation, framework migration-file generation, migration execution, database/schema mutation, cPanel/live-target action, deployment, Release/GitHub Release, Production/customer data, updater installation, or Production readiness.

## Why Sprint 15 is the next bounded capability

Sprint 14 published a deterministic, immutable, non-executable `MigrationPlanningArtifact` from an exact approved `PhysicalSchemaPlan` and matching Sprint 13 review envelope.

The repository also already contains a separate framework-agnostic Migration Foundation with:

- `MigrationIdentifier`;
- `MigrationChecksum`;
- `MigrationDefinition`;
- `MigrationManifest`;
- dependency-order validation;
- dry-run `MigrationPlanner`;
- migration lock abstraction;
- synthetic dry-run executor and execution service.

These two existing boundaries are intentionally not yet connected.

The narrowest safe successor is therefore a deterministic bridge that converts a valid Sprint 14 planning artifact into a **governed, non-executable migration manifest artifact** while preserving source traceability and existing migration safety semantics.

The bridge must stop before SQL, Laravel migration generation, database access, or migration execution.

## Controlled outcome

Provide a deterministic, immutable bridge:

`MigrationPlanningArtifact`

→ exact source fingerprint verification

→ governed migration definitions

→ governed `MigrationManifest`

→ traceable bridge artifact

The bridge artifact is planning/generation evidence only. It is not execution authority.

## Authorized implementation paths

Sprint 15 implementation is limited to exactly these paths:

1. `src/SchemaPlanning/Foundation.php`
2. `src/SchemaPlanning/MigrationArtifactBridge.php` — new
3. `tests/schema-planning.php`
4. `docs/GOVERNED_MIGRATION_ARTIFACT_BRIDGE_FOUNDATION.md` — new

No other path is authorized by this entry gate.

The existing `src/Migration/Foundation.php`, application source, dependency manifests, workflow files, database files, Laravel migration directories, deployment files, updater files, and release files are explicitly excluded.

## Source binding requirements

The bridge must accept exactly one `MigrationPlanningArtifact` plus one bounded bridge correlation ID.

Before producing output it must:

- deterministically serialize the supplied Sprint 14 artifact;
- derive a SHA-256 fingerprint from that exact serialized artifact;
- preserve the source planning correlation ID;
- preserve the source review correlation ID;
- preserve baseline and target manifest fingerprints;
- preserve every stable Sprint 14 source change identifier;
- reject empty, malformed, unsupported, duplicated, or non-additive step material;
- independently verify that every step kind remains one of the Sprint 14 additive allowlist kinds.

A generated bridge artifact must therefore remain traceable to the exact Sprint 14 planning input.

## Allowed change kinds

Only the Sprint 14 additive kinds may be bridged:

- `ENTITY_CREATED`;
- `ATTRIBUTE_ADDED`;
- `UNIQUE_INDEX_ADDED`;
- `REFERENCE_ADDED`.

No destructive, removal, mutation, tenant-scope, tenant-key, vendor, physical-mapping, scalar-mapping, primary-index, or unsupported kind may enter the governed migration manifest.

Fail closed on any unsupported kind even if an upstream object was manually constructed or tampered with.

## Deterministic migration identity

The bridge must create stable migration identifiers without using wall-clock time, random values, filesystem state, network state, database state, or environment state.

Because the existing migration foundation uses ordered migration identifiers, Sprint 15 may use a deterministic non-temporal sequence namespace derived from the stable planning-step order and source change identity.

Required properties:

- same exact bridge input produces the same migration identifiers;
- identifiers remain unique within the manifest;
- identifiers remain lexically ordered according to the existing `MigrationManifest` contract;
- identifier material must not contain raw database/table names, credentials, endpoints, tenant records, or arbitrary payloads;
- source change identity remains separately preserved in the bridge artifact.

The numeric date/time-shaped portion of an existing `MigrationIdentifier` must not be interpreted by Sprint 15 as execution time, deployment time, or Release time.

## Conservative dependency strategy

Sprint 14 carries stable ordered planning steps but does not provide a full executable dependency graph.

Therefore Sprint 15 must not invent an unsafe dependency graph.

For the bounded foundation, generated definitions use a conservative deterministic serial chain:

- first generated definition has no dependency;
- every later definition depends on the immediately preceding generated definition.

This preserves deterministic order and prevents later steps from being treated as independently executable by the existing planner.

A future separately authorized framework migration generation capability may introduce richer dependency semantics only after exact source schema material and framework rules are separately governed.

## Safety and rollback classification

Every Sprint 15 generated `MigrationDefinition` must be classified conservatively as:

- safety: `CAUTION`;
- rollback: `FORWARD_ONLY`.

Sprint 15 must not claim `SAFE` or `REVERSIBLE`, because it does not generate or verify executable schema operations or rollback behavior.

This classification is intentionally restrictive and does not imply execution readiness.

## Checksum requirements

Each generated migration definition must have deterministic equal declared/artifact checksums derived only from bounded safe canonical bridge material.

Checksum descriptor inputs may include:

- exact Sprint 14 artifact fingerprint;
- stable source change identifier;
- additive change kind;
- safe logical entity identifier;
- optional safe logical component identifier;
- after fingerprint;
- deterministic ordinal.

The generated artifact must never expose the raw checksum descriptor.

The descriptor must not contain SQL, raw manifests, credentials, endpoints, private filesystem paths, tenant records, or Production/customer data.

## Governed bridge artifact

The Sprint 15 bridge result should be an immutable/read-only wrapper containing bounded traceability metadata plus the existing `MigrationManifest`.

Directional fields:

- source Sprint 14 artifact fingerprint;
- source planning correlation ID;
- source review correlation ID;
- bridge correlation ID;
- reviewer reference;
- baseline manifest fingerprint;
- target manifest fingerprint;
- stable source-change-to-migration-identifier bindings;
- governed migration manifest.

The wrapper must remain deterministic and JSON serializable.

## Security and tenant isolation

Sprint 15 remains deny-by-default.

Required protections:

- no tenant records are read;
- no database connection is opened;
- no schema metadata is introspected from a live database;
- no secret/config credential is accepted;
- no tenant-scope or tenant-key change is bridgeable;
- no raw physical schema definition is emitted;
- no raw table/column definition is reconstructed;
- no cross-tenant data exists in bridge output;
- stable error codes and bounded error messages only;
- no arbitrary exception or sensitive payload leakage.

## Explicit database and execution boundary

Sprint 15 must not:

- generate SQL, DDL, or DML;
- create Laravel/framework migration files;
- write files to a migration directory at runtime;
- call `artisan migrate`;
- open PDO or framework database connections;
- inspect database metadata;
- create or alter tables;
- execute migration plans;
- acquire a real database migration lock;
- write a migration journal;
- perform schema rollback;
- backfill data;
- access cPanel or a live target;
- deploy an application;
- activate updater runtime;
- create Release/GitHub Release authority;
- promote Production readiness.

The existing synthetic migration executor remains historical/platform-foundation test capability and is not invoked by the Sprint 15 bridge.

## Required test coverage

Exact-head tests must demonstrate at minimum:

1. a valid Sprint 14 entity-created planning artifact bridges successfully;
2. attribute-added, unique-index-added, and reference-added steps bridge successfully;
3. equivalent input plus exact bridge correlation produces byte-equivalent bridge JSON;
4. bridge artifact and binding records are read-only;
5. source Sprint 14 artifact fingerprint is deterministic and preserved;
6. source planning/review correlation IDs are preserved;
7. baseline/target fingerprints are preserved;
8. stable source change IDs are preserved;
9. generated migration IDs are deterministic, unique, and ordered;
10. generated definitions form the conservative serial dependency chain;
11. every generated definition is `CAUTION`;
12. every generated definition is `FORWARD_ONLY`;
13. declared and artifact checksums match;
14. malformed or unsupported change kind fails closed;
15. tenant-scope and tenant-key spoof/bypass cannot be bridged;
16. duplicate source change identifiers cannot be accepted;
17. output contains no SQL, credential, endpoint, raw path, tenant record, or raw physical schema material;
18. source contains no PDO/database access, network dependency, or filesystem side effect;
19. existing Sprint 12, Sprint 13, Sprint 14 schema-planning regressions remain green;
20. existing migration foundation tests remain green;
21. full repository `composer test` remains green through CI.

## Acceptance criteria

Sprint 15 is technically acceptable only when:

- exactly the four authorized implementation paths change;
- no existing Migration Foundation contract is weakened;
- valid Sprint 14 planning artifacts convert deterministically into an existing `MigrationManifest` wrapped by bounded traceability metadata;
- unsupported or tampered material fails closed;
- generated identity, dependency, checksum, safety, and rollback semantics are deterministic and conservative;
- no SQL, migration file, database connection, database execution, schema mutation, network call, filesystem side effect, deployment, Release, or Production behavior exists;
- all exact-head required CI and Product Owner merge authority succeed.

## Lifecycle after Sprint 15

Successful Sprint 15 publication authorizes no automatic successor implementation.

The likely next separately gated engineering decision is **Framework Migration Generation Foundation**, using the governed Sprint 15 artifact to generate deterministic framework-specific migration material without executing it.

Only after that generation boundary is proven may the project consider a separately authorized migration execution foundation in Local/Test/CI, followed later by durable application persistence.

## Preserved lifecycle boundaries

- Phase 0: **COMPLETE / EXIT APPROVED / PUBLISHED**
- M7 Technical Preview: **COMPLETE / ACCEPTED**
- Sprint 14: **COMPLETE / IMPLEMENTED / PUBLISHED**
- Sprint 15: **AUTHORIZED by this Product Owner continuation, bounded to this entry gate**
- Customer Service & Support source implementation: **NOT AUTHORIZED by this Sprint**
- Localization source implementation: **NOT AUTHORIZED by this Sprint**
- Framework migration generation: **NOT AUTHORIZED by this Sprint**
- Migration execution/live schema mutation: **NOT AUTHORIZED**
- Durable application persistence: **NOT AUTHORIZED by this Sprint**
- Deployment/live target mutation: **NOT AUTHORIZED**
- Release/GitHub Release: **NOT AUTHORIZED**
- Production: **NOT AUTHORIZED**
- Production readiness: **NO-GO**

Attribution: **Lab | zefry**

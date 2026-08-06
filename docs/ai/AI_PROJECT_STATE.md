# AI Project State

## Current engineering state

- Current Sprint: Sprint 11 — Physical Schema Mapping Capability and Vendor Compatibility Policy Foundation
- Current Phase: Phase 1 — Platform Foundation
- Current Milestone: Physical Mapping and Vendor Compatibility Policy Foundation
- Current Module: Physical Mapping and Vendor Compatibility Policy
- Exact Base: `302c9957bcda55fe8265fc0a0449003d59f23620`
- Branch: `agent/sprint11-physical-schema-mapping-vendor-compatibility-policy`
- Authentication Foundation: Published.
- Tenant Context Foundation: Published.
- Authorization Boundary Foundation: Published.
- Configuration and Secret Boundary Foundation: Published.
- Runtime Foundation: Published.
- Persistence Capability and Database Connection Boundary: Published.
- Migration Governance and Safety Foundation: Published.
- Generic Data Definition and Tenant Isolation Policy Foundation: Published.
- Physical Schema Mapping and Vendor Compatibility Policy Foundation: Implemented on branch.
- Production table: Not Started.
- Final tenant data model: Not Started.
- Final business schema: Not Started.
- Production migration: Not Performed.
- POS: Not Started.
- Deployment: None.
- Release: None.

## Implemented capability

- canonical Physical Identifier;
- reserved physical namespace rejection;
- bounded MariaDB vendor identifier;
- `UTF8MB4` charset policy;
- Unicode and binary collation compatibility classifications;
- portable logical-to-physical scalar mapping vocabulary;
- deterministic string length and decimal precision/scale validation;
- UUID fixed mapping policy;
- primary-index mapping contract;
- unique-index mapping contract;
- deterministic index-key byte budget;
- foreign-key `COMPATIBLE` and `INCOMPATIBLE` classification;
- eligible target-index policy;
- tenant-key physical mapping requirements;
- global-to-tenant reference denial;
- immutable Physical Mapping Manifest;
- deterministic Vendor Compatibility Validator;
- safe compatibility report;
- stable physical-mapping error codes;
- deterministic no-network and no-production-database tests.

## Historical residual risk

Legacy regressions were not re-run before Sprint 09 merge. Sprint 10 and Sprint 11 executed those regressions after publication against verified exact-base blobs, but this does not rewrite the historical pre-merge lifecycle fact.

## Capability status

Verified foundation capability: PHP `>=8.2` contract, local PHP 8.4.16 syntax and regression execution, framework-agnostic physical mapping representation, deterministic synthetic MariaDB compatibility policy, and no executable or production adapter.

Unknown: live MariaDB patch compatibility, production SQL mode, storage-engine settings, actual index-prefix limits, live collation availability, physical foreign-key enforcement, online schema change support, final tenant model, final business schema, production migration grants, advisory lock policy, backup and restore objectives, deployment method, rollback execution authority, dan production connection limits.

## Deferred capability

Production table, executable SQL, final tenant data model, final business schema, migration artifact generation, production migration adapter, repository bisnis, transaction persistence, idempotency persistence, audit persistence, persistent session, cache, queue, scheduler, mail, storage, deployment, rollback execution, POS, dan semua business modules.

Attribution: Lab | zefry

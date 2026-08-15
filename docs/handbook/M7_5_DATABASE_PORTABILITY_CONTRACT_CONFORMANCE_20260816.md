# M7.5 Database Portability Contract Conformance Evidence — 2026-08-16

Attribution: **Lab | zefry**

## Purpose

This handbook record captures the bounded evidence used to qualify `ENGINE:PORTABILITY_CONTRACT` for the current M7.5 Technical Preview scope.

It is an evidence and conformance record, not an authorization for database implementation, cross-engine runtime work, DBME, deployment, release, or Production.

## Governing architecture

DEC-005R requires:

- database-engine-neutral Domain and Application;
- no database-vendor dependency in business rules;
- engine-specific behavior confined to Infrastructure;
- canonical logical semantics independent of physical engine representation;
- fail-closed handling of unsupported or unsafe mappings;
- qualification by evidence rather than driver connectivity claims.

The current Technical Preview relational runtime evidence remains MariaDB 11.4.8. Recognition of MySQL and PostgreSQL as architectural profile directions does not qualify those profiles.

## Executable contract

`src/Portability/Foundation.php` introduces a framework-agnostic `DatabasePortabilityContract` with two explicit classifications:

- `LOGICAL_BUSINESS`;
- `INFRASTRUCTURE`.

For `LOGICAL_BUSINESS`, the evaluator rejects:

- concrete `pdo_mysql` / `pdo_pgsql` style driver coupling;
- database DSN coupling;
- vendor-specific adapter, connector, connection, or repository coupling;
- direct conditional branching against relational vendor identity;
- raw SQL statement patterns.

Infrastructure-classified source is permitted to contain engine-specific implementation details because DEC-005R places physical/runtime adapter behavior at that boundary.

Unknown profile directions fail closed. Known direction identifiers are limited to MariaDB, MySQL, and PostgreSQL architecture directions and are not represented as runtime-qualified merely by construction.

## Reserved-name distinction

The initial conformance rule deliberately failed CI because `src/DataDefinition/ValueObjects.php` contains `MYSQL` in a reserved-prefix denylist.

That occurrence is not a dependency: the logical contract rejects identifiers using a vendor-reserved namespace. Treating this protective denylist as vendor coupling would invert the intended architecture rule.

The evaluator was therefore corrected to detect concrete vendor dependencies and vendor-identity branching rather than every lexical occurrence of a vendor name. Negative tests were strengthened to prove that actual `pdo_mysql` leakage and conditional vendor branching still fail closed.

## Regression coverage

`tests/database-portability-contract.php` scans canonical repository roots rather than only synthetic fixtures.

Logical/business roots:

- `apps/web/app/Domain`;
- `apps/web/app/Application`;
- `src/Auth`;
- `src/Authorization`;
- `src/Tenant`;
- `src/DataDefinition`.

Bounded Infrastructure roots:

- `src/Persistence`;
- `src/PhysicalMapping`;
- `apps/web/app/Infrastructure/Persistence`.

Synthetic negative cases verify:

- unknown profile direction rejection;
- concrete vendor dependency rejection in logical/business code;
- vendor-identity branching rejection;
- raw SQL rejection;
- missing logical/business evidence rejection;
- safe acceptance of vendor-specific coupling when classified as Infrastructure;
- report serialization does not include source or credential-shaped content.

The test is included in root `composer test`, so the existing PHP Foundation Regression remains the primary enforcement gate. M7.1 Application Regression also runs the root foundation regression before application-specific work, providing a second integration signal without introducing another workflow.

## Exact-head qualification result

Implementation qualification head:

`0534587661f34f1bb5a16ab2b5bceaa3726ebdc5`

Results:

- Governance Required Checks: **SUCCESS**;
- PHP Foundation Regression: **SUCCESS**;
- M7.1 Application Regression: **SUCCESS**.

The evidence reconciliation commit must itself pass the same applicable checks before the Draft PR can be considered publication-ready.

## Qualification conclusion

For the bounded current codebase and selected Stage-1 relational profile, `ENGINE:PORTABILITY_CONTRACT` is **VERIFIED candidate** because executable CI now proves the required logical/business vendor-neutrality boundary while keeping existing engine-specific code isolated to Infrastructure.

This is intentionally narrower than cross-engine runtime qualification.

## What remains unproven

The following are not claimed:

- MySQL runtime qualification;
- PostgreSQL runtime qualification;
- cross-engine behavioral equivalence execution;
- DBME implementation or data mobility;
- physical schema portability execution;
- restore verification;
- rollback verification;
- full tenant-isolation qualification;
- Production portability or readiness.

## Evaluator impact

Proposed after publication:

- `ENGINE:PORTABILITY_CONTRACT`: **VERIFIED**;
- verified controls: **21**;
- blockers: **8**;
- outcome: **BLOCKED**;
- `lifecycle_authority_created=false`.

Canonical `main` remains 20 VERIFIED / 9 BLOCKED until the governed Draft PR is separately authorized and merged.

# M7.5 Database Portability Contract State — 2026-08-16

Attribution: **Lab | zefry**

## Scope

This state record reconciles the bounded M7.5 Database Portability Contract hardening authorized for Draft PR #119.

Repository baseline before this work:

`cd6126942cc8a2d13f259968fe43fcc0b0b7a3df`

Conformance implementation head that passed exact-head CI before evidence reconciliation:

`0534587661f34f1bb5a16ab2b5bceaa3726ebdc5`

No database, SQL, DDL, migration, physical schema, DBME, live credential, deployment, release, or Production action is part of this work.

## Conformance boundary

The formal executable contract is implemented in:

`src/Portability/Foundation.php`

The conformance regression is implemented in:

`tests/database-portability-contract.php`

The regression is part of the existing root `composer test` suite and therefore enforced by PHP Foundation Regression and by M7.1's preserved root-foundation regression step.

The bounded contract enforces:

- logical business code must not contain concrete relational-vendor driver, DSN, adapter, connector, connection, repository, or vendor-identity branching dependency;
- raw SQL statements are denied in logical business code;
- vendor-specific behavior may remain inside explicitly classified Infrastructure boundaries;
- unknown relational engine-profile directions fail closed;
- a missing logical-business evidence set fails closed;
- reports serialize classification/result metadata only, not source text or credential material.

A vendor name used only as a reserved namespace that logical code explicitly rejects is not treated as a database dependency. This distinction prevents a false positive while preserving the stronger rule against concrete vendor coupling.

## Canonical roots covered by regression

Logical/business roots inspected:

- `apps/web/app/Domain`;
- `apps/web/app/Application`;
- `src/Auth`;
- `src/Authorization`;
- `src/Tenant`;
- `src/DataDefinition`.

Bounded Infrastructure roots inspected:

- `src/Persistence`;
- `src/PhysicalMapping`;
- `apps/web/app/Infrastructure/Persistence`.

The existing MariaDB/MySQL-shaped coupling in Persistence and PhysicalMapping remains an Infrastructure concern and does not become business-code coupling.

## Exact-head CI evidence

At `0534587661f34f1bb5a16ab2b5bceaa3726ebdc5`:

- Governance Required Checks: **SUCCESS**;
- PHP Foundation Regression: **SUCCESS**;
- M7.1 Application Regression: **SUCCESS**.

The first implementation attempt correctly failed because the initial lexical rule treated the `MYSQL` reserved-prefix denylist in `src/DataDefinition/ValueObjects.php` as a dependency. The rule was corrected to detect concrete coupling rather than harmless denylist vocabulary, and the negative regression was strengthened with concrete `pdo_mysql` leakage and vendor-identity branching cases.

## Qualification interpretation

Within the currently authorized M7.5 scope, `ENGINE:PORTABILITY_CONTRACT` is promoted to **VERIFIED candidate** because:

1. DEC-005R already establishes the engine-neutral Domain/Application contract and Infrastructure ownership of vendor behavior;
2. the repository now contains an executable conformance policy rather than documentation-only intent;
3. canonical logical/business roots are scanned by CI;
4. concrete vendor leakage and raw SQL fail closed in synthetic negative tests;
5. bounded vendor-specific Infrastructure coupling remains explicitly classified rather than hidden;
6. the previously qualified MariaDB Stage-1 relational profile remains the runtime profile evidence; and
7. the contract does not claim that MySQL or PostgreSQL runtime profiles are qualified merely because their architectural directions are recognized.

## Explicit limitations

This verification does **not** mean:

- MySQL runtime profile is qualified;
- PostgreSQL runtime profile is qualified;
- cross-engine behavioral CI exists;
- DBME exists;
- zero-change cross-engine migration has been demonstrated;
- restore or rollback is verified;
- tenant isolation is fully verified;
- M7.6 or M7.7 is authorized;
- Production readiness is achieved.

Those remain separate evidence and authority gates.

## Proposed evaluator state

If this Draft PR is published after final exact-head checks:

- verified controls: **21**;
- blockers: **8**;
- outcome: **BLOCKED**;
- `lifecycle_authority_created=false`.

Canonical `main` remains **20 VERIFIED / 9 BLOCKED** until publication.

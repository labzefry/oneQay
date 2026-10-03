# Sprint267 — Governed Production Schema Bootstrap

Author by Lab | zefry

## Scope and explicit authority

Sprint267 implements **exact-source bootstrap planning** and an actual 27-migration rehearsal **only on disposable CI databases**. This is engineering evidence, not permission to initialize the real Production database, deploy Production, modify staging, select another target, reactivate Final Shift Close, provision permissions, dispatch a producer, or activate the updater.

Exact canonical predecessor: `c83d5131d7aaab19ddbd001223236aa424d2ea48`.
Exact application/runtime source: `505518f79e8a70f789b94f5074a040eae785aeb0`.
Production release identity: `production-505518f79e8a`.

A separately submitted read-only cPanel inventory established that the prospective isolated Production database `oneqay-production-01` was empty: zero tables, no migration ledger, and all 27 application migrations missing. That observation is **historical**. The plan never treats it as authorization or as a fresh guarantee about future database contents.

## Bounded six-path envelope

1. `ops/final-shift-close/PRODUCTION_SCHEMA_BOOTSTRAP_SOURCE_LOCK.json`: immutable 27-file migration names and exact original Git blob hashes from source `505518f...`.
2. `tools/production/cpanel/governed-production-schema-bootstrap-foundation.php`: source-integrity verification and fail-closed historical read-only inventory qualification.
3. `tools/production/cpanel/prepare-governed-production-schema-bootstrap-plan.php`: source-locked **plan-only** CLI producing a private, secret-free `PRODUCTION_SCHEMA_BOOTSTRAP_PLAN_PREPARED_NOT_AUTHORIZED` document. It makes **no database connection** and never runs migration code.
4. `tools/production/tests/sprint267-disposable-schema-bootstrap.php`: disposable test-only application migration execution. It requires `CI=true`, `GITHUB_ACTIONS=true`, `ONEQAY_SPRINT267_DISPOSABLE=true`, a verified `RUNNER_TEMP`, and the exact original source lock. It has **no Production execution option**.
5. `.github/workflows/sprint267-governed-production-schema-bootstrap-regression.yml`: exact envelope, blob, planning-negative and disposable **SQLite and MySQL** execution gates.
6. This handoff.

## Security invariants and execution boundary

- Each of the 27 migration PHP sources is checked against its exact original Git blob, in order, before any isolated CI database is opened. Extra, missing, modified, or symlinked migration files fail closed.
- The plan accepts only a read-only inventory with zero base tables, no migration registry, zero previously executed migrations, and the exact 27 missing migration names.
- Planning never connects to a database, changes the application, accepts production credentials, modifies the shared domain, or changes repository operational state.
- CI uses an independently created temporary SQLite file, plus a disposable MySQL 8.0 service database named `oneqay_s267_ci`. It checks the database is initially empty, executes exactly the locked Laravel migration set, verifies the migration ledger and required Final Shift Close table, verifies zero business rows, and checks a second migrate operation does not replay the migrations.
- The CI-only executor rejects any environment without explicit GitHub CI, disposable-fixture attestation, allowlisted driver, and fixed disposable MySQL database credentials. It accepts no arbitrary DSN, production host, production database name, or Production execution mode.
- No real Production bootstrap executable is introduced in Sprint267, deliberately avoiding an authority bypass. A future separately governed executor must require fresh direct database emptiness/read-before-write evidence, exact release/source lock, short-lived **Production schema provisioning authority**, durable audit evidence and safe partial-failure recovery before executing any migration. The existing Production deployment executor still forbids migrations.

## Expected isolated CI outcomes

`SPRINT267_EXACT_SIX_PATH_ENVELOPE=PASS`

`SPRINT267_27_HISTORICAL_MIGRATION_BLOBS=PASS`

`SPRINT267_READ_ONLY_PLAN_FAIL_CLOSED=PASS`

`SPRINT267_SQLITE_DISPOSABLE_27_MIGRATIONS=PASS`

`SPRINT267_MYSQL_DISPOSABLE_27_MIGRATIONS=PASS`

`PRODUCTION_DATABASE_EXECUTION=NOT_PERFORMED`

## Next handoff after merge

After all exact-head CI and repository-native Product Owner merge gates succeed and Sprint267 is squash-merged, prepare a **separately authorized, one-time Production schema provisioning successor**. It must use the same existing empty Production database, not the durable-staging database; it must not claim that historical migration #27 execution on staging is authorization to mutate the new Production database. Require fresh empty-schema verification immediately before any future mutation, verified rollback/backup strategy, an authority-bound operation ledger, and post-provision schema verification. Only then continue the already-published Sprint266 same-domain fixed-public target qualification and independently authorized cutover.

Production deployment/traffic: `NOT_AUTHORIZED`.

Technical Preview: `NOT_AUTHORIZED`.

Updater: `INACTIVE`.

Attribution: Lab | zefry

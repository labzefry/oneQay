# Database Schema Governance and Migration Safety Foundation

## Scope

Sprint 09 menyediakan foundation framework-agnostic untuk tata kelola manifest migrasi, validasi checksum, dependency ordering, safety classification, rollback classification, dry-run planning, lock boundary, synthetic execution, stable error codes, dan deterministic tests.

Foundation ini tidak membuat SQL produksi, schema bisnis, tabel produksi, tenant data model final, koneksi database produksi, migration terhadap hosting, deployment, atau release.

## Published base

Exact published base Sprint 09:

`5e620f7e1975450d7538e2d04c0b098c2ead962f`

Published Sprint 08 telah direkonsiliasi sebagai `Published`. Parent published Sprint 08 adalah `7420539c17be0758c8393f16e6f4232666a2bb2c`, dan published tree sesuai approved Sprint 08 exact head `1f2305359f3353fe40a24dc6629ee34987498efb`.

## Migration identifier

`MigrationIdentifier` menerima format canonical:

```text
MIG_YYYYMMDD_HHMMSS_DESCRIPTION
```

Identifier dinormalisasi menjadi uppercase dan wajib memiliki timestamp serta deskripsi canonical. URL, path, whitespace internal, delimiter bebas, dan identifier non-canonical ditolak.

## Checksum and tamper detection

`MigrationChecksum` menggunakan SHA-256 lowercase sepanjang 64 karakter. Checksum deklaratif wajib sama dengan checksum artifact fingerprint.

Canonical descriptor hanya digunakan untuk menghasilkan digest dan tidak disimpan dalam object. Dengan demikian, marker sensitif, path sintetis, atau material internal tidak ikut muncul dalam JSON, serialization, print, export, plan, maupun result.

Manifest menolak checksum mismatch dengan stable error code `MIGRATION_CHECKSUM_MISMATCH`.

## Ordered manifest

`MigrationManifest`:

- menolak identifier duplikat;
- mewajibkan urutan identifier meningkat;
- menolak dependency yang tidak tersedia;
- mewajibkan dependency muncul sebelum migration yang bergantung kepadanya;
- menolak self-dependency dan duplicate dependency;
- mempertahankan ordered immutable entry list.

Manifest tidak memuat SQL atau database credential.

## Safety classification

Safety classification:

- `SAFE`;
- `CAUTION`;
- `DESTRUCTIVE`.

`MigrationPlanningPolicy::safeDefault()` menolak migration berklasifikasi `DESTRUCTIVE`. Representasi destructive hanya dapat masuk ke dry-run plan melalui policy eksplisit. Foundation ini tidak menyediakan production executor atau deployment authority.

## Rollback classification

Rollback classification:

- `REVERSIBLE`;
- `FORWARD_ONLY`.

`MigrationPlanner::assertRollbackAvailable()` menolak rollback untuk migration `FORWARD_ONLY` menggunakan stable error code `MIGRATION_ROLLBACK_UNAVAILABLE`.

Classification tidak mengandung rollback SQL, production command, atau executable database instruction.

## Dry-run plan

`MigrationPlanner` menghasilkan `MigrationPlan` yang selalu `dry_run=true`.

Plan hanya memuat:

- ordered migration identifiers;
- declared checksums;
- derived plan checksum;
- dry-run state.

Plan menolak applied identifier yang tidak terdapat dalam manifest, applied state yang bukan contiguous prefix, dan dependency state yang belum terpenuhi.

## Lock and execution boundary

`MigrationLock` memisahkan concurrency coordination dari planner dan executor. `SyntheticMigrationLock` menyediakan deterministic test tanpa shared service atau database.

`MigrationExecutor` merupakan interface. Sprint 09 hanya menyediakan `SyntheticMigrationExecutor` yang memproses dry-run identifiers dan tidak membuka database, menjalankan SQL, membuat schema, atau menyimpan state persisten.

`MigrationExecutionService`:

- mewajibkan correlation ID;
- memperoleh lock sebelum executor dipanggil;
- menolak lock yang tidak tersedia;
- selalu melepaskan lock setelah success atau failure;
- memetakan exception menjadi stable error codes;
- tidak meneruskan raw exception atau internal detail.

## Stable error codes

- `MIGRATION_IDENTIFIER_INVALID`
- `MIGRATION_CHECKSUM_INVALID`
- `MIGRATION_CHECKSUM_MISMATCH`
- `MIGRATION_DUPLICATE_IDENTIFIER`
- `MIGRATION_ORDER_INVALID`
- `MIGRATION_DEPENDENCY_MISSING`
- `MIGRATION_DESTRUCTIVE_DENIED`
- `MIGRATION_ROLLBACK_UNAVAILABLE`
- `MIGRATION_PLAN_INVALID`
- `MIGRATION_LOCK_UNAVAILABLE`
- `MIGRATION_EXECUTION_FAILED`
- `MIGRATION_NOT_READY`

## Validation

Commands:

```bash
php -l src/Migration/Foundation.php
php -l tests/migration.php
php tests/run.php
php tests/runtime.php
php tests/persistence.php
php tests/migration.php
```

Bounded candidate result:

```text
No syntax errors detected in src/Migration/Foundation.php
No syntax errors detected in tests/migration.php
Migration Governance and Safety tests passed: 47 assertions.
```

Migration tests mencakup:

- canonical identifier;
- invalid identifier rejection;
- SHA-256 checksum;
- duplicate migration rejection;
- checksum mismatch rejection;
- missing dependency rejection;
- manifest ordering rejection;
- unknown applied identifier rejection;
- non-contiguous applied-state rejection;
- destructive migration deny-by-default;
- explicit destructive classification representation;
- reversible dan forward-only classification;
- dry-run plan;
- lock acquisition dan release;
- executor failure mapping;
- secret leakage negative test;
- path leakage negative test;
- SQL and credential leakage negative test;
- no-business-schema test;
- no-POS behavior test;
- no-production-database-connection test.

Tests tidak menggunakan network, production database, production credential, atau production data.

## Capability gap

Capability berikut tetap `UNKNOWN`:

- production migration account;
- least-privilege migration grants;
- database advisory lock support dan policy;
- production migration transaction semantics;
- online schema change capability;
- backup retention dan restore verification;
- RTO dan RPO;
- production migration window;
- deployment method;
- rollback execution authority;
- production database connection limits.

Capability `UNKNOWN` tidak diasumsikan tersedia dan tidak diperlukan oleh synthetic Sprint 09 foundation.

## Explicit exclusions

Sprint 09 tidak membuat:

- business schema;
- tenant data model final;
- user production table;
- POS table;
- sales, payment, inventory, atau catalog schema;
- production SQL;
- production migration file;
- MariaDB hosting migration;
- database credential;
- production database connection;
- repository bisnis;
- business query;
- persistent session;
- seed production;
- deployment;
- release;
- workflow atau ruleset change.

## Next dependency

Tahap setelah Sprint 09 memerlukan publication, exact-head validation, independent approval, dan Product Owner authorization terpisah. Sprint 09 tidak memberi authority untuk membuat business schema, menjalankan migration produksi, atau memulai POS.

Attribution: Lab | zefry

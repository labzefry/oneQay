# Generic Data Definition Contract and Tenant Isolation Schema Policy Foundation

## Scope

Sprint 10 menyediakan foundation framework-agnostic untuk kontrak definisi data generik dan kebijakan isolasi tenant. Scope dibatasi pada identifier canonical, tipe scalar portabel, constraint generik, default-value policy, primary-key dan uniqueness policy, reference contract, tenant-scope classification, tenant-isolation deny-by-default, immutable manifest, deterministic validator, safe report, stable error codes, tests, dokumentasi, dan checkpoint.

Foundation ini tidak membuat executable SQL, production table, final tenant data model, migration file, production migration, repository bisnis, query bisnis, POS, deployment, atau release.

## Published base

Exact published base Sprint 10:

`227290c10b26d7f310f669526f3722c82489050e`

Published Sprint 09 direkonsiliasi sebagai `Published`. Parent published Sprint 09 adalah `5e620f7e1975450d7538e2d04c0b098c2ead962f`, dan published tree sesuai approved Sprint 09 exact head `9173a238cb012819cba7355e46cf902a8e347d31`.

## Historical residual risk

Authentication, Tenant Context, Authorization, Configuration, Runtime, Bootstrap, dan Persistence regressions tidak dieksekusi ulang sebelum Sprint 09 merge. Fakta historis tersebut tetap dicatat dan tidak diubah menjadi klaim bahwa regression telah dijalankan sebelum merge.

Pada Sprint 10, regression suite tersebut dijalankan setelah publication terhadap source exact published base yang blob-nya diverifikasi, kemudian dijalankan bersama kandidat Sprint 10. Hasil ini merupakan evidence Sprint 10 dan bukan retroactive evidence untuk lifecycle sebelum Sprint 09 merge.

## Canonical data definition identifier

`DataDefinitionIdentifier`:

- menormalisasi identifier menjadi uppercase snake case;
- membatasi panjang maksimal 64 karakter;
- menolak URL, path, whitespace internal, delimiter bebas, dan identifier yang diawali angka;
- menolak reserved namespace seperti `SYS`, `SQL`, `MYSQL`, `PG`, `INFORMATION_SCHEMA`, `INTERNAL`, dan `ONEQAY_INTERNAL`.

Identifier digunakan untuk entity, attribute, unique constraint, reference, generated-default marker, dan tenant key. Identifier tidak merepresentasikan nama physical table atau executable database object.

## Portable scalar type vocabulary

Tipe scalar portabel yang didukung:

- `STRING`;
- `INTEGER`;
- `DECIMAL`;
- `BOOLEAN`;
- `UUID`;
- `DATE`;
- `DATETIME`;
- `JSON`.

Tipe tersebut bersifat logical dan framework-agnostic. Tipe tidak memetakan diri secara otomatis ke vendor database, SQL type, storage engine, collation, atau production column.

## Value constraints

`ValueConstraint` menerapkan aturan deterministic:

- `STRING` wajib memiliki length antara 1 dan 4096;
- `DECIMAL` wajib memiliki precision antara 1 dan 38;
- scale `DECIMAL` wajib antara 0 dan precision;
- tipe selain `STRING` tidak menerima length;
- tipe selain `DECIMAL` tidak menerima precision atau scale.

Invalid length, precision, atau scale ditolak menggunakan stable error code.

## Nullability and default-value policy

Nullability classification:

- `REQUIRED`;
- `NULLABLE`.

Default-value classification:

- `NONE`;
- `NULL_VALUE`;
- `LITERAL_FINGERPRINT`;
- `GENERATED_IDENTIFIER`.

Literal default tidak disimpan sebagai raw value. Foundation menyimpan SHA-256 fingerprint dari canonical scalar representation sehingga secret, path, credential, atau data sintetis tidak ikut muncul dalam JSON, serialization, manifest, atau validation report.

Required attribute tidak dapat menggunakan `NULL_VALUE` default policy.

## Primary-key policy

`PrimaryKeyDefinition`:

- wajib memiliki satu sampai empat attribute identifier;
- menolak duplicate attribute;
- mewajibkan seluruh attribute tersedia dalam entity;
- mewajibkan seluruh primary-key attribute berstatus `REQUIRED`;
- mewajibkan tenant key menjadi bagian primary key untuk entity `TENANT_SCOPED`.

Policy tersebut merupakan logical contract dan tidak membuat physical primary-key constraint.

## Uniqueness policy

`UniqueConstraintDefinition`:

- memiliki canonical identifier;
- memiliki satu sampai delapan attribute identifier;
- menolak duplicate attribute;
- hanya dapat merujuk attribute yang tersedia dalam entity;
- untuk entity `TENANT_SCOPED`, setiap unique constraint wajib memasukkan tenant key.

Aturan tenant key mencegah uniqueness policy yang secara default berlaku lintas tenant.

## Generic reference contract

`ReferenceDefinition` memuat:

- canonical reference identifier;
- target entity identifier;
- source-to-target attribute map.

Validator mewajibkan:

- source dan target attribute tersedia;
- source dan target menggunakan portable scalar type yang sama;
- target attribute map cocok dengan target primary key atau salah satu target unique constraint;
- target entity tersedia dalam manifest;
- tenant-scoped target hanya dapat direferensikan oleh tenant-scoped source;
- tenant-to-tenant reference wajib memetakan source tenant key tepat ke target tenant key.

Tidak tersedia opt-in untuk cross-tenant reference. Cross-tenant reference ditolak secara deny-by-default.

## Tenant-scope classification

Tenant scope:

- `GLOBAL`;
- `TENANT_SCOPED`.

Entity `TENANT_SCOPED` wajib memiliki tenant key yang:

- tersedia sebagai attribute;
- berstatus `REQUIRED`;
- menggunakan logical type `UUID` atau `STRING`;
- tidak memiliki literal atau null default;
- menjadi bagian primary key;
- menjadi bagian setiap unique constraint;
- dipetakan secara eksplisit pada tenant-to-tenant reference.

Entity `GLOBAL` tidak boleh mendeklarasikan tenant key.

## Immutable manifest

`DataDefinitionManifest`:

- hanya menerima `EntityDefinition`;
- menolak manifest kosong;
- menolak duplicate entity identifier;
- mempertahankan ordered immutable entity list;
- menyediakan deterministic entity index;
- tidak memuat SQL, connection detail, credential, atau production data.

`EntityDefinition` mempertahankan immutable attribute, unique constraint, reference, primary-key, tenant-scope, dan tenant-key contracts.

## Deterministic policy validator

`DataDefinitionPolicyValidator` memvalidasi:

- tenant-key presence dan safety;
- primary-key attribute availability dan nullability;
- tenant-key membership pada tenant primary key;
- uniqueness attribute availability;
- tenant-key membership pada tenant uniqueness;
- reference target availability;
- source-target type compatibility;
- target key eligibility;
- tenant-to-tenant key mapping;
- global-to-tenant denial.

Validator tidak membuka database, membaca production data, menjalankan SQL, atau menghasilkan migration.

## Safe validation report

`DataDefinitionValidationReport` hanya memuat:

- valid state;
- stable error codes;
- canonical entity identifiers;
- correlation ID.

Report tidak memuat raw default value, descriptor, internal path, SQL, credential, database endpoint, production data, atau raw exception.

## Stable error codes

- `DATA_DEFINITION_IDENTIFIER_INVALID`
- `DATA_DEFINITION_RESERVED_NAMESPACE`
- `DATA_DEFINITION_SCALAR_TYPE_INVALID`
- `DATA_DEFINITION_CONSTRAINT_INVALID`
- `DATA_DEFINITION_DEFAULT_INVALID`
- `DATA_DEFINITION_PRIMARY_KEY_INVALID`
- `DATA_DEFINITION_UNIQUE_POLICY_INVALID`
- `DATA_DEFINITION_REFERENCE_INVALID`
- `DATA_DEFINITION_TENANT_KEY_REQUIRED`
- `DATA_DEFINITION_TENANT_KEY_INVALID`
- `DATA_DEFINITION_CROSS_TENANT_REFERENCE_DENIED`
- `DATA_DEFINITION_DUPLICATE_ENTITY`
- `DATA_DEFINITION_DUPLICATE_ATTRIBUTE`
- `DATA_DEFINITION_MANIFEST_INVALID`
- `DATA_DEFINITION_NOT_READY`

## Validation

Commands yang dijalankan pada kandidat Sprint 10:

```bash
php -l src/Auth/Foundation.php
php -l src/Tenant/Foundation.php
php -l src/Authorization/Foundation.php
php -l src/Configuration/Foundation.php
php -l src/Runtime/Foundation.php
php -l src/Persistence/Foundation.php
php -l src/Migration/Foundation.php
php -l src/DataDefinition/Foundation.php
php -l src/DataDefinition/ValueObjects.php
php -l src/DataDefinition/Contracts.php
php -l src/DataDefinition/Validation.php
php -l tests/data-definition.php
php -l tests/data-definition-contracts.php
php -l tests/data-definition-policy.php
php tests/run.php
php tests/runtime.php
php tests/persistence.php
php tests/migration.php
php tests/data-definition.php
```

Hasil:

```text
Authentication, Tenant Context, Authorization, and Configuration Boundary tests passed: 51 assertions.
Runtime Capability and Bootstrap tests passed: 17 assertions.
Persistence Capability and Database Connection Boundary tests passed: 39 assertions.
Migration Governance and Safety tests passed: 47 assertions.
Data Definition and Tenant Isolation Policy tests passed: 70 assertions.
```

Seluruh source dan test foundation lama yang digunakan untuk regression diverifikasi terhadap Git blob SHA pada exact published base sebelum execution.

Tests tidak menggunakan network, production database, production credential, atau production data.

## Negative tests

Sprint 10 mencakup:

- invalid identifier rejection;
- reserved namespace rejection;
- invalid scalar type rejection;
- invalid string length rejection;
- invalid decimal precision dan scale rejection;
- required-null default rejection;
- raw literal leakage rejection;
- duplicate entity rejection;
- duplicate attribute rejection;
- missing tenant key rejection;
- nullable tenant key rejection;
- tenant primary key without tenant key rejection;
- global entity with tenant key rejection;
- tenant uniqueness without tenant key rejection;
- global-to-tenant reference rejection;
- tenant reference without tenant-key mapping rejection;
- incomplete target-key reference rejection;
- reference scalar type mismatch rejection;
- secret, path, SQL, credential, dan data leakage-negative checks;
- no-production-table check;
- no-business-schema check;
- no-POS behavior check;
- no-production-database-connection check;
- no-migration-execution check.

## Capability gap

Capability berikut tetap `UNKNOWN`:

- final tenant data model;
- physical table naming policy;
- MariaDB type mapping;
- collation dan index-length limits;
- physical foreign-key support policy;
- online schema change capability;
- production migration account dan grants;
- production advisory lock strategy;
- backup retention dan restore verification;
- RTO dan RPO;
- deployment method;
- rollback execution authority;
- production connection limits.

Capability `UNKNOWN` tidak diasumsikan tersedia dan tidak diperlukan oleh synthetic Sprint 10 foundation.

## Explicit exclusions

Sprint 10 tidak membuat:

- executable SQL;
- production table;
- user production table;
- final tenant data model;
- POS, sales, payment, inventory, atau catalog schema;
- migration file;
- production migration;
- PDO production adapter;
- database credential;
- production database connection;
- repository bisnis;
- business query;
- persistent session;
- production seed;
- deployment;
- release;
- workflow atau ruleset change.

## Next dependency

Tahap setelah Sprint 10 memerlukan publication, exact-head validation, independent approval, dan Product Owner authorization terpisah. Sprint 10 tidak memberikan authority untuk membuat physical schema, production table, executable SQL, migration production, POS, deployment, atau release.

Attribution: Lab | zefry

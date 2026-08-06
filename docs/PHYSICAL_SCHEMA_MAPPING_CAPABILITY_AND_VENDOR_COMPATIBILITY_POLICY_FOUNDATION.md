# Physical Schema Mapping Capability and Vendor Compatibility Policy Foundation

## Scope

Sprint 11 menyediakan foundation framework-agnostic untuk memetakan kontrak data logis ke representasi physical compatibility policy untuk MariaDB. Foundation hanya memodelkan identifier, vendor vocabulary, scalar mapping, charset, collation, index budget, foreign-key compatibility, tenant-key requirements, immutable manifest, deterministic validator, safe report, stable error codes, dan synthetic tests.

Foundation tidak menghasilkan executable SQL, tidak membuat production table, tidak membuka database, tidak menjalankan migration, dan tidak menetapkan final tenant atau business data model.

## Published base

Exact published base: `302c9957bcda55fe8265fc0a0449003d59f23620`.

Sprint 10 direkonsiliasi sebagai Published melalui PR #49. Approved Sprint 10 exact head adalah `261ee8650ba30edf9afccf9a9853768d7c7f958a`, dan approved serta published tree adalah `b70c78cdfc0befe88908dcf64cc4d8fe3a2efd69`.

## Historical residual risk

Authentication, Tenant Context, Authorization, Configuration, Runtime, Bootstrap, dan Persistence regressions tidak dieksekusi ulang sebelum Sprint 09 merge. Sprint 10 dan Sprint 11 regression evidence tidak berlaku retroaktif sebagai pre-merge Sprint 09 evidence.

## Compatibility policy

- Physical identifier menggunakan lowercase snake case, maksimal 64 karakter, dan menolak reserved namespace.
- Vendor vocabulary saat ini hanya `MARIADB_11`; ini adalah policy target, bukan live server discovery.
- Charset hanya `UTF8MB4`.
- Collation classification: `UTF8MB4_UNICODE_CI` dan `UTF8MB4_BINARY`.
- Logical `STRING` dipetakan ke `VARCHAR`.
- Logical `INTEGER` dipetakan ke `BIGINT_SIGNED`.
- Logical `DECIMAL` dipetakan ke `DECIMAL` dengan precision 1–38 dan scale 0–precision.
- Logical `BOOLEAN` dipetakan ke `TINYINT_BOOLEAN`.
- Logical `UUID` dipetakan ke `CHAR_UUID` dengan fixed length 36 dan binary-compatible collation.
- Logical `DATE`, `DATETIME`, dan `JSON` dipetakan ke `DATE`, `DATETIME`, dan `JSON_DOCUMENT`.
- Unsupported mapping ditolak secara deny-by-default.

## Index and reference policy

Primary dan unique index mapping memakai ordered logical attribute identifiers. Validator menerapkan deterministic index-key budget maksimal 3072 estimated bytes. `JSON_DOCUMENT` tidak diterima sebagai index-compatible mapping.

Foreign-key compatibility hanya `COMPATIBLE` atau `INCOMPATIBLE`. Source dan target harus memiliki logical type, physical type, length, precision, scale, charset, dan collation yang kompatibel. Target wajib merepresentasikan primary index atau eligible unique index.

Entity tenant-scoped wajib memiliki tenant-key physical mapping, memasukkannya ke primary index dan setiap unique index, serta memetakan tenant key pada tenant-to-tenant reference. Global-to-tenant reference ditolak secara deny-by-default.

## Safe report

`VendorCompatibilityReport` hanya memuat compatible state, stable error codes, canonical logical entity identifiers, vendor identifier, dan correlation ID. Report tidak memuat raw SQL, path internal, database endpoint, credential, production data, raw exception, atau arbitrary input material.

## Validation

Exact-base source blobs diverifikasi sebelum execution. Hasil kandidat Sprint 11:

```text
Authentication, Tenant Context, Authorization, and Configuration Boundary tests passed: 51 assertions.
Runtime Capability and Bootstrap tests passed: 17 assertions.
Persistence Capability and Database Connection Boundary tests passed: 39 assertions.
Migration Governance and Safety tests passed: 47 assertions.
Data Definition and Tenant Isolation Policy tests passed: 70 assertions.
Physical Mapping and Vendor Compatibility tests passed: 88 assertions.
```

PHP syntax validation lulus untuk seluruh foundation dan tests yang dijalankan. Pengujian menggunakan PHP CLI 8.4.16 dengan composer constraint `>=8.2`; target hosting PHP 8.3.26 tidak digunakan dalam local test execution. Tests tidak menggunakan network, production database, production credential, atau production data.

Negative tests mencakup invalid identifier, reserved namespace, unsupported vendor/scalar mapping, invalid charset/collation, invalid length/precision/scale, duplicate mapping, missing tenant key, tenant index policy, index budget overflow, JSON index rejection, incompatible reference, non-eligible target key, cross-tenant reference, leakage-negative, no-executable-SQL, no-production-table, no-business-schema, no-POS, dan no-production-database-connection.

## Capability gap

Tetap `UNKNOWN`: final tenant data model, final business schema, live MariaDB patch compatibility, production SQL mode, storage-engine settings, actual index-prefix limits, live collation availability, physical foreign-key enforcement, online schema change capability, production migration grants, lock strategy, backup/restore evidence, RTO/RPO, deployment method, rollback authority, dan production connection limits.

## Explicit exclusions

Sprint 11 tidak membuat executable SQL, DDL, production table, user production table, final tenant/business/POS schema, migration file, production migration, production PDO adapter, database credential, production database connection, repository bisnis, business query, persistent session, seed, deployment, release, workflow, atau ruleset change.

Attribution: Lab | zefry

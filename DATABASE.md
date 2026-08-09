# oneQay Database Handbook

## Goals

Database harus menjaga integritas transaksi, isolasi tenant, auditability, compatibility migration, backup/restore, dan performance predictable. **MySQL Server** adalah canonical relational database engine family melalui substantive DEC-005. Exact MySQL LTS series, minor, patch, runtime, provider, dan configuration tetap separately gated.

## Canonical engine and physical tenancy direction

Substantive DEC-005 menetapkan:

- canonical relational database engine family: **MySQL Server**;
- version boundary: **supported MySQL LTS family**, dengan exact series/minor/patch deferred;
- default physical tenancy: **shared database + shared schema + mandatory immutable tenant isolation key**;
- future stronger physical isolation: bounded hybrid evolution path hanya melalui separate authority dan material evidence;
- tenant authorization: **Application-authoritative** dengan database integrity/security sebagai defense-in-depth;
- database/vendor-specific behavior: **Infrastructure concern**;
- migration/schema evolution: versioned, deterministic, compatible, recoverable, dan reconcilable;
- recoverability: backup success bukan bukti recoverability tanpa successful restore evidence.

Keputusan ini tidak menetapkan actual schema, SQL, DDL, migration, database configuration, provider, replication topology, atau production implementation.

## Data ownership

Setiap tabel memiliki owning module. Modul lain mengakses data melalui application contract atau event, bukan join/write langsung. Shared reference data harus memiliki owner dan lifecycle yang jelas.

## Tenant isolation

Baseline yang disetujui:

- tenant ID immutable menjadi isolation key;
- domain/subdomain hanya routing hint;
- tenant-scoped table memiliki tenant ID non-null;
- unique constraint tenant-scoped menyertakan tenant ID;
- foreign key tenant-scoped mencegah referensi lintas tenant;
- query enforcement berada pada repository/data-access boundary;
- privileged cross-tenant access menggunakan interface terpisah dan audit.

Default physical isolation melalui DEC-005 adalah **shared database + shared schema** dengan mandatory tenant identity. Dedicated database atau stronger physical storage boundary hanya merupakan bounded future evolution path untuk requirement enterprise/regulatory/jurisdiction/scale/recovery/security yang separately verified dan separately authorized.

Tenant authorization tetap Application-authoritative. Database constraint dan database-native security mechanism berfungsi sebagai integrity enforcement dan defense-in-depth, bukan pengganti Application authorization ownership.

## Identifier strategy

- Public identifier harus sulit ditebak bila enumeration berisiko.
- Internal identifier tidak boleh dipakai sebagai authorization control.
- Tenant ID tidak dapat berubah setelah dibuat.
- External provider ID disimpan bersama provider dan tenant context.
- Natural key bisnis dapat berubah dan bukan default primary key.

## Data types

- Money menggunakan fixed precision decimal dan currency code.
- Quantity menggunakan precision sesuai domain dan unit eksplisit.
- Time disimpan sebagai UTC instant; local business date disimpan bila memiliki makna domain.
- Boolean tidak digunakan bila state lebih dari dua; gunakan explicit status.
- JSON hanya untuk data fleksibel yang tidak membutuhkan relational constraint/query kritis.
- Sensitive value memiliki classification dan encryption/tokenization policy.

## Schema conventions

- Nama konsisten, eksplisit, dan mengikuti ubiquitous language.
- `created_at`, `updated_at`, actor/audit field, dan version field digunakan sesuai kebutuhan.
- Soft delete bukan default; gunakan bila retention dan restore semantics jelas.
- Status transition dijaga application/domain invariant dan audit.
- Index dibuat berdasarkan access pattern dan diverifikasi dengan execution plan.
- MySQL-specific behavior atau optimization tidak boleh menjadi dependency Domain/Application; detail vendor ditempatkan pada Infrastructure boundary.

## Migration policy

Setiap perubahan schema wajib memiliki:

- unique version dan descriptive name;
- forward migration;
- rollback/recovery strategy;
- compatibility window;
- estimated duration/lock impact;
- backup requirement;
- test pada snapshot representatif;
- owner dan monitoring signal.

Destructive change menggunakan **expand → migrate → verify → contract**. Application versi lama dan baru harus dapat berjalan selama compatibility window bila rolling/staged deployment digunakan.

## Data migration

- Batch besar resumable dan idempotent.
- Simpan checkpoint, progress, failure count, dan correlation ID.
- Rate dibatasi agar OLTP tetap sehat.
- Rekonsiliasi count, total, checksum, dan domain invariant setelah migrasi.
- Raw sensitive data tidak boleh diekspor ke workstation tanpa masking dan approval.

## Transaction and concurrency

- Transaction boundary mengikuti use case.
- Gunakan optimistic concurrency/versioning untuk conflicting edit bila sesuai.
- Lock explicit harus bounded dan memiliki deadlock handling.
- Distributed side effect menggunakan outbox/saga-like compensation, bukan transaction lintas vendor.
- Financial posting dan inventory movement harus idempotent dan auditable.

## Audit and history

Audit minimum mencatat actor, tenant, action, resource, before/after yang aman, timestamp, source, correlation ID, dan outcome. Secret, password, token, dan sensitive payment payload tidak boleh masuk audit. Retention serta immutability ditetapkan berdasarkan classification dan compliance.

## Backup and restore

- Backup terenkripsi, access-controlled, monitored, dan memiliki retention.
- Backup tenant harus dapat ditemukan dan dipulihkan sesuai isolation model.
- Restore test dilakukan berkala pada environment terisolasi.
- Keberhasilan job backup bukan bukti recoverability; hanya restore rehearsal yang lulus.
- Shared-schema physical backup tidak otomatis membuktikan tenant-scoped recoverability; tenant recovery memerlukan separately designed and verified procedure.
- RPO/RTO ditetapkan per capability sebelum production melalui DEC-012.

## Performance

- Semua collection query memiliki limit.
- N+1 dan full scan pada hot path dilarang.
- Index mempertimbangkan tenant ID sebagai leading component sesuai access pattern.
- Reporting berat dipindahkan ke read model/warehouse saat threshold tercapai.
- Slow query budget, connection pool, storage growth, dan vacuum/maintenance dipantau.

## Privacy lifecycle

Setiap data class memiliki purpose, owner, legal basis bila relevan, retention, deletion/anonymization, export, dan access policy. Tenant deletion harus aman, terotorisasi, bertahap, dapat diaudit, dan menghormati retention obligation.

## Required tests

- migration forward dan recovery;
- tenant isolation dan foreign-key tampering;
- monetary precision dan rounding;
- concurrency/idempotency;
- backup/restore;
- data retention/deletion;
- performance query kritis;
- compatibility antara application version selama rollout.

## Database change checklist

1. Owner module dan business purpose jelas.
2. Tenant scope, key, constraint, dan index benar.
3. Classification/encryption/retention ditetapkan.
4. Migration dan rollback direhearsal.
5. Lock, size, duration, dan load impact dianalisis.
6. API/event/report consumers diperiksa.
7. Tests, monitoring, documentation, task, dan changelog diperbarui.

# oneQay Database Handbook

## Canonical post-Sprint 30 program-state reconciliation — 2026-08-19

For current identity, security, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint28/post-Sprint29 wording retained below as historical provenance.

- Sprint 21 through Sprint 30 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 29 First-Control-Principal Bootstrap Credential Foundation is published through source PR #195 and closes the first protected-control credential circular dependency without credential overwrite, password recovery, or session creation.
- Sprint 30 Privileged TOTP MFA Foundation is published through PR #199 as `6d41755eba4030c2b0b7c4f3b7a5806b761b0ad7` with tree `bf1d56af5524e77919833bd64b585cdca84af55d` after **22/22** exact-head workflows succeeded.
- Sprint 30 source remained within the exact **46-path** envelope whose sorted-path SHA-256 is `95daaf86ba93ae797fccf3825d65d27acd4f71ee58916898a16fbc83d432a5ce`.
- Canonical source migrations are exactly **#1 through #9**. Migration #9 adds one tenant-scoped TOTP-factor row per identity with encrypted secret ciphertext and monotonic accepted-time-step replay state.
- The direct TOTP dependency is pinned to `spomky-labs/otphp` **11.5.0**; oneQay does not implement custom TOTP/HMAC/Base32 cryptography.
- `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED` remains source-default **false** and Sprint 29–30 delivery remains bounded to **Local/Test/CI**.
- For an armed protected-control principal, password verification alone does not establish the full privileged session. Restricted enrollment/challenge state is used until successful confirmed TOTP challenge establishes full session MFA evidence.
- TOTP secrets are Restricted, encrypted at rest, context-bound to tenant + identity, and never stored as plaintext. Accepted TOTP time steps advance monotonically to deny replay.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Password change/reset/recovery, MFA recovery, factor replacement/deletion, multiple factors, WebAuthn/passkeys, federation, API-token authentication, Preview auth activation, and Production auth activation remain separately governed.
- **JRN-003 remains UNRESOLVED**; this reconciliation creates no password/MFA recovery path.
- The next logical governed identity/security concern is **Privileged Reauthentication / Step-Up Session Freshness Foundation**. DEC-006 already requires risk-based reauthentication/step-up for sensitive operations. This concern is **CANDIDATE / NOT AUTHORIZED** until a separate bounded entry gate freezes semantics, freshness evidence, session transitions, routes, exact source envelope, schema decision, and preservation tests.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_30_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 28 database-state reconciliation — 2026-08-18

For current database/schema interpretation, this section supersedes older current-facing M7.5 database-state wording retained below as historical qualification provenance.

Canonical source now contains exactly migrations **#1 through #8**. Migrations #1–#7 remain immutable; migration #8, `0000_00_00_000008_create_initial_password_enrollments.php`, is the only Sprint 28 schema addition and is additive/forward-only.

Current published credential/control schema progression includes:

1. foundational context graph;
2. organizational access grants;
3. scoped role/permission policy;
4. policy mutation journal;
5. initial tenant-administrator provisioning journal;
6. protected-control administrator mutation journal;
7. identity password credentials; and
8. initial password enrollments.

Migration #7 stores exact tenant-scoped password credential ownership `(tenant_id, identity_id)` using one-way hashes. Migration #8 stores secret-minimal enrollment lifecycle evidence and persists only the enrollment token digest, never plaintext enrollment tokens or plaintext passwords.

Sprint 28 does not authorize credential update/upsert/delete, password reset/change/recovery/rotation/revocation, Production schema execution, or Technical Preview schema application. Technical Preview remains **`NO_SCHEMA_CHANGE`**, Production remains **`NO-GO / NOT AUTHORIZED`**, updater remains **`DISABLED / UNWIRED`**, and `ONEQAY_PERSISTENCE_ENABLED=false` remains the repository default.

The next logical identity concern, First-Control-Principal Bootstrap Credential Foundation, is separately governed and does not gain schema or migration authority from this documentation reconciliation.

The detailed canonical publication record is `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`. Earlier M7.5 and pre-schema current-state statements below remain historical provenance.

Attribution: **Lab | zefry**

## Canonical M7.5 lifecycle closure — 2026-08-17

For current database/runtime interpretation, this section supersedes the older current-facing M7.5 consolidation retained below as historical architecture/checkpoint text.

The bounded non-Production Technical Preview database qualification is now **CLOSED / EVIDENCE_COMPLETE / PUBLISHED**. The mandatory evaluator is **29 VERIFIED / 0 BLOCKED** after PR #129, and PR #130 records secure retirement of the disposable restore-rehearsal database without changing that evaluator.

Current bounded evidence therefore includes verified application connectivity, least privilege, transaction semantics, migration boundary, connection/resource visibility, Database Portability Contract conformance, database-backed tenant isolation, and successful isolated backup/restore rehearsal. Specifically:

- `ENGINE:TENANT_ISOLATION = VERIFIED`;
- `ENGINE:RESTORE_VERIFIED = VERIFIED`;
- `RUNTIME:BACKUP_RESTORE = VERIFIED`.

These Technical Preview facts do **not** establish a permanent Production business schema, Production disaster-recovery SLA, tenant-selective Production restore capability, general Production readiness, or permission to execute new database/schema/migration work. Exact numerical Production RPO/RTO remain outside this evidence claim.

`lifecycle_authority_created=false` remains true for the M7.5 evidence package. M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, and Production remain separately gated and **NOT AUTHORIZED**; production readiness remains **NO-GO**.

Attribution: **Lab | zefry**

## Canonical program-state consolidation — 2026-08-16

For current database/runtime interpretation, this section supersedes older M7.5 qualification wording retained below as historical architecture/checkpoint text.

The bounded non-Production P1/cPanel MariaDB qualification has materially progressed: application connectivity, least privilege, transaction semantics, migration boundary, connection-limit visibility, backup export, and Database Portability Contract controls are now governed `VERIFIED` evidence. The complete M7.5 evaluator is **26 VERIFIED / 3 BLOCKED**, outcome **BLOCKED / INCOMPLETE**, with `lifecycle_authority_created=false`.

The three remaining blockers are:

- `ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED`;
- `ENGINE:TENANT_ISOLATION:PARTIAL`;
- `RUNTIME:BACKUP_RESTORE:PARTIAL`.

This does **not** establish durable Production business persistence, a permanent business schema, full durable two-tenant database-backed isolation, successful restore, Production recoverability, or Production readiness. Existing backup/export evidence must not be interpreted as verified restore. M7.6, M7.7, Phase 0 Exit, Sprint 14, Release, and Production remain not authorized.

Historical DEC-005/DEC-005R provenance and prior qualification snapshots below remain preserved.

## Goals

Database harus menjaga integritas transaksi, isolasi tenant, auditability, compatibility migration, backup/restore, portability, dan performance predictable. Current canonical direction melalui substantive **DEC-005R — Portable Relational Persistence Architecture** adalah engine-neutral Domain/Application dengan qualified relational engine profiles dan target **ZERO BUSINESS-CODE CHANGE** antar profile yang resmi dikualifikasi.

Historical DEC-005 tetap Approved historical decision dan partially superseded oleh DEC-005R. Shared database/shared schema, immutable tenant isolation key, Application-authoritative tenant authorization, Infrastructure-owned vendor behavior, schema-evolution, dan recoverability principles tetap dipertahankan.

## Canonical relational persistence and physical tenancy direction

Substantive DEC-005R menetapkan:

- Domain dan Application: **database-engine-neutral**;
- business rules: tidak boleh bergantung pada relational-engine vendor identity;
- portability target: **ZERO BUSINESS-CODE CHANGE** antar officially qualified relational engine profiles;
- engine-specific behavior dan physical mapping: **Infrastructure concern**;
- canonical logical schema/contract: engine-neutral;
- relational engine-profile directions: **MariaDB, MySQL, PostgreSQL**;
- MariaDB 11.4 family: Stage-1 profile direction, **subject to runtime qualification**;
- formal **Database Portability Contract** direction;
- cross-engine qualification/CI direction;
- oneQay **Database Mobility & Migration Engine — DBME** direction;
- automatic physical adaptation hanya bila semantic equivalence terbukti;
- unsafe, lossy, atau ambiguous conversion: **fail closed**;
- default physical tenancy: **shared database + shared schema + mandatory immutable tenant isolation key**;
- future stronger physical isolation: bounded hybrid evolution path hanya melalui separate authority dan material evidence;
- tenant authorization: **Application-authoritative** dengan database integrity/security sebagai defense-in-depth;
- migration/schema evolution: versioned, deterministic, compatible, recoverable, dan reconcilable;
- recoverability: backup success bukan bukti recoverability tanpa successful restore evidence.

DEC-005R tidak menetapkan actual physical schema, SQL, DDL, executable migration, database credentials, live database connection, provider, replication topology, DBME implementation, cross-engine CI implementation, atau Production implementation.

## Engine-profile qualification

Engine/profile dianggap qualified hanya berdasarkan evidence, bukan berdasarkan nama produk, compatibility claim, atau kemampuan driver untuk terkoneksi.

Qualification yang kelak diotorisasi harus mencakup secara proporsional:

- logical contract mapping;
- transaction behavior;
- exact-money semantics;
- tenant-aware uniqueness dan referential integrity;
- UUID/identifier mapping;
- JSON semantics;
- date/time semantics;
- collation/case-sensitivity behavior;
- migration/schema evolution behavior;
- backup/verified restore;
- operational limits;
- representative performance/query-plan evidence;
- Database Portability Contract conformance.

MariaDB 11.4 evidence pada hosting saat ini adalah **engine-family/version evidence**, bukan runtime qualification.

## Data ownership

Setiap tabel memiliki owning module. Modul lain mengakses data melalui application contract atau event, bukan join/write langsung. Shared reference data harus memiliki owner dan lifecycle yang jelas.

## Tenant isolation

Baseline yang disetujui dan dipertahankan oleh DEC-005R:

- tenant ID immutable menjadi isolation key;
- domain/subdomain hanya routing hint;
- tenant-scoped table memiliki tenant ID non-null;
- unique constraint tenant-scoped menyertakan tenant ID;
- foreign key tenant-scoped mencegah referensi lintas tenant;
- query enforcement berada pada repository/data-access boundary;
- privileged cross-tenant access menggunakan interface terpisah dan audit.

Default physical isolation adalah **shared database + shared schema** dengan mandatory tenant identity. Dedicated database atau stronger physical storage boundary hanya merupakan bounded future evolution path untuk requirement enterprise/regulatory/jurisdiction/scale/recovery/security yang separately verified dan separately authorized.

Tenant authorization tetap Application-authoritative. Database constraint dan database-native security mechanism berfungsi sebagai integrity enforcement dan defense-in-depth, bukan pengganti Application authorization ownership.

## Identifier strategy

- Public identifier harus sulit ditebak bila enumeration berisiko.
- Internal identifier tidak boleh dipakai sebagai authorization control.
- Tenant ID tidak dapat berubah setelah dibuat.
- External provider ID disimpan bersama provider dan tenant context.
- Natural key bisnis dapat berubah dan bukan default primary key.
- Logical identifier contract harus dapat dipetakan secara deterministic ke setiap officially qualified engine profile.

## Data types

Canonical logical data vocabulary harus engine-neutral. Existing foundation direction mencakup `STRING`, `INTEGER`, `DECIMAL`, `BOOLEAN`, `UUID`, `DATE`, `DATETIME`, dan `JSON`.

- Money menggunakan fixed precision decimal dan currency code.
- Quantity menggunakan precision sesuai domain dan unit eksplisit.
- Time disimpan sebagai UTC instant; local business date disimpan bila memiliki makna domain.
- Boolean tidak digunakan bila state lebih dari dua; gunakan explicit status.
- JSON hanya untuk data fleksibel yang tidak membutuhkan relational constraint/query kritis.
- Sensitive value memiliki classification dan encryption/tokenization policy.
- Perbedaan physical type antar engine tidak boleh mengubah canonical business semantics.

## Schema conventions

- Nama konsisten, eksplisit, dan mengikuti ubiquitous language.
- `created_at`, `updated_at`, actor/audit field, dan version field digunakan sesuai kebutuhan.
- Soft delete bukan default; gunakan bila retention dan restore semantics jelas.
- Status transition dijaga application/domain invariant dan audit.
- Index dibuat berdasarkan access pattern dan diverifikasi dengan execution plan.
- Vendor-specific behavior atau optimization tidak boleh menjadi dependency Domain/Application; detail vendor ditempatkan pada Infrastructure engine-profile boundary.
- Physical mapping harus berasal dari canonical logical contract dan tidak boleh silently coerce semantic differences.

## Migration policy

Setiap perubahan schema yang kelak diotorisasi wajib memiliki:

- unique version dan descriptive name;
- forward migration;
- rollback/recovery strategy;
- compatibility window;
- estimated duration/lock impact;
- backup requirement;
- test pada snapshot representatif;
- owner dan monitoring signal;
- engine-profile applicability/compatibility evidence.

Destructive change menggunakan **expand → migrate → verify → contract**. Application versi lama dan baru harus dapat berjalan selama compatibility window bila rolling/staged deployment digunakan.

DEC-005R menambahkan future DBME architecture direction: preflight/dry-run, compatibility analysis, physical adaptation hanya jika equivalent, fail-closed unsafe conversion, reconciliation, controlled cutover, source retention, dan rollback hanya jika genuinely safe. Tidak ada executable DBME/migration yang diotorisasi oleh handbook ini.

## Data migration

Untuk future separately authorized migration/DBME execution:

- Batch besar resumable dan idempotent bila semantics memungkinkan.
- Simpan checkpoint, progress, failure count, dan correlation ID.
- Rate dibatasi agar OLTP tetap sehat.
- Rekonsiliasi count, total, checksum, dan domain invariant setelah migrasi.
- Source data dipertahankan sampai controlled acceptance/cutover policy terpenuhi.
- Unsafe/lossy conversion harus gagal sebelum cutover.
- Raw sensitive data tidak boleh diekspor ke workstation tanpa masking dan approval.
- Privileged migration operations memerlukan least privilege, explicit authority, dan audit evidence.

## Transaction and concurrency

- Transaction boundary mengikuti use case.
- Gunakan optimistic concurrency/versioning untuk conflicting edit bila sesuai.
- Lock explicit harus bounded dan memiliki deadlock handling.
- Distributed side effect menggunakan outbox/saga-like compensation, bukan transaction lintas vendor.
- Financial posting dan inventory movement harus idempotent dan auditable.
- Engine-profile implementation tidak boleh mengubah externally observable business transaction semantics.

## Audit and history

Audit minimum mencatat actor, tenant, action, resource, before/after yang aman, timestamp, source, correlation ID, dan outcome. Secret, password, token, dan sensitive payment payload tidak boleh masuk audit. Retention serta immutability ditetapkan berdasarkan classification dan compliance.

DBME/mobility operations yang kelak diimplementasikan harus menghasilkan audit evidence untuk preflight, source/target profile, plan identity, reconciliation, cutover, failure, dan recovery outcome.

## Backup and restore

- Backup terenkripsi, access-controlled, monitored, dan memiliki retention.
- Backup tenant harus dapat ditemukan dan dipulihkan sesuai isolation model.
- Restore test dilakukan berkala pada environment terisolasi.
- Keberhasilan job backup bukan bukti recoverability; hanya restore rehearsal yang lulus.
- Shared-schema physical backup tidak otomatis membuktikan tenant-scoped recoverability; tenant recovery memerlukan separately designed and verified procedure.
- RPO/RTO ditetapkan per capability sebelum production melalui DEC-012.
- Engine-profile qualification harus menyertakan relevant backup/restore evidence sebelum runtime acceptance.

## Performance

- Semua collection query memiliki limit.
- N+1 dan full scan pada hot path dilarang.
- Index mempertimbangkan tenant ID sebagai leading component sesuai access pattern.
- Reporting berat dipindahkan ke read model/warehouse saat threshold tercapai.
- Slow query budget, connection pool, storage growth, dan engine-specific maintenance dipantau melalui Infrastructure profile.
- Portability tidak berarti mengabaikan engine-specific optimization; optimization harus tetap berada di Infrastructure dan tidak mengubah business contract.

## Privacy lifecycle

Setiap data class memiliki purpose, owner, legal basis bila relevan, retention, deletion/anonymization, export, dan access policy. Tenant deletion harus aman, terotorisasi, bertahap, dapat diaudit, dan menghormati retention obligation.

Cross-engine mobility tidak boleh menurunkan privacy classification, tenant isolation, encryption/security boundary, atau retention obligations.

## Required tests

Future separately authorized persistence implementation harus mencakup secara applicable:

- migration forward dan recovery;
- tenant isolation dan foreign-key tampering;
- monetary precision dan rounding;
- concurrency/idempotency;
- backup/restore;
- data retention/deletion;
- performance query kritis;
- compatibility antara application version selama rollout;
- Database Portability Contract tests;
- cross-engine behavioral qualification untuk officially supported profiles;
- fail-closed unsupported/lossy mapping tests;
- migration reconciliation tests.

## Current implementation boundary

Current `apps/web` POS/business persistence masih synthetic/in-memory. Bounded Infrastructure coupling tetap ada pada `src/Persistence` dan `src/PhysicalMapping`; existing logical DataDefinition foundation sudah memiliki portable logical vocabulary direction. Migration foundation saat ini adalah governance/planning/dry-run foundation, bukan live DBME.

Publication DEC-005R ini tidak mengubah source tersebut.

## Database change checklist

1. Owner module dan business purpose jelas.
2. Tenant scope, key, constraint, dan index benar.
3. Canonical logical semantics dan engine-profile mapping jelas.
4. Classification/encryption/retention ditetapkan.
5. Migration dan rollback/recovery direhearsal.
6. Lock, size, duration, dan load impact dianalisis.
7. Database Portability Contract impact diperiksa.
8. API/event/report consumers diperiksa.
9. Tests, monitoring, documentation, task, dan changelog diperbarui.
10. Tidak ada database/vendor dependency yang bocor ke Domain/Application.

## Authority boundary

Dokumen ini tidak mengotorisasi physical schema, SQL, DDL, executable migrations, seeders, database drivers/adapters, DBME, cross-engine CI, live database access, credentials, data movement, M7.5, Sprint 14, deployment, release, atau Production.

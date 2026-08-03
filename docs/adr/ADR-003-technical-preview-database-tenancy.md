# ADR-003: Technical Preview Database and Physical Tenancy

- Status: Proposed
- Date: 2026-08-03
- Decision owner: Product Owner OneQay
- Evidence: Issue #23
- Scope: Technical Preview v0.0.1 only

## Context

Product Owner memilih opsi D1: MySQL-compatible engine dengan shared schema dan mandatory tenant identity. Engine dan versi aktual masih menunggu hosting evidence.

## Proposed decision

Gunakan satu database dan shared schema untuk preview. Setiap tenant-owned record memiliki immutable tenant identifier. Tenant identifier menjadi bagian dari unique constraints, foreign-key strategy, indexes, query enforcement, cache keys, jobs, files, audit records, dan backup/restore selection.

## Mandatory invariants

- Request tanpa validated tenant context ditolak.
- Tenant identifier tidak berasal hanya dari subdomain atau input klien.
- Cross-tenant relationship dilarang kecuali explicit platform-level model yang diaudit.
- Lookup by global ID tetap memerlukan tenant predicate.
- Seeder menghasilkan dua tenant sintetis deterministik.
- Tenant-isolation negative tests mencakup read, write, enumeration, cache, job, export, file, audit, dan restore.
- Migration wajib versioned, deterministic, forward-compatible, serta memiliki rollback/recovery plan.

## Alternatives considered

- D2 PostgreSQL shared schema dengan optional RLS defense: isolasi tambahan, tetapi Stage 1 availability belum terbukti.
- D3 database/schema per tenant: isolasi fisik kuat, tetapi provisioning, migration, pooling, backup, dan restore terlalu kompleks untuk T+5.

## Consequences

Shared schema meminimalkan operasi preview, tetapi membuat central tenant enforcement dan composite integrity constraints sebagai kontrol Critical. Engine-specific syntax tidak boleh masuk ke domain layer.

## Acceptance conditions

- Engine/version dan backup/restore capability terverifikasi.
- Database design review memetakan tenant ownership setiap candidate table.
- Isolation test matrix dan restore rehearsal tersedia.
- Product Owner menyetujui exact head ADR.

Keputusan ini belum menetapkan schema atau migration dan belum mengotorisasi source code.

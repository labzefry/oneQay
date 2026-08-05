# Tenant Context Foundation

## Scope

Sprint 04 menambahkan fondasi tenant context yang terbatas:

- immutable `TenantIdentifier`;
- immutable `TenantContext`;
- `TenantContextResolver` interface;
- session-backed resolver;
- validation dan stable tenant-context errors;
- session regeneration ketika tenant berubah;
- tenant-aware session key `tenant.active_id`;
- deterministic tests.

## Boundary dengan Authentication

Tenant context hanya dapat dipilih ketika `SessionGuard` memulihkan authenticated user. Tenant ID disimpan pada trusted session state. Logout atau session invalidation membersihkan seluruh state, termasuk tenant context.

Integrasi ini tidak menambah MFA, invitation, recovery, password reset, persistent user storage, atau persistent session storage.

## Explicit non-authorization statement

Tenant context bukan authorization. Modul ini tidak memiliki role, permission, policy, RBAC, ABAC, support access, atau cross-tenant administrator. Pemilihan tenant tidak membuktikan bahwa pengguna berwenang mengakses tenant tersebut; authorization mapping wajib ditambahkan pada sprint terpisah.

## Tenant identifier rules

Tenant identifier:

- dinormalisasi menjadi lowercase;
- tidak boleh kosong;
- maksimum 64 karakter;
- hanya menerima huruf kecil, angka, underscore, dan hyphen;
- tidak boleh diawali atau diakhiri underscore/hyphen;
- menolak domain, URL, dan path;
- tidak dipercaya langsung sebagai authorization decision.

Semua test menggunakan synthetic tenant identifiers.

## Error codes

- `TENANT_CONTEXT_REQUIRED`
- `TENANT_CONTEXT_INVALID`
- `TENANT_CONTEXT_UNAVAILABLE`

Error publik menggunakan `ErrorEnvelope` dengan correlation ID dan tidak memuat credential, password hash, session token, role, permission, atau internal authorization decision.

## Testing

Jalankan:

```bash
php tests/run.php
```

Test bersifat deterministic, tanpa network, database produksi, production credential, atau production data.

## Deferred capability

- user-to-tenant membership;
- authorization dan policy;
- persistent tenant repository;
- onboarding, suspension, termination, export, dan restore;
- custom domain;
- POS, sales, payment, inventory, dan catalog;
- deployment dan release.

## Security limitations

Session-backed tenant context hanya menjaga context integrity dasar. Modul ini belum memverifikasi membership atau entitlement. Seluruh tenant-scoped operation harus tetap ditolak sampai Authorization Foundation disetujui dan diimplementasikan.

## Next sprint dependency

Sprint berikutnya harus ditetapkan oleh Product Owner. Authorization Foundation tidak boleh dimulai hanya karena Tenant Context Foundation selesai.

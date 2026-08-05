# Authorization Boundary Foundation

## Scope

Sprint 05 membangun batas authorization yang framework-agnostic, deny-by-default, explicit, dan tenant-bound.

Komponen:

- immutable Authorization Subject;
- canonical Permission Identifier;
- immutable Authorization Context dan Decision;
- Authorization Policy interface;
- deny-by-default policy;
- explicit synthetic grant adapter untuk test;
- tenant-bound evaluation;
- stable error codes dan correlation ID;
- regression test Authentication dan Tenant Context.

## Boundary

Subject hanya memuat authenticated user ID dan active Tenant ID. Permission tidak berasal langsung dari URL, route, client role, atau arbitrary text. Evaluasi selalu membutuhkan authenticated user dan active tenant, serta menolak subject yang berbeda user atau tenant.

Sprint ini tidak membuat final role taxonomy, RBAC/ABAC engine, persistent role/permission storage, superadmin bypass, support access, impersonation, tenant lifecycle, POS, sales, payment, inventory, catalog, atau business schema.

## Deny by default

`DenyByDefaultPolicy` selalu menolak. `ExplicitGrantPolicy` hanya adapter sintetis untuk test dan mengikat grant pada kombinasi user, tenant, dan permission. Grant tidak berpindah ketika active tenant berubah.

## Error codes

- `AUTHORIZATION_AUTHENTICATION_REQUIRED`
- `AUTHORIZATION_TENANT_REQUIRED`
- `AUTHORIZATION_CONTEXT_INVALID`
- `AUTHORIZATION_CROSS_TENANT_DENIED`
- `AUTHORIZATION_PERMISSION_DENIED`

Error publik menggunakan `ErrorEnvelope` dengan correlation ID dan tidak memuat credential, session token, tenant lain, atau implementasi policy internal.

## Testing

```bash
php -l src/Authorization/Foundation.php
php -l tests/run.php
php tests/run.php
```

Test memakai user, tenant, dan permission sintetis; deterministic; tanpa network, database produksi, credential produksi, atau data produksi.

## Deferred capability

Persistent membership/grant repository, final role model, policy administration, user management, audit persistence, business permissions, dan POS tetap ditunda dan memerlukan authority terpisah.

## Security limitation

Fondasi ini belum membuktikan membership atau entitlement dari database. Explicit grant adapter tidak boleh dipakai sebagai production authorization store.

Attribution: Lab | zefry

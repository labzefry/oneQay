# Authentication Foundation

## Scope

Sprint ini membangun fondasi autentikasi sesi saja:

- login dan logout;
- password hashing melalui PHP `PASSWORD_DEFAULT`;
- session ID regeneration;
- session fingerprint validation;
- CSRF token issuance dan validation;
- stable error envelope dengan correlation ID;
- basic deterministic authentication test.

## Boundary

Tidak termasuk:

- tenant context atau tenant isolation implementation;
- role, permission, policy, atau authorization;
- POS, payment, inventory, atau business schema;
- MFA, invitation, password reset, dan account recovery;
- persistent database adapter;
- deployment dan release.

## Design

`AuthenticationService` mengorkestrasi credential verification dan session login/logout. `SessionGuard` menjaga authenticated user state, session regeneration, fingerprint, serta CSRF token. Password hashing dan user/session persistence dipisahkan melalui interface agar framework dan infrastruktur dapat ditambahkan kemudian.

`InMemoryUserProvider` dan `ArraySessionStore` hanya merupakan adapter test dan Technical Preview foundation. Keduanya bukan persistent production implementation.

## Error envelope

Error aman mengikuti struktur:

```json
{
  "error": {
    "code": "AUTH_INVALID_CREDENTIALS",
    "message": "Email atau kata sandi tidak valid.",
    "correlation_id": "corr-123",
    "details": {}
  }
}
```

Credential, password hash, session token, dan detail internal tidak boleh dimasukkan ke error atau log publik.

## Testing

Jalankan:

```bash
php tests/run.php
```

Test mencakup password hash, login berhasil, login gagal, session regeneration, authenticated user restoration, fingerprint mismatch, CSRF token, logout, dan error envelope.

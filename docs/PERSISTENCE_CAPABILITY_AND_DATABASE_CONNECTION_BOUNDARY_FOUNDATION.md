# Persistence Capability and Database Connection Boundary Foundation

## Scope

Sprint 08 menyediakan boundary capability persistence dan koneksi database yang framework-agnostic untuk OneQay Technical Preview. Scope dibatasi pada capability model, validasi konfigurasi koneksi, kebijakan koneksi PDO MySQL yang aman, connector interface, adapter sintetis, adapter PDO MySQL, safe connection result, stable error codes, tests, documentation, dan checkpoint.

Sprint ini tidak membuat schema, migration, repository bisnis, query bisnis, transaction bisnis, seed data, persistent session, persistent configuration, tenant data store, POS, deployment production, atau release.

## Published base

Exact base Sprint 08 adalah published Sprint 07 commit:

`7420539c17be0758c8393f16e6f4232666a2bb2c`

## Verified hosting capability

Capability target cPanel yang telah dibuktikan tanpa credential:

- MariaDB 11.4.8 tersedia;
- koneksi server dilaporkan melalui localhost dan UNIX socket;
- PHP PDO tersedia;
- PHP PDO MySQL tersedia;
- PHP runtime situs 8.3.26 memenuhi requirement minimal 8.2;
- phpMyAdmin dan database management UI tersedia;
- backup UI tersedia;
- akun tidak memiliki SSH.

Capability berikut tetap `UNKNOWN`:

- production database credential;
- database connection aplikasi OneQay;
- database TLS untuk koneksi aplikasi;
- exact socket path yang diperbolehkan untuk aplikasi;
- connection limit per account;
- maximum concurrent database connections;
- database backup retention dan restore RTO/RPO;
- final deployment method.

Credential, password, token, API key, dan production secret tidak diminta serta tidak disimpan.

## Persistence capability model

`PersistenceCapabilityIdentifier` hanya menerima canonical uppercase snake case. Capability minimal:

- `PDO_MYSQL_DRIVER`;
- `MARIADB_SERVER`;
- `DATABASE_CREDENTIALS`;
- `DATABASE_CONNECTION`;
- `DATABASE_TLS`.

`PersistenceCapabilityReport` memisahkan required, available, unavailable, dan unknown capability. Capability yang belum dibuktikan tetap `UNKNOWN`.

Foundation readiness hanya memerlukan capability teknis yang telah dibuktikan untuk membangun boundary, yaitu PDO MySQL dan MariaDB server. Hal tersebut tidak memberikan production database readiness atau deployment authority.

## Database configuration boundary

Configuration keys:

- `DB_DRIVER`;
- `DB_HOST`;
- `DB_PORT`;
- `DB_NAME`;
- `DB_USER`;
- `DB_PASSWORD`;
- `DB_CHARSET`.

Aturan:

- driver hanya `PDO_MYSQL`;
- port harus 1 sampai 65535;
- database name dan username harus mengikuti format terbatas;
- charset wajib `utf8mb4`;
- host tidak menerima URL, path, control character, atau whitespace;
- password wajib menggunakan `SecretValue` dari Configuration Boundary;
- result tidak memuat host, username, password, DSN, socket path, atau raw exception.

Tidak ada value production yang ditulis ke repository.

## PDO MySQL policy

PDO policy menetapkan:

- exception error mode;
- associative fetch mode;
- native prepared statements dengan emulated prepares dinonaktifkan;
- stringify fetches dinonaktifkan;
- persistent PDO connection dinonaktifkan.

Adapter PDO hanya membuka connection ketika dipanggil secara eksplisit oleh integration boundary. Bootstrap publik Sprint 07 tidak diubah dan tidak membuka database.

## Connection boundary

`DatabaseConnector` dan `DatabaseConnection` memisahkan application boundary dari PDO. Synthetic connector digunakan untuk deterministic test tanpa network dan tanpa production database.

`DatabaseConnectionService`:

- menghentikan proses sebelum connector ketika configuration invalid;
- memetakan failure ke stable error codes;
- menggunakan correlation ID;
- hanya mengembalikan safe metadata berupa canonical driver;
- tidak menampilkan raw PDO exception;
- tidak menjalankan schema, migration, atau business query.

## Stable error codes

- `PERSISTENCE_CONFIGURATION_INVALID`
- `PERSISTENCE_DRIVER_UNSUPPORTED`
- `PERSISTENCE_CAPABILITY_UNKNOWN`
- `PERSISTENCE_CAPABILITY_UNAVAILABLE`
- `PERSISTENCE_CONNECTION_FAILED`
- `PERSISTENCE_CONNECTION_UNAVAILABLE`
- `PERSISTENCE_NOT_READY`

## Testing

Jalankan:

```bash
php -l src/Persistence/Foundation.php
php -l tests/persistence.php
php tests/run.php
php tests/runtime.php
php tests/persistence.php
```

Bounded persistence test menggunakan synthetic configuration, password, capability provider, dan database connector. Test tidak menggunakan network, cPanel credential, production credential, production data, atau production database.

Local bounded candidate result:

```text
Persistence Capability and Database Connection Boundary tests passed: 36 assertions.
```

Tests mencakup capability `UNKNOWN`, immutable report, configuration validation, secret leakage, safe connection result, non-persistent PDO policy, connector failure mapping, serta explicit no-schema/no-business behavior.

## Security limitations

- PDO adapter belum digunakan terhadap target production.
- Production credential dan connection tetap unverified.
- Localhost atau UNIX socket evidence tidak boleh diperlakukan sebagai permission untuk menggunakan arbitrary filesystem path.
- Database account privilege, least privilege, connection limit, TLS requirement, backup retention, restore verification, dan rotation masih deferred.
- Foundation ini tidak menggantikan database firewall, account isolation, secret manager, monitoring, backup, atau incident response.

## Deferred

- schema dan migration;
- tenant persistence model;
- repository dan unit of work;
- transaction boundary;
- idempotency persistence;
- audit persistence;
- backup and restore implementation;
- cache, queue, scheduler, mail, dan storage;
- POS dan semua business modules;
- deployment dan release.

## Next dependency

Sprint berikutnya memerlukan publication Sprint 08, exact-head regression, capability review, dan Product Owner authority terpisah. Tidak ada kewenangan membuat schema atau business persistence dari Sprint 08.

Attribution: Lab | zefry

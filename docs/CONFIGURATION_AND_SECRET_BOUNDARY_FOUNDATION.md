# Configuration and Secret Boundary Foundation

## Scope

Sprint 06 menyediakan boundary konfigurasi dan secret yang framework-agnostic untuk OneQay Technical Preview. Foundation ini hanya menangani pembacaan konfigurasi trusted, canonical key, klasifikasi environment, proteksi secret, validasi startup, stable error code, dan test sintetis.

Foundation ini tidak membuat persistence, database connection, schema, migration, queue, scheduler, cache, storage, mail, cPanel integration, deployment, POS, atau business module.

## Environment classification

Environment yang diizinkan:

- `local`;
- `test`;
- `preview`;
- `production`.

`EnvironmentIdentifier` bersifat immutable dan canonical. Nilainya tidak ditentukan dari hostname, domain, request, cookie, tenant context, atau input client. Environment selain daftar tersebut ditolak dengan `CONFIGURATION_INVALID`.

`preview` dan `production` dikategorikan restricted environment. Restricted environment menolak debug aktif dan session yang tidak secure.

## Configuration key

`ConfigurationKey`:

- immutable;
- canonical ke uppercase snake case;
- tidak menerima whitespace;
- tidak menerima dash, path, atau arbitrary structure;
- tidak menyimpan value;
- tidak membocorkan secret.

Contoh synthetic key:

- `APP_ENV`;
- `APP_DEBUG`;
- `SESSION_SECURE`;
- `APP_KEY`.

## Configuration source

`ConfigurationSource` menyediakan operasi:

- required string;
- optional string dengan explicit default;
- required boolean;
- optional boolean dengan explicit default;
- secret.

Adapter yang tersedia:

- `ArrayConfigurationSource`, hanya untuk deterministic test;
- `EnvironmentVariableConfigurationSource`, membaca trusted process environment melalui `getenv`.

Adapter tidak membaca query string, cookie, request body, tenant input, atau domain. Adapter juga tidak menulis kembali environment.

## Missing, empty, and invalid values

Boundary membedakan:

- missing required value: `CONFIGURATION_REQUIRED`;
- empty required value: `CONFIGURATION_EMPTY`;
- invalid format atau boolean: `CONFIGURATION_INVALID`;
- missing atau empty secret: `CONFIGURATION_SECRET_REQUIRED`.

Boolean canonical yang diterima:

- true: `1`, `true`, `yes`, `on`;
- false: `0`, `false`, `no`, `off`.

## Secret protection

`SecretValue`:

- menyimpan secret secara private;
- hanya membuka raw value melalui method `reveal()` yang eksplisit;
- mengembalikan `[REDACTED]` untuk string conversion;
- mengembalikan `[REDACTED]` untuk debug output;
- mengembalikan `[REDACTED]` untuk JSON dan PHP serialization;
- tidak memasukkan raw secret ke exception atau validation result.

Foundation tidak menganggap wrapper ini sebagai external secret manager. Rotation, vault integration, encryption at rest, dan managed secret service tetap deferred.

## Startup validation

`StartupConfigurationValidator` memvalidasi:

- `APP_ENV`;
- `APP_DEBUG`;
- `SESSION_SECURE`;
- `APP_KEY`.

`preview` dan `production` ditolak bila:

- debug aktif;
- session tidak secure;
- required secret tidak tersedia;
- environment tidak valid.

Hasil validasi hanya memuat status dan stable error codes. Hasil tidak memuat raw environment dump, credential, atau secret.

## Stable error codes

- `CONFIGURATION_REQUIRED`;
- `CONFIGURATION_EMPTY`;
- `CONFIGURATION_INVALID`;
- `CONFIGURATION_SECRET_REQUIRED`;
- `CONFIGURATION_ENVIRONMENT_UNSAFE`.

Ketika dipetakan ke HTTP error, gunakan `ErrorEnvelope` dengan correlation ID dan safe message.

## Testing

Jalankan:

```bash
php -l src/Auth/Foundation.php
php -l src/Tenant/Foundation.php
php -l src/Authorization/Foundation.php
php -l src/Configuration/Foundation.php
php -l src/Http/ErrorEnvelope.php
php -l tests/run.php
php tests/run.php
```

Expected result pada final content candidate:

```text
Authentication, Tenant Context, Authorization, and Configuration Boundary tests passed: 49 assertions.
```

Test menggunakan synthetic user, tenant, permission, configuration, dan secret. Test tidak menggunakan network, production credential, production data, atau production database.

## Explicit boundaries

Foundation ini bukan:

- persistent configuration store;
- database configuration;
- external secret manager;
- server provisioning;
- deployment system;
- cPanel integration;
- authorization policy administration;
- POS atau business module.

## Security limitations

- Environment variable tetap bergantung pada keamanan process host.
- `reveal()` harus digunakan hanya pada integration boundary yang membutuhkan raw secret.
- Secret wrapper tidak menggantikan rotation, access control, encryption at rest, atau managed vault.
- Startup validation belum melakukan deployment atau infrastructure verification.
- Production readiness memerlukan verification terpisah terhadap runtime, hosting, backup, rollback, logging, dan secret management.

## cPanel dependency status

Spesifikasi dan credential cPanel tidak diperlukan untuk Sprint 06. Informasi capability hosting mulai diperlukan sebelum keputusan mengenai:

- platform runtime integration;
- persistent database integration;
- queue dan scheduler;
- cache;
- storage;
- mail;
- backup dan restore;
- deployment;
- rollback.

Jangan menyimpan credential, password, API key, access token, atau secret hosting di repository.

## Deferred capability

- application runtime bootstrap;
- persistent configuration;
- database and migration foundation;
- external secret management;
- cache, queue, scheduler, storage, and mail integration;
- deployment and rollback;
- POS and all business modules.

## Next dependency

Sprint berikutnya memerlukan Product Owner authority terpisah. Sebelum platform runtime integration dimulai, capability hosting harus diverifikasi tanpa meminta atau menyimpan credential rahasia.

Attribution: Lab | zefry

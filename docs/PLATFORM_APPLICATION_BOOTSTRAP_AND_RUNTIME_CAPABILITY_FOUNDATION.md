# Platform Application Bootstrap and Runtime Capability Foundation

## Scope

Sprint 07 menyediakan Application Bootstrap framework-agnostic, immutable Bootstrap Result, Runtime Capability Identifier dan Report, validasi PHP serta extension, document-root safety, minimal public-entry boundary, health, readiness, correlation ID, stable runtime errors, synthetic tests, dan checkpoint.

Tidak ada persistence, database connection, schema, migration, POS, business API, deployment production, queue, scheduler integration, mail integration, atau storage integration.

## Hosting capability evidence

Capability target cPanel yang telah dibuktikan tanpa credential:

- runtime situs PHP 8.3.26;
- Apache 2.4.63 pada Linux x86_64;
- JSON, OpenSSL, Mbstring, PDO, PDO MySQL, Filter, Session, dan Ctype tersedia;
- MariaDB 11.4.8 tersedia, tetapi tidak digunakan Sprint 07;
- Cron Jobs, backup, Errors, Raw Access, Metrics, email tools, SSL/TLS, File Manager, Git Version Control UI, MultiPHP Manager, dan Select PHP Version tersedia pada cPanel;
- memory limit 512M;
- max execution time 300 detik;
- upload max filesize 32M;
- post max size 32M;
- akun tidak memiliki SSH.

Capability berikut tetap `UNKNOWN` karena belum dibuktikan pada target `oneqay.n07.my.id`:

- Composer executable;
- URL rewrite efektif;
- custom document root tepat ke folder `public`;
- minimum cron interval;
- background worker atau long-running process;
- Redis atau managed cache service;
- symlink policy;
- metode deployment final.

Tidak ada capability `UNKNOWN` yang diasumsikan `AVAILABLE`.

## Runtime model

`RuntimeCapabilityIdentifier` hanya menerima canonical uppercase snake case dan tidak menyimpan hostname, tenant, credential, environment value, atau secret. `RuntimeCapabilityReport` memisahkan required, available, unavailable, dan unknown capability.

PHP minimum adalah 8.2. Required extensions: JSON, OpenSSL, Mbstring, PDO, PDO MySQL, Filter, Session, dan Ctype. Unit test menggunakan synthetic provider tanpa network, production credential, production data, atau production database.

## Document-root safety

Target aman adalah web document root yang menunjuk tepat ke folder `public`. Public entry membandingkan resolved document root dengan folder `public`. Root yang mengekspos source, tests, docs internal, environment file, Composer metadata, log, backup, secret, atau private configuration tidak diterima.

Jika cPanel tidak dapat mengarahkan document root ke `public`, tidak ada unsafe fallback. Status tetap capability gap sampai safe layout dibuktikan.

## Public entry, health, dan readiness

`public/index.php` menjalankan Configuration Boundary dan Runtime Capability validation, membuat correlation ID, tidak menentukan tenant, tidak melakukan authorization, tidak membuka database, serta tidak menampilkan stack trace, raw environment, phpinfo, path internal, atau secret.

`/health` hanya melaporkan process-level health. Readiness hanya berhasil bila configuration valid, required runtime capability tersedia, document root aman, dan bootstrap berhasil.

## Stable error codes

- `RUNTIME_PHP_VERSION_UNSUPPORTED`
- `RUNTIME_EXTENSION_REQUIRED`
- `RUNTIME_CAPABILITY_UNKNOWN`
- `RUNTIME_DOCUMENT_ROOT_UNSAFE`
- `RUNTIME_REWRITE_UNAVAILABLE`
- `RUNTIME_BOOTSTRAP_FAILED`
- `RUNTIME_CONFIGURATION_INVALID`
- `RUNTIME_NOT_READY`

## Commands

```bash
php -l src/Runtime/Foundation.php
php -l public/index.php
php -l tests/runtime.php
php tests/run.php
php tests/runtime.php
```

## cPanel compatibility

Architecture kompatibel secara bersyarat dengan cPanel shared hosting tanpa SSH karena runtime tidak membutuhkan daemon atau database. GO runtime tetap bergantung pada pembuktian document root target ke `public`, configuration environment aman, dan hasil test pada exact head.

Halaman `phpinfo()` yang dipakai untuk capability verification harus dihapus atau dibatasi akses karena menampilkan path internal, cookie, request variables, dan konfigurasi server.

## Deferred

Persistence, database foundation, Redis, queue, scheduler integration, background worker, mail, storage, deployment, rollback, POS, dan seluruh business behavior tetap deferred.

Attribution: Lab | zefry

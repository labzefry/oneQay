# oneQay Coding Standards

## Scope

Standar ini berlaku lintas backend, frontend, PWA, Android, API, automation, installer, updater, migration, test, dan infrastructure configuration. Aturan spesifik bahasa ditambahkan setelah ADR stack disetujui.

## Core rules

- Utamakan clarity, correctness, security, maintainability, dan testability.
- Satu sumber business rule; hindari duplicate logic.
- Dependency mengarah ke domain/application.
- Komposisi lebih disukai daripada inheritance kompleks.
- Public contract kecil, eksplisit, terversi, dan terdokumentasi.
- Mutable global state, hidden side effect, dan implicit tenant context dilarang.

## Naming

- Gunakan ubiquitous language dari domain.
- Nama menjelaskan intent, bukan mekanisme sementara.
- Hindari singkatan yang tidak standar.
- Boolean menggunakan bentuk positif seperti `isActive` atau `canRefund`.
- Unit dan currency disertakan bila raw number dapat ambigu.
- Identifier tenant tidak boleh memakai domain/subdomain sebagai primary identity.

## Structure

Setiap module memisahkan domain, application, interface, infrastructure, dan tests. Framework bootstrap tidak boleh menjadi tempat business logic. Shared code hanya untuk konsep stabil lintas module; folder `helpers` generik harus dihindari.

## Domain modeling

- Entity menjaga invariant.
- Value object immutable untuk money, quantity, identifier, date range, dan konsep nilai lain.
- Application service mengorkestrasi use case; domain service hanya untuk rule yang tidak cocok pada entity/value object.
- Repository interface berada di application/domain boundary; implementasi berada di infrastructure.
- Event menggunakan past tense dan schema version.

## Functions and classes

- Satu tanggung jawab yang koheren.
- Dependency eksplisit melalui constructor atau parameter.
- Hindari parameter boolean yang mengubah perilaku; gunakan command/strategy bernama.
- Batasi nesting dengan guard clause.
- Error tidak boleh ditelan; map menjadi domain/application error yang aman.
- Komentar menjelaskan why, constraint, atau trade-off; jangan mengulang code.

## Types and validation

- Gunakan strict typing bila didukung.
- Validasi input pada trust boundary dan invariant pada domain boundary.
- Jangan gunakan floating point untuk money.
- Date/time disimpan sebagai instant standar; presentation mengikuti timezone tenant.
- Enum/constant memiliki unknown handling untuk compatibility.
- Serialization contract tidak menggunakan entity persistence secara langsung.

## Multi-tenant coding rules

- Tenant context adalah required dependency untuk operasi tenant-scoped.
- Repository tenant-scoped tidak menerima query tanpa tenant identifier.
- Cache, lock, file path, event, job, metric, dan log dimension harus tenant-aware.
- Platform query lintas tenant menggunakan interface terpisah dan privileged authorization.
- Test wajib mencoba cross-tenant identifier tampering.

## Security rules

- Gunakan parameterized query/ORM safe API.
- Encode output sesuai context dan sanitasi rich content.
- Terapkan CSRF untuk state-changing browser request.
- Password hanya melalui modern password hashing abstraction.
- Secret tidak boleh berada dalam code, fixture, screenshot, log, atau exception.
- Upload divalidasi berdasarkan content, size, extension allowlist, scanning, dan storage isolation.
- Redirect, URL fetch, archive extraction, dan template rendering memiliki allowlist/control khusus.

## API implementation

- Handler tipis: parse, authenticate, authorize, validate, call use case, serialize.
- Error response mengikuti API_SPEC.md.
- Idempotency key digunakan pada payment, order creation, dan operasi retry-prone.
- Pagination dan filtering memiliki bounded limits.
- Tidak mengembalikan internal identifier atau field sensitif tanpa kebutuhan contract.

## Database implementation

- Semua schema change melalui migration versioned.
- Migration production menghindari long lock dan destructive one-step change.
- Query baru memiliki review index dan execution plan bila kritis.
- Transaction boundary ditetapkan pada use case.
- Test tidak bergantung pada urutan atau shared mutable fixture.

## Frontend/PWA

- UI state tidak menjadi source of truth untuk authorization.
- Komponen reusable menggunakan design token dan semantic accessibility.
- Async state memiliki loading, empty, success, partial, stale, dan error handling.
- Offline behavior harus eksplisit; konflik tidak boleh diselesaikan diam-diam.
- Bundle, image, font, dan third-party script mengikuti performance/privacy budget.

## Android

- Domain/use case dipisahkan dari Android framework.
- Keystore, secure storage, certificate/network policy, dan lifecycle ditangani sesuai threat model.
- Sensitive screen dan deep link divalidasi.
- Background sync idempotent dan tenant/account aware.

## Error handling and logging

- Gunakan stable error code dan correlation ID.
- User message aman dan dapat ditindaklanjuti; internal detail hanya pada protected logs.
- PII, payment data, credential, token, raw request body, dan cross-tenant data tidak dicatat.
- Retry dibatasi dan menggunakan backoff/jitter untuk transient failure.

## Dependency standards

Dependency baru harus memiliki justification, license, active maintenance, security posture, pinned version, owner, dan removal strategy. Hindari dependency untuk fungsi kecil yang aman dibuat melalui standard library.

## Testability

- Clock, random, ID generator, external service, dan filesystem diabstraksikan pada boundary.
- Test nama menjelaskan scenario dan expected behavior.
- Unit test deterministic, cepat, dan tidak memakai jaringan.
- Integration test menggunakan boundary nyata yang relevan.

## Formatting and static analysis

Formatter, linter, type checker, dependency rule, secret scan, dan security analyzer ditetapkan per stack serta dijalankan konsisten secara lokal dan CI. Warning baru diperlakukan sebagai failure kecuali baseline exception terdokumentasi.

## Review checklist

- Business rule dan scope benar.
- Tidak ada duplicate logic atau boundary violation.
- Tenant isolation dan authorization eksplisit.
- Error, retry, idempotency, audit, dan observability tepat.
- Migration/API compatibility aman.
- Tests mencakup happy, boundary, failure, dan abuse cases.
- Dokumentasi, tasks, dan changelog diperbarui.

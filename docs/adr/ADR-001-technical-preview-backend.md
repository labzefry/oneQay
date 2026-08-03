# ADR-001: Technical Preview Backend

- Status: Proposed
- Date: 2026-08-03
- Decision owner: Product Owner OneQay
- Evidence: Issue #23
- Scope: Technical Preview v0.0.1 only

## Context

OneQay membutuhkan backend modular-monolith yang dapat disiapkan cepat untuk sandbox, tetap memiliki batas domain yang jelas, dan dapat dipindahkan dari shared hosting menuju lingkungan yang lebih terkontrol tanpa mengubah business logic.

Product Owner memilih opsi B1: Laravel/PHP. Versi PHP, versi framework, extension, queue runtime, dan support matrix belum dapat ditetapkan sebelum capability assessment hosting lengkap.

## Proposed decision

Gunakan Laravel/PHP sebagai delivery framework untuk modular monolith. Domain dan application layer tidak boleh bergantung langsung pada HTTP, ORM, queue driver, filesystem, atau provider eksternal. Framework menjadi composition dan delivery mechanism, bukan pemilik business rules.

## Guardrails

- Modul memiliki boundary, ownership, contract, dan migration yang jelas.
- Tenant context divalidasi sebelum memasuki application use case.
- Query tenant menggunakan enforcement terpusat dan negative isolation tests.
- Operasi finansial dan retry-prone memiliki idempotency boundary.
- Long-running work tidak diasumsikan tersedia sampai worker capability terbukti.
- Framework/version hanya dapat dipin melalui dependency review dan Accepted ADR.

## Alternatives considered

- B2 Symfony/PHP: kontrol komponen kuat, tetapi delivery risk T+5 lebih tinggi.
- B3 NestJS/Node.js: TypeScript dan realtime tooling kuat, tetapi persistent runtime pada Stage 1 belum terbukti.

## Consequences

Keputusan ini mengoptimalkan cPanel compatibility dan time-to-preview. Risiko utamanya adalah framework coupling dan fitur background yang tidak berjalan bila hosting tidak mendukung worker; keduanya harus ditangani melalui architecture tests dan deployment capability gate.

## Acceptance conditions

- Hosting PHP/extensions dan process model terverifikasi.
- Dependency, license, security, dan maintenance review selesai.
- Architecture boundary test plan tersedia.
- Product Owner menyetujui exact head ADR.

Keputusan ini belum mengotorisasi source code.

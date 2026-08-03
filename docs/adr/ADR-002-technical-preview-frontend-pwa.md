# ADR-002: Technical Preview Frontend and PWA

- Status: Proposed
- Date: 2026-08-03
- Decision owner: Product Owner OneQay
- Evidence: Issue #23
- Scope: Technical Preview v0.0.1 only

## Context

Technical Preview membutuhkan antarmuka modern untuk satu vertical slice tanpa menambah deployment unit yang tidak diperlukan. Offline transaction semantics dan mobile/public API belum menjadi scope preview.

## Proposed decision

Gunakan Vue 3, Inertia, dan Vite untuk frontend preview dalam deployment unit yang sama dengan backend. PWA pada tahap ini hanya boleh menyediakan installability dan static asset caching yang aman; transaksi offline, queued mutation, dan conflict resolution tetap ditunda.

## Guardrails

- Domain rules tidak ditempatkan di browser.
- Authorization selalu ditegakkan server-side.
- API contract tetap eksplisit pada boundary yang akan digunakan konsumen eksternal.
- Cache key dan client state wajib tenant-aware dan dibersihkan saat tenant/session berubah.
- Tidak ada sensitive data dalam service-worker cache.
- Accessibility, locale, currency, timezone, dan responsive behavior diuji pada critical path.

## Alternatives considered

- F2 React/Next.js: pemisahan client/API kuat, tetapi menambah Node runtime dan deployment complexity.
- F3 Blade/Livewire/Alpine: operasi cPanel paling sederhana, tetapi jalur PWA dan rich interaction lebih terbatas.

## Consequences

Satu deployment unit mempercepat preview. Konsekuensinya, boundary antara server-rendered navigation dan future external API harus didokumentasikan agar tidak menjadi coupling permanen.

## Acceptance conditions

- Build tool tersedia pada build environment tanpa mewajibkan Node runtime di production.
- Cache, session transition, dan tenant-switch threat cases memiliki test plan.
- Browser support matrix dan accessibility baseline ditetapkan.
- Product Owner menyetujui exact head ADR.

Keputusan ini belum mengotorisasi source code.

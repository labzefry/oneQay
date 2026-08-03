# ADR-006: Technical Preview Offline POS Boundary

- Status: Proposed
- Date: 2026-08-03
- Decision owner: Product Owner OneQay
- Evidence: Issue #23, boundary OFF-1
- Scope: Technical Preview v0.0.1 only

## Context

Offline POS membutuhkan identity, local storage, conflict, stock, money, sequence, security, and reconciliation semantics yang belum diselesaikan. Product Owner memilih online-only untuk preview.

## Proposed decision

Technical Preview tidak menerima mutation ketika koneksi tidak tersedia. PWA dapat menyimpan static assets dan menampilkan offline status, tetapi tidak menyimpan sensitive transactional data, membuat sale offline, mengurangi stok offline, atau melakukan background replay.

## Guardrails

- UI gagal secara jelas dan tidak memberi kesan transaksi berhasil.
- Tidak ada service-worker caching untuk authenticated mutation responses.
- Retry setelah reconnect menggunakan explicit user action atau safe idempotent flow.
- Offline queue, conflict resolution, device trust, sequence allocation, and reconciliation memerlukan ADR terpisah.

## Alternatives considered

- Local-first transaction queue: terlalu berisiko sebelum stock/payment conflict semantics selesai.
- Read-only cached catalog: dapat dievaluasi kemudian setelah data classification dan cache threat review.

## Consequences

Online-only membatasi demo lapangan tetapi mengurangi risiko double sale, double stock movement, stale price, dan credential leakage.

## Acceptance conditions

- Offline UX dan reconnect tests tersedia.
- Tidak ada transactional mutation yang berhasil tanpa server acknowledgement.
- Product Owner menyetujui exact head ADR.

Keputusan ini belum mengotorisasi source code.

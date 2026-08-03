# ADR-005: Technical Preview Payment Boundary

- Status: Proposed
- Date: 2026-08-03
- Decision owner: Product Owner OneQay
- Evidence: Issue #23, boundary PAY-1
- Scope: Technical Preview v0.0.1 only

## Context

Technical Preview harus mendemonstrasikan cash-sale happy path tanpa memproses uang nyata atau memilih payment provider.

## Proposed decision

Preview menggunakan synthetic cash-only tender. Tidak ada provider API, callback, authorization, capture, settlement, refund, chargeback, reconciliation, QR code pembayaran, production credential, atau real-money processing.

Nilai uang menggunakan integer minor units dan satu currency per sale. Completion hanya dapat mengikuti sale-level payment-sufficiency fact; boundary ini tidak mengubah seluruh payment hypothesis pada Domain Event Storming dari Proposed.

## Guardrails

- Synthetic marker wajib terlihat pada environment dan data.
- Idempotency key wajib dirancang untuk retry-prone sale completion.
- Cancellation, late payment, uncertain payment, split tender, partial payment, dan provider evidence berada di luar preview.
- Receipt diberi label demonstrasi dan bukan bukti fiskal.
- Provider selection membutuhkan ADR baru, threat model, compliance review, contract test, and reconciliation design.

## Alternatives considered

- Provider sandbox: ditunda karena menambah external dependency dan dapat mengaburkan boundary preview.
- Full payment abstraction: ditunda sampai payment semantics dan provider requirements disetujui.

## Acceptance conditions

- Tidak ada network call atau credential payment.
- Money, idempotency, cancellation, and audit tests untuk cash happy path tersedia.
- Product Owner menyetujui exact head ADR.

Keputusan ini belum mengotorisasi source code.

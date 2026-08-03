# ADR-004: Technical Preview Authentication, MFA, and Session

- Status: Proposed
- Date: 2026-08-03
- Decision owner: Product Owner OneQay
- Evidence: Issue #23
- Scope: Technical Preview v0.0.1 only

## Context

Product Owner memilih opsi A1: first-party session. Technical Preview membutuhkan revocable session dan privileged-role protection tanpa ketergantungan identity provider eksternal.

## Proposed decision

Gunakan secure server-side session dengan cookie `Secure`, `HttpOnly`, dan appropriate `SameSite`; CSRF protection; password hashing yang mengikuti supported runtime; session rotation; login throttling; serta TOTP MFA untuk privileged roles. Recovery code disimpan hashed, sekali pakai, dapat direvoke, dan seluruh recovery event diaudit.

## Guardrails

- Tenant selection bukan authentication dan tidak menambah authorization.
- Authorization deny-by-default pada setiap use case.
- Session memiliki idle dan absolute expiry.
- Password reset, MFA recovery, support access, dan session revocation tetap explicit high-risk flows.
- Secret, raw recovery code, password, dan session token tidak masuk log atau issue.
- JRN-003 tetap unresolved blocker untuk final recovery semantics.

## Alternatives considered

- A2 external OIDC: federation kuat, tetapi provider, cost, outage, callback, dan compliance belum diputuskan.
- A3 browser-held JWT: revocation dan refresh-token risk tidak sebanding untuk same-origin preview.

## Consequences

First-party session menyederhanakan preview dan revocation. OneQay tetap memerlukan formal permission model, support-access controls, session inventory, recovery abuse tests, dan future API authentication ADR.

## Acceptance conditions

- Privileged-role list dan authorization matrix direview.
- Session, MFA, recovery, rate-limit, dan audit test plan tersedia.
- JRN-003 tidak dinyatakan selesai.
- Product Owner menyetujui exact head ADR.

Keputusan ini belum mengotorisasi source code.

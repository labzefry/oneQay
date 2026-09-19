# Changelog

## 2026-09-19 — Sprint194 closed canonically

**Sprint194: Guarded Technical Preview Activation Request**

- Added a private Technical Preview activation request after valid installation completion.
- Bound the request to exact release ID, active runtime environment SHA-256, and installation-completion SHA-256.
- Added deterministic request identity, audit creation time, private 0600 evidence, atomic write semantics, exact replay idempotency, and fail-closed tamper handling.
- Added explicit operator confirmation `REQUEST_TECHNICAL_PREVIEW_ACTIVATION`.
- Installer reports `PENDING APPROVAL / NOT AUTHORIZED` and does not activate the application.
- Governed M7.5 package includes activation-request source/schema and request metadata.
- Final engineering head `af1027156209977a75d24f54fec031f86bedf8f6`: 82/82 SUCCESS.
- Engineering PR #819 squash merged at `7e3e58d7be012ee5d797acb879cfb9f9a1e829dc`.
- Dedicated Sprint194 run `35423991024`: SUCCESS.
- Exact-head M7.5 run `35423990554`: SUCCESS.
- Canonical main-push M7.5 run `35424129329`: SUCCESS.
- Post-merge shared-runtime `35424129284`, cPanel `35424129292`, and Sprint155 `35424129446`: SUCCESS.
- Engineering path hash: `a85b8ceffbf533ea5f3aff7555dba90129c2c851bb8d9bb8ed427ce2e572f804`.
- Reconciliation path hash: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO unchanged.
- Next position: Sprint195 bounded discovery.

Author by Lab | zefry

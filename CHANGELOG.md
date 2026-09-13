# Changelog

This changelog records material canonical progress. Detailed provenance remains in merged pull requests, Git history, per-sprint documents, workflows, and machine-readable contracts.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

## 2026-09-13 — Sprint147 closed

**Sprint147: canonical throttle rejection metadata identity response hardening**

- Purpose: close the remaining response-side ownership ambiguity after Sprint146 exact throttle-budget identity.
- Objective: require canonical framework throttle-rejection metadata before Final Shift Close `429` privacy/security hardening.
- Required metadata: exact `X-RateLimit-Remaining: 0`, non-empty decimal `Retry-After`, and non-empty decimal `X-RateLimit-Reset`.
- Missing, nonzero/numeric-alias remaining, empty, or non-digit retry/reset metadata remains framework-owned.
- Sprint144 named-route regression fixture was made successor-compatible with the canonical metadata shape proved by Sprint142.
- Engineering PR #711 squash merged.
- Canonical engineering commit: `50a3ba99b8f5628381d9df63f4f6a0e1020d550a`.
- Final authorized head: `513d95dbb4d5ab95a8f6c3282f8911cf339a9697`.
- Exact-head qualification: 31/31 successful.
- Product Owner authority run `34752002084` successful.
- Engineering envelope: six paths; SHA-256 `ffd176da808eda16fccdf0375fcae2fd5bcc1cfc4b931aca6492ca31eb9b1d40`.
- Operational NO-GO remained unchanged.
- Next position: Sprint148 bounded discovery; no objective preselected.

## 2026-09-13 — Sprint146 closed

- PR #709 squash merged at `a5e4aec8c142e7478a0e58d2d732dbf106393b06`.
- Added exact canonical per-route throttle-budget identity to response ownership.
- 30/30 exact-head CI; Product Owner authority run `34750648988` successful.

## 2026-09-13 — Sprint145 closed

- PR #707 squash merged at `6d4fc06ac1166d15d8598d2a6d39d594f2493767`.
- Added canonical controller-action identity to throttle-response ownership.

## Earlier material engineering history

Sprint142 hardened authenticated throttle rejections, Sprint143 added DB HEAD parity, Sprint144 added named-route identity, and Sprint130–Sprint141 established the canonical control-plane token, delivery, authenticated HTTP, throttle, and rejection-hardening chain. Earlier work established architecture/governance, bounded POS foundations, JRN-010 cash/variance evidence, and Final Shift Close source/readiness controls.

## Current lifecycle boundary

Operational machine-readable authority remains in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`. Migration #27 remains `NOT_EXECUTED`, permissions `NONE`, feature `INACTIVE`, deployment `NOT_GRANTED`, preview/production `NOT_AUTHORIZED`, updater `INACTIVE`, durable target blocked, selected target `null`.

Author by Lab | zefry

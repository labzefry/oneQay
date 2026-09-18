# Changelog

## 2026-09-19 — Sprint185 closed canonically

**Sprint185: Preboot Database Compatibility Verification**

- Objective: `PREBOOT_DATABASE_COMPATIBILITY_VERIFICATION`.
- Added live read-only database compatibility verification before pending runtime configuration is accepted.
- Added MySQL/MariaDB connection and server-version verification.
- Added `utf8mb4`, UTC, schema-state, and database-scoped least-privilege verification.
- Foreign/incompatible schema and unsafe/global database privileges fail closed.
- Failed verification does not create `.env.pending` and preserves an unexpired one-time installation authority for correction/retry.
- Successful verification binds safe database facts into pending configuration and exposes `PENDING_CONFIGURATION_VERIFIED`.
- Active `.env` is not created; persistence, Technical Preview, Production, and updater activation remain disabled.
- Packaged the pre-boot verifier into the deterministic governed M7.5 release artifact.
- Added exact-envelope historical compatibility for M7.5 and Sprint32/Sprint33/Sprint34 without modifying application migration, authentication, or recovery semantics.
- Final engineering head `bf17397f739eab4aae531ed6b6a1b0b5430ae98e` completed 71/71 pull-request workflows successfully.
- Product Owner exact-head merge authority succeeded.
- PR #799 squash merged at `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`; this is the canonical Sprint185 engineering evidence.
- Canonical main-push M7.5 run `35382800589` completed successfully.
- Final engineering envelope: 10 paths; SHA-256 `775caa7723278af855b888f2c6bac592d9187e7b988619799f90e2e7a6950e99`.
- Canonical reconciliation envelope: 8 paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO remains unchanged.
- Next position: Sprint186 bounded discovery from canonical post-Sprint185.

## Recent material progression

- **Sprint185:** verified pending database compatibility before configuration commit; engineering squash `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`.
- **Sprint184:** secure pre-boot pending runtime configuration preparation; engineering squash `dfb65d2782a580108d8ccd9a6f9203720fa036b7`.
- **Sprint183:** canonical governed M7.5 release automation restored; engineering squash `cd3facf81e3b1734353656da3ba5800607900fcb`.
- **Sprint182:** governed release artifact → installer-readiness bridge; engineering squash `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`.
- **Sprint181:** operator-visible installation readiness wizard; engineering squash `deb999fcd0694ba85c132d1b490bd90c0dc86309`.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

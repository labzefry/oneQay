# Changelog

## 2026-09-19 — Sprint184 closed canonically

**Sprint184: Preboot Installation Configuration Preparation**

- Objective: `PREBOOT_INSTALLATION_CONFIGURATION_PREPARATION`.
- Added governed pre-boot installation configuration preparation before Laravel runtime boot.
- Added exact-release binding and private expiring one-time installation authority.
- Added SHA-256 token verification and fail-closed replay prevention.
- Added bounded HTTPS application URL and MySQL-compatible runtime configuration validation.
- Added fresh application key generation.
- Added atomic private `.env.pending` preparation; active `.env` is not created.
- Packaged operator-facing `install.php` and private installation implementation into the deterministic M7.5 release artifact.
- Prepared configuration keeps Technical Preview, persistence, and update-control activation disabled.
- Added exact-envelope historical compatibility for M7.5 and Sprint32/Sprint33/Sprint34 without changing application authentication/recovery or migration source semantics.
- Final engineering head `a22d98f18618be3ccf5ff8274ebf5528b33f01d7` completed 72/72 pull-request workflows successfully.
- Product Owner exact-head merge authority succeeded.
- PR #797 squash merged at `dfb65d2782a580108d8ccd9a6f9203720fa036b7`; this is the canonical Sprint184 engineering evidence.
- Canonical main-push M7.5 run `35378584615` completed successfully.
- Final engineering envelope: 9 paths; SHA-256 `e2537851052c57b1ec3b7d2e99e1b5d0208fb3c54da207d5280682eb85c686a0`.
- Canonical reconciliation envelope: 8 paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO remains unchanged.
- Next position: Sprint185 bounded discovery from canonical post-Sprint184.

## Recent material progression

- **Sprint184:** secure pre-boot pending runtime configuration preparation; engineering squash `dfb65d2782a580108d8ccd9a6f9203720fa036b7`.
- **Sprint183:** canonical governed M7.5 release automation restored; engineering squash `cd3facf81e3b1734353656da3ba5800607900fcb`.
- **Sprint182:** governed release artifact → installer-readiness bridge; engineering squash `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`.
- **Sprint181:** operator-visible installation readiness wizard; engineering squash `deb999fcd0694ba85c132d1b490bd90c0dc86309`.
- **Sprint180:** server-assisted initial merchant sign-in; engineering squash `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

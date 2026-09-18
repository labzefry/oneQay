# Changelog

## 2026-09-18 — Sprint182 closed canonically

**Sprint182: Governed Release Installation Preflight Bridge Foundation**

- Objective: `GOVERNED_RELEASE_INSTALLATION_PREFLIGHT_BRIDGE_FOUNDATION`.
- Added a shared trusted-build installer-manifest builder.
- Bound installer evidence to exact source SHA, release ID, artifact filename, size, and SHA-256.
- Preserved the canonical M7.5 archive builder and Release Manifest v1 contract.
- Validated installer-facing policy through `SecureInstallationReadiness`.
- Proved digest tampering fails closed.
- Proved deterministic M7.5 artifact and installer-manifest reproduction.
- Extended historical Sprint32/Sprint33/Sprint34 M7.5 migration isolation only through canonical migration #27.
- Dedicated Sprint182 exact-head workflow completed the real artifact → sidecar → validation path successfully.
- Final engineering exact head `cc6eba43ccd9dfd316f652aaf133a7372a86380b` completed 68/68 pull-request workflows successfully.
- Engineering envelope: 6 paths; SHA-256 `4968d9fcf35b7a17d67b1909cfd1dad93f2b08b7d82b8c6d30be4dc09f067be3`.
- Engineering PR #791 squash merged at `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`.
- Canonical reconciliation envelope: 6 paths; SHA-256 `a78e4871e0852ac6c4f466b7f3278efde846a4ae84f5fe589123672e568aea35`.
- Legacy M7.5 push-event startup failure is documented as pre-existing canonical debt, not a Sprint182 regression.
- Operational NO-GO remains unchanged.
- Next position: Sprint183 bounded discovery from canonical post-Sprint182.

## Recent material progression

- **Sprint182:** governed release artifact → installer-readiness bridge; engineering squash `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`.
- **Sprint181:** operator-visible installation readiness wizard; engineering squash `deb999fcd0694ba85c132d1b490bd90c0dc86309`.
- **Sprint180:** server-assisted initial merchant sign-in; engineering squash `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`.
- **Sprint179:** atomic initial POS-ready merchant authorization; engineering squash `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`.
- **Sprint178:** guarded merchant first-party application entry; engineering squash `8992c2ed1b6278d113e24e38a847bedeac345161`.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

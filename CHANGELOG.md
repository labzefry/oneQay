# Changelog

## 2026-09-19 — Sprint186 closed canonically

**Sprint186: Governed Activation-Readiness Handoff**

- Objective: `GOVERNED_ACTIVATION_READINESS_HANDOFF`.
- Added exact-release binding to verified `.env.pending`.
- Added private `activation-readiness.json` after verified pending configuration commit.
- Bound activation-readiness evidence to pending configuration SHA-256 and byte length.
- Added safe database evidence without copying database password, installation token, or application key.
- Added pending tamper detection and governed-release mismatch rejection.
- Preserved `PENDING_CONFIGURATION_VERIFIED` while exposing `activation_handoff_ready`.
- Updated pre-boot UI with `SEALED / NOT AUTHORIZED` handoff status.
- Packaged the activation-readiness verifier and handoff metadata in the governed M7.5 release.
- Explicitly kept activation, migration execution, Technical Preview, Production, and updater authority false.
- Added exact-envelope historical compatibility for M7.5 and Sprint32/Sprint33/Sprint34 without modifying migration, authentication, recovery, or business application semantics.
- Final engineering head `3c6453cd54ac0bc907d85ec01d7410d2c48e19fb` completed 74/74 pull-request workflows successfully.
- Product Owner exact-head merge authority succeeded.
- PR #802 squash merged at `7cb9e59ede2908f44f4f0d7b2d1c855d885bf3ae`; this is the canonical Sprint186 engineering evidence.
- Canonical main-push M7.5 run `35387074508` completed successfully.
- Post-merge cPanel/shared-runtime and Sprint155 source-contract regressions succeeded.
- Final engineering envelope: 10 paths; SHA-256 `6a948cea5e7d88f95abec930a0681f851df9d04a1eaaa835f4aac3de1e0c8102`.
- Canonical reconciliation envelope: 8 paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO remains unchanged.
- Next position: Sprint187 bounded discovery from canonical post-Sprint186.

## Recent material progression

- **Sprint186:** tamper-evident exact-release activation-readiness handoff; engineering squash `7cb9e59ede2908f44f4f0d7b2d1c855d885bf3ae`.
- **Sprint185:** verified pending database compatibility before configuration commit; engineering squash `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`.
- **Sprint184:** secure pre-boot pending runtime configuration preparation; engineering squash `dfb65d2782a580108d8ccd9a6f9203720fa036b7`.
- **Sprint183:** canonical governed M7.5 release automation restored; engineering squash `cd3facf81e3b1734353656da3ba5800607900fcb`.
- **Sprint182:** governed release artifact → installer-readiness bridge; engineering squash `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

# Changelog

## 2026-09-18 — Sprint183 closed canonically

**Sprint183: Governed M7.5 Release Workflow Execution Restoration**

- Objective: `GOVERNED_M7_5_RELEASE_WORKFLOW_EXECUTION_RESTORATION`.
- Restored canonical M7.5 GitHub Actions workflow startup and execution.
- Split the oversized historical Web regression command while preserving behavior and compatibility state through `GITHUB_ENV`.
- Preserved every `run` block below the workflow command-size boundary.
- Restored post-M7.4 historical synthetic compatibility by temporarily isolating newer POS persistence successors and restoring them deterministically.
- Initial engineering head `798e2f1a223bbce7ed0032bb4996e292a337bb72` completed 69/69 pull-request workflows successfully.
- Initial PR #793 squash merged at `03804129dac67c85fea3540d421a9b49adc3eb13`.
- Post-merge main push exposed a non-PR compatibility gap before reconciliation.
- Corrective PR #794 aligned non-PR schema-free historical qualification with the proven PR lane.
- Corrective exact head `f3527999a500463e9eea3f8b9b22dec24a34e93e` completed 69/69 pull-request workflows successfully.
- Corrective PR #794 squash merged at `cd3facf81e3b1734353656da3ba5800607900fcb`; this is the canonical Sprint183 engineering evidence.
- Canonical main-push M7.5 run `35369464318` completed successfully through packaging, installer sidecar generation, deterministic reproduction, artifact upload, and tracked-source cleanliness.
- Final engineering envelope: 1 path; SHA-256 `bcec6fc13a26f5c88f4408d76d362195ca9d546cc2df6d6c388a67640b93cce2`.
- Canonical reconciliation envelope: 8 paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- No application/migration source bytes or operational authority changed.
- Operational NO-GO remains unchanged.
- Next position: Sprint184 bounded discovery from canonical post-Sprint183.

## Recent material progression

- **Sprint183:** canonical governed M7.5 release automation restored; engineering squash `cd3facf81e3b1734353656da3ba5800607900fcb`.
- **Sprint182:** governed release artifact → installer-readiness bridge; engineering squash `ceb8fd7839a5077d59247b2cbdd2ba30559c4d25`.
- **Sprint181:** operator-visible installation readiness wizard; engineering squash `deb999fcd0694ba85c132d1b490bd90c0dc86309`.
- **Sprint180:** server-assisted initial merchant sign-in; engineering squash `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`.
- **Sprint179:** atomic initial POS-ready merchant authorization; engineering squash `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

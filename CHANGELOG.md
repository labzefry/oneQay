# Changelog

## 2026-09-19 — Sprint187 closed canonically

**Sprint187: Governed Runtime Configuration Promotion Request**

- Objective: `GOVERNED_RUNTIME_CONFIGURATION_PROMOTION_REQUEST`.
- Added a private runtime configuration promotion request after verified pending configuration and sealed activation-readiness handoff.
- Bound request identity to exact release ID, pending-environment SHA-256, and activation-readiness SHA-256.
- Added deterministic `PENDING_APPROVAL` request identity.
- Defined future exact-byte promotion from `.env.pending` to `.env`.
- Required separate exact-bound single-use operational authority.
- Explicitly requested no Technical Preview, persistence, updater, or migration changes.
- Explicitly kept promotion, migration, Technical Preview, Production, updater, and deployment authority false/not granted.
- Added atomic rollback if request materialization fails before preparation authority consumption.
- Added `promotion_request_pending` state and professional `PENDING APPROVAL` UI.
- Packaged request implementation and future authority path metadata in the governed M7.5 artifact.
- Final engineering head `ff7f6f58c487955ec43dcdaf3b01cdf7cdc02c28` completed 75/75 workflows successfully.
- Product Owner exact-head merge authority succeeded.
- PR #804 squash merged at `a327883e588e671491bb2a0cdfc03568e904dd8f`; this remains the canonical Sprint187 engineering evidence.
- Canonical main-push M7.5 run `35388478453` completed successfully.
- Post-merge cPanel/shared-runtime/Sprint155 source-contract regressions succeeded.
- Final engineering envelope: 10 paths; SHA-256 `e022e387816a78f400b0780ba1eefc6c1d8880ec51fb7ce31dd93f72f5726f8f`.
- Canonical reconciliation envelope: 8 paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO remains unchanged.
- Next position: Sprint188 bounded discovery.

## Recent material progression

- **Sprint187:** exact-bound runtime configuration promotion request; engineering squash `a327883e588e671491bb2a0cdfc03568e904dd8f`.
- **Sprint186:** tamper-evident exact-release activation-readiness handoff; engineering squash `7cb9e59ede2908f44f4f0d7b2d1c855d885bf3ae`.
- **Sprint185:** verified pending database compatibility; engineering squash `c53c76fc86ef5be67dd999ac7fc7e08f84c82f01`.
- **Sprint184:** secure pre-boot pending runtime configuration preparation; engineering squash `dfb65d2782a580108d8ccd9a6f9203720fa036b7`.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

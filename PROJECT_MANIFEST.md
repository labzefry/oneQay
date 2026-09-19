# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository owner / attribution:** Lab | zefry
**Default branch:** `main`
**Status date:** 2026-09-19

## Current canonical engineering checkpoint

**Canonical engineering checkpoint:** Sprint187
**Objective:** `GOVERNED_RUNTIME_CONFIGURATION_PROMOTION_REQUEST`
**Canonical engineering commit:** `a327883e588e671491bb2a0cdfc03568e904dd8f`
**Engineering PR:** #804 — `Sprint187: materialize governed runtime promotion request`
**Final engineering head:** `ff7f6f58c487955ec43dcdaf3b01cdf7cdc02c28`
**Exact-head qualification:** 75/75 successful
**Canonical main-push M7.5 qualification:** run `35388478453` — SUCCESS
**Engineering envelope:** 10 paths — `e022e387816a78f400b0780ba1eefc6c1d8880ec51fb7ce31dd93f72f5726f8f`
**Canonical reconciliation envelope:** 8 paths — `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`
**Previous canonical checkpoint:** Sprint186 reconciliation `e9a675858a7e8fac0b4c290aff6681b24e53ec04`
**Next position:** Sprint188 bounded discovery from the fully reconciled Sprint187 checkpoint.

> `a327883e588e671491bb2a0cdfc03568e904dd8f` is the canonical Sprint187 engineering evidence. The reconciliation squash must not replace it as the canonical engineering commit.

## 1. Purpose / Why

Sprint187 closes the governance gap after the sealed Sprint186 activation-readiness handoff. The installation chain could prove exact-release readiness, but there was no durable request artifact that an independent authority could review without directly promoting the runtime configuration.

## 2. What changed

- Added a private governed runtime-configuration promotion request.
- Materializes the request only after verified pending configuration and the exact-release activation-readiness handoff commit successfully.
- Binds the request to the exact governed release ID, exact pending-environment SHA-256, and exact activation-readiness SHA-256.
- Uses deterministic request identity and state `PENDING_APPROVAL`.
- Defines the future requested operation as exact-byte promotion from private `.env.pending` to active `.env`.
- Requires a separate, single-use, exact-bound operational authority at `runtime-configuration-promotion-authority.json`.
- Explicitly requests no Technical Preview flag change, persistence flag change, updater flag change, or migration execution.
- Explicitly records promotion, migration, Technical Preview, Production, updater, and deployment authority as false/not granted.
- Preserves rollback-on-request-materialization failure so preparation authority is not consumed unless pending configuration, handoff, and request all commit.
- Exposes `promotion_request_pending` and `PENDING APPROVAL` in the pre-boot operator UI.
- Packages the request implementation and request/authority metadata into the governed M7.5 artifact.
- Preserves M7.5 and Sprint32/Sprint33/Sprint34 historical qualification only for the exact Sprint187 envelope.

## 3. Evidence / Qualification

- Canonical parent before Sprint187 engineering: `e9a675858a7e8fac0b4c290aff6681b24e53ec04`.
- Final engineering head `ff7f6f58c487955ec43dcdaf3b01cdf7cdc02c28` completed 75/75 pull-request workflows successfully.
- Dedicated Sprint187 regression proved deterministic request identity, exact release/pending/handoff binding, request privacy, secret non-disclosure, tamper invalidation, no fabricated promotion authority, no active `.env`, preserved Sprint184–Sprint186 behavior, governed artifact packaging, and request-before-authority NO-GO.
- Repository-native Product Owner merge authority succeeded on the exact qualified head.
- PR #804 squash merged at `a327883e588e671491bb2a0cdfc03568e904dd8f`.
- Canonical main-push M7.5 run `35388478453` completed successfully.
- Post-merge shared-runtime evidence also succeeded: cPanel run `35388478471`, shared-runtime boundary run `35388478481`, and Sprint155 source-contract run `35388478525`.
- Final engineering envelope: exactly 10 paths; SHA-256 `e022e387816a78f400b0780ba1eefc6c1d8880ec51fb7ce31dd93f72f5726f8f`.
- Canonical reconciliation envelope: exactly 8 paths; SHA-256 `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## 4. Operational boundaries / NO-GO

Machine-readable operational authority under `ops/final-shift-close/` remains authoritative and unchanged:

- durable activation target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`;
- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview / Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

Sprint187 creates a review request only. It does not create active `.env`, grant promotion authority, execute migrations, mutate database/business state, change runtime feature flags, deploy, activate Technical Preview/Production, activate the updater, or select a durable target.

## 5. Next position

Begin Sprint188 bounded discovery from fully reconciled Sprint187. Select the smallest material P0/P1 blocker after the exact-bound promotion request that advances the governed installation/onboarding journey without crossing operational authority.

## Documentation responsibility

`PROJECT_MANIFEST.md` is the canonical human-readable state. `README.md`, `CHANGELOG.md`, `TASKS.md`, and `ROADMAP.md` are reconciled summaries. Git history, merged PRs, workflows, tests, and machine-readable contracts preserve detailed provenance.

Author by Lab | zefry

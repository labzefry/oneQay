# oneQay

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Current canonical main before this documentation reconciliation: `faff7d2c1a9e1bfe6f1f84cda416f5a62f0c63b2`.

PR #889 (`Sprint232: transition Final Shift Close permission provisioning state`) is merged. Its squash parent is Sprint237 main `07bb6e474fd3217132828a0cd09737a87a9ef308`, and the merge commit is verified.

Before merge:
- exact engineering head `0ff4a1cd73bc04f462733e1ab76e8b4d1c012588`;
- 102/102 PR-triggered exact-head workflows SUCCESS;
- Sprint102 run `35761431103` attempt 2 SUCCESS;
- permission provisioning evidence run `35872855919` SUCCESS;
- exactly one changed path: `ops/final-shift-close/STATE.json`;
- path-set SHA-256 `25254f999ec85014b9e591cb7bb2d72f65b745acfadc57bd7d6233c4e485f923`.

## Durable staging baseline

Selected target: `oneqay-durable-staging-01` (`durable-staging`, `SELECTED_NOT_AUTHORIZED`).

Running identity:
- source `5be28a3c001738373588b58e9d29832c46402de1`;
- artifact SHA-256 `66be23792478fb191f912571b35076c42783b9733cdb3fb514b549d22ec90dd7`;
- selection fingerprint `858280ea3575317e8d88eed7009c770530097e261c391b2869fa81ad7c8546ce`.

Merchant bootstrap is complete. Migration #27 is `EXECUTED`. `pos.shift.close` is durably provisioned to `merchant-initial-pos-operator` with `default_grant = NONE`.

## Final Shift Close boundary

Canonical state:
- migration #27: `EXECUTED`;
- permission provisioning: `PROVISIONED`;
- Final Shift Close feature: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview: `NOT_AUTHORIZED`;
- Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

Do not replay merchant bootstrap, migration #27, or the permission grant.

## Next material source blocker

The repository already contains the Sprint154 feature-activation execution-plan source foundation, but no dispatchable `.github/workflows/final-shift-close-feature-activation.yml` exists. The Final Shift Close-specific selected-target configuration mutation transport is not yet materialized, while delivery remains runtime-allowlisted to `local`, `test`, and `ci`.

The next meaningful engineering slice is to materialize a fail-closed, selected-target-bound activation executor/transport foundation with exact-head authority checks, read-before/write/read-after flag verification, health attestation, verified rollback, and regression qualification. This engineering work must not activate Final Shift Close or infer Technical Preview/Production/deployment authority.

## Operational NO-GO

Final Shift Close activation, deployment, Technical Preview, Production traffic, Production activation, updater activation, target reselection, Remote MySQL, a second migration #27 execution, and a second permission grant remain forbidden unless separately authorized where applicable.

Author by Lab | zefry

# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

**Current canonical main:** `faff7d2c1a9e1bfe6f1f84cda416f5a62f0c63b2`
**Current closure:** PR #889 — `Sprint232: transition Final Shift Close permission provisioning state`
**PR #889 engineering head:** `0ff4a1cd73bc04f462733e1ab76e8b4d1c012588`
**PR #889 squash parent:** `07bb6e474fd3217132828a0cd09737a87a9ef308`
**Predecessor engineering checkpoint:** Sprint237 / PR #894 — permission successor-head reattestation
**Repository signature:** VERIFIED / valid

PR #889 closed the canonical state transition from `permission_provisioning.state = NONE` to `PROVISIONED` after migration #27 execution and exact selected-target permission evidence. The merge changed exactly `ops/final-shift-close/STATE.json` (+1/-1). Its sorted-newline path-set SHA-256 is `25254f999ec85014b9e591cb7bb2d72f65b745acfadc57bd7d6233c4e485f923`.

## Exact closure evidence

- PR-triggered exact-head workflow matrix before merge: `102/102` SUCCESS.
- Sprint102 Operational Sequencing Gate run `35761431103`, attempt 2: SUCCESS.
- permission provisioning evidence run `35872855919`: SUCCESS.
- exact-head `product-owner-merge-authority`: SUCCESS.
- exact-head `final-shift-close-permission-provisioning-authority`: SUCCESS.
- exact-head `final-shift-close-permission-provisioning-evidence`: SUCCESS.
- `final-shift-close-migration27-execution-authority`: intentional fail-closed after migration completion; do not replay migration #27.
- `final-shift-close-feature-activation-authority`: intentional fail-closed; feature activation is not authorized.

## Durable selected target

- environment: `oneqay-durable-staging-01`
- runtime class: `durable-staging`
- selection state: `SELECTED_NOT_AUTHORIZED`
- exact running source: `5be28a3c001738373588b58e9d29832c46402de1`
- exact running artifact SHA-256: `66be23792478fb191f912571b35076c42783b9733cdb3fb514b549d22ec90dd7`
- readiness attestation SHA-256: `3a45a4de0328cb8e5e0ed5eafb907e0a41230974c03b21d03ed571b7a2d3564d`
- selection fingerprint SHA-256: `858280ea3575317e8d88eed7009c770530097e261c391b2869fa81ad7c8546ce`
- selected database binding run: `35685845647`
- selected database binding SHA-256: `c9247f4200c8b55eb2d8e109185337a44b663dc44961d67cac51eded7ef54e0d`

## Durable merchant baseline

Canonical merchant tuple:
- tenant `tenant-staging-001`
- identity `identity-staging-admin-001`
- organization `organization-staging-001`
- outlet `outlet-staging-001`
- device `device-staging-001`
- provisioning `provisioning-staging-001`

Protected control role remains `authorization-policy-administrator` with `authorization.policy.manage`. Business role `merchant-initial-pos-operator` now durably has `pos.shift.close`; no second permission grant is allowed.

## Current Final Shift Close operational state

- `migration27.state = EXECUTED`
- `permission_provisioning.state = PROVISIONED`
- `permission_provisioning.permission_id = pos.shift.close`
- `permission_provisioning.default_grant = NONE`
- `feature_activation.state = INACTIVE`
- `deployment_authority = NOT_GRANTED`
- `technical_preview_activation = NOT_AUTHORIZED`
- `production_activation = NOT_AUTHORIZED`
- `updater_activation = INACTIVE`

Migration #27, merchant bootstrap, and `pos.shift.close` provisioning are completed and must not be replayed.

## Next material engineering blocker

The repository has the Sprint154 activation execution-plan source foundation, but the dispatchable Final Shift Close activation workflow `.github/workflows/final-shift-close-feature-activation.yml` is absent and the selected-target-bound configuration mutation transport remains unmaterialized for feature activation. `FinalShiftCloseServiceProvider` still enables delivery only for `local`, `test`, and `ci`.

The next bounded source work must close the dispatchable selected-target-bound feature-activation executor / configuration-mutation transport readiness gap with read-before/write/read-after verification, non-mutating health attestation, verified rollback, exact-target and exact-head binding, and regression coverage. It must not activate the feature and must not widen the runtime allowlist until the full durable dependency envelope for the selected runtime class is qualified.

Feature activation itself requires separate exact-head Product Owner authority and remains outside standing engineering authority.

## Operational NO-GO

Until separately authorized: Final Shift Close stays `INACTIVE`; deployment remains `NOT_GRANTED`; Technical Preview and Production remain `NOT_AUTHORIZED`; Production traffic remains unauthorized; updater remains `INACTIVE`; target reselection is forbidden; Remote MySQL remains closed; no second migration #27 execution; no second permission grant.

Author by Lab | zefry

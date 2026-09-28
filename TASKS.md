# oneQay Tasks

**Canonical main:** `8d343fd90b00390e73169c9def4d13e1cc24f2fe`

## Completed and do not replay

- [x] Durable target selected: `oneqay-durable-staging-01`.
- [x] Durable merchant bootstrap completed.
- [x] Migration #27 executed and verified.
- [x] `pos.shift.close` provisioned with `default_grant = NONE`.
- [x] Final Shift Close runtime activation completed.
- [x] Current runtime source `505518f79e8a70f789b94f5074a040eae785aeb0` deployed to durable staging.
- [x] Post-activation producer run `36407450933` completed successfully.
- [x] Trusted ingestion run `36413265200` attempt 1 completed successfully.
- [x] Selected-target generation qualification run `36414746533` completed successfully.
- [x] PR #922 squash merged as canonical main `8d343fd90b00390e73169c9def4d13e1cc24f2fe`.
- [x] Selected target generation reconciled to source `505518f...`, artifact `5b3030d...`, readiness `069a27b...`.
- [x] Same-source Production candidate published by run `36380024637`.
- [x] Same-source Production dark-deployment operator kit published by run `36380081412`.

Do not repeat merchant bootstrap, migration #27, permission provisioning, feature activation, staging deployment, post-activation producer, trusted ingestion, or same-target generation reconciliation for this generation.

## Current Production-readiness work

- [ ] Materialize exact Production target input for the real isolated Production environment.
- [ ] Keep Production private bindings outside the public document root with owner-only permissions.
- [ ] Run Production target qualification only; do not deploy during preflight.
- [ ] Confirm whether the real Production host satisfies the current symlink-backed active-release/document-root contract.
- [ ] If the host uses the same no-symlink/fixed-public-root constraints observed on durable staging, implement and qualify a Production `FIXED_PUBLIC_BRIDGE` successor before any deployment authority request.
- [ ] Only after a qualified target exists, prepare the exact Production deployment-authority request.
- [ ] Obtain separate short-lived operational authority before any dark deployment.
- [ ] Cap dark deployment at `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.
- [ ] Do not activate Production business traffic without a separate later authority and business-runtime readiness gate.

## Exact Production source material

- Production runtime source: `505518f79e8a70f789b94f5074a040eae785aeb0`.
- Production release ID: `production-505518f79e8a`.
- Production archive SHA-256: `906fb0dd630fc1de95e23a5505999567e2f287b53da8cb8ec346f9d44e311695`.
- Production candidate artifact ID: `10952267537`.
- Production operator-kit artifact ID: `10952223291`.
- Durable-staging prerequisite artifact SHA-256: `5b3030d5154e3b0938a944c5b6b0d33218078f37d8f066a4e9cc4067ff285451`.

## Operational boundary

- Production deployment authority: `NOT_GRANTED`.
- Production traffic: `NOT_AUTHORIZED`.
- Technical Preview: `NOT_AUTHORIZED`.
- updater: `INACTIVE`.
- no migration #27 replay.
- no permission reprovisioning.
- no Final Shift Close reactivation.
- no target reselection.

Author by Lab | zefry

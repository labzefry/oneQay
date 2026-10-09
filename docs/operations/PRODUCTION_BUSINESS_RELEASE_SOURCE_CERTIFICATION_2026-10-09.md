# oneQay — Business Release Source Certification (2026-10-09)

Author by Lab | zefry

## Canonical lineage and purpose

Engineering baseline: `main` at `630f37c93134aabf948c3109947995728f7dce4f` (PRs #932–#936 merged). Do not repeat operator-reported 28-receipt/43-table oneQayDev schema reconciliation, migration #27, earlier preflight, private secret configuration, domain setup or Cron. Staging at `oneqay.n07.my.id` remains protected. The isolated `oneqaydev.n07.my.id` target and private `/home/pekd7254/oneqay-production-test` were prepared by the operator, but no business application cutover is evidenced.

This delivery adds an **independent source-certification release path**. It deliberately does **not** change the legacy dark-only builder, schema or fixed-public deployment executor, and does not claim to finish the integrated commercial go-live.

## Source-certification contract

- The trusted `Production Business Release Certification` Actions workflow must run on `main` at the exact `GITHUB_SHA`. On a PR it tests the candidate head without publishing the Production artifact.
- Before a business-capable RELEASE.json is written, the business builder requires locked Laravel dependencies, previously built frontend assets, trusted GitHub Actions/main/ref/run identity and passing merchant, runtime, catalog, inventory, shift, sale and Final Shift Close source regressions.
- Archive and manifest are derived from the same source SHA and application tree. A deterministic tar payload and matching SHA-256 are emitted; the new v2 validator checks source metadata, source CI provenance, manifest/embedded RELEASE agreement and archive integrity.
- In this context `business_runtime_activation_ready=true` means the **application source has passed the named isolated CI suites**; it does not mean the target host, initial merchant, real CASH sale, recovery drill or traffic are qualified. The release explicitly retains `host_business_acceptance_verified=false`, `deployment_authority=NOT_GRANTED`, `production_traffic_activation=NOT_AUTHORIZED` and `production_activation=NOT_AUTHORIZED`.
- The current dark-only operator is not business-capable and must reject this successor manifest. Do not use the old dark executor to deploy business runtime, and never modify a production flag or rewrite manifest to manufacture readiness.
- This workflow makes no database mutation, host deployment, DNS change or real-money transaction.

## Remaining critical business go-live scope (not certified here)

1. A separately gated, noninteractive, private-file first-merchant Production provisioning operator tied to the exact tenant/identity/organization/outlet/device, without exposing credentials. Existing Local/Test/CI merchant console bootstrap remains untouched.
2. A new fixed-public cPanel production-business successor with explicit exact-target deployment authority, rollback snapshot, switch/readback and a separately controlled traffic activation. Reuse private oneQayDev configuration only after independent qualification; do not overwrite staging.
3. On the isolated oneQayDev host: authenticate merchant, verify session/MFA and scope, configure catalog and stock, open cash and shift, complete a real durable CASH sale, verify receipt and idempotent replay, decrement inventory, close shift, verify Final Shift Close, audit and reporting, and rehearse rollback.
4. Distinct authorization for any Production host mutation and eventual `oneqay.n07.my.id` cutover. No host or traffic authority is implied by a GitHub squash merge or successful source-certification workflow.

## Acceptance and interpretation

Only a green Actions run on the exact source commit can substantiate a **source-certified artifact**. It is not the business-ready deployment package requested for oneQayDev until (1) secure provisioning, (2) compatible operator and (3) target-host acceptance are implemented and qualified. Retain the v1 dark-only path for historical staging and Production governance compatibility.

Do not collect or store database credentials, APP_KEY, TOTP secrets, approval tokens or private binding JSON in repository, issue comments or chat.

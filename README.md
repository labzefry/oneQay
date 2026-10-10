# oneQay

> **Latest verified Production Go-Live handoff (2026-10-11):** [Canonical real-business checkpoint](docs/operations/PRODUCTION_GO_LIVE_CHECKPOINT_2026-10-11.md) · [Master continuation prompt](docs/operations/MASTER_CONTINUATION_PROMPT_ONEQAY_2026-10-11.md) · [oneQayDev cPanel upgrade handoff](docs/operations/ONEQAYDEV_CPANEL_UPGRADE_HANDOFF_2026-10-11.md). PR #938 is MERGED; certified application source `409ac6b2bdb80d31fbfb9425ddbb413a278b97e6`. **Actual cPanel on-host upgrade, merchant provisioning and real CASH transaction are NOT YET VERIFIED.** Older SHA/status text below is historical context. Never rerun migrations or touch occupied staging.

**oneQay — The Future of Intelligent Business Management**

Enterprise-oriented multi-tenant business-management platform built with Modular Monolith First, Clean Architecture, DDD, tenant-first boundaries, deny-by-default authorization, and governed fail-closed delivery.

**Repository / Product Owner attribution:** Lab | zefry

## Current canonical status

Post-activation durable-staging reconciliation is closed on canonical main `8d343fd90b00390e73169c9def4d13e1cc24f2fe` through PR #922.

Final Shift Close remains canonically `ACTIVE`; migration #27 remains `EXECUTED`; permission `pos.shift.close` remains `PROVISIONED` with `default_grant = NONE`.

## Current selected durable target

Selection state remains `SELECTED_NOT_AUTHORIZED` for the already-selected target `oneqay-durable-staging-01` (`durable-staging`). This is a same-target generation reconciliation, not a target reselection.

Current running generation:

- source commit: `505518f79e8a70f789b94f5074a040eae785aeb0`;
- durable-staging artifact SHA-256: `5b3030d5154e3b0938a944c5b6b0d33218078f37d8f066a4e9cc4067ff285451`;
- readiness attestation SHA-256: `069a27b16ff81082bd3d746fe678dc966b8f2a61bb95c35f5ab4c0168f7d347e`;
- selection fingerprint SHA-256: `bdce15c5053446119979efe6b9f72572857c06a0caa11d649b8ec54d7239a411`;
- trusted ingestion run: `36413265200`, attempt `1`;
- ingestion fingerprint SHA-256: `f9c9e205a7670ba4e69cdf6f82c32a17589c79880e048d9452213fd5cdafbd04`.

Post-activation trust chain:

- producer run `36407450933`: SUCCESS;
- trusted ingestion run `36413265200`: SUCCESS;
- selected-target generation qualification run `36414746533`: SUCCESS;
- PR #922 squash merge: `8d343fd90b00390e73169c9def4d13e1cc24f2fe`.

## Exact-source Production dark-deployment material

The current staging generation has a same-source Production candidate and Production dark-deployment operator kit for source `505518f79e8a70f789b94f5074a040eae785aeb0`.

Production candidate:

- publication run `36380024637`;
- Actions artifact ID `10952267537`;
- Actions artifact name `oneqay-production-505518f79e8a-operator-bundle`;
- Production archive SHA-256 `906fb0dd630fc1de95e23a5505999567e2f287b53da8cb8ec346f9d44e311695`.

Production operator kit:

- publication run `36380081412`;
- Actions artifact ID `10952223291`;
- Actions artifact name `oneqay-production-operator-kit-505518f79e8a`;
- dark-deployment success ceiling `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.

These artifacts are readiness material only. They do not grant Production deployment or traffic authority.

## Current operational boundary

- Final Shift Close runtime flag: `ACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview: `NOT_AUTHORIZED`;
- Production deployment/traffic: `NOT_AUTHORIZED`;
- updater: `INACTIVE`;
- no migration #27 replay;
- no permission reprovisioning;
- no feature reactivation;
- no target reselection.

## Next production-readiness blocker

The next bounded step is Production target qualification/preflight against the exact-source Production candidate. No Production target is yet canonically qualified or selected.

The existing Production dark-deployment adapter accepts cPanel/no-SSH execution but still requires a symlink-backed active-release pointer and a document root shaped as `<active-release-pointer>/apps/web/public`. The already-observed durable-staging cPanel target instead required `FIXED_PUBLIC_BRIDGE` with PHP symlink unavailable. Therefore Production must fail closed until the real Production target either proves the existing symlink/direct-document-root contract or a separately governed Production fixed-public-bridge successor is published.

Production target qualification and any later deployment authority remain separate from this reconciliation.

Author by Lab | zefry

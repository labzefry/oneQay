# oneQay Project Manifest

**Product:** oneQay — The Future of Intelligent Business Management
**Repository / Product Owner attribution:** Lab | zefry

## Canonical state

- canonical main after post-activation selected-target generation reconciliation: `8d343fd90b00390e73169c9def4d13e1cc24f2fe`
- reconciliation PR: #922 — squash merged
- current application/runtime source generation: `505518f79e8a70f789b94f5074a040eae785aeb0`
- Final Shift Close: `ACTIVE`
- migration #27: `EXECUTED` / no replay
- permission `pos.shift.close`: `PROVISIONED`, `default_grant = NONE`
- deployment authority: `NOT_GRANTED`
- Technical Preview: `NOT_AUTHORIZED`
- Production deployment/traffic: `NOT_AUTHORIZED`
- updater: `INACTIVE`

## Selected durable target generation

- environment: `oneqay-durable-staging-01`
- runtime class: `durable-staging`
- selection state: `SELECTED_NOT_AUTHORIZED`
- running source: `505518f79e8a70f789b94f5074a040eae785aeb0`
- running artifact SHA-256: `5b3030d5154e3b0938a944c5b6b0d33218078f37d8f066a4e9cc4067ff285451`
- readiness attestation SHA-256: `069a27b16ff81082bd3d746fe678dc966b8f2a61bb95c35f5ab4c0168f7d347e`
- selection fingerprint SHA-256: `bdce15c5053446119979efe6b9f72572857c06a0caa11d649b8ec54d7239a411`
- trusted ingestion run: `36413265200`, attempt `1`
- ingestion fingerprint SHA-256: `f9c9e205a7670ba4e69cdf6f82c32a17589c79880e048d9452213fd5cdafbd04`

## Post-activation evidence chain

- post-activation durable-runtime producer run: `36407450933` — SUCCESS
- trusted ingestion run: `36413265200` — SUCCESS
- selected-target generation qualification run: `36414746533` — SUCCESS
- selected-target generation PR #922 merge: `8d343fd90b00390e73169c9def4d13e1cc24f2fe`

## Exact-source Production material

Production candidate for source `505518f79e8a70f789b94f5074a040eae785aeb0`:

- publication run: `36380024637`
- Actions artifact ID: `10952267537`
- artifact name: `oneqay-production-505518f79e8a-operator-bundle`
- Production archive SHA-256: `906fb0dd630fc1de95e23a5505999567e2f287b53da8cb8ec346f9d44e311695`
- release ID: `production-505518f79e8a`

Production dark-deployment operator kit:

- publication run: `36380081412`
- Actions artifact ID: `10952223291`
- artifact name: `oneqay-production-operator-kit-505518f79e8a`
- Production traffic activation: `NOT_AUTHORIZED`
- deployment authority: `NOT_GRANTED`

## Next material production-readiness blocker

A real Production target must be qualified before any deployment authority request exists. The current Production adapter supports `CPANEL_CRON_PHP_CLI_NO_SSH`, but its target qualification/execution contract still requires a symlink-backed active-release pointer and a document root exactly under that active release.

The observed durable-staging cPanel target required `FIXED_PUBLIC_BRIDGE` and could not use PHP symlink semantics. That staging observation is not itself a Production target qualification. Production therefore remains fail-closed until the actual Production target proves the current Production contract or a governed Production fixed-public-bridge successor is published.

No Production deployment, Production traffic activation, migration replay, permission reprovisioning, feature reactivation, target reselection, updater activation, or producer dispatch is authorized by this manifest reconciliation.

Author by Lab | zefry

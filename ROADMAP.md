# oneQay Roadmap

**Canonical main:** `8d343fd90b00390e73169c9def4d13e1cc24f2fe`

## Completed operational horizon

Final Shift Close durable-staging activation is complete and canonically `ACTIVE`. Migration #27 and permission provisioning are complete and must not be replayed.

The post-activation durable-staging source promotion and trust-chain closure are also complete for runtime source `505518f79e8a70f789b94f5074a040eae785aeb0`:

1. exact artifact deployed to `oneqay-durable-staging-01`;
2. fresh post-activation runtime reattestation completed;
3. trusted ingestion completed;
4. same-target selected-generation qualification completed;
5. PR #922 reconciled the selected generation to canonical main without target reselection.

## Current production-readiness position

- selected durable staging: `oneqay-durable-staging-01`;
- running source: `505518f79e8a70f789b94f5074a040eae785aeb0`;
- running artifact: `5b3030d5154e3b0938a944c5b6b0d33218078f37d8f066a4e9cc4067ff285451`;
- readiness attestation: `069a27b16ff81082bd3d746fe678dc966b8f2a61bb95c35f5ab4c0168f7d347e`;
- Final Shift Close: `ACTIVE`;
- Production candidate for the same runtime source: published;
- Production dark-deployment operator kit for the same runtime source: published;
- Production target: not yet canonically qualified or selected;
- Production deployment authority: `NOT_GRANTED`;
- Production traffic: `NOT_AUTHORIZED`;
- Technical Preview: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

## Next material horizon

1. Qualify the real Production target without deployment mutation.
2. Bind exact Production environment ID, filesystem roots, document-root presentation model, dark-health endpoint, private runtime bindings, and observed host capabilities.
3. Fail closed if the target cannot satisfy the current Production deployment contract.
4. If the actual Production cPanel host shares the observed no-symlink/fixed-document-root constraints, publish a bounded Production `FIXED_PUBLIC_BRIDGE` successor before any deployment authority request.
5. Only after target qualification, prepare an exact Production deployment-authority request bound to source `505518f...` and Production archive `906fb0dd...`.
6. Require separate short-lived Product Owner operational authority before dark deployment.
7. Dark deployment may reach only `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`; Production business traffic remains a later separately governed gate.
8. Preserve tenant isolation, deny-by-default authorization, rollback, observability, installer/updater governance, and the p95 server-response target.

## Authority boundary

This production-readiness reconciliation does not authorize Production deployment/traffic, migration #27 replay, permission reprovisioning, feature reactivation, target reselection, producer dispatch, Technical Preview activation, or updater activation.

Author by Lab | zefry

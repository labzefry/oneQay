# oneQay Roadmap

**Canonical entry for Sprint252:** `2dd17971532fabf52ace576fb7cb4aff5556bac9`

## Completed operational horizon

Final Shift Close durable-staging activation is complete and canonically `ACTIVE`. Migration #27 and permission provisioning are complete and must not be replayed. Trusted activation execution and evidence are SUCCESS.

## Current production-readiness position

- selected durable staging: `oneqay-durable-staging-01`;
- running source: `59b42137eed717aacc82080a46753440bb4d8537`;
- Final Shift Close: `ACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview: `NOT_AUTHORIZED`;
- Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

## Next material engineering horizon

1. Close post-activation current-main publication and promotion continuity.
2. Publish a deterministic current-main durable-staging successor artifact and promotion kit without runtime mutation.
3. Only under separate short-lived deployment authority, promote the exact current-main successor to the same isolated durable-staging target.
4. Prove Final Shift Close remains `ACTIVE`, re-attest runtime/source/artifact identity, and reconcile selected-target generation.
5. Use that fresh staging evidence to advance dark-production readiness; Production traffic/activation remains separately governed.
6. Preserve tenant isolation, security, rollback, observability, installer/updater governance, and the p95 server-response target.

## Authority boundary

Sprint252 source work is not deployment authority and does not authorize Technical Preview, Production, Production traffic, updater activation, target reselection, migration replay, permission reprovisioning, or runtime allowlist widening.

Author by Lab | zefry

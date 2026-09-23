# oneQay Roadmap

**Roadmap checkpoint:** post-PR #889 canonical reconciliation
**Canonical operational baseline:** `faff7d2c1a9e1bfe6f1f84cda416f5a62f0c63b2`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed operational horizon

The durable staging target `oneqay-durable-staging-01` is selected but not authorized for feature activation. Merchant bootstrap is complete, migration #27 is executed, and `pos.shift.close` is durably provisioned to `merchant-initial-pos-operator` with no default grant.

PR #889 closed the repository state transition to `permission_provisioning.state = PROVISIONED` after exact selected-target evidence run `35872855919` succeeded. Final Shift Close remains `INACTIVE`.

## Current production-readiness position

The repository has moved beyond the historical Sprint219 checkpoint recorded by the previous root documentation. Current canonical constraints are:

- migration #27: `EXECUTED`;
- permission provisioning: `PROVISIONED`;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview: `NOT_AUTHORIZED`;
- Production: `NOT_AUTHORIZED`;
- updater: `INACTIVE`.

No replay of migration #27, merchant bootstrap, or permission provisioning is permitted.

## Next material engineering horizon

The immediate source-side blocker is feature-activation execution readiness, not another migration/permission compatibility loop.

1. Materialize a dispatchable Final Shift Close feature-activation executor that remains fail-closed without exact activation authority.
2. Bind it to the already-selected durable target and exact running source/artifact identity.
3. Materialize an authenticated configuration mutation transport scoped only to `ONEQAY_POS_SHIFT_CLOSE_ENABLED`.
4. Require read-before/write/read-after verification.
5. Require non-mutating health/route attestation on the same target.
6. Require verified rollback to `false` on any post-write failure.
7. Preserve deny-by-default, secret-free evidence, replay resistance, and exact-head/target binding.
8. Qualify the full durable dependency envelope before widening the Final Shift Close runtime allowlist beyond `local`, `test`, and `ci`.
9. Only after source readiness is complete may a separate exact-head Product Owner feature-activation authority be requested.

## Authority boundary

Engineering readiness work does not authorize feature activation. Technical Preview activation, Production activation/traffic, deployment, updater activation, target reselection, Remote MySQL, and any repeat operational mutation remain separately prohibited.

The roadmap must advance through business-completion blockers rather than anti-granular compatibility chains.

Author by Lab | zefry

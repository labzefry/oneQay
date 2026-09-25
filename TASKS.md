# oneQay Tasks

**Canonical entry for Sprint252:** `2dd17971532fabf52ace576fb7cb4aff5556bac9`

## Completed and do not replay

- [x] Durable target selected: `oneqay-durable-staging-01`.
- [x] Durable merchant bootstrap completed.
- [x] Migration #27 executed and verified.
- [x] `pos.shift.close` provisioned with `default_grant = NONE`.
- [x] Final Shift Close runtime activation completed.
- [x] Trusted activation evidence run `36121950330` attempt 2 completed successfully.
- [x] PR #903 squash merged; canonical Final Shift Close state is `ACTIVE`.

Do not repeat merchant bootstrap, migration #27, permission provisioning, or feature activation.

## Sprint252 — post-activation promotion continuity

- [x] Require canonical `feature_activation.state = ACTIVE` in current publication/promotion workflows.
- [x] Add post-activation deployment-plan successor.
- [x] Add cPanel post-activation deployment executor successor that verifies raw readiness remains `ACTIVE`.
- [x] Add ACTIVE deployment evidence schema and qualifier.
- [x] Package successor entrypoints in the durable-staging promotion kit.
- [x] Preserve migration/permission/deployment/TP/Production/updater/allowlist NO-GO boundaries.

## Next operational step after Sprint252 merge

- [ ] Qualify the automatically published exact-main artifact/promotion kit.
- [ ] Obtain separate exact short-lived deployment authority before any real cPanel source promotion.
- [ ] Promote only the exact governed artifact to the existing isolated durable-staging target.
- [ ] Verify Final Shift Close remains `ACTIVE`.
- [ ] Produce fresh runtime attestation and reconcile selected-target generation.
- [ ] Continue to dark-production readiness; do not activate Production traffic without separate authority.

Author by Lab | zefry

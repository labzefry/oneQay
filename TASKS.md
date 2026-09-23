# oneQay Tasks

**Current canonical operational checkpoint:** PR #889 closed
**Canonical main before this reconciliation:** `faff7d2c1a9e1bfe6f1f84cda416f5a62f0c63b2`
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed and do not replay

- [x] Durable target selected: `oneqay-durable-staging-01` / `durable-staging` / `SELECTED_NOT_AUTHORIZED`.
- [x] Durable merchant bootstrap completed.
- [x] Migration #27 executed and verified.
- [x] `pos.shift.close` provisioned to `merchant-initial-pos-operator` with `default_grant = NONE`.
- [x] Successor-head permission evidence run `35872855919` completed successfully.
- [x] Sprint102 sequencing gate run `35761431103` attempt 2 completed successfully.
- [x] PR #889 exact head `0ff4a1cd73bc04f462733e1ab76e8b4d1c012588` qualified at 102/102 PR-triggered workflows SUCCESS.
- [x] PR #889 squash merged as `faff7d2c1a9e1bfe6f1f84cda416f5a62f0c63b2`.
- [x] Post-merge canonical `STATE.json` records migration `EXECUTED`, permission `PROVISIONED`, feature `INACTIVE`.

Do not repeat merchant bootstrap, migration #27, or permission provisioning.

## Next P0/P1 engineering readiness

- [ ] Materialize dispatchable `.github/workflows/final-shift-close-feature-activation.yml` without executing activation.
- [ ] Bind the executor to the exact selected durable target and exact target PR head.
- [ ] Materialize authenticated configuration mutation transport scoped only to `ONEQAY_POS_SHIFT_CLOSE_ENABLED`.
- [ ] Enforce read-before = `false`, bounded write, and read-after = `true` on the same target.
- [ ] Enforce non-mutating health/route attestation after write.
- [ ] Enforce rollback to `false` and verify rollback on any failed post-write proof.
- [ ] Keep evidence signed/secret-free/replay-resistant according to repository contracts.
- [ ] Qualify the full durable dependency envelope for the selected runtime class before any runtime allowlist widening.
- [ ] Add regression coverage for authority absence, target mismatch, source/artifact mismatch, write/readback failure, health failure, rollback failure, and success evidence publication boundary.

## Separate operational authority required later

- [ ] Obtain exact-head Final Shift Close feature-activation authority only after source readiness is green.
- [ ] Execute feature activation only under that separate authority.
- [ ] Keep Technical Preview, deployment, Production, Production traffic, and updater actions separately governed.

## Still prohibited

No Final Shift Close activation, deployment, Technical Preview activation, Production activation/traffic, updater activation, target reselection, Remote MySQL opening, second migration #27 execution, or second permission grant is authorized by this reconciliation.

Author by Lab | zefry

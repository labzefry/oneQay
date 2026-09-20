# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint211 closed canonically
**Canonical engineering commit:** `e37300d5d1be6727cdb5d818b6365c6429f2af9d`
**Engineering PR:** #857
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint211 completed

- [x] Confirm Sprint205 durable artifact was reproducible but not retained.
- [x] Add main-only durable-staging publication workflow.
- [x] Reuse Sprint205 deterministic builder and validator.
- [x] Reuse Sprint206 secret-free deployment handoff.
- [x] Require artifact reproducibility before upload.
- [x] Publish archive + manifest + SHA-256 sidecar + handoff as one bundle.
- [x] Preserve M7.5 and Sprint32–34 historical compatibility.
- [x] Qualify final head `276acc9ab8fe61cb65632bce8e5f2dda9d411fc8` at 94/94 SUCCESS.
- [x] Product Owner merge authority SUCCESS.
- [x] Engineering squash `e37300d5d1be6727cdb5d818b6365c6429f2af9d`.
- [x] Publication run `35487670967` SUCCESS.
- [x] Publish artifact ID `10597712890` with SHA-256 `faf6b4799648c0fe1d5ddcf4a55e0a39ba8bd0506e3e2ef3f4bb33f38495d079`.
- [x] Preserve operational NO-GO unchanged.
- [x] Update issue #856 with the published bundle evidence.

## Next operational blocker — issue #856

- [ ] Materialize/select the actual isolated non-production durable-staging provider/target.
- [ ] Produce truthful Sprint208 `OPERATOR_TARGET_CANDIDATE`.
- [ ] Produce exact deployment-authority request bound to the published artifact and target.
- [ ] Obtain separately issued external deployment authority, lifetime <= 900 seconds.
- [ ] Qualify authority with approval token via STDIN.
- [ ] Generate exact Sprint207 operator deployment plan.
- [ ] Execute externally against the real target.
- [ ] Produce Sprint209 deployment evidence from actual readback/health/rollback.
- [ ] Configure protected producer bindings.
- [ ] Dispatch protected attestation producer and run trusted ingestion.
- [ ] Keep accepted target unselected until separate selection authority exists.

## Still prohibited

No real target deployment, migration #27 execution, permission provisioning, target selection, producer dispatch, Final Shift Close activation, Technical Preview activation, Production activation, or updater activation is authorized by Sprint211 artifact publication.

Author by Lab | zefry

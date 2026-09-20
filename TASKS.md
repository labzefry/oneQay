# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint217 closed canonically
**Canonical engineering commit:** `afb048c9b7edc53d13ad8f5fc1197a8874966450`
**Engineering PR:** #869
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint217 completed

- [x] Prove the cPanel kit was stale-bound to Sprint211.
- [x] Rebind the kit contract to Sprint216 staging artifact `10603323419`.
- [x] Include exact publication/artifact/digest/archive/manifest/handoff identity in kit metadata.
- [x] Remove current operator instructions that select Sprint211 by Sprint number.
- [x] Update historical Sprint213 regression to preserve current binding.
- [x] Add dedicated Sprint217 regression.
- [x] Prove current kit builds deterministically.
- [x] Exercise packaged kit through cPanel target qualification and Sprint208 authority request.
- [x] Qualify final engineering head at 96/96 SUCCESS.
- [x] Publish current cPanel kit `10603569410`.
- [x] Independently verify ZIP checksum, manifest, release binding, absence of stale Sprint211 refs, application bytes, and secret-bearing file shapes.

## Real durable-staging gate — issue #856

- [ ] Materialize a real isolated durable-staging target.
- [ ] Use application artifact `10603323419`.
- [ ] Use cPanel kit `10603569410` when the real target is cPanel no-SSH.
- [ ] Run real target qualification.
- [ ] Obtain separate Sprint208 deployment authority <=900 seconds.
- [ ] Generate exact Sprint207 plan.
- [ ] Run guarded Sprint214 staging executor.
- [ ] Qualify deployment evidence as `DEPLOYED_VERIFIED_NOT_SELECTED`.

## Next source blocker

- [ ] Add bounded Production dark-deployment execution tool/adapter.
- [ ] Revalidate exact Production plan, authority, target and artifact.
- [ ] Preserve migration-free, traffic-inactive posture.
- [ ] Verify dark health and rollback rehearsal.
- [ ] Emit only Production deployment evidence candidate accepted as `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.

## Production promotion gate

- [ ] Use Production candidate `10603358335`.
- [ ] Require verified same-source staging evidence.
- [ ] Qualify exact real Production target.
- [ ] Obtain separate Production deployment authority <=900 seconds.
- [ ] Execute dark deployment only.
- [ ] Keep Production traffic/business activation separately blocked.

## Still prohibited

No migration #27 execution, permission provisioning, target selection, producer dispatch, Final Shift Close activation, Technical Preview activation, Production traffic activation, or updater activation is authorized by Sprint217 repository merge.

Author by Lab | zefry

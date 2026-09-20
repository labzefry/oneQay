# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint216 closed canonically
**Canonical engineering commit:** `d0b5becbf945c5192e797d512a704eb5aecc6eaa`
**Engineering PR:** #867
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint216 completed

- [x] Prove durable-staging artifacts are not valid as Production artifacts.
- [x] Preserve application source freeze while adding Production deployment governance.
- [x] Add deterministic secret-free Production candidate builder/manifest validation.
- [x] Add exact Production target candidate contract.
- [x] Require verified same-source durable-staging deployment evidence.
- [x] Add separate <=900-second Production deployment authority binding.
- [x] Add deterministic dark Production deployment plan.
- [x] Add Production deployment evidence qualification limited to `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.
- [x] Keep migration #27 and Production business/traffic activation unauthorized.
- [x] Qualify final engineering head at 94/94 SUCCESS.
- [x] Publish same-source staging artifact `10603323419`.
- [x] Publish same-source Production candidate `10603358335`.
- [x] Independently verify Actions digests, archive checksums/integrity, migration count, secret filename absence, and byte-identical application payload files.

## Real durable-staging gate — issue #856

- [ ] Materialize a real isolated durable-staging target.
- [ ] Use Sprint216 staging artifact `10603323419`.
- [ ] For cPanel no-SSH, ensure operator tooling is bound compatibly to the Sprint216 release before execution.
- [ ] Run truthful target qualification.
- [ ] Produce the exact Sprint208 target candidate.
- [ ] Obtain separate deployment authority <=900 seconds.
- [ ] Generate exact deployment plan.
- [ ] Execute guarded staging deployment.
- [ ] Qualify deployment evidence as `DEPLOYED_VERIFIED_NOT_SELECTED`.

## Production promotion gate

- [ ] Use Sprint216 Production candidate `10603358335`.
- [ ] Require same-source verified staging evidence.
- [ ] Materialize/qualify exact real Production target.
- [ ] Prepare exact Production deployment authority request.
- [ ] Obtain separate Production deployment authority <=900 seconds.
- [ ] Dark-deploy and verify rollback/health/provenance.
- [ ] Qualify only to `PRODUCTION_DEPLOYED_VERIFIED_NOT_ACTIVATED`.
- [ ] Keep Production business/traffic activation separately blocked until explicitly delivered and authorized.

## Still prohibited

No migration #27 execution, permission provisioning, target selection, producer dispatch, Final Shift Close activation, Technical Preview activation, Production traffic activation, or updater activation is authorized by Sprint216 repository merge.

Author by Lab | zefry

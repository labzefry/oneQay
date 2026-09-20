# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint212 closed canonically
**Canonical engineering commit:** `7adc0f34bbf1646400c344e7c6d1f89324db61d1`
**Engineering PR:** #859
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint212 completed

- [x] Confirm shared-hosting/cPanel support is a declared installer requirement.
- [x] Prove no implemented cPanel no-SSH target qualification bridge existed.
- [x] Add strict cPanel no-SSH observed target-profile schema.
- [x] Add private Cron/PHP CLI host inspector.
- [x] Fail closed on PHP/runtime/path/private-binding/symlink/atomic-rename incompatibility.
- [x] Ensure secret values never appear in profile/candidate output.
- [x] Bridge qualified cPanel profiles into the existing Sprint208 `OPERATOR_TARGET_CANDIDATE`.
- [x] Prove Sprint208 authority-request compatibility in dedicated regression.
- [x] Preserve application runtime source untouched.
- [x] Qualify final head `d3f771daabcf9263069e6b7b23af6302b06dab45` at 91/91 SUCCESS.
- [x] Product Owner merge authority SUCCESS.
- [x] Engineering squash `7adc0f34bbf1646400c344e7c6d1f89324db61d1`.
- [x] Preserve operational NO-GO unchanged.

## Next operational blocker — issue #856

- [ ] Materialize a real isolated non-production target.
- [ ] For cPanel no-SSH: create the private target tree and `0600` bindings through File Manager.
- [ ] For cPanel no-SSH: run the Sprint212 inspector through a one-shot Cron/PHP CLI job.
- [ ] Reject the host if required Cron/PHP CLI/extensions/symlink/atomic rename/document-root isolation are unavailable.
- [ ] Produce truthful Sprint208 `OPERATOR_TARGET_CANDIDATE` from the observed profile.
- [ ] Produce exact deployment-authority request bound to the target and Sprint211 published artifact.
- [ ] Obtain separately issued external deployment authority, lifetime <= 900 seconds.
- [ ] Qualify authority with approval token through the governed private channel.
- [ ] Generate the exact Sprint207 operator deployment plan.
- [ ] Execute externally against the real target.
- [ ] Produce Sprint209 deployment evidence from actual readback/health/rollback.
- [ ] Configure protected producer bindings and run trusted attestation/ingestion.
- [ ] Keep any accepted target unselected until separate target-selection authority exists.

## Still prohibited

No real target creation/deployment, migration #27 execution, permission provisioning, target selection, producer dispatch, Final Shift Close activation, Technical Preview activation, Production activation, or updater activation is authorized by Sprint212.

Author by Lab | zefry

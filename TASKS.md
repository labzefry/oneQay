# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint204 closed canonically  
**Canonical engineering commit:** `a5672b315a320092c6fa8cc74d984cb70f4e18ae`  
**Engineering PR:** #841  
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint204 completed

- [x] Preserve Sprint203 bounded merchant-core staging bridge and historical repository runtime guards.
- [x] Establish canonical qualification identity `durable-staging` without converting the historical `staging` alias into qualification authority.
- [x] Add authenticated read-only `GET /internal/oneqay/durable-runtime/readiness`.
- [x] Require exact runtime identity plus `ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED=true`.
- [x] Emit the existing readiness contract shape with exact source/artifact provenance fields and fail-closed capability declarations.
- [x] Keep bearer token secret and responses `no-store, private`.
- [x] Preserve Final Shift Close inactive state and Production denial.
- [x] Preserve downstream producer/ingestion/selection workflows as undispatched.
- [x] Qualify final engineering head `f849d3902c026105ae9e21088b45a05b6d71dcaa` at 88/88 SUCCESS.
- [x] Product Owner merge authority SUCCESS.
- [x] Engineering squash `a5672b315a320092c6fa8cc74d984cb70f4e18ae`.
- [x] Preserve operational NO-GO unchanged.

## Next material blocker

- [ ] Materialize or identify an actual isolated non-synthetic `durable-staging` environment outside repository source.
- [ ] Provide exact running source commit and artifact SHA-256 from that environment.
- [ ] Provide durable persistence/session/authorization/transaction/POS behavior.
- [ ] Provide authenticated configuration mutation with read-before-write/read-after verification.
- [ ] Provide non-mutating health attestation and verified rollback.
- [ ] Obtain separate operational authority before protected producer dispatch or target mutation.
- [ ] Run existing producer → ingestion → selection ordering only when the real target and authority prerequisites are satisfied.
- [ ] If qualification reveals a concrete source deficiency, open the next bounded engineering sprint for that deficiency only.

## Still prohibited

No target selection, deployment, migration #27 execution, permission provisioning, Final Shift Close activation, Technical Preview activation, Production activation, updater activation, or producer dispatch is authorized by Sprint204 source readiness.

Author by Lab | zefry

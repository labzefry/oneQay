# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint207 closed canonically
**Canonical engineering commit:** `2cfc55cf3dde9304b713ba9fb6f70509dd4dafda`
**Engineering PR:** #847
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint207 completed

- [x] Prove the repository-side operator planning gap after Sprint206.
- [x] Preserve Preview-only `SystemUpdate*` semantics and `NO_SCHEMA_CHANGE`.
- [x] Add a strict durable-staging operator target contract/schema.
- [x] Add a strict deterministic deployment-plan contract/schema.
- [x] Bind exact release/source/artifact/manifest identity to exact target environment and external authority.
- [x] Require durable database/session/authorization/transaction/POS capabilities.
- [x] Require authenticated configuration channel and read-before-write/read-after verification.
- [x] Require provenance readback, non-mutating health attestation, preserved rollback target, rollback verification, and deployment evidence.
- [x] Reject Production/synthetic targets, migration authority, target/artifact/authority drift, missing bindings/capabilities, root-wide deployment, traversal/dot segments, and filesystem escape/collision.
- [x] Produce deterministic secret-free deployment plans.
- [x] Prove the planner performs no deployment/runtime mutation.
- [x] Qualify final engineering head `b770e86b1a33e70a9272643f5abf04e1c150648c` at 86/86 SUCCESS.
- [x] Product Owner merge authority SUCCESS.
- [x] Engineering squash `2cfc55cf3dde9304b713ba9fb6f70509dd4dafda`.
- [x] Preserve operational NO-GO unchanged.

## Next material blocker

- [ ] Obtain separate operational authority before creating or deploying a real durable-staging environment.
- [ ] Provide the exact external target descriptor bound to the exact release/artifact and authority evidence.
- [ ] Execute the generated operator plan on an isolated non-production target.
- [ ] Bind external runtime configuration/secrets without embedding them in repository artifacts or planning evidence.
- [ ] Verify exact `ONEQAY_RUNNING_SOURCE_COMMIT`, `ONEQAY_RUNNING_ARTIFACT_SHA256`, runtime class, and environment ID.
- [ ] Establish durable persistence/session/authorization/transaction/POS prerequisites without Production data.
- [ ] Verify authenticated configuration readback, non-mutating health, and rollback evidence.
- [ ] Dispatch the protected attestation producer only after real target and authority prerequisites are satisfied.
- [ ] Preserve downstream ordering: attest → ingest → select-not-authorized → migration/permission evidence → later activation authority.
- [ ] Open another engineering sprint only if targeted discovery or real deployment exposes a concrete missing source capability.

## Still prohibited

No real environment deployment, migration #27 execution, permission provisioning, target selection, producer dispatch, Final Shift Close activation, Technical Preview activation, Production activation, or updater activation is authorized by Sprint207 engineering readiness.

Author by Lab | zefry

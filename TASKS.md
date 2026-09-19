# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint205 closed canonically
**Canonical engineering commit:** `5d9826e96adfb31d1e9b9389d222db180f84935c`
**Engineering PR:** #843
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint205 completed

- [x] Identify the missing governed artifact gap between Sprint204 source readiness and real durable staging.
- [x] Preserve the existing Technical Preview manifest and builder semantics.
- [x] Add a dedicated `durable-staging` release artifact contract.
- [x] Add strict durable-staging manifest schema and validator.
- [x] Bind artifact identity to exact source commit and exact SHA-256.
- [x] Include canonical migration source #1–#27 without executing migrations.
- [x] Exclude secret-bearing environment files, cached runtime configuration, `node_modules`, and test suites.
- [x] Bind runtime provenance keys required by Sprint204 without embedding values.
- [x] Prove deterministic artifact reproduction.
- [x] Preserve Preview `NO_SCHEMA_CHANGE` behavior.
- [x] Qualify final engineering head `c5b560a03bfec152f2860e7612b18517fb75434b` at 89/89 SUCCESS.
- [x] Product Owner merge authority SUCCESS.
- [x] Engineering squash `5d9826e96adfb31d1e9b9389d222db180f84935c`.
- [x] Preserve operational NO-GO unchanged.

## Next material blocker

- [ ] Obtain separate operational authority before creating or deploying a real staging environment.
- [ ] Materialize an isolated non-production runtime using the governed durable-staging artifact.
- [ ] Bind exact `ONEQAY_RUNNING_SOURCE_COMMIT` and `ONEQAY_RUNNING_ARTIFACT_SHA256` from deployed evidence.
- [ ] Configure durable persistence/session/authorization/transaction/POS prerequisites without Production data.
- [ ] Provide authenticated configuration mutation/readback, non-mutating health, and verified rollback.
- [ ] Dispatch the protected attestation producer only after the target and authority prerequisites are satisfied.
- [ ] Preserve selector ordering: attest → ingest → select-not-authorized → migration/permission evidence → later activation authority.
- [ ] If real deployment reveals a concrete source deficiency, open the next bounded engineering sprint for that deficiency only.

## Still prohibited

No real environment deployment, migration #27 execution, permission provisioning, target selection, producer dispatch, Final Shift Close activation, Technical Preview activation, Production activation, or updater activation is authorized by Sprint205 source readiness.

Author by Lab | zefry

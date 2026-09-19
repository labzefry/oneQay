# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint208 closed canonically
**Canonical engineering commit:** `20e835262f8163d45101ab00a818881421085d2e`
**Engineering PR:** #849
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint208 completed

- [x] Prove Sprint207 accepted opaque authority metadata without a canonical request/authority binding chain.
- [x] Add an authority-free durable-staging target-candidate contract.
- [x] Add deterministic exact-target deployment-authority request generation.
- [x] Bind request to exact release/source/artifact/manifest/environment/target-descriptor identity.
- [x] Add strict separate external deployment-authority contract.
- [x] Require approval-token verification without exposing token values.
- [x] Limit authority lifetime to at most 900 seconds.
- [x] Emit a private qualified operator target carrying request/authority evidence.
- [x] Make Sprint207 planner reject expired/not-yet-valid authority.
- [x] Make Sprint207 planner reconstruct and verify exact target-candidate fingerprint.
- [x] Preserve migration/Production/Technical Preview/updater/target-selection/producer denial.
- [x] Qualify final engineering head `67aa2e73a0433585a34b76df4c7bec97b578aefe` at 87/87 SUCCESS.
- [x] Product Owner merge authority SUCCESS.
- [x] Engineering squash `20e835262f8163d45101ab00a818881421085d2e`.
- [x] Preserve operational NO-GO unchanged.

## Next material blocker

- [ ] Obtain/materialize a real isolated non-production durable-staging environment.
- [ ] Prepare the exact operator target candidate for that runtime.
- [ ] Generate the exact Sprint208 deployment-authority request.
- [ ] Obtain separately issued short-lived authority bound to that request and target.
- [ ] Qualify authority using the approval token without persisting token plaintext.
- [ ] Generate and externally execute the Sprint207 operator deployment plan.
- [ ] Bind external runtime configuration/secrets without repository embedding.
- [ ] Verify exact running source/artifact/runtime/environment identity.
- [ ] Verify durable persistence/session/authorization/transaction/POS prerequisites.
- [ ] Verify configuration readback, non-mutating health, and rollback evidence.
- [ ] Dispatch protected attestation producer only after real target qualification.
- [ ] Preserve downstream ordering: attest → ingest → select-not-authorized → migration/permission evidence → later activation authority.
- [ ] Open another engineering sprint only if real deployment exposes a concrete missing source capability.

## Still prohibited

No real environment deployment, migration #27 execution, permission provisioning, target selection, producer dispatch, Final Shift Close activation, Technical Preview activation, Production activation, or updater activation is authorized by Sprint208 engineering readiness.

Author by Lab | zefry

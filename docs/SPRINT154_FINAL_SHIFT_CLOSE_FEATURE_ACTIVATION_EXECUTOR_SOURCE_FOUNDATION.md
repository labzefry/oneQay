# Sprint154 — Final Shift Close Feature Activation Executor Source Foundation

Author by Lab | zefry

## 1. Purpose

Sprint154 closes the smallest non-duplicative source gap after Sprint153 without fabricating an operational configuration channel. Canonical post-selection readiness requires a selected-target-bound feature activation executor after migration #27, same-target permission provisioning, and full durable dependency-envelope qualification. Repository discovery proved the final runtime provider is still restricted to `local/test/ci`, the feature-activation workflow is absent, and no repository-native Final Shift Close configuration-mutation transport has been materialized.

## 2. Bounded objective

`FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION_EXECUTOR_SOURCE_FOUNDATION`

Materialize a framework-independent, deterministic execution-plan qualifier for the future feature-activation executor. The qualifier is allowed to construct a plan only when all future prerequisites are exact and mutually bound: persisted selected target, migration #27 executed, permission `pos.shift.close` provisioned with no default grant, full Sprint150 dependency qualification for the same target, and a separate exact feature-activation authority bound to the selected target, dependency envelope, and executor source commit.

## 3. Fail-closed activation plan

The only valid plan uses `ONEQAY_POS_SHIFT_CLOSE_ENABLED` with the fixed ceremony `false -> true` and rollback `false`. Ordered execution semantics are frozen as:

1. `READ_FLAG_BEFORE`;
2. `WRITE_FLAG_TRUE`;
3. `READ_FLAG_AFTER_REQUIRE_TRUE`;
4. `NON_MUTATING_HEALTH_ATTESTATION`;
5. on any post-write failure, `WRITE_FLAG_FALSE`;
6. `VERIFY_ROLLBACK_READBACK_FALSE`.

Target/environment/runtime/source/artifact/readiness/selection identity, target-binding SHA-256, dependency-envelope SHA-256, and authority identity must remain exact. Unknown authority fields, target drift, dependency drift, embedded secrets, missing migration execution, or missing permission provisioning fail closed.

## 4. Why transport is deliberately not invented

Sprint110 requires a future durable target to prove an authenticated configuration-mutation channel, read-before/write/read-after support, non-mutating health attestation, and verified rollback. It does not define a repository-native protocol, endpoint, credential transport, or adapter for that channel. Sprint105/Sprint106 likewise recorded the runtime mutation channel as incomplete. Therefore Sprint154 does not invent an HTTP endpoint, shell path, hosting API, `.env` writer, credential format, or secret transport.

The source foundation records `concrete_configuration_transport=NOT_IMPLEMENTED` and `dispatch_state=NOT_PERFORMED`. The future workflow `.github/workflows/final-shift-close-feature-activation.yml` remains intentionally absent until a trustworthy target-bound transport can be materialized and qualified.

## 5. Frozen engineering envelope and qualification

Exactly six paths are owned by Sprint154:

1. `.github/workflows/sprint154-final-shift-close-feature-activation-executor-source-foundation-regression.yml`
2. `apps/web/app/Application/Pos/FinalShiftCloseFeatureActivationExecutionPlan.php`
3. `apps/web/tests/pos-final-shift-close-feature-activation-execution-plan.php`
4. `docs/SPRINT154_FINAL_SHIFT_CLOSE_FEATURE_ACTIVATION_EXECUTOR_SOURCE_FOUNDATION.md`
5. `ops/final-shift-close/FEATURE_ACTIVATION_EXECUTOR_SOURCE_FOUNDATION_CONTRACT.json`
6. `ops/final-shift-close/POST_SELECTION_DOWNSTREAM_READINESS.json`

Sorted newline-terminated envelope SHA-256:

`35499fbb12404b3ab5f25de924d4528060f4cf4eb362091b116e5faf3c3766c3`

The active regression must lint and execute the new plan qualifier, validate the machine contract, lock the exact six-path envelope, preserve provider `local/test/ci`, and prove canonical state/selection/downstream operational NO-GO boundaries remain unchanged.

## 6. Operational boundary and next gate

Sprint154 is source-only. It does not persist a target, dispatch capability/dependency producers, execute migration #27, provision permissions, widen the Final Shift Close runtime allowlist, mutate `ONEQAY_POS_SHIFT_CLOSE_ENABLED`, create runtime credentials, dispatch an activation executor, grant operational activation authority, deploy/release, activate Technical Preview/Production, or activate the updater.

After Sprint154 closure, the next bounded discovery must determine the smallest trustworthy way to materialize the selected-target-bound configuration-mutation transport / dispatchable feature-activation executor. No such transport or operational authority is implied by this source foundation.

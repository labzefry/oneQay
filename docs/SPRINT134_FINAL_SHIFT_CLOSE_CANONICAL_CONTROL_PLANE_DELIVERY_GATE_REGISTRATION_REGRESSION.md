# Sprint134 — Final Shift Close Canonical Control-Plane Delivery Gate Registration Regression

Author by Lab | zefry

## Bounded objective

Sprint134 adds CI-only executable proof for the remaining non-duplicative Final Shift Close control-plane provider delivery-gate registration gap after Sprint131 qualified middleware positive paths, Sprint132 qualified direct controller positive paths, and Sprint133 qualified direct controller fail-closed mappings.

Repository-native control-plane regressions already prove route absence for disabled delivery and for enabled delivery with empty, below-minimum, and above-maximum tokens. Sprint134 therefore does not duplicate those cases. Its new executable responsibility is the missing provider-level consequence of the Sprint130 canonical character policy: a token whose length is canonically valid but whose character set is invalid must keep both Final Shift Close control-plane routes unregistered.

Execution mode:

`CI_ONLY_SYNTHETIC_DELIVERY_GATE_REGISTRATION_REGRESSION`

## Delivery-gate qualification

Aggregate Sprint134 qualification requires both the preserved historical registration regressions and the new Sprint134 regression.

Historical owned evidence remains responsible for proving:

- manifest-materialization delivery is absent when its `enabled` configuration is `false`;
- manifest-materialization delivery is absent when enabled with an empty, below-minimum, or above-maximum token;
- runtime DB-binding-attestation delivery is absent when its `enabled` configuration is `false`;
- runtime DB-binding-attestation delivery is absent when enabled with an empty, below-minimum, or above-maximum token.

The new Sprint134 executable regression is responsible only for the previously uncovered canonical character-policy consequence:

- construct a token exactly at the canonical minimum length while containing a character forbidden by `FinalShiftCloseRuntimeControlPlaneTokenPolicy`;
- enable both Final Shift Close control-plane delivery providers only inside the isolated test process;
- prove the canonical token policy rejects that fixture because of its character set rather than its length;
- bootstrap the application without sending a control-plane request;
- inspect the in-process route collection and prove both the manifest-materialization and runtime DB-binding-attestation named routes are absent;
- prove the production manifest writer, manifest materializer, runtime database identity reader, runtime DB-binding attestation service, and both control-plane controllers remain unresolved;
- never resolve or invoke the filesystem runtime manifest writer, Laravel `oneqay` database connection, canonical runtime manifest, or any operational runtime target.

Together, historical evidence plus the Sprint134 regression cover the unqualified delivery states `DISABLED`, `EMPTY`, `BELOW_CANONICAL_MINIMUM`, `ABOVE_CANONICAL_MAXIMUM`, and `DISALLOWED_CHARACTER` without duplicating already-owned executable coverage.

## Canonical token-policy boundary

Sprint134 does not redefine the runtime control-plane token policy. Sprint130 remains the canonical owner of token validity semantics, including minimum length, maximum length, allowed character set, exact Bearer handling, constant-time comparison, and endpoint-specific credential disposition.

The Sprint134 fixture is deliberately exactly `MINIMUM_LENGTH` bytes long and includes one disallowed character. The regression calls the existing canonical policy to establish fixture disposition; it does not introduce a second validator, alternate character policy, relaxed length policy, fallback credential path, or test-only registration bypass.

## Synthetic isolation boundary

The Sprint134 regression boots the normal application only after test-owned environment values enable both delivery providers with the deliberately invalid token fixture. It invokes the HTTP kernel bootstrap lifecycle solely to execute provider boot logic, then inspects the router collection directly.

The active Sprint134 test must not send any request to a Final Shift Close control-plane route. It must not:

- call the manifest-materialization controller;
- call the runtime DB-binding-attestation controller;
- resolve `FinalShiftCloseRuntimeBindingManifestWriter` through the production provider binding;
- resolve `FinalShiftCloseRuntimeBindingManifestMaterializer` through the production provider binding;
- resolve `FinalShiftCloseRuntimeDatabaseIdentityReader` through the production provider binding;
- resolve `FinalShiftCloseRuntimeDbBindingAttestation` through the production provider binding;
- instantiate the filesystem runtime manifest writer as part of qualification;
- open the Laravel `oneqay` database connection;
- read or write the canonical runtime binding manifest;
- mutate durable target selection or any operational state file.

A valid-token positive registration case is intentionally outside Sprint134. Existing historical regressions already own valid-gate middleware behavior, and widening Sprint134 into positive operational registration would move the boundary toward production side-effect adapters without adding the missing fail-closed evidence.

## Non-duplication boundary

Sprint134 does not repeat:

- Sprint130 token-policy semantics;
- the historical manifest-materialization control-plane regression for disabled, empty, short, oversized, or valid-token credential behavior;
- the historical runtime DB-binding-attestation control-plane regression for disabled, empty, short, oversized, or valid-token credential behavior;
- Sprint131 middleware positive-path qualification;
- Sprint132 direct controller positive-path behavior;
- Sprint133 direct controller fail-closed translation and information containment;
- application-service materialization or DB-binding-attestation business-rule qualification;
- provider registration source existence already established by historical registration sprints.

Sprint134 closes only the missing cross-provider route-registration consequence for a length-valid token containing a disallowed character, while the active workflow retains the historical registration regressions as aggregate evidence for the other delivery-gate classes.

## Successor compatibility

Sprint133 becomes historical once Sprint134 exists. Its workflow must stop owning the full successor PR envelope while continuing to validate its owned controller fail-closed invariant, Sprint132 controller positive path, Sprint131 middleware positive path, fail-closed security regressions, exact-head checkout, synthetic isolation, and canonical operational NO-GO boundary.

Sprint134 alone owns the exact Sprint134 source envelope.

## Exact Sprint134 source envelope

Sprint134 contains exactly five paths:

1. `.github/workflows/sprint133-final-shift-close-canonical-control-plane-controller-fail-closed-regression.yml`
2. `.github/workflows/sprint134-final-shift-close-canonical-control-plane-delivery-gate-registration-regression.yml`
3. `apps/web/tests/final-shift-close-runtime-control-plane-delivery-gate-registration-regression.php`
4. `docs/SPRINT134_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_DELIVERY_GATE_REGISTRATION_REGRESSION.md`
5. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_DELIVERY_GATE_REGISTRATION_REGRESSION_CONTRACT.json`

Source-envelope SHA-256: `73b87b460ef349a05ab801d10fc6dd4a19d056625f4a042ff621ab96a12d2c0e`.

Hash semantics:

- sort paths lexicographically;
- concatenate one path per line;
- include the final newline;
- compute SHA-256 over the resulting UTF-8 bytes.

## Canonical no-go boundary

`SPRINT134_VALID_OPERATIONAL_CONTROL_PLANE_REQUEST = NOT_PERFORMED`

`SPRINT134_REAL_ROUTE_INVOCATION = NOT_PERFORMED`

`SPRINT134_POSITIVE_OPERATIONAL_ROUTE_REGISTRATION = NOT_REQUIRED`

`SPRINT134_PRODUCTION_SIDE_EFFECT_ADAPTER_RESOLUTION = NOT_PERFORMED`

`SPRINT134_REAL_FILESYSTEM_ADAPTER_INVOCATION = NOT_PERFORMED`

`SPRINT134_CANONICAL_RUNTIME_MANIFEST_WRITE = NOT_PERFORMED`

`SPRINT134_REAL_DATABASE_CONNECTION = NOT_PERFORMED`

`SPRINT134_REAL_DATABASE_IDENTITY_READ = NOT_PERFORMED`

`SPRINT134_RUNTIME_CONFIGURATION_MUTATION = NOT_PERFORMED`

`SPRINT134_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SPRINT134_CANONICAL_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SPRINT134_OPERATIONAL_RUNTIME_DB_BINDING_ATTESTATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

## Forbidden actions

Sprint134 must not:

- deploy or release any application artifact;
- activate Technical Preview or Production;
- activate the updater;
- execute migration #27;
- provision runtime permissions;
- select, authorize, or activate a durable runtime target;
- provision a usable operational control-plane token;
- invoke a valid operational control-plane request;
- materialize the canonical runtime binding manifest;
- connect to the real runtime database for qualification;
- mutate runtime allowlists, feature activation state, or operational state files;
- introduce a route-registration bypass or fake-green CI path.

## Closure condition

Sprint134 is qualified only when the exact five-path envelope and fingerprint match, historical control-plane regressions continue proving route absence for disabled/empty/short/oversized delivery states, the new Sprint134 executable regression proves both providers remain route-absent for the length-valid disallowed-character state, production side-effect services remain unresolved and uninvoked, Sprint133 and earlier owned regressions remain successful under successor-compatible workflows, required repository checks are terminal SUCCESS on the exact PR HEAD, repository-native exact-head Product Owner merge authority is valid, the final race check is clean, and post-merge operational state remains unchanged.

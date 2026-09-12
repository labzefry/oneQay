# Sprint135 — Final Shift Close Canonical Control-Plane Positive Registration Metadata Regression

Author by Lab | zefry

## Bounded objective

Sprint135 adds CI-only executable proof for the smallest remaining non-duplicative control-plane registration gap after Sprint134: when both Final Shift Close control-plane providers receive the same canonical valid token inside an isolated test process, each expected route must be registered with the exact controller action, method, path, throttle, and token middleware while registration itself remains inert and does not resolve or invoke production side-effect services.

Execution mode:

`CI_ONLY_SYNTHETIC_POSITIVE_REGISTRATION_METADATA_REGRESSION`

## Why this is not duplicate work

Historical manifest-materialization and DB-binding-attestation control-plane regressions already prove provider-local valid-token route presence and unauthenticated credential rejection. Sprint130 owns canonical token validity. Sprint131 owns middleware positive-path behavior. Sprint132 owns direct controller positive-path behavior. Sprint133 owns direct controller fail-closed translation. Sprint134 owns the cross-provider route-absence consequence for a length-valid token containing a disallowed character.

Sprint135 therefore does not re-own those behaviors. Its new executable responsibility is narrower:

- use one token exactly at `FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH` that the canonical policy accepts;
- enable both delivery providers only inside the isolated test process;
- bootstrap provider registration without sending a Final Shift Close control-plane request;
- prove both named routes are simultaneously present;
- prove each route points to the intended invokable controller class;
- prove exact route URI and HTTP method disposition;
- prove the expected throttle and token middleware remain attached;
- prove route registration alone leaves production writer, materializer, database identity reader, DB-binding attestation service, and both controllers unresolved.

## Synthetic positive-registration boundary

The token used by Sprint135 is a test-owned synthetic fixture. It is not provisioned to any runtime and is not an operational credential.

Sprint135 performs positive registration only in the in-process test application. It does not:

- send a request to either control-plane route;
- invoke either controller;
- resolve the production filesystem manifest writer;
- resolve the production manifest materializer;
- resolve the production runtime database identity reader;
- resolve the production DB-binding attestation service;
- connect to the Laravel `oneqay` database;
- read or write the canonical runtime binding manifest;
- select or activate a durable runtime target;
- execute migration #27;
- provision a runtime permission;
- activate Final Shift Close, Technical Preview, Production, deployment, release, or updater state.

## Canonical route metadata qualified by Sprint135

### Manifest materialization

- route name: `internal.final-shift-close.runtime-binding-manifest.materialize`;
- URI: `internal/final-shift-close/runtime-binding-manifest/materialize`;
- method includes `POST` and exposes no `GET`;
- action: `FinalShiftCloseRuntimeBindingManifestMaterializationController`;
- throttle: `throttle:1,1`;
- middleware: `RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware`.

### Runtime DB-binding attestation

- route name: `internal.final-shift-close.runtime-db-binding-attestation`;
- URI: `internal/final-shift-close/runtime-db-binding-attestation`;
- methods: `GET,HEAD`;
- action: `FinalShiftCloseRuntimeDbBindingAttestationController`;
- throttle: `throttle:2,1`;
- middleware: `RequireFinalShiftCloseRuntimeBindingTokenMiddleware`.

## Inert-registration proof

After the application kernel bootstrap and route-collection inspection, Sprint135 requires these services to remain unresolved:

- `FinalShiftCloseRuntimeBindingManifestWriter`;
- `FinalShiftCloseRuntimeBindingManifestMaterializer`;
- `FinalShiftCloseRuntimeDatabaseIdentityReader`;
- `FinalShiftCloseRuntimeDbBindingAttestation`;
- `FinalShiftCloseRuntimeBindingManifestMaterializationController`;
- `FinalShiftCloseRuntimeDbBindingAttestationController`.

This proves that route registration metadata can be qualified without crossing into production side-effect adapter resolution.

## Successor compatibility

Sprint134 is historical from Sprint135 onward. Its workflow remains successor-compatible and continues owning the disallowed-character route-absence regression plus its historical aggregate registration evidence and operational NO-GO assertions. Sprint135 does not restore Sprint134 full-PR envelope ownership.

Sprint130–Sprint134 regressions remain authoritative for their owned invariants.

## Exact Sprint135 source envelope

Sprint135 contains exactly four paths:

1. `.github/workflows/sprint135-final-shift-close-canonical-control-plane-positive-registration-metadata-regression.yml`
2. `apps/web/tests/final-shift-close-runtime-control-plane-positive-registration-metadata-regression.php`
3. `docs/SPRINT135_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_POSITIVE_REGISTRATION_METADATA_REGRESSION.md`
4. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_POSITIVE_REGISTRATION_METADATA_REGRESSION_CONTRACT.json`

Source-envelope SHA-256: `628d99da2ce7f65938dc12df387c829748e36c4a8d8cab18a08eb5402fcf78fc`.

Hash semantics:

- sort paths lexicographically;
- concatenate one path per line;
- include the final newline;
- compute SHA-256 over the resulting UTF-8 bytes.

## Canonical no-go boundary

`SPRINT135_SYNTHETIC_POSITIVE_ROUTE_REGISTRATION = PERFORMED_IN_TEST_PROCESS_ONLY`

`SPRINT135_OPERATIONAL_POSITIVE_ROUTE_REGISTRATION = NOT_PERFORMED`

`SPRINT135_VALID_OPERATIONAL_CONTROL_PLANE_REQUEST = NOT_PERFORMED`

`SPRINT135_REAL_ROUTE_INVOCATION = NOT_PERFORMED`

`SPRINT135_CONTROLLER_INVOCATION = NOT_PERFORMED`

`SPRINT135_PRODUCTION_SIDE_EFFECT_ADAPTER_RESOLUTION = NOT_PERFORMED`

`SPRINT135_REAL_FILESYSTEM_ADAPTER_INVOCATION = NOT_PERFORMED`

`SPRINT135_CANONICAL_RUNTIME_MANIFEST_WRITE = NOT_PERFORMED`

`SPRINT135_REAL_DATABASE_CONNECTION = NOT_PERFORMED`

`SPRINT135_REAL_DATABASE_IDENTITY_READ = NOT_PERFORMED`

`SPRINT135_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

## Closure condition

Sprint135 is qualified only when the exact four-path envelope and fingerprint match, the synthetic canonical-minimum valid-token fixture is accepted by Sprint130 policy, both control-plane routes are simultaneously registered with their exact owned metadata, all listed production side-effect services remain unresolved, no control-plane request is sent, historical Sprint130–Sprint134 regressions remain successful, required repository checks are terminal SUCCESS on the exact PR HEAD, repository-native exact-head Product Owner merge authority is valid, the final race check is clean, and post-merge operational state remains unchanged.

# Sprint110 Final Shift Close Durable Runtime Readiness Attestation

Author by Lab | zefry

## Purpose

Sprint110 materializes a fail-closed, read-only source contract for qualifying a future isolated non-production durable application runtime before Final Shift Close runtime allowlist expansion can be considered.

Sprint109 established that no currently canonical target qualifies. Sprint110 does not create or select a replacement environment. It creates the verifier that a future target must satisfy.

## Canonical disposition

- `DURABLE_RUNTIME_READINESS_VERIFIER = MATERIALIZED_SOURCE_ONLY`
- `DURABLE_ACTIVATION_TARGET_SELECTION = BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`
- `SELECTED_TARGET = NONE`
- `RUNTIME_ALLOWLIST_CHANGE = NOT_IMPLEMENTED`
- `ENVIRONMENT_CREATION = NOT_PERFORMED`
- `ENVIRONMENT_DEPLOYMENT = NOT_PERFORMED`
- `MIGRATION_27_EXECUTION = NOT_PERFORMED`
- `PERMISSION_PROVISIONING = NONE`
- `FEATURE_ACTIVATION = INACTIVE`
- `FEATURE_ACTIVATION_EVIDENCE_PRODUCER = NOT_IMPLEMENTED`
- `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`
- `PRODUCTION_AUTHORITY = NOT_GRANTED`
- `UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

## Why this precedes runtime allowlist expansion

The canonical Final Shift Close provider and durable repository both currently restrict operational runtime classes to `local`, `test`, and `ci`.

Adding another runtime class before a real target can prove its durable serving envelope would turn a readiness task into activation-envelope mutation. Sprint110 therefore leaves those source guards unchanged.

## Readiness attestation contract

A future candidate must provide a secret-free attestation consumed by `FinalShiftCloseDurableRuntimeReadiness`.

The attestation must prove all of the following:

1. an explicit stable environment identifier;
2. an explicit stable runtime class that is not local, test, CI, synthetic preview, or Production;
3. runtime model `NON_SYNTHETIC_DURABLE_RUNTIME`;
4. isolation `ISOLATED_NON_PRODUCTION`;
5. the target is a serving application runtime, not a CI/rehearsal foundation;
6. synthetic fixtures are not the persistence model;
7. Production traffic is not served by the target;
8. durable persistence is enabled;
9. durable first-party session control is enabled;
10. durable authorization is enabled;
11. durable transaction boundaries are enabled;
12. durable POS persistence is enabled;
13. the exact running source commit is a lowercase 40-hex commit identifier;
14. the exact running application artifact has a lowercase SHA-256 digest;
15. configuration mutation uses an authenticated channel;
16. the channel supports read-before-write/read-after verification;
17. non-mutating health attestation is available;
18. feature-flag rollback can be verified;
19. activation requires separate explicit authority;
20. Final Shift Close remains `INACTIVE` during target-readiness qualification;
21. the attestation embeds no secrets.

Unknown or missing fields fail closed.

## Explicitly rejected runtime classes

The source validator rejects these runtime classes as future durable activation targets:

- `local`
- `test`
- `testing`
- `ci`
- `preview`
- `synthetic-preview`
- `production`
- `prod`

This does not rename or mutate any current runtime. It prevents a synthetic, developer, CI, or Production environment from being mislabeled as the first isolated durable activation target.

## Executable qualification

`apps/web/tests/pos-final-shift-close-durable-runtime-readiness.php` proves that:

- the canonical future durable target shape qualifies;
- all existing local/test/CI/preview/Production classes fail;
- synthetic runtime evidence fails;
- non-serving staging foundations fail;
- missing durable session/authorization/transaction/POS persistence fails;
- unauthenticated configuration mutation fails;
- missing read-before-write/read-after verification fails;
- missing health attestation or rollback fails;
- malformed source/artifact provenance fails;
- target readiness cannot mark Final Shift Close active;
- embedded secrets fail;
- missing and unexpected fields fail;
- `requireQualified()` throws a canonical fail-closed rejection.

## Boundaries

Sprint110 does not:

- create a staging environment;
- deploy an application artifact;
- change `ONEQAY_RUNTIME_CLASS` behavior;
- extend the Final Shift Close runtime allowlist;
- change `FinalShiftCloseServiceProvider` delivery guards;
- change `LaravelCloseShiftRepository` operational guards;
- execute migration #27;
- grant `pos.shift.close`;
- enable `ONEQAY_POS_SHIFT_CLOSE_ENABLED`;
- produce activation evidence;
- activate Technical Preview or Production;
- enable the updater;
- perform release or DNS actions.

A later sprint may select a concrete target only after real evidence satisfying this verifier exists. Runtime allowlist expansion remains a separately bounded and separately authorized source change.

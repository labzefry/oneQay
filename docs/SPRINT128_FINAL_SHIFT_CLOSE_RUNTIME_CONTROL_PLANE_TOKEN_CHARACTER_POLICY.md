# Sprint128 — Final Shift Close Runtime Control Plane Token Character Policy

Author by Lab | zefry

## Objective

Sprint128 repairs the bearer-token character policy used by the Final Shift Close runtime binding manifest materialization and runtime DB-binding attestation control planes.

The previous regular expression placed `-` inside a range position. The resulting character class did not precisely represent the documented policy. Sprint128 replaces it with the exact bounded class `[A-Za-z0-9._~+=\/-]{32,512}` and applies that policy consistently to configured tokens and presented bearer tokens.

## Security Semantics

Allowed token characters are limited to:

- ASCII letters `A-Z` and `a-z`;
- digits `0-9`;
- `.`, `_`, `~`, `+`, `=`, `/`, `-`.

Token length remains 32 through 512 characters inclusive.

Characters including `:`, `;`, `<`, `>`, `@`, spaces, and tabs are rejected.

## Materialization Control Plane

The materialization provider now requires both the existing length boundary and the exact character policy before registering its route.

The materialization middleware independently validates the configured expected token with the same policy. Its historical failure semantics remain unchanged:

- invalid expected token: HTTP 503;
- missing, malformed, disallowed-character, or mismatched bearer: HTTP 401.

Sprint128 does not send a valid bearer credential to the materialization route and does not invoke its controller or materializer.

## DB-Binding Attestation Control Plane

The DB-attestation provider now requires the same exact configured-token policy before registering its route.

The DB-attestation middleware independently validates both expected and bearer token character policy while preserving the existing cloaked disposition:

- invalid expected token: HTTP 404;
- missing, malformed, disallowed-character, or mismatched bearer: HTTP 404.

Sprint128 does not send a valid bearer credential to the DB-attestation route and does not invoke attestation.

## Executable Regression

CI proves:

- the exact regex accepts every documented allowed character, including literal hyphen;
- the exact regex rejects `:`, `;`, `<`, `>`, `@`, spaces, and tabs;
- a valid 32-character configured token containing hyphen can register each control-plane route under synthetic CI configuration;
- a 32-character configured token containing colon cannot register either route;
- materialization middleware rejects an invalid expected token with HTTP 503 and an invalid bearer with HTTP 401;
- DB-attestation middleware rejects the same conditions with cloaked HTTP 404;
- no valid control-plane request, controller invocation, materialization, or DB attestation is performed.

## Source Preservation

Sprint128 does not modify:

- either control-plane configuration file;
- application or shared bootstrap;
- routes or controllers;
- runtime binding manifest materializer or writer;
- runtime DB-binding attestation service or database identity reader;
- database migrations;
- canonical `STATE.json`;
- durable activation target selection.

Sprint120 through Sprint127 remain active through exact successor-compatibility qualification.

## Canonical Boundaries

`SPRINT128_TOKEN_CHARACTER_POLICY = EXACT_BOUNDED_ASCII_CLASS`

`SPRINT128_CONFIGURED_TOKEN_VALIDATION = PROVIDER_AND_MIDDLEWARE`

`SPRINT128_BEARER_TOKEN_VALIDATION = MIDDLEWARE`

`SPRINT128_VALID_CONTROL_PLANE_REQUEST = NOT_PERFORMED`

`SPRINT128_CONTROLLER_INVOCATION = NOT_PERFORMED`

`SPRINT128_RUNTIME_CONFIGURATION_MUTATION = NOT_PERFORMED`

`SPRINT128_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SPRINT128_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SPRINT128_RUNTIME_DB_BINDING_ATTESTATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

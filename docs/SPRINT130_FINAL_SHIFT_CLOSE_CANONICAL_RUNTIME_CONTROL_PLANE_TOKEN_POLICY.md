# Sprint130 — Final Shift Close Canonical Runtime Control-Plane Token Policy

Author by Lab | zefry

## Bounded objective

Sprint130 establishes one canonical source of truth for Final Shift Close runtime control-plane token validation and Bearer credential parsing.

The canonical policy is implemented in:

`apps/web/app/Application/Pos/FinalShiftCloseRuntimeControlPlaneTokenPolicy.php`

Both runtime control-plane providers and both security middleware now consume this policy. `hash_equals()` remains in the security-sensitive middleware so credential comparison behavior is unchanged.

## Canonical token policy

- minimum length: `32`
- maximum length: `512`
- allowed characters: `[A-Za-z0-9._~+=\/-]`
- literal hyphen is allowed
- `:`, `;`, `<`, `>`, `@`, whitespace, and tab are rejected
- Bearer parsing accepts only the exact `Bearer ` scheme followed by a canonical valid token
- no token is persisted to source
- no token is provisioned by Sprint130
- no plaintext secret logging is introduced

## Preserved control-plane disposition

Materialization control plane:

- invalid configured/expected token → HTTP `503`
- missing, malformed, invalid-character, or mismatched bearer → HTTP `401`
- throttle remains `1,1`

DB binding attestation control plane:

- invalid configured/expected token → cloaked HTTP `404`
- missing, malformed, invalid-character, or mismatched bearer → cloaked HTTP `404`
- throttle remains `2,1`

Default-off route-registration semantics remain unchanged for both control planes.

## Historical successor compatibility

Sprint130 changes where the token-policy invariant is implemented, so only historical workflows whose owned token-policy assertions referenced the former duplicated source are compatibility-corrected:

- Sprint119
- Sprint123
- Sprint124
- Sprint125
- Sprint126
- Sprint127
- Sprint128
- Sprint129

Those workflows continue to validate their owned invariant against the canonical policy source and security middleware. Historical full-PR envelope locking is not reintroduced. The exact Sprint130 envelope is owned only by the active Sprint130 workflow.

## Exact Sprint130 source envelope

Sprint130 contains exactly 17 paths:

1. `.github/workflows/sprint119-final-shift-close-runtime-db-binding-attestation.yml`
2. `.github/workflows/sprint123-final-shift-close-runtime-binding-manifest-control-plane-registration.yml`
3. `.github/workflows/sprint124-final-shift-close-runtime-db-binding-attestation-control-plane-registration.yml`
4. `.github/workflows/sprint125-final-shift-close-runtime-db-binding-attestation-control-plane-regression.yml`
5. `.github/workflows/sprint126-final-shift-close-runtime-binding-manifest-control-plane-token-hardening.yml`
6. `.github/workflows/sprint127-final-shift-close-runtime-binding-manifest-control-plane-regression.yml`
7. `.github/workflows/sprint128-final-shift-close-runtime-control-plane-token-character-policy.yml`
8. `.github/workflows/sprint129-final-shift-close-historical-regression-successor-compatibility.yml`
9. `.github/workflows/sprint130-final-shift-close-canonical-runtime-control-plane-token-policy.yml`
10. `apps/web/app/Application/Pos/FinalShiftCloseRuntimeControlPlaneTokenPolicy.php`
11. `apps/web/app/Delivery/Http/Middleware/RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware.php`
12. `apps/web/app/Delivery/Http/Middleware/RequireFinalShiftCloseRuntimeBindingTokenMiddleware.php`
13. `apps/web/app/Providers/FinalShiftCloseRuntimeBindingManifestMaterializationServiceProvider.php`
14. `apps/web/app/Providers/FinalShiftCloseRuntimeDbBindingAttestationServiceProvider.php`
15. `apps/web/tests/final-shift-close-runtime-control-plane-token-character-policy.php`
16. `docs/SPRINT130_FINAL_SHIFT_CLOSE_CANONICAL_RUNTIME_CONTROL_PLANE_TOKEN_POLICY.md`
17. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_TOKEN_POLICY_CONTRACT.json`

Source-envelope SHA-256: `a9250384c5a6a6d57664f11587bbe9bc4a981f149d1e44aace2e95aaca94fb24`.

## Regression evidence required

Sprint130 CI proves:

- lengths 32 and 512 are accepted;
- lengths 31 and 513 are rejected;
- every canonical allowed character is accepted, including literal hyphen;
- `:`, `;`, `<`, `>`, `@`, whitespace, and tab are rejected;
- both providers remain default-off and only register routes with a valid canonical token;
- materialization middleware preserves HTTP 503/401 dispositions;
- DB attestation middleware preserves cloaked HTTP 404 dispositions;
- mismatched valid-shape bearer credentials are rejected;
- `hash_equals()` remains the credential comparison mechanism;
- no valid operational control-plane request reaches a controller.

## Canonical no-go boundary

`SPRINT130_VALID_CONTROL_PLANE_REQUEST = NOT_PERFORMED`

`SPRINT130_CONTROLLER_INVOCATION = NOT_PERFORMED`

`SPRINT130_RUNTIME_CONFIGURATION_MUTATION = NOT_PERFORMED`

`SPRINT130_RUNTIME_TOKEN_PROVISIONING = NOT_PERFORMED`

`SPRINT130_RUNTIME_MANIFEST_MATERIALIZATION = NOT_PERFORMED`

`SPRINT130_RUNTIME_DB_BINDING_ATTESTATION = NOT_PERFORMED`

`SELECTED_TARGET = NONE`

`DB_BINDING_PRODUCER_DISPATCH = NOT_PERFORMED`

`MIGRATION_27_EXECUTION = NOT_PERFORMED`

`PERMISSION_PROVISIONING = NONE`

`FEATURE_ACTIVATION = INACTIVE`

`DEPLOYMENT_AUTHORITY = NOT_GRANTED`

`TECHNICAL_PREVIEW = NO-GO`

`PRODUCTION = NO-GO`

`UPDATER = INACTIVE`

## Closure condition

Sprint130 is qualified only when the exact 17-path envelope and SHA-256 fingerprint match, canonical executable regression succeeds on the exact PR HEAD, all material historical successor regressions remain terminal SUCCESS, Product Owner merge authority qualifies the exact HEAD, final race check is clean, and post-merge operational state remains unchanged.

# Sprint137 — Final Shift Close Canonical Control Plane Authenticated HTTP Fail-Closed Regression

Author by Lab | zefry

## Objective

Close the smallest remaining non-duplicative Final Shift Close control-plane regression gap after Sprint136: prove that a canonical-valid synthetic bearer can traverse the registered route and token middleware to the real controller while downstream application failure is still translated fail-closed at the HTTP boundary for both control planes.

This sprint is CI-only and synthetic. It does not authorize or perform operational control-plane requests, runtime-token provisioning, migration #27 execution, permission provisioning, feature activation, durable-target selection, deployment, release, Technical Preview activation, Production activation, or updater activation.

## Canonical baseline

- Canonical post-Sprint136 repository checkpoint: `38084e1d61d0654f4b1235acb891f6c6912d8813`.
- Sprint136 engineering commit: `2a92a870d9c8388bdb9ac4f516e9d671237ce116`.
- Sprint136 proved canonical-valid authenticated HTTP success through real route → real token middleware → real controller while production side-effect adapters remained unresolved.
- Sprint133 owns direct-controller fail-closed translation and leakage regression.
- Historical control-plane regressions own disabled/invalid registration and missing/malformed credential rejection.

## New executable gap

No existing regression composes all of the following in one fail-closed proof:

1. canonical-valid synthetic bearer credential;
2. registered canonical internal route;
3. canonical token middleware;
4. real delivery controller resolved through the HTTP kernel;
5. synthetic downstream application failure;
6. exact fail-closed HTTP response contract;
7. production filesystem/database adapters remaining unresolved.

Sprint137 owns only this composed fail-closed HTTP boundary.

## Qualification cases

### Materialization control plane

The regression enables the materialization control plane with one canonical-minimum valid synthetic token, binds a test-owned materializer backed by a blocked synthetic target-selection fixture, and dispatches a real POST request through the application HTTP kernel.

Expected evidence:

- route is registered;
- bearer middleware accepts the canonical-valid synthetic credential;
- real materialization controller is resolved through HTTP dispatch;
- application failure is translated to HTTP `503` with exact body `{"error":"materialization_unavailable"}`;
- in-memory writer is never invoked;
- production manifest-writer binding is never resolved;
- response remains private/non-cacheable with robot exclusion;
- no selection path, fingerprint, blocked-state marker, or bearer token leaks into the response;
- canonical runtime-binding manifest path remains untouched.

### Database-binding attestation control plane

The regression enables the DB-attestation control plane with the same canonical-valid synthetic token, binds a test-owned attestation service backed by a valid synthetic manifest and a throwing in-memory database-identity reader, and dispatches a real GET request through the application HTTP kernel.

Expected evidence:

- route is registered;
- bearer middleware accepts the canonical-valid synthetic credential;
- real DB-attestation controller is resolved through HTTP dispatch;
- synthetic identity-reader failure is translated to HTTP `503` with exact body `{"error":"RUNTIME_DB_BINDING_ATTESTATION_UNAVAILABLE"}`;
- throwing synthetic reader is invoked exactly once;
- production database-identity-reader binding is never resolved;
- response remains private/non-cacheable, `nosniff`, and robot-excluded;
- no exception marker, synthetic DB identity, manifest path, or bearer token leaks into the response;
- no real database connection or identity read is performed.

## Frozen source envelope

Exactly four paths belong to Sprint137:

1. `.github/workflows/sprint137-final-shift-close-canonical-control-plane-authenticated-http-fail-closed-regression.yml`
2. `apps/web/tests/final-shift-close-runtime-control-plane-authenticated-http-fail-closed-regression.php`
3. `docs/SPRINT137_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_AUTHENTICATED_HTTP_FAIL_CLOSED_REGRESSION.md`
4. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_AUTHENTICATED_HTTP_FAIL_CLOSED_REGRESSION_CONTRACT.json`

Sorted newline-terminated path SHA-256:

`52a6fd043b7004add6d454feba69de97ae859a6a8e7a27cd97f25ca891516f5d`

## Historical ownership preserved

- Sprint130 — canonical control-plane token policy.
- Sprint131 — middleware positive path.
- Sprint132 — direct-controller positive path.
- Sprint133 — direct-controller fail-closed translation/leakage.
- Sprint134 — invalid delivery-gate route absence.
- Sprint135 — canonical-valid positive route-registration metadata and inertness.
- Sprint136 — canonical-valid authenticated HTTP positive path.

Sprint137 must not weaken or duplicate those ownership boundaries.

## Operational boundary

The authoritative operational state remains unchanged:

- migration #27: `NOT_EXECUTED`;
- permission provisioning: `NONE`;
- feature activation: `INACTIVE`;
- deployment authority: `NOT_GRANTED`;
- Technical Preview activation: `NOT_AUTHORIZED`;
- Production activation: `NOT_AUTHORIZED`;
- updater activation: `INACTIVE`;
- durable target selection: `BLOCKED_NO_QUALIFIED_NON_SYNTHETIC_DURABLE_TARGET`;
- selected target: `null`.

A successful Sprint137 regression is evidence of synthetic fail-closed HTTP composition only. It is not operational authorization.

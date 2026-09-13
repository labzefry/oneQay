# Sprint147 — Final Shift Close Canonical Control Plane Throttle Rejection Metadata Identity Response Hardening Regression

Author by Lab | zefry

## Purpose / Why

Sprint146 bound Final Shift Close throttle-response ownership to the exact canonical per-route `X-RateLimit-Limit` value after Sprint145 had already bound request identity to canonical route name, canonical controller action, exact HTTP method, and exact internal path.

The remaining response-side ambiguity is the rest of the framework throttle-rejection metadata. Sprint142 proved through real HTTP-kernel dispatch that canonical framework throttle rejections carry `X-RateLimit-Remaining: 0`, a non-empty decimal `Retry-After`, and a non-empty decimal `X-RateLimit-Reset`. The current hardener does not require those signals before rewriting the response.

Without this boundary, a synthetic or unrelated `429` response with the correct route identity and exact canonical ceiling could still be treated as owned even when its remaining/retry/reset metadata is missing or malformed.

## Objective / Gap

Bounded objective:

`CANONICAL_CONTROL_PLANE_THROTTLE_REJECTION_METADATA_IDENTITY_RESPONSE_HARDENING_REGRESSION`

A Final Shift Close throttle rejection may receive the established privacy/security hardening only when all previously qualified request and budget identity signals match and the framework throttle metadata also has the canonical shape:

1. `X-RateLimit-Limit` is the exact canonical per-route string (`1` for materialization POST; `2` for DB-attestation GET/HEAD);
2. `X-RateLimit-Remaining` is exact string `0`;
3. `Retry-After` is present, non-empty, and decimal digits only;
4. `X-RateLimit-Reset` is present, non-empty, and decimal digits only.

Missing metadata, nonzero or numeric-alias remaining values, empty values, and non-digit retry/reset values remain framework-owned.

## What changed

- `HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware` now delegates response metadata qualification to a dedicated canonical throttle-rejection metadata predicate.
- The hardener requires explicit presence of the canonical framework rate-limit headers before evaluating their values.
- The hardener requires exact `X-RateLimit-Remaining: 0` in addition to the Sprint146 exact canonical ceiling.
- `Retry-After` and `X-RateLimit-Reset` must both be non-empty decimal strings.
- A dedicated Sprint147 direct-middleware regression covers materialization POST and DB-attestation GET/HEAD across canonical and malformed/missing metadata cases.
- The historical Sprint144 named-route identity regression fixture now carries the canonical throttle-rejection metadata shape established by Sprint142 so its owned route-identity assertions remain executable under the stricter response identity boundary.
- Sprint146 throttle-budget identity remains preserved as the immediate historical ownership boundary.
- Sprint142 real HTTP-kernel evidence remains the source of truth for the canonical framework metadata shape.

## Evidence / Qualification

Sprint147 is CI-only bounded source engineering. Its exact six-path source envelope is:

1. `.github/workflows/sprint147-final-shift-close-canonical-control-plane-throttle-rejection-metadata-identity-response-hardening-regression.yml`
2. `apps/web/app/Delivery/Http/Middleware/HardenFinalShiftCloseRuntimeControlPlaneThrottleResponseMiddleware.php`
3. `apps/web/tests/final-shift-close-runtime-control-plane-named-route-identity-throttle-response-hardening-regression.php`
4. `apps/web/tests/final-shift-close-runtime-control-plane-throttle-rejection-metadata-identity-response-hardening-regression.php`
5. `docs/SPRINT147_FINAL_SHIFT_CLOSE_CANONICAL_CONTROL_PLANE_THROTTLE_REJECTION_METADATA_IDENTITY_RESPONSE_HARDENING_REGRESSION.md`
6. `ops/final-shift-close/CANONICAL_RUNTIME_CONTROL_PLANE_THROTTLE_REJECTION_METADATA_IDENTITY_RESPONSE_HARDENING_REGRESSION_CONTRACT.json`

Frozen sorted newline-terminated envelope SHA-256:

`ffd176da808eda16fccdf0375fcae2fd5bcc1cfc4b931aca6492ca31eb9b1d40`

Qualification requires:

- exact-head checkout and exact six-path envelope lock;
- machine-readable Sprint147 contract validation;
- Sprint147 direct-middleware malformed/missing metadata regression;
- Sprint146 throttle-budget identity regression preservation;
- Sprint144 named-route identity regression preservation with successor-compatible canonical metadata fixture;
- Sprint142 real HTTP-kernel throttle-response evidence preservation;
- PHP syntax, Composer validation/install/audit, and tracked-source cleanliness;
- canonical lifecycle NO-GO assertions;
- repository-native Product Owner merge authority before squash merge.

## Operational boundaries / NO-GO

Sprint147 does not execute or authorize any operational action. It does not:

- execute migration #27;
- provision Final Shift Close permissions;
- activate the Final Shift Close feature;
- provision an operational runtime control-plane token;
- select or activate a durable runtime target;
- perform operational runtime-binding manifest materialization;
- perform operational DB-binding attestation;
- change runtime allowlists;
- deploy or publish a release;
- activate Technical Preview;
- activate Production;
- activate the updater.

Machine-readable operational state remains authoritative in `ops/final-shift-close/STATE.json` and `ops/final-shift-close/DURABLE_ACTIVATION_TARGET_SELECTION.json`.

## Next position

After Sprint147 closure and canonical reconciliation, the next position is **Sprint148 bounded discovery from canonical post-Sprint147**. No Sprint148 objective, implementation, or source envelope is preselected here.

# Sprint96 — Final Shift Close Runtime Wiring

Author by Lab | zefry

## Purpose

Sprint96 wires the canonical Sprint95 Final Shift Close lifecycle into the Laravel service container while preserving fail-closed delivery boundaries and historical qualification horizons.

This sprint does not expose Final Shift Close through HTTP routes or UI and does not execute migration #27.

## Runtime wiring

`FinalShiftCloseServiceProvider` owns the bounded container registration for:

- `LaravelExpectedCashSnapshotReader`;
- `DeriveCashVariance`;
- `FinalShiftCloseAuthorizationPolicy`;
- `CloseShiftRepository` -> `LaravelCloseShiftRepository`;
- `CloseShift`;
- `ShiftCloseClock`.

The provider consumes the canonical `OrganizationalContextStore`, `DurableScopedAuthorizationPolicy`, and `PersistenceTransaction` registrations from `AppServiceProvider`.

The dedicated provider is registered through the Laravel application-builder boundary in `bootstrap/app.php`. Sprint96 intentionally leaves the historical shared `bootstrap/providers.php` list unchanged.

## Fail-closed feature control

`FinalShiftCloseServiceProvider` reads `ONEQAY_POS_SHIFT_CLOSE_ENABLED` directly with a default of `false` and `FILTER_VALIDATE_BOOL`.

Sprint96 intentionally leaves the historical shared `config/oneqay.php` horizon unchanged. Container resolvability therefore does not itself authorize or activate Final Shift Close persistence, and the repository remains fail-closed unless a separately authorized runtime explicitly arms the feature.

## Preserved authorization policy

- dedicated permission remains `pos.shift.close`;
- default role grant remains `NO_DEFAULT_GRANT`;
- closer/opener separation remains mandatory;
- nonzero variance closer/explanation-author separation remains mandatory;
- nonzero variance closer/reviewer separation remains mandatory;
- review acceptance remains prerequisite evidence, not final close authority.

## Explicit non-goals

Sprint96 does not:

- modify migration #27;
- execute migration #27;
- add or modify routes;
- add controller delivery;
- add UI delivery;
- grant `pos.shift.close` to any role;
- modify the historical shared `bootstrap/providers.php` provider list;
- modify the historical shared `config/oneqay.php` POS feature horizon;
- enable Technical Preview;
- enable Production;
- enable updater installation or activation.

## Canonical boundary status

- `FINAL_SHIFT_CLOSE_APPLICATION_SOURCE = MATERIALIZED`
- `FINAL_SHIFT_CLOSE_RUNTIME_WIRING = SOURCE_MATERIALIZED`
- `FINAL_SHIFT_CLOSE_RUNTIME_FEATURE_FLAG_DEFAULT = DISABLED`
- `FINAL_SHIFT_CLOSE_ROUTE_DELIVERY = NOT_IMPLEMENTED`
- `FINAL_SHIFT_CLOSE_UI_DELIVERY = NOT_IMPLEMENTED`
- `MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`
- `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`
- `PRODUCTION_AUTHORITY = NOT_GRANTED`
- `UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

The next bounded step may qualify delivery readiness or another explicitly scoped source dependency, but activation and migration execution remain separate authorities.

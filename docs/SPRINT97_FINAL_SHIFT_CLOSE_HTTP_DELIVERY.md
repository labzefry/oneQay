# Sprint97 — Final Shift Close HTTP Delivery

Author by Lab | zefry

## Purpose

Sprint97 adds a bounded HTTP delivery source for the canonical Sprint95 Final Shift Close lifecycle while preserving the fail-closed runtime and activation boundaries established by Sprint96.

## Closed request contract

The endpoint accepts exactly one application input:

- `operation_id`

The caller cannot supply authoritative:

- shift identity;
- expected cash;
- observed closing cash;
- variance amount or direction;
- explanation/review identity or outcome;
- closer actor identity;
- close timestamp.

Those values remain derived from verified session context and durable evidence inside the transactional lifecycle.

## Route boundary

The bounded route is:

`POST /pos/shifts/close`

with route name:

`pos.shifts.close`

The route is registered by `FinalShiftCloseServiceProvider` only when all of the following are true:

- runtime class is `local`, `test`, or `ci`;
- durable persistence is explicitly enabled;
- session control is explicitly enabled;
- canonical POS sale completion prerequisite is explicitly enabled;
- `ONEQAY_POS_SHIFT_CLOSE_ENABLED=true`.

`ONEQAY_POS_SHIFT_CLOSE_ENABLED` defaults to `false`, therefore the route is absent by default.

The route uses web/session middleware, active-session enforcement, bounded throttling, and `RequirePosSessionContextMiddleware` before application execution.

## Response and error boundary

Success returns only bounded close evidence/reconciliation identifiers and values required by the caller. Internal closer/opener/explanation/reviewer actor identities are not returned.

Authorization denial uses a safe 403 envelope. Invalid or transaction-rejected close attempts use a safe 422 envelope. Responses are `no-store, private`.

## Explicit non-goals

Sprint97 does not:

- modify or execute migration #27;
- add Final Shift Close UI;
- grant `pos.shift.close` to any role;
- enable Final Shift Close by default;
- enable Technical Preview;
- enable Production;
- enable updater installation or activation.

## Canonical boundary status

- `FINAL_SHIFT_CLOSE_APPLICATION_SOURCE = MATERIALIZED`
- `FINAL_SHIFT_CLOSE_RUNTIME_WIRING = MATERIALIZED`
- `FINAL_SHIFT_CLOSE_HTTP_DELIVERY_SOURCE = MATERIALIZED`
- `FINAL_SHIFT_CLOSE_HTTP_ROUTE_DEFAULT = ABSENT_FAIL_CLOSED`
- `FINAL_SHIFT_CLOSE_UI_DELIVERY = NOT_IMPLEMENTED`
- `MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`
- `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`
- `PRODUCTION_AUTHORITY = NOT_GRANTED`
- `UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

This source publication is not runtime activation authority.

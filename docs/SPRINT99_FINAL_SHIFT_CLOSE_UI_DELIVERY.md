# Sprint99 Final Shift Close UI Delivery

Author by Lab | zefry

## Purpose

Sprint99 materializes a bounded operator-facing Final Shift Close page on top of the canonical Sprint95–Sprint98 lifecycle, runtime wiring, HTTP delivery, and durable-delivery qualification.

This sprint does **not** activate Final Shift Close. The page and POST endpoint remain absent unless the existing Final Shift Close provider is explicitly armed in an authorized Local/Test/CI runtime with persistence, session-control, POS prerequisite, and `ONEQAY_POS_SHIFT_CLOSE_ENABLED=true`.

## UI contract

The guarded GET page is:

`GET /pos/shifts/close` — route name `pos.shifts.close.page`.

The canonical mutation remains:

`POST /pos/shifts/close` — route name `pos.shifts.close`.

The page controller requires the canonical durable `pos.shift.close` permission before rendering. Absence of an explicit durable role-permission grant denies access.

The Vue page submits exactly one authoritative input:

`operation_id`

The UI cannot submit shift identity, opening/closing cash, expected cash, variance, review evidence/outcome, opener/closer/reviewer identities, cutoff, or close timestamp.

## Stable retry behavior

The UI creates one stable operation ID using `crypto.randomUUID()` and retains it across a failed request. This supports the canonical exact-operation replay contract instead of manufacturing a second lifecycle command during retry.

After a successful durable close response, the UI does not issue a second mutation from the same page state.

## JSON delivery compatibility

The existing Sprint97 POST endpoint returns a JSON response. Sprint99 therefore uses `axios` directly rather than Inertia `router.post`, avoiding an Inertia protocol mismatch while preserving Laravel same-origin CSRF/XSRF handling.

## Security boundary

- page authorization: dedicated durable `pos.shift.close` permission;
- session authority: canonical `session.active` middleware inherited from `FinalShiftCloseServiceProvider`;
- POS context: canonical `RequirePosSessionContextMiddleware` inherited from the provider group;
- route/runtime feature gate: unchanged from Sprint97;
- no default role grant;
- no caller-supplied authoritative reconciliation state;
- no actor identity leakage in the UI contract;
- no direct lifecycle/repository mutation from the browser.

## Lifecycle boundaries

`FINAL_SHIFT_CLOSE_UI_DELIVERY_SOURCE = MATERIALIZED`

`FINAL_SHIFT_CLOSE_UI_ROUTE_DEFAULT = ABSENT_FAIL_CLOSED`

`FINAL_SHIFT_CLOSE_PERMISSION_DEFAULT_GRANT = NONE`

`MIGRATION_27_LIVE_EXECUTION = NOT_AUTHORIZED`

`TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`

`PRODUCTION_AUTHORITY = NOT_GRANTED`

`UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

No migration is executed by this sprint. No provider feature default changes. No deployment, release, DNS, updater, Technical Preview activation, or Production activation is performed.

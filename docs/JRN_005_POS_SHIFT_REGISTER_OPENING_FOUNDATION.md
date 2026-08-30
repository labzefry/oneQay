# JRN-005 POS Shift/Register Opening Foundation

Author by Lab | zefry

## Status

**SOURCE FOUNDATION / LOCAL-TEST-CI ONLY / DEFAULT DISABLED / NOT ACTIVATED**

This document describes the bounded Sprint48 JRN-005 source foundation. It creates no deployment, release, migration-execution, Technical Preview, Production, updater, or rollback authority.

## Purpose

The foundation allows an authorized operator to open one accountable active shift for the exact verified tenant/outlet/device-backed register execution context.

The source is intentionally smaller than the full shift/register and cash-control lifecycle.

## Server-owned context

Every opening derives authority from the canonical verified organizational context:

- actor identity;
- tenant;
- organization;
- outlet;
- device-backed register execution context.

The caller supplies none of these values.

The bounded command contains only `operation_id`.

## Authorization

Required permission:

`pos.shift.open`

Authorization is deny-by-default through the canonical durable scoped authorization policy.

No default role or permission grant is created.

## Durable state

Source migration #18 defines:

`oneqay_pos_shifts`

It stores immutable opening evidence and a bounded active-occupancy sentinel.

The exact active uniqueness boundary is:

`tenant_id + outlet_id + device_id + active_slot`

Sprint48 writes server-owned `active_slot = 1`.

The database uniqueness constraint is the final arbiter preventing two active shifts in the same exact device-backed register context.

## Idempotency

The durable operation boundary is:

`tenant_id + operation_id`

The semantic fingerprint binds the verified actor, tenant, organization, outlet, device, and opening command semantics.

Exact replay returns the original shift identity, correlation identity, opening time, and active result without writing a second row.

Conflicting reuse of an operation id fails closed.

## Register boundary

Sprint48 does not create a general register entity or register-administration surface.

The already verified canonical device is used only as the bounded register execution context for this foundation.

Different verified devices at the same outlet may hold independent active shifts.

## Runtime boundary

Feature flag:

`ONEQAY_POS_SHIFT_OPENING_ENABLED`

Config:

`oneqay.pos_shift_opening.enabled`

Default is `false`.

The HTTP mutation is:

`POST /pos/shifts/open`

It is available only in Local/Test/CI when explicitly armed and requires active first-party session control plus `RequirePosSessionContextMiddleware`.

## Explicit exclusions

This source does not implement:

- shift close;
- opening cash;
- cash movement or reconciliation;
- register administration;
- JRN-010;
- JRN-006 active-shift enforcement;
- sale mutation;
- stock mutation;
- catalog mutation;
- payment-provider integration;
- deployment or release.

A later separately bounded gate must decide how active-shift evidence becomes a JRN-006 sale-completion precondition.

## Migration posture

Migration #18 is source-published by this bounded source only after normal exact-head qualification and merge.

It remains:

**NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migrations #16 and #17 also remain source-published but not executed, applied, or activated.

Technical Preview remains **NO_SCHEMA_CHANGE / NOT ACTIVATED**.

Production remains **NO-GO**.

Attribution: **Lab | zefry**

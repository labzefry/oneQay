# Sprint86 — Final Shift Close Source-Readiness Entry Gate

Author by Lab | zefry

## 1. Purpose

Sprint86 defines the bounded entry gate for a future durable Final Shift Close capability after the canonical cash-control chain has reached opening cash, closing cash, expected cash, variance derivation, variance explanation, and maker-checker review decision evidence.

This gate is planning/readiness only. It does **not** implement Final Shift Close, add a final-close permission, create or execute a migration, mutate `oneqay_pos_shifts`, bind new runtime services, activate Technical Preview, deploy a release, activate Production, or select Final Shift Close authority.

## 2. Canonical evidence available before Sprint86

The canonical repository already provides:

- durable shift opening with one active slot per tenant/outlet/device context;
- immutable opening-cash evidence;
- immutable observed closing-cash evidence;
- immutable sale-to-shift binding;
- server-derived expected cash;
- canonical cash-variance derivation;
- durable nonzero-variance explanation evidence;
- durable maker-checker review decision evidence;
- reviewer permission `pos.shift.cash-variance-review-decision.record`;
- review outcomes `REVIEW_ACCEPTED` and `REVIEW_REJECTED`;
- exact tenant/organization/outlet/shift/evidence/cutoff/currency/scale/amount binding checks.

The canonical repository does **not** yet provide:

- a durable `CloseShift` application service;
- a Final Shift Close repository/result/command family;
- a Final Shift Close permission;
- immutable Final Shift Close evidence;
- closer identity, close operation identifier/fingerprint, or close timestamp in the shift lifecycle;
- a governed mutation that releases `active_slot` after a successful final close.

Therefore Final Shift Close cannot be treated as a minor extension of reviewer approval.

## 3. Business-control boundary

A future Final Shift Close implementation must preserve the following fail-closed rules.

### A. Canonical zero-variance path

When canonical variance direction is `MATCH`, a future source design may be eligible to proceed without cash-variance explanation/review evidence because there is no nonzero variance to adjudicate.

This is only source-readiness semantics. Sprint86 does not select who may close, does not grant close authority, and does not implement the mutation.

### B. Canonical nonzero-variance path

When canonical variance direction is `OVER` or `SHORT`, a future Final Shift Close must fail closed unless all of the following are authoritative and exactly bound:

- the canonical variance;
- its canonical explanation evidence;
- its canonical review decision evidence;
- review outcome equals `REVIEW_ACCEPTED`.

`REVIEW_REJECTED`, missing explanation, missing review evidence, stale evidence, cross-scope evidence, mismatched amounts, mismatched cutoff, mismatched currency/scale, or competing evidence must block finalization.

### C. Reviewer permission is not close authority

The reviewer permission `pos.shift.cash-variance-review-decision.record` authorizes only the bounded review-decision action already implemented.

A future Final Shift Close permission must be a separate deny-by-default capability. Sprint86 does not name, register, grant, seed, or bind such a permission.

### D. Actor-separation policy remains unselected

Canonical maker-checker enforcement requires the variance reviewer to differ from the explanation author. Sprint86 does not infer an additional rule for the future closer.

Whether the future closer may be the original shift actor, explanation author, reviewer, or must be a separate actor requires an explicit later policy decision. No such authority rule is selected here.

## 4. Required future persistence design

Before Final Shift Close source can be qualified, a separately bounded schema-planning step must define an auditable and race-safe persistence model.

At minimum that future design must address:

- immutable close operation identity and semantic fingerprint;
- immutable close evidence identity;
- authoritative closer identity;
- close correlation identity;
- authoritative close timestamp;
- exact binding to tenant, organization, outlet, device, shift, opening-cash evidence, closing-cash evidence, cutoff, expected cash, observed cash, variance, currency, and scale;
- binding to accepted review evidence when variance is nonzero;
- exact replay idempotency;
- competing-operation rejection;
- concurrent-close race safety;
- atomic transition of the shift from active to closed;
- release of the active-slot uniqueness marker only inside the same successful transaction that records final close evidence;
- preservation of historical close evidence after the active slot is released.

A candidate design may use append-only final-close evidence plus an atomic update of `oneqay_pos_shifts.active_slot` from `1` to `NULL`, but Sprint86 does not approve a concrete migration or table shape.

## 5. Transaction and locking requirements for future source

A future implementation must fail closed unless authoritative state is read and mutated under a transaction boundary that prevents competing close operations from both succeeding.

The future design must account for locking or equivalent concurrency control over:

- the canonical active shift row;
- authoritative closing-cash evidence;
- authoritative expected-cash/variance inputs;
- explanation/review evidence when nonzero variance applies;
- the final-close operation/evidence namespace.

No Final Shift Close implementation may rely only on caller-provided projections or Preview/session state.

## 6. Scope isolation

Sprint86 changes only this decision document and its dedicated workflow gate.

Sprint86 explicitly excludes:

- `apps/web/app/Application/Pos/CloseShift.php`;
- any Final Shift Close command/repository/result source;
- any `Infrastructure/Pos` close repository;
- `AppServiceProvider` or other runtime bindings;
- permission registry/default grant changes;
- database migrations, including any migration #27 candidate;
- route/UI changes;
- Technical Preview runtime/configuration changes;
- deployment, release, DNS, updater, or live-host actions.

## 7. Sprint86 decision

Subject to successful exact-head Sprint86 CI:

- `FINAL_SHIFT_CLOSE_SOURCE_READINESS = PASS_TO_SCHEMA_PLANNING`
- `FINAL_SHIFT_CLOSE_AUTHORITY = NOT_SELECTED`
- `SHIFT_CLOSE_EXECUTION = NOT_IMPLEMENTED`
- `FINAL_SHIFT_CLOSE_PERMISSION = NOT_DEFINED`
- `MIGRATION_27 = NOT_CREATED`
- `MIGRATION_EXECUTION_AUTHORITY = NOT_GRANTED`
- `TECHNICAL_PREVIEW_ACTIVATION_AUTHORITY = NOT_GRANTED`
- `DEPLOYMENT_EXECUTION_AUTHORITY = NOT_GRANTED`
- `PRODUCTION_AUTHORITY = NOT_GRANTED`
- `UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

`PASS_TO_SCHEMA_PLANNING` means only that the canonical prerequisites and unresolved persistence/authority boundaries are sufficiently explicit for a later bounded schema-planning sprint. It is not permission to create or execute a migration, implement Final Shift Close, or grant anyone close authority.

## 8. Next bounded concern

If Sprint86 becomes canonical, the next safe bounded concern is a **Final Shift Close schema-readiness plan** that chooses the durable evidence model and atomic active-slot release contract without executing any migration and without implementing Final Shift Close authority.

Until a later explicit policy/source decision says otherwise:

- Final durable Shift Close authority remains **NOT SELECTED**;
- Final Shift Close execution remains **NOT IMPLEMENTED**;
- Technical Preview remains **NOT ACTIVATED / NO-GO**;
- Production remains **NO-GO**.

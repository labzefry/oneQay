# JRN-010 Prerequisite — Cash Variance Reviewer Authorization Policy

Author by Lab | zefry

## Status

`SPRINT80 REVIEWER AUTHORIZATION POLICY SELECTED / DOCS-ONLY / DEDICATED PERMISSION SELECTED / NO DEFAULT GRANT / NO ROLE INFERENCE / NO RUNTIME WIRING / NO CLOSE AUTHORITY / PREVIEW INACTIVE / PRODUCTION NO-GO`

## Canonical base

Sprint80 begins from canonical post-Sprint79 `main`:

`26d5eb07e8157bd795f2fb378edf748513841bfa`

Sprint79 already publishes the durable cash-variance reviewer-decision source foundation and migration #26 source only. Sprint80 does not reopen its outcomes, maker-checker rule, durable evidence schema, replay semantics, or source foundation.

## Decision

The minimum dedicated reviewer permission identifier is selected as:

`pos.shift.cash-variance-review-decision.record`

This permission authorizes only the bounded act of recording one canonical cash-variance reviewer decision against authoritative durable explanation evidence.

It is capability-specific and is not a synonym for any role, title, administrator status, supervisor status, shift-close authority, accounting authority, settlement authority, or deployment authority.

## Authorization semantics

Reviewer authorization must be evaluated from authoritative current verified organizational context and the current permission snapshot for the exact organization and outlet.

The authorization boundary must remain:

- tenant scoped;
- organization scoped;
- outlet scoped;
- deny-by-default;
- no default grant;
- no role-name inference;
- no implicit administrator bypass;
- no implicit supervisor bypass;
- no caller-supplied tenant, organization, outlet, reviewer identity, or permission authority.

A missing permission snapshot, mismatched verified context, absent permission, stale/unresolvable organizational authority, or ambiguous authorization state must fail closed.

## Required ordering

The dedicated reviewer authorization check must complete successfully before any authoritative reviewer side effect, including:

1. reviewer clock acquisition;
2. transaction entry;
3. reviewer-decision persistence.

The existing canonical subject, explanation binding, non-zero variance validation, and maker-checker invariants remain required. Authorization does not replace them.

## Separation from explanation authority

The existing explanation-author permission:

`pos.shift.cash-variance-explanation.record`

must not authorize reviewer decisions and must not be treated as an alias for the dedicated reviewer permission.

An actor may hold one permission, both permissions, or neither permission according to an authoritative permission snapshot. No grant is inferred by Sprint80.

Even when an actor is independently authorized for both capabilities, the canonical maker-checker rule remains mandatory: reviewer actor identity must differ from the authoritative explanation author identity for the exact explanation evidence under review.

## Review outcomes remain unchanged

Sprint80 does not modify the only canonical reviewer outcomes:

- `REVIEW_ACCEPTED`;
- `REVIEW_REJECTED`.

`REVIEW_ACCEPTED` records authoritative review evidence only. It is not final shift-close authorization and does not mutate the shift into a closed state.

`REVIEW_REJECTED` remains fail-closed and does not create close authority, waiver, write-off, arbitrary cash adjustment, or accounting treatment.

## No role or default-grant selection

Sprint80 intentionally selects no reviewer role and no default permission grant.

No role label such as administrator, supervisor, manager, owner, cashier, or any future role name may be used as an authorization shortcut unless a separately canonical permission-assignment mechanism grants the exact selected permission in authoritative scope.

Seeders, factories, bootstrap data, or configuration must not silently grant `pos.shift.cash-variance-review-decision.record` as part of Sprint80.

## Coherent next implementation slice

After Sprint80 becomes canonical, existing source facts are sufficient to implement reviewer authorization as one coherent bounded source slice rather than separate readiness, envelope, authorization-source, and reconciliation micro-sprints.

That implementation should minimally:

- publish the selected constant in the existing POS permission vocabulary;
- apply the existing authoritative organization/outlet permission-snapshot pattern to `RecordCashVarianceReviewDecision`;
- fail closed on context mismatch or permission absence;
- preserve maker-checker and deterministic durable review evidence;
- prove authorization occurs before reviewer clock, transaction, and persistence side effects;
- add targeted regression coverage for authorized, forbidden, cross-scope, explanation-permission-only, and self-review cases;
- avoid role/default-grant publication;
- avoid new schema or migration unless fresh implementation evidence proves one is required;
- avoid Shift Close selection or implementation.

No separate compatibility predecessor should be created unless fresh exact-head CI proves a stale historical workflow/oracle conflict against legitimate bounded source.

## Delivery acceleration posture

Sprint80 is intentionally the final minimum policy gate for this reviewer-authorization prerequisite.

After the coherent authorization implementation is canonical, engineering should stop deepening reviewer-policy theory unless a concrete MVP or Technical Preview blocker requires it and should instead prioritize completion of usable business journeys and Technical Preview readiness.

Governance remains an enabler of secure delivery, not the delivery objective.

## Explicit non-scope

Sprint80 does not select, publish, execute, activate, or authorize:

- reviewer role names;
- default permission grants;
- permission seeding;
- provider/config runtime binding;
- controller, route, API, or UI;
- privileged MFA/step-up;
- reviewer quorum, escalation, amendment, reversal, override, supersession, waiver, or write-off;
- final Shift Close;
- close permission or close authority;
- arbitrary cash adjustment;
- accounting/general-ledger treatment;
- settlement/provider reconciliation;
- migration #27;
- shared migration execution;
- migration rollback or destructive database operations;
- Technical Preview activation;
- Production activation;
- deployment/release;
- updater activation.

## Lifecycle lock

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #26: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Shared migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

JRN-010 final Shift Close: **NOT SELECTED**

## Sprint80 decision summary

**Dedicated reviewer permission = `pos.shift.cash-variance-review-decision.record`.**

**Authorization = exact current tenant/organization/outlet scope, deny-by-default, no role inference, no default grant.**

**Authorization ordering = before reviewer clock, transaction, and persistence side effects.**

**Explanation permission cannot substitute for reviewer permission.**

**Maker-checker remains mandatory.**

**Reviewer authorization is not Shift Close authority.**

**Next = one coherent reviewer-authorization implementation slice, then business-journey / Technical Preview readiness focus.**

Author by Lab | zefry

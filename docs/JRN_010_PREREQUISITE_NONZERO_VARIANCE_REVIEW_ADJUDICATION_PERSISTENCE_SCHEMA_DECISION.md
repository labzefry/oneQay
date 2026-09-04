# JRN-010 Prerequisite — Non-Zero Variance Review / Adjudication Persistence and Schema Decision Gate

Author by Lab | zefry

## Status

`SPRINT77 PERSISTENCE/SCHEMA DECISION GATE ONLY / DEDICATED REVIEW-DECISION EVIDENCE REQUIRED / MIGRATION #26 SELECTED SEMANTICALLY / MIGRATION SOURCE NOT PUBLISHED / REVIEW_ACCEPTED + REVIEW_REJECTED ONLY / NO REVIEWER PERMISSION SELECTED / NO CLOSE AUTHORITY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint77 selects the minimum dedicated durable persistence/schema boundary required by canonical Sprint75 reviewer policy and canonical Sprint76 source-readiness findings.

It does not publish migration #26 source, reviewer application source, reviewer repository source, infrastructure adapter, permission source, provider/config binding, controller, route, API, UI, role/default grant, privileged step-up, close authority, final evidence freshness, close concurrency/idempotency, final shift transition, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution/application, rollback, or destructive database authority.

## Canonical basis

The canonical baseline for Sprint77 is:

`6f1df9442873eefc78fee2854676395b78fddf48`

Canonical Sprint75 establishes:

- every canonical `OVER` or `SHORT` requires durable explanation evidence before review;
- reviewer authority is distinct from explanation-author authority;
- reviewer actor must differ from the authoritative explanation author;
- the review subject is the exact canonical variance plus the exact authoritative durable explanation evidence;
- the exact reviewer outcomes are `REVIEW_ACCEPTED` and `REVIEW_REJECTED`;
- review outcome does not mutate variance or explanation evidence;
- neither reviewer outcome grants Shift Close authority.

Canonical Sprint76 establishes:

- transient reviewer state cannot substitute for authoritative reviewer evidence;
- current explanation persistence must not be overloaded as reviewer-decision persistence;
- a dedicated authoritative reviewer-decision durability boundary is required;
- reviewer source implementation remains blocked until a bounded persistence/schema decision becomes canonical.

Sprint77 does not reopen those decisions.

## Canonical outcome terminology

Sprint75 is the policy-selection authority for reviewer outcomes and selects exactly:

- `REVIEW_ACCEPTED`;
- `REVIEW_REJECTED`.

Sprint76 uses the shorter terms `APPROVE` and `REJECT` while explicitly stating that it freezes rather than reopens Sprint75 policy.

Sprint77 therefore treats those Sprint76 words only as descriptive shorthand and does not select additional stored outcomes or aliases.

The durable reviewer-decision schema selected by Sprint77 must persist only the canonical Sprint75 outcome names `REVIEW_ACCEPTED` and `REVIEW_REJECTED`.

Unknown outcomes must fail closed.

## Persistence conclusion

The current canonical durable explanation-evidence relation cannot safely represent independent reviewer decisions.

Reviewer decision evidence has a different authoritative actor, a different governed action, a distinct authorization responsibility, and a separate downstream policy meaning.

Therefore Sprint77 selects exactly one dedicated append-only reviewer-decision evidence foundation.

Migration #26 is now **SELECTED SEMANTICALLY** for that dedicated foundation.

The selected future migration filename is:

`apps/web/database/migrations/0000_00_00_000026_create_pos_cash_variance_review_decision_evidence_foundation.php`

The selected future table name is:

`oneqay_pos_cash_variance_review_decision_evidence`

Migration #26 is not source-published by Sprint77.

No migration is executed, applied, or activated by this decision.

## Why explanation persistence must remain separate

The canonical table created by migration #25 is authoritative explanation evidence.

It must not gain reviewer-decision columns and must not be updated in place when review occurs.

Storing reviewer decisions inside:

- the explanation-evidence row;
- opening cash evidence;
- closing cash evidence;
- the POS shift row;
- generic audit logs;
- session state;
- cache;
- UI state;
- request payload only;

would either mutate existing authoritative evidence, collapse maker-checker authority boundaries, or fail the Sprint76 durability requirement.

A separate append-only reviewer-decision evidence relation is therefore the minimum compatible persistence boundary.

## Selected minimum durable evidence

The future migration #26 must preserve only the minimum authoritative evidence required for exact subject binding, maker-checker separation, deterministic replay/conflict detection, and audit attribution.

Sprint77 selects the following durable evidence responsibilities:

- `tenant_id` — exact tenant boundary;
- `review_evidence_id` — stable reviewer-decision evidence identity;
- `operation_id` — caller operation identity for deterministic replay semantics;
- `payload_fingerprint` — canonical reviewer-decision payload fingerprint for conflict-safe replay;
- `shift_id` — exact canonical shift identity;
- `opening_cash_evidence_id` — exact canonical opening-cash evidence identity;
- `closing_cash_evidence_id` — exact canonical closing-cash evidence identity;
- `cash_variance_explanation_evidence_id` — exact authoritative durable explanation evidence identity being reviewed;
- `explanation_actor_identity_id` — authoritative explanation-author identity copied as immutable review-subject evidence;
- `reviewer_actor_identity_id` — authoritative reviewer identity;
- `organization_id` — exact organization identity;
- `outlet_id` — exact outlet identity;
- `cutoff_at_unix` — exact canonical variance cutoff;
- `expected_cash_atomic` — exact canonical expected cash amount;
- `observed_closing_cash_atomic` — exact canonical observed closing cash amount;
- `variance_atomic` — exact canonical signed variance amount;
- `variance_direction` — exact canonical `OVER` or `SHORT`;
- `currency` — exact canonical currency;
- `currency_scale` — exact canonical scale;
- `explanation_payload_fingerprint` — immutable binding to the exact authoritative explanation payload/evidence snapshot;
- `review_outcome` — exactly `REVIEW_ACCEPTED` or `REVIEW_REJECTED`;
- `correlation_id` — correlation identity for traceability;
- `reviewed_at_unix` — authoritative reviewer-decision persistence time.

The exact physical SQL types remain constrained by canonical MySQL-compatible evidence conventions and must be frozen by the future source-envelope/source-foundation work without widening these semantics.

## Deliberately absent review content

Sprint75 did not select a reviewer comment, rejection reason, reason-code catalog, remediation note, waiver rationale, or accounting treatment.

Sprint77 therefore selects no:

- `reviewer_comment`;
- `rejection_reason`;
- `reason_code`;
- `waiver_reason`;
- `write_off_reason`;
- `accounting_treatment`;
- remediation payload.

Adding any such authoritative content requires a separately bounded product/policy decision.

The initial reviewer-decision evidence foundation records only the selected outcome and its exact authoritative subject/provenance.

## Exact review-subject snapshot

The reviewer decision must be independently auditable without relying on mutable presentation state.

Therefore the durable reviewer row preserves the canonical variance snapshot and explicit explanation binding selected above.

The snapshot must originate from the canonical `CashVarianceResult` and exact durable explanation evidence.

Callers must not independently override:

- tenant;
- organization;
- outlet;
- shift;
- opening evidence identity;
- closing evidence identity;
- explanation evidence identity;
- explanation author identity;
- cutoff;
- expected amount;
- observed amount;
- variance;
- direction;
- currency;
- scale;
- explanation fingerprint;
- reviewer actor identity.

Those values must be resolved from trusted canonical evidence and verified execution context by later source implementation.

## Direction and subject restriction

Reviewer-decision evidence selected by Sprint77 exists only for canonical non-zero variance review.

A future reviewer write must accept only:

- `OVER` with `variance_atomic > 0`; or
- `SHORT` with `variance_atomic < 0`.

`MATCH` must not enter this reviewer-decision persistence path.

Automatic tolerance remains exactly `0` atomic units.

A reviewer outcome cannot normalize `OVER` or `SHORT` into `MATCH`.

## Maker-checker persistence invariant

The authoritative reviewer actor must differ from the authoritative explanation author actor.

The future persistence boundary must retain both authoritative identity values explicitly and must fail closed when:

`reviewer_actor_identity_id == explanation_actor_identity_id`

This inequality must be enforced before any successful reviewer-decision persistence side effect.

The infrastructure/repository layer must independently reject a self-review write even if an upstream application caller is defective.

The future migration/source publication should also use the narrowest database-level constraint compatible with the canonical MySQL target to prevent equal stored reviewer/explanation actor identities from becoming authoritative rows.

No role name, administrator label, owner label, supervisor label, or default grant may bypass this maker-checker invariant.

## Key and uniqueness posture

The future table must be tenant-scoped and append-only.

Sprint77 selects these minimum identity/uniqueness semantics:

- primary evidence identity: exact `tenant_id + review_evidence_id`;
- operation replay identity: exact `tenant_id + operation_id`;
- one authoritative reviewer decision for the exact authoritative explanation evidence under the initial foundation.

A later source foundation must encode the smallest deterministic database uniqueness representation that protects the exact explanation-review relationship without broadening review policy.

Competing independent authoritative decisions for the same exact explanation evidence must fail closed under this initial foundation.

Sprint77 selects no re-review, override, quorum, escalation, reversal, amendment, or supersession workflow.

## Deterministic replay semantics

The future reviewer repository must preserve deterministic replay behavior:

- same tenant + same operation + same exact review payload -> return the original authoritative review evidence;
- same tenant + same operation + conflicting payload -> fail closed;
- same exact authoritative explanation evidence + competing independent review decision -> fail closed;
- cross-tenant operation identity reuse remains isolated;
- replay must still satisfy the future reviewer authorization boundary and exact trusted context requirements.

Sprint77 does not implement replay code.

## Foreign-key and relationship posture

The future migration #26 must use tenant-scoped restrictive relationships where canonical parent identities already exist.

At minimum the selected reviewer evidence must remain restrictively bound to:

- the exact POS shift;
- the exact opening cash evidence;
- the exact closing cash evidence;
- the exact durable cash-variance explanation evidence;
- the authoritative explanation actor identity;
- the authoritative reviewer actor identity;
- the exact organization;
- the exact outlet.

Delete/update cascades that could silently rewrite or erase authoritative reviewer evidence are not selected.

Independent foreign keys alone are not sufficient to prove the whole review subject; later repository/application logic must verify exact cross-record tenant/organization/outlet/shift/evidence agreement before persistence.

## Append-only posture

Authoritative reviewer-decision evidence must not be updated or deleted in place.

The initial foundation selects:

- insert once;
- exact replay readback;
- no update mutation;
- no delete mutation;
- no outcome reversal;
- no supersession implementation;
- no second competing decision for the same authoritative explanation evidence.

A future requirement for re-review or correction must receive its own bounded immutable design before becoming authoritative.

## REVIEW_ACCEPTED posture

`REVIEW_ACCEPTED` means only that the authoritative distinct reviewer accepted the exact canonical non-zero variance plus exact durable explanation pair for possible downstream policy evaluation.

It does not:

- convert variance to `MATCH`;
- create tolerance;
- adjust cash;
- authorize write-off;
- choose accounting treatment;
- grant close eligibility;
- grant Shift Close authority;
- satisfy final evidence freshness;
- satisfy privileged step-up;
- mutate the shift.

## REVIEW_REJECTED posture

`REVIEW_REJECTED` remains fail-closed for downstream close-policy evaluation.

It does not:

- delete or rewrite explanation evidence;
- authorize replacement explanation;
- select remediation;
- create a second review opportunity;
- normalize the variance;
- grant close authority;
- trigger accounting or settlement behavior.

Any remediation or explanation supersession remains separately unselected.

## Reviewer authorization remains unselected

Sprint77 selects persistence/schema only.

It does not select the reviewer permission identifier.

The existing explanation-author permission:

`pos.shift.cash-variance-explanation.record`

must not be reused for reviewer authorization.

A later bounded reviewer authorization decision/source gate must preserve deny-by-default scoped authorization and must not infer permission from role names.

The future reviewer source ordering must still ensure authorization occurs before authoritative reviewer clock, transaction, and repository persistence.

## Source-envelope readiness conclusion

With this dedicated persistence/schema boundary selected, reviewer decision durability is ready for a later exact source-envelope freeze.

Sprint78 should freeze the smallest source publication envelope required to establish the dedicated reviewer-decision evidence foundation.

That future envelope may include only the minimum necessary migration, immutable contracts, application/repository boundary, infrastructure adapter, deterministic regression, workflow qualification, and source-foundation documentation proven necessary by Sprint77.

Sprint78 must not yet grant reviewer permission/default roles, Shift Close authority, runtime delivery, deployment, migration execution, or lifecycle activation.

## Migration #26 lifecycle posture

Migration #26 status after Sprint77 is:

**SELECTED SEMANTICALLY / SOURCE NOT PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

This means only that migration sequence slot #26 is reserved for the dedicated reviewer-decision evidence foundation described by this gate.

It does not authorize creating, executing, applying, activating, rolling back, or destructively changing any database migration in Sprint77.

## Historical compatibility requirement

A future migration #26 source publication may expose historical workflow or migration-horizon assumptions.

If fresh qualification reveals stale historical compatibility oracles caused solely by the legitimate migration #26 source shape, future work must:

1. classify the failure precisely;
2. preserve the frozen legitimate source semantics;
3. avoid generic bypass or fake-green qualification;
4. publish the smallest workflow-only compatibility predecessor if required;
5. fresh-qualify and merge that predecessor;
6. replay frozen source byte-identically from canonical main;
7. fresh-qualify again before merge.

Sprint77 changes no workflow.

## Fail-closed requirements for future source

Any later reviewer implementation must fail closed when:

- canonical variance evidence is absent;
- variance is `MATCH`;
- variance sign/direction is inconsistent;
- authoritative durable explanation evidence is absent;
- exact explanation/variance binding cannot be proven;
- trusted reviewer actor identity is absent;
- reviewer actor equals explanation author actor;
- tenant/organization/outlet/shift context differs;
- opening or closing evidence identity differs;
- cutoff differs;
- expected/observed/variance amount differs;
- currency or scale differs;
- explanation actor identity or explanation fingerprint differs;
- operation replay conflicts;
- another authoritative decision already exists for the same exact explanation evidence under the initial foundation;
- review outcome is not exactly `REVIEW_ACCEPTED` or `REVIEW_REJECTED`;
- caller attempts to supply or mutate close state, tolerance, accounting, settlement, or cash adjustment state;
- future reviewer authorization is absent or invalid;
- repository persistence is unavailable or disabled.

Unknown states remain denied by default.

## Explicit non-scope

Sprint77 does not select or implement:

- migration #26 source publication;
- migration execution/application/activation;
- exact source path fingerprint;
- reviewer permission identifier;
- reviewer permission source;
- default role/grant mapping;
- reviewer quorum;
- reviewer comment/rejection reason/reason code;
- re-review, reversal, override, escalation, or supersession;
- explanation amendment/supersession;
- provider/config/runtime binding;
- controller/route/API/UI;
- privileged MFA/step-up;
- final evidence freshness;
- close permission;
- close authority;
- close concurrency/idempotency;
- final shift transition;
- controlled reopen;
- late-event remediation;
- tolerance widening;
- waiver/write-off;
- arbitrary cash movement;
- accounting/general ledger;
- provider settlement/reconciliation;
- Technical Preview activation;
- Production activation;
- deployment/release;
- updater activation;
- rollback/destructive database operations.

## Lifecycle lock

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #26: **SELECTED SEMANTICALLY / SOURCE NOT PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

JRN-010 Shift Close: **NOT SELECTED**

## Sprint78 entry gate

The exact next bounded task after Sprint77 is:

**Sprint78 — Non-Zero Variance Reviewer Decision Source Envelope Gate**

Sprint78 must freeze the smallest exact source path envelope for the dedicated reviewer-decision evidence foundation selected here.

Sprint78 must remain source-envelope only and must not execute/apply migration #26, grant reviewer permission/default roles, implement final close authority, activate runtime delivery, or widen lifecycle posture.

## Sprint77 status

**SPRINT77 REVIEW / ADJUDICATION PERSISTENCE AND SCHEMA DECISION = COMPLETE**

**Migration #26 = SELECTED SEMANTICALLY / SOURCE NOT PUBLISHED.**

**Reviewer source implementation = BLOCKED pending canonical Sprint78 exact source-envelope freeze.**

Author by Lab | zefry

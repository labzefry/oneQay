# JRN-010 Prerequisite — Non-Zero Cash Variance Explanation Schema and Source-Envelope Readiness Gate

Author by Lab | zefry

## Status

`SPRINT68 READINESS/SCHEMA-SELECTION GATE ONLY / MIGRATION #25 SELECTED SEMANTICALLY / MIGRATION SOURCE NOT PUBLISHED / ONE APPEND-ONLY EXPLANATION-EVIDENCE STRUCTURE / NO REVIEWER POLICY / NO CLOSE AUTHORITY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint68 selects the minimum durable schema boundary required by canonical Sprint67 and determines whether the explanation capability is ready for a later exact source-envelope gate.

It does not publish migration #25 source, application source, repository source, infrastructure adapter, controller, route, UI, permission, reviewer workflow, privileged step-up, close authority, concurrency/idempotency implementation, final shift transition, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution, rollback, or destructive database authority.

## Canonical basis

The canonical baseline is `9fed24abdb9d2b73b2ec6473f7bfbd346b612344`.

Canonical Sprint64 publishes immutable `CashVarianceResult`.

Canonical Sprint65 keeps `OVER` and `SHORT` unresolved and non-authorizing.

Canonical Sprint66 requires explanation for every non-zero variance and selects automatic tolerance exactly `0` atomic units.

Canonical Sprint67 requires authoritative non-zero explanation evidence to be durable and auditable before any later reviewer or final-close policy may depend on it.

Sprint68 does not reopen those semantics.

## Schema selection conclusion

Durability cannot be satisfied by the current canonical schema without creating a new durable evidence structure.

Therefore migration #25 is now **SELECTED SEMANTICALLY** for exactly one append-only POS cash-variance explanation-evidence foundation.

The selected future migration filename is:

`apps/web/database/migrations/0000_00_00_000025_create_pos_cash_variance_explanation_evidence_foundation.php`

The selected future table name is:

`oneqay_pos_cash_variance_explanation_evidence`

Migration #25 is not source-published by Sprint68.

No migration is executed, applied, or activated by this decision.

## Why a new structure is required

Canonical opening and closing cash evidence tables persist source observations, but neither table may be repurposed to store explanation text or adjudication metadata.

Canonical `CashVarianceResult` is intentionally derived and stateless.

Storing explanation inside:

- opening cash evidence;
- closing cash evidence;
- POS shifts;
- POS sales;
- generic audit logs;
- session state;
- cache;
- request payload only;

would either mutate authoritative evidence, blur responsibility, or fail Sprint67 durability/auditability requirements.

A dedicated append-only evidence structure is therefore the minimum compatible boundary.

## Selected durable evidence columns

The future migration #25 must create only the minimum evidence required for deterministic identity binding, replay safety, audit attribution, and explanation content.

Required columns are selected as follows:

- `tenant_id` — tenant boundary;
- `evidence_id` — stable explanation evidence identity;
- `operation_id` — caller operation identity used for exact replay semantics;
- `payload_fingerprint` — canonical request/evidence fingerprint for conflict-safe replay;
- `shift_id` — exact shift identity;
- `opening_cash_evidence_id` — exact canonical opening evidence identity;
- `closing_cash_evidence_id` — exact canonical closing evidence identity;
- `actor_identity_id` — authoritative actor attribution;
- `organization_id` — exact organization identity;
- `outlet_id` — exact outlet identity;
- `cutoff_at_unix` — exact canonical variance cutoff;
- `expected_cash_atomic` — canonical expected cash atomic value;
- `observed_closing_cash_atomic` — canonical observed closing atomic value;
- `variance_atomic` — canonical signed variance atomic value;
- `variance_direction` — exact `OVER` or `SHORT`;
- `currency` — canonical currency;
- `currency_scale` — canonical scale;
- `explanation_text` — intentionally supplied non-empty human-readable explanation;
- `correlation_id` — correlation identity for traceability;
- `recorded_at_unix` — authoritative persistence timestamp.

No reviewer, approval, rejection, waiver, close-state, write-off, accounting, settlement, or shift-transition column is selected.

## Selected type posture

The future migration must follow the existing canonical evidence conventions where compatible:

- tenant/string identities remain bounded string/char types consistent with migrations #22/#23;
- `expected_cash_atomic` is non-negative;
- `observed_closing_cash_atomic` is non-negative;
- `variance_atomic` is signed;
- `currency` is exactly three characters;
- `currency_scale` is a non-negative small integer;
- `cutoff_at_unix` and `recorded_at_unix` use non-negative integer timestamp storage;
- `explanation_text` must support human-readable text without changing the arithmetic evidence.

Sprint68 does not select a UI character counter or presentation truncation rule.

A later source gate must reject empty explanation content before persistence.

## Direction restriction

The selected explanation table is for mandatory non-zero explanation evidence only.

Therefore a future write must accept only:

- `OVER` with `variance_atomic > 0`; or
- `SHORT` with `variance_atomic < 0`.

`MATCH` explanation evidence is not selected by Sprint68.

The database schema may use a bounded string for direction, but application/repository regression must enforce the exact sign/direction invariant fail-closed.

No tolerance-based normalization into `MATCH` is permitted.

## Exact evidence snapshot requirement

Because `CashVarianceResult` is not itself persisted, the durable explanation row must preserve the exact canonical variance snapshot values listed above.

This duplicate snapshot is intentional audit evidence.

It must permit later code to prove which exact variance was explained without recomputing the historical variance from mutable presentation state.

A future repository must verify that all persisted snapshot values originate from the canonical `CashVarianceResult`; they must not be accepted as independent caller overrides.

## Primary key and uniqueness posture

The future table must be tenant-scoped and append-only.

The minimum selected key semantics are:

- primary identity: exact `tenant_id + evidence_id`;
- replay uniqueness: exact `tenant_id + operation_id`;
- one authoritative explanation for the exact selected non-zero variance snapshot under the initial foundation.

The future migration/source-envelope gate must choose the smallest deterministic database uniqueness representation for that exact variance binding.

Sprint68 deliberately does not select an amendment/supersession model.

Therefore a later source implementation must fail closed rather than silently create multiple competing authoritative explanations for the same canonical variance.

## Replay semantics

The canonical pattern from opening/closing cash evidence is retained conceptually:

- same tenant + same operation + same canonical payload -> exact replay returns the original durable evidence;
- same tenant + same operation + conflicting payload -> fail closed;
- same canonical variance + competing independent authoritative explanation -> fail closed under the initial foundation;
- cross-tenant operation identity reuse remains isolated.

Exact replay behavior must be implemented and regression-qualified in a later source sprint.

Sprint68 does not publish idempotency code.

## Foreign-key posture

The future migration must use tenant-scoped restrictive foreign-key relationships where canonical parent keys already exist.

At minimum the selected schema must be bound to:

- the exact POS shift;
- the exact opening cash evidence;
- the exact closing cash evidence;
- the authoritative actor identity;
- the exact organization;
- the exact outlet.

Delete/update cascades that could silently rewrite or erase authoritative explanation relationships are not selected.

The future migration must use restrictive behavior consistent with canonical migrations #22/#23.

Application/repository logic must still prove that all referenced records belong to the same exact canonical variance context; independent foreign keys alone are not sufficient to prove the whole relationship.

## Append-only posture

Authoritative explanation evidence must not be updated in place.

The initial foundation selects:

- insert once;
- exact replay readback;
- no update mutation;
- no delete mutation;
- no supersession implementation;
- no reviewer mutation.

If later product requirements need explanation correction, a separately bounded immutable supersession design must be selected.

Sprint68 does not select that design.

## Repository readiness conclusion

A repository/infrastructure boundary will be required because authoritative explanation evidence must be durable.

However, exact repository interface and adapter paths are not selected in Sprint68.

The future source design must preserve:

- tenant isolation;
- authoritative verified execution context;
- stable operation replay;
- exact variance binding;
- actor attribution;
- append-only persistence;
- fail-closed conflict detection;
- no reviewer or close authority.

Source-envelope readiness is therefore **CONFIRMED**, subject to a later exact path freeze.

## Application boundary readiness

A future application service may accept only:

- one canonical non-zero `CashVarianceResult`;
- one explanation command containing operation identity and explanation content;
- authoritative execution context resolved by existing trusted application boundaries;
- correlation identity according to existing repository conventions.

The caller must not be permitted to independently supply:

- tenant;
- organization;
- outlet;
- shift;
- opening evidence identity;
- closing evidence identity;
- cutoff;
- expected amount;
- observed amount;
- variance;
- direction;
- currency;
- scale;
- actor identity;
- reviewer identity;
- approval state;
- close state.

Those values must be resolved from canonical evidence and trusted context.

## Actor/context readiness

Sprint67 requires authoritative actor attribution.

Sprint68 confirms that actor identity must come from the verified execution context used by the application boundary, not from free-form request data.

This does not yet select:

- a new POS permission;
- which existing role may explain;
- a default grant;
- reviewer eligibility;
- privileged step-up.

Authorization remains a later separately bounded decision.

## Feature/runtime posture

No runtime feature is activated.

The later source foundation, if published, must remain incapable of Production use under the existing lifecycle posture unless separately authorized.

Sprint68 selects no:

- environment flag;
- route;
- controller;
- API resource;
- UI;
- scheduled job;
- queue;
- webhook.

## Migration #25 lifecycle posture

Migration #25 status after Sprint68 is:

**SELECTED SEMANTICALLY / SOURCE NOT PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

This selection means only that migration sequence slot #25 is reserved for the durable explanation-evidence foundation described by this gate.

It does not authorize creating or applying the migration in this Sprint.

The next exact source-envelope gate may freeze migration #25 as one path in a future bounded source publication.

## Historical compatibility requirement

Canonical workflows and regressions currently contain material horizons that expect exactly migrations #1–#24 and reject #25.

A future migration #25 source publication must not weaken business semantics or silently bypass those horizons.

If fresh source qualification reveals historical workflow/oracle incompatibility caused solely by the legitimate migration #25 selection, that incompatibility must be:

1. classified separately;
2. corrected in a bounded workflow-only predecessor;
3. fresh-qualified;
4. merged before source replay;
5. followed by byte-identical replay of the frozen source envelope.

No fake-green CI and no generic unknown-shape bypass are permitted.

## Fail-closed readiness requirements

Any later source implementation must fail closed when:

- variance is `MATCH`;
- variance direction/sign is inconsistent;
- explanation is empty;
- trusted actor context is absent;
- exact tenant/organization/outlet/shift binding cannot be proven;
- opening/closing evidence identity does not match the canonical variance;
- cutoff differs;
- expected/observed/variance values differ;
- currency or scale differs;
- operation replay conflicts;
- another authoritative explanation already exists for the same selected variance under the initial foundation;
- caller attempts to submit reviewer/approval/close state;
- repository persistence is disabled;
- runtime class is not explicitly permitted by the future bounded source foundation.

## Exact source-envelope posture

Sprint68 does not freeze the complete future source path set.

The next bounded Sprint should freeze the minimum path envelope for:

- one migration #25 file;
- one immutable explanation command/result contract as needed;
- one application service;
- one application repository port;
- one infrastructure adapter;
- one dedicated regression;
- one dedicated workflow;
- one source-foundation document;
- only the minimum provider/config binding if strictly required for construction and regression.

Controller, route, UI, permission, reviewer, MFA, close-state, and deployment paths must remain excluded.

## Explicit non-scope

Sprint68 does not select or implement:

- migration #25 source publication;
- migration execution/application;
- exact repository path names;
- exact adapter path names;
- exact application class names;
- full source fingerprint;
- reason-code catalog;
- explanation supersession;
- explanation deletion;
- reviewer/supervisor policy;
- approval/rejection policy;
- maker-checker;
- permissions/default grants;
- privileged MFA/step-up;
- close authority;
- close concurrency/idempotency implementation;
- final evidence freshness window;
- final shift-state transition;
- controlled reopen;
- late-event remediation;
- arbitrary cash movement;
- denomination counting;
- settlement/provider reconciliation;
- accounting/general ledger;
- controller/route/API/UI;
- Production runtime activation;
- deployment/release;
- updater activation;
- Technical Preview activation;
- Production activation;
- rollback/destructive database operations.

## Next bounded gate

After Sprint68 is canonical, the next bounded Sprint should freeze the **exact durable explanation source envelope** only.

That Sprint should:

- inspect collisions against canonical source;
- select exact class/interface/adapter/test/workflow/doc paths;
- include the selected migration #25 path;
- freeze sorted path count and SHA-256 fingerprint;
- preserve append-only exact-replay semantics;
- preserve current lifecycle locks;
- not publish the source implementation yet.

A subsequent Sprint may then publish that exact envelope, subject to fresh historical compatibility qualification.

## Migration and lifecycle lock

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **SELECTED SEMANTICALLY / SOURCE NOT PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

JRN-010 Shift Close: **NOT SELECTED**

Attribution: **Lab | zefry**

# JRN-010 Prerequisite — Non-Zero Cash Variance Explanation Durability Readiness Gate

Author by Lab | zefry

## Status

`SPRINT67 READINESS GATE ONLY / NON-ZERO EXPLANATION MUST BE DURABLE AND AUDITABLE / TRANSIENT EXPLANATION NOT SOURCE-READY / NO SCHEMA SELECTED / MIGRATION #25 NOT SELECTED / NO REVIEWER POLICY / NO CLOSE AUTHORITY / JRN-010 SHIFT CLOSE NOT SELECTED`

Sprint67 determines only whether mandatory explanation evidence for canonical non-zero cash variance may remain transient or must become durable before any reviewer or final-close policy can safely consume it.

It does not publish explanation source implementation, schema, repository, infrastructure adapter, controller, route, UI, permission, reviewer workflow, privileged step-up, close authority, concurrency/idempotency, final shift transition, deployment, release, updater activation, Technical Preview activation, Production activation, migration execution, rollback, or destructive database authority.

## Canonical basis

The canonical baseline is `0857374a78bb94985ec692c48ba2279c943530d6`.

Canonical Sprint64 publishes immutable `CashVarianceResult` evidence.

Canonical Sprint65 classifies:

- exact `MATCH` as mechanically reconciled evidence only;
- `OVER` as unresolved variance evidence;
- `SHORT` as unresolved variance evidence.

Canonical Sprint66 selects:

- automatic tolerance exactly `0` atomic units;
- no explanation required for exact `MATCH` under that policy;
- explanation required for every `OVER` and `SHORT`;
- explanation cannot alter expected cash, observed cash, signed variance, direction, evidence identity, cutoff, currency, or scale;
- explanation is not reviewer approval and not close authority.

Sprint67 does not reopen those semantics.

## Readiness conclusion

Mandatory explanation evidence for `OVER` and `SHORT` must be durable and auditable before any later reviewer, approval, rejection, remediation, or final-close eligibility policy may depend on it.

A purely transient explanation is **not source-ready** for the JRN-010 decision chain.

The reason is structural, not presentation-driven:

- a later reviewer must be able to prove which exact explanation was reviewed;
- later policy must be able to prove which exact variance evidence the explanation belonged to;
- explanation must survive request/session/process boundaries;
- explanation must remain available for audit after subsequent decisions;
- a later close decision must not depend on ephemeral caller memory;
- explanation must not be silently replaced between submission and review.

Therefore transient-only explanation would create an unverifiable decision dependency and is rejected for the governed path.

## Durable evidence requirement

Future explanation source, if separately selected, must create durable evidence representing an intentional explanation for one exact canonical non-zero `CashVarianceResult`.

Durability means the evidence must survive:

- request completion;
- process restart;
- application restart;
- reviewer handoff;
- later audit;
- later policy evaluation.

Durability does not itself mean a specific database technology or table shape is selected in Sprint67.

## Exact binding requirement

Each durable explanation must be exact-bound to the canonical variance evidence it explains.

At minimum, the future durable contract must make it impossible to ambiguously reuse an explanation across a different:

- tenant;
- organization;
- outlet;
- shift;
- opening cash evidence identity;
- closing cash evidence identity;
- canonical cutoff;
- expected cash atomic value;
- observed closing cash atomic value;
- signed variance atomic value;
- variance direction;
- currency;
- currency scale.

A later implementation may persist a canonical evidence fingerprint instead of duplicating every field only if the exact binding remains independently verifiable and fail-closed.

Sprint67 does not select a fingerprint algorithm or persistence representation.

## Explanation identity requirement

A durable explanation requires its own stable evidence identity.

The future explanation identity must:

- be non-empty;
- identify exactly one explanation evidence record/version;
- not be derived from mutable free text alone;
- not grant permission, reviewer status, approval, or close authority.

Sprint67 does not select UUID/ULID/database-key format.

## Actor attribution requirement

A durable explanation must record the authoritative actor identity that intentionally supplied it.

Actor attribution is audit evidence only.

It does not select:

- which role is allowed to explain variance;
- a permission name;
- a default grant;
- reviewer eligibility;
- maker-checker;
- privileged MFA/step-up.

A later authorization gate must separately determine who may create explanation evidence.

The future durability design must not infer actor identity from presentation labels or client-supplied display names.

## Recorded-time requirement

A durable explanation must contain an authoritative recorded timestamp suitable for audit ordering.

The recorded timestamp must not:

- change the canonical variance cutoff;
- alter expected cash;
- alter observed closing cash;
- determine variance direction;
- create tolerance;
- grant approval or close authority.

Sprint67 does not select amendment ordering, freshness windows, or final-close cutoff semantics.

## Content requirement inherited from Sprint66

The explanation content must remain intentionally supplied and non-empty after the future contract's canonical normalization rules.

Sprint67 does not select:

- reason-code catalog;
- maximum text length;
- localization;
- attachment support;
- evidence upload;
- accounting treatment code;
- reviewer comment structure.

These remain separately bounded.

## Immutability and supersession posture

Once durable explanation evidence becomes authoritative, it must not be silently updated in place.

A later design must use one of these governed patterns:

- immutable explanation evidence with no edit;
- immutable superseding explanation evidence linked to the prior evidence.

Sprint67 selects the immutability requirement but does not select the supersession workflow.

Deletion that would erase authoritative audit evidence is not selected.

## No variance mutation

Durable explanation evidence must never rewrite or recalculate the canonical variance.

The following remain immutable from the explanation perspective:

- expected cash;
- observed closing cash;
- signed variance;
- direction;
- opening/closing evidence identity;
- canonical cutoff;
- currency;
- scale.

A future explanation write must fail closed if a caller attempts to submit overrides for these values.

## Reviewer separation

Durable explanation evidence is a prerequisite input for a possible later reviewer policy.

It is not itself:

- reviewed;
- approved;
- rejected;
- waived;
- reconciled;
- close-authorized.

Sprint67 selects no reviewer/supervisor role, approval state, quorum, maker-checker rule, permission, or privileged step-up.

A later reviewer policy must consume exact durable explanation evidence plus the exact canonical variance evidence without altering either.

## Why transient explanation is rejected

A transient-only explanation would permit materially unsafe ambiguity, including:

- reviewer sees text different from what was originally supplied;
- explanation disappears before review;
- retry produces another explanation with no stable identity;
- close evaluation cannot prove which explanation was consumed;
- audit cannot reconstruct the decision chain;
- mutable runtime state becomes the source of truth.

Therefore the governed JRN-010 path must not treat transient explanation as sufficient evidence.

Transient UI/input state may exist before persistence in a future delivery flow, but it must not count as authoritative explanation evidence.

## Schema and repository readiness posture

Sprint67 establishes that durable storage will be required for authoritative non-zero explanation evidence.

However, exact schema and infrastructure are **not yet selected**.

Sprint67 does not select:

- table name;
- column names;
- indexes;
- foreign-key layout;
- repository interface;
- infrastructure adapter;
- transaction boundary;
- idempotency key;
- unique-key model;
- retention period;
- migration file;
- migration #25.

Migration #25 therefore remains **NOT SELECTED**.

The next bounded gate must decide the minimum durability/schema envelope before source publication.

## Fail-closed requirements

Any future durable explanation implementation must fail closed when:

- canonical variance evidence is absent;
- direction/sign consistency is invalid;
- variance direction is `MATCH` when explanation is being submitted as mandatory non-zero evidence;
- explanation content is empty after canonical normalization;
- explanation identity is missing or malformed;
- actor identity is missing or untrusted;
- recorded timestamp is invalid;
- exact variance binding cannot be proven;
- caller attempts to override canonical variance fields;
- caller attempts to infer approval or close authority from explanation persistence;
- an authoritative explanation is silently mutated in place;
- an unknown explanation lifecycle state is encountered.

## Deterministic dependency posture

For the governed non-zero variance path:

`canonical OVER/SHORT -> durable explanation required -> later reviewer/close policy may be considered`

For exact `MATCH`:

`canonical MATCH -> no explanation required by Sprint66/Sprint67`

Neither path authorizes final close.

## Explicit non-scope

Sprint67 does not select or implement:

- exact explanation source paths;
- exact schema;
- migration #25;
- repository interface;
- infrastructure adapter;
- explanation transaction boundary;
- idempotency/concurrency;
- explanation supersession workflow;
- deletion/retention policy;
- reason-code catalog;
- permissions/default grants;
- reviewer/supervisor policy;
- approval/rejection policy;
- maker-checker;
- privileged MFA/step-up;
- close authority;
- final evidence freshness window;
- final shift-state transition;
- controlled reopen;
- late-event remediation;
- arbitrary cash movement;
- denomination counting;
- settlement/provider reconciliation;
- accounting/general ledger;
- endpoint/controller/route/API resource;
- UI/reporting;
- runtime feature flag;
- deployment/release;
- updater activation;
- Technical Preview activation;
- Production activation;
- migration execution/application;
- rollback/destructive database operations.

## Next bounded gate

After Sprint67 is canonical, the next bounded Sprint should define the **minimum durable explanation schema/source-envelope readiness boundary only**.

That gate may evaluate whether migration #25 should be selected for one append-only explanation-evidence structure, but it must not combine:

- schema selection;
- source implementation;
- reviewer approval;
- permission/default grants;
- privileged step-up;
- close concurrency;
- final shift transition;
- runtime delivery.

If migration #25 is eventually selected, publication of its source still does not authorize migration execution/application.

## Migration and lifecycle lock

Migration #22: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #23: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #24: **SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**

Migration #25: **NOT SELECTED**

Technical Preview: **INACTIVE**

Production: **NO-GO**

Updater: **INACTIVE**

Deployment/release: **NOT AUTHORIZED**

Migration execution/application: **NOT AUTHORIZED**

Rollback/destructive database operations: **NOT AUTHORIZED**

JRN-010 Shift Close: **NOT SELECTED**

Attribution: **Lab | zefry**

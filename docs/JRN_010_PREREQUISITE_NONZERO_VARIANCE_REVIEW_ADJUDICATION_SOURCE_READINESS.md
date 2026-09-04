# JRN-010 Prerequisite — Non-Zero Variance Review / Adjudication Source Readiness

Author by Lab | zefry

## Sprint76 bounded gate

This document is the Sprint76 docs/planning-only source-readiness gate for the independent reviewer/adjudication decision selected by the canonical Sprint75 policy.

It does not implement reviewer source, select a reviewer permission identifier, select a database schema, publish migration #26, bind runtime infrastructure, or grant Shift Close authority.

## Canonical policy inherited from Sprint75

Sprint76 freezes the following Sprint75 semantics:

- every canonical `OVER` or `SHORT` variance already has durable explanation evidence before review;
- explanation author authority is not reviewer authority;
- the review subject is the exact canonical `CashVarianceResult` plus the exact authoritative current durable explanation evidence;
- reviewer decision is independent, evidence-bound, and uses the policy outcomes `APPROVE` or `REJECT`;
- self-review is fail-closed: the explanation actor cannot be the qualifying reviewer actor;
- reviewer approval is only a future close-policy prerequisite and is not Shift Close authority;
- reviewer rejection does not mutate, delete, roll back, or rewrite explanation evidence;
- neither review outcome may modify opening cash, observed closing cash, expected cash, signed variance, direction, tenant, organization, outlet, shift, evidence binding, cutoff, currency, or scale;
- authoritative reviewer evidence must be durable; transient DTO, cache, UI state, client state, log text, or audit text cannot substitute for authoritative review evidence.

`MATCH` remains outside this reviewer path and remains insufficient by itself to grant Shift Close.

## Targeted canonical source readout

The current canonical source has a dedicated explanation-authoring durability path:

- `apps/web/app/Application/Pos/CashVarianceExplanationRepository.php` is an explanation-specific repository port;
- `apps/web/app/Application/Pos/RecordCashVarianceExplanation.php` is an explanation-authoring application service with scoped authorization before authoritative clock, transaction, and repository persistence;
- `apps/web/app/Infrastructure/Pos/LaravelCashVarianceExplanationRepository.php` is a durable adapter for immutable cash-variance explanation evidence and its replay/conflict semantics.

A targeted canonical source search did not identify an existing dedicated reviewer/adjudication decision write/replay surface that can satisfy the Sprint75 evidence contract.

Therefore the existing explanation repository/service/adapter must not be overloaded to persist reviewer decisions. Reusing explanation evidence as reviewer evidence would collapse two independently governed authorities, weaken maker-checker separation, and create an ambiguous persistence contract.

## Sprint76 readiness decision

The reviewer/adjudication policy is **SEMANTICALLY READY but NOT SOURCE-IMPLEMENTATION READY**.

A dedicated authoritative reviewer-decision durability boundary is required before reviewer source implementation can be safely selected.

The repository currently provides a useful canonical ordering pattern through explanation recording, but that pattern does not remove the need to decide the reviewer-decision persistence contract first.

Sprint76 therefore blocks reviewer source implementation until a bounded persistence/schema decision becomes canonical.

## Future integration responsibilities

Without selecting concrete source files or schema in this Sprint, the future reviewer-decision capability must eventually provide all of the following responsibilities:

1. accept an exact review subject bound to the canonical non-zero variance and authoritative explanation evidence;
2. preserve exact tenant, organization, outlet, shift, evidence, monetary, cutoff, currency, and scale agreement;
3. preserve explanation actor provenance and reviewer actor provenance separately;
4. fail closed when reviewer actor equals explanation actor;
5. require a dedicated scoped reviewer authorization capability distinct from explanation-author permission;
6. represent only the selected reviewer outcomes `APPROVE` or `REJECT`;
7. record an authoritative reviewer timestamp only after prerequisite validation, context checks, self-review denial, and authorization succeed;
8. durably persist the reviewer decision as separate authoritative evidence;
9. provide deterministic replay/conflict behavior appropriate to that reviewer evidence;
10. expose no implicit Shift Close, reconciliation, waiver, write-off, settlement, or final state-transition authority.

The exact reviewer permission identifier remains intentionally unselected in Sprint76.

## Required future ordering boundary

Any later reviewer source must preserve this fail-closed ordering boundary:

1. validate review correlation / request identity;
2. validate canonical non-zero variance subject;
3. resolve and validate authoritative durable explanation evidence;
4. resolve current verified organizational context;
5. enforce exact tenant / organization / outlet / shift agreement;
6. deny self-review before any qualifying persistence side effect;
7. require the future dedicated reviewer permission through canonical scoped authorization;
8. obtain authoritative clock time;
9. enter the persistence transaction;
10. record or replay authoritative reviewer-decision evidence.

Denied scope, missing permission, wrong permission, cross-scope grant, malformed subject, missing authoritative explanation, or self-review must not reach authoritative clock, transaction, or reviewer repository persistence.

This ordering is a readiness constraint only; it is not a source implementation selection.

## Persistence/schema gate required next

Because Sprint75 requires reviewer decisions to become authoritative durable evidence and current canonical explanation persistence cannot safely double as reviewer persistence, Sprint77 must decide the smallest dedicated persistence/schema boundary.

Sprint77 must determine, without executing any migration:

- whether reviewer decisions require a separate durable relation or another explicitly dedicated persistence shape;
- the minimum immutable evidence identity and subject binding required for authoritative review;
- uniqueness, replay, and conflicting-replay invariants;
- how self-review denial is protected at the application and persistence boundaries without role-based bypass;
- restrictive relationships needed to preserve exact tenant / organization / outlet / shift / explanation linkage;
- whether a new migration source is required and, only if proven necessary by that gate, whether migration #26 should be selected for source publication.

Until Sprint77 is canonical, **migration #26 remains NOT SELECTED**.

Even if a later gate selects migration #26 source publication, execution/application/activation remains separately unauthorized.

## Explicitly not selected in Sprint76

Sprint76 does not select or implement:

- reviewer permission identifier;
- reviewer permission source;
- reviewer command/result source;
- reviewer repository port source;
- reviewer transaction source;
- reviewer infrastructure adapter;
- table, columns, indexes, foreign keys, constraints, or migration source;
- migration #26;
- provider/config binding;
- controller, route, API, UI, or privileged step-up flow;
- role/default/admin/supervisor/cashier grant;
- Shift Close permission or authority;
- close eligibility implementation;
- close concurrency, close idempotency, or freshness window;
- shift-state transition;
- settlement or GL/accounting behavior;
- deployment/release;
- Technical Preview activation;
- Production activation;
- updater activation.

## Lifecycle posture

No lifecycle posture changes in Sprint76:

- migration #22: SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED;
- migration #23: SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED;
- migration #24: SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED;
- migration #25: SOURCE-PUBLISHED ONLY / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED;
- migration #26: NOT SELECTED;
- Technical Preview: INACTIVE;
- Production: NO-GO;
- updater: INACTIVE;
- deployment/release: NOT AUTHORIZED;
- migration execution/application: NOT AUTHORIZED;
- JRN-010 Shift Close: NOT SELECTED.

## Sprint77 entry gate

The exact next bounded task after Sprint76 is:

**Sprint77 — Non-Zero Variance Reviewer Decision Persistence / Schema Decision Gate**

Sprint77 remains docs/planning-first. It must decide the minimum dedicated durable persistence contract required by the Sprint75 reviewer evidence policy and this Sprint76 readiness result before any reviewer source envelope or implementation is opened.

Sprint77 must not execute/apply a migration, bind runtime infrastructure, implement reviewer business source, or select Shift Close authority.

## Sprint76 status

**SPRINT76 REVIEW / ADJUDICATION SOURCE READINESS = COMPLETE**

**Reviewer source implementation = BLOCKED pending canonical Sprint77 persistence/schema decision.**

Author by Lab | zefry

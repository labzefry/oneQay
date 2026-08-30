# Sprint48 JRN-005 Shift/Register Opening Entry Gate

## Status

**SELECTED / ENTRY-GATE ONLY / NO SOURCE OR SCHEMA AUTHORITY**

Canonical Product Owner attribution: **Lab | zefry**

Date: 2026-08-30

---

## 1. Canonical baseline

This entry gate starts only from the canonical post-Sprint47 publication state.

- Canonical branch: `main`.
- Canonical baseline commit: `b48c11aa531d79187eccb8eb0b09cea9ec949767`.
- Canonical baseline tree: `b076912d5bb63431826f78afb54c9ad8f05748c1`.
- Sprint47 JRN-004 source is published through PR #440 / merge `77ca26f06054b190b3b3ace9e51f875ec255316b`.
- Canonical source contains migrations exactly #1 through #17.
- Migration #16 is **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.
- Migration #17 is **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.
- Technical Preview remains **NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED**.
- Production remains **NO-GO / NOT AUTHORIZED**.
- Updater remains **DISABLED / UNWIRED**.
- No deployment, release, migration execution, rollback, or Production authority is created here.

This gate does not reinterpret stale probes, closed unmerged branches, or historical planning text as current authority.

---

## 2. Selected Sprint48 concern

Sprint48 selects exactly one bounded Business MVP successor concern:

**JRN-005 — Tenant/Outlet-Scoped Shift/Register Opening Accountability Foundation**

This is a **foundation**, not completion of the full JRN-005 or JRN-010 operational-cash lifecycle.

The repository-native journey model defines JRN-005 as shift/register opening and identifies an active shift as a precondition for JRN-006 sale execution. Sprint46 intentionally excluded JRN-005 from the bounded JRN-006 sale-completion foundation, and Sprint47 selected JRN-004 catalog preparation instead.

Sprint48 therefore addresses the smallest remaining operational precondition immediately adjacent to the already-published catalog and sale foundations.

---

## 3. Bounded business objective

The selected concern may eventually allow an authorized operator to establish one accountable operational shift for the exact current tenant/outlet/register context.

The minimum intended outcome is:

1. an authorized operator requests shift opening;
2. the system binds the request to the current authenticated server-owned organizational context;
3. the register/operational context is resolved within that exact tenant/outlet boundary;
4. the system denies opening when another active shift already occupies the exact governed register context;
5. the successful result records immutable opening accountability evidence;
6. exact retry is safe and does not create a second active shift;
7. conflicting retry fails closed.

This entry gate does not yet authorize implementation of those semantics.

---

## 4. Repository-native journey evidence

The current journey inventory already defines:

- JRN-005: **Shift/register opening**;
- actors: Cashier and Outlet Manager;
- outcome: register ready to transact with accountability;
- preconditions that include outlet, register, cashier access, and opening policy;
- the `OpenShift -> ShiftOpened` command/event pair;
- a one-active-shift-per-context invariant according to policy;
- opening evidence as an auditable requirement.

The current JRN-006 journey separately identifies **Active shift** as a sale precondition.

Sprint48 selects only the bounded opening/accountability foundation needed to make that dependency governable later.

---

## 5. Explicit in-scope semantics

A later Sprint48 schema/source-envelope gate may analyze and freeze only the following source concern:

- a shift-opening command;
- an immutable shift identifier owned by the server/persistence boundary;
- exact tenant and outlet binding;
- exact operator identity binding;
- exact register/operational-context binding;
- server-owned opened-at time;
- deterministic operation/correlation identity;
- one-active-shift conflict denial;
- exact replay / conflicting replay behavior;
- deny-by-default authorization;
- bounded Local/Test/CI delivery only when explicitly feature-armed;
- regression evidence for tenant isolation and concurrency/conflict behavior.

No broader source authority is implied.

---

## 6. Explicitly excluded semantics

Sprint48 does **not** select or authorize:

- shift close;
- JRN-010 payment reconciliation;
- cash count;
- cash variance;
- denomination management;
- cash drawer hardware integration;
- opening cash amount policy;
- cash movement ledger;
- settlement/import reconciliation;
- provider reconciliation;
- register creation or broad register administration;
- device enrollment;
- outlet creation/configuration;
- catalog CRUD;
- price engine;
- tax/fiscal configuration;
- stock receiving;
- stock transfer;
- stock count;
- stock adjustment;
- purchasing/supplier lifecycle;
- void;
- cancellation;
- return;
- refund;
- discount/promotion policy;
- customer/CRM;
- accounting;
- external payment-provider integration;
- offline POS;
- Production reporting;
- deployment or release.

A future concern must separately gate any of these areas.

---

## 7. Tenant and authority boundary

Any future Sprint48 source must preserve the canonical security model:

- tenant is server-derived;
- identity is server-derived;
- organization is server-derived;
- outlet is server-derived;
- device is server-derived;
- session authority is server-derived;
- role and permission authority are not accepted from caller input;
- correlation/event time are server-controlled;
- cross-tenant or cross-outlet register resolution fails closed.

A public register identifier, if later required at a delivery boundary, is not itself authority. It must resolve inside the exact server-owned tenant/outlet/device context before use.

Caller-controlled tenant, organization, outlet, device, actor, role, permission, or session authority remains forbidden.

---

## 8. Authorization posture

The candidate bounded permission is:

`pos.shift.open`

This entry gate only freezes the intended deny-by-default authorization meaning. It does not add the permission to source and does not grant it to any role.

No default grant is authorized.

Any later source gate must prove compatibility with the canonical durable scoped authorization policy before source publication.

---

## 9. Register context boundary

The current canonical POS execution context already carries verified tenant, organization, outlet, device, and actor identity.

A dedicated durable register/shift persistence model is not yet selected by this entry gate.

The next schema/source-envelope gate must determine, from exact canonical source, whether:

1. existing tenant/outlet/device structures can safely bind a bounded register identity without schema change; or
2. one separately bounded migration is required.

No register schema, shift schema, table name, column list, or migration number is frozen here.

---

## 10. One-active-shift invariant

The future source design must preserve a fail-closed invariant equivalent to:

> Within the exact governed register context, a new active shift cannot be created while another active shift is still active.

The exact uniqueness/concurrency mechanism is deferred to the schema/source-envelope gate.

The implementation must not rely only on an application-level pre-check if concurrent requests could create two active shifts.

---

## 11. Idempotency and replay

The source gate must define an exact durable replay boundary before implementation.

The intended minimum is:

- stable server-validated operation identity;
- exact tenant binding;
- exact outlet/register context binding;
- exact actor binding where semantically required;
- exact replay returns the original successful opening result without creating another shift;
- conflicting reuse of the same operation identity fails closed.

This entry gate does not select a journal table or migration.

---

## 12. Opening evidence

The minimum future opening evidence may include only:

- shift identity;
- tenant/outlet/register binding;
- actor identity;
- operation identity;
- correlation identity;
- server-owned opened-at time;
- resulting active state.

**Opening cash amount is explicitly excluded from this Sprint48 foundation** because cash-management and reconciliation policy remain separate concerns.

This avoids silently creating JRN-010 or cash-control semantics inside a shift-opening foundation.

---

## 13. Relationship to JRN-006 sale completion

Sprint48 does not modify JRN-006 source through this entry gate.

The canonical JRN-006 sale-completion foundation remains source-published with its existing bounded semantics.

A later Sprint48 schema/source-envelope gate must explicitly decide whether the bounded Sprint48 source envelope includes:

- only shift opening; or
- one exact fail-closed integration guard that requires the current active shift before sale completion.

No implicit JRN-006 mutation authority is created here.

Completed sale records and sale-line price snapshots remain immutable.

---

## 14. Relationship to JRN-004 catalog preparation

Sprint47 JRN-004 remains unchanged.

Sprint48 does not authorize:

- caller-controlled stock quantity;
- broad catalog administration;
- repricing completed sales;
- mutation of JRN-004 preparation journal history.

Catalog sellability and current price remain distinct from shift state.

---

## 15. Schema decision

**Migration #18 is NOT SELECTED.**

This entry gate creates no migration file and no schema authority.

The next separately bounded schema/source-envelope gate must inspect exact canonical source and choose one of:

- **NO_SCHEMA_CHANGE**, or
- one exact bounded source-only migration proposal.

Even if a migration is later selected, execution/application/activation remains separately unauthorized.

---

## 16. Source-envelope decision

No application source path is authorized by this entry gate.

A later schema/source-envelope gate must freeze:

- exact changed-file list;
- sorted newline-terminated path fingerprint;
- exact permission/source integration points;
- exact migration posture;
- exact feature flag posture;
- exact regression workflow;
- historical compatibility requirements.

Unknown changed-file shapes must remain fail-closed.

---

## 17. Feature/runtime posture

Any future Sprint48 mutation boundary must remain:

- default disabled;
- Local/Test/CI only unless separately authorized;
- unavailable in Production by default;
- unable to create deployment/release/updater authority;
- unable to execute migrations.

Candidate feature naming may be analyzed later, but no configuration key is frozen by this gate.

---

## 18. Required regression properties for the next gate

The next gate must require evidence for at least:

- exact tenant isolation;
- exact outlet/register context isolation;
- deny-by-default permission;
- unauthorized operator denial;
- duplicate-open denial;
- concurrent-open safety;
- exact replay;
- conflicting replay denial;
- caller-selected authority rejection;
- immutable opening evidence;
- no cross-tenant/outlet leakage;
- no JRN-004 catalog regression;
- no JRN-006 completed-sale mutation;
- historical migration/source preservation;
- tracked-source cleanliness.

No fake-green or skipped material runner may qualify the source.

---

## 19. Historical compatibility posture

Sprint48 must preserve the existing exact historical regressions for:

- M7.1 through M7.6;
- identity/access Sprint21 through Sprint43;
- Sprint46 JRN-006;
- Sprint47 JRN-004;
- governance;
- PHP foundation;
- updater/deployment-control safety.

Compatibility work, if required by exact failure evidence, must remain workflow-only and bounded.

A run with `jobs=[]` is not success.

---

## 20. Lifecycle locks

This gate does not authorize:

- migration #18;
- migration execution;
- migration application;
- migration activation;
- Technical Preview schema change;
- Technical Preview activation;
- Production activation;
- deployment;
- release;
- updater wiring/activation;
- rollback;
- direct mutation of `main`;
- force update;
- branch-protection bypass.

Migrations #16 and #17 remain source-published but unapplied/unactivated.

---

## 21. Required next bounded gate

If this entry gate is published, the next logical Sprint48 task is:

**Sprint48 JRN-005 Shift/Register Opening Schema & Source Envelope Gate**

That next gate must inspect the exact then-current canonical `main` before selecting any source path or migration posture.

It must not treat this entry gate as migration, runtime, deployment, or Production authority.

---

## 22. Entry-gate conclusion

Sprint48 selects only:

**JRN-005 — Tenant/Outlet-Scoped Shift/Register Opening Accountability Foundation**

The concern is bounded to opening one accountable operational shift in the correct governed context, with deny-by-default authorization, one-active-shift safety, replay safety, and immutable opening evidence.

This entry gate creates **documentation authority only**.

**Migration #18 remains NOT SELECTED.**

**Technical Preview remains NOT ACTIVATED.**

**Production remains NO-GO.**

Attribution: **Lab | zefry**

# Sprint49 JRN-006 Active Shift Sale-Completion Precondition Entry Gate

## Status

**SELECTED / ENTRY-GATE ONLY / NO SOURCE OR SCHEMA AUTHORITY**

Canonical Product Owner attribution: **Lab | zefry**

Date: 2026-09-01

---

## 1. Canonical baseline

This entry gate starts only from canonical post-Sprint48 publication and reconciliation state.

- Canonical branch: `main`.
- Canonical baseline commit: `de2f9d8cb19300e0578a649607c9b30ad604b010`.
- Canonical baseline tree: `d637531a59da071ba875043c3583a2cefbe269ec`.
- Sprint46 JRN-006 sale/payment/receipt foundation is source-published.
- Sprint47 JRN-004 catalog sellability/current-price preparation foundation is source-published.
- Sprint48 JRN-005 shift/register opening foundation is source-published through PR #458.
- Canonical source contains migrations exactly #1 through #18.
- Migrations #16, #17, and #18 are **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.
- Technical Preview remains **NO_SCHEMA_CHANGE / NOT ACTIVATED**.
- Production remains **NO-GO / NOT AUTHORIZED**.
- Updater remains **NOT ACTIVATED**.
- Deployment, release, migration execution/application, rollback, force update, and direct protected-main mutation remain unauthorized.

This gate does not reinterpret closed probes, stale branches, historical planning text, or source publication as runtime/deployment authority.

---

## 2. Selected Sprint49 concern

Sprint49 selects exactly one bounded Business MVP dependency-integration concern:

**JRN-006 — Active Shift Sale-Completion Precondition Integration Foundation**

The canonical journey model states that an **Active shift** is a precondition for JRN-006 sale execution.

Sprint48 intentionally published durable JRN-005 shift opening without modifying JRN-006. Its canonical foundation explicitly requires a later separately bounded gate to decide how active-shift evidence becomes a fail-closed sale-completion precondition.

Sprint49 therefore selects only that missing dependency boundary.

This is not shift-close implementation, cash reconciliation, or completion of the wider JRN-005/JRN-010 lifecycle.

---

## 3. Bounded business objective

The future bounded integration may allow JRN-006 sale completion to proceed only when the exact current server-verified register execution context has an active shift.

The minimum intended behavior is:

1. the cashier initiates the already-governed JRN-006 sale-completion operation;
2. tenant, identity, organization, outlet, and device-backed register context remain reconstructed from server-owned session/context evidence;
3. before a fresh sale completion mutates durable sale/payment/stock state, the system resolves active-shift evidence inside that exact governed context;
4. no matching active shift causes deterministic fail-closed denial;
5. an active shift from another tenant, outlet, or device never satisfies the precondition;
6. caller-supplied shift/register authority is rejected;
7. existing JRN-006 idempotency and replay semantics are preserved;
8. completed sale evidence remains immutable.

This entry gate does not authorize implementation of those semantics.

---

## 4. Repository-native dependency evidence

Canonical repository evidence establishes:

- JRN-005: **Shift/register opening**;
- JRN-006: **Sale, payment recording, and receipt**;
- JRN-010: **Shift close and payment reconciliation**;
- the JRN-006 journey precondition explicitly includes **Active shift**;
- Sprint48 source created durable active-shift evidence for the exact tenant/outlet/device-backed register execution context;
- Sprint48 intentionally excluded JRN-006 active-shift enforcement;
- Sprint48 canonical documentation requires a later separately bounded gate for this integration.

Sprint49 closes only this dependency gap at governance-selection level.

---

## 5. Explicit in-scope semantics

A later Sprint49 schema/source-envelope gate may analyze and freeze only the minimum integration needed to prove:

- JRN-006 fresh sale completion requires an active shift;
- active-shift lookup is tenant scoped;
- active-shift lookup is outlet scoped;
- active-shift lookup is device-backed register-context scoped;
- caller cannot select a shift, register, tenant, outlet, device, actor, role, permission, or session authority;
- missing active shift fails closed before sale/payment/stock mutation;
- wrong-context active shift fails closed;
- existing `pos.sale.complete` authorization remains mandatory;
- existing JRN-006 durable operation idempotency and semantic fingerprint remain deterministic;
- exact replay behavior does not create duplicate sale/payment/stock effects;
- completed sale and line-price evidence remains immutable;
- JRN-004 catalog state remains independently governed;
- JRN-005 opening evidence is not rewritten by sale completion.

No broader source authority is implied.

---

## 6. Explicitly excluded semantics

Sprint49 does **not** select or authorize:

- shift close;
- JRN-010 shift close/payment reconciliation;
- opening cash;
- closing cash;
- cash count;
- cash variance;
- denomination management;
- cash movement ledger;
- drawer hardware integration;
- settlement/import reconciliation;
- provider reconciliation;
- register creation or broad register administration;
- device enrollment;
- outlet administration;
- caller-selected shift identity;
- shift reassignment;
- shift reopen;
- shift transfer/handoff;
- changing JRN-005 one-active-shift uniqueness;
- broad catalog administration;
- stock receiving/transfer/count/adjustment;
- purchasing or supplier lifecycle;
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
- deployment;
- release;
- migration execution/application/activation;
- Technical Preview activation;
- Production activation;
- updater activation;
- rollback.

Each remains separately governed.

---

## 7. Tenant, outlet, and register-context boundary

Any future Sprint49 source must preserve canonical server-owned context:

- tenant is server-derived;
- identity is server-derived;
- organization is server-derived;
- outlet is server-derived;
- device-backed register execution context is server-derived;
- session authority is server-derived;
- role/permission authority is not accepted from caller input;
- correlation identity and event time remain server-controlled.

The active shift must be resolved inside the exact verified tenant + outlet + device-backed register context.

An active shift belonging to another device at the same outlet must not satisfy the current device's sale precondition.

An active shift from another outlet or tenant must never satisfy it.

---

## 8. Authorization posture

The canonical sale-completion permission remains:

`pos.sale.complete`

This entry gate does **not** select a new permission and does not grant any permission to a role.

Active-shift presence is intended as an additional fail-closed operational precondition, not a replacement for authorization.

A later source gate must inspect the exact current authorization path before freezing implementation points.

---

## 9. Caller-input boundary

This entry gate does not expand the JRN-006 caller authority surface.

The future integration must not accept caller-selected:

- `tenant_id`;
- `organization_id`;
- `outlet_id`;
- `device_id`;
- `register_id`;
- `shift_id`;
- actor identity;
- role;
- permission;
- session authority;
- active-state assertion.

If a shift identifier is later included in durable sale evidence, it must be server-resolved and separately frozen by the schema/source-envelope gate.

---

## 10. Replay and mutation ordering

The next gate must inspect exact canonical JRN-006 replay behavior before selecting implementation ordering.

The intended safety requirements are:

- exact replay of an already-completed operation remains deterministic;
- replay must not create a second sale, payment, stock decrement, receipt, or shift mutation;
- a fresh operation with no active shift must fail before sale/payment/stock mutation;
- conflicting operation reuse remains fail closed;
- Sprint49 must not silently weaken the existing `tenant_id + operation_id` durability boundary.

Whether active-shift validation is required again for replay of an already-completed sale must be explicitly decided by the source-envelope gate from canonical idempotency semantics. This entry gate does not invent that behavior.

---

## 11. Shift evidence immutability

Sprint49 does not authorize mutation of JRN-005 opening evidence.

The future integration is read/precondition oriented unless a separately bounded source gate proves that immutable sale-to-shift linkage evidence is necessary.

It must not:

- close a shift;
- alter `active_slot`;
- alter `opened_at_unix`;
- rewrite the opening operation/correlation identity;
- move a shift to another tenant/outlet/device;
- create a second shift as a side effect of sale completion.

---

## 12. Schema decision

**No Sprint49 schema decision is made by this entry gate.**

Migration #19 is **NOT SELECTED**.

Migration #18 remains source-published but unexecuted/unapplied/unactivated.

The next separately bounded schema/source-envelope gate must inspect the exact then-current canonical source and choose one of:

- **NO_SCHEMA_CHANGE**, if the precondition can be integrated safely using existing durable shift evidence without expanding required sale evidence; or
- one exact bounded source-only schema proposal if immutable sale-to-shift linkage or another proven integrity requirement cannot be satisfied otherwise.

No migration may be created, executed, applied, or activated by this entry gate.

---

## 13. Source-envelope decision

No application source path is authorized by this entry gate.

The next gate must inspect exact canonical:

- JRN-006 application service/repository boundaries;
- JRN-006 durable repository adapter;
- JRN-005 shift repository/state boundary;
- POS execution context;
- persistence transaction ordering;
- HTTP/runtime feature boundaries;
- dedicated JRN-006/JRN-005 regressions;
- migration horizon #1–#18;
- historical compatibility runners.

Only then may it freeze:

- exact changed-file list;
- sorted newline-terminated path fingerprint;
- exact read/precondition integration interface;
- exact replay ordering;
- exact migration posture;
- exact feature/runtime posture;
- exact regression proof.

Unknown changed-file shapes must remain fail closed.

---

## 14. Runtime posture

Sprint49 creates no new runtime activation.

Canonical defaults remain fail closed:

- JRN-006 sale completion remains Local/Test/CI only and source-default disabled;
- JRN-005 shift opening remains Local/Test/CI only and source-default disabled;
- Technical Preview remains unactivated;
- Production remains unactivated;
- migrations remain unexecuted.

The next source gate must preserve those boundaries unless separate lifecycle authority exists.

---

## 15. Required regression properties for the next gate

The next gate must require evidence for at least:

- sale denied with no active shift;
- sale denied with active shift from another tenant;
- sale denied with active shift from another outlet;
- sale denied with active shift from another device-backed register context;
- sale allowed only with exact current active shift plus existing JRN-006 authorization/context prerequisites;
- caller-selected shift/register authority rejected;
- exact JRN-006 replay preserved;
- conflicting replay denied;
- no duplicate stock/payment/sale effect;
- no JRN-005 opening-evidence mutation;
- no JRN-004 catalog regression;
- tenant isolation preserved;
- transaction ordering fails closed before fresh sale mutation;
- migrations #1–#18 preserved unless one separately frozen future migration is selected;
- historical regressions remain executable;
- tracked-source cleanliness;
- no `jobs=[]` accepted as qualification success.

---

## 16. Relationship to JRN-010

JRN-010 remains separately bounded and **NOT SELECTED** by Sprint49.

Sprint49 does not add close/reconciliation semantics merely because JRN-010 also depends on an active shift.

No expected-vs-actual amount, variance, reviewer, settlement, cash count, payment reconciliation, or final close state is introduced here.

---

## 17. Lifecycle locks

This gate does not authorize:

- migration #19;
- migration execution/application/activation;
- Technical Preview schema change;
- Technical Preview activation;
- Production activation;
- deployment;
- release;
- updater activation;
- rollback;
- direct mutation of protected `main`;
- force update;
- branch-protection bypass;
- CI bypass or fake-green qualification.

Migrations #16, #17, and #18 remain **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**.

---

## 18. Required next bounded gate

If this entry gate is published, the next logical Sprint49 task is:

**Sprint49 JRN-006 Active Shift Sale-Completion Precondition Schema & Source Envelope Gate**

That gate must start from the exact then-current canonical `main`.

It must not treat this entry gate as source, schema, runtime, migration-execution, deployment, release, or Production authority.

---

## 19. Entry-gate conclusion

Sprint49 selects only:

**JRN-006 — Active Shift Sale-Completion Precondition Integration Foundation**

The concern is bounded to making canonical JRN-005 active-shift evidence a fail-closed operational precondition for a fresh JRN-006 sale completion in the exact current server-verified tenant/outlet/device-backed register context.

This entry gate creates **documentation authority only**.

**Migration #19 remains NOT SELECTED.**

**Technical Preview remains NOT ACTIVATED.**

**Production remains NO-GO.**

Exact changed-file envelope:

`docs/SPRINT_49_JRN_006_ACTIVE_SHIFT_SALE_COMPLETION_PRECONDITION_ENTRY_GATE.md`

Sorted newline-terminated path SHA-256:

`3964a91b30218aa2ad2aec1cdb87e1fe12cdb8f65dc2bee146c974cbe60c8d99`

Attribution: **Lab | zefry**

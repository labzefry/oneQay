# ADR-006: Offline POS Semantics and Conflict Resolution

- Status: Accepted — substantive DEC-008 representation, canonical after governed publication
- Date: 2026-08-10
- Decision owner: Product Owner oneQay (`labzefry`)
- Substantive authority: DEC-008 — Offline POS Semantics and Conflict Resolution
- Decision baseline: `21f1b2e938d9f6a42b1dd2ff717c4e049b5119d7`
- Decision baseline tree: `8cf993f0c82c84bdc46a18aa70c4cb5425b89ac6`
- Historical evidence: Issue #23, Technical Preview boundary OFF-1
- Decision record: `docs/handbook/DEC_008_DECISION_RECORD.md`

## Context

ADR-006 began as a **Proposed** Technical Preview v0.0.1 boundary from Issue #23 / OFF-1.

Historical OFF-1 selected:

**ONLINE-ONLY FOR TECHNICAL PREVIEW**.

At that time, offline identity, local storage, conflict, stock, money, sequence, security, replay, synchronization, and reconciliation semantics were intentionally unresolved. The Technical Preview therefore did not accept transactional mutation while connectivity was unavailable.

Substantive DEC-008 now establishes the bounded long-term offline architecture direction while preserving that historical OFF-1 provenance.

## Historical OFF-1 provenance

Historical Technical Preview behavior was:

- no transactional mutation when connectivity was unavailable;
- PWA could cache static assets and display offline status;
- authenticated mutation responses were not service-worker cached;
- no sensitive transactional data was silently persisted to enable offline sale;
- no offline stock reduction;
- no background transactional replay;
- reconnect retry required explicit user action or demonstrably safe idempotent behavior;
- offline queue, conflict resolution, device trust, sequence allocation, and reconciliation remained unresolved;
- Product Owner exact-head approval was required;
- no source-code authority was granted.

OFF-1 is historical provenance only. It must not be rewritten as though the complete DEC-008 architecture had already existed in the original Technical Preview proposal.

## Decision

Canonical current direction:

**STAGED / HYBRID OFFLINE ARCHITECTURE**.

The first bounded MVP remains:

**ONLINE-AUTHORITATIVE TRANSACTIONS**.

Future disconnected mutations, if later separately authorized, remain **PROVISIONAL CLIENT OPERATIONS** until **SERVER VALIDATION AND ACCEPTANCE**.

### O0 — Online-authoritative transactional baseline

Approved.

Sale, payment recording, stock mutation, authoritative shift mutation, void/refund/correction, and final authoritative transaction acceptance require server acknowledgement.

Broad disconnected/local-first transactional POS is not approved for first bounded MVP.

### O1 — Bounded degraded/read-only offline capability

Approved as architecture direction only.

Classified approved non-sensitive or appropriately protected data may support degraded/read-only experience under later implementation authority, data classification, security review, and explicit freshness/version rules.

Cached information remains non-authoritative.

### O2 — Future bounded offline transaction capability

Approved only as a future-compatible architecture direction.

O2 implementation is not authorized by DEC-008 publication.

If later authorized, disconnected mutations must be provisional and cannot become final business truth without server validation and acceptance.

## Offline authority model

The architecture distinguishes:

- locally recorded intent;
- provisional local operation;
- server accepted operation;
- server rejected operation;
- conflicted operation requiring resolution.

Server remains authoritative for:

- transaction correctness;
- tenant authorization;
- inventory acceptance;
- payment sufficiency;
- shift/register validity;
- user/device authorization;
- financial correctness.

## Stable operation identity

Retry-prone and future provisional offline operations require stable operation identity established before first submission and retained across retry, reconnect, synchronization, duplicate detection, client crash recovery, correlation, and audit.

Exact identifier format and physical persistence remain separately gated.

## Replay and idempotency

Synchronization/retry follows these principles:

- deterministic replay;
- bounded idempotency;
- duplicate suppression;
- already-applied detection;
- same operation identity with conflicting semantic payload is rejected;
- retry does not create duplicate business effects;
- partial synchronization produces explicit per-operation outcomes;
- replay evidence remains auditable.

## Conflict classification

Conceptual conflict classes include:

- duplicate/already applied;
- stale price or catalog;
- insufficient/depleted stock;
- invalid or closed shift;
- revoked or expired user authority;
- revoked or compromised device context;
- tenant/outlet authorization mismatch;
- cancelled/voided/reversed business state;
- payment evidence conflict;
- receipt/reference/sequence collision;
- changed server-side rule or invariant;
- causal/dependency failure.

Exact codes, persistence, and UI remain implementation-gated.

## Conflict resolution authority

Silent last-write-wins is not an acceptable default for transactional facts.

Automatic resolution is allowed only where the result is deterministic and proven safe, such as exact duplicate already-applied handling.

Other conflicts may require:

- operator acknowledgement;
- supervisor decision;
- rejection and re-entry;
- support/escalation.

Financial, payment, authorization, tenant-isolation, and transaction-integrity conflicts fail safely rather than silently rewriting facts to make synchronization succeed.

## Inventory boundary

Server remains authoritative for inventory acceptance.

Cached/local stock is advisory and may be stale. Offline state does not create an implicit stock reservation.

A future provisional offline sale may produce explicit stock conflict during server submission. Oversell risk must remain explicit.

Final inventory design and schema remain separately gated.

## Price and catalog boundary

Approved classified catalog/price data may support bounded read-only degraded display under later security and implementation authority.

Cached information needs explicit freshness/version semantics.

If future O2 operations use cached price, that value is provisional transaction evidence and remains subject to server acceptance rules.

Exact freshness duration and stale-price policy remain separately gated.

## Payment offline boundary

DEC-007 remains binding.

Canonical distinctions remain:

- `CASH / CASH_COUNTED`;
- `MANUAL_EXTERNAL / OPERATOR_RECORDED`;
- future `PROVIDER_ELECTRONIC / PROVIDER_VERIFIED`.

First bounded MVP does not authorize offline payment mutation.

Future O2 may remain compatible with provisional `CASH` and `MANUAL_EXTERNAL` operations only under separate implementation authority and server acceptance.

Offline state must not manufacture `PROVIDER_VERIFIED` evidence.

DEC-008 does not authorize offline electronic-provider acceptance, store-and-forward card processing, offline provider authorization, offline QR/QRIS confirmation, locally authoritative provider-electronic success, or real-money provider integration.

## Register and shift boundary

Future offline capability can operate only against previously established bounded tenant/outlet/register/shift context.

First bounded offline direction does not create authoritative disconnected shift opening or closing.

A shift that becomes closed, invalid, or incompatible server-side during disconnection creates explicit conflict handling for affected provisional operations after reconnect.

## Authentication and device trust

DEC-006 remains binding.

Future transactional offline capability requires previously server-authorized bounded context scoped as applicable to identity, tenant, outlet, device/register, permitted capability, and bounded validity.

Cached credentials or old client state do not provide unlimited offline authority.

Privileged operations do not gain broad disconnected authority for convenience.

Because immediate server revocation cannot be observed while disconnected, offline exposure remains bounded and business operations remain provisional until server evaluation.

Exact token/grant representation and numerical expiry remain separately gated. JRN-003 remains unresolved.

## Tenant and outlet context

DEC-005 server-authoritative tenant isolation remains binding.

Offline local state is scoped to validated tenant/outlet context. Missing or ambiguous tenant context fails closed.

Offline mode must not silently allow cross-tenant mutation, cross-outlet mutation, or tenant switching based only on cached/client state.

## PWA and Native Android boundary

Capability is staged.

For first bounded MVP:

- PWA offline transactional mutation is not approved;
- Android offline transactional mutation is not approved.

O1 read-only/degraded capability may later be implemented for either channel under separate security and implementation authority.

If O2 bounded offline transactional capability is later justified and authorized, **Native Android is the preferred initial transactional offline channel**.

PWA transactional offline mutation remains separately gated until sufficient security, lifecycle, storage, and reliability evidence exists.

## Local-data security

Future local persistence remains:

- minimal;
- classified;
- tenant scoped;
- user/device/session scoped where applicable;
- protected according to classification;
- bounded in retention;
- excluded from unsafe logs and analytics;
- invalidated or isolated when tenant/user/session/security context changes where applicable.

Restricted payment/authentication secrets are not ordinary offline business cache.

Exact local database, encryption, secure-storage, keystore, library, and key-management implementation remain separately gated.

## Synchronization ordering and reference allocation

Synchronization uses **bounded causal/dependency ordering**, not a required global total ordering.

Ordering/dependency is preserved only where correctness needs it, such as bounded relationships between shift/register context, sale, payment evidence, dependent correction, cancellation, and reversal.

Client wall-clock ordering alone is insufficient.

Disconnected clients do not receive unrestricted authority to create canonical global receipt/reference sequences.

Preferred future-compatible direction:

**PROVISIONAL LOCAL REFERENCE → AUTHORITATIVE SERVER REFERENCE / SEQUENCE AFTER ACCEPTANCE**.

Preallocated sequence ranges or another fiscal/reference mechanism remain separately gated if later evidence requires them.

## Offline reconciliation

Offline synchronization reconciliation determines whether local operations have converged with server knowledge and records whether each operation is accepted, rejected, conflicted, or pending resolution.

This reconciliation is distinct from payment/provider settlement, provider reconciliation, accounting/general ledger, and physical inventory count.

## Failure recovery

Future synchronization architecture must support:

- resumable synchronization after interruption;
- safe duplicate retry;
- explicit partial-batch results;
- ambiguous timeout handling without assuming success or failure;
- crash recovery using stable operation identity;
- bounded reconnect retry;
- explicit invalid-entry handling;
- visible unrecoverable conflicts.

Invalid or conflicted operations must not be silently discarded merely to empty a local queue.

## Observability and audit

Offline-related audit evidence conceptually supports correlation across:

- operation identity;
- origin/channel;
- device;
- operator;
- tenant;
- outlet;
- register;
- shift where applicable;
- local-recorded time;
- server-received time;
- replay attempts;
- accepted/rejected/conflicted outcome;
- conflict reason;
- conflict-resolution actor/action;
- correlation identity.

Audit excludes Restricted secrets and prohibited sensitive payment credentials.

## Client-clock boundary

Client wall-clock time is metadata/evidence, not authoritative global ordering truth.

Client clock does not independently determine final transaction precedence, payment truth, authorization truth, settlement, tenant authority, or server-state precedence.

Server evidence, stable operation identity, and bounded causal/dependency metadata remain authoritative coordination inputs.

## Security guardrails

- Server-authoritative tenant isolation remains mandatory.
- Server-authoritative authentication/revocation remains mandatory.
- Cached credentials do not provide unlimited authority.
- Local data is minimized and classified.
- Restricted secrets are not ordinary offline business cache.
- Replay and duplicate protection are mandatory.
- Tenant/outlet scope fails closed.
- Lost/revoked-device and stale-authorization risks remain explicit.
- Service-worker/background sync is not transaction-correctness authority.
- Audit evidence excludes prohibited Restricted secrets.

## Alternatives considered

### Online-only forever

Simplest operational model, but unnecessarily prevents future bounded offline evolution where validated business evidence later justifies it.

### Read-only offline only

Useful degraded capability but insufficient as a long-term direction for environments that may later require transactional continuity.

### Broad local-first transactional POS

Provides maximum disconnected capability but introduces premature distributed-system, conflict, security, inventory, payment, sequence, and reconciliation complexity for the first bounded MVP.

### Staged / Hybrid

**Selected direction.**

Keep first-MVP transactions online-authoritative, allow bounded degraded/read-only evolution, and preserve a future path to provisional server-validated offline transactions without granting implementation authority now.

## Consequences

Benefits:

- preserves transaction trust and tenant isolation for first MVP;
- avoids premature local-first complexity;
- keeps Android/PWA architecture future-compatible;
- establishes explicit replay/conflict/security rules before any offline implementation;
- preserves DEC-006 and DEC-007 authority boundaries.

Tradeoffs:

- first MVP cannot finalize business transactions while disconnected;
- future O2 capability requires additional implementation/security evidence;
- cached information must clearly expose degraded/freshness state;
- future provisional operations may be rejected/conflicted after reconnect.

## Explicit non-scope

This ADR does not select or authorize:

- physical schema, table, column, index, SQL, DDL, or migration;
- exact operation-ID representation;
- exact queue implementation, Redis, broker, or message queue technology;
- exact local database technology;
- exact encryption/keystore/secure-storage library;
- exact PWA storage or Background Sync implementation;
- exact synchronization transport;
- exact retry/backoff values;
- exact offline-retention duration;
- exact offline authorization expiry;
- exact conflict UI or technical permission matrix;
- payment provider, payment SDK, or offline electronic-provider processing;
- final inventory architecture;
- accounting/general ledger;
- fiscal/jurisdiction-specific receipt implementation;
- Android or PWA source implementation;
- Sprint 14;
- deployment;
- release;
- production.

## Acceptance and lifecycle boundary

This ADR is Accepted only as the repository representation of the already-approved substantive DEC-008 decision after governed publication.

It does not itself grant implementation authority.

Any later offline implementation must receive separate Product Owner authority and must preserve current DEC-003, DEC-004, DEC-005, DEC-006, DEC-007, security, API, tenant, payment, and lifecycle boundaries.

Phase 0 remains **IN PROGRESS**. Sprint 14 remains **NOT AUTHORIZED**. Final/business/production application implementation remains **BLOCKED / SEPARATELY GATED**. Production readiness remains **NO-GO**.

Attribution: **Lab | zefry**

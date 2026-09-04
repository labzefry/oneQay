# Sprint81 — Synthetic Technical Preview Readiness Gate

Author by Lab | zefry

## 1. Purpose

Sprint81 records the first bounded readiness decision after the canonical Sprint80 reviewer-authorization implementation and the merged end-to-end Synthetic Technical Preview cash-control journey.

This gate is **readiness-only**. It does not activate Technical Preview, execute migrations, publish a deployment, enable Production, wire the updater, or grant any new runtime/business authority.

Canonical source baseline entering this gate:

- reviewer authorization policy and implementation are canonical through Sprint80 and PR #609;
- the bounded Synthetic Technical Preview cash-control journey is canonical through PR #610 as merge `04997a3bbccc3384ff8d3df0cfd098efbf308b46`;
- the exact source journey covers verified synthetic sign-in, verified tenant/outlet context, session-scoped cash shift opening, CASH/manual sale, server cash ledgering, observed closing cash, server-derived expected cash, and canonical `MATCH` / `OVER` / `SHORT` reconciliation;
- PR #610 exact source head `d14d89ec487e1b414eda43aba4619bf75dbc3c38` qualified Governance, PHP Foundation, M7.1, and M7.4A with all materially triggered workflows successful before Product Owner exact-head authorization and squash publication.

## 2. Current source posture

The current Synthetic Technical Preview source is intentionally fail-closed:

- `ONEQAY_TECHNICAL_PREVIEW_ENABLED` remains `false` by default in `apps/web/environment.example`;
- the Preview controller accepts only runtime classes `local`, `test`, `testing`, `ci`, or `preview` when the explicit Preview flag is true;
- Production runtime is not in the Preview runtime allowlist;
- the Preview ServiceProvider loads its dedicated cash-control route file only when the explicit Preview flag is true;
- ServiceProvider-owned cash-control routes use the `web` middleware so Preview session and CSRF protections are present without returning route ownership to shared `routes/web.php`;
- Preview pages continue to publish `productionReady = false`;
- synthetic principal/context/catalog data remain deterministic Preview fixtures rather than real tenant/customer credentials or provider data;
- the cash shift, receipt, and reconciliation journey remains session-scoped Preview state and is not durable Shift Close evidence;
- no new schema or migration source was added by the Preview cash-control journey.

## 3. Readiness decision

**Decision: SOURCE-READY / NOT ACTIVATION-READY.**

The end-to-end synthetic business journey is sufficiently source-qualified to proceed to runtime-readiness work, but Technical Preview activation is **not authorized** by Sprint81.

The current blocking runtime concern is session continuity.

`apps/web/environment.example` currently uses:

```text
SESSION_DRIVER=array
```

The browser journey spans multiple requests and depends on server-trusted session continuity for:

- allowlisted synthetic principal identity;
- verified context selection;
- active synthetic cash shift;
- receipt projection;
- reconciliation projection;
- logout invalidation and CSRF regeneration.

An in-memory `array` session is appropriate for bounded test execution but must not be treated as an approved deployed Preview session architecture. Sprint81 therefore refuses to equate passing CI with deployed Preview readiness.

## 4. Required conditions before any Technical Preview activation may be considered

A later bounded gate must select and qualify all of the following before activation authority can be requested:

1. **Preview session runtime**
   - explicit server-controlled session persistence across browser requests;
   - no caller-selected tenant, organization, outlet, device, actor, role, permission, shift identity, or reconciliation authority;
   - session fixation resistance, regeneration on sign-in, invalidation on logout, and CSRF continuity;
   - secure cookie transport and scope appropriate to the selected Preview host;
   - bounded expiration and stale-session behavior;
   - fail-closed behavior if the session backend is unavailable or corrupted.

2. **Preview-only runtime boundary**
   - `ONEQAY_RUNTIME_CLASS=preview` must remain distinct from Production;
   - explicit `ONEQAY_TECHNICAL_PREVIEW_ENABLED=true` must be necessary, never implicit;
   - switching the flag off must remove the dedicated Preview route surface without requiring business-data rollback;
   - Production must remain denied even if a Preview flag is misconfigured.

3. **Synthetic-data boundary**
   - only deterministic synthetic principals, contexts, catalog items, sale evidence, cash evidence, and reconciliation evidence;
   - no real customer, worker, merchant, payment-provider, financial-account, or Production credential data;
   - no provider-verified payment claim for manual/external tender.

4. **No-schema-change boundary**
   - Technical Preview must not require execution of source-published business migrations merely to exercise the synthetic journey;
   - `ONEQAY_PREVIEW_DB_QUALIFICATION_ENABLED` remains a separate qualification concern and is not implied by activating the synthetic journey;
   - no migration execution, application, activation, rollback, or destructive database authority is created by this readiness gate.

5. **Executable smoke qualification**
   - synthetic sign-in;
   - verified context selection;
   - open one synthetic cash shift;
   - reject sale before shift open;
   - complete CASH sale with authoritative total and change;
   - exclude non-CASH/manual external tender from the cash-sales ledger;
   - close with observed cash;
   - derive expected cash server-side;
   - project canonical `MATCH`, `OVER`, or `SHORT` reconciliation;
   - ensure closed shift no longer projects as active;
   - logout and deny post-logout Preview access.

6. **Operational off-switch and observability**
   - a deterministic Preview disable path must be proven before activation;
   - runtime logs must preserve correlation identity without exposing secrets;
   - activation must not authorize deployment/release publication beyond the separately approved Preview deployment boundary.

## 5. Explicitly not selected in Sprint81

Sprint81 does **not** select or authorize:

- a concrete deployed session backend;
- Redis, database-session, file-session, or other session infrastructure;
- new session schema or migration;
- migration execution for existing source-published migrations;
- durable Preview Shift Close;
- durable reviewer-decision execution from the synthetic journey;
- real payment-provider integration;
- real tenant/customer data;
- Production runtime;
- deployment or release publication;
- updater activation;
- DNS/domain cutover;
- rollback execution;
- destructive database operations.

## 6. Next bounded task

After Sprint81 becomes canonical, the next bounded task is:

**Sprint82 — Technical Preview Session Runtime and Activation-Envelope Selection.**

Sprint82 should select the minimum session/runtime semantics necessary for a real browser-based Synthetic Technical Preview while preserving:

- no Production authority;
- synthetic-only business data;
- deny-by-default runtime admission;
- no migration execution unless separately authorized;
- deterministic off-switch;
- exact executable qualification before any later activation request.

Sprint82 must remain a selection/qualification gate unless a separately bounded source/config implementation is explicitly required by the selected runtime design.

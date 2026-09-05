# Sprint85 — Technical Preview Activation-Readiness Evidence Gate

Author by Lab | zefry

## 1. Purpose

Sprint85 closes the bounded technical-readiness evidence loop opened by Sprint81 and refined by Sprint82–Sprint84 for the first single-instance Synthetic Technical Preview.

This gate is evidence-only. It does **not** activate Technical Preview, deploy or publish a release, change DNS, execute or roll back migrations, enable Production, activate the updater, bind real credentials/data, or grant durable final Shift Close authority.

## 2. Canonical scope

This decision applies only to the selected first Synthetic Technical Preview envelope:

- runtime class `preview`;
- explicit Preview enablement required;
- single application instance;
- server-side `file` session persistence;
- encrypted session payload;
- dedicated `oneqay-preview-session` cookie;
- Secure + HttpOnly + SameSite=Lax;
- host-only cookie domain and path `/`;
- session lifetime no greater than 60 minutes;
- deterministic synthetic identities, contexts, catalog, sale/cash/reconciliation/reviewer evidence only;
- no Production runtime or real payment-provider claim;
- no Preview business migration execution requirement.

Multi-node Preview, Production runtime, real tenant/customer/payment data, durable final Shift Close, and shared-session infrastructure remain outside this gate.

## 3. Evidence matrix

### A. Runtime admission and Production denial — PASS

Canonical `TechnicalPreviewRuntimePolicy` admits deployed Preview only when the explicit flag and complete selected session envelope are valid. Production and unknown runtime classes remain denied. Qualification runtime classes remain bounded to local/test/testing/ci.

### B. Deployed file-session continuity — PASS

The canonical file-session continuity regression proves, across independent HTTP requests:

- encrypted server-side file session resolution;
- dedicated Secure/HttpOnly/SameSite=Lax host-only cookie;
- bounded 60-minute lifetime;
- session identifier rotation on sign-in;
- verified context continuity without caller-selected foreign tenant authority;
- CSRF rejection for forged mutations;
- cash-control route/session continuity;
- logout invalidation;
- stale pre-logout cookie denial.

### C. Missing, corrupt, and expired session containment — PASS after Sprint85 exact qualification

Sprint85 adds independent HTTP proof that previously signed-in Preview authority is not retained when the selected file-session backend state is:

- missing;
- corrupt / not decryptable;
- expired beyond the selected 60-minute lifetime.

Each condition must fail closed to the Preview sign-in boundary and must not serve Preview POS authority.

### D. Route off-switch and config-cache boundary — PASS

Sprint84 proves:

- disabled Preview removes the Preview route surface and returns route-level 404;
- Production retains no Preview routes even when the enable flag is mistakenly true;
- exact deployed Preview retains only the approved route surface;
- CI qualification remains available;
- Preview database-qualification delivery no longer performs raw request-time `env()` reads.

### E. Synthetic-data and no-schema-change boundary — PASS

Canonical Preview delivery remains deterministic/synthetic. The governed Preview release artifact:

- excludes `.env`, `.git`, `node_modules`, and database migration paths;
- requires `migration_classification = NO_SCHEMA_CHANGE`;
- validates the manifest/checksum binding;
- proves deterministic archive reproduction;
- does not convert source-published durable migrations into Technical Preview activation authority.

Preview database qualification remains a separate qualification concern and does not become implied deployment or migration authority.

### F. Business smoke and maker-checker preservation — PASS

Canonical executable Preview coverage preserves the bounded journey through synthetic sign-in, verified context, shift open, sale, receipt, observed close, server-derived expected cash, reconciliation, sale adjustment, and variance maker-checker evidence. Preview review ACCEPT/REJECT remains non-durable and does not grant final Shift Close authority.

### G. Observability and safe error surface — PASS

M7.1 executable regression proves:

- correlation identity propagation and regeneration for invalid IDs;
- safe generic error envelopes;
- Preview observation log stored under private application storage, never public web root;
- bounded `info` level and 14-day retention;
- request/exception correlation IDs remain searchable;
- query/body/cookie/Authorization values and exception messages are not copied into observation logs.

### H. Recovery, staging, and shared-runtime safety foundations — PASS as source qualification

Canonical recovery/staging regressions prove:

- updater install remains hard disabled and unwired;
- no live-host/network/shell/credential mutation primitive is introduced by the Preview recovery rehearsal foundation;
- staging rejects links, hardlinks, secret files, and limit violations;
- health verification and rollback states are present;
- rollback health failure is explicit;
- deployment locking and active-pointer compare failures are fail-closed;
- shared runtime configuration does not expose raw secret/database surfaces or mutate `.env`;
- the governed public bootstrap binds the private shared environment without copying `.env` into a release.

These are source/rehearsal qualifications only; they are **not** evidence that a target host has been deployed or mutated.

## 4. Sprint85 decision

Subject to successful exact-head Sprint85 CI:

- `SOURCE_READINESS = PASS`
- `SELECTED_RUNTIME_READINESS = PASS`
- `TECHNICAL_ACTIVATION_READINESS = PASS_FOR_SEPARATE_ACTIVATION_REQUEST`
- `ACTIVATION_AUTHORITY = NOT_GRANTED`
- `DEPLOYMENT_EXECUTION_AUTHORITY = NOT_GRANTED`
- `PRODUCTION_AUTHORITY = NOT_GRANTED`
- `MIGRATION_EXECUTION_AUTHORITY = NOT_GRANTED`
- `UPDATER_ACTIVATION_AUTHORITY = NOT_GRANTED`

`PASS_FOR_SEPARATE_ACTIVATION_REQUEST` means the canonical codebase has the bounded technical evidence required to consider a later, separately authorized Synthetic Technical Preview activation. It is not itself permission to deploy, expose, publish, migrate, update DNS, or activate anything.

## 5. Mandatory target-environment preflight after separate authority

If a later Product Owner decision separately authorizes a Preview activation attempt, that bounded activation must still fail closed unless the selected target environment proves at execution time:

- exactly one intended Preview application instance for this file-session envelope;
- HTTPS/TLS available before enabling the Secure session cookie;
- dedicated host scope consistent with host-only cookie semantics;
- private persistent session directory exists, is writable by the application runtime identity, and is not web-accessible;
- config-cached runtime resolves exactly to the selected Preview envelope;
- Preview off-switch can remove the route surface;
- liveness/readiness and bounded smoke checks pass after activation;
- rollback/recovery procedure for that target is available;
- no migration execution is required for the synthetic Preview journey;
- no Production credential/data surface is introduced.

Failure of any target-environment preflight condition must leave activation disabled or trigger the separately authorized recovery path. Sprint85 grants no authority to perform that preflight against a live host.

## 6. Explicit NO-GO boundaries

Sprint85 does not authorize or perform:

- Technical Preview deployment or public exposure;
- DNS/domain cutover;
- release publication;
- Production activation;
- updater activation;
- migration execution or rollback;
- destructive database operations;
- real identity/customer/payment-provider data;
- multi-node Preview scaling;
- durable reviewer decision or final Shift Close execution.

## 7. Next bounded concern

After Sprint85 becomes canonical, the repository may proceed only to a **separate Product Owner activation decision / target-environment preflight gate** if such authority is explicitly granted later.

Until then, Technical Preview remains **NOT ACTIVATED / NO-GO**.
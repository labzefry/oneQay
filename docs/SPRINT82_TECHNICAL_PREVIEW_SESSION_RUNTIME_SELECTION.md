# Sprint82 — Technical Preview Session Runtime and Activation-Envelope Selection

Author by Lab | zefry

## 1. Purpose

Sprint82 selects the minimum session/runtime semantics required before the canonical Synthetic Technical Preview may become activation-ready.

This follows Sprint81, whose canonical decision is **SOURCE-READY / NOT ACTIVATION-READY** because the current browser journey depends on session continuity while `apps/web/environment.example` still defaults to `SESSION_DRIVER=array`.

Sprint82 remains a selection gate. It does not activate Technical Preview, modify runtime source/configuration, execute migrations, deploy, publish a release, enable Production, or activate the updater.

## 2. Selected runtime class

A deployed Synthetic Technical Preview must use:

```text
ONEQAY_RUNTIME_CLASS=preview
```

The Preview route surface must additionally require an explicit Preview enable setting. Production remains deny-by-default and must never become eligible merely because the enable flag is true.

Local/Test/Testing/CI may continue to exercise Preview source for qualification, but only runtime class `preview` is selected for an actual deployed Preview environment.

## 3. Selected session backend

For the first bounded, single-instance Synthetic Technical Preview, select:

```text
SESSION_DRIVER=file
```

Rationale:

- server-side state preserves the existing server-owned principal/context/shift/reconciliation authority;
- file session requires no database-session table and no new migration;
- Redis or another external session service is not required for the first single-instance Preview;
- client-carried cookie session data is not selected because Preview cash-control state must remain server-controlled rather than transported as application state by the browser;
- `array` remains suitable for isolated tests but is not selected for deployed multi-request browser continuity.

The selected file backend is valid only for one application instance with persistent local storage. Multi-node Preview scaling is explicitly outside Sprint82 and would require a later shared-session infrastructure gate.

## 4. Selected session security envelope

A deployed Preview session must satisfy all of the following:

- session data stored outside the public web root under application-controlled storage;
- storage writable only by the application runtime identity to the extent supported by the host;
- dedicated Preview session cookie name rather than sharing a Production cookie namespace;
- host-only cookie scope; no wildcard/sibling-subdomain session sharing;
- cookie path `/`;
- `Secure = true` for deployed Preview;
- `HttpOnly = true`;
- `SameSite = Lax`;
- session payload encryption enabled for deployed Preview;
- maximum selected session lifetime: **60 minutes**;
- sign-in must regenerate the session identifier before synthetic principal authority is stored;
- logout must invalidate the session and regenerate the CSRF token;
- unavailable/corrupt session state must fail closed to Preview sign-in/context rather than infer authority.

The 60-minute lifetime is a bounded Technical Preview value, not a Production session policy.

## 5. Config-cache-safe activation semantics

Direct runtime use of `env()` outside configuration files is not selected for deployed Preview activation.

Sprint82 selects a config-backed Preview control surface so the later implementation must:

- add a dedicated `technical_preview` configuration section under the existing oneQay configuration boundary or an equivalently bounded config file;
- read `ONEQAY_TECHNICAL_PREVIEW_ENABLED` only from configuration construction;
- have the Preview ServiceProvider and Preview controller consume `config(...)`, not raw `env(...)` calls;
- preserve explicit runtime-class checking from the config-backed `oneqay.runtime_class` value;
- keep unknown/missing configuration fail-closed;
- remain compatible with Laravel configuration caching.

## 6. Route-registration rule

The later implementation must preserve dedicated Preview route ownership and select this rule:

A deployed Preview route surface may be registered only when:

1. config-backed Technical Preview enablement is true; and
2. configured runtime class is in the explicitly qualified Preview-safe runtime allowlist.

Production is not in that allowlist.

The dedicated cash-control route file must retain `web` middleware so session and CSRF protections remain active without moving the route definitions back into shared `routes/web.php`.

## 7. Required implementation qualification

The next source/config implementation must prove at minimum:

### Configuration and fail-closed behavior

- missing/false Preview flag does not register or expose Preview routes;
- Production runtime does not register or serve Preview routes even if a Preview flag is mistakenly true;
- deployed Preview runtime rejects `array`, database, cookie, Redis, or unknown session drivers for this selected first Preview envelope;
- deployed Preview runtime rejects non-secure or non-encrypted session configuration;
- Local/Test/Testing/CI qualification remains possible without creating Production authority.

### Session continuity

Using the selected file session backend, executable regression must prove continuity across independent HTTP requests for:

- synthetic sign-in;
- verified context selection;
- active cash shift;
- receipt state;
- reconciliation state;
- logout invalidation.

The proof must not rely on one in-memory request object or the `array` driver.

### Security controls

Executable regression must prove:

- session ID changes on successful Preview sign-in;
- CSRF-protected cash-control mutations remain valid only with the current session/token;
- forged tenant/context/session state does not grant Preview authority;
- logout burns prior Preview session authority;
- session cookies for deployed Preview are Secure, HttpOnly, SameSite=Lax, and host-only;
- the Preview session cookie namespace is distinct from any future Production namespace.

### Existing business journey preservation

The canonical Sprint81 smoke path remains required:

`synthetic sign-in → verified context → open shift → CASH/manual sale → receipt → close cash → expected cash → MATCH/OVER/SHORT reconciliation → logout`

No durable Shift Close or migration execution may be introduced to satisfy this proof.

## 8. Explicitly not selected

Sprint82 does not select or authorize:

- Redis or other shared session infrastructure;
- database session persistence or a session table;
- any session migration;
- multi-node Preview scaling;
- Production session policy;
- real identities, customer data, payment-provider data, or Production credentials;
- durable Preview cash-shift persistence;
- durable reviewer/Shift Close execution;
- migration execution or rollback;
- Preview deployment or public exposure;
- release publication;
- Production activation;
- updater activation;
- DNS/domain cutover;
- destructive database actions.

## 9. Next bounded task

After Sprint82 becomes canonical, the next bounded task is:

**Sprint83 — Implement and qualify the selected Technical Preview session/runtime envelope.**

Sprint83 should be one coherent source/config/test slice limited to the selected runtime/session controls. It must preserve Synthetic Technical Preview business semantics from PR #610 and must not include deployment or activation authority.

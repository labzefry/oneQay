# Post-Sprint37 Canonical Program State

Date: 2026-08-23
Repository: `labzefry/oneQay`
Attribution: **Lab | zefry**

Sprint37 — **First-Party All-Session Termination (Tenant-Scoped Revoke-All) Foundation** is **COMPLETE / IMPLEMENTED / PUBLISHED**.

Published source PR: **#245**
Canonical source commit: `0b2ac0ea18b1ad28e6b46ba952d2ac260b297643`
Canonical source tree: `fe9fd64e7f9dda2e24c4fb573f73b5d5441a5d50`
Canonical source parent: `d8f9ffe52773c44f256a4721dc2390bd734cf797`
Qualified source head: `f6b3b18e16d68f91a8e12dbd567a06571add1daf`
GitHub signature: **verified / valid**

Frozen source envelope: **11 paths**
Sorted-path SHA-256: `a221779e05b1e8ab220610b5068be5d1eb01bc08b516b338ba8f22373e7d89d0`

The published Sprint37 governance chain includes entry-gate preservation PR **#238**, entry gate PR **#239**, schema/source-envelope preservation PR **#240**, schema/source-envelope gate PR **#241**, historical-preservation bridge PR **#243**, historical-preservation predecessor PR **#244**, and source PR **#245**. PR **#242** was closed without merge and is not publication authority. All lifecycle authorities for these exact PRs/heads are consumed.

Post-Sprint37 canonical-reconciliation successor preservation is published through PR **#246** as canonical commit `deadaeacd665cb62e5c59e4fe33c70d5162ec992`, tree `7ba8b9366dab15f1dd45053220c4540234b77f3f`, parent `0b2ac0ea18b1ad28e6b46ba952d2ac260b297643`, signature **verified / valid**.

PR #246 changes exactly the three fail-closed Sprint35/Sprint36/Sprint37 preservation workflows. Its sorted-path SHA-256 is `93fe19acad52a31981ecea81ab1dc65c730f0f01729959ca6eaedb0de5b128b7`. The predecessor recognizes only the exact two-document post-Sprint37 reconciliation successor shape and preserves executable regressions, migration guards, and activation boundaries.

## First-party all-session termination semantics

Sprint37 adds `POST /auth/sessions/revoke-all`, named `auth.sessions.revoke-all`, inside the existing first-party session-control boundary.

The operation accepts no caller-controlled tenant, identity, authority identifier, public handle, target list, device selector, organization selector, outlet selector, or arbitrary scope as authority. Framework CSRF input remains permitted. Exact tenant + identity ownership comes from authenticated server-owned session context.

Before mutation, the current logical first-party authority is validated against exact tenant, identity, organization, outlet, device, credential epoch, factor epoch, revocation state, and idle expiry.

Durable revoke-all then:

- revokes every active, unrevoked logical authority for the exact current tenant + identity;
- includes the current logical authority;
- excludes already revoked and expired authorities from the active target set;
- never crosses into another tenant;
- never crosses into another identity;
- never mutates `credential_epoch` or `factor_epoch`;
- remains monotonic under replay/concurrency and cannot resurrect an authority.

The repository writes `all_sessions_revoked` only when at least one durable active authority transitions to revoked. Repeating the durable transition after convergence affects zero rows and does not emit duplicate transition evidence.

Audit evidence remains server-derived and secret-free. It does not contain passwords, password hashes, TOTP secrets, recovery codes, session secrets, cookies, CSRF tokens, bearer tokens, or public session handles.

## Durable-before-local terminal ordering

The current Laravel session is invalidated only after durable revoke-all succeeds. After durable success the controller regenerates the CSRF token and returns `204 No Content` with private no-store caching.

A durable failure does not perform successful local terminal handling. Runtime or persistence failure remains fail-closed.

## Privileged step-up and preserved Sprint36 behavior

Protected privileged identities reuse the existing `session_control` step-up scope with exactly **300 seconds** freshness. Sprint37 introduces no new MFA mechanism or privilege scope. Ordinary identities for which protected-control MFA is not required receive no invented privileged challenge.

Sprint37 preserves:

- active first-party session inventory;
- remote revoke-one by opaque `public_handle`;
- revoke-others while preserving current authority;
- canonical current-session logout;
- credential-epoch and factor-epoch enforcement;
- exact tenant + identity ownership isolation;
- fixed **7200-second** idle authority lifetime;
- disabled-by-default session-control activation;
- Local/Test/CI runtime restriction.

Published Local/Test/CI session-control routes now include:

- `GET /auth/sessions`
- `DELETE /auth/sessions/{public_handle}`
- `POST /auth/sessions/revoke-others`
- `POST /auth/sessions/revoke-all`
- `POST /auth/reauthenticate/session-control`
- canonical `POST /auth/logout`

## Schema and activation boundary

Sprint37 is **NO_SCHEMA_CHANGE**.

Canonical source migrations remain exactly **#1 through #13**. Migrations #1–#13 are immutable for this reconciliation. Migration #14 is **NOT REQUIRED / NOT SELECTED / NOT AUTHORIZED**.

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` remains the source default. `oneqay.session_control.enabled` remains the canonical configuration switch. Sprint37 execution remains **Local/Test/CI only**.

Technical Preview remains **`NO_SCHEMA_CHANGE`**.

Production remains **`NO-GO / NOT AUTHORIZED`**.

Updater remains **`DISABLED / UNWIRED`**.

Deployment remains **NOT AUTHORIZED**.

Release remains **NOT AUTHORIZED**.

## Reconciliation publication envelope

The post-Sprint37 canonical program-state reconciliation changes exactly:

1. `docs/ai/AI_NEXT_TASK.md`
2. `docs/ai/AI_POST_SPRINT_37_CANONICAL_STATE.md`

Sorted-path SHA-256: `2b3a711f5cb921dea0fb51a005c0bc742186bce526a0a7e87b10215733215dbe`.

The exact pre-reconciliation canonical base is `deadaeacd665cb62e5c59e4fe33c70d5162ec992`. Any final reconciliation publication commit must be verified from GitHub after merge rather than predicted in this document.

Unknown successor shapes remain fail-closed.

## Next governed boundary

This reconciliation selects **no Sprint38 implementation concern**.

It grants no Sprint38 source authority, no migration #14 authority, no schema authority, no Technical Preview activation, no Production activation, no updater authority, no deployment authority, and no release authority.

The next governed action is Product Owner selection of a future bounded concern. Any future concern requires its own fresh GitHub state verification, exact entry gate, source/schema decision, changed-file envelope, regression/preservation chain, and separate authority before source mutation.

Attribution: **Lab | zefry**

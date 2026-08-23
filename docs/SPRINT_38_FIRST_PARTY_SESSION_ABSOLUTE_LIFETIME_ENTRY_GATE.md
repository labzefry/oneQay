# Sprint 38 — First-Party Session Absolute Lifetime Foundation — Entry Gate

Attribution: Lab | zefry

## 1. Governed purpose

Sprint38 is selected as a bounded continuation of the first-party logical session-authority foundation published through Sprint36 and Sprint37.

The concern is:

> **First-Party Session Absolute Lifetime Foundation**

This entry gate freezes the concern, lifetime semantics, schema decision, runtime boundary, regression expectations, explicit exclusions, and non-authority for the next governed source-envelope stage. It does not implement runtime behavior.

Canonical base at entry-gate preparation:

- `main`: `272fcbd0caa566b599d2f956ff7fdb91048014c0`
- tree: `b78e094e525207f3dd432710ce77c00a11b95096`
- parent: `f959386d71bcd3815f2d897217f1e4b794fca35d`
- canonical commit signature: verified / valid

The immediately preceding preservation publication is PR #248.

## 2. Why this is the selected next concern

The canonical Security Handbook requires first-party browser sessions to have both idle and absolute timeout semantics.

Sprint36 introduced durable first-party logical session authority with exact tenant + identity ownership, fixed sliding idle authority lifetime of 7200 seconds, inventory, remote revocation, canonical logout, credential/factor epoch validation, and fail-closed request-time authority enforcement.

Sprint37 added tenant-scoped revoke-all while preserving the same authority model and idle-lifetime behavior.

The current logical authority record already stores both `issued_at_unix` and `expires_at_unix`, but the current sliding touch behavior can continue moving `expires_at_unix` forward as long as activity remains within the idle window. Therefore a continuously active authority currently has no separately frozen maximum lifetime from its original issuance.

Sprint38 closes that bounded security gap without creating a new authentication family, new route family, trusted-device model, risk engine, or account-control plane.

## 3. Security outcome

Sprint38 shall enforce a fixed maximum lifetime for each durable first-party logical session authority from its original issuance time.

The frozen values are:

- idle lifetime: exactly **7200 seconds**;
- absolute lifetime: exactly **43200 seconds (12 hours)** from `issued_at_unix`.

The effective authority deadline is always the earlier of:

1. the current sliding idle deadline; and
2. the immutable absolute deadline derived from original issuance.

No request activity, touch operation, session rotation, inventory request, revocation workflow, or privileged step-up may extend a logical authority beyond its fixed absolute deadline.

## 4. Frozen absolute-lifetime semantics

For every logical first-party session authority:

`absolute_deadline = issued_at_unix + 43200`

The initial expiry at issuance remains bounded by the existing idle lifetime and therefore resolves to:

`expires_at_unix = min(issued_at_unix + 7200, absolute_deadline)`

For any later valid touch at server time `now`:

`expires_at_unix = min(now + 7200, absolute_deadline)`

The following rules are frozen:

1. `issued_at_unix` is immutable for the lifetime of a logical authority.
2. The absolute deadline is derived only from the stored server-owned issuance timestamp.
3. Caller-supplied timestamps never become authority for lifetime calculation.
4. A touch may move the idle deadline forward only up to the fixed absolute deadline.
5. A touch must never write an expiry greater than `issued_at_unix + 43200`.
6. Session/framework rotation must preserve the same logical authority and therefore the same original absolute deadline.
7. Activity at or after an authority that has become expired must not extend, reactivate, replace, or resurrect that authority.
8. Replayed or concurrent touch attempts must not move the durable expiry past the same fixed absolute deadline.
9. Revoked authority remains revoked regardless of remaining idle or absolute time.
10. Expiration by passage of time does not require physical deletion of the durable authority row.

Sprint38 preserves the existing request-time expiry boundary convention used by the first-party session authority service. A later source gate must preserve that convention consistently rather than silently changing equality semantics while adding absolute lifetime.

## 5. Sliding idle timeout remains authoritative

Sprint38 does not replace the current 7200-second idle authority lifetime.

An authority may become unusable before its 12-hour absolute deadline because of inactivity. The 12-hour value is a maximum age, not a guaranteed session duration.

A continuously active authority may receive bounded sliding idle extensions, but those extensions stop at the fixed absolute deadline.

Sprint38 does not increase or decrease the existing idle TTL.

## 6. Current authority validation

Request-time first-party authority enforcement must continue to validate the existing exact owner/context and epoch evidence before considering an authority usable.

Sprint38 adds the requirement that a current authority is also within its fixed absolute lifetime derived from its original `issued_at_unix`.

Existing checks remain authoritative for:

- exact tenant ownership;
- exact identity ownership;
- exact current authority ID;
- organization context;
- outlet context where applicable;
- device context where applicable;
- revoked state;
- credential epoch;
- privileged factor epoch where applicable;
- idle expiry;
- runtime and persistence eligibility.

Absolute lifetime must not weaken or substitute for any of those checks.

## 7. Inventory semantics

Existing session inventory remains server-authoritative and owner-scoped.

An authority whose effective expiry has passed must not be presented as an active inventoried session merely because its durable row still exists.

Sprint38 introduces no new public authority identifier and no new caller-controlled selector.

Existing inventory fields may continue to expose the bounded non-secret session metadata already governed by Sprint36, including issuance, last-seen, and expiry information. Sprint38 does not authorize disclosure of internal `authority_id`, secrets, cookies, CSRF tokens, credential material, TOTP material, or recovery material.

## 8. No new HTTP route or mutation family

Sprint38 does not require a new HTTP endpoint.

The selected concern is enforced through the existing logical-authority issuance, validation, touch, and inventory semantics.

Existing published Local/Test/CI session-control routes remain unchanged:

- `GET /auth/sessions`
- `DELETE /auth/sessions/{public_handle}`
- `POST /auth/sessions/revoke-others`
- `POST /auth/sessions/revoke-all`
- `POST /auth/reauthenticate/session-control`
- canonical `POST /auth/logout`

No new route, payload, caller selector, or remote authority is selected by this entry gate.

## 9. Credential, factor, and step-up preservation

Sprint38 does not modify password credential epochs or privileged TOTP factor epochs.

Credential and factor epoch evidence remains separately authoritative for current-session validity.

Protected privileged identities continue using the existing `session_control` step-up scope with exactly 300-second freshness for privileged session-control mutations. Absolute expiry does not create a privileged bypass and cannot be extended by successful step-up.

Ordinary identities receive no invented privileged challenge because of Sprint38.

An absolute-expired first-party logical authority cannot be made current again merely by presenting old MFA, step-up, credential-epoch, or factor-epoch evidence.

## 10. Schema decision

Sprint38 entry-gate schema status is:

> **NO_SCHEMA_CHANGE**

Reasons:

- migration #13 already stores immutable issuance time in `issued_at_unix`;
- migration #13 already stores the effective durable expiry in `expires_at_unix`;
- the absolute deadline can be derived deterministically from server-owned issuance time plus the frozen 43200-second maximum;
- the selected concern requires no new durable entity, owner coordinate, token, secret, audit table, or column.

Therefore:

- migration #14 is **NOT REQUIRED**;
- migration #14 is **NOT SELECTED**;
- migration #14 is **NOT AUTHORIZED**;
- migrations #1 through #13 remain immutable for Sprint38.

## 11. Runtime and configuration boundary

Sprint38 reuses the existing session-control feature arm:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

Canonical configuration remains within:

`oneqay.session_control`

A later schema/source-envelope gate may authorize one bounded configuration scalar under that existing namespace for the frozen absolute lifetime, for example an exact value of `43200`. This entry gate does not itself mutate configuration or application source.

No new feature arm is selected.

The concern remains limited to Local / Test / CI governed execution until a later authority explicitly changes runtime activation status.

Technical Preview remains `NO_SCHEMA_CHANGE` and receives no activation authority here.

Production remains `NO-GO / NOT AUTHORIZED`.

Updater remains `DISABLED / UNWIRED`.

Deployment and release remain not authorized.

## 12. Audit semantics

Sprint38 does not select a new first-party session audit event merely because time passes.

Absolute expiry is a deterministic validity boundary derived from existing durable timestamps rather than a caller-triggered security mutation.

Existing first-party session audit semantics remain unchanged and secret-free.

If a future concern proposes explicit durable expiration-transition evidence, that event requires separate justification and authority; it is not authorized by this entry gate.

## 13. Required security regression expectations for later source implementation

A later governed Sprint38 source implementation must prove at minimum:

- newly issued authority preserves the existing 7200-second initial idle deadline;
- absolute deadline is exactly `issued_at_unix + 43200`;
- authority remains usable before both its idle and absolute deadlines when every other authority check is valid;
- existing 7200-second idle expiration remains effective;
- repeated activity cannot extend an authority beyond its fixed 43200-second absolute lifetime;
- touch updates expiry to no later than `min(now + 7200, issued_at_unix + 43200)`;
- `issued_at_unix` is never rewritten by touch or framework-session rotation;
- exact existing expiry boundary semantics are preserved consistently;
- an absolute-expired authority cannot be resurrected by a touch, replay, concurrency race, framework session rotation, inventory request, or privileged step-up;
- an absolute-expired authority is omitted from active session inventory;
- another tenant's authority remains untouched;
- another identity's authority remains untouched;
- credential and factor epochs are not mutated;
- existing inventory, revoke-one, revoke-others, revoke-all, canonical logout, and privileged session-control semantics remain preserved;
- disabled feature state fails closed;
- disallowed runtime state fails closed;
- persistence/runtime failure remains fail-closed;
- no migration #14 exists for Sprint38;
- migrations #1 through #13 remain unchanged;
- audit remains secret-free and no new expiration event is synthesized;
- the full Sprint36 and Sprint37 executable preservation regressions remain successful.

## 14. Explicit exclusions

Sprint38 does **not** include:

- changing the existing 7200-second idle lifetime;
- caller-configurable session duration;
- per-role or per-user absolute lifetime policies;
- trusted-device enrollment;
- remembered-device semantics;
- device trust scoring;
- IP reputation;
- browser fingerprint authority;
- adaptive or risk-based authentication-engine implementation;
- account suspension;
- identity disablement;
- administrator revocation of another identity's sessions;
- cross-tenant global identity logout;
- password mutation;
- TOTP factor mutation or replacement;
- recovery-code mutation;
- API-token or personal-access-token lifecycle;
- mobile-native token lifecycle;
- WebAuthn/passkeys;
- federation/SSO;
- support impersonation;
- break-glass administration;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## 15. Changed-file envelope of this entry gate

This entry-gate publication is documentation-only and changes exactly one path:

`docs/SPRINT_38_FIRST_PARTY_SESSION_ABSOLUTE_LIFETIME_ENTRY_GATE.md`

Sorted-path SHA-256 of the newline-terminated sorted changed-file list:

`b5e631b39dee456c18e2f14ab3266db599a97d3b5f3778c8d8dfdd3243658434`

No application source, migration, workflow, canonical state document, roadmap, task list, manifest, Technical Preview artifact, updater artifact, deployment artifact, or release artifact belongs to this envelope.

## 16. Non-authority statement

Publishing this entry gate authorizes only the bounded architectural/security decision represented by this document.

It does **not** itself authorize:

- application-source mutation;
- controller/service/repository/middleware mutation;
- config mutation;
- test mutation;
- workflow mutation after this entry-gate publication;
- schema mutation;
- migration #14;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

The authority used to publish this entry gate is consumed by the exact PR/head that publishes it and does not become standing source authority.

## 17. Next governed stage after publication

After this entry gate is published, the next bounded stage is a **Sprint38 schema/source-envelope gate**.

That later gate must freeze the exact source, configuration, test, documentation, and preservation-workflow changed-file envelope required to implement the selected absolute-lifetime concern while preserving Sprint35/Sprint36/Sprint37 historical fail-closed semantics and the `NO_SCHEMA_CHANGE` decision.

No Sprint38 source implementation may begin before that later source-stage authority is granted.

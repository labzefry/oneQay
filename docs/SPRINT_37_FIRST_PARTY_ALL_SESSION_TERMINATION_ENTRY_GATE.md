# Sprint 37 — First-Party All-Session Termination (Tenant-Scoped Revoke-All) Foundation — Entry Gate

Attribution: Lab | zefry

## 1. Governed purpose

Sprint37 is selected as a bounded continuation of the first-party session-control security foundation published through Sprint36.

The concern is:

> **First-Party All-Session Termination (Tenant-Scoped Revoke-All) Foundation**

This entry gate freezes the concern, security semantics, source boundaries to be designed next, and explicit non-authority. It does not implement runtime behavior.

Canonical base at entry-gate preparation:

- `main`: `61913b71e893d833812b09d750d15d7c23eff3b4`
- tree: `6437ebd2b243405049af4ec888a8f32725091419`
- parent: `968bb4f76765a28e4f1193cc450ff818d285ff87`
- canonical commit signature: verified / valid

The preceding preservation-compatibility publication is PR #238.

## 2. Why this is the selected next concern

DEC-006 establishes explicit first-party session logout/revocation direction, including privileged session/device inventory and `revoke-one / revoke-all` capability direction.

Sprint36 implemented durable first-party logical session authority, inventory, revoke-one, revoke-others, canonical current-session logout, exact tenant + identity ownership, and privileged `session_control` step-up semantics. Sprint36 intentionally did **not** expose revoke-all.

Sprint37 therefore closes one bounded security gap without introducing a new authentication family, trusted-device model, risk engine, or cross-tenant identity control plane.

## 3. Security outcome

Sprint37 shall provide one server-authoritative operation that terminates every active first-party logical session authority owned by the exact current tenant + identity pair, including the current logical authority.

The operation is tenant-scoped and identity-scoped. It is not global account disablement and is not a cross-tenant sign-out primitive.

## 4. Exact authority ownership

The server must derive all authority coordinates from the authenticated server-side session context.

Caller-supplied values must never become authority for:

- tenant identity;
- user/identity identity;
- logical authority ID;
- `public_handle` selection;
- organization/outlet ownership;
- actor authority.

The target set is exactly:

`current tenant + current identity + all currently active first-party logical session authorities`

No authority belonging to another tenant or another identity may be revoked by this operation.

## 5. Frozen revoke-all semantics

Sprint37 freezes the following meaning of **revoke-all**:

1. Revoke every active durable first-party logical session authority for the exact current tenant + identity pair.
2. The current logical session authority is included in that set.
3. Revocation is server-authoritative and durable before local framework-session termination is considered complete.
4. Already-revoked authorities remain revoked.
5. Repeated, replayed, or concurrent requests must not resurrect a revoked authority.
6. A race between concurrent revoke-all operations must converge monotonically to the same secure state.
7. Session authorities belonging to other tenant + identity ownership pairs remain untouched.

This is not a caller-selected bulk list operation.

## 6. Current Laravel session termination

After durable revoke-all succeeds, the current Laravel session must be invalidated using the canonical current-session termination pattern and the CSRF token must be regenerated.

The operation must not report a secure terminal state while leaving the current framework session usable after its logical authority has been revoked.

The current canonical logout route remains a separate current-session-only action. Sprint37 does not redefine canonical logout semantics.

## 7. Privileged step-up semantics

Sprint37 reuses the existing canonical session-control mutation boundary.

For a protected privileged identity whose current policy requires MFA-backed privileged session control:

- required step-up scope remains exactly `session_control`;
- freshness remains exactly 300 seconds;
- the existing confirmed TOTP-backed privileged reauthentication semantics are preserved;
- no alternate privileged proof is invented.

For an ordinary identity that is not subject to that privileged requirement:

- Sprint37 must not invent or assign a privileged MFA challenge;
- the operation remains bound to active authenticated first-party session authority and the existing session-control mutation context.

## 8. Credential and factor epoch preservation

Sprint37 does not modify credential or TOTP factor epochs.

Existing credential-epoch and factor-epoch enforcement remains authoritative for active session validation. Revoke-all is a session-authority termination operation, not a credential mutation or factor replacement operation.

## 9. Candidate HTTP contract frozen for the next source gate

The bounded candidate route is frozen as:

- method: `POST`
- path: `/auth/sessions/revoke-all`
- route name: `auth.sessions.revoke-all`

Payload policy:

- closed payload;
- only framework CSRF input is tolerated;
- no tenant, identity, authority, handle, scope, count, filter, or selector is accepted from the caller as authority.

Middleware direction:

- active first-party session authority required;
- existing session-control mutation context required;
- existing session-control throttling direction preserved;
- runtime and persistence boundaries remain fail-closed.

This route freeze does not authorize source implementation yet.

## 10. Audit semantics

Sprint37 may use the existing first-party session audit storage introduced by migration #13.

The bounded candidate audit event is:

`all_sessions_revoked`

The event should record the server-derived actor/owner context, correlation ID, target authority context where applicable, and occurrence time without secrets.

A bulk summary event is acceptable because the existing Sprint36 implementation already uses summary audit semantics for `other_sessions_revoked`.

Audit records must not contain passwords, TOTP secrets, recovery codes, session secrets, CSRF tokens, raw cookies, or other authentication secrets.

## 11. Schema decision

Sprint37 entry-gate schema status is:

> **NO_SCHEMA_CHANGE**

Reasons:

- migration #13 already provides the durable first-party session authority table required to revoke all active owner sessions;
- migration #13 already provides first-party session audit storage with an event-type field capable of representing the bounded new event;
- the selected concern does not require a new durable entity or column.

Therefore:

- migration #14 is **NOT REQUIRED**;
- migration #14 is **NOT SELECTED**;
- migration #14 is **NOT AUTHORIZED**;
- migrations #1 through #13 remain immutable for Sprint37.

## 12. Runtime and feature-arm boundary

Sprint37 reuses the existing session-control feature arm:

`ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`

Canonical config direction remains:

`oneqay.session_control.enabled`

No new feature arm is selected by this entry gate.

The concern remains limited to Local / Test / CI governed execution until a later authority explicitly changes runtime activation status.

Technical Preview remains `NO_SCHEMA_CHANGE` and receives no activation authority here.

Production remains `NO-GO / NOT AUTHORIZED`.

Updater remains `DISABLED / UNWIRED`.

Deployment and release remain not authorized.

## 13. Required security regression expectations for later source implementation

A later governed source implementation must prove at minimum:

- all active first-party logical session authorities for the exact current tenant + identity are revoked;
- the current logical authority is included;
- the current Laravel session is invalidated after durable revocation and CSRF is regenerated;
- another tenant's authorities are untouched;
- another identity's authorities are untouched;
- caller-supplied tenant/identity/authority/handle selectors cannot influence ownership;
- repeated/replayed revoke-all remains monotonic and does not resurrect authority;
- concurrent revoke-all converges securely;
- privileged identities requiring protected session-control mutation require fresh `session_control` step-up within 300 seconds;
- ordinary identities are not assigned an invented privileged MFA requirement;
- disabled feature state fails closed;
- disallowed runtime state fails closed;
- persistence/runtime preconditions remain fail-closed;
- no migration #14 exists for Sprint37;
- migrations #1 through #13 remain unchanged;
- audit remains secret-free;
- existing Sprint36 inventory, revoke-one, revoke-others, and canonical logout semantics remain preserved except for the governed addition of the new revoke-all route and audit event.

## 14. Explicit exclusions

Sprint37 does **not** include:

- cross-tenant global identity logout;
- administrator revocation of another identity's sessions;
- account suspension or identity disablement;
- trusted-device enrollment or remembered-device semantics;
- device trust scoring;
- IP reputation or browser fingerprint authority;
- adaptive/risk-based authentication engine implementation;
- password mutation;
- TOTP factor mutation or replacement;
- recovery-code mutation;
- API-token or personal-access-token revocation;
- mobile-native token lifecycle;
- WebAuthn/passkey implementation;
- federation/SSO implementation;
- support impersonation;
- break-glass administration;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## 15. Changed-file envelope of this entry gate

This entry-gate publication is documentation-only and changes exactly one path:

`docs/SPRINT_37_FIRST_PARTY_ALL_SESSION_TERMINATION_ENTRY_GATE.md`

Sorted-path SHA-256 of the newline-terminated sorted changed-file list:

`0a3fe02e88dd12689eee39b1940642e61a91afb1bc985086a6cbdd9a10d30e2b`

No application source, migration, workflow, canonical state document, roadmap, task list, manifest, Technical Preview artifact, updater artifact, deployment artifact, or release artifact belongs to this envelope.

## 16. Non-authority statement

Publishing this entry gate authorizes only the bounded architectural/security decision represented by this document.

It does **not** itself authorize:

- application-source mutation;
- route/controller/service/repository mutation;
- test mutation;
- workflow mutation;
- schema mutation;
- migration #14;
- Technical Preview activation;
- Production activation;
- updater wiring;
- deployment;
- release.

## 17. Next governed stage after publication

After this entry gate is published, the next bounded stage is a **Sprint37 schema/source-envelope gate**.

That later gate must freeze the exact source/test/workflow changed-file envelope required to implement the selected revoke-all concern while preserving Sprint35/Sprint36 historical fail-closed semantics and the `NO_SCHEMA_CHANGE` decision.

No Sprint37 source implementation may begin before that later authority is granted.

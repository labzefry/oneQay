# AI Next Task

## Canonical post-Sprint 36 program-state reconciliation — 2026-08-22

For current identity, security, recovery, session-control, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint35 wording retained below as historical provenance.

- Sprint 21 through Sprint 36 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint36 **First-Party Session Inventory & Revocation Foundation** is published through source PR #230 as `6b653d669c8b5474c806ecd5d61d854f25821cdf` with tree `7608a1dd7acaa4d6c7f72b2f14bc82d5b45b8d9a`, parent `0f21f021244b4260e2b712e064218fa186cd2142`, and a verified/valid publication signature.
- The exact qualified Sprint36 source head was `bf95e06ec0097f2d515f9337b1ef3b2c69360888`; the semantic source diff is exactly **23 paths** with sorted-path SHA-256 `ea735f8f5ee06d480863f9d1ba7ae58a91642109963c3f340e8453f3205bb7ae`.
- Sprint36 entry gate PR #226, entry successor compatibility PR #227, schema/source-envelope gate PR #228, schema successor compatibility PR #229, preservation compatibility PR #231, requalification preservation corrections PR #232/#233, and source PR #230 are published provenance; their lifecycle authorities are consumed and grant no standing successor authority.
- Post-Sprint36 reconciliation successor compatibility is published through PR #235 as `6358e28b2502ea46fc5f19f8cb4fd52ffdcba14f`; the correction changes exactly Sprint35/Sprint36 preservation workflows with sorted-path SHA-256 `c333c5a6ac31588aa9b0520160a1a1080a22af2971658d742ba9456eff94cbc8`. Unknown successor shapes remain fail-closed.
- Canonical source migrations are now exactly **#1 through #13**. Migrations #1–#12 remain immutable. Migration #13 creates durable server-owned first-party session authority plus secret-free audit evidence.
- Sprint36 publishes durable logical session authority separate from raw Laravel session identifiers. Internal `authority_id` is never exposed; external inventory/revocation uses opaque `public_handle`, which is not an authentication authority.
- Full session authority is issued only after successful authentication/MFA. Restricted recovery/pending sessions do not become inventoried authorities. Laravel session rotation preserves the current logical authority rather than creating a new durable authority.
- Session authority captures exact tenant + identity ownership plus `credential_epoch` and, where applicable, `factor_epoch`. Request-time enforcement is fail-closed for revoked, expired, stale-epoch, malformed, missing, or mismatched authority evidence.
- Fixed idle authority TTL remains **7200 seconds**. Privileged remote revocation uses canonical step-up scope `session_control` with **300-second** freshness.
- Published Local/Test/CI session-control routes are `GET /auth/sessions`, `DELETE /auth/sessions/{public_handle}`, `POST /auth/sessions/revoke-others`, and `POST /auth/reauthenticate/session-control`; canonical `POST /auth/logout` revokes the current durable authority. Sprint36 introduces no revoke-all operation.
- `ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false` remains the source default. Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.
- This reconciliation selects **no Sprint37 implementation concern**, assumes no migration #14, and grants no Sprint37 source, Preview, Production, updater, deployment, or release authority.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_36_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 35 program-state reconciliation — 2026-08-21

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint34 wording retained below as historical provenance.

- Sprint 21 through Sprint 35 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint35 **Privileged TOTP Recovery & Factor Replacement Foundation** is published through source PR #221 as `0bc4204badd05c45e729116937fef44448a91e59` with tree `d108098077fa5221b90e0de8d503424080138a9b`, parent `b6e8335610943216b293f6f6275bbe7dc5c6498e`, and a verified/valid publication signature.
- The exact qualified Sprint35 source head was `7b2d46bcd8d1301eca67540f38fd263f9a86cc68`; the semantic source diff is exactly **17 paths** with sorted-path SHA-256 `e889db1c7eaa22b3ed008f8781ab35652ca950a3f009c309e5c478d01d368f11`, inside the published 19-path source envelope from PR #220.
- PR #223 preserves the historical workflow matrix through exactly **18 legacy workflow paths** with sorted-path SHA-256 `25dbbd94087eba4157fa9c209f09174a127154a98067abbfbeec233bbe9398cd`; unknown successor shapes remain fail-closed.
- Canonical source migrations are exactly **#1 through #12**. Migration #13 is **NOT SELECTED / DOES NOT EXIST** at this historical checkpoint.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_35_CANONICAL_STATE.md`.

Attribution: **Lab | zefry**

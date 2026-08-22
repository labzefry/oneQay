# Post-Sprint36 Canonical Program State

Date: 2026-08-22  
Repository: `labzefry/oneQay`  
Attribution: **Lab | zefry**

Sprint36 — **First-Party Session Inventory & Revocation Foundation** is **COMPLETE / IMPLEMENTED / PUBLISHED**.

Published source PR: **#230**  
Canonical source commit: `6b653d669c8b5474c806ecd5d61d854f25821cdf`  
Canonical source tree: `7608a1dd7acaa4d6c7f72b2f14bc82d5b45b8d9a`  
Canonical source parent: `0f21f021244b4260e2b712e064218fa186cd2142`  
Qualified source head: `bf95e06ec0097f2d515f9337b1ef3b2c69360888`  
GitHub signature: **verified / valid**

Frozen source envelope: **23 paths**  
Sorted-path SHA-256: `ea735f8f5ee06d480863f9d1ba7ae58a91642109963c3f340e8453f3205bb7ae`

Post-publication reconciliation successor compatibility is published through PR **#235** as canonical commit `6358e28b2502ea46fc5f19f8cb4fd52ffdcba14f`, tree `24208fc78a5fe30a428bd75f682ea38f5f2d4339`, parent `6b653d669c8b5474c806ecd5d61d854f25821cdf`, signature **verified / valid**.

Canonical source migrations are exactly **#1 through #13**. Migrations #1–#12 remain immutable. Migration #13 adds durable server-owned first-party session authority and secret-free audit evidence. No migration #14 is selected or authorized.

Sprint36 publishes a logical server-owned authority for eligible full sessions, separate from raw Laravel session identifiers. Internal `authority_id` is never exposed; opaque `public_handle` is used only for inventory/revocation addressing and is not an authentication authority.

Full authority is issued only after successful authentication/MFA. Restricted recovery, enrollment, challenge, or pending sessions are not inventoried. Laravel session rotation preserves the current logical authority. Canonical logout revokes the current durable authority before invalidation.

Session authority is bound to exact tenant + identity context and captures `credential_epoch` plus privileged `factor_epoch` when applicable. Missing, malformed, revoked, expired, mismatched, or stale epoch authority evidence fails closed at request time.

Fixed idle authority TTL is **7200 seconds**. Remote privileged revocation requires canonical step-up scope `session_control` with **300-second** freshness.

Published Local/Test/CI routes are:
- `GET /auth/sessions`
- `DELETE /auth/sessions/{public_handle}`
- `POST /auth/sessions/revoke-others`
- `POST /auth/reauthenticate/session-control`
- existing canonical `POST /auth/logout`

Sprint36 introduces **no revoke-all** operation.

Source defaults remain disabled: `ONEQAY_PERSISTENCE_ENABLED=false` and `ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED=false`.

Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.

The post-Sprint36 compatibility publication in PR #235 recognizes only the exact two-document reconciliation successor shape:
1. `docs/ai/AI_NEXT_TASK.md`
2. `docs/ai/AI_POST_SPRINT_36_CANONICAL_STATE.md`

Sorted-path SHA-256: `2e2c88793557dc4a0bed42a3610bc83f1c811a469dd50636f62b5c9397e9683d`.

Unknown successor shapes remain fail-closed.

This reconciliation does **not** select a Sprint37 implementation concern and grants no Sprint37 source authority, migration #14 authority, Preview/Production activation, updater authority, deployment authority, or release authority.

Attribution: **Lab | zefry**

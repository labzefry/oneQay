# Changelog

## Canonical post-Sprint48 JRN-005 source publication reconciliation — 2026-09-01

This current-facing section supersedes older post-Sprint47/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `83ffc5f77129767e836349ae3e3d2d0f2f5bb7ef`; tree `4f1027da5440630830c6c996f0b89be6462f255d`; GitHub signature **verified / valid**.
- Sprint48 JRN-005 **POS Shift/Register Opening Foundation** is **IMPLEMENTED / SOURCE-PUBLISHED** through PR #458 as `83ffc5f77129767e836349ae3e3d2d0f2f5bb7ef`, from qualified exact source head `d65aec3db0096429469878f322ea91d2c6731039`.
- The published JRN-005 source envelope remains exactly **15 paths** with sorted newline-terminated SHA-256 `5e19664988cabba0030f9927d26b0702370414be4cd6b424d585925b634ca2b8`.
- Canonical source now contains migrations exactly **#1–#18**. Migrations #16, #17, and #18 remain **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**. This reconciliation grants no migration execution authority.
- JRN-005 adds only bounded opening of one accountable active shift for the exact verified tenant/outlet/device-backed register execution context. It does not create shift close, opening cash, cash movement/reconciliation, general register administration, JRN-010, JRN-006 active-shift enforcement, sale/catalog/stock mutation, payment-provider integration, deployment, or release authority.
- `pos.shift.open` is deny-by-default through the canonical durable scoped authorization policy. The caller supplies only `operation_id`; actor identity, tenant, organization, outlet, device-backed register execution context, correlation identity, and event time remain server-derived.
- The active uniqueness boundary is `tenant_id + outlet_id + device_id + active_slot`; server-owned `active_slot = 1` and the database uniqueness constraint remain the final arbiter. Different verified devices at the same outlet may hold independent active shifts.
- Durable idempotency remains `tenant_id + operation_id`. Exact replay returns the original shift/opening evidence without a second write; conflicting operation-id reuse fails closed.
- `ONEQAY_POS_SHIFT_OPENING_ENABLED` remains **default false**. The HTTP mutation `POST /pos/shifts/open` remains **Local/Test/CI only** when explicitly armed and protected by active first-party session control plus the canonical POS session-context middleware. Technical Preview and Production remain unactivated.
- The final Sprint48 exact source head completed **36 materially triggered pull-request workflows / 36 success / 0 non-success**, including dedicated JRN-005, Sprint46 JRN-006, Sprint47 JRN-004, Sprint43 compatibility, Sprint21–Sprint42 historical preservation, M7.1–M7.6 boundaries, Governance, PHP Foundation, and updater/deployment-control regressions.
- Exact Product Owner merge authorization for PR #458 was recorded for head `d65aec3db0096429469878f322ea91d2c6731039` in comment `5493432963`; evaluator run `33504130660` / job `99844040504` completed **success** and exact-head status `product-owner-merge-authority` was **success** before squash publication.
- The bounded Sprint48 closure chain includes Sprint46 compatibility PR #472, Sprint47 compatibility PR #478, Sprint43 compatibility PR #479, and final source PR #458. Closed, superseded, stale, or unmerged probes do not constitute canonical authority.
- Technical Preview remains **`NO_SCHEMA_CHANGE / NOT ACTIVATED`**. Migrations #16, #17, and #18 remain unapplied/unactivated in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`NOT ACTIVATED`**. Deployment, release, migration execution, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint48 successor implementation concern is selected by this reconciliation. Any Sprint49 or other successor concern requires a separately bounded entry gate; new source/schema/runtime authority, Preview/Production activation, updater wiring, deployment, release, migration execution, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


## Canonical post-Sprint47 JRN-004 source publication reconciliation — 2026-08-30

This current-facing section supersedes older post-Sprint46/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `77ca26f06054b190b3b3ace9e51f875ec255316b`; tree `a8d4faf4bf14a59ac68b70190be81c50f373f839`; GitHub signature **verified / valid**.
- Sprint47 JRN-004 **Tenant/Outlet-Scoped Catalog Sellability and Current Price Preparation Foundation** is **IMPLEMENTED / SOURCE-PUBLISHED** through PR #440 as `77ca26f06054b190b3b3ace9e51f875ec255316b`, from qualified exact source head `c9ec9542270765168a8e6be369141f71bf4f3336`.
- The published JRN-004 source envelope remains exactly **15 paths** with sorted newline-terminated SHA-256 `c1ac6d5130c9d78ff99ed0accf5d11e3f08debc575c0d5152ef5b24e6f82ce02`.
- Canonical source now contains migrations exactly **#1–#17**. Migration #16 remains **SOURCE-PUBLISHED / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**. Migration #17 is **SOURCE-PUBLISHED / SELECTED IN SOURCE DESIGN / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**. This reconciliation grants no migration execution authority.
- JRN-004 adds only bounded tenant+outlet catalog preparation for current display name, unit price, currency/scale, and sellability. It does not create broad catalog CRUD, inventory administration, stock adjustment, pricing-engine, tax/fiscal, promotion, purchasing, supplier, refund/void, or external-payment authority.
- `pos.catalog.prepare` is deny-by-default through the canonical durable scoped authorization policy. Tenant, identity, organization, outlet, device, current session authority, mutation identity, correlation identity, and event time remain server-derived; no default permission grant is created.
- Canonical `oneqay_pos_sale_catalog_items` remains the current bounded catalog state. Existing `available_quantity` is preserved exactly; a newly prepared row receives only deterministic server-owned zero. Caller-controlled stock quantity remains forbidden.
- Migration #17 adds only the durable `oneqay_pos_catalog_preparation_journal` source required for exact idempotency/replay, conflict denial, before/after audit evidence, correlation identity, and event time. Exact replay returns the original applied after-state without rewriting current catalog state; conflicting replay fails closed.
- Completed JRN-006 sale-line price snapshots remain immutable and are not recalculated by later catalog preparation.
- `ONEQAY_POS_CATALOG_PREPARATION_ENABLED` remains **default false**. The HTTP mutation boundary remains **Local/Test/CI only** when canonical session control is enabled and the feature is explicitly armed. Technical Preview and Production remain unactivated.
- The final Sprint47 exact source head completed **36 materially triggered pull-request workflows / 36 success / 0 non-success**, including the dedicated Sprint47 JRN-004 regression, Sprint46 JRN-006 preservation, Sprint21–Sprint43 historical preservation, M7.1–M7.6 boundaries, Governance, PHP Foundation, and updater/deployment-control regressions.
- Exact Product Owner merge authorization for PR #440 was recorded for head `c9ec9542270765168a8e6be369141f71bf4f3336`; the post-authorization `issue_comment` evaluator completed **success** before squash publication.
- The bounded Sprint47 publication chain includes entry gate PR #425, schema/source-envelope gate PR #428, exact JRN-006 compatibility PR #441, frozen-source compatibility PRs #449 and #450, and final source PR #440. Closed, superseded, stale, or unmerged probes do not constitute canonical authority.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**. Migrations #15, #16, and #17 remain unapplied/unactivated in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment, release, migration execution, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint47 successor implementation concern is selected by this reconciliation. Any Sprint48 or other successor concern must begin with a separately bounded Product Owner entry gate; new source/schema/runtime authority, Preview/Production activation, updater wiring, deployment, release, migration execution, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


## Canonical post-Sprint46 JRN-006 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint45/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `437e463d4e862b1a1ba26cd500ea7ec23e352878`; tree `05fa7b8b6fa1cb95ef45728824c855ded43e205a`; GitHub signature **verified / valid**.
- Sprint46 JRN-006 **POS Sale Completion / Payment Recording / Receipt Foundation** is **IMPLEMENTED / PUBLISHED** through PR #419 as `437e463d4e862b1a1ba26cd500ea7ec23e352878`, from qualified exact source head `22b5ea04ad9abc742ed0c14b5c18cd0d00b57446`.
- The published JRN-006 source envelope remains exactly **14 paths** with sorted newline-terminated SHA-256 `ed29b6128c193f0efd6359748e220a37aefaec856acc4bc3b90f445ce3ccb674`.
- Canonical source now contains migrations exactly **#1–#16**. Migration #16 is **SOURCE-PUBLISHED / SELECTED IN SOURCE DESIGN / NOT EXECUTED / NOT APPLIED / NOT ACTIVATED**. This reconciliation grants no migration execution authority.
- JRN-006 implements only tenant/outlet-scoped sale completion, bounded payment recording, and deterministic receipt evidence. It does not complete the broader POS MVP and does not add catalog administration, shift/register lifecycle, void/refund/return, purchasing, supplier lifecycle, CRM, accounting, external payment providers, offline POS, or broad reporting.
- Runtime authority is reconstructed from the current first-party server session as verified tenant, identity, organization, outlet, and device context. The caller cannot select tenant, organization, outlet, device, actor, role, permission, session authority, product price, stock quantity, or sale identity.
- `pos.sale.complete` is required through the existing durable scoped authorization policy. No default grant is created.
- Server-owned durable catalog state supplies price and availability. Sale, line snapshots, stock decrement, payment/receipt evidence, and sale-event evidence remain tenant+outlet scoped and execute inside the canonical persistence transaction.
- Exact `tenant_id + operation_id` is the durable idempotency boundary. The semantic fingerprint binds actor/context, cart, tender category, and tendered amount. Exact replay returns the original receipt without a second stock decrement; conflicting replay fails closed.
- `ONEQAY_POS_SALE_COMPLETION_ENABLED` remains **default false**. The HTTP boundary is created only for **Local/Test/CI** when session control is enabled and the feature is explicitly armed. Technical Preview and Production remain unactivated.
- The final Sprint46 exact source head completed **36 materially triggered pull-request workflows / 36 success / 0 non-success**, including the dedicated Sprint46 JRN-006 regression, M7.2, M7.3, M7.4, M7.4A, M7.5, Sprint21–Sprint43 preservation, Governance, PHP Foundation, and updater/deployment-control regressions.
- Exact Product Owner merge authorization for PR #419 was recorded for head `22b5ea04ad9abc742ed0c14b5c18cd0d00b57446`; the post-authorization `issue_comment` evaluator completed **success** before squash publication.
- The bounded Sprint46 publication chain includes Business MVP entry gate PR #397, JRN-006 schema/source gate PR #399, bounded compatibility publications through PR #401–#418 as required by exact failure evidence, and final source PR #419. Closed or superseded unmerged probes do not constitute canonical authority.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**. Migration #15 and migration #16 remain unapplied/unactivated in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment, release, migration execution, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint46 successor implementation concern is selected by this reconciliation. Any Sprint47 or other successor concern must begin with a separately bounded Product Owner entry gate; Preview/Production activation, updater wiring, deployment, release, migration execution, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


## Canonical post-Sprint45 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint44/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `031d2379565a9b5cb5f1e6bc9e02957f8291206d`; tree `4beda30c01ffcc3f371c1460fc2caaa8fe4adea0`; GitHub signature **verified / valid**.
- Sprint45 **First-Party Pending MFA Identity Eligibility Revalidation Foundation** is **IMPLEMENTED / PUBLISHED** through PR #387 as `031d2379565a9b5cb5f1e6bc9e02957f8291206d`, from qualified exact source head `0f1e7db2193254171ac2ac3794ec0a8fd5a5140e`.
- Sprint45 source remains exactly **4 paths** with sorted newline-terminated SHA-256 `5dfaecf9be5c584b431606a7253515ab623ad9a11b4ff74062e794a1f40917c7`.
- Canonical source migrations remain exactly **#1–#15**. Sprint45 is **NO_SCHEMA_CHANGE**; migration #16 is **NOT SELECTED** and does not exist.
- Sprint45 revalidates current authentication eligibility for the exact pending tenant+identity before privileged pending-MFA enrollment start, enrollment confirmation, or challenge completion may advance authentication state.
- If the pending identity is disabled or otherwise ineligible, the pending framework authentication state is invalidated fail closed and the CSRF token is regenerated. Later reactivation cannot resume or restore that burned pending state; canonical fresh primary authentication remains mandatory.
- Sprint45 does not create a reactivation-login path, does not restore old framework/logical session authority, does not reuse historical public session handles or authority identifiers, and does not weaken Sprint41–Sprint44 disablement, termination, reactivation, or fresh-authentication no-resurrection semantics.
- Tenant, identity, organization, outlet, device, credential/factor epoch, privileged-factor, and current eligibility boundaries remain server-derived and deny-by-default. Caller-selected tenant/role/permission/session authority is not introduced.
- Sprint45 creates no credential, TOTP/recovery-factor, role, permission, grant, membership, organization/outlet/device, protected-control, deployment, release, updater, or migration-execution authority.
- The final Sprint45 exact source head completed **19 materially triggered pull-request workflows / 19 success / 0 non-success**, and the exact-head `product-owner-merge-authority` status completed **success** before squash publication.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; migration #15 and Sprint42/Sprint43/Sprint44/Sprint45 source remain unactivated/unapplied in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment, release, migration execution, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint45 successor implementation concern is selected by this reconciliation. Any Sprint46 or other successor concern must begin with a separately bounded Product Owner entry gate; migration #16, new source/schema/runtime authority, Preview/Production activation, updater wiring, deployment, release, migration execution, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**

## Canonical post-Sprint44 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint43/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `0c74a28b470238250439d7dde10518529a39b90e`; tree `4ee89603c5793f38d6ce89d7056bc33ff159e8eb`; GitHub signature **verified / valid**.
- Sprint44 **First-Party Identity Reactivation Fresh Authentication Re-entry Foundation** is **IMPLEMENTED / PUBLISHED** through PR #365 as `0c74a28b470238250439d7dde10518529a39b90e`, from qualified exact source head `e6729db82e554a433507a1c176166dfeb184bf76`.
- Sprint44 source remains exactly **4 paths** with sorted newline-terminated SHA-256 `ff01f1355de6c7fdfd28c2d359eb70787dd8448f0b1fc6d9cb73c1a0fb76580a`.
- Canonical source migrations remain exactly **#1–#15**. Sprint44 is **NO_SCHEMA_CHANGE**; migration #16 is **NOT SELECTED** and does not exist.
- Sprint44 enforces current first-party identity authentication eligibility in the canonical fresh-login path after credential verification and before organizational context entry, MFA pending state, credential-epoch capture, logical authority issuance, or framework-session establishment. Disabled or otherwise ineligible identity evidence fails closed with the existing safe `AUTHENTICATION_FAILED` envelope.
- Sprint43 reactivation remains eligibility-only. Reactivation creates no framework session, logical session, session authority, public session handle, or restored authority. A reactivated identity must complete canonical fresh authentication with current credentials and current organizational validity before any new authority can be issued.
- Fresh authentication after valid reactivation issues new logical authority and a new public session handle. Historical revoked, expired, idle-expired, epoch-invalid, membership-invalid, organization/outlet/device-invalid, or otherwise terminated authority remains invalid and is never resurrected or reused.
- Sprint44 does not clear historical `revoked_at_unix`, does not restore old authority/session identifiers, does not add `login_after_reactivate`, restore, resume, automatic-login, self-service reactivation-login, protected-control bypass, break-glass, or caller-selected tenant/role/permission/session authority.
- Cross-tenant credential borrowing and invalid organizational re-entry remain denied. Later Sprint41 disablement plus Sprint42 termination semantics revoke the newly issued target authority while preserving unrelated active sessions and historical revocation evidence.
- The final Sprint44 dedicated regression proves the exact source envelope, NO_SCHEMA_CHANGE and migration #16 lock, canonical composition/route non-widening, fail-closed ordering, PHP syntax, fresh-authentication re-entry semantics, Sprint41–Sprint43 preservation, full application regression, lifecycle locks, and tracked-source cleanliness.
- The final Sprint44 exact source head completed **21 materially triggered pull-request workflows / 21 success / 0 non-success**, and the exact-head `product-owner-merge-authority` status completed **success** before squash publication.
- The bounded Sprint44 publication chain includes entry/schema gates PR #353 and #356, bounded compatibility publications including PR #352, #355, #357, #359, #363, and #364, and final source PR #365. Closed unmerged attempts such as PR #354 and #358 do not constitute canonical authority.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; Sprint41 migration #15 and Sprint42/Sprint43/Sprint44 source remain unactivated/unapplied in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment, release, migration execution, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint44 successor implementation concern is selected by this reconciliation. Any Sprint45 or other successor concern must begin with a separately bounded Product Owner entry gate; migration #16, new source/schema/runtime authority, Preview/Production activation, updater wiring, deployment, release, migration execution, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


## Canonical post-Sprint43 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint42/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `034c3dcc86d202bb076c3b03d5cb933f856ec308`; tree `51d05048de8d842465baa7999cb4b68b2b4444c7`; GitHub signature **verified / valid**.
- Sprint43 **First-Party Identity Authentication Eligibility Reactivation Foundation** is **IMPLEMENTED / PUBLISHED** through PR #350 as `034c3dcc86d202bb076c3b03d5cb933f856ec308`, from qualified exact source head `7036fbf7613d086a25bb36bc63c33607036b87e3`.
- Sprint43 source remains exactly **9 paths** with sorted newline-terminated SHA-256 `3d0293362f451fe4bf472d0d2c38c3eec3d67df75b451ba5b273e8dbdb0f2eed`.
- Canonical source migrations remain exactly **#1–#15**. Sprint43 is **NO_SCHEMA_CHANGE**; migration #16 is **NOT SELECTED** and does not exist.
- Sprint43 adds only server-authorized reactivation for one exact same-tenant ordinary identity through the dedicated `reactivate` operation and the exact transition `first_party_authentication_enabled: false -> true`. Authority remains server-derived tenant-scoped policy administration plus `AdministrationPermission::MANAGE`; self, protected-control, missing, unauthorized, malformed, and cross-tenant targets remain fail closed.
- The reactivation API is separate from Sprint41 disablement: `POST /administration/identities/{identity_id}/authentication-reactivation`; the request body remains exactly `mutation_id`. No generic toggle, caller-selected operation, boolean setter, bulk mutation, timed reactivation, or cross-tenant authority is introduced.
- Durable evidence reuses `oneqay_identity_authentication_eligibility_mutations` with the exact operation value `reactivate`, deterministic fingerprint binding, `applied` / `no_change` outcomes, exact replay, mutation-ID conflict denial, and deterministic convergence.
- Reactivation never creates or restores framework/logical sessions, never clears `revoked_at_unix`, never revives Sprint42-terminated or expired sessions, and never synthesizes MFA, step-up, enrollment, or recovery authority. Fresh authentication remains mandatory. Sprint36–Sprint42 session, revocation, lifetime, organizational-access, request-time eligibility, disablement, and disablement-triggered termination semantics remain preserved.
- Reactivation does not restore or mutate password/hash, credential epoch, TOTP/recovery factors, factor epoch, tenant/organization/outlet/device membership, role assignments, permissions, grants, or protected-control state.
- The bounded Sprint43 publication chain includes entry gate PR #340, schema/source-envelope preservation and publication PRs #341–#342, bounded compatibility publications #345–#347 and #349, and final source PR #350. Closed unmerged compatibility attempts do not constitute canonical authority.
- The final Sprint43 exact source head completed **36 materially triggered pull-request checks / 36 success / 0 non-success**, and the exact-head `product-owner-merge-authority` status completed **success** before squash publication.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; migration #15 and Sprint42/Sprint43 source remain unactivated/unapplied in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment, release, and rollback remain **`NOT AUTHORIZED`**.
- No post-Sprint43 successor implementation concern is selected by this reconciliation. Any Sprint44 or other successor concern must begin with a separately bounded Product Owner entry gate; migration #16, new source/schema/runtime authority, Preview/Production activation, updater wiring, deployment, release, and rollback are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


## Canonical post-Sprint42 source publication reconciliation — 2026-08-29

This current-facing section supersedes older post-Sprint41/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `6507927a258ab1378d6d3878e807b54cc9e6c5b2`; tree `69e165705483ebdca15e7ad66832d353beb390d8`; GitHub signature **verified / valid**.
- Sprint42 **First-Party Identity Disablement Session Termination Foundation** is **IMPLEMENTED / PUBLISHED** through PR #334 as `6507927a258ab1378d6d3878e807b54cc9e6c5b2`, from qualified exact source head `8fd104d817e2b473502f142198b24788e13afe41`.
- Sprint42 source remains exactly **8 paths** with sorted newline-terminated SHA-256 `6315890d318c3cdfca549bfacef6cb8d1ca66a4421416b49b4978095a98b6729`.
- Canonical source migrations remain exactly **#1–#15**. Sprint42 is **NO_SCHEMA_CHANGE**; migration #16 is not selected and does not exist.
- Sprint42 composes the existing Sprint41 authorized disable-only identity eligibility mutation with exact-tenant + exact-target active logical-session termination inside the canonical `PersistenceTransaction`. Successful fresh `applied`, fresh `no_change`, and exact replay outcomes re-enforce zero active target logical sessions before success without adding a public route or widening the Sprint41 payload.
- Sprint40 request-time identity authentication-eligibility revalidation remains an independent mandatory defense. Sprint36–Sprint39 session ownership, revocation, lifetime, tenant-membership, and organization/outlet/device revalidation semantics remain preserved.
- Sprint42 inserts no self-service session audit event, introduces no credential/factor/membership/grant mutation, and creates no enable/reactivation path.
- The bounded Sprint42 entry/schema/source-preservation and compatibility chain is published through PR #326–#333, #336, and #335. The final 19-workflow historical compatibility predecessor PR #335 is published as `fdbf30e2637dc71be16ac3f374f5973f104a3a9c`; PR #336 is published as `dea436904e56ed71353b56ab9b792762db2d95b7`.
- The final Sprint42 exact source head completed **29 materially triggered pull-request workflows / 29 success / 0 non-success**, and the exact-head `product-owner-merge-authority` status completed **success** before squash publication.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; Sprint41 migration #15 and Sprint42 source remain unactivated/unapplied in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **`NOT AUTHORIZED`**.
- No post-Sprint42 successor implementation concern is selected by this reconciliation. Any Sprint43 concern must begin with a separately bounded Product Owner entry gate; migration #16, new source/schema/runtime authority, reactivation, Preview/Production activation, updater wiring, deployment, and release are not implied.
- This reconciliation changes only the established **13-document canonical state envelope**, whose sorted newline-terminated path SHA-256 remains `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.

Attribution: **Lab | zefry**


## Canonical post-Sprint41 source publication reconciliation — 2026-08-27

This current-facing section supersedes older post-Sprint40/current-state wording retained below as historical provenance. It records canonical source and CI evidence only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `1994a7821846db9f872edb62a984c4248f766c1e`; tree `1eb7a9294eed86c6e3333f0db25ef9e3793aaaf0`; GitHub signature **verified / valid**.
- Sprint41 **First-Party Identity Authentication Eligibility Administration Foundation** is **IMPLEMENTED / PUBLISHED** through PR #315 as `1994a7821846db9f872edb62a984c4248f766c1e`, from qualified source head `fadd0c5bba83e4a2e2e209e1750de2224b7f3b68`.
- Sprint41 source remains exactly **12 paths** with sorted newline-terminated SHA-256 `b2c5fc10a8baa2d56991d6dbd36b0407159d70953654ef322a9a11d23660489b`.
- Canonical source migrations are exactly **#1–#15**. Migration #15 creates only the tenant-scoped `oneqay_identity_authentication_eligibility_mutations` journal; migrations #1–#14 remain immutable.
- Sprint41 implements only server-authorized `first_party_authentication_enabled: true -> false` administration for eligible ordinary same-tenant identities. No enable/reactivation, bulk mutation, protected-control disablement, administrator session revocation, credential mutation, factor mutation, membership mutation, or grant mutation authority exists.
- Sprint40 remains the independent request-time consumer of current authentication eligibility. Sprint41 does not weaken Sprint36–Sprint40 session, lifetime, organizational-access, or eligibility revalidation controls.
- Bounded historical/source compatibility closure required for source publication is merged through PR #316–#323, and the post-Sprint41 canonical-document preservation predecessor is published through PR #325. These PRs changed preservation/governance behavior only where applicable and created no runtime, deployment, updater, Preview, or Production authority.
- Canonical main-push oracle **M7.5 Technical Preview Release Artifact #338** (run `33095155642`) completed **SUCCESS** on `1994a7821846db9f872edb62a984c4248f766c1e`.
- Main-push **Backend Updater Control Plane Regression #121**, **Privileged Update Security Regression #123**, and **Read-Only Update Deployment UI Regression #106** also completed **SUCCESS** on the same canonical commit; these are regression evidence only.
- Technical Preview remains **`NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`**; Sprint41 source and migration #15 are **not activated/applied** in Technical Preview. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **`NOT AUTHORIZED`**.
- No post-Sprint41 successor implementation concern is selected or authorized by this reconciliation. Any next concern must begin with a separately bounded Product Owner entry gate; no Sprint42, migration #16, source, schema, runtime, Preview, Production, updater, deployment, or release authority is implied.

Attribution: **Lab | zefry**


## Canonical post-Sprint40 M7.5 preservation closure — 2026-08-27

This current-facing section supersedes older pre-Sprint40/current-state wording retained below as historical provenance. It records repository state only and creates no new implementation or lifecycle authority.

- Functional canonical baseline reconciled by this docs-only publication: `fe502ee40471633e292606ef203a2f0e90754175`; tree `6b494a9a152539a0e922bb564ff96930ff82d86c`; GitHub signature **verified / valid**.
- Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** source is **IMPLEMENTED / PUBLISHED** through PR #286 as `03e86d4e677632a7516c8f4ed2c34045647b774a`, from qualified source head `c8d0f1ab6477f1c743247a519cbc1e6996365199`.
- The Sprint40 source envelope remains exactly **8 paths** with SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- Canonical source migration files are exactly **#1–#14**. Migration #14 exists in source and adds only `first_party_authentication_enabled`; this does **not** authorize or imply schema application in Technical Preview or Production.
- Post-Sprint40 historical-regression preservation is published through PR #295 (Sprint32 horizon) and PR #296 (Sprint39 horizon). The bounded M7.5 seven-workflow correction is published through PR #297 and corrected for canonical-main push behavior through PR #298.
- The governed seven-workflow changed-path fingerprint remains `4784ffca1c940d3fa54a2a3988ead07e2de993bde8d3af2bd41014dbdf905be0`.
- Canonical main-push oracle **M7.5 Technical Preview Release Artifact #307** (run `33040247339`) completed **SUCCESS** on `fe502ee40471633e292606ef203a2f0e90754175`. Full-source tests, historical M7.2/M7.3 fixtures with temporary migration #10–#14 isolation, restoration verification, POS/Preview/background regressions, manifest/checksum validation, deterministic archive reproduction, artifact upload, and tracked-source cleanliness all succeeded.
- The oracle and generated qualification artifact are CI evidence only. **Technical Preview remains `NO_SCHEMA_CHANGE / SPRINT40 NOT ACTIVATED`; Production remains `NO-GO / NOT AUTHORIZED`; updater remains `DISABLED / UNWIRED`; deployment and release remain `NOT AUTHORIZED`.**
- PR #295–#298 changed workflow-governance/preservation behavior only; they did not add application source, apply schema, activate runtime, or grant standing successor authority.
- No post-Sprint40 successor implementation concern is selected or authorized by this reconciliation. Any next concern requires fresh canonical-main verification and separate bounded Product Owner authority.

Attribution: **Lab | zefry**


## Canonical Sprint40 pre-source documentation synchronization — 2026-08-25

This current-facing entry records the governed pre-source Sprint40 state. Historical changelog sections remain provenance.

### Added

- Recorded Sprint40 **First-Party Session Identity Disablement Revalidation Foundation** as the selected concern after published entry-gate PR #268 and schema/source-envelope gate PR #270.
- Recorded the future migration #14 selection: `apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php`, adding only non-null boolean `first_party_authentication_enabled` default `true` to `oneqay_identities` when the later source stage is separately authorized.
- Recorded the frozen exact eight-path future Sprint40 source envelope with SHA-256 `a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8`.
- Recorded source-preservation publication PR #271, compatibility corrections PR #272/#273, and documentation-sync preservation predecessor PR #274.

### Changed

- Synchronized exactly 13 canonical documentation paths to the current pre-source Sprint40 state under preserved fingerprint `b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d`.
- Current canonical source migrations remain exactly **#1–#13**; migration #14 is selected for later implementation but does not yet exist or apply on canonical `main`.
- Current next-work direction is the separately governed frozen Sprint40 source implementation after this documentation synchronization is published and canonical `main` is freshly verified.

### Security and lifecycle boundaries

- Sprint40 identity eligibility semantics remain request-time, server-authoritative, and fail-closed; existing credential/factor epochs, session revocation/inventory, idle/absolute lifetime, tenant membership, and organization/outlet/device revalidation remain independent controls.
- This documentation synchronization creates no source/dependency/workflow YAML/runtime mutation, no migration #14 creation or execution, no route/API/payload/audit-event/feature-arm addition, and no Preview or Production activation.
- Technical Preview remains **`NO_SCHEMA_CHANGE` / Sprint40 not activated**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Deployment and release remain **NOT AUTHORIZED**.

Attribution: **Lab | zefry**

## Canonical post-Sprint 33 program-state reconciliation — 2026-08-20

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint32 wording retained below as historical provenance.

- Sprint 21 through Sprint 33 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 33 Recovery-Bound Password Reset Completion Foundation is published through source PR #213 as `9eba56d92b4b714225d677990ffed93687b0b2cb` with tree `492e723b6343dab518b43645883976ad20f0054c`, parent `c89baa55318dca230cd0ef792df80e3d54b8165d`, and a verified/valid publication signature after **24/24** exact-head pull-request workflows succeeded.
- The qualified Sprint33 source head was `a7a50644cbe67e6f08138c79cf50a9350e8e220d`; source remained exactly **39 paths** with sorted-path SHA-256 `04a1177c12712183a7dda4ae81be1356c0e41294533336c9f999d376c224712a`.
- Sprint33 entry-gate PR #211 and source-envelope gate PR #212 remain published provenance; their authorities and PR #213 merge authority are consumed and grant no standing successor authority.
- Canonical source migrations remain exactly **#1 through #10** and are unchanged by Sprint33. No migration #11 is authorized.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default and recovery execution remains bounded to **Local/Test/CI**.
- Sprint32 proof still establishes only `password_reset_required` restricted state for exactly **600 seconds**; Sprint33 binds the consumed server-owned recovery `code_id` into that restricted evidence and exposes only `POST /auth/recovery/password-reset` inside the same bounded recovery arm.
- Reset accepts only opaque `password` input of **12–4096 bytes**, performs no trim/normalization, hashes with `PASSWORD_DEFAULT`, updates only the existing exact credential row, revokes remaining unused recovery codes, and appends exactly one secret-free `password_reset_completed` audit event atomically.
- Credential epoch is derived without schema change from the durable count of `password_reset_completed` rows. Fresh normal login captures the epoch; stale, malformed, negative, future, or post-reset legacy-missing epoch evidence fails closed as applicable.
- Protected-control principals and identities with confirmed privileged TOTP remain ineligible for recovery completion; TOTP secret material is not read, decrypted, replaced, deleted, or mutated.
- Successful reset invalidates the restricted session and regenerates CSRF but establishes no normal/full login, MFA evidence, step-up evidence, or epoch evidence; fresh normal login remains mandatory.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Authenticated in-session password change, administrative password overwrite, MFA/TOTP recovery and factor lifecycle, protected-control recovery bypass, support/admin bypass, email/SMS recovery delivery, passkeys/WebAuthn, federation, API-token authentication, Preview/Production auth/schema activation, updater activation, deployment, and release remain separately governed.
- Sprint32 + Sprint33 now form a bounded Local/Test/CI end-to-end recovery sequence for eligible non-protected identities without confirmed privileged TOTP, but this does not activate recovery in Technical Preview or Production.
- This reconciliation selects **no new post-Sprint33 implementation concern** and grants no Sprint34, migration #11, source, Preview, Production, updater, deployment, or release authority.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_33_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 32 program-state reconciliation — 2026-08-19

For current identity, security, recovery, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint30/post-Sprint31 wording retained below as historical provenance.

- Sprint 21 through Sprint 32 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 31 Privileged Reauthentication / Step-Up Session Freshness Foundation remains published with exact **300-second** freshness for the `policy_administration` scope and its source-default-disabled Local/Test/CI boundary.
- Sprint 32 Authentication Recovery / JRN-003 Recovery Proof Foundation is published through source PR #208 as `914f93f8636bbd0901c61d8a8f14ad69c2c8fbfe` with tree `89f8dcea209ea912ba2539f3c6224a3a0519c8f7`, parent `7f2cc64e5a85158fb24cf03b61d2b36ead73190a`, and a verified/valid publication signature after **24/24** exact-head pull-request workflows succeeded.
- Sprint 32 source remained within the exact **32-path** envelope whose sorted-path SHA-256 is `db230ab3b77fff67f0bd12d7d7b615146d9d9df9a0af12014214e1862e9f6867`.
- Canonical source migrations are exactly **#1 through #10**. Migrations #1–#9 remain immutable. Migration #10 creates only `oneqay_identity_recovery_codes` and `oneqay_identity_recovery_audit`. No migration #11 is authorized.
- `ONEQAY_AUTHENTICATION_RECOVERY_ENABLED=false` remains the source default and Sprint 32 recovery execution remains bounded to **Local/Test/CI**.
- Successful recovery-code rotation issues exactly **8** `rq1.<22-char selector>.<43-char secret>` codes, persists no plaintext recovery secret/code, and uses SHA-256 digest verification with `hash_equals` plus secret-free audit evidence.
- Recovery-code rotation and proof are atomic; same-code replay/concurrency is fail-closed with at most one winner.
- Successful recovery proof establishes only the restricted `password_reset_required` session for exactly **600 seconds**. It does **not** establish a normal/full authenticated session, does not populate the five canonical Sprint27 full-session keys, and does not read/decrypt the TOTP secret.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Password reset/change/overwrite, automatic/full login from recovery proof, MFA/TOTP recovery, factor replacement/deletion, protected-control recovery, support/admin bypass, email/SMS recovery, passkeys, federation, API-token authentication, Preview/Production auth/schema activation, updater activation, deployment, and release authority remain separately governed and **NOT AUTHORIZED** by Sprint 32 or this reconciliation.
- Sprint 32 publishes the JRN-003 **recovery-proof foundation** only; this reconciliation does not claim end-to-end password recovery completion because password reset/change/overwrite remain excluded.
- This reconciliation selects **no new post-Sprint32 implementation concern** and grants no Sprint33, migration #11, source, Preview, Production, updater, deployment, or release authority. Any subsequent source work requires a separately bounded Product Owner entry gate.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_32_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 30 program-state reconciliation — 2026-08-19

For current identity, security, schema, runtime, workflow, and next-work interpretation, this section supersedes older current-facing post-Sprint28/post-Sprint29 wording retained below as historical provenance.

- Sprint 21 through Sprint 30 governed control/identity foundations are **COMPLETE / IMPLEMENTED / PUBLISHED** within their bounded authorities.
- Sprint 29 First-Control-Principal Bootstrap Credential Foundation is published through source PR #195 and closes the first protected-control credential circular dependency without credential overwrite, password recovery, or session creation.
- Sprint 30 Privileged TOTP MFA Foundation is published through PR #199 as `6d41755eba4030c2b0b7c4f3b7a5806b761b0ad7` with tree `bf1d56af5524e77919833bd64b585cdca84af55d` after **22/22** exact-head workflows succeeded.
- Sprint 30 source remained within the exact **46-path** envelope whose sorted-path SHA-256 is `95daaf86ba93ae797fccf3825d65d27acd4f71ee58916898a16fbc83d432a5ce`.
- Canonical source migrations are exactly **#1 through #9**. Migration #9 adds one tenant-scoped TOTP-factor row per identity with encrypted secret ciphertext and monotonic accepted-time-step replay state.
- The direct TOTP dependency is pinned to `spomky-labs/otphp` **11.5.0**; oneQay does not implement custom TOTP/HMAC/Base32 cryptography.
- `ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED` remains source-default **false** and Sprint 29–30 delivery remains bounded to **Local/Test/CI**.
- For an armed protected-control principal, password verification alone does not establish the full privileged session. Restricted enrollment/challenge state is used until successful confirmed TOTP challenge establishes full session MFA evidence.
- TOTP secrets are Restricted, encrypted at rest, context-bound to tenant + identity, and never stored as plaintext. Accepted TOTP time steps advance monotonically to deny replay.
- Technical Preview remains **`NO_SCHEMA_CHANGE`**. Production remains **`NO-GO / NOT AUTHORIZED`**. Updater remains **`DISABLED / UNWIRED`**. Durable persistence remains source-default-disabled with `ONEQAY_PERSISTENCE_ENABLED=false`.
- Password change/reset/recovery, MFA recovery, factor replacement/deletion, multiple factors, WebAuthn/passkeys, federation, API-token authentication, Preview auth activation, and Production auth activation remain separately governed.
- **JRN-003 remains UNRESOLVED**; this reconciliation creates no password/MFA recovery path.
- The next logical governed identity/security concern is **Privileged Reauthentication / Step-Up Session Freshness Foundation**. DEC-006 already requires risk-based reauthentication/step-up for sensitive operations. This concern is **CANDIDATE / NOT AUTHORIZED** until a separate bounded entry gate freezes semantics, freshness evidence, session transitions, routes, exact source envelope, schema decision, and preservation tests.

The detailed factual baseline is `docs/ai/AI_POST_SPRINT_30_CANONICAL_STATE.md`. Historical sections below remain preserved as provenance and must not override this section for current-state interpretation.

Attribution: **Lab | zefry**

## Canonical post-Sprint 28 program-state reconciliation — 2026-08-18

The current `[Unreleased]` program state now includes the governed control/identity publication sequence through Sprint 28 and the documentation-only post-Sprint28 canonical reconciliation.

Attribution: **Lab | zefry**

## [Unreleased]

### Added

- Published Sprint 21 — durable tenant-scoped role/permission policy foundation.
- Published Sprint 22 — governed policy-administration foundation.
- Published Sprint 23 — initial tenant-administrator provisioning foundation.
- Published Sprint 24 — protected-control administrator lifecycle foundation.
- Published Sprint 25 — policy-administration delivery foundation with durable session-context re-verification.
- Published Sprint 26 — first-party password credential verification foundation for exact `(tenant_id, identity_id)` ownership, Local/Test/CI only, using canonical migration #7.
- Published Sprint 27 — first-party login/logout and server-side session establishment foundation for Local/Test/CI only, with session fixation protection, CSRF rotation, durable tenant/organizational verification, and no credential mutation.
- Published Sprint 28 — governed two-step first-party initial password enrollment foundation for Local/Test/CI only. Administrator authorization is separated from target password selection; enrollment tokens are generated from `random_bytes(32)`, persisted only as SHA-256 digests, bounded to a 900-second TTL, and credentials are created insert-only with `PASSWORD_DEFAULT`.
- Added canonical migration #8, `0000_00_00_000008_create_initial_password_enrollments.php`, as the only Sprint 28 schema addition. Migrations #1–#7 remain immutable.
- Published Sprint 28 source PR #188 as `b012262b0028c21c7662d5a9edec3cbf249bba5e` after all 19 triggered exact-head workflows completed successfully.
- Published post-Sprint28 canonical reconciliation PR #189 as `68a9b5736a3fc169b50984857954322b169bc42e`.

### Changed

- Reconciled current-facing `README.md`, `PROJECT_MANIFEST.md`, `ROADMAP.md`, `TASKS.md`, `CHANGELOG.md`, `docs/ai/AI_NEXT_TASK.md`, `docs/ai/AI_PROJECT_STATE.md`, `docs/ai/AI_SESSION_STATE.md`, `ARCHITECTURE.md`, `DATABASE.md`, `DEPLOYMENT.md`, `SECURITY.md`, and `.github/workflows/README.md` to the published post-Sprint28 canonical state.
- Updated the current next-work direction from historical Secure Web Updater wording to the separately governed **First-Control-Principal Bootstrap Credential Foundation** concern.
- Updated current workflow inventory to recognize the governed Sprint 21–28 regression chain and Sprint 28's 19/19 exact-head qualification result.
- Updated current database-state wording to recognize canonical migrations exactly #1–#8 while preserving Technical Preview `NO_SCHEMA_CHANGE` and Production schema non-authority.
- Updated current identity/security wording to recognize published credential verification, login/session establishment, and initial-password-enrollment controls without broadening their Local/Test/CI runtime boundary.

### Security

- Preserved exact tenant-scoped credential ownership and deny-by-default tenant/control boundaries.
- Preserved generic credential/login/enrollment failure behavior and anti-enumeration principles.
- Preserved session invalidation/regeneration and CSRF-token rotation on first-party login/logout.
- Preserved one-time digest-only initial-password-enrollment tokens and insert-only credential creation.
- Preserved explicit exclusion of password change, reset, recovery, rotation, revocation, credential overwrite, MFA/passkey/federation delivery, and first-control-principal bootstrap implementation.

### Runtime and release boundaries

- Technical Preview remains **`NO_SCHEMA_CHANGE`**.
- Production remains **`NO-GO / NOT AUTHORIZED`**.
- Updater remains **`DISABLED / UNWIRED`**.
- Durable application persistence remains default-disabled with **`ONEQAY_PERSISTENCE_ENABLED=false`**.
- Sprint 26–28 credential/login/enrollment delivery remains Local/Test/CI-only and absent from Preview and Production.

### Next governed concern

The next logical identity concern is **First-Control-Principal Bootstrap Credential Foundation**. It remains **UNRESOLVED / NOT AUTHORIZED** and requires a separately published bounded entry gate before any source implementation.

This changelog reconciliation does **not** authorize Sprint 29 implementation, migration #9, any assumption that migration #9 is required, new source/dependency/workflow/runtime/schema mutation, password lifecycle expansion, Technical Preview credential activation, Production authentication/enrollment, updater activation, deployment, Release, Phase 0 Exit, Sprint 14, or Production.

## Historical changelog provenance

Detailed historical changelog entries through M7.5, prior decisions, governance recurrences, and earlier publication snapshots remain immutable in Git history. Current-state interpretation must follow this post-Sprint28 section together with `docs/ai/AI_POST_SPRINT_28_CANONICAL_STATE.md`.

No product release exists yet. A dated/tagged product version will be added only through the separately governed release process.

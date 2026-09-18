# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint180 closed
**Canonical engineering commit:** `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`
**Latest engineering PR:** #787 — `Sprint180: add initial merchant context-assisted sign-in`
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Completed Sprint180 state

Sprint180 materialized `MERCHANT_INITIAL_CONTEXT_ASSISTED_SIGN_IN_FOUNDATION`.

- [x] Existing first-party login controller preserved unchanged.
- [x] Existing first-party login route preserved unchanged.
- [x] Existing MFA enrollment/challenge lifecycle preserved.
- [x] Existing `/pos` transition preserved.
- [x] Server-assisted exact merchant login context added.
- [x] Only tenant/identity/organization/outlet/device context fields are browser-delivered.
- [x] `provisioning_id` is not browser-delivered.
- [x] Manual editing of opaque tenant/identity/organization/outlet/device IDs removed from initial merchant sign-in.
- [x] Invalid or incomplete assisted context fails closed.
- [x] Local/Test/CI + persistence + session-control gates preserved.
- [x] Sprint178 entry regression preserved.
- [x] Sprint179 POS-ready bootstrap regression preserved.
- [x] Exact engineering head completed 62/62 surfaced PR-triggered workflows successfully.
- [x] Repository-native exact-head Product Owner merge authority succeeded.
- [x] Engineering PR #787 squash merged at `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`.
- [x] Exact six-path canonical reconciliation envelope defined.

Engineering envelope: 4 paths; SHA-256 `2c122014511daaeb8ca1d5cbd2ee4bb184733ed1154e08c8e0000f3b758c9c6c`.

Canonical reconciliation envelope: 6 paths; SHA-256 `eb9b36e214455c09714f9d03f034e85aec06506d43b64be314b2b95b00b4f41b`.

## Preserved lifecycle state

Machine-readable operational state under `ops/final-shift-close/` remains authoritative and unchanged: selected target `null`; migration #27 `NOT_EXECUTED`; permission provisioning `NONE`; producer dispatch not performed; feature activation `INACTIVE`; deployment authority `NOT_GRANTED`; Technical Preview/Production `NOT_AUTHORIZED`; updater `INACTIVE`; runtime allowlist Local/Test/CI only.

## Sprint180 canonical closure

- [x] Engineering PR qualified and squash merged.
- [x] Engineering evidence frozen at `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`.
- [x] Canonical reconciliation limited to workflow + five project-state documents.
- [x] Reconciliation preserves the engineering commit rather than replacing it with the reconciliation squash.
- [x] Operational NO-GO remains unchanged.

## Next engineering position

Begin **Sprint181 bounded discovery** only from the fully reconciled Sprint180 state. Select the smallest non-duplicative P0/P1 blocker proven by live repository evidence. No real merchant provisioning, runtime widening, migrations, deployment, Technical Preview, Production, updater activation, durable-target selection, producer dispatch, or operational permission provisioning is pre-authorized.

Author by Lab | zefry

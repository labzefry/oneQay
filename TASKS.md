# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint203 closed canonically
**Canonical engineering commit:** `088dc0a38d80abc776cacc7052ddb3dbfdcf0c70`
**Engineering PR:** #839
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint203 completed

- [x] Reject invasive direct staging allowlist widening after exact-head evidence.
- [x] Reject the intermediate broad composition design after exact-head compatibility evidence.
- [x] Preserve historical Local/Test/CI repository guards unchanged.
- [x] Require explicit `ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED=true` for the external staging bridge.
- [x] Keep Production and unknown runtimes outside the bridge.
- [x] Add bounded request-scoped compatibility projection with runtime restoration.
- [x] Add guarded `oneqay:merchant-context:bootstrap-staging` wrapper over existing atomic merchant bootstrap authority.
- [x] Cover merchant sign-in/session and existing account-security/MFA/recovery controls when enabled.
- [x] Cover POS Operations Hub, Catalog & Opening Stock, Shift Start/opening cash, Cashier, and durable sale completion.
- [x] Keep sale void, refund, closing cash, Final Shift Close, updater, deployment, target selection, and activation outside the bridge.
- [x] Complete 88/88 exact-head qualification.
- [x] Product Owner merge authority SUCCESS.
- [x] Engineering squash `088dc0a38d80abc776cacc7052ddb3dbfdcf0c70`.
- [x] Operational NO-GO preserved.

Engineering envelope SHA-256: `42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766`.

Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Next

After canonical reconciliation, begin **Sprint204 durable non-synthetic staging target qualification/readiness**. Prioritize a meaningful production-readiness result rather than another merchant UI micro-feature. The work must remain read-only against any real target unless separate operational authority is granted. Do not infer deployment, migration, permission provisioning, target selection, Technical Preview, Production, updater, Final Shift Close, or producer-dispatch authority.

Author by Lab | zefry

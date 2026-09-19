# oneQay Tasks

**Current canonical engineering checkpoint:** Sprint199 closed canonically
**Canonical engineering commit:** `f2692018b261a723b9b360efe650969926adb2d2`
**Engineering PR:** #829
**Canonical status authority:** `PROJECT_MANIFEST.md`

## Sprint199 completed

- [x] Add merchant-facing Account & Security controls to the POS Operations Hub.
- [x] Reuse the existing authenticated password-change endpoint and require fresh sign-in after success.
- [x] Reuse existing password recovery-code rotation authority.
- [x] Reuse existing privileged authenticator recovery-code rotation authority.
- [x] Add Foundation password recovery through the existing restricted recovery-session flow.
- [x] Add lost-authenticator replacement through the existing privileged TOTP recovery flow.
- [x] Refresh XSRF after session regeneration in multi-step recovery.
- [x] Keep recovery material out of localStorage/sessionStorage.
- [x] Keep security capabilities server-derived from existing routes/configuration.
- [x] Introduce no new permission, schema, authentication engine, migration execution, or operational activation authority.
- [x] Complete 85/85 exact-head engineering qualification.
- [x] Product Owner merge authority SUCCESS.
- [x] Engineering squash `f2692018b261a723b9b360efe650969926adb2d2`.
- [x] Operational NO-GO preserved.

Engineering envelope SHA-256: `2aba38a7f80dcc6178ce39f865789b4e20b26f2d3ebb96b1d307af0c628e77e4`.

Canonical reconciliation envelope SHA-256: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.

## Next

After canonical reconciliation, begin **Sprint200 business-first bounded discovery**. Select the next material P0/P1 merchant end-to-end blocker and avoid anti-granular lifecycle chaining. Do not assume migration, permission, updater, deployment, durable-target, live Technical Preview, Production, persistence, Final Shift Close, or producer-dispatch authority.

Author by Lab | zefry

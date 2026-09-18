# Changelog

## 2026-09-18 — Sprint179 closed canonically

**Sprint179: Merchant Bootstrap Initial POS Operation Authorization Foundation**

- Objective: `MERCHANT_BOOTSTRAP_INITIAL_POS_OPERATION_AUTHORIZATION_FOUNDATION`.
- Closed the proven bootstrap-to-POS authorization gap for the initial merchant principal.
- Preserved Sprint176 atomic merchant-context bootstrap unchanged and wrapped it in a POS-ready outer transaction.
- Added exact outlet/device access for the bootstrapped principal.
- Added separate role `merchant-initial-pos-operator` instead of widening the protected control role.
- Granted only catalog preparation, inventory baseline, shift open, opening cash, and sale completion permissions.
- Assigned the operational role only at the exact bootstrapped device scope.
- Sale void, refund, Final Shift Close, and broader role scopes remain denied by default.
- Outer transaction regression proves rollback of context, credential, access, role, policy, and journal state when downstream POS authorization fails.
- Final engineering exact head `94d1f2937a3ab71803738c2a2408170e63b6fcf1` completed 61/61 surfaced PR-triggered workflow runs successfully.
- Engineering envelope: 4 paths; SHA-256 `33206447002d40b489742fdb7b0c50670705400d16c184e352aa64aeb1534feb`.
- Engineering PR #785 squash merged at `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`.
- Canonical reconciliation envelope: 6 paths; SHA-256 `72d21048381af6505f8b6315141efef93f909e407d54377e3726d49f0c38ccff`.
- Operational NO-GO remains unchanged.
- Next position: Sprint180 bounded discovery from canonical post-Sprint179.

## Recent material progression

- **Sprint179:** atomic initial POS-ready merchant authorization; engineering squash `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`.
- **Sprint178:** guarded merchant first-party application entry; engineering squash `8992c2ed1b6278d113e24e38a847bedeac345161`.
- **Sprint177:** guarded merchant-context bootstrap delivery; engineering squash `6752af1eb957993a6080206d9a40f7163bd24be6`.
- **Sprint176:** atomic merchant-context bootstrap foundation; engineering squash `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`.
- **Sprint169–Sprint175:** secure installation and governed release readiness.
- **Sprint162–Sprint168:** guarded POS operations and performance/accountability foundations.
- **Sprint88–Sprint155:** Final Shift Close source/readiness chain; operational execution did not occur.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

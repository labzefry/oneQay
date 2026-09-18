# Changelog

## 2026-09-18 — Sprint180 closed canonically

**Sprint180: Merchant Initial Context Assisted Sign-In Foundation**

- Objective: `MERCHANT_INITIAL_CONTEXT_ASSISTED_SIGN_IN_FOUNDATION`.
- Removed manual tenant, identity, organization, outlet, and device ID entry from the initial merchant sign-in UI.
- Reused the exact existing installation grant to supply those context fields server-side.
- `provisioning_id` is never serialized into merchant-entry HTML.
- Existing first-party login controller, login route, MFA flow, and `/pos` transition remain authoritative and unchanged.
- Merchant entry stays Local/Test/CI + persistence + session-control gated and additionally requires a complete exact installation context.
- Missing or malformed assisted context fails closed to Foundation posture.
- Sprint178 and Sprint179 preservation remained successful.
- Final engineering exact head `e4844d47cc99c8f655c09e358b224f45c29513e1` completed 62/62 surfaced PR-triggered workflow runs successfully.
- Engineering envelope: 4 paths; SHA-256 `2c122014511daaeb8ca1d5cbd2ee4bb184733ed1154e08c8e0000f3b758c9c6c`.
- Engineering PR #787 squash merged at `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`.
- Canonical reconciliation envelope: 6 paths; SHA-256 `eb9b36e214455c09714f9d03f034e85aec06506d43b64be314b2b95b00b4f41b`.
- Operational NO-GO remains unchanged.
- Next position: Sprint181 bounded discovery from canonical post-Sprint180.

## Recent material progression

- **Sprint180:** server-assisted initial merchant sign-in; engineering squash `8451470f2eb37b45df53ac0d6f30e73c1e9cb5ad`.
- **Sprint179:** atomic initial POS-ready merchant authorization; engineering squash `fb0a886ac7f1447fa26f3eefcc808158d4ef044d`.
- **Sprint178:** guarded merchant first-party application entry; engineering squash `8992c2ed1b6278d113e24e38a847bedeac345161`.
- **Sprint177:** guarded merchant-context bootstrap delivery; engineering squash `6752af1eb957993a6080206d9a40f7163bd24be6`.
- **Sprint176:** atomic merchant-context bootstrap foundation; engineering squash `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`.
- **Sprint169–Sprint175:** secure installation and governed release readiness.
- **Sprint162–Sprint168:** guarded POS operations and performance/accountability foundations.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

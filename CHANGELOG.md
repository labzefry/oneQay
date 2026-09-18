# Changelog

## 2026-09-18 — Sprint178 closed canonically

**Sprint178: Merchant First-Party Application Entry Foundation**

- Objective: `MERCHANT_FIRST_PARTY_APPLICATION_ENTRY_FOUNDATION`.
- Connected existing first-party login/session authority to a guarded browser entry surface.
- Reused the existing Foundation Inertia surface instead of adding a competing root route/controller.
- Merchant entry is server-gated to Local/Test/CI with persistence and session control enabled.
- Existing TOTP enrollment/challenge flow is reused without altering its security semantics.
- Successful full session authority transitions to the existing `/pos` Operations Hub.
- No public registration or implicit permission grant was introduced.
- Final engineering exact head `ee0e2e8acc5238fa0cb2e3be56b6e58cf2092239` completed 60/60 surfaced PR-triggered workflow runs successfully.
- Engineering envelope: 4 paths; SHA-256 `9d27ecd0802230d3484aa7ca424064313595e8174172eb889c2736250e2815c5`.
- Engineering PR #783 squash merged at `8992c2ed1b6278d113e24e38a847bedeac345161`.
- Canonical reconciliation envelope: 6 paths; SHA-256 `d994709453d1415d23d2bdfc8ecade257d8baa8b07b9aff98654a2b4cb6bb0f5`.
- Operational NO-GO remains unchanged.
- Next position: Sprint179 bounded discovery from canonical post-Sprint178.

## Recent material progression

- **Sprint178:** guarded merchant first-party application entry; engineering squash `8992c2ed1b6278d113e24e38a847bedeac345161`.
- **Sprint177:** guarded merchant-context bootstrap delivery; engineering squash `6752af1eb957993a6080206d9a40f7163bd24be6`.
- **Sprint176:** atomic merchant-context bootstrap foundation; engineering squash `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`.
- **Sprint169–Sprint175:** secure installation and governed release readiness.
- **Sprint162–Sprint168:** guarded POS operations and performance/accountability foundations.
- **Sprint88–Sprint155:** Final Shift Close source/readiness chain; operational execution did not occur.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

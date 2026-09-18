# Changelog

## 2026-09-18 — Sprint177 engineering closed; canonical reconciliation in progress

**Sprint177: Merchant Context Guarded Bootstrap Delivery Foundation**

- Objective: `MERCHANT_CONTEXT_GUARDED_BOOTSTRAP_DELIVERY_FOUNDATION`.
- Added a guarded, auto-discovered console delivery surface for the Sprint176 atomic merchant-context bootstrap.
- Exact merchant tuple comes only from separately configured preauthorization material; command arguments cannot mint or alter it.
- Merchant bootstrap, first-control credential bootstrap, and persistence are independently default-deny and must be explicitly armed.
- Runtime remains Local/Test/CI only; Production-like runtime fails closed.
- Hidden password confirmation, generic sanitized failures, no tuple/secret output, and replay denial are regression-proven.
- No HTTP route, UI onboarding, real merchant provisioning, deployment, migration execution, Technical Preview/Production authorization, updater activation, durable-target selection, or producer dispatch was introduced.
- Exact engineering head `5a1b790414e2616ad6337224dc06392ee1154ec2` completed 59/59 surfaced PR-triggered workflow runs successfully.
- Engineering envelope: 4 paths; SHA-256 `de509f025c78e8f2ed7d0335b81b6f423deb621c3f1d6bc54bba4a312635d872`.
- Engineering PR #781 squash merged at `6752af1eb957993a6080206d9a40f7163bd24be6`.
- Canonical reconciliation envelope: 6 paths; SHA-256 `cf8df3335c6b40d148a86b3b9ad7a565400b727a88c62b26869f8a4a5481e78c`.
- Operational NO-GO remains unchanged.
- Next position after reconciliation: Sprint178 bounded discovery from canonical post-Sprint177.

## Recent material progression

- **Sprint176:** atomic merchant-context bootstrap foundation; engineering squash `af2ed4db8e49c4a75f1e1b743986cc20f3e3b0ff`.
- **Sprint175:** governed host/platform requirements readiness; engineering squash `6357d883fe04ec0515f9d21c6adc0ee907787cde`.
- **Sprint169–Sprint174:** secure installation and governed release readiness.
- **Sprint162–Sprint168:** guarded POS operations and performance/accountability foundations.
- **Sprint88–Sprint155:** Final Shift Close source/readiness chain; operational execution did not occur.

Current project-state authority: [`PROJECT_MANIFEST.md`](PROJECT_MANIFEST.md).

Author by Lab | zefry

# Changelog

## 2026-09-19 — Sprint195 closed canonically

**Sprint195: Technical Preview Activation Authority Readiness**

- Added exact-bound qualification for separately provisioned Synthetic Technical Preview activation authority.
- Authority is bound to release ID, activation request, active runtime environment SHA-256, installation-completion SHA-256, and authority lifetime.
- Added one-time out-of-band approval-token qualification with maximum 900-second authority lifetime.
- Added private 0600 durable execution-readiness evidence with atomic write and exact replay idempotency.
- Readiness state is `TECHNICAL_PREVIEW_ACTIVATION_EXECUTION_READY_NOT_EXECUTED`.
- Preserved mandatory target-environment preflight: HTTPS, single instance, private file-session storage, runtime-envelope revalidation, Preview off-switch, post-activation health, rollback/recovery, synthetic-only operation, and no schema migration requirement.
- Installer adds guarded `qualify_technical_preview_activation_authority` with exact confirmation `QUALIFY_TECHNICAL_PREVIEW_ACTIVATION`.
- Operator surface reports `QUALIFIED / READY / NOT ACTIVATED`.
- Governed M7.5 packages authority/readiness source and schemas without creating operational authority at build time.
- Final engineering head `11ec883590d05d28aeeac08a0286a3e149db00e7`: 83/83 SUCCESS.
- Engineering PR #821 squash merged at `022b1667ce25a9f4b86a71c95b2c59ad37793d4e`.
- Dedicated Sprint195 run `35425205497`: SUCCESS.
- Exact-head M7.5 run `35425205248`: SUCCESS.
- M7.1 run `35425205249`, Governance `35425205459`, PHP Foundation `35425205135`, cPanel `35425205076`, and shared-runtime `35425205240`: SUCCESS.
- Engineering path hash: `b6b06d99b300fa67cc6341f098eff73c430b11c6d98444ebb75a352d3e7589c2`.
- Reconciliation path hash: `896f53a875a1356548262e9d0c8a994769f8a845d65f551049b9708f11811bb3`.
- Operational NO-GO unchanged.
- Next position: Sprint196 bounded discovery.

Author by Lab | zefry

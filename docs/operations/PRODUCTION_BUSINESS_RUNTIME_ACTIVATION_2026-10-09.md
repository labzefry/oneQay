# Production Business Runtime — 2026-10-09

Author by Lab | zefry.

## Completed host context (operator-reported, not Production proof)
oneqaydev.n07.my.id is an isolated testing hostname, public root /home/pekd7254/public_html/oneqaydev.n07.my.id; private runtime /home/pekd7254/oneqay-production-test. Host preflight, private configuration, and reconciliation of 27 core plus one POS module migration were reported successful. No database password, APP_KEY or customer information is committed.

Qualified durable staging oneqay.n07.my.id remains on a5e833bce4baa22c21a144c558a0ca123f1d0b1e. Its traffic and filesystem must not be changed by this PR. Final Shift Close staging authority, migration #27 and updater are unchanged.

## Native business runtime work
ProductionBusinessRuntimeGate preserves Local/Test/CI behavior, rejects all other runtimes by default, and allows native Production only with (1) a newly certified same-source release whose business_runtime_activation_ready is true, (2) private exact source/artifact/environment binding, (3) persistent session and durable DB flags, (4) explicit separate operator business-traffic authorization switches, and (5) APP_ENV production, APP_DEBUG false. No compatibility projection to CI, no privileged bootstrap broadening.

This is only an isolated policy foundation, not active source enablement, Production release certification or permission to transact. Current published production-a5e833bce4ba explicitly has business_runtime_activation_ready false, so remains denied regardless of .env changes.

## Next verifiable gates
- Review CI and tenant/device authorization invariants; complete controlled Final Shift Close and live operational capabilities.
- Qualify real-like disposable transactional tests: login/MFA, merchant context, catalog, shift open, cash sale, stock decrement, idempotent receipt, shift close, reporting, backup and recovery.
- Publish a NEW Production release with business readiness true only after tests and application source requalification; never relabel prior a5e833.
- Govern isolated oneqaydev dark deployment through fixed-public cPanel no SSH, external exact-target time-bound deployment authority and rollback; separate explicit approval for real business traffic and final main-domain cutover.

Operational Production business traffic remains NOT_AUTHORIZED. Do not run earlier host Cron again.

## CI reconciliation 2026-10-09

Initial PR #933 source-wide edits touched 67 existing application/view files and caused 88 historical CI failures. Failures included durable access boundary regression and exact source path governance. Rather than weaken the tests or pretend acceptance, all 67 modified app/view files were restored byte-for-byte from canonical main while retaining the new tested policy, dedicated test, workflow and checkpoint. The new policy is NOT wired into live HTTP routes. A future narrowly scoped provider/controller integration must preserve old contract tests or deliberately evolve them with functional proof and formal review. No host action needed; dark-only application RC unchanged.

## Historical path governance and operator-only packaging

Two cPanel regression jobs for Sprint212/214 explicitly reject any modified application source other than tests in an unrelated PR. To preserve those checks, the fail-closed business admission policy is now a standalone NOT-INSTALLED operator foundation at `tools/production/ProductionBusinessRuntimeGate.php`; it is **not** Laravel-autoloaded and cannot authorize existing routes. The previously added application class is deleted. A subsequent properly governed native Production application change must coordinate acceptance tests, durable transaction proving and release metadata certification. This PR remains production-traffic inactive.

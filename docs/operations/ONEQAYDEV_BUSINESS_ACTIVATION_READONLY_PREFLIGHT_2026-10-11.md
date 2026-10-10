# oneQayDev — Business Traffic Activation Engineering: Read-only Host Gate

**Author by Lab | zefry** | 2026-10-11 | Engineering-only, no host authority.

## Status / provenance
- Source-certified business POS application: \`409ac6b2bdb80d31fbfb9425ddbb413a278b97e6\`, release \`production-409ac6b2bdb8\`; GitHub Actions \`38066218900\` SUCCESS. The subsequent documentation-only main \`273dc063e6f65b33d83e01937cb2300f4ad1ce00\` is not the archive source.
- User-provided cPanel \`upgrade-result.json\` screenshot on 2026-10-11 reported \`HEALTH_READY_BUSINESS_OFF\`, 6,252 integrity-checked files, placeholder preserved, migrations/staging untouched and activation NOT_AUTHORIZED.
- **OPERATOR-REPORTED:** first merchant provisioning command printed \`ONEQAYDEV_FIRST_MERCHANT_APPLIED\`, private credential file state \`APPLIED\`. GitHub cannot independently attest that host/database fact.
- No evidence of actual on-host CASH sale, receipts, idempotency replay, session / MFA login, inventory, Final Shift Close, or user-authorized business traffic.
- Existing staging \`https://oneqay.n07.my.id\` and \`/home/pekd7254/oneqay-staging\` strictly out of scope.
- GitHub repository metadata returned public visibility in this session, despite historical "private" handoffs; do not commit secrets. 

## This delivery
\`tools/production/cpanel/inspect-oneqaydev-business-activation.php\` is **single-file CLI-only, exact-domain/source/release-bound**. It does not contain any code to enable traffic or write runtime configuration, grant permissions, issue operational authority, touch staging, change migrations, or provision another merchant.

It checks:
1. Exact isolated cPanel paths and certificate source, immutable \`RELEASE.json\`, original health-only public bridge SHA, upgrade result and retained placeholder.
2. Existing owner-only first merchant credential \`APPLIED\` and nonblank scoped identifiers without printing credential, password, secrets or IDs.
3. Release-specific 0600 runtime environment, original app \`.env\` binding, Production APP settings and exact source/archive identity; both transaction and business traffic flags must remain OFF.
4. HTTPS \`/health/live\` + \`/health/ready\` success **and** \`/\` returning 503 from the original health-only bridge. This is a **pre-activation** check, so open business routes are a failure.
5. Database read-only \`SELECT 1\`, table count at least 43, and count of core Laravel migration receipts at least 27. No DDL/DML or schema/credential mutations.
6. Privileged TOTP, step-up, durable session control, core CASH POS flags and secure session cookie. If any are missing, returns \`PREACTIVATION_BLOCKED\`; do NOT flip flags manually during this inspection.

The PHP tool produces only sanitized stdout:
- \`PREACTIVATION_HOST_INSPECTED\` (read-only facts satisfied; **NOT** business activation authority).
- \`PREACTIVATION_BLOCKED\` (missing required feature/session readiness).
- \`STOPPED_FAIL_CLOSED\` (exact code, no private data).
Even \`PREACTIVATION_HOST_INSPECTED\` does **not** authorize changing \`.env\` or public index, and does not mean real CASH acceptance.

## Host handoff: only after source PR passes required CI and merges
The source script is **not** part of the already published \`409ac6b2bdb8\` app archive. It must be independently fetched from the merged repository commit and identity-checked, then uploaded by cPanel File Manager to a **new private directory** under:
\`/home/pekd7254/oneqay-production-test/oneqaydev-business-preflight/\`

Use one temporary cPanel Cron with PHP CLI; remove Cron immediately after a single execution. Example command:

\`\`\`bash
/usr/local/bin/php /home/pekd7254/oneqay-production-test/oneqaydev-business-preflight/inspect-oneqaydev-business-activation.php inspect > /home/pekd7254/oneqay-production-test/oneqaydev-business-preflight/preflight-result.json 2>&1
\`\`\`

**The command must NOT be run yet** until the script is published and supplied/verified. The output file remains outside public_html and contains only non-secret summary. Never upload \`private-bindings.json\`, \`production-runtime*.env\`, \`credential.json\`, token, TOTP material or sensitive logs.

## Still required: SEPARATE governed business activation
Not implemented or authorized by this preflight PR:
1. Independently bound **short-lived Product Owner host business-traffic authorization**, exact host / release / certified source / private target / expected dark-bridge hash / rollback snapshot and expiry.
2. Review feature flags, cookie policy, session driver and DB-backed session controls, keep privileged MFA enabled, prepare candidate configuration **without** changing currently bound \`.env\`; authenticate private authority on the exact host.
3. One controlled public presentation + runtime activation with staging untouched, atomic switch / explicit failure rollback; verify after switch that ProductionBusinessRuntimeGate is true for certified source, unauthorized routes/users denied and only CASH tender accepted. Never edit \`RELEASE.json\` or install artificial CI/Preview flags.
4. Verified first merchant sign-in and TOTP where privileged, tenant/org/outlet/device scope, actual CASH sale, durable receipt and replay protection, stock decrement, closing cash, Final Shift Close, audit/report and rollback recovery. No test-only evidence substituted for host proof.
5. Optional final cutover to occupied staging domain requires fresh, separate authority. Updater remains INACTIVE.

**No Production / cash traffic activation is permitted through this source PR, its CI, or its documentation.**

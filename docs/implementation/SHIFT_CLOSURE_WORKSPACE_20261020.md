# Shift closure workspace — 20 October 2026

Author by Lab | zefry

Legacy owners with access administration can now see the close workspace even
when their historical account lacks cash-recording or final-close permissions.
This read does not assign a role. The protected Business access form offers the
cashier role explicitly; saving still requires the existing step-up verification.
A separate, initially unassigned shift-supervisor template supports review duties.

Enabled cashier accounts may sign in at `/business-login` with their existing
email and password. Canonical credential, MFA, identity eligibility, session and
RBAC checks still apply. A disabled cashier or suspended managed business is
rejected. Cashier login does not grant business administration.

## Closing an existing web shift

1. Open **Tutup kasir** from the POS overview.
2. An authorized person counts the actual cash and saves **Kas akhir**.
3. A different authorized person signs in and approves closure.
4. Any variance must pass the existing explanation and independent review flow.
5. After closure, sign in on Windows POS and open the next shift normally.

Opening cash is never guessed as counted closing cash. Retrying an uncertain
request retains its operation ID; canonical idempotency prevents duplicate
closure evidence. Actual host cash counts and financial approval remain user
operations. No production shift was closed by source development or tests.

## Validation and deployment boundary

Disposable SQLite and MySQL fixtures cover missing owner permissions, readonly
visibility, independent closure, denial of self approval, disabled accounts,
suspended businesses, replay, and the resulting Windows login snapshot.
TypeScript checking and the production UI build remain release requirements.
The successor operator must preserve all 35 migration receipts and 84 tables,
existing identities, roles, sessions, SMTP, and financial records.
It may enable only the named reconciliation workspace flag; permissions remain
unchanged. Source certification is distinct from actual hosting acceptance.

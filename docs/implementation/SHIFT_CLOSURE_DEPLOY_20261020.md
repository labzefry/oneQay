# Shift closure deployment — 20 October 2026

Author by Lab | zefry

The successor replaces certified production `c52bb6095ed0` only on
`oneqaydev.n07.my.id`, binding the predecessor runtime, public bridge, rewrite
rules, 6384 source-file inventory, and immutable deployment receipt.
The code-only verifier preserves 35 migration receipts and 84 tables.
Accounts, roles, credentials, sessions, SMTP and cash transactions are preserved.

The only added runtime setting is
`ONEQAY_POS_CASH_VARIANCE_RECONCILIATION_ENABLED=true`, declared in the hashed
request and native grant. It exposes existing permission-checked reconciliation
commands. Independent explanation, review and final approval remain mandatory.
Any other configuration delta is rejected. No role is automatically assigned.

Verification checks closure, counted-cash and reconciliation routes, service
resolution, native production gating, HTTPS assets and anonymous denial.
`BUSINESS_WORKSPACE_UPDATED_ACCEPTANCE_PENDING` does not claim user acceptance.
Cash counts and actual financial approval remain user operations.

Verified private ZIP retention and the shared retention lock remain active.
Replace the predecessor worker only after publication succeeds. Remove the
one-time update cron after success. Create a fresh native grant only when the
immutable packet is uploaded and ready; an expired grant can be renewed.

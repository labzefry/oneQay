# Control plane operations

The platform owner is bound to the private host configuration. A business administrator never acquires platform authority through a role assignment. Owner writes use the existing session, CSRF, MFA and five-minute step-up boundary, optimistic versions, idempotency keys and audit notes.

## Accounts and permissions

Account approval/invitation, suspension and resumption retain the canonical account lifecycle. Account detail supports feature restrictions, a descriptive plan label, internal notes and session revocation. Plan labels do not implement billing. Enabling a feature never grants a permission. New activation explicitly provisions the operational owner and cashier preset; staff assignments still require the business administrator's policy action. The owner tenant cannot be a cross-business recovery target.

TOTP reset requires verified requester identity. It rotates the encrypted pending secret, increments the factor epoch, revokes old sessions and both recovery-code families, preserves the password and queues a transactional notification. The next sign-in requires new enrollment. No TOTP secrets are returned to the platform owner.

## NOC and operations cron

Run `oneqay:control:work --limit=20` through the certified release's PHP CLI entry once per minute, replacing the previous mail-only worker. It samples the actual SQL connection and application filesystem, then processes bounded campaign and SMTP work under a local lock. The deployment package must provide the exact private entry and cron path; do not guess paths or use an expired grant.

Charts aggregate the previous 24 hours; p95 is a histogram upper bound. A missing/stale sample is not healthy evidence. Measurements describe the application process/filesystem, not host-wide CPU, shared-hosting quotas or provider infrastructure. HTTP incident records contain safe codes and correlation identifiers, never SQL, request bodies, IP addresses or credentials. Recurring incidents reopen. Metrics retain seven days; resolved errors retain thirty days.

## Communications

In-app announcements can target all active registered businesses. Marketing email requires explicit subscription and respects SMTP suppression. Draft review binds the actual audience and content digest; changes require another review. Publication can be scheduled within thirty days. Fan-out rechecks active status, recipient identity and subscription. Cancellation stops queued work; previously sent or in-flight messages cannot be recalled. Opt-out affects marketing, not transactional security messages. SMTP acceptance is distinct from delivery and inbox placement; provider feedback is required for bounce/delivery confirmation.

## Locale and identity

A saved manual language choice wins. A country header is accepted only from a direct Cloudflare network peer. ID selects Indonesian; other recognized countries select English. Without a trustworthy country hint, browser language and Indonesian timezone provide a fallback. These hints choose presentation only and never authorize access. Cloudflare IP ranges are bundled and require periodic maintenance if provider ranges change.

Visible UI and PDF/email footer attribution is copyright oneQay. Developer attribution remains in source comments. Lucide icons from the Iconify collection are bundled to keep navigation usable under the existing CSP without a runtime network dependency.

## Pilot acceptance

The initial hosted business has a separate owner-only pilot action bound to its private bootstrap tenant, outlet and device. It does not create a registration record. The pilot reads linked JRN_004 catalog, JRN_005 opening, JRN_006 positive counted-cash sale and JRN_010 shift-close evidence for the same tenant/shift. It remains NOT_YET_VERIFIED until evidence is complete. A production-only owner attestation additionally binds its digest, exact source and receipt reference. Synthetic test fixtures cannot attest real money or establish host acceptance.

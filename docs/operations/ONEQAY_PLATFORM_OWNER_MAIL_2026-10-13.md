# Platform owner, business activation and transactional mail

Author by Lab | zefry

This successor implements the owner's 2026-10-13 instruction on source baseline `894750c6a59a4ac28f42d5da8d8eb426280a412b`. It does not claim the Enterprise Vision's remaining payroll, tax, AI, marketplace, external payments or DBME capabilities are complete. Host deployment remains separately evidenced.

## Product behavior

- A private host binding identifies the platform owner's existing tenant and identity. A merchant's administrator role never implies platform ownership.
- `/platform` lists managed businesses and operation history, SMTP settings, persistent mail states and cron heartbeat. Indonesian is default; English is selectable.
- Owner writes require current canonical session, MFA and a fresh five-minute policy-administration step-up. Business registration is pending until the owner approves an invitation.
- Invitation approval and the encrypted email outbox commit together. A 256-bit invitation token expires in 24 hours. The recipient sets their own initial password. Canonical context graph, initial protected administrator, credential hash, organizational grants and operational role contracts provision the actual account atomically.
- Activation issues no session. Canonical login and privileged MFA remain mandatory. A five-minute receipt recovery accepts the original token plus the correct password, returns only login identifiers, and never changes the password or repeats provisioning.
- Suspension and resumption revoke tenant sessions. Managed-tenant eligibility decorates the existing identity verifier used by fresh login, active requests and pending MFA. Pre-existing unmanaged tenants retain their canonical eligibility.
- Reasons, version checks, stable operation IDs and immutable operation outcomes protect owner state changes. No public tenant signup or merchant self-approval is introduced.

## Mail behavior

SMTP settings and outbox payloads use the existing Laravel application encryption key. The secret is never returned to the browser, and the report omits recipient addresses/body/token/SMTP transcript. TLS certificate/name verification is mandatory on 465 or STARTTLS 587; connections pin a validated public DNS address. There is no relay fallback or reputation-evasion mechanism.

A database claim and random lease prevent competing cron workers from taking the same message. A definitive temporary SMTP reply gets bounded exponential backoff with jitter (five attempts), while definitive permanent replies fail. A worker crash or ambiguous acceptance becomes `unknown`; automatic resend is forbidden. Owner retry of unknown acceptance requires an explicit duplicate-risk acknowledgement. Provider-deferred messages remain owned by the provider and are not resent locally. Three consecutive transport failures pause new claims for five minutes. A configurable 1–60 message/minute global cap bounds the sender's load.

SMTP acceptance is not delivery. Authenticated feedback may record delivery, hard bounce, complaint, or provider deferral. Event IDs are idempotent and conflicting reuse is rejected. Recipient/message binding prevents cross-message updates; late success does not erase a complaint or hard bounce. Hard-bounced/complaining recipients are suppressed across this platform sender. Generic SMTP 5xx does not suppress recipients; only explicit 5.1.1 does so synchronously.

DNS inspection reports the **presence** of SPF, DKIM selector and DMARC records. It does not validate actual SPF sender authorization, DKIM signatures/alignment, PTR, reputation, or guarantee inbox placement. Those need the provider/domain configuration and a real received-message inspection.

## Private host configuration

Apply the new additive module migration once, before enabling the module. Preserve all historical migrations and all existing application keys, merchant IDs, credentials and sessions. This is not a presentation-only update and must not use the older bilingual updater.

Set privately, using the existing verified owner IDs:

```dotenv
ONEQAY_PLATFORM_ENABLED=true
ONEQAY_PLATFORM_OWNER_TENANT=<existing platform owner's tenant ID>
ONEQAY_PLATFORM_OWNER_IDENTITY=<existing platform owner's identity ID>
ONEQAY_MAIL_FEEDBACK_SECRET=<at least 32 random secret bytes, kept private>
ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED=true
ONEQAY_PRIVILEGED_STEP_UP_ENABLED=true
APP_URL=https://<actual application hostname>
```

The existing release certification, persistence, secure-session and production-business runtime checks still apply. No anonymous or automatic owner grant exists. Changing the owner binding is a hosting administration operation. Do not enter private passwords into source control or chat.

After deployment, set SMTP in `/platform`, provision SPF/DKIM/DMARC at the actual DNS authority, confirm the provider signs outgoing mail, and configure a feedback adapter. The module is transactional-only: no marketing campaigns or unsolicited bulk sender UI.

cPanel cron schedule: once per minute. Substitute the **verified active release path**; do not paste an invented release name:

```text
/usr/local/bin/php /home/pekd7254/oneqay-production-test/releases/<active-release>/apps/web/artisan oneqay:mail:work --limit=20 >> /home/pekd7254/oneqay-production-test/shared/mail-worker.log 2>&1
```

Logs contain only safe worker totals/failure codes. Configure log rotation. The dashboard reports a stale heartbeat after three minutes, queued age, retry/failure/unknown status, suppression totals and recent feedback.

## Feedback adapter protocol

An authenticated provider integration or trusted bounce-mail processor sends `POST /platform/mail/feedback` with JSON:

```json
{"event_id":"provider-stable-unique-event","message_id":"32-character-oneqay-message-id","recipient":"synthetic@example.test","type":"hard_bounce"}
```

`X-OneQay-Timestamp`: current Unix seconds (five-minute tolerance).
`X-OneQay-Signature`: lowercase hex `HMAC-SHA256(timestamp + "." + exact_raw_body, ONEQAY_MAIL_FEEDBACK_SECRET)`.

The mail carries `X-OneQay-Message-ID` and a deterministic RFC Message-ID. The integration must verify its provider's native signature or trust the authenticated mailbox/MTA source before converting feedback. **Plain inbound emails and arbitrary From headers are not authentication.** This generic adapter endpoint is not a claim that SES, Mailgun, Postmark, cPanel DSN collection, or any provider account is already connected. Native provider configuration remains an operator setup dependency.

## Qualification and limitations

The dedicated workflow runs disposable SQLite and MySQL activation/credential/isolation/outbox/retry/bounce tests, existing credential/role/step-up regressions, TypeScript checking and a production UI build. All recipients are synthetic; CI never sends external mail.

At initial source preparation, native CI, host migration, cron installation, SMTP credentials, provider feedback connection, DNS alignment, received-message authentication and authenticated production acceptance are pending. Do not label these performed without evidence.

Current release download remains a source-certified application artifact, not a database upgrade installer. An additive backend/schema successor operator and a current host binding report are still needed for this cPanel target.

## First native qualification

Initial PR #952 head `643fcb68b629a678a66064fe141fa67b8d426ffa`, run `38329177033`, job `115074750142`: **PASS** — 50 SQLite assertions, 50 MySQL assertions, existing atomic merchant bootstrap, 20 business-role assertions, existing privileged step-up regression, TypeScript and production Vite build. Subsequent login, compiled browser, HTTP feedback and session-revocation additions require their successor-head results; this initial result must not be projected onto later edits.

The registered business owner uses `/business-login` with their email/password. If an email owns multiple businesses, the login form accepts the exact business ID as a discriminator. Canonical TOTP enrollment/challenge endpoints manage the factor; session/CSRF rotation reloads the sign-in page. The platform owner link appears in the existing workspace only for the privately bound owner, and backend authorization remains mandatory.

Historical source-shape guards assumed migrations #1–#8 or old PR path sets. The exact-base platform compatibility helper verifies that historical migrations, existing behavioral regression steps, dependencies, source certification, runtime gates and operational state are unchanged. It permits only the explicitly listed module paths, three bounded provisioning-scope extensions and additive owner navigation. Existing executable tests remain enabled; no compatibility flag mislabels this work as presentation-only.

## Native cPanel bounce-mail path

The optional `oneqay:mail:bounce` command reads one RFC3464 DSN from standard input. It never sends mail, prints message bodies or deletes inbox contents. Valid reports carry an exact per-message return address with a 96-bit HMAC capability, and the stored message's recipient must match. Arbitrary From headers are not trusted. Reports larger than 256 KiB, duplicate/ambiguous critical fields, unsupported encodings/actions and invalid tokens are rejected without creating a bounce loop. Storage failure exits with EX_TEMPFAIL 75 so the MTA can retry.

Set `ONEQAY_MAIL_BOUNCE_ADDRESS=bounce@<same sender domain>` privately, with the same feedback secret. The mailbox local part must be 1–6 lowercase alphanumeric characters. Outgoing mail then uses a valid plus-addressed return path (at most 64 local-part bytes). **Before enabling this setting**, create the bounce mailbox and use cPanel Email Filters / Global Email Filters to pipe matching bounce-address reports to the deployed command, using the hosting PHP binary and verified active-release path. Test with an actual provider-generated DSN; providers that rewrite the envelope sender require their authenticated feedback adapter instead. No filter or mailbox has been installed automatically.

Illustrative pipe target (replace active-release only after deployment):

```text
/usr/local/bin/php /home/pekd7254/oneqay-production-test/releases/<active-release>/apps/web/artisan oneqay:mail:bounce
```

A hard invalid-recipient DSN suppresses the address. Definitive failed mailbox-full 5.2.2 / temporary 4.x DSNs schedule a bounded retry. `delayed` remains provider-owned and does not duplicate its delivery attempt. Other permanent failures remain failed without misclassifying the recipient as invalid. Complaint feedback uses the signed provider endpoint.

Primary implementation references: RFC3464 (https://www.rfc-editor.org/rfc/rfc3464), cPanel email filters (https://docs.cpanel.net/knowledge-base/email/how-to-configure-email-filters/), cPanel plus addressing (https://docs.cpanel.net/cpanel/email/manage-email-accounts/), Gmail sender guidelines (https://support.google.com/mail/answer/81126). DNS/authentication and provider reputation affect delivery; no inbox guarantee is offered.

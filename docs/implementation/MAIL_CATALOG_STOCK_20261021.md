# Email delivery reports, category creation and explicit stock modes

Author by Lab | zefry. Requested 21 October 2026. This is **stage 1: repository publication only**. Hosting deployment, production SMTP sends and Windows update promotion are deferred to the next user-directed stage.

## Problem and behavior

The previous control worker invoked NOC sampling and campaign preparation before SMTP in a shared try block. A failure could skip transactional mail entirely. The SMTP test showed a queued acknowledgment but did not refresh its delivery report in the same screen. A disabled transport returned no work without an actionable dashboard state. Payload decryption errors rolled back the claim, potentially poisoning subsequent invocations. These are source-proven failure paths; they do not prove the exact current provider/credential/network failure on hosting.

Mail now runs independently from health and campaigns. Each phase returns a safe status, each claim records an attempt event, and malformed payloads become visible failures. TLS negotiation/authentication runs before DATA, so pre-submission connection errors can retry safely; a lost acknowledgment after DATA remains unknown and requires reviewed retry. SMTP 535, connection/DNS and unreadable-credential codes have readable guidance. SMTP acceptance is never mislabeled inbox delivery. Credentials/transcripts/body/recipient are not returned in reports.

The owner-only report endpoint refreshes the SMTP monitor and full history every ten seconds while visible, without replacing unsaved configuration. Test IDs link to actual queue status. The existing step-up, CSRF, throttle, owner boundary, suppression and retry controls remain in force.

## Three-day email log retention

The normal minute worker automatically removes delivery events older than 72 hours, at most 1,000 per run, and removes encrypted terminal-message recipient/subject/body content in batches of 500. Reports show the last 72 hours plus active queues. Minimal opaque IDs, fingerprints and deduplication tombstones remain to prevent a retried old request from sending twice. Suppression hashes remain to prevent renewed delivery to bounced/complaining recipients. Active queued/retry/sending messages are protected. Old purged messages cannot be manually resent; create a fresh, valid business request instead. This retention is for SMTP delivery logs, not financial journals or security/business audit records.

## Product categories

A scoped category directory permits creating categories before adding products. Existing names on product profiles remain available without rewriting products. Creation uses current catalog permission, normalized/case-folded names, scoped uniqueness and the existing audited/idempotent business mutation path. Category dialogs, stock selectors, tabs and secondary actions have explicit dark text/light backgrounds and focus states.

## Stock policy and financial integrity

`tracked` is the default for all existing products and old client requests. `unlimited` explicitly skips stock reduction; it does not create a large fictitious quantity. Physical counters and opening baselines remain intact. Switching existing policy also requires inventory permission. Omitting mode on an edit preserves current policy. Unlimited imports require opening quantity zero; the updated Excel template adds optional `stock_mode`, and old templates remain tracked.

Canonical sales record `stock_deducted` per line. Void uses the original sale effect even if the product policy later changes. Inventory reconciliation excludes unlimited sale/void quantities. Replenishment rejects unlimited products. Web cashier, desktop snapshots/leases, backend sync, native SQLite reservation and desktop UI honor policy. A policy change invalidates stale desktop catalog authority and produces a review conflict rather than silently accepting changed stock semantics. PDF labels unlimited; Excel includes mode while preserving the physical numeric counter. Money and quantities remain integer/decimal validated.

Desktop source version is 0.2.1. Existing 0.2.0 installers do not gain unlimited UI merely by updating hosting. Build/qualify the new Windows artifact and distribute it in stage 2. No signed updater manifest is promoted by this work.

## Additive internal HTTP contracts

- `POST /pos/business/products/categories`: existing `{operation_id,data:{name}}` business command envelope; current catalog permission; scoped stable replay.
- Product save/import accepts optional `stock_mode` (`tracked` or `unlimited`); missing mode defaults to tracked for creation and preserves current policy on edit.
- `GET /platform/mail/report?page=1`: existing owner-only session/context boundary, no-store response, 25 messages/page, 40 recent events, safe status/code fields, 12 reads/minute.
- Desktop snapshot/lease optionally includes `tracks_stock:false`; absent means true. Mode changes are included in the immutable catalog authority revision.

## Schema, deployment and rollback

Two new module migrations only:

- Business: `2026_10_21_000001_create_product_categories` creates one scoped directory table.
- POS: `2026_10_21_000001_add_explicit_stock_tracking` adds catalog `tracks_stock` and sale-line `stock_deducted`, both default true.

No historical migration file is edited. From the observed production baseline, expected totals are 36 → 38 migration receipts and 87 → 88 tables. Existing stock values, credentials, sessions, SMTP settings, TOTP and role assignments are not rewritten. Rehearse on disposable MySQL and take a private backup before stage 2. Never run a blanket migration or reuse the old c2e deployment operator.

An older app does not understand unlimited sale effects. After unlimited sales exist, do not roll back to an older business-on app that could restock those sales incorrectly on void. Use a forward fix or keep business traffic closed while a compatible recovery is prepared. Additive schema and financial evidence must remain intact; migration down intentionally refuses destructive cleanup.

Current hosting remains source `c2e3e3b5a05e9729a478f432c9751d4b4297bcfe`, deployed 21 October 2026 at 00:34:15 WIB, with 87 tables. Exact operational hashes and archive evidence are in PR #974 comment 6297183155. This work does not redeploy hosting or issue an expiring deployment grant.

## Validation and continuation

Dedicated SQL tests exercise worker isolation with an actual outbox; adapter pre-DATA, auth, transient and ambiguous results; three-day cutoff, active-queue protection, content removal and dedupe; category isolation/replay; canonical unlimited sale/replay/void across policy changes; existing tracked-stock rejection; and import/report compatibility. Native core tests cover offline unlimited reservations/replay and legacy snapshot defaults. Compiled UI tests cover category actions, stock mode selection, delivery-report refresh and readable controls. See actual CI evidence in the pull request before merge; source readiness is not production email or financial acceptance.


## Compatibility and dependency validation

Vite is pinned to the patched 8.1.6 release in web and desktop; the web lock also resolves follow-redirects 1.16.1. These are minimal patch fixes for the advisories surfaced by CI, not a major dependency upgrade. Primary advisory references: <https://github.com/advisories/GHSA-698v-4r3x-3vcv>, <https://github.com/advisories/GHSA-r825-w8mm-5pxw>, <https://github.com/advisories/GHSA-587h-7wmj-h5pm>, <https://github.com/advisories/GHSA-q3jh-wrc6-jgq8>. Both npm audits report zero known vulnerabilities at validation time.

Historical sprint workflows contain one-time closed-world source/migration envelopes. Their bounded successor checks apply only to this repository, branch and predecessor; the helper checks the complete changed-file set and exact content hashes, preserves all historical DDL, Composer, identity/authorization and operational-authority files, and does not disable functional regression steps. Current SQLite/MySQL contracts exercise the actual new canonical sale/void paths and full module schema. SMTP acceptance, actual inbox delivery and production financial acceptance remain separate from these synthetic checks.

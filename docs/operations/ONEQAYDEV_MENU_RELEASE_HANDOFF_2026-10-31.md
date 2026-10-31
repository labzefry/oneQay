# oneQayDev Menu Release — Consolidated Handoff (31 October 2026)

Author by Lab | zefry · integration owner PR #992 · source-stage only

## Deliverable and scope
PR #992 is the single source candidate for the F&B menu book, guest QR cart/order and cashier-confirmed POS settlement. New orders are unpaid until an authenticated business cashier settles a canonical sale; table identifiers are optional. The three additive module migrations create six new tables, preserving 108 predecessor tables and 41 old migration receipts. Existing transaction history, credentials, tenant isolation and production database are not reset. Source-qualified web tests and this document **are not evidence of live host activation**.

This integrated candidate contains the 32 path-scoped historical workflow changes previously proposed in PR #993 and the Symfony Mime v7.4.22 fix already present in PR #992 and equivalent to PR #994. Changes are consolidated without weakening the five required `main` checks; historical tests remain available manually. Do not merge the duplicate PRs merely to trigger more CI runs.

## Fast release sequence (one CI source, one cPanel operator)
1. Qualify exact PR #992 head via **Menu book catalog integration** (SQLite + MySQL, PHP/POS/permission tests, UI palette/type-check/build) and the required governance checks. A GitHub comment from the actual owner `labzefry` authorizing **that exact head** is needed to satisfy `product-owner-merge-authority`; an assistant/app comment cannot impersonate it.
2. Squash-merge PR #992 only when required checks/owner authority are genuinely satisfied. The existing `Production Business Release Certification` workflow builds a source-bound release artifact on protected `main`, NOT on a draft PR. The release contains a production-ready web build; it does not activate host traffic or migrate live databases.
3. On the cPanel host use File Manager to place `tools/production/cpanel/inspect-oneqaydev-menu-predecessor.php` at `/home/pekd7254/oneqay-production-test/inspect-oneqaydev-menu-predecessor.php`. Execute ONE read-only Cron:
   `/usr/local/bin/php /home/pekd7254/oneqay-production-test/inspect-oneqaydev-menu-predecessor.php > /home/pekd7254/oneqay-production-test/oneqaydev-menu-binding.log 2>&1`
   The script emits a private `oneqaydev-menu-predecessor-binding.json` containing only hashes and the canonical old artifact ID. No secret values, passwords or DB rows are exported.
4. Package the main-branch certified artifact with `python3 tools/production/package-oneqaydev-menu-book.py --predecessor-binding <private_binding.json> --certified-zip <certified_actions_zip> --source <exact_main_sha> --run <certification_run_id> --output <outdir>`. This produces one ZIP with release contents and `update.php`. Never substitute synthetic test ZIPs or fabricated predecessor hashes.
5. Upload/extract this output ZIP **only** inside `/home/pekd7254/oneqay-production-test` (not public_html). Follow its `PETUNJUK.txt`: `update.php preflight` must report `READY_NO_ACTIVATION`, including MySQL, GD, HTTPS wildcard/tenant route, previous release and three additive migrations. Post the package-generated *fresh exact* owner deployment authorization on PR #992. Only then run one-time `update.php update <owner_comment_id>`; verify `update-result.json`, then install the one worker Cron.
6. The operator retains verified previous release/assets and rollback evidence, never drops a table, never touches SMTP credentials, and never turns on business traffic without its host/owner gates. Keep the public production domain `oneqay.n07.my.id` untouched; target is only `oneqaydev.n07.my.id`.

## Operational limits and next releases
The native menu guest ordering flag defaults OFF until explicitly enabled for the qualified target. The Cashier POS sale-completion permission, active shift and canonical pricing remain mandatory; changing source or adding tables does not grant permission. Windows POS QR scanning, membership points ledger/redemption, discounts, marketing consent queue and an enterprise production cutover are **not included** in this bounded F&B menu release. They require separate completed implementations; do not advertise them as deployed.

The development host's actual predecessor bytes, private hash binding, certified release ZIP/run and cPanel access are external facts that cannot be inferred from repository contents. If any do not match, the operator must stop without touching merchant data.

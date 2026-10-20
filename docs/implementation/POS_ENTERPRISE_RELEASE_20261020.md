# POS enterprise release checkpoint — 20 October 2026

Author by Lab | zefry. This is source/build evidence, not a claim of installation or hardware acceptance.

## Completed application release

- PR #966 squash-merged at `5f2acf1aec6c6cc9a5cbab128c312141fe0301c4`; implementation head `223fe1a584edd29b969803e3cf881fe3462af056`.
- All five required governance/owner statuses passed. Windows, native security tests, Rust offline core, Playwright UI, SQLite/MySQL cashier lifecycle, web type-check/build and production source certification passed.
- Windows run `39287808772`, artifact `12203369740`, installer SHA-256 `9f4d5f719600ea74e42c78d84a4a339fb9014c82cc4699011e3219204a9fb5c6`.
- Main source certification run `39356620709`, artifact `12235975098`, artifact ZIP SHA-256 `aeb11266970bba88e68ee8b128bee68d3be6f70fa90c59279bb0521ed361d23d`.
- Two non-required Sprint41/42 source assertions still reject the already-existing authentication-reactivation route on newer branch names. Identity policy and routes are unchanged from the base; the executable Sprint41 regression passed locally. These historical assertions are not reported as passing.

## Hosting successor

The operator is bound to installed predecessor `45509005b2f9ed4e5ffddd1e8c7db100a50e52fe` and certified candidate `5f2acf1aec6c6cc9a5cbab128c312141fe0301c4`. It adds one module migration with three tables: 34 → 35 receipts, 81 → 84 tables. Existing credentials, permissions, SMTP settings, sessions and v1 desktop acknowledgements are preserved.

`package-oneqaydev-pos-enterprise.py` packages the actual certified artifact, verifies its source/run/checksums, rejects links and unsafe paths, creates an inventory, and writes the exact native authority request. The resulting package contains 6384 certified source files. Upload under `/home/pekd7254/oneqay-production-test`, never under the public document root.

The source helper, schema preservation, rollback, empty worker lock, HTTPS login/error behavior, TLS retries and private ZIP retention passed preparation-branch CI run `39288678009`. The final bound operator and actual certified package must pass their own PR checks before distribution.

Do not start the one-hour native deployment grant before the package is ready to execute. Keep the installed predecessor worker cron until the successor installation succeeds; then replace it using the path in the package. Retention compresses old releases/payloads privately after 120 seconds and preserves files on failure.

## Remaining operator evidence

At this checkpoint no successor hosting result has been received. Hosting remains at the last evidenced release `production-45509005b2f9`. Windows 10 hardware, printer/drawer/scanner behavior, real cash and actual email delivery require operator evidence.

The updater owner key was delivered privately as `oneQay-Windows-Updater-Owner-Key-20261019.zip`. Configure Actions secret `TAURI_SIGNING_PRIVATE_KEY` before publishing a signed versioned Windows release. The key must never be uploaded to hosting or committed. No Authenticode certificate is configured.

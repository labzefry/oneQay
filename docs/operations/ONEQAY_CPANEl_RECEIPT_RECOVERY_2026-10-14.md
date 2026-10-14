# oneQayDev receipt-baseline recovery

Author by Lab | zefry

Host evidence from 14 October 2026 lists 28 receipts: the exact 27 immutable canonical migration names and 0000_00_00_000028_create_pos_inventory_replenishment_foundation. The earlier operator incorrectly required a count of 27. Do not remove the valid #28 receipt or replay that migration.

The corrected installer accepts only the exact 27 or 28 name sets. A retained #28 requires its table, all columns with correct precision/nullability, tenant primary/operation uniqueness, product-time index and all five restrictive same-domain references. Extra/unknown/duplicate receipts, missing or unsafe schema, and existing business/platform module tables fail before the first DDL. Baseline 27 installs three exact paths; baseline 28 retains replenishment and installs two. Historical receipts, DDL and owner credentials remain preserved. No destructive down/receipt edit is offered.

## Recovery scope

The recover CLI mode applies only to the exact observed report SHA256 0a96991c38a5545268c3ed1354b7e5a74f4d215a5c1d03eae8e0ab3997e2ec4a with EXACT_HISTORICAL_RECEIPTS_REQUIRED and PUBLIC_SWITCH_NOT_PERFORMED. It requires no module-installation.json, an intact certified f5a037 candidate, its already-generated private runtime binding, no candidate public entry, unchanged e93b7f public/private source, full rollback snapshot/assets, and staging fingerprint. It archives the prior report and retains the existing candidate/runtime/feedback secret; it does not clear failure checkpoints or repeat partially applied DDL. Fresh native owner authority must bind the corrected scripts and new request digest before execution.

After qualification, replace only update.php, install-modules.php and authority-request.json inside the existing private package. Preserve payload/archive/manifests, candidate/runtime, rollback and prior guards. Execute recover with the newly issued comment ID once. A current source-certified release remains distinct from on-host authenticated business acceptance and real SMTP setup.

## Qualification

The dedicated workflow exercises both 27 and 28 baselines on disposable SQLite and MySQL, rejects unknown receipts and missing tenant uniqueness, preserves credentials and historical metadata, and retains all existing owner/security/rollback assertions. Native results and the exact qualified head must be recorded after they complete. No hosting write or migration has been performed by source preparation.

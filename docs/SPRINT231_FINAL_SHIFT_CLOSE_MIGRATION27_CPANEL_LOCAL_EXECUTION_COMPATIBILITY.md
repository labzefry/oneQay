# Sprint231 Final Shift Close Migration27 cPanel Local Execution Compatibility

Author by Lab | zefry

## Purpose

Sprint231 fixes the live execution boundary proven by two Final Shift Close migration #27 workflow attempts.

Run `35695002979` failed before database access because the protected execution Environment did not yet provide the required migration27 database secrets.

After the exact five selected-target database secrets were configured, run `35695624097` advanced further and proved the real infrastructure boundary:

`SQLSTATE[HY000] [2002] Connection refused`

The GitHub-hosted runner attempted to connect to the selected cPanel database through `127.0.0.1`. That endpoint is intentionally local to the cPanel host and is not reachable from GitHub-hosted infrastructure.

Both runs failed before `php artisan migrate` executed. Migration #27 therefore remained `NOT_EXECUTED`.

## Design

Sprint231 preserves the existing GitHub-hosted direct execution path for targets where the selected database is legitimately reachable.

For the selected cPanel shared-hosting runtime, Sprint231 adds an additive cPanel-local execution path:

1. exact operational authority remains owned by the target PR exact head;
2. selected-target database-binding evidence remains required;
3. the migration is executed from the selected cPanel release itself;
4. the local executor verifies the exact migration Git blob;
5. the local executor verifies the exact selected-runtime binding manifest;
6. the local executor verifies the trusted database binding before mutation;
7. predecessor tables and migrations are verified before mutation;
8. only migration #27 is executed;
9. schema, ledger, table emptiness, required indexes, CHECK constraint, and required columns are verified after execution;
10. a short-lived signed secret-free Base64 JSON evidence payload is produced;
11. GitHub validates exact main, target PR/head, binding run/attempt, selected runtime identity, database binding, migration blob, TTL, HMAC, and post-schema assertions before publishing execution evidence success.

Remote MySQL is not enabled and is not required.

## Canonical migration identity

Path:

`apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php`

Git blob:

`a412560c2f340f4783a385aef729dbd074389a4c`

The currently selected durable-staging runtime source:

`5be28a3c001738373588b58e9d29832c46402de1`

contains the exact same migration blob.

## cPanel local executor

Source:

`ops/final-shift-close/cpanel-migration27-local-execution.php`

The executor is CLI-only and requires explicit arguments for:

- application root;
- runtime env;
- runtime binding manifest;
- current canonical main SHA;
- target PR number;
- target exact head SHA;
- selected-target DB-binding run ID and attempt;
- expected database-binding SHA-256;
- private output path.

The executor uses the canonical oneQay runtime database namespace:

- `ONEQAY_DB_HOST`
- `ONEQAY_DB_PORT`
- `ONEQAY_DB_DATABASE`
- `ONEQAY_DB_USERNAME`
- `ONEQAY_DB_PASSWORD`

The result is signed using HMAC-SHA256 with context:

`oneqay-migration27-cpanel-local-execution-v1`

The protected GitHub Environment secret carrying the short-lived evidence is:

`ONEQAY_MIGRATION27_LOCAL_EXECUTION_B64`

TTL is at most 900 seconds.

## Exact Sprint231 initial source envelope

Sprint231 initially changes exactly five paths:

1. `.github/workflows/final-shift-close-migration27-execution.yml`
2. `.github/workflows/sprint231-final-shift-close-migration27-cpanel-local-execution-compatibility.yml`
3. `docs/SPRINT231_FINAL_SHIFT_CLOSE_MIGRATION27_CPANEL_LOCAL_EXECUTION_COMPATIBILITY.md`
4. `ops/final-shift-close/MIGRATION27_SELECTED_TARGET_BINDING_CONTRACT.json`
5. `ops/final-shift-close/cpanel-migration27-local-execution.php`

Initial sorted-newline path-set SHA-256:

`5e9b356ab7a5e068bead7e13ab9de2f2f22ce6d97f351670931eaa43b433918a`

Historical workflow compatibility may be reconciled in the same Sprint231 PR if exact-head CI proves stale executor immutability assertions. Such reconciliation must not change operational state or weaken Sprint102 authority ownership.

## Target PR consequence

The migration state-transition PR #883 currently has exact head:

`8aa998d3600994085ae521663ec97c56b909bd5e`

Its migration execution authority was explicitly granted and successfully evaluated for that exact head.

Because the execution workflow requires the target PR base SHA to equal current canonical main, Sprint231 merge will require PR #883 to be synchronized to the new main. Its exact head will therefore change.

Operational migration authority is exact-head-bound and must be explicitly re-granted for the new synchronized PR #883 head. Sprint231 does not transfer that authority automatically.

## NO-GO preserved by Sprint231 source work

`MIGRATION27_LIVE_EXECUTION = NOT_PERFORMED`

`MIGRATION27_STATE = NOT_EXECUTED`

`PERMISSION_PROVISIONING = NONE`

`FINAL_SHIFT_CLOSE = INACTIVE`

`TECHNICAL_PREVIEW = NOT_AUTHORIZED`

`PRODUCTION = NOT_AUTHORIZED`

`UPDATER = INACTIVE`

`REMOTE_MYSQL_ENABLEMENT = NOT_REQUIRED`

`TARGET_RESELECTION = NO`

Author by Lab | zefry

# Sprint226 Final Shift Close Migration27 cPanel Local DB Env Binding Correction

Author by Lab | zefry

## Purpose

Sprint226 fixes one concrete live cPanel compatibility defect discovered after Sprint224 canonical merge.

The cPanel diagnostic proved:

- PHP CLI 8.3.33 = PASS
- PDO = PASS
- pdo_mysql = PASS
- runtime .env exists, is a regular non-symlink file, and is readable
- runtime binding manifest exists, is a regular non-symlink file, and is readable
- runtime binding manifest exact field set = PASS
- runtime binding manifest schema/state/identity = PASS
- failure occurs before database access at dotenv parsing
- observed failure: DB_HOST_PRESENT=NO
- failed stage: DOTENV_PARSE
- error code: REQUIRED_DB_ENV_VALUE_MISSING_DB_HOST

The failure is not a cPanel configuration error.

## Root cause

The Sprint224 cPanel-local independent PDO probe incorrectly read generic Laravel-style keys:

- DB_HOST
- DB_PORT
- DB_DATABASE
- DB_USERNAME
- DB_PASSWORD

The canonical oneQay application database configuration has always used the dedicated namespace:

- ONEQAY_DB_HOST
- ONEQAY_DB_PORT
- ONEQAY_DB_DATABASE
- ONEQAY_DB_USERNAME
- ONEQAY_DB_PASSWORD

Canonical source of truth:

`apps/web/config/database.php`

Sprint226 therefore corrects the probe to read only the canonical `ONEQAY_DB_*` namespace.

Do not add duplicate generic `DB_*` values to the runtime .env.

## Exact bounded envelope

Sprint226 changes exactly eight paths:

1. `.github/workflows/sprint103-final-shift-close-migration27-execution-evidence.yml`
2. `.github/workflows/sprint117-final-shift-close-migration27-selected-target-binding.yml`
3. `.github/workflows/sprint118-final-shift-close-migration27-selected-target-db-binding.yml`
4. `.github/workflows/sprint224-final-shift-close-migration27-cpanel-local-db-binding-probe.yml`
5. `.github/workflows/sprint226-final-shift-close-migration27-cpanel-local-db-env-binding-regression.yml`
6. `docs/SPRINT226_FINAL_SHIFT_CLOSE_MIGRATION27_CPANEL_LOCAL_DB_ENV_BINDING_CORRECTION.md`
7. `ops/final-shift-close/MIGRATION27_SELECTED_TARGET_BINDING_CONTRACT.json`
8. `ops/final-shift-close/cpanel-migration27-local-db-binding-probe.php`

Sorted-newline path-set SHA-256:

`f3b3d0b1a3940171019eb58d793139341bb28461d43be8974d8ae4f93cff2354`

No application runtime source, migration source, workflow producer schema, operational state, target-selection state, deployment, Technical Preview, Production, or updater source is changed.

## Corrected probe contract

The cPanel-local probe now reads:

- `ONEQAY_DB_HOST`
- `ONEQAY_DB_PORT`
- `ONEQAY_DB_DATABASE`
- `ONEQAY_DB_USERNAME`
- `ONEQAY_DB_PASSWORD`

The protected GitHub Environment still carries the selected-target binding secrets required by the producer. The local probe computes the same credential-binding semantic object from the canonical oneQay runtime credentials.

The probe remains:

- CLI-only
- read-only
- exact-main bound
- selected-target bound
- 900-second TTL
- HMAC-SHA256 authenticated
- secret-free in logs and evidence
- fail-closed on identity, migration-state, fingerprint, or HMAC mismatch

## Operational handling after merge

After Sprint226 merge:

1. download the canonical current-main version of `ops/final-shift-close/cpanel-migration27-local-db-binding-probe.php`;
2. replace only the private cPanel operator copy:
   `/home/pekd7254/oneqay-operator/cpanel-migration27-local-db-binding-probe.php`;
3. keep the runtime `.env` unchanged;
4. rerun the one-shot cPanel Cron using the exact post-merge main SHA;
5. require:
   - `RESULT=SUCCESS`
   - `MODE=CPANEL_LOCAL_INDEPENDENT_PDO_READBACK`
   - `MIGRATION27=NOT_EXECUTED`
6. copy only the fresh generated Base64 payload into protected GitHub Environment secret `ONEQAY_MIGRATION27_BINDING_LOCAL_PROBE_B64`;
7. dispatch `Final Shift Close Migration27 Selected Target DB Binding` from current main within the payload lifetime.

A successful producer run still does not authorize migration #27.

## NO-GO preserved

`MIGRATION27_EXECUTION = NOT_PERFORMED`

`MIGRATION27_EXECUTION_AUTHORITY = NOT_GRANTED`

`PERMISSION_PROVISIONING = NONE`

`FINAL_SHIFT_CLOSE = INACTIVE`

`TECHNICAL_PREVIEW = NOT_AUTHORIZED`

`PRODUCTION = NOT_AUTHORIZED`

`UPDATER = INACTIVE`

`DATABASE_MUTATION = NO`

`TARGET_RESELECTION = NO`

Author by Lab | zefry

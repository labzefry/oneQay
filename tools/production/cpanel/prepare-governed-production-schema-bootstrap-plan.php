<?php

declare(strict_types=1);

// Author by Lab | zefry
// Planning only; never performs database access or migration execution.
require_once __DIR__.'/governed-production-schema-bootstrap-foundation.php';

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    if ($argc !== 4) {
        fwrite(STDERR, "Usage: php prepare-governed-production-schema-bootstrap-plan.php <exact-source-migrations-dir> <read-only-inventory.json> <private-output-plan.json>\n");
        exit(64);
    }
    try {
        $lock = s267JsonFile(dirname(__DIR__, 3).'/ops/final-shift-close/PRODUCTION_SCHEMA_BOOTSTRAP_SOURCE_LOCK.json');
        $plan = s267PreparePlan($lock, s267JsonFile($argv[2]), $argv[1]);
        s267WritePrivatePlan($argv[3], $plan);
        fwrite(STDOUT, "SPRINT267_PLAN=PREPARED_NOT_AUTHORIZED\n");
        fwrite(STDOUT, "PRODUCTION_DATABASE_MUTATION=NOT_PERFORMED\n");
        fwrite(STDOUT, "STAGING_MUTATION=NOT_PERFORMED\n");
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, "SPRINT267_PLAN=FAILED:".($e instanceof Sprint267SchemaBootstrapException ? $e->getMessage() : "internal_error")."\n");
        exit(1);
    }
}

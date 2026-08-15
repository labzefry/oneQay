<?php

declare(strict_types=1);

// Author by Lab | zefry

require __DIR__ . '/../src/Runtime/Qualification.php';

use OneQay\Runtime\Qualification\QualificationOutcome;
use OneQay\Runtime\Qualification\RuntimeQualificationEvaluator;
use OneQay\Runtime\Qualification\SanitizedRuntimeEvidenceParser;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$evidencePath = __DIR__ . '/../docs/evidence/runtime/p1-cpanel-actual-20260815.json';
$reportPath = __DIR__ . '/../docs/evidence/runtime/p1-cpanel-actual-20260815.report.json';

$evidenceRaw = file_get_contents($evidencePath);
$reportRaw = file_get_contents($reportPath);
$assert(is_string($evidenceRaw) && $evidenceRaw !== '', 'Actual P1 evidence package is missing.');
$assert(is_string($reportRaw) && $reportRaw !== '', 'Actual P1 deterministic report is missing.');

/** @var array<string, mixed> $evidencePayload */
$evidencePayload = json_decode($evidenceRaw, true, 512, JSON_THROW_ON_ERROR);
/** @var array<string, mixed> $expectedReport */
$expectedReport = json_decode($reportRaw, true, 512, JSON_THROW_ON_ERROR);

$evidence = (new SanitizedRuntimeEvidenceParser())->parse($evidencePayload);
$actualReport = (new RuntimeQualificationEvaluator())->evaluate($evidence);
$actualArray = $actualReport->toArray();

$assert($actualReport->outcome === QualificationOutcome::BLOCKED, 'Actual P1 evidence must remain BLOCKED until all mandatory controls are VERIFIED.');
$assert($actualArray === $expectedReport, 'Committed actual P1 report does not match evaluator output.');
$assert($actualArray['lifecycle_authority_created'] === false, 'Actual P1 qualification report must never create lifecycle authority.');
$assert($actualArray['target_id'] === 'p1-cpanel-actual-20260815', 'Actual P1 target identifier drifted.');
$assert($actualArray['engine_profile']['family'] === 'MARIADB', 'Actual P1 evidence must retain MariaDB family identity.');
$assert($actualArray['engine_profile']['version'] === '11.4.8', 'Actual P1 evidence must retain observed MariaDB version.');
$assert($actualArray['verified'] === ['ENGINE:BACKUP_EXPORT', 'RUNTIME:PHP_RUNTIME'], 'Only PHP runtime and database backup/export are sufficiently VERIFIED by this actual evidence intake.');
$assert(count($actualArray['blocking']) === 27, 'Expected exactly 27 blocking runtime/engine controls after the actual cPanel evidence intake.');
$assert(in_array('RUNTIME:PHP_CLI:PARTIAL', $actualArray['blocking'], true), 'PHP CLI must remain PARTIAL without successful oneQay CLI execution evidence.');
$assert(in_array('RUNTIME:SCHEDULER_CRON:PARTIAL', $actualArray['blocking'], true), 'Scheduler must remain PARTIAL without successful oneQay scheduler execution evidence.');
$assert(in_array('RUNTIME:SAFE_DOCUMENT_ROOT:PARTIAL', $actualArray['blocking'], true), 'Safe document root must remain PARTIAL until the selected Preview hostname is provisioned and mapped.');
$assert(in_array('RUNTIME:PREVIEW_ISOLATION:UNVERIFIED', $actualArray['blocking'], true), 'Preview isolation must remain UNVERIFIED until the selected Preview target exists and is tested.');
$assert(in_array('ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED', $actualArray['blocking'], true), 'Successful isolated restore must remain NOT_SUPPLIED.');
$assert(in_array('RUNTIME:ROLLBACK:NOT_SUPPLIED', $actualArray['blocking'], true), 'Rollback evidence must remain NOT_SUPPLIED.');
$assert(in_array('ENGINE:TENANT_ISOLATION:UNVERIFIED', $actualArray['blocking'], true), 'Database-profile tenant isolation must remain UNVERIFIED.');
$assert(in_array('ENGINE:TRANSACTION_SEMANTICS:UNVERIFIED', $actualArray['blocking'], true), 'Transaction semantics must remain UNVERIFIED without actual synthetic runtime evidence.');

$serialized = strtolower(json_encode($actualArray, JSON_THROW_ON_ERROR));
foreach (['password', 'private_key', 'api_token', 'session_secret', 'database_password', 'app_key'] as $forbidden) {
    $assert(!str_contains($serialized, $forbidden), 'Actual P1 report contains a forbidden secret-shaped field.');
}

fwrite(STDOUT, sprintf("M7.5 actual P1 evidence regression passed: %d assertions.\n", $assertions));

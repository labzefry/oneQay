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

$evidencePath = __DIR__ . '/../docs/evidence/runtime/p1-cpanel-historical.json';
$reportPath = __DIR__ . '/../docs/evidence/runtime/p1-cpanel-historical.report.json';

$evidenceRaw = file_get_contents($evidencePath);
$reportRaw = file_get_contents($reportPath);
$assert(is_string($evidenceRaw) && $evidenceRaw !== '', 'P1 evidence package is missing.');
$assert(is_string($reportRaw) && $reportRaw !== '', 'P1 deterministic report is missing.');

/** @var array<string, mixed> $evidencePayload */
$evidencePayload = json_decode($evidenceRaw, true, 512, JSON_THROW_ON_ERROR);
/** @var array<string, mixed> $expectedReport */
$expectedReport = json_decode($reportRaw, true, 512, JSON_THROW_ON_ERROR);

$evidence = (new SanitizedRuntimeEvidenceParser())->parse($evidencePayload);
$actualReport = (new RuntimeQualificationEvaluator())->evaluate($evidence);
$actualArray = $actualReport->toArray();

$assert($actualReport->outcome === QualificationOutcome::BLOCKED, 'Historical P1 evidence must remain BLOCKED.');
$assert($actualArray === $expectedReport, 'Committed deterministic P1 report does not match evaluator output.');
$assert($actualArray['lifecycle_authority_created'] === false, 'Qualification report must never create lifecycle authority.');
$assert($actualArray['engine_profile']['family'] === 'MARIADB', 'Historical P1 evidence must retain MariaDB family identity.');
$assert($actualArray['engine_profile']['version'] === '11.4.8', 'Historical P1 evidence must retain observed MariaDB version.');
$assert($actualArray['verified'] === ['RUNTIME:PHP_RUNTIME'], 'Only PHP runtime is sufficiently verified by current historical P1 evidence.');
$assert(count($actualArray['blocking']) === 28, 'Expected exactly 28 blocking runtime/engine controls.');
$assert(in_array('ENGINE:RESTORE_VERIFIED:NOT_SUPPLIED', $actualArray['blocking'], true), 'Verified restore must remain NOT_SUPPLIED.');
$assert(in_array('RUNTIME:ROLLBACK:NOT_SUPPLIED', $actualArray['blocking'], true), 'Rollback must remain NOT_SUPPLIED.');
$assert(in_array('RUNTIME:OUTBOUND_DNS_HTTPS:NOT_SUPPLIED', $actualArray['blocking'], true), 'Outbound DNS/HTTPS evidence must remain NOT_SUPPLIED.');
$assert(in_array('ENGINE:APPLICATION_CONNECTIVITY:UNVERIFIED', $actualArray['blocking'], true), 'Application database connectivity must remain UNVERIFIED.');
$assert(in_array('ENGINE:TENANT_ISOLATION:UNVERIFIED', $actualArray['blocking'], true), 'Database-profile tenant isolation must remain UNVERIFIED.');

$serialized = json_encode($actualArray, JSON_THROW_ON_ERROR);
foreach (['password', 'private_key', 'api_token', 'session_secret', 'database_password'] as $forbidden) {
    $assert(!str_contains(strtolower($serialized), $forbidden), 'Evidence report contains a forbidden secret-shaped field.');
}

fwrite(STDOUT, sprintf("M7.5 historical P1 evidence regression passed: %d assertions.\n", $assertions));

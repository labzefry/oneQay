<?php

declare(strict_types=1);

// Author by Lab | zefry

require __DIR__ . '/../src/Runtime/Qualification.php';

use OneQay\Runtime\Qualification\EvidenceItem;
use OneQay\Runtime\Qualification\EvidenceStatus;
use OneQay\Runtime\Qualification\QualificationOutcome;
use OneQay\Runtime\Qualification\RuntimeQualificationCatalog;
use OneQay\Runtime\Qualification\RuntimeQualificationEvaluator;
use OneQay\Runtime\Qualification\SanitizedRuntimeEvidenceParser;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$parser = new SanitizedRuntimeEvidenceParser();
$evaluator = new RuntimeQualificationEvaluator();

$blockedPayload = [
    'target_id' => 'preview-p2-sanitized',
    'observed_at' => '2026-08-15T00:00:00+07:00',
    'engine' => ['family' => 'MARIADB', 'version' => '11.4.8'],
    'capabilities' => [
        'PHP_RUNTIME' => ['status' => 'VERIFIED', 'reference' => 'repo:DEC-009#D-009-02'],
        'SAFE_DOCUMENT_ROOT' => ['status' => 'UNVERIFIED', 'reference' => 'repo:assessment#document-root'],
    ],
    'engine_checks' => [
        'APPLICATION_CONNECTIVITY' => ['status' => 'PARTIAL', 'reference' => 'repo:assessment#database-connectivity'],
    ],
];

$blocked = $evaluator->evaluate($parser->parse($blockedPayload));
$assert($blocked->outcome === QualificationOutcome::BLOCKED, 'Incomplete evidence must fail closed.');
$assert(in_array('RUNTIME:SAFE_DOCUMENT_ROOT:UNVERIFIED', $blocked->blocking, true), 'Unverified runtime capability was not blocking.');
$assert(in_array('ENGINE:APPLICATION_CONNECTIVITY:PARTIAL', $blocked->blocking, true), 'Partial engine evidence was not blocking.');
$assert(in_array('RUNTIME:PHP_CLI:NOT_SUPPLIED', $blocked->blocking, true), 'Missing runtime evidence was not classified as NOT_SUPPLIED.');
$assert($blocked->toArray()['lifecycle_authority_created'] === false, 'Qualification report must never create lifecycle authority.');

$verifiedRuntime = [];
foreach (RuntimeQualificationCatalog::RUNTIME_REQUIREMENTS as $identifier) {
    $verifiedRuntime[$identifier] = [
        'status' => EvidenceStatus::VERIFIED->value,
        'reference' => 'evidence:runtime/' . strtolower($identifier),
    ];
}

$verifiedEngine = [];
foreach (RuntimeQualificationCatalog::ENGINE_PROFILE_REQUIREMENTS as $identifier) {
    $verifiedEngine[$identifier] = [
        'status' => EvidenceStatus::VERIFIED->value,
        'reference' => 'evidence:engine/' . strtolower($identifier),
    ];
}

$completePayload = [
    'target_id' => 'preview-p2-qualified',
    'observed_at' => '2026-08-15T00:00:00+07:00',
    'engine' => ['family' => 'MARIADB', 'version' => '11.4.8'],
    'capabilities' => $verifiedRuntime,
    'engine_checks' => $verifiedEngine,
];

$complete = $evaluator->evaluate($parser->parse($completePayload));
$assert($complete->outcome === QualificationOutcome::EVIDENCE_COMPLETE, 'Fully verified evidence package should be evidence-complete.');
$assert($complete->blocking === [], 'Evidence-complete report must not contain blockers.');
$assert(count($complete->verified) === count(RuntimeQualificationCatalog::RUNTIME_REQUIREMENTS) + count(RuntimeQualificationCatalog::ENGINE_PROFILE_REQUIREMENTS), 'Verified evidence count is incomplete.');

$reversePayload = $completePayload;
$reversePayload['capabilities'] = array_reverse($verifiedRuntime, true);
$reversePayload['engine_checks'] = array_reverse($verifiedEngine, true);
$reverse = $evaluator->evaluate($parser->parse($reversePayload));
$assert($complete->toJson() === $reverse->toJson(), 'Qualification output must be deterministic regardless of input map order.');

foreach ([
    ['payload' => $completePayload + ['password' => 'prohibited'], 'message' => 'Unexpected top-level fields must be rejected.'],
    ['payload' => array_replace_recursive($completePayload, ['engine' => ['family' => 'SQLITE']]), 'message' => 'Unauthorized engine family must be rejected.'],
] as $invalidCase) {
    try {
        $parser->parse($invalidCase['payload']);
        $assert(false, $invalidCase['message']);
    } catch (InvalidArgumentException) {
        $assert(true, $invalidCase['message']);
    }
}

try {
    new EvidenceItem(EvidenceStatus::VERIFIED);
    $assert(false, 'Verified evidence without reference must be rejected.');
} catch (InvalidArgumentException) {
    $assert(true, 'Verified evidence without reference rejected.');
}

try {
    new EvidenceItem(EvidenceStatus::PARTIAL, 'unsafe reference with spaces');
    $assert(false, 'Unsanitized evidence reference must be rejected.');
} catch (InvalidArgumentException) {
    $assert(true, 'Unsanitized evidence reference rejected.');
}

$source = (string) file_get_contents(__DIR__ . '/../src/Runtime/Qualification.php');
$assert(!preg_match('/\b(new\s+PDO|mysqli_connect|pg_connect|curl_exec|fsockopen|shell_exec|proc_open)\b/i', $source), 'Qualification evaluator must not perform network, database, or process execution.');
$assert(!preg_match('/\b(password|credential|access_token|private_key)\b/i', $complete->toJson()), 'Qualification output must not expose credential-shaped fields.');

fwrite(STDOUT, sprintf("M7.5 runtime qualification preparation tests passed: %d assertions.\n", $assertions));

<?php

declare(strict_types=1);

require __DIR__ . '/../src/Migration/Foundation.php';
require __DIR__ . '/../src/SchemaPlanning/Foundation.php';

use OneQay\Migration\MigrationChecksum;
use OneQay\Migration\MigrationDefinition;
use OneQay\Migration\MigrationException;
use OneQay\Migration\MigrationExecutionService;
use OneQay\Migration\MigrationIdentifier;
use OneQay\Migration\MigrationManifest;
use OneQay\Migration\MigrationPlanner;
use OneQay\Migration\MigrationPlanningPolicy;
use OneQay\Migration\MigrationRollbackClassification;
use OneQay\Migration\MigrationSafetyClassification;
use OneQay\Migration\SyntheticMigrationExecutor;
use OneQay\Migration\SyntheticMigrationLock;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$firstId = new MigrationIdentifier('MIG_20260806_000001_FOUNDATION_MARKER');
$secondId = new MigrationIdentifier('MIG_20260806_000002_COMPATIBILITY_MARKER');
$thirdId = new MigrationIdentifier('MIG_20260806_000003_FORWARD_MARKER');
$assert($firstId->value === 'MIG_20260806_000001_FOUNDATION_MARKER', 'Canonical migration identifier failed.');

foreach (['', 'migration_1', 'MIG_20260806_BAD', 'MIG_20260806_000001_bad-name'] as $invalid) {
    try {
        new MigrationIdentifier($invalid);
        $assert(false, 'Invalid migration identifier accepted.');
    } catch (MigrationException $exception) {
        $assert($exception->errorCode === MigrationException::IDENTIFIER_INVALID, 'Unexpected identifier error code.');
    }
}

$sensitiveMarker = 'synthetic-' . hash('sha256', 'sprint09-sensitive-marker');
$syntheticPath = '/' . implode('/', ['synthetic', 'private', 'location']);
$canonicalOne = implode('|', ['FOUNDATION_MARKER', $sensitiveMarker, $syntheticPath]);
$canonicalTwo = 'COMPATIBILITY_MARKER';
$canonicalThree = 'FORWARD_MARKER';

$checksumOne = MigrationChecksum::fromCanonicalDescriptor($canonicalOne);
$checksumTwo = MigrationChecksum::fromCanonicalDescriptor($canonicalTwo);
$checksumThree = MigrationChecksum::fromCanonicalDescriptor($canonicalThree);
$assert(strlen($checksumOne->value) === 64, 'Checksum length is invalid.');
$assert(!str_contains(json_encode($checksumOne, JSON_THROW_ON_ERROR), $sensitiveMarker), 'Checksum JSON leaked descriptor content.');
$assert(!str_contains(serialize($checksumOne), $syntheticPath), 'Checksum serialization leaked path.');
$assert(!str_contains(print_r($checksumOne, true), $sensitiveMarker), 'Checksum print leaked sensitive marker.');
$assert(!str_contains(var_export($checksumOne, true), $syntheticPath), 'Checksum export leaked path.');

$first = new MigrationDefinition(
    $firstId,
    $checksumOne,
    $checksumOne,
    [],
    MigrationSafetyClassification::SAFE,
    MigrationRollbackClassification::REVERSIBLE,
);
$second = new MigrationDefinition(
    $secondId,
    $checksumTwo,
    $checksumTwo,
    [$firstId],
    MigrationSafetyClassification::CAUTION,
    MigrationRollbackClassification::REVERSIBLE,
);
$third = new MigrationDefinition(
    $thirdId,
    $checksumThree,
    $checksumThree,
    [$secondId],
    MigrationSafetyClassification::SAFE,
    MigrationRollbackClassification::FORWARD_ONLY,
);

$manifest = new MigrationManifest([$first, $second, $third]);
$assert($manifest->identifiers() === [$firstId->value, $secondId->value, $thirdId->value], 'Manifest ordering changed.');
$assert(count($manifest->entries()) === 3, 'Manifest entries changed.');

try {
    new MigrationManifest([$first, $first]);
    $assert(false, 'Duplicate migration identifier accepted.');
} catch (MigrationException $exception) {
    $assert($exception->errorCode === MigrationException::DUPLICATE_IDENTIFIER, 'Duplicate migration error code changed.');
}

$badChecksum = new MigrationDefinition(
    new MigrationIdentifier('MIG_20260806_000004_CHECKSUM_MARKER'),
    MigrationChecksum::fromCanonicalDescriptor('DECLARED'),
    MigrationChecksum::fromCanonicalDescriptor('ACTUAL'),
    [$thirdId],
    MigrationSafetyClassification::SAFE,
    MigrationRollbackClassification::REVERSIBLE,
);
try {
    new MigrationManifest([$first, $second, $third, $badChecksum]);
    $assert(false, 'Checksum mismatch accepted.');
} catch (MigrationException $exception) {
    $assert($exception->errorCode === MigrationException::CHECKSUM_MISMATCH, 'Checksum mismatch error code changed.');
}

$missingDependency = new MigrationDefinition(
    new MigrationIdentifier('MIG_20260806_000004_MISSING_MARKER'),
    MigrationChecksum::fromCanonicalDescriptor('MISSING'),
    MigrationChecksum::fromCanonicalDescriptor('MISSING'),
    ['MIG_20260806_000000_UNKNOWN_MARKER'],
    MigrationSafetyClassification::SAFE,
    MigrationRollbackClassification::REVERSIBLE,
);
try {
    new MigrationManifest([$first, $second, $third, $missingDependency]);
    $assert(false, 'Missing dependency accepted.');
} catch (MigrationException $exception) {
    $assert($exception->errorCode === MigrationException::DEPENDENCY_MISSING, 'Missing dependency error code changed.');
}

$outOfOrder = new MigrationDefinition(
    new MigrationIdentifier('MIG_20260806_000000_ORDER_MARKER'),
    MigrationChecksum::fromCanonicalDescriptor('ORDER'),
    MigrationChecksum::fromCanonicalDescriptor('ORDER'),
    [],
    MigrationSafetyClassification::SAFE,
    MigrationRollbackClassification::REVERSIBLE,
);
try {
    new MigrationManifest([$first, $outOfOrder]);
    $assert(false, 'Out-of-order migration accepted.');
} catch (MigrationException $exception) {
    $assert($exception->errorCode === MigrationException::ORDER_INVALID, 'Ordering error code changed.');
}

$planner = new MigrationPlanner();
$plan = $planner->plan($manifest);
$assert($plan->isDryRun, 'Migration plan is not dry-run.');
$assert($plan->identifiers() === $manifest->identifiers(), 'Migration plan identifiers changed.');
$assert(strlen($plan->checksum()) === 64, 'Migration plan checksum is invalid.');
$assert($planner->plan($manifest, [$firstId])->identifiers() === [$secondId->value, $thirdId->value], 'Applied migration handling failed.');

try {
    $planner->plan($manifest, ['MIG_20260806_999999_UNKNOWN_MARKER']);
    $assert(false, 'Unknown applied migration accepted.');
} catch (MigrationException $exception) {
    $assert($exception->errorCode === MigrationException::PLAN_INVALID, 'Unknown applied migration error code changed.');
}

try {
    $planner->plan($manifest, [$secondId]);
    $assert(false, 'Non-contiguous applied migration state accepted.');
} catch (MigrationException $exception) {
    $assert($exception->errorCode === MigrationException::ORDER_INVALID, 'Applied-prefix ordering error code changed.');
}

$destructiveId = new MigrationIdentifier('MIG_20260806_000004_DESTRUCTIVE_MARKER');
$destructiveChecksum = MigrationChecksum::fromCanonicalDescriptor('DESTRUCTIVE_MARKER');
$destructive = new MigrationDefinition(
    $destructiveId,
    $destructiveChecksum,
    $destructiveChecksum,
    [$thirdId],
    MigrationSafetyClassification::DESTRUCTIVE,
    MigrationRollbackClassification::FORWARD_ONLY,
);
$destructiveManifest = new MigrationManifest([$first, $second, $third, $destructive]);
try {
    $planner->plan($destructiveManifest);
    $assert(false, 'Destructive migration was not denied by default.');
} catch (MigrationException $exception) {
    $assert($exception->errorCode === MigrationException::DESTRUCTIVE_DENIED, 'Destructive denial error code changed.');
}
$explicitPlan = $planner->plan($destructiveManifest, [], MigrationPlanningPolicy::explicitlyAllowDestructive());
$assert(in_array($destructiveId->value, $explicitPlan->identifiers(), true), 'Explicit destructive classification was not represented.');

$planner->assertRollbackAvailable($first);
$assert(true, 'Reversible migration was rejected.');
try {
    $planner->assertRollbackAvailable($third);
    $assert(false, 'Forward-only migration reported rollback available.');
} catch (MigrationException $exception) {
    $assert($exception->errorCode === MigrationException::ROLLBACK_UNAVAILABLE, 'Rollback classification error code changed.');
}

$lock = new SyntheticMigrationLock();
$executor = new SyntheticMigrationExecutor();
$service = new MigrationExecutionService();
$result = $service->run($plan, $lock, $executor, 'corr-migration-001');
$assert($result->isSuccessful, 'Synthetic dry-run execution failed.');
$assert($result->isDryRun, 'Synthetic execution was not dry-run.');
$assert($result->processedIdentifiers === $plan->identifiers(), 'Synthetic execution identifiers changed.');
$assert($executor->executeCalls === 1, 'Synthetic executor call count changed.');
$assert(!$lock->isHeld(), 'Migration lock was not released.');

$encodedResult = json_encode($result, JSON_THROW_ON_ERROR);
foreach ([$sensitiveMarker, $syntheticPath, 'DB_PASSWORD', 'DB_USER', 'DB_HOST'] as $forbidden) {
    $assert(!str_contains($encodedResult, $forbidden), 'Migration result leaked sensitive or connection material.');
}

$unavailableLock = new SyntheticMigrationLock(false);
$unusedExecutor = new SyntheticMigrationExecutor();
$lockFailure = $service->run($plan, $unavailableLock, $unusedExecutor, 'corr-migration-002');
$assert(!$lockFailure->isSuccessful, 'Unavailable migration lock was accepted.');
$assert(in_array(MigrationException::LOCK_UNAVAILABLE, $lockFailure->errorCodes, true), 'Lock failure code missing.');
$assert($unusedExecutor->executeCalls === 0, 'Executor ran without migration lock.');

$failingLock = new SyntheticMigrationLock();
$failingExecutor = new SyntheticMigrationExecutor(true);
$executionFailure = $service->run($plan, $failingLock, $failingExecutor, 'corr-migration-003');
$assert(!$executionFailure->isSuccessful, 'Executor failure was accepted.');
$assert(in_array(MigrationException::EXECUTION_FAILED, $executionFailure->errorCodes, true), 'Execution failure code missing.');
$assert(!$failingLock->isHeld(), 'Migration lock was not released after failure.');

$source = (string) file_get_contents(__DIR__ . '/../src/Migration/Foundation.php');
$assert(!preg_match('/\b(CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|INSERT\s+INTO|DELETE\s+FROM)\b/i', $source), 'Production SQL behavior introduced.');
$assert(!preg_match('/\b(pos|sale|payment|inventory|catalog)\b/i', $source), 'Business schema or POS behavior introduced.');
$assert(!preg_match('/(?:\/home\/|\/var\/|[A-Z]:\\\\)/', $source), 'Internal path introduced.');
$assert(!preg_match('/\b(password|credential|api[_-]?key|token)\s*[:=]\s*[\'\"][^\'\"]+/i', $source), 'Credential-like value introduced.');
$assert(!str_contains($source, 'new PDO('), 'Production database connection introduced.');

$bridgeSteps = [
    new \OneQay\SchemaPlanning\MigrationPlanningStep(
        new \OneQay\SchemaPlanning\StableChangeIdentifier(str_repeat('1', 64)),
        \OneQay\SchemaPlanning\SchemaChangeKind::ENTITY_CREATED,
        'ENTITY_ALPHA',
        null,
        null,
        new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('a', 64)),
    ),
    new \OneQay\SchemaPlanning\MigrationPlanningStep(
        new \OneQay\SchemaPlanning\StableChangeIdentifier(str_repeat('2', 64)),
        \OneQay\SchemaPlanning\SchemaChangeKind::ATTRIBUTE_ADDED,
        'ENTITY_ALPHA',
        'ATTRIBUTE_ALPHA',
        null,
        new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('b', 64)),
    ),
    new \OneQay\SchemaPlanning\MigrationPlanningStep(
        new \OneQay\SchemaPlanning\StableChangeIdentifier(str_repeat('3', 64)),
        \OneQay\SchemaPlanning\SchemaChangeKind::UNIQUE_INDEX_ADDED,
        'ENTITY_ALPHA',
        'UNIQUE_ALPHA',
        null,
        new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('c', 64)),
    ),
    new \OneQay\SchemaPlanning\MigrationPlanningStep(
        new \OneQay\SchemaPlanning\StableChangeIdentifier(str_repeat('4', 64)),
        \OneQay\SchemaPlanning\SchemaChangeKind::REFERENCE_ADDED,
        'ENTITY_ALPHA',
        'REFERENCE_ALPHA',
        null,
        new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('d', 64)),
    ),
];
$planningArtifact = new \OneQay\SchemaPlanning\MigrationPlanningArtifact(
    new \OneQay\SchemaPlanning\PlanFingerprint(str_repeat('e', 64)),
    new \OneQay\SchemaPlanning\CorrelationId('corr-review-bridge-001'),
    new \OneQay\SchemaPlanning\CorrelationId('corr-planning-bridge-001'),
    new \OneQay\SchemaPlanning\ReviewerReference('zefriansyah'),
    new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('f', 64)),
    new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('0', 64)),
    $bridgeSteps,
);
$bridge = new \OneQay\SchemaPlanning\DeterministicGovernedMigrationArtifactBridge();
$governed = $bridge->build($planningArtifact, 'corr-bridge-001');
$governedAgain = $bridge->build($planningArtifact, 'corr-bridge-001');
$assert(json_encode($governed, JSON_THROW_ON_ERROR) === json_encode($governedAgain, JSON_THROW_ON_ERROR), 'Governed migration bridge output is not deterministic.');
$assert((new ReflectionClass($governed))->isReadOnly(), 'Governed migration manifest artifact is not immutable.');
$assert(count($governed->bindings()) === 4, 'Governed migration binding count changed.');
$assert((new ReflectionClass($governed->bindings()[0]))->isReadOnly(), 'Governed migration binding is not immutable.');

$expectedArtifactFingerprint = hash(
    'sha256',
    json_encode($planningArtifact, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION),
);
$assert($governed->sourcePlanningArtifactFingerprint->value === $expectedArtifactFingerprint, 'Sprint 14 planning artifact fingerprint was not preserved.');
$assert($governed->sourcePlanningCorrelationId->value === 'corr-planning-bridge-001', 'Source planning correlation ID was not preserved.');
$assert($governed->sourceReviewCorrelationId->value === 'corr-review-bridge-001', 'Source review correlation ID was not preserved.');
$assert($governed->bridgeCorrelationId->value === 'corr-bridge-001', 'Bridge correlation ID was not preserved.');
$assert($governed->reviewerReference->value === 'zefriansyah', 'Reviewer reference was not preserved through bridge generation.');
$assert($governed->baselineFingerprint->value === str_repeat('f', 64), 'Baseline fingerprint was not preserved through bridge generation.');
$assert($governed->targetFingerprint->value === str_repeat('0', 64), 'Target fingerprint was not preserved through bridge generation.');

$bridgeEntries = $governed->manifest->entries();
$bridgeIdentifiers = $governed->manifest->identifiers();
$assert(count($bridgeEntries) === 4, 'Governed migration manifest entry count changed.');
$sortedBridgeIdentifiers = $bridgeIdentifiers;
sort($sortedBridgeIdentifiers, SORT_STRING);
$assert($bridgeIdentifiers === $sortedBridgeIdentifiers, 'Governed migration identifiers are not deterministically ordered.');
$assert(count(array_unique($bridgeIdentifiers)) === 4, 'Governed migration identifiers are not unique.');

foreach ($bridgeEntries as $index => $entry) {
    $assert($entry->safety === MigrationSafetyClassification::CAUTION, 'Bridge generated a non-CAUTION migration definition.');
    $assert($entry->rollback === MigrationRollbackClassification::FORWARD_ONLY, 'Bridge generated a reversible migration claim.');
    $assert($entry->hasValidChecksum(), 'Bridge generated a checksum mismatch.');
    $expectedDependencies = $index === 0 ? [] : [$bridgeEntries[$index - 1]->identifier->value];
    $assert($entry->dependencies === $expectedDependencies, 'Bridge serial dependency chain changed.');
    $assert($governed->bindings()[$index]->migrationIdentifier->equals($entry->identifier), 'Bridge binding order does not match manifest order.');
    $assert($governed->bindings()[$index]->sourceChangeIdentifier->value === $bridgeSteps[$index]->sourceChangeIdentifier->value, 'Bridge source change identity was not preserved.');
}

$bridgePlan = $planner->plan($governed->manifest);
$assert($bridgePlan->isDryRun, 'Governed bridge manifest did not remain dry-run under the existing planner.');
$assert($bridgePlan->identifiers() === $bridgeIdentifiers, 'Existing migration planner changed governed bridge ordering.');
$assert(!\OneQay\SchemaPlanning\MigrationPlanningStep::isAllowedKind(\OneQay\SchemaPlanning\SchemaChangeKind::TENANT_SCOPE_CHANGED), 'Tenant-scope mutation became bridge-eligible.');
$assert(!\OneQay\SchemaPlanning\MigrationPlanningStep::isAllowedKind(\OneQay\SchemaPlanning\SchemaChangeKind::TENANT_KEY_CHANGED), 'Tenant-key mutation became bridge-eligible.');

$governedEncoded = json_encode($governed, JSON_THROW_ON_ERROR);
foreach (['ENTITY_ALPHA', 'ATTRIBUTE_ALPHA', 'UNIQUE_ALPHA', 'REFERENCE_ALPHA', 'DB_PASSWORD', 'DB_USER', 'DB_HOST', 'jdbc:', 'mysql:host=', 'CREATE TABLE', 'ALTER TABLE', 'DROP TABLE', 'INSERT INTO', 'DELETE FROM', '/var/', '/home/'] as $forbidden) {
    $assert(!str_contains($governedEncoded, $forbidden), 'Governed bridge output contains forbidden or raw planning material.');
}
$bridgeSource = (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/MigrationArtifactBridge.php');
$assert(!preg_match('/\b(CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|INSERT\s+INTO|DELETE\s+FROM)\b/i', $bridgeSource), 'Governed migration bridge contains executable SQL.');
$assert(!str_contains($bridgeSource, 'new PDO('), 'Governed migration bridge introduced a database connection.');
$assert(!preg_match('/\b(curl_|fsockopen|stream_socket_client)\b/i', $bridgeSource), 'Governed migration bridge introduced a network dependency.');
$assert(!preg_match('/\b(file_put_contents|fopen|unlink|mkdir|rename)\b/i', $bridgeSource), 'Governed migration bridge introduced a filesystem side effect.');
$assert(!str_contains($bridgeSource, 'MigrationExecutionService'), 'Governed migration bridge introduced migration execution coupling.');
$assert(str_contains($bridgeSource, 'MigrationSafetyClassification::CAUTION'), 'Governed migration bridge CAUTION classification is missing.');
$assert(str_contains($bridgeSource, 'MigrationRollbackClassification::FORWARD_ONLY'), 'Governed migration bridge FORWARD_ONLY classification is missing.');

fwrite(STDOUT, sprintf("Migration Governance and Safety tests passed: %d assertions.\n", $assertions));

<?php

declare(strict_types=1);

require __DIR__ . '/../src/Migration/Foundation.php';

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

fwrite(STDOUT, sprintf("Migration Governance and Safety tests passed: %d assertions.\n", $assertions));

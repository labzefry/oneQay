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

$throwsLaravelGeneration = static function (callable $callback, string $code) use ($assert): void {
    try {
        $callback();
        $assert(false, "Expected {$code}.");
    } catch (\OneQay\SchemaPlanning\LaravelMigrationGenerationException $exception) {
        $assert($exception->errorCode === $code, "Unexpected {$exception->errorCode}.");
    }
};

$ddId = static fn (string $value) => new \OneQay\DataDefinition\DataDefinitionIdentifier($value);
$physicalId = static fn (string $value) => new \OneQay\PhysicalMapping\PhysicalIdentifier($value);
$required = \OneQay\DataDefinition\NullabilityPolicy::REQUIRED;
$nullable = \OneQay\DataDefinition\NullabilityPolicy::NULLABLE;
$global = \OneQay\DataDefinition\TenantScope::GLOBAL;
$none = static fn () => \OneQay\DataDefinition\DefaultValueDefinition::none();
$nullDefault = static fn () => \OneQay\DataDefinition\DefaultValueDefinition::nullValue();
$constraint = static fn (string $type, ?int $length = null) => new \OneQay\DataDefinition\ValueConstraint(new \OneQay\DataDefinition\PortableScalarType($type), $length);
$definitionAttribute = static fn (string $id, string $type, $nullability, $default, ?int $length = null) => new \OneQay\DataDefinition\AttributeDefinition($ddId($id), $constraint($type, $length), $nullability, $default);

$charset = new \OneQay\PhysicalMapping\CharsetPolicy(\OneQay\PhysicalMapping\CharsetPolicy::UTF8MB4);
$unicode = new \OneQay\PhysicalMapping\CollationPolicy(\OneQay\PhysicalMapping\CollationPolicy::UNICODE_CI);
$binary = new \OneQay\PhysicalMapping\CollationPolicy(\OneQay\PhysicalMapping\CollationPolicy::BINARY);
$uuidScalar = static fn () => new \OneQay\PhysicalMapping\PhysicalScalarMapping(
    new \OneQay\DataDefinition\PortableScalarType(\OneQay\DataDefinition\PortableScalarType::UUID),
    new \OneQay\PhysicalMapping\PhysicalTypeIdentifier(\OneQay\PhysicalMapping\PhysicalTypeIdentifier::CHAR_UUID),
    36, null, null, $charset, $binary,
);
$stringScalar = static fn (int $length) => new \OneQay\PhysicalMapping\PhysicalScalarMapping(
    new \OneQay\DataDefinition\PortableScalarType(\OneQay\DataDefinition\PortableScalarType::STRING),
    new \OneQay\PhysicalMapping\PhysicalTypeIdentifier(\OneQay\PhysicalMapping\PhysicalTypeIdentifier::VARCHAR),
    $length, null, null, $charset, $unicode,
);
$physicalAttribute = static fn (string $logical, string $physical, $scalar) => new \OneQay\PhysicalMapping\PhysicalAttributeMapping($ddId($logical), $physicalId($physical), $scalar);
$physicalIndex = static fn (string $name, $kind, array $attributes) => new \OneQay\PhysicalMapping\PhysicalIndexMapping($physicalId($name), $kind, $attributes);

$parentPhysical = new \OneQay\PhysicalMapping\PhysicalEntityMapping(
    $ddId('PARENT_ENTITY'), $physicalId('parent_entity'), $global,
    [$physicalAttribute('ID', 'id', $uuidScalar())],
    $physicalIndex('pk_parent_entity', \OneQay\PhysicalMapping\IndexKind::PRIMARY, ['ID']),
);
$recordReference = new \OneQay\PhysicalMapping\PhysicalReferenceMapping($physicalId('fk_record_parent'), $ddId('PARENT_ENTITY'), ['PARENT_ID' => 'ID']);
$recordUnique = $physicalIndex('uq_record_code', \OneQay\PhysicalMapping\IndexKind::UNIQUE, ['CODE']);
$recordPhysical = new \OneQay\PhysicalMapping\PhysicalEntityMapping(
    $ddId('RECORD_ENTITY'), $physicalId('record_entity'), $global,
    [
        $physicalAttribute('ID', 'id', $uuidScalar()),
        $physicalAttribute('CODE', 'code', $stringScalar(64)),
        $physicalAttribute('PARENT_ID', 'parent_id', $uuidScalar()),
        $physicalAttribute('DESCRIPTION', 'description', $stringScalar(256)),
    ],
    $physicalIndex('pk_record_entity', \OneQay\PhysicalMapping\IndexKind::PRIMARY, ['ID']),
    [$recordUnique], [$recordReference],
);
$newPhysical = new \OneQay\PhysicalMapping\PhysicalEntityMapping(
    $ddId('NEW_ENTITY'), $physicalId('new_entity'), $global,
    [$physicalAttribute('ID', 'id', $uuidScalar()), $physicalAttribute('NAME', 'name', $stringScalar(128))],
    $physicalIndex('pk_new_entity', \OneQay\PhysicalMapping\IndexKind::PRIMARY, ['ID']),
);
$targetPhysical = new \OneQay\PhysicalMapping\PhysicalMappingManifest(
    new \OneQay\PhysicalMapping\VendorIdentifier(\OneQay\PhysicalMapping\VendorIdentifier::MARIADB_11),
    [$parentPhysical, $recordPhysical, $newPhysical],
);
$canonicalizer = new \OneQay\SchemaPlanning\PhysicalManifestCanonicalizer();
$targetCanonical = $canonicalizer->canonicalize($targetPhysical);
$targetFingerprint = $canonicalizer->fingerprint($targetCanonical);

$parentDefinition = new \OneQay\DataDefinition\EntityDefinition(
    $ddId('PARENT_ENTITY'), $global,
    [$definitionAttribute('ID', \OneQay\DataDefinition\PortableScalarType::UUID, $required, $none())],
    new \OneQay\DataDefinition\PrimaryKeyDefinition(['ID']),
);
$recordDefinition = new \OneQay\DataDefinition\EntityDefinition(
    $ddId('RECORD_ENTITY'), $global,
    [
        $definitionAttribute('ID', \OneQay\DataDefinition\PortableScalarType::UUID, $required, $none()),
        $definitionAttribute('CODE', \OneQay\DataDefinition\PortableScalarType::STRING, $required, $none(), 64),
        $definitionAttribute('PARENT_ID', \OneQay\DataDefinition\PortableScalarType::UUID, $required, $none()),
        $definitionAttribute('DESCRIPTION', \OneQay\DataDefinition\PortableScalarType::STRING, $nullable, $nullDefault(), 256),
    ],
    new \OneQay\DataDefinition\PrimaryKeyDefinition(['ID']),
    [new \OneQay\DataDefinition\UniqueConstraintDefinition($ddId('RECORD_CODE_UNIQUE'), ['CODE'])],
    [new \OneQay\DataDefinition\ReferenceDefinition($ddId('RECORD_PARENT_REF'), $ddId('PARENT_ENTITY'), ['PARENT_ID' => 'ID'])],
);
$newDefinition = new \OneQay\DataDefinition\EntityDefinition(
    $ddId('NEW_ENTITY'), $global,
    [
        $definitionAttribute('ID', \OneQay\DataDefinition\PortableScalarType::UUID, $required, $none()),
        $definitionAttribute('NAME', \OneQay\DataDefinition\PortableScalarType::STRING, $required, $none(), 128),
    ],
    new \OneQay\DataDefinition\PrimaryKeyDefinition(['ID']),
);
$targetDefinitions = new \OneQay\DataDefinition\DataDefinitionManifest([$parentDefinition, $recordDefinition, $newDefinition]);

$step = static fn (string $idChar, $kind, string $entity, ?string $component, mixed $canonical) => new \OneQay\SchemaPlanning\MigrationPlanningStep(
    new \OneQay\SchemaPlanning\StableChangeIdentifier(str_repeat($idChar, 64)),
    $kind, $entity, $component, null, $canonicalizer->fingerprint($canonical),
);
$s16Steps = [
    $step('5', \OneQay\SchemaPlanning\SchemaChangeKind::ENTITY_CREATED, 'NEW_ENTITY', null, $targetCanonical['entities']['NEW_ENTITY']),
    $step('6', \OneQay\SchemaPlanning\SchemaChangeKind::ATTRIBUTE_ADDED, 'RECORD_ENTITY', 'DESCRIPTION', $targetCanonical['entities']['RECORD_ENTITY']['attributes']['DESCRIPTION']),
    $step('7', \OneQay\SchemaPlanning\SchemaChangeKind::UNIQUE_INDEX_ADDED, 'RECORD_ENTITY', 'uq_record_code', $targetCanonical['entities']['RECORD_ENTITY']['unique_indexes']['uq_record_code']),
    $step('8', \OneQay\SchemaPlanning\SchemaChangeKind::REFERENCE_ADDED, 'RECORD_ENTITY', 'fk_record_parent', $targetCanonical['entities']['RECORD_ENTITY']['references']['fk_record_parent']),
];
$s16Planning = new \OneQay\SchemaPlanning\MigrationPlanningArtifact(
    new \OneQay\SchemaPlanning\PlanFingerprint(str_repeat('9', 64)),
    new \OneQay\SchemaPlanning\CorrelationId('corr-review-s16'),
    new \OneQay\SchemaPlanning\CorrelationId('corr-planning-s16'),
    new \OneQay\SchemaPlanning\ReviewerReference('zefriansyah'),
    new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('a', 64)),
    $targetFingerprint,
    $s16Steps,
);
$s16Governed = $bridge->build($s16Planning, 'corr-bridge-s16');
$s16Generator = new \OneQay\SchemaPlanning\DeterministicLaravelMigrationGenerator();
$s16Generated = $s16Generator->generate($s16Planning, $s16Governed, $targetDefinitions, $targetPhysical, 'corr-generation-s16');
$s16GeneratedAgain = $s16Generator->generate($s16Planning, $s16Governed, $targetDefinitions, $targetPhysical, 'corr-generation-s16');
$assert(json_encode($s16Generated, JSON_THROW_ON_ERROR) === json_encode($s16GeneratedAgain, JSON_THROW_ON_ERROR), 'Sprint 16 generation is not deterministic.');
$assert((new ReflectionClass($s16Generated))->isReadOnly(), 'Sprint 16 generation artifact is not immutable.');
$assert(count($s16Generated->files()) === 4, 'Sprint 16 generated file count changed.');
$assert($s16Generated->targetManifestFingerprint->value === $targetFingerprint->value, 'Sprint 16 target mapping fingerprint changed.');
$assert(strlen($s16Generated->targetDefinitionFingerprint->value) === 64, 'Sprint 16 target definition fingerprint is invalid.');

$generatedPaths = array_map(static fn ($file): string => $file->relativePath, $s16Generated->files());
$sortedGeneratedPaths = $generatedPaths;
sort($sortedGeneratedPaths, SORT_STRING);
$assert($generatedPaths === $sortedGeneratedPaths, 'Sprint 16 generated paths are not ordered.');
$assert(count(array_unique($generatedPaths)) === 4, 'Sprint 16 generated paths are not unique.');
$allGeneratedSource = '';
foreach ($s16Generated->files() as $index => $file) {
    $assert((new ReflectionClass($file))->isReadOnly(), 'Sprint 16 file artifact is not immutable.');
    $assert(hash('sha256', $file->source) === $file->sourceFingerprint, 'Sprint 16 source fingerprint changed.');
    $assert($file->sourceChangeIdentifier->value === $s16Steps[$index]->sourceChangeIdentifier->value, 'Sprint 16 source change traceability changed.');
    $assert($file->migrationIdentifier->value === $s16Governed->bindings()[$index]->migrationIdentifier->value, 'Sprint 16 governed migration traceability changed.');
    $assert(str_contains($file->source, "throw new \\LogicException('Forward-only generated migration; rollback is not authorized.');"), 'Sprint 16 forward-only down boundary is missing.');
    $allGeneratedSource .= "\n" . $file->source;
}
$assert(str_contains($allGeneratedSource, "Schema::create('new_entity'"), 'Sprint 16 entity creation rendering missing.');
$assert(str_contains($allGeneratedSource, "\$table->primary(['id'], 'pk_new_entity')"), 'Sprint 16 primary index rendering missing.');
$assert(str_contains($allGeneratedSource, "\$table->string('description', 256)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable()->default(null)"), 'Sprint 16 nullable/default rendering missing.');
$assert(str_contains($allGeneratedSource, "\$table->unique(['code'], 'uq_record_code')"), 'Sprint 16 unique rendering missing.');
$assert(str_contains($allGeneratedSource, "\$table->foreign(['parent_id'], 'fk_record_parent')->references(['id'])->on('parent_entity')"), 'Sprint 16 reference rendering missing.');
foreach (['Schema::drop', 'dropColumn', 'dropForeign', 'dropUnique', 'DB::statement', 'DB::unprepared', 'new PDO(', 'artisan migrate', 'CREATE TABLE', 'ALTER TABLE', 'DROP TABLE'] as $forbidden) {
    $assert(!str_contains($allGeneratedSource, $forbidden), 'Sprint 16 generated source contains forbidden executable or destructive material.');
}

$alternateRecordPhysical = new \OneQay\PhysicalMapping\PhysicalEntityMapping(
    $ddId('RECORD_ENTITY'), $physicalId('record_entity'), $global,
    [
        $physicalAttribute('ID', 'id', $uuidScalar()),
        $physicalAttribute('CODE', 'code', $stringScalar(64)),
        $physicalAttribute('PARENT_ID', 'parent_id', $uuidScalar()),
        $physicalAttribute('DESCRIPTION', 'description', $stringScalar(128)),
    ],
    $physicalIndex('pk_record_entity', \OneQay\PhysicalMapping\IndexKind::PRIMARY, ['ID']), [$recordUnique], [$recordReference],
);
$alternatePhysical = new \OneQay\PhysicalMapping\PhysicalMappingManifest(new \OneQay\PhysicalMapping\VendorIdentifier(\OneQay\PhysicalMapping\VendorIdentifier::MARIADB_11), [$parentPhysical, $alternateRecordPhysical, $newPhysical]);
$throwsLaravelGeneration(
    fn () => $s16Generator->generate($s16Planning, $s16Governed, $targetDefinitions, $alternatePhysical, 'corr-generation-s16-mismatch'),
    \OneQay\SchemaPlanning\LaravelMigrationGenerationException::TARGET_MANIFEST_MISMATCH,
);

$definitionWithDescription = static function ($nullability, $default) use ($ddId, $global, $definitionAttribute, $required, $none): \OneQay\DataDefinition\EntityDefinition {
    return new \OneQay\DataDefinition\EntityDefinition(
        $ddId('RECORD_ENTITY'), $global,
        [
            $definitionAttribute('ID', \OneQay\DataDefinition\PortableScalarType::UUID, $required, $none()),
            $definitionAttribute('CODE', \OneQay\DataDefinition\PortableScalarType::STRING, $required, $none(), 64),
            $definitionAttribute('PARENT_ID', \OneQay\DataDefinition\PortableScalarType::UUID, $required, $none()),
            $definitionAttribute('DESCRIPTION', \OneQay\DataDefinition\PortableScalarType::STRING, $nullability, $default, 256),
        ],
        new \OneQay\DataDefinition\PrimaryKeyDefinition(['ID']),
        [new \OneQay\DataDefinition\UniqueConstraintDefinition($ddId('RECORD_CODE_UNIQUE'), ['CODE'])],
        [new \OneQay\DataDefinition\ReferenceDefinition($ddId('RECORD_PARENT_REF'), $ddId('PARENT_ENTITY'), ['PARENT_ID' => 'ID'])],
    );
};
$literalDefinitions = new \OneQay\DataDefinition\DataDefinitionManifest([$parentDefinition, $definitionWithDescription($nullable, \OneQay\DataDefinition\DefaultValueDefinition::literal('x')), $newDefinition]);
$throwsLaravelGeneration(
    fn () => $s16Generator->generate($s16Planning, $s16Governed, $literalDefinitions, $targetPhysical, 'corr-generation-s16-literal'),
    \OneQay\SchemaPlanning\LaravelMigrationGenerationException::DEFAULT_POLICY_UNSUPPORTED,
);
$requiredDefinitions = new \OneQay\DataDefinition\DataDefinitionManifest([$parentDefinition, $definitionWithDescription($required, $none()), $newDefinition]);
$throwsLaravelGeneration(
    fn () => $s16Generator->generate($s16Planning, $s16Governed, $requiredDefinitions, $targetPhysical, 'corr-generation-s16-required'),
    \OneQay\SchemaPlanning\LaravelMigrationGenerationException::REQUIRED_ATTRIBUTE_UNSAFE,
);

$badSteps = $s16Steps;
$badSteps[1] = new \OneQay\SchemaPlanning\MigrationPlanningStep(
    $s16Steps[1]->sourceChangeIdentifier,
    $s16Steps[1]->kind,
    $s16Steps[1]->entityIdentifier,
    $s16Steps[1]->componentIdentifier,
    null,
    new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('b', 64)),
);
$badPlanning = new \OneQay\SchemaPlanning\MigrationPlanningArtifact(
    $s16Planning->sourcePlanFingerprint,
    $s16Planning->sourceReviewCorrelationId,
    $s16Planning->planningCorrelationId,
    $s16Planning->reviewerReference,
    $s16Planning->baselineFingerprint,
    $s16Planning->targetFingerprint,
    $badSteps,
);
$badGoverned = $bridge->build($badPlanning, 'corr-bridge-s16-bad-after');
$throwsLaravelGeneration(
    fn () => $s16Generator->generate($badPlanning, $badGoverned, $targetDefinitions, $targetPhysical, 'corr-generation-s16-bad-after'),
    \OneQay\SchemaPlanning\LaravelMigrationGenerationException::AFTER_FINGERPRINT_MISMATCH,
);

$s16GeneratorSource = (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/LaravelMigrationGeneration.php');
$assert(!str_contains($s16GeneratorSource, 'new PDO('), 'Sprint 16 generator introduced a database connection.');
$assert(!preg_match('/\b(curl_|fsockopen|stream_socket_client)\b/i', $s16GeneratorSource), 'Sprint 16 generator introduced a network dependency.');
$assert(!preg_match('/\b(file_put_contents|fopen|unlink|mkdir|rename|copy)\b/i', $s16GeneratorSource), 'Sprint 16 generator introduced a filesystem side effect.');
$assert(!preg_match('/\b(exec|shell_exec|system|passthru|proc_open)\s*\(/i', $s16GeneratorSource), 'Sprint 16 generator introduced process execution.');
$assert(!str_contains($s16GeneratorSource, 'MigrationExecutionService'), 'Sprint 16 generator introduced migration execution coupling.');
$assert(!str_contains($s16GeneratorSource, 'apps/web/database'), 'Sprint 16 generator introduced application migration materialization.');

$throwsLaravelMaterialization = static function (callable $callback, string $code) use ($assert): void {
    try {
        $callback();
        $assert(false, "Expected {$code}.");
    } catch (\OneQay\SchemaPlanning\LaravelMigrationMaterializationException $exception) {
        $assert($exception->errorCode === $code, "Unexpected {$exception->errorCode}.");
    }
};

$removeTree = null;
$removeTree = static function (string $path) use (&$removeTree): void {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (!is_dir($path)) {
        return;
    }
    $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        $removeTree($item->getPathname());
    }
    @rmdir($path);
};

$s17Composer = (string) file_get_contents(__DIR__ . '/../apps/web/composer.json');
$s17Parent = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'oneqay-s17-' . getmypid();
$removeTree($s17Parent);
$assert(@mkdir($s17Parent, 0700, false), 'Sprint 17 staging parent could not be created.');

$s17Materializer = new \OneQay\SchemaPlanning\GovernedLaravelMigrationMaterializer();
$s17Report = $s17Materializer->materialize($s16Generated, $s17Composer, $s17Parent, 'corr-materialization-s17');
$s17ArtifactFingerprint = hash(
    'sha256',
    json_encode($s16Generated, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION),
);
$assert((new ReflectionClass($s17Report))->isReadOnly(), 'Sprint 17 materialization report is not immutable.');
$assert($s17Report->generationArtifactFingerprint->value === $s17ArtifactFingerprint, 'Sprint 17 generation artifact fingerprint changed.');
$assert($s17Report->framework === \OneQay\SchemaPlanning\LaravelMigrationGenerationArtifact::FRAMEWORK, 'Sprint 17 framework identity changed.');
$assert($s17Report->frameworkVersion === \OneQay\SchemaPlanning\LaravelMigrationGenerationArtifact::FRAMEWORK_VERSION, 'Sprint 17 framework version changed.');
$expectedWorkspace = '.oneqay-migration-materialization/' . substr($s17ArtifactFingerprint, 0, 24);
$assert($s17Report->workspaceRelativePath === $expectedWorkspace, 'Sprint 17 deterministic workspace identity changed.');
$assert(count($s17Report->files()) === count($s16Generated->files()), 'Sprint 17 persisted file count changed.');

$s17Workspace = $s17Parent . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $s17Report->workspaceRelativePath);
$s17MigrationDirectory = $s17Workspace . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
foreach ($s17Report->files() as $index => $persisted) {
    $generated = $s16Generated->files()[$index];
    $assert((new ReflectionClass($persisted))->isReadOnly(), 'Sprint 17 persisted file evidence is not immutable.');
    $assert(!$persisted->alreadyIdentical, 'Sprint 17 first materialization unexpectedly reported an existing identical file.');
    $assert($persisted->relativePath === $generated->relativePath, 'Sprint 17 persisted relative path changed.');
    $assert($persisted->expectedSourceFingerprint === $generated->sourceFingerprint, 'Sprint 17 expected fingerprint changed.');
    $assert($persisted->persistedSourceFingerprint === $generated->sourceFingerprint, 'Sprint 17 persisted fingerprint changed.');
    $assert($persisted->persistedBytes === strlen($generated->source), 'Sprint 17 persisted byte count changed.');
    $destination = $s17Workspace . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $generated->relativePath);
    $assert(is_file($destination), 'Sprint 17 expected staged migration file is missing.');
    $contents = (string) file_get_contents($destination);
    $assert(hash('sha256', $contents) === $generated->sourceFingerprint, 'Sprint 17 staged source fingerprint changed.');
    token_get_all($contents, TOKEN_PARSE);
    $assert(true, 'Sprint 17 staged migration PHP syntax validation failed.');
}

$s17Second = $s17Materializer->materialize($s16Generated, $s17Composer, $s17Parent, 'corr-materialization-s17-idempotent');
$assert($s17Second->workspaceRelativePath === $s17Report->workspaceRelativePath, 'Sprint 17 idempotent workspace identity changed.');
foreach ($s17Second->files() as $persisted) {
    $assert($persisted->alreadyIdentical, 'Sprint 17 second materialization was not idempotent.');
}

$s17Validated = $s17Materializer->validate($s16Generated, $s17Composer, $s17Parent, 'corr-validation-s17');
$assert($s17Validated->generationArtifactFingerprint->value === $s17ArtifactFingerprint, 'Sprint 17 validation artifact fingerprint changed.');
$assert($s17Validated->workspaceRelativePath === $expectedWorkspace, 'Sprint 17 validation workspace changed.');
$assert(count($s17Validated->files()) === 4, 'Sprint 17 validation file count changed.');

$throwsLaravelMaterialization(
    fn () => $s17Materializer->materialize($s16Generated, '{bad-json', $s17Parent, 'corr-materialization-s17-bad-json'),
    \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::FRAMEWORK_TARGET_MISMATCH,
);
$wrongComposer = json_encode(['require' => ['php' => '^8.2', 'laravel/framework' => '13.0.0']], JSON_THROW_ON_ERROR);
$throwsLaravelMaterialization(
    fn () => $s17Materializer->materialize($s16Generated, $wrongComposer, $s17Parent, 'corr-materialization-s17-wrong-framework'),
    \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::FRAMEWORK_TARGET_MISMATCH,
);
$throwsLaravelMaterialization(
    fn () => $s17Materializer->materialize($s16Generated, $s17Composer, __DIR__ . '/../apps/web', 'corr-materialization-s17-app-root'),
    \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::STAGING_PARENT_INVALID,
);

$unexpected = $s17MigrationDirectory . DIRECTORY_SEPARATOR . 'unexpected.php';
$assert(file_put_contents($unexpected, "<?php\n") !== false, 'Sprint 17 unexpected-file fixture could not be created.');
$throwsLaravelMaterialization(
    fn () => $s17Materializer->validate($s16Generated, $s17Composer, $s17Parent, 'corr-validation-s17-unexpected'),
    \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::UNEXPECTED_FILE,
);
@unlink($unexpected);

$firstGenerated = $s16Generated->files()[0];
$firstDestination = $s17Workspace . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $firstGenerated->relativePath);
$assert(file_put_contents($firstDestination, $firstGenerated->source . "\n// deliberate-s17-tamper\n", LOCK_EX) !== false, 'Sprint 17 tamper fixture could not be created.');
$throwsLaravelMaterialization(
    fn () => $s17Materializer->validate($s16Generated, $s17Composer, $s17Parent, 'corr-validation-s17-tampered'),
    \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::PERSISTED_VALIDATION_MISMATCH,
);
$throwsLaravelMaterialization(
    fn () => $s17Materializer->materialize($s16Generated, $s17Composer, $s17Parent, 'corr-materialization-s17-tampered'),
    \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::EXISTING_CONTENT_MISMATCH,
);
$assert(file_put_contents($firstDestination, $firstGenerated->source, LOCK_EX) === strlen($firstGenerated->source), 'Sprint 17 tamper fixture could not be restored.');
$s17Materializer->validate($s16Generated, $s17Composer, $s17Parent, 'corr-validation-s17-restored');
$assert(true, 'Sprint 17 restored workspace failed validation.');

$assert(@unlink($firstDestination), 'Sprint 17 missing-file fixture could not be created.');
$throwsLaravelMaterialization(
    fn () => $s17Materializer->validate($s16Generated, $s17Composer, $s17Parent, 'corr-validation-s17-missing'),
    \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::MISSING_FILE,
);
$s17Rematerialized = $s17Materializer->materialize($s16Generated, $s17Composer, $s17Parent, 'corr-materialization-s17-rematerialize');
$assert(count($s17Rematerialized->files()) === 4, 'Sprint 17 governed re-materialization did not restore the expected file set.');
$assert(hash_file('sha256', $firstDestination) === $firstGenerated->sourceFingerprint, 'Sprint 17 re-materialized file fingerprint changed.');

if (function_exists('symlink')) {
    $linkPath = $s17MigrationDirectory . DIRECTORY_SEPARATOR . 'unexpected-link.php';
    if (@symlink($firstDestination, $linkPath)) {
        $throwsLaravelMaterialization(
            fn () => $s17Materializer->validate($s16Generated, $s17Composer, $s17Parent, 'corr-validation-s17-symlink'),
            \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::SYMLINK_DENIED,
        );
        @unlink($linkPath);
    } else {
        $assert(true, 'Sprint 17 symlink fixture is unavailable on this runtime.');
    }
}

$syntaxFile = new \OneQay\SchemaPlanning\LaravelMigrationFileArtifact(
    $firstGenerated->sourceChangeIdentifier,
    $firstGenerated->migrationIdentifier,
    $s16Generated->generationCorrelationId,
    'database/migrations/0000_00_00_000001_entity_created_555555555555.php',
    "<?php\nreturn new class extends;\n",
);
$syntaxArtifact = new \OneQay\SchemaPlanning\LaravelMigrationGenerationArtifact(
    $s16Generated->sourcePlanningArtifactFingerprint,
    $s16Generated->targetDefinitionFingerprint,
    $s16Generated->targetManifestFingerprint,
    $s16Generated->generationCorrelationId,
    [$syntaxFile],
);
$throwsLaravelMaterialization(
    fn () => $s17Materializer->materialize($syntaxArtifact, $s17Composer, $s17Parent, 'corr-materialization-s17-syntax'),
    \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::SYNTAX_INVALID,
);

$shapeSource = str_replace(
    'use Illuminate\\Support\\Facades\\Schema;',
    'use Illuminate\\Support\\Facades\\DB;',
    $firstGenerated->source,
);
$shapeFile = new \OneQay\SchemaPlanning\LaravelMigrationFileArtifact(
    $firstGenerated->sourceChangeIdentifier,
    $firstGenerated->migrationIdentifier,
    $s16Generated->generationCorrelationId,
    $firstGenerated->relativePath,
    $shapeSource,
);
$shapeArtifact = new \OneQay\SchemaPlanning\LaravelMigrationGenerationArtifact(
    $s16Generated->sourcePlanningArtifactFingerprint,
    $s16Generated->targetDefinitionFingerprint,
    $s16Generated->targetManifestFingerprint,
    $s16Generated->generationCorrelationId,
    [$shapeFile],
);
$throwsLaravelMaterialization(
    fn () => $s17Materializer->materialize($shapeArtifact, $s17Composer, $s17Parent, 'corr-materialization-s17-shape'),
    \OneQay\SchemaPlanning\LaravelMigrationMaterializationException::SOURCE_SHAPE_INVALID,
);

$s17MaterializerSource = (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/LaravelMigrationMaterialization.php');
$assert(str_contains($s17MaterializerSource, 'token_get_all($source, TOKEN_PARSE)'), 'Sprint 17 in-process PHP parser boundary is missing.');
$assert(str_contains($s17MaterializerSource, 'file_put_contents($destination, $file->source, LOCK_EX)'), 'Sprint 17 exact local materialization boundary is missing.');
$assert(!str_contains($s17MaterializerSource, 'MigrationExecutionService'), 'Sprint 17 materializer introduced migration execution coupling.');
$assert(!str_contains($s17MaterializerSource, 'apps/web/database/migrations'), 'Sprint 17 materializer introduced direct application migration installation.');
$assert(!str_contains($s17MaterializerSource, 'PHP_BINARY'), 'Sprint 17 materializer introduced subprocess PHP execution.');
$tokens = token_get_all($s17MaterializerSource);
$forbiddenCalls = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'curl_init', 'fsockopen', 'stream_socket_client'];
for ($i = 0, $tokenCount = count($tokens); $i < $tokenCount; $i++) {
    $token = $tokens[$i];
    if (!is_array($token) || $token[0] !== T_STRING || !in_array(strtolower($token[1]), $forbiddenCalls, true)) {
        continue;
    }
    $j = $i + 1;
    while ($j < $tokenCount && is_array($tokens[$j]) && in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
        $j++;
    }
    $assert($j >= $tokenCount || $tokens[$j] !== '(', 'Sprint 17 materializer introduced a forbidden process or network call.');
}

$removeTree($s17Parent);
$assert(!file_exists($s17Parent), 'Sprint 17 temporary staging workspace cleanup failed.');

fwrite(STDOUT, sprintf("Migration Governance and Safety tests passed: %d assertions.\n", $assertions));

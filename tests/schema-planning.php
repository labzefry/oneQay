<?php

declare(strict_types=1);

require __DIR__ . '/../src/SchemaPlanning/Foundation.php';

use OneQay\DataDefinition\DataDefinitionIdentifier;
use OneQay\DataDefinition\PortableScalarType;
use OneQay\DataDefinition\TenantScope;
use OneQay\PhysicalMapping\CharsetPolicy;
use OneQay\PhysicalMapping\CollationPolicy;
use OneQay\PhysicalMapping\IndexKind;
use OneQay\PhysicalMapping\PhysicalAttributeMapping;
use OneQay\PhysicalMapping\PhysicalEntityMapping;
use OneQay\PhysicalMapping\PhysicalIdentifier;
use OneQay\PhysicalMapping\PhysicalIndexMapping;
use OneQay\PhysicalMapping\PhysicalMappingManifest;
use OneQay\PhysicalMapping\PhysicalReferenceMapping;
use OneQay\PhysicalMapping\PhysicalScalarMapping;
use OneQay\PhysicalMapping\PhysicalTypeIdentifier;
use OneQay\PhysicalMapping\VendorIdentifier;
use OneQay\SchemaPlanning\ChangeRisk;
use OneQay\SchemaPlanning\CorrelationId;
use OneQay\SchemaPlanning\DeterministicPhysicalSchemaPlanner;
use OneQay\SchemaPlanning\ManifestFingerprint;
use OneQay\SchemaPlanning\PhysicalSchemaChange;
use OneQay\SchemaPlanning\PhysicalSchemaPlan;
use OneQay\SchemaPlanning\PlanDisposition;
use OneQay\SchemaPlanning\SchemaChangeKind;
use OneQay\SchemaPlanning\SchemaPlanningException;
use OneQay\SchemaPlanning\StableChangeIdentifier;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};
$throwsPlanningCode = static function (callable $callback, string $code) use ($assert): void {
    try {
        $callback();
        $assert(false, "Expected {$code}.");
    } catch (SchemaPlanningException $exception) {
        $assert($exception->errorCode === $code, "Unexpected {$exception->errorCode}.");
    }
};
$kindValues = static fn ($plan): array => array_map(static fn ($change): string => $change->kind->value, $plan->changes());

$id = static fn (string $value): DataDefinitionIdentifier => new DataDefinitionIdentifier($value);
$physical = static fn (string $value): PhysicalIdentifier => new PhysicalIdentifier($value);
$charset = new CharsetPolicy(CharsetPolicy::UTF8MB4);
$unicode = new CollationPolicy(CollationPolicy::UNICODE_CI);
$binary = new CollationPolicy(CollationPolicy::BINARY);
$uuidMap = static fn (): PhysicalScalarMapping => new PhysicalScalarMapping(
    new PortableScalarType(PortableScalarType::UUID),
    new PhysicalTypeIdentifier(PhysicalTypeIdentifier::CHAR_UUID),
    36,
    null,
    null,
    $charset,
    $binary,
);
$stringMap = static fn (int $length = 128): PhysicalScalarMapping => new PhysicalScalarMapping(
    new PortableScalarType(PortableScalarType::STRING),
    new PhysicalTypeIdentifier(PhysicalTypeIdentifier::VARCHAR),
    $length,
    null,
    null,
    $charset,
    $unicode,
);
$integerMap = static fn (): PhysicalScalarMapping => new PhysicalScalarMapping(
    new PortableScalarType(PortableScalarType::INTEGER),
    new PhysicalTypeIdentifier(PhysicalTypeIdentifier::BIGINT_SIGNED),
);
$attribute = static fn (string $logical, string $name, PhysicalScalarMapping $map): PhysicalAttributeMapping => new PhysicalAttributeMapping($id($logical), $physical($name), $map);
$index = static fn (string $name, IndexKind $kind, array $attributes): PhysicalIndexMapping => new PhysicalIndexMapping($physical($name), $kind, $attributes);
$reference = static fn (string $name, string $target, array $map): PhysicalReferenceMapping => new PhysicalReferenceMapping($physical($name), $id($target), $map);
$vendor = new VendorIdentifier(VendorIdentifier::MARIADB_11);
$manifest = static fn (array $entities): PhysicalMappingManifest => new PhysicalMappingManifest($vendor, $entities);

$catalog = static function (
    string $physicalName = 'reference_catalog',
    int $codeLength = 128,
    bool $withUnique = true,
    array $primary = ['ID'],
    ?array $uniqueAttributes = null,
    bool $reordered = false,
) use ($attribute, $uuidMap, $stringMap, $id, $physical, $index): PhysicalEntityMapping {
    $attributes = [
        $attribute('ID', 'id', $uuidMap()),
        $attribute('CODE', 'code', $stringMap($codeLength)),
    ];
    if ($reordered) {
        $attributes = array_reverse($attributes);
    }
    $unique = $withUnique
        ? [$index('uq_reference_catalog_code', IndexKind::UNIQUE, $uniqueAttributes ?? ['CODE'])]
        : [];
    return new PhysicalEntityMapping(
        $id('REFERENCE_CATALOG'),
        $physical($physicalName),
        TenantScope::GLOBAL,
        $attributes,
        $index('pk_reference_catalog', IndexKind::PRIMARY, $primary),
        $unique,
    );
};
$tenant = static function (
    bool $withDescription = false,
    int $codeLength = 128,
    ?PhysicalScalarMapping $codeMap = null,
    array $primary = ['TENANT_ID', 'RECORD_ID'],
    bool $withUnique = true,
    ?array $uniqueAttributes = null,
    TenantScope $scope = TenantScope::TENANT_SCOPED,
    ?string $tenantKey = 'TENANT_ID',
    bool $reordered = false,
    array $references = [],
    bool $withOwner = false,
) use ($attribute, $uuidMap, $stringMap, $id, $physical, $index): PhysicalEntityMapping {
    $attributes = [
        $attribute('TENANT_ID', 'tenant_id', $uuidMap()),
        $attribute('RECORD_ID', 'record_id', $uuidMap()),
        $attribute('CODE', 'code', $codeMap ?? $stringMap($codeLength)),
    ];
    if ($withDescription) {
        $attributes[] = $attribute('DESCRIPTION', 'description', $stringMap(256));
    }
    if ($withOwner) {
        $attributes[] = $attribute('OWNER_ID', 'owner_id', $uuidMap());
    }
    if ($reordered) {
        $attributes = array_reverse($attributes);
    }
    $unique = $withUnique
        ? [$index('uq_tenant_record_code', IndexKind::UNIQUE, $uniqueAttributes ?? ['TENANT_ID', 'CODE'])]
        : [];
    return new PhysicalEntityMapping(
        $id('TENANT_RECORD'),
        $physical('tenant_record'),
        $scope,
        $attributes,
        $index('pk_tenant_record', IndexKind::PRIMARY, $primary),
        $unique,
        $references,
        $tenantKey === null ? null : $id($tenantKey),
    );
};
$child = static function (array $references = [], bool $twoParents = false) use ($attribute, $uuidMap, $id, $physical, $index): PhysicalEntityMapping {
    $attributes = [
        $attribute('ID', 'id', $uuidMap()),
        $attribute('PARENT_ID', 'parent_id', $uuidMap()),
    ];
    if ($twoParents) {
        $attributes[] = $attribute('ALT_PARENT_ID', 'alt_parent_id', $uuidMap());
    }
    return new PhysicalEntityMapping(
        $id('CHILD_RECORD'),
        $physical('child_record'),
        TenantScope::GLOBAL,
        $attributes,
        $index('pk_child_record', IndexKind::PRIMARY, ['ID']),
        [],
        $references,
    );
};
$extra = static function () use ($attribute, $uuidMap, $id, $physical, $index): PhysicalEntityMapping {
    return new PhysicalEntityMapping(
        $id('EXTRA_ENTITY'),
        $physical('extra_entity'),
        TenantScope::GLOBAL,
        [$attribute('ID', 'id', $uuidMap())],
        $index('pk_extra_entity', IndexKind::PRIMARY, ['ID']),
    );
};

$planner = new DeterministicPhysicalSchemaPlanner();
$baseline = $manifest([$catalog(), $tenant()]);
$reordered = $manifest([$tenant(reordered: true), $catalog(reordered: true)]);
$noChanges = $planner->plan($baseline, $reordered, 'corr-schema-001');
$assert($noChanges->disposition === PlanDisposition::NO_CHANGES, 'Equivalent reordered manifests were not NO_CHANGES.');
$assert($noChanges->baselineFingerprint->equals($noChanges->targetFingerprint), 'Equivalent reordered manifest fingerprints differ.');
$assert($noChanges->changes() === [], 'Equivalent reordered manifests produced changes.');
$assert(json_encode($noChanges, JSON_THROW_ON_ERROR) === json_encode($planner->plan($baseline, $reordered, 'corr-schema-001'), JSON_THROW_ON_ERROR), 'Identical planning input is not deterministic.');

$entityAdded = $planner->plan($baseline, $manifest([$catalog(), $tenant(), $extra()]), 'corr-schema-002');
$assert($entityAdded->disposition === PlanDisposition::REVIEW_REQUIRED, 'Entity creation was not REVIEW_REQUIRED.');
$assert(in_array(SchemaChangeKind::ENTITY_CREATED->value, $kindValues($entityAdded), true), 'Entity creation kind missing.');

$attributeAdded = $planner->plan($baseline, $manifest([$catalog(), $tenant(withDescription: true)]), 'corr-schema-003');
$assert($attributeAdded->disposition === PlanDisposition::REVIEW_REQUIRED, 'Attribute addition was not REVIEW_REQUIRED.');
$assert(in_array(SchemaChangeKind::ATTRIBUTE_ADDED->value, $kindValues($attributeAdded), true), 'Attribute addition kind missing.');

$entityRemoved = $planner->plan($manifest([$catalog(), $tenant(), $extra()]), $baseline, 'corr-schema-004');
$assert($entityRemoved->disposition === PlanDisposition::BLOCKED, 'Entity removal was not BLOCKED.');
$assert(in_array(SchemaChangeKind::ENTITY_REMOVED->value, $kindValues($entityRemoved), true), 'Entity removal kind missing.');

$entityPhysicalChanged = $planner->plan($baseline, $manifest([$catalog(physicalName: 'reference_catalog_v2'), $tenant()]), 'corr-schema-005');
$assert($entityPhysicalChanged->disposition === PlanDisposition::BLOCKED, 'Entity physical identifier change was not BLOCKED.');
$assert(in_array(SchemaChangeKind::ENTITY_PHYSICAL_IDENTIFIER_CHANGED->value, $kindValues($entityPhysicalChanged), true), 'Entity physical identifier kind missing.');

$physicalMappingChanged = $planner->plan($baseline, $manifest([$catalog(codeLength: 256), $tenant()]), 'corr-schema-006');
$assert(in_array(SchemaChangeKind::ATTRIBUTE_PHYSICAL_MAPPING_CHANGED->value, $kindValues($physicalMappingChanged), true), 'Attribute physical mapping change kind missing.');
$assert($physicalMappingChanged->disposition === PlanDisposition::BLOCKED, 'Attribute physical mapping change was not BLOCKED.');

$scalarMappingChanged = $planner->plan($baseline, $manifest([$catalog(), $tenant(codeMap: $integerMap())]), 'corr-schema-007');
$assert(in_array(SchemaChangeKind::ATTRIBUTE_SCALAR_MAPPING_CHANGED->value, $kindValues($scalarMappingChanged), true), 'Attribute scalar mapping change kind missing.');
$assert($scalarMappingChanged->disposition === PlanDisposition::BLOCKED, 'Attribute scalar mapping change was not BLOCKED.');

$primaryChanged = $planner->plan($baseline, $manifest([$catalog(primary: ['CODE']), $tenant()]), 'corr-schema-008');
$assert(in_array(SchemaChangeKind::PRIMARY_INDEX_CHANGED->value, $kindValues($primaryChanged), true), 'Primary index change kind missing.');
$assert($primaryChanged->disposition === PlanDisposition::BLOCKED, 'Primary index change was not BLOCKED.');

$uniqueAdded = $planner->plan($manifest([$catalog(withUnique: false), $tenant()]), $baseline, 'corr-schema-009');
$assert(in_array(SchemaChangeKind::UNIQUE_INDEX_ADDED->value, $kindValues($uniqueAdded), true), 'Unique index addition kind missing.');
$assert($uniqueAdded->disposition === PlanDisposition::REVIEW_REQUIRED, 'Unique index addition was not REVIEW_REQUIRED.');
$uniqueRemoved = $planner->plan($baseline, $manifest([$catalog(withUnique: false), $tenant()]), 'corr-schema-010');
$assert(in_array(SchemaChangeKind::UNIQUE_INDEX_REMOVED->value, $kindValues($uniqueRemoved), true), 'Unique index removal kind missing.');
$assert($uniqueRemoved->disposition === PlanDisposition::BLOCKED, 'Unique index removal was not BLOCKED.');
$uniqueChanged = $planner->plan($baseline, $manifest([$catalog(uniqueAttributes: ['ID']), $tenant()]), 'corr-schema-011');
$assert(in_array(SchemaChangeKind::UNIQUE_INDEX_CHANGED->value, $kindValues($uniqueChanged), true), 'Unique index mutation kind missing.');
$assert($uniqueChanged->disposition === PlanDisposition::BLOCKED, 'Unique index mutation was not BLOCKED.');

$parentReference = $reference('fk_child_parent', 'REFERENCE_CATALOG', ['PARENT_ID' => 'ID']);
$referenceAdded = $planner->plan(
    $manifest([$catalog(), $tenant(), $child()]),
    $manifest([$catalog(), $tenant(), $child([$parentReference])]),
    'corr-schema-012',
);
$assert(in_array(SchemaChangeKind::REFERENCE_ADDED->value, $kindValues($referenceAdded), true), 'Reference addition kind missing.');
$assert($referenceAdded->disposition === PlanDisposition::REVIEW_REQUIRED, 'Reference addition was not REVIEW_REQUIRED.');
$referenceRemoved = $planner->plan(
    $manifest([$catalog(), $tenant(), $child([$parentReference])]),
    $manifest([$catalog(), $tenant(), $child()]),
    'corr-schema-013',
);
$assert(in_array(SchemaChangeKind::REFERENCE_REMOVED->value, $kindValues($referenceRemoved), true), 'Reference removal kind missing.');
$assert($referenceRemoved->disposition === PlanDisposition::BLOCKED, 'Reference removal was not BLOCKED.');
$altReference = $reference('fk_child_parent', 'REFERENCE_CATALOG', ['ALT_PARENT_ID' => 'ID']);
$referenceChanged = $planner->plan(
    $manifest([$catalog(), $tenant(), $child([$parentReference], true)]),
    $manifest([$catalog(), $tenant(), $child([$altReference], true)]),
    'corr-schema-014',
);
$assert(in_array(SchemaChangeKind::REFERENCE_CHANGED->value, $kindValues($referenceChanged), true), 'Reference mutation kind missing.');
$assert($referenceChanged->disposition === PlanDisposition::BLOCKED, 'Reference mutation was not BLOCKED.');

$scopeBaseline = $manifest([$catalog(), $tenant(scope: TenantScope::GLOBAL, tenantKey: null, primary: ['RECORD_ID'], withUnique: false)]);
$scopeTarget = $manifest([$catalog(), $tenant(scope: TenantScope::TENANT_SCOPED, tenantKey: 'TENANT_ID', primary: ['TENANT_ID', 'RECORD_ID'], withUnique: false)]);
$scopeChanged = $planner->plan($scopeBaseline, $scopeTarget, 'corr-schema-015');
$assert(in_array(SchemaChangeKind::TENANT_SCOPE_CHANGED->value, $kindValues($scopeChanged), true), 'Tenant scope change kind missing.');
$assert($scopeChanged->disposition === PlanDisposition::BLOCKED, 'Tenant scope change was not BLOCKED.');

$tenantKeyChanged = $planner->plan(
    $manifest([$catalog(), $tenant(withUnique: false, withOwner: true)]),
    $manifest([$catalog(), $tenant(primary: ['OWNER_ID', 'RECORD_ID'], withUnique: false, tenantKey: 'OWNER_ID', withOwner: true)]),
    'corr-schema-016',
);
$assert(in_array(SchemaChangeKind::TENANT_KEY_CHANGED->value, $kindValues($tenantKeyChanged), true), 'Tenant key change kind missing.');
$assert($tenantKeyChanged->disposition === PlanDisposition::BLOCKED, 'Tenant key change was not BLOCKED.');

$invalidTenant = $tenant(scope: TenantScope::TENANT_SCOPED, tenantKey: null, withUnique: false);
$throwsPlanningCode(
    fn () => $planner->plan($manifest([$catalog(), $invalidTenant]), $baseline, 'corr-schema-017'),
    SchemaPlanningException::MANIFEST_INCOMPATIBLE,
);
$throwsPlanningCode(
    fn () => $planner->plan($baseline, $baseline, '   '),
    SchemaPlanningException::CORRELATION_ID_INVALID,
);
$throwsPlanningCode(
    fn () => $planner->plan($baseline, $baseline, 'unsafe/path'),
    SchemaPlanningException::CORRELATION_ID_INVALID,
);

$manualBefore = new ManifestFingerprint(str_repeat('1', 64));
$manualAfter = new ManifestFingerprint(str_repeat('2', 64));
$manualChange = new PhysicalSchemaChange(
    new StableChangeIdentifier(str_repeat('3', 64)),
    SchemaChangeKind::ENTITY_REMOVED,
    ChangeRisk::BLOCKED,
    'MANUAL_ENTITY',
    null,
    $manualBefore,
    null,
);
$throwsPlanningCode(
    fn () => new PhysicalSchemaPlan(
        $manualBefore,
        $manualAfter,
        PlanDisposition::REVIEW_REQUIRED,
        new CorrelationId('corr-schema-invariant-001'),
        [$manualChange],
    ),
    SchemaPlanningException::PLAN_INVALID,
);
$throwsPlanningCode(
    fn () => new PhysicalSchemaPlan(
        $manualBefore,
        $manualAfter,
        PlanDisposition::BLOCKED,
        new CorrelationId('corr-schema-invariant-002'),
        [$manualChange, $manualChange],
    ),
    SchemaPlanningException::PLAN_INVALID,
);
$throwsPlanningCode(
    fn () => new PhysicalSchemaPlan(
        $manualBefore,
        $manualAfter,
        PlanDisposition::NO_CHANGES,
        new CorrelationId('corr-schema-invariant-003'),
        [],
    ),
    SchemaPlanningException::PLAN_INVALID,
);

$multipleTarget = $manifest([$catalog(codeLength: 256, withUnique: false), $tenant(withDescription: true)]);
$first = $planner->plan($baseline, $multipleTarget, 'corr-schema-018');
$second = $planner->plan($baseline, $multipleTarget, 'corr-schema-018');
$assert(json_encode($first, JSON_THROW_ON_ERROR) === json_encode($second, JSON_THROW_ON_ERROR), 'Stable change identifiers or ordering changed.');
$sortKeys = array_map(static fn ($change): string => $change->sortKey(), $first->changes());
$sortedKeys = $sortKeys;
sort($sortedKeys, SORT_STRING);
$assert($sortKeys === $sortedKeys, 'Changes are not stably ordered.');

$encoded = json_encode($first, JSON_THROW_ON_ERROR);
foreach (['DB_PASSWORD', 'DB_USER', 'DB_HOST', 'jdbc:', 'mysql:host=', 'CREATE TABLE', 'ALTER TABLE', 'DROP TABLE'] as $forbidden) {
    $assert(!str_contains($encoded, $forbidden), 'Plan output contains forbidden material.');
}
$source = implode("\n", [
    (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/Foundation.php'),
    (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/ValueObjects.php'),
    (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/Contracts.php'),
    (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/Planning.php'),
]);
$assert(!preg_match('/\b(CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|INSERT\s+INTO|DELETE\s+FROM)\b/i', $source), 'Executable SQL was introduced.');
$assert(!str_contains($source, 'new PDO('), 'Database connection was introduced.');
$assert(!preg_match('/\b(curl_|fsockopen|stream_socket_client)\b/i', $source), 'Network dependency was introduced.');
$assert(str_contains($source, 'SchemaChangeKind::VENDOR_CHANGED') && str_contains($source, 'ChangeRisk::BLOCKED'), 'Vendor change deny path is missing.');
$assert(!preg_match('/\b(pos|sale|payment|inventory)\b/i', $source), 'Business-module behavior was introduced.');

$throwsReviewCode = static function (callable $callback, string $code) use ($assert): void {
    try {
        $callback();
        $assert(false, "Expected {$code}.");
    } catch (\OneQay\SchemaPlanning\SchemaReviewException $exception) {
        $assert($exception->errorCode === $code, "Unexpected {$exception->errorCode}.");
    }
};
$reviewer = new \OneQay\SchemaPlanning\DeterministicSchemaChangeReviewer();
$notRequired = $reviewer->review(
    $noChanges,
    'reviewer.system',
    'corr-review-001',
    \OneQay\SchemaPlanning\ReviewDecision::NOT_REQUIRED,
    \OneQay\SchemaPlanning\ReviewReasonCode::NO_CHANGES,
);
$assert($notRequired->decision === \OneQay\SchemaPlanning\ReviewDecision::NOT_REQUIRED, 'NO_CHANGES did not produce NOT_REQUIRED.');
$assert($notRequired->sourceDisposition === PlanDisposition::NO_CHANGES, 'NO_CHANGES source disposition was not preserved.');

$approvedReview = $reviewer->review(
    $entityAdded,
    'zefriansyah',
    'corr-review-002',
    \OneQay\SchemaPlanning\ReviewDecision::APPROVED_FOR_MIGRATION_PLANNING,
    \OneQay\SchemaPlanning\ReviewReasonCode::REVIEW_APPROVED,
);
$assert($approvedReview->decision === \OneQay\SchemaPlanning\ReviewDecision::APPROVED_FOR_MIGRATION_PLANNING, 'REVIEW_REQUIRED plan was not approved for migration planning.');
$assert($approvedReview->sourceDisposition === PlanDisposition::REVIEW_REQUIRED, 'REVIEW_REQUIRED source disposition was not preserved.');
$assert($approvedReview->sourceCorrelationId->value === $entityAdded->correlationId->value, 'Source correlation ID was not preserved.');
$expectedPlanFingerprint = hash(
    'sha256',
    json_encode($entityAdded, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION),
);
$assert($approvedReview->sourcePlanFingerprint->value === $expectedPlanFingerprint, 'Source plan fingerprint was not preserved.');
$approvedAgain = $reviewer->review(
    $entityAdded,
    'zefriansyah',
    'corr-review-002',
    'APPROVED_FOR_MIGRATION_PLANNING',
    'REVIEW_APPROVED',
);
$assert(json_encode($approvedReview, JSON_THROW_ON_ERROR) === json_encode($approvedAgain, JSON_THROW_ON_ERROR), 'Equivalent review input is not deterministic.');
$assert((new ReflectionClass($approvedReview))->isReadOnly(), 'Review envelope is not immutable.');

$rejectedReview = $reviewer->review(
    $attributeAdded,
    'zefriansyah',
    'corr-review-003',
    \OneQay\SchemaPlanning\ReviewDecision::REJECTED,
    \OneQay\SchemaPlanning\ReviewReasonCode::REVIEW_REJECTED,
);
$assert($rejectedReview->decision === \OneQay\SchemaPlanning\ReviewDecision::REJECTED, 'REVIEW_REQUIRED rejection failed.');

$blockedRejected = $reviewer->review(
    $entityRemoved,
    'zefriansyah',
    'corr-review-004',
    \OneQay\SchemaPlanning\ReviewDecision::REJECTED,
    \OneQay\SchemaPlanning\ReviewReasonCode::PLAN_BLOCKED,
);
$assert($blockedRejected->decision === \OneQay\SchemaPlanning\ReviewDecision::REJECTED, 'BLOCKED plan did not remain rejected.');
$throwsReviewCode(
    fn () => $reviewer->review($entityRemoved, 'zefriansyah', 'corr-review-005', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED'),
    \OneQay\SchemaPlanning\SchemaReviewException::BLOCKED_APPROVAL_DENIED,
);
$throwsReviewCode(
    fn () => $reviewer->review($scopeChanged, 'zefriansyah', 'corr-review-006', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED'),
    \OneQay\SchemaPlanning\SchemaReviewException::BLOCKED_APPROVAL_DENIED,
);
$throwsReviewCode(
    fn () => $reviewer->review($tenantKeyChanged, 'zefriansyah', 'corr-review-007', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED'),
    \OneQay\SchemaPlanning\SchemaReviewException::BLOCKED_APPROVAL_DENIED,
);
$throwsReviewCode(
    fn () => $reviewer->review($noChanges, 'zefriansyah', 'corr-review-008', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED'),
    \OneQay\SchemaPlanning\SchemaReviewException::DECISION_NOT_ALLOWED,
);
$throwsReviewCode(
    fn () => $reviewer->review($entityAdded, 'zefriansyah', 'corr-review-009', 'NOT_REQUIRED', 'NO_CHANGES'),
    \OneQay\SchemaPlanning\SchemaReviewException::DECISION_NOT_ALLOWED,
);
$throwsReviewCode(
    fn () => $reviewer->review($entityAdded, 'zefriansyah', 'corr-review-010', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_REJECTED'),
    \OneQay\SchemaPlanning\SchemaReviewException::REASON_CODE_NOT_ALLOWED,
);
$throwsReviewCode(
    fn () => $reviewer->review($entityAdded, 'unsafe/reviewer', 'corr-review-011', 'REJECTED', 'REVIEW_REJECTED'),
    \OneQay\SchemaPlanning\SchemaReviewException::REVIEWER_REFERENCE_INVALID,
);
$throwsReviewCode(
    fn () => $reviewer->review($entityAdded, 'zefriansyah', 'corr-review-012', 'APPROVE', 'REVIEW_APPROVED'),
    \OneQay\SchemaPlanning\SchemaReviewException::DECISION_INVALID,
);
$throwsReviewCode(
    fn () => $reviewer->review($entityAdded, 'zefriansyah', 'corr-review-013', 'REJECTED', 'free form'),
    \OneQay\SchemaPlanning\SchemaReviewException::REASON_CODE_INVALID,
);
$throwsPlanningCode(
    fn () => $reviewer->review($entityAdded, 'zefriansyah', 'unsafe/path', 'REJECTED', 'REVIEW_REJECTED'),
    SchemaPlanningException::CORRELATION_ID_INVALID,
);

$reviewEncoded = json_encode($approvedReview, JSON_THROW_ON_ERROR);
foreach (['DB_PASSWORD', 'DB_USER', 'DB_HOST', 'jdbc:', 'mysql:host=', 'CREATE TABLE', 'ALTER TABLE', 'DROP TABLE', '/var/', 'tenant_record', 'reference_catalog'] as $forbidden) {
    $assert(!str_contains($reviewEncoded, $forbidden), 'Review output contains forbidden material.');
}
$reviewSource = (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/Review.php');
$assert(!preg_match('/\b(CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|INSERT\s+INTO|DELETE\s+FROM)\b/i', $reviewSource), 'Review foundation contains executable SQL.');
$assert(!str_contains($reviewSource, 'new PDO('), 'Review foundation introduced a database connection.');
$assert(!preg_match('/\b(curl_|fsockopen|stream_socket_client)\b/i', $reviewSource), 'Review foundation introduced a network dependency.');
$assert(!preg_match('/\b(file_put_contents|fopen|unlink|mkdir|rename)\b/i', $reviewSource), 'Review foundation introduced a filesystem side effect.');

$throwsMigrationPlanningCode = static function (callable $callback, string $code) use ($assert): void {
    try {
        $callback();
        $assert(false, "Expected {$code}.");
    } catch (\OneQay\SchemaPlanning\MigrationPlanningException $exception) {
        $assert($exception->errorCode === $code, "Unexpected {$exception->errorCode}.");
    }
};

$migrationBuilder = new \OneQay\SchemaPlanning\DeterministicMigrationPlanningArtifactBuilder();
$entityMigration = $migrationBuilder->build($entityAdded, $approvedReview, 'corr-migration-001');
$entityMigrationAgain = $migrationBuilder->build($entityAdded, $approvedReview, 'corr-migration-001');
$assert(json_encode($entityMigration, JSON_THROW_ON_ERROR) === json_encode($entityMigrationAgain, JSON_THROW_ON_ERROR), 'Migration planning output is not deterministic.');
$assert((new ReflectionClass($entityMigration))->isReadOnly(), 'Migration planning artifact is not immutable.');
$assert(count($entityMigration->steps()) === 1, 'Entity-add planning step count is invalid.');
$entityStep = $entityMigration->steps()[0];
$assert((new ReflectionClass($entityStep))->isReadOnly(), 'Migration planning step is not immutable.');
$assert($entityStep->kind === SchemaChangeKind::ENTITY_CREATED, 'Entity-add planning kind is invalid.');
$assert($entityStep->sourceChangeIdentifier->value === $entityAdded->changes()[0]->identifier->value, 'Source change identifier was not preserved.');
$assert($entityMigration->sourcePlanFingerprint->value === $approvedReview->sourcePlanFingerprint->value, 'Source plan fingerprint was not preserved in migration planning.');
$assert($entityMigration->sourceReviewCorrelationId->value === $approvedReview->correlationId->value, 'Source review correlation was not preserved.');
$assert($entityMigration->reviewerReference->value === $approvedReview->reviewerReference->value, 'Reviewer reference was not preserved.');

$approvedAttributeReview = $reviewer->review($attributeAdded, 'zefriansyah', 'corr-review-migration-attribute', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED');
$attributeMigration = $migrationBuilder->build($attributeAdded, $approvedAttributeReview, 'corr-migration-002');
$assert($attributeMigration->steps()[0]->kind === SchemaChangeKind::ATTRIBUTE_ADDED, 'Attribute-add planning kind is invalid.');

$approvedUniqueReview = $reviewer->review($uniqueAdded, 'zefriansyah', 'corr-review-migration-unique', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED');
$uniqueMigration = $migrationBuilder->build($uniqueAdded, $approvedUniqueReview, 'corr-migration-003');
$assert($uniqueMigration->steps()[0]->kind === SchemaChangeKind::UNIQUE_INDEX_ADDED, 'Unique-index-add planning kind is invalid.');

$approvedReferenceReview = $reviewer->review($referenceAdded, 'zefriansyah', 'corr-review-migration-reference', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED');
$referenceMigration = $migrationBuilder->build($referenceAdded, $approvedReferenceReview, 'corr-migration-004');
$assert($referenceMigration->steps()[0]->kind === SchemaChangeKind::REFERENCE_ADDED, 'Reference-add planning kind is invalid.');

$throwsMigrationPlanningCode(
    fn () => $migrationBuilder->build($attributeAdded, $rejectedReview, 'corr-migration-rejected'),
    \OneQay\SchemaPlanning\MigrationPlanningException::REVIEW_NOT_APPROVED,
);
$throwsMigrationPlanningCode(
    fn () => $migrationBuilder->build($noChanges, $notRequired, 'corr-migration-not-required'),
    \OneQay\SchemaPlanning\MigrationPlanningException::PLAN_DISPOSITION_INVALID,
);
$throwsMigrationPlanningCode(
    fn () => $migrationBuilder->build($entityRemoved, $blockedRejected, 'corr-migration-blocked'),
    \OneQay\SchemaPlanning\MigrationPlanningException::PLAN_DISPOSITION_INVALID,
);

$fingerprintMismatchReview = new \OneQay\SchemaPlanning\SchemaChangeReviewEnvelope(
    new \OneQay\SchemaPlanning\PlanFingerprint(str_repeat('f', 64)),
    PlanDisposition::REVIEW_REQUIRED,
    $entityAdded->correlationId,
    new CorrelationId('corr-review-migration-fingerprint'),
    new \OneQay\SchemaPlanning\ReviewerReference('zefriansyah'),
    \OneQay\SchemaPlanning\ReviewDecision::APPROVED_FOR_MIGRATION_PLANNING,
    \OneQay\SchemaPlanning\ReviewReasonCode::REVIEW_APPROVED,
);
$throwsMigrationPlanningCode(
    fn () => $migrationBuilder->build($entityAdded, $fingerprintMismatchReview, 'corr-migration-fingerprint'),
    \OneQay\SchemaPlanning\MigrationPlanningException::SOURCE_PLAN_FINGERPRINT_MISMATCH,
);

$correlationMismatchReview = new \OneQay\SchemaPlanning\SchemaChangeReviewEnvelope(
    $approvedReview->sourcePlanFingerprint,
    PlanDisposition::REVIEW_REQUIRED,
    new CorrelationId('corr-schema-mismatch'),
    new CorrelationId('corr-review-migration-correlation'),
    new \OneQay\SchemaPlanning\ReviewerReference('zefriansyah'),
    \OneQay\SchemaPlanning\ReviewDecision::APPROVED_FOR_MIGRATION_PLANNING,
    \OneQay\SchemaPlanning\ReviewReasonCode::REVIEW_APPROVED,
);
$throwsMigrationPlanningCode(
    fn () => $migrationBuilder->build($entityAdded, $correlationMismatchReview, 'corr-migration-correlation'),
    \OneQay\SchemaPlanning\MigrationPlanningException::SOURCE_CORRELATION_MISMATCH,
);

$maliciousTenantScopeChange = new PhysicalSchemaChange(
    new StableChangeIdentifier(str_repeat('4', 64)),
    SchemaChangeKind::TENANT_SCOPE_CHANGED,
    ChangeRisk::REVIEW_REQUIRED,
    'MANUAL_ENTITY',
    null,
    null,
    new ManifestFingerprint(str_repeat('5', 64)),
);
$maliciousTenantScopePlan = new PhysicalSchemaPlan(
    $manualBefore,
    $manualAfter,
    PlanDisposition::REVIEW_REQUIRED,
    new CorrelationId('corr-schema-malicious-scope'),
    [$maliciousTenantScopeChange],
);
$maliciousTenantScopeReview = $reviewer->review($maliciousTenantScopePlan, 'zefriansyah', 'corr-review-malicious-scope', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED');
$throwsMigrationPlanningCode(
    fn () => $migrationBuilder->build($maliciousTenantScopePlan, $maliciousTenantScopeReview, 'corr-migration-malicious-scope'),
    \OneQay\SchemaPlanning\MigrationPlanningException::CHANGE_KIND_NOT_ALLOWED,
);

$maliciousTenantKeyChange = new PhysicalSchemaChange(
    new StableChangeIdentifier(str_repeat('6', 64)),
    SchemaChangeKind::TENANT_KEY_CHANGED,
    ChangeRisk::REVIEW_REQUIRED,
    'MANUAL_ENTITY',
    'TENANT_ID',
    null,
    new ManifestFingerprint(str_repeat('7', 64)),
);
$maliciousTenantKeyPlan = new PhysicalSchemaPlan(
    $manualBefore,
    $manualAfter,
    PlanDisposition::REVIEW_REQUIRED,
    new CorrelationId('corr-schema-malicious-key'),
    [$maliciousTenantKeyChange],
);
$maliciousTenantKeyReview = $reviewer->review($maliciousTenantKeyPlan, 'zefriansyah', 'corr-review-malicious-key', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED');
$throwsMigrationPlanningCode(
    fn () => $migrationBuilder->build($maliciousTenantKeyPlan, $maliciousTenantKeyReview, 'corr-migration-malicious-key'),
    \OneQay\SchemaPlanning\MigrationPlanningException::CHANGE_KIND_NOT_ALLOWED,
);

$malformedAdditiveChange = new PhysicalSchemaChange(
    new StableChangeIdentifier(str_repeat('8', 64)),
    SchemaChangeKind::ATTRIBUTE_ADDED,
    ChangeRisk::REVIEW_REQUIRED,
    'MANUAL_ENTITY',
    'MANUAL_ATTRIBUTE',
    new ManifestFingerprint(str_repeat('9', 64)),
    new ManifestFingerprint(str_repeat('a', 64)),
);
$malformedAdditivePlan = new PhysicalSchemaPlan(
    $manualBefore,
    $manualAfter,
    PlanDisposition::REVIEW_REQUIRED,
    new CorrelationId('corr-schema-malformed-additive'),
    [$malformedAdditiveChange],
);
$malformedAdditiveReview = $reviewer->review($malformedAdditivePlan, 'zefriansyah', 'corr-review-malformed-additive', 'APPROVED_FOR_MIGRATION_PLANNING', 'REVIEW_APPROVED');
$throwsMigrationPlanningCode(
    fn () => $migrationBuilder->build($malformedAdditivePlan, $malformedAdditiveReview, 'corr-migration-malformed-additive'),
    \OneQay\SchemaPlanning\MigrationPlanningException::CHANGE_FINGERPRINT_INVALID,
);

$migrationEncoded = json_encode($entityMigration, JSON_THROW_ON_ERROR);
foreach (['DB_PASSWORD', 'DB_USER', 'DB_HOST', 'jdbc:', 'mysql:host=', 'CREATE TABLE', 'ALTER TABLE', 'DROP TABLE', 'INSERT INTO', 'DELETE FROM', '/var/', 'tenant_record', 'reference_catalog'] as $forbidden) {
    $assert(!str_contains($migrationEncoded, $forbidden), 'Migration planning output contains forbidden material.');
}
$migrationSource = (string) file_get_contents(__DIR__ . '/../src/SchemaPlanning/MigrationPlanning.php');
$assert(!preg_match('/\b(CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|INSERT\s+INTO|DELETE\s+FROM)\b/i', $migrationSource), 'Migration planning foundation contains executable SQL.');
$assert(!str_contains($migrationSource, 'new PDO('), 'Migration planning foundation introduced a database connection.');
$assert(!preg_match('/\b(curl_|fsockopen|stream_socket_client)\b/i', $migrationSource), 'Migration planning foundation introduced a network dependency.');
$assert(!preg_match('/\b(file_put_contents|fopen|unlink|mkdir|rename)\b/i', $migrationSource), 'Migration planning foundation introduced a filesystem side effect.');

fwrite(STDOUT, sprintf("Schema Planning tests passed: %d assertions.\n", $assertions));

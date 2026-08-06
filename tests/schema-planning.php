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

fwrite(STDOUT, sprintf("Schema Planning tests passed: %d assertions.\n", $assertions));

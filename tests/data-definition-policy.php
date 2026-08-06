<?php

declare(strict_types=1);

use OneQay\DataDefinition\AttributeDefinition;
use OneQay\DataDefinition\DataDefinitionException;
use OneQay\DataDefinition\DataDefinitionIdentifier;
use OneQay\DataDefinition\DataDefinitionManifest;
use OneQay\DataDefinition\DataDefinitionPolicyValidator;
use OneQay\DataDefinition\DefaultValueDefinition;
use OneQay\DataDefinition\DefaultValuePolicy;
use OneQay\DataDefinition\EntityDefinition;
use OneQay\DataDefinition\NullabilityPolicy;
use OneQay\DataDefinition\PortableScalarType;
use OneQay\DataDefinition\PrimaryKeyDefinition;
use OneQay\DataDefinition\ReferenceDefinition;
use OneQay\DataDefinition\TenantScope;
use OneQay\DataDefinition\UniqueConstraintDefinition;
use OneQay\DataDefinition\ValueConstraint;

// Duplicate entity and attribute rejection.
$throwsCode(fn () => new DataDefinitionManifest([$globalEntity, $globalEntity]), DataDefinitionException::DUPLICATE_ENTITY);
$throwsCode(
    fn () => new EntityDefinition(
        new DataDefinitionIdentifier('DUPLICATE_ATTRIBUTE_ENTITY'),
        TenantScope::GLOBAL,
        [$globalId, $globalId],
        new PrimaryKeyDefinition(['ID']),
    ),
    DataDefinitionException::DUPLICATE_ATTRIBUTE,
);

// Missing and unsafe tenant-key policies.
$missingTenantKey = new EntityDefinition(
    new DataDefinitionIdentifier('TENANT_WITHOUT_KEY'),
    TenantScope::TENANT_SCOPED,
    [$recordId],
    new PrimaryKeyDefinition(['RECORD_ID']),
);
$missingReport = $validator->validate(new DataDefinitionManifest([$missingTenantKey]), 'corr-definition-002');
$assert(!$missingReport->isValid, 'Missing tenant key was accepted.');
$assert(in_array(DataDefinitionException::TENANT_KEY_REQUIRED, $missingReport->errorCodes, true), 'Tenant key required code missing.');

$nullableTenantId = new AttributeDefinition(new DataDefinitionIdentifier('TENANT_ID'), $uuidConstraint, NullabilityPolicy::NULLABLE, DefaultValueDefinition::none());
$invalidTenantKeyEntity = new EntityDefinition(
    new DataDefinitionIdentifier('TENANT_WITH_NULLABLE_KEY'),
    TenantScope::TENANT_SCOPED,
    [$nullableTenantId, $recordId],
    new PrimaryKeyDefinition(['RECORD_ID']),
    [],
    [],
    new DataDefinitionIdentifier('TENANT_ID'),
);
$invalidTenantReport = $validator->validate(new DataDefinitionManifest([$invalidTenantKeyEntity]), 'corr-definition-003');
$assert(in_array(DataDefinitionException::TENANT_KEY_INVALID, $invalidTenantReport->errorCodes, true), 'Unsafe tenant key was accepted.');
$assert(in_array(DataDefinitionException::PRIMARY_KEY_INVALID, $invalidTenantReport->errorCodes, true), 'Tenant primary key without tenant key was accepted.');

$globalWithTenantKey = new EntityDefinition(
    new DataDefinitionIdentifier('GLOBAL_WITH_TENANT_KEY'),
    TenantScope::GLOBAL,
    [$tenantId, $recordId],
    new PrimaryKeyDefinition(['RECORD_ID']),
    [],
    [],
    new DataDefinitionIdentifier('TENANT_ID'),
);
$globalTenantReport = $validator->validate(new DataDefinitionManifest([$globalWithTenantKey]), 'corr-definition-004');
$assert(in_array(DataDefinitionException::TENANT_KEY_INVALID, $globalTenantReport->errorCodes, true), 'Global tenant key was accepted.');

// Tenant uniqueness must include tenant key.
$unsafeUniqueEntity = new EntityDefinition(
    new DataDefinitionIdentifier('TENANT_UNSAFE_UNIQUE'),
    TenantScope::TENANT_SCOPED,
    [$tenantId, $recordId, $code],
    new PrimaryKeyDefinition(['TENANT_ID', 'RECORD_ID']),
    [new UniqueConstraintDefinition(new DataDefinitionIdentifier('UNIQUE_CODE_ONLY'), ['CODE'])],
    [],
    new DataDefinitionIdentifier('TENANT_ID'),
);
$unsafeUniqueReport = $validator->validate(new DataDefinitionManifest([$unsafeUniqueEntity]), 'corr-definition-005');
$assert(in_array(DataDefinitionException::UNIQUE_POLICY_INVALID, $unsafeUniqueReport->errorCodes, true), 'Cross-tenant uniqueness was accepted.');

// Global-to-tenant references are denied by default.
$globalReferenceSource = new EntityDefinition(
    new DataDefinitionIdentifier('GLOBAL_REFERENCE_SOURCE'),
    TenantScope::GLOBAL,
    [$globalId, $parentId],
    new PrimaryKeyDefinition(['ID']),
    [],
    [new ReferenceDefinition(
        new DataDefinitionIdentifier('REFERENCE_GLOBAL_TO_TENANT'),
        new DataDefinitionIdentifier('TENANT_RECORD'),
        ['PARENT_ID' => 'RECORD_ID'],
    )],
);
$crossTenantReport = $validator->validate(new DataDefinitionManifest([$tenantEntity, $globalReferenceSource]), 'corr-definition-006');
$assert(!$crossTenantReport->isValid, 'Global-to-tenant reference was accepted.');
$assert(in_array(DataDefinitionException::CROSS_TENANT_REFERENCE_DENIED, $crossTenantReport->errorCodes, true), 'Cross-tenant denial code missing.');

// Tenant-to-tenant reference must map tenant key explicitly.
$unsafeTenantReference = new EntityDefinition(
    new DataDefinitionIdentifier('TENANT_UNSAFE_REFERENCE'),
    TenantScope::TENANT_SCOPED,
    [$tenantId, $recordId, $parentId],
    new PrimaryKeyDefinition(['TENANT_ID', 'RECORD_ID']),
    [],
    [new ReferenceDefinition(
        new DataDefinitionIdentifier('REFERENCE_WITHOUT_TENANT_PAIR'),
        new DataDefinitionIdentifier('TENANT_RECORD'),
        ['PARENT_ID' => 'RECORD_ID'],
    )],
    new DataDefinitionIdentifier('TENANT_ID'),
);
$unsafeReferenceReport = $validator->validate(new DataDefinitionManifest([$tenantEntity, $unsafeTenantReference]), 'corr-definition-007');
$assert(in_array(DataDefinitionException::CROSS_TENANT_REFERENCE_DENIED, $unsafeReferenceReport->errorCodes, true), 'Missing tenant-key reference mapping was accepted.');
$assert(in_array(DataDefinitionException::REFERENCE_INVALID, $unsafeReferenceReport->errorCodes, true), 'Incomplete target key mapping was accepted.');

// Reference type and target-key policy.
$stringParent = new AttributeDefinition(new DataDefinitionIdentifier('PARENT_CODE'), $stringConstraint, NullabilityPolicy::REQUIRED, DefaultValueDefinition::none());
$typeMismatchSource = new EntityDefinition(
    new DataDefinitionIdentifier('TYPE_MISMATCH_SOURCE'),
    TenantScope::GLOBAL,
    [$globalId, $stringParent],
    new PrimaryKeyDefinition(['ID']),
    [],
    [new ReferenceDefinition(
        new DataDefinitionIdentifier('REFERENCE_TYPE_MISMATCH'),
        new DataDefinitionIdentifier('REFERENCE_CATALOG'),
        ['PARENT_CODE' => 'ID'],
    )],
);
$typeMismatchReport = $validator->validate(new DataDefinitionManifest([$globalEntity, $typeMismatchSource]), 'corr-definition-008');
$assert(in_array(DataDefinitionException::REFERENCE_INVALID, $typeMismatchReport->errorCodes, true), 'Reference type mismatch was accepted.');

// Safe report and object representations do not expose raw material.
$encodedManifest = json_encode($manifest, JSON_THROW_ON_ERROR);
$encodedReport = json_encode($validReport, JSON_THROW_ON_ERROR);
foreach ([$sensitiveMarker, $syntheticPath, 'DB_PASSWORD', 'DB_USER', 'DB_HOST'] as $forbidden) {
    $assert(!str_contains($encodedManifest, $forbidden), 'Manifest leaked sensitive or connection material.');
    $assert(!str_contains($encodedReport, $forbidden), 'Validation report leaked sensitive or connection material.');
}
$assert($validReport->correlationId === 'corr-definition-001', 'Correlation ID changed.');

// Explicit scope exclusions.
$source = implode("\n", [
    (string) file_get_contents(__DIR__ . '/../src/DataDefinition/Foundation.php'),
    (string) file_get_contents(__DIR__ . '/../src/DataDefinition/ValueObjects.php'),
    (string) file_get_contents(__DIR__ . '/../src/DataDefinition/Contracts.php'),
    (string) file_get_contents(__DIR__ . '/../src/DataDefinition/Validation.php'),
]);
$assert(!preg_match('/\b(CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|INSERT\s+INTO|UPDATE\s+.+\s+SET|DELETE\s+FROM)\b/i', $source), 'Executable production SQL was introduced.');
$assert(!preg_match('/\b(pos|sale|payment|inventory|catalog_schema)\b/i', $source), 'Business schema or POS behavior was introduced.');
$assert(!preg_match('/(?:\/home\/|\/var\/|[A-Z]:\\\\)/', $source), 'Internal path was introduced.');
$assert(!preg_match('/\b(password|credential|api[_-]?key|token)\s*[:=]\s*[\'\"][^\'\"]+/i', $source), 'Credential-like value was introduced.');
$assert(!str_contains($source, 'new PDO('), 'Production database connection was introduced.');
$assert(!str_contains($source, 'MigrationExecutor'), 'Migration execution capability was introduced.');

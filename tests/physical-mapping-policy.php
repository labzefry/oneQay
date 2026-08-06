<?php

declare(strict_types=1);

use OneQay\DataDefinition\DataDefinitionIdentifier;
use OneQay\DataDefinition\PortableScalarType;
use OneQay\DataDefinition\TenantScope;
use OneQay\PhysicalMapping\CharsetPolicy;
use OneQay\PhysicalMapping\CollationPolicy;
use OneQay\PhysicalMapping\ForeignKeyCompatibility;
use OneQay\PhysicalMapping\IndexKind;
use OneQay\PhysicalMapping\PhysicalAttributeMapping;
use OneQay\PhysicalMapping\PhysicalEntityMapping;
use OneQay\PhysicalMapping\PhysicalIdentifier;
use OneQay\PhysicalMapping\PhysicalIndexMapping;
use OneQay\PhysicalMapping\PhysicalMappingException;
use OneQay\PhysicalMapping\PhysicalMappingManifest;
use OneQay\PhysicalMapping\PhysicalReferenceMapping;
use OneQay\PhysicalMapping\PhysicalScalarMapping;
use OneQay\PhysicalMapping\PhysicalTypeIdentifier;
use OneQay\PhysicalMapping\VendorCompatibilityValidator;
use OneQay\PhysicalMapping\VendorIdentifier;

// Duplicate entity, physical entity name, and attribute rejection.
$throwsCode(
    fn () => new PhysicalMappingManifest($vendor, [$globalEntity, $globalEntity]),
    PhysicalMappingException::DUPLICATE_ENTITY,
);
$duplicatePhysicalEntity = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('OTHER_GLOBAL_ENTITY'),
    new PhysicalIdentifier('reference_catalog'),
    TenantScope::GLOBAL,
    [$globalId],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_other_global'), IndexKind::PRIMARY, ['ID']),
);
$throwsCode(
    fn () => new PhysicalMappingManifest($vendor, [$globalEntity, $duplicatePhysicalEntity]),
    PhysicalMappingException::DUPLICATE_ENTITY,
);
$throwsCode(
    fn () => new PhysicalEntityMapping(
        new DataDefinitionIdentifier('DUPLICATE_ATTRIBUTE_ENTITY'),
        new PhysicalIdentifier('duplicate_attribute_entity'),
        TenantScope::GLOBAL,
        [$globalId, $globalId],
        new PhysicalIndexMapping(new PhysicalIdentifier('pk_duplicate_attribute'), IndexKind::PRIMARY, ['ID']),
    ),
    PhysicalMappingException::DUPLICATE_ATTRIBUTE,
);

// Missing tenant key and tenant key not represented by primary mapping.
$missingTenantKey = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('TENANT_WITHOUT_KEY'),
    new PhysicalIdentifier('tenant_without_key'),
    TenantScope::TENANT_SCOPED,
    [$recordId],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_tenant_without_key'), IndexKind::PRIMARY, ['RECORD_ID']),
);
$missingTenantReport = $validator->validate(new PhysicalMappingManifest($vendor, [$missingTenantKey]), 'corr-physical-002');
$assert(in_array(PhysicalMappingException::TENANT_KEY_REQUIRED, $missingTenantReport->errorCodes, true), 'Missing tenant key was accepted.');

$tenantOutsidePrimary = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('TENANT_OUTSIDE_PRIMARY'),
    new PhysicalIdentifier('tenant_outside_primary'),
    TenantScope::TENANT_SCOPED,
    [$tenantId, $recordId],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_tenant_outside_primary'), IndexKind::PRIMARY, ['RECORD_ID']),
    [],
    [],
    new DataDefinitionIdentifier('TENANT_ID'),
);
$tenantOutsidePrimaryReport = $validator->validate(new PhysicalMappingManifest($vendor, [$tenantOutsidePrimary]), 'corr-physical-003');
$assert(in_array(PhysicalMappingException::TENANT_KEY_REQUIRED, $tenantOutsidePrimaryReport->errorCodes, true), 'Tenant key outside primary index was accepted.');

// Tenant uniqueness must include tenant key.
$unsafeUniqueEntity = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('TENANT_UNSAFE_UNIQUE'),
    new PhysicalIdentifier('tenant_unsafe_unique'),
    TenantScope::TENANT_SCOPED,
    [$tenantId, $recordId, $code],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_tenant_unsafe_unique'), IndexKind::PRIMARY, ['TENANT_ID', 'RECORD_ID']),
    [new PhysicalIndexMapping(new PhysicalIdentifier('uq_code_only'), IndexKind::UNIQUE, ['CODE'])],
    [],
    new DataDefinitionIdentifier('TENANT_ID'),
);
$unsafeUniqueReport = $validator->validate(new PhysicalMappingManifest($vendor, [$unsafeUniqueEntity]), 'corr-physical-004');
$assert(in_array(PhysicalMappingException::TENANT_KEY_REQUIRED, $unsafeUniqueReport->errorCodes, true), 'Tenant uniqueness without tenant key was accepted.');

// Global mapping cannot declare a tenant key.
$globalWithTenantKey = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('GLOBAL_WITH_TENANT_KEY'),
    new PhysicalIdentifier('global_with_tenant_key'),
    TenantScope::GLOBAL,
    [$tenantId, $recordId],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_global_with_tenant_key'), IndexKind::PRIMARY, ['RECORD_ID']),
    [],
    [],
    new DataDefinitionIdentifier('TENANT_ID'),
);
$globalTenantReport = $validator->validate(new PhysicalMappingManifest($vendor, [$globalWithTenantKey]), 'corr-physical-005');
$assert(in_array(PhysicalMappingException::TENANT_KEY_REQUIRED, $globalTenantReport->errorCodes, true), 'Global tenant key was accepted.');

// Index-key budget and unsupported indexed type.
$oversizedStringMap = new PhysicalScalarMapping(
    new PortableScalarType('STRING'),
    new PhysicalTypeIdentifier('VARCHAR'),
    800,
    null,
    null,
    $charset,
    $unicode,
);
$oversizedCode = new PhysicalAttributeMapping(new DataDefinitionIdentifier('OVERSIZED_CODE'), new PhysicalIdentifier('oversized_code'), $oversizedStringMap);
$oversizedEntity = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('OVERSIZED_INDEX_ENTITY'),
    new PhysicalIdentifier('oversized_index_entity'),
    TenantScope::GLOBAL,
    [$globalId, $oversizedCode],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_oversized_entity'), IndexKind::PRIMARY, ['ID']),
    [new PhysicalIndexMapping(new PhysicalIdentifier('uq_oversized_code'), IndexKind::UNIQUE, ['OVERSIZED_CODE'])],
);
$oversizedReport = $validator->validate(new PhysicalMappingManifest($vendor, [$oversizedEntity]), 'corr-physical-006');
$assert(in_array(PhysicalMappingException::INDEX_BUDGET_EXCEEDED, $oversizedReport->errorCodes, true), 'Index-key budget overflow was accepted.');

$jsonIndexedEntity = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('JSON_INDEX_ENTITY'),
    new PhysicalIdentifier('json_index_entity'),
    TenantScope::GLOBAL,
    [$globalId, $metadata],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_json_index'), IndexKind::PRIMARY, ['ID']),
    [new PhysicalIndexMapping(new PhysicalIdentifier('uq_json_document'), IndexKind::UNIQUE, ['METADATA'])],
);
$jsonIndexReport = $validator->validate(new PhysicalMappingManifest($vendor, [$jsonIndexedEntity]), 'corr-physical-007');
$assert(in_array(PhysicalMappingException::INDEX_INVALID, $jsonIndexReport->errorCodes, true), 'Unsupported JSON index mapping was accepted.');

// Foreign-key mapping must be physically compatible and target an eligible key.
$stringParent = new PhysicalAttributeMapping(new DataDefinitionIdentifier('PARENT_CODE'), new PhysicalIdentifier('parent_code'), $stringMap);
$typeMismatchSource = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('TYPE_MISMATCH_SOURCE'),
    new PhysicalIdentifier('type_mismatch_source'),
    TenantScope::GLOBAL,
    [$globalId, $stringParent],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_type_mismatch'), IndexKind::PRIMARY, ['ID']),
    [],
    [new PhysicalReferenceMapping(
        new PhysicalIdentifier('fk_type_mismatch'),
        new DataDefinitionIdentifier('REFERENCE_CATALOG'),
        ['PARENT_CODE' => 'ID'],
    )],
);
$typeMismatchReport = $validator->validate(new PhysicalMappingManifest($vendor, [$globalEntity, $typeMismatchSource]), 'corr-physical-008');
$assert(in_array(PhysicalMappingException::FOREIGN_KEY_INCOMPATIBLE, $typeMismatchReport->errorCodes, true), 'Incompatible foreign-key mapping was accepted.');

$notKeySource = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('NOT_KEY_SOURCE'),
    new PhysicalIdentifier('not_key_source'),
    TenantScope::GLOBAL,
    [$globalId, $stringParent],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_not_key_source'), IndexKind::PRIMARY, ['ID']),
    [],
    [new PhysicalReferenceMapping(
        new PhysicalIdentifier('fk_not_key_target'),
        new DataDefinitionIdentifier('REFERENCE_CATALOG'),
        ['PARENT_CODE' => 'CODE', 'ID' => 'ID'],
    )],
);
$notKeyReport = $validator->validate(new PhysicalMappingManifest($vendor, [$globalEntity, $notKeySource]), 'corr-physical-009');
$assert(in_array(PhysicalMappingException::FOREIGN_KEY_INCOMPATIBLE, $notKeyReport->errorCodes, true), 'Reference to a non-eligible target key was accepted.');

// Global-to-tenant and tenant-to-tenant without tenant-key mapping are denied.
$globalToTenant = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('GLOBAL_TO_TENANT'),
    new PhysicalIdentifier('global_to_tenant'),
    TenantScope::GLOBAL,
    [$globalId, $parentId],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_global_to_tenant'), IndexKind::PRIMARY, ['ID']),
    [],
    [new PhysicalReferenceMapping(
        new PhysicalIdentifier('fk_global_to_tenant'),
        new DataDefinitionIdentifier('TENANT_RECORD'),
        ['PARENT_ID' => 'RECORD_ID'],
    )],
);
$globalToTenantReport = $validator->validate(new PhysicalMappingManifest($vendor, [$tenantEntity, $globalToTenant]), 'corr-physical-010');
$assert(in_array(PhysicalMappingException::TENANT_KEY_REQUIRED, $globalToTenantReport->errorCodes, true), 'Global-to-tenant reference was accepted.');

$tenantWithoutTenantPair = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('TENANT_WITHOUT_TENANT_PAIR'),
    new PhysicalIdentifier('tenant_without_tenant_pair'),
    TenantScope::TENANT_SCOPED,
    [$tenantId, $childId, $parentId],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_tenant_without_pair'), IndexKind::PRIMARY, ['TENANT_ID', 'CHILD_ID']),
    [],
    [new PhysicalReferenceMapping(
        new PhysicalIdentifier('fk_without_tenant_pair'),
        new DataDefinitionIdentifier('TENANT_RECORD'),
        ['PARENT_ID' => 'RECORD_ID'],
    )],
    new DataDefinitionIdentifier('TENANT_ID'),
);
$tenantPairReport = $validator->validate(new PhysicalMappingManifest($vendor, [$tenantEntity, $tenantWithoutTenantPair]), 'corr-physical-011');
$assert(in_array(PhysicalMappingException::TENANT_KEY_REQUIRED, $tenantPairReport->errorCodes, true), 'Tenant reference without tenant-key mapping was accepted.');
$assert(in_array(PhysicalMappingException::FOREIGN_KEY_INCOMPATIBLE, $tenantPairReport->errorCodes, true), 'Incomplete target-key mapping was accepted.');

// Safe report and manifest representations do not expose raw material.
$sensitiveMarker = 'synthetic-sensitive-' . hash('sha256', 'sprint11-marker');
$syntheticPath = '/' . implode('/', ['private', 'synthetic', 'physical']);
$encodedManifest = json_encode($manifest, JSON_THROW_ON_ERROR);
$encodedReport = json_encode($validReport, JSON_THROW_ON_ERROR);
foreach ([$sensitiveMarker, $syntheticPath, 'DB_PASSWORD', 'DB_USER', 'DB_HOST', 'jdbc:', 'mysql:host='] as $forbidden) {
    $assert(!str_contains($encodedManifest, $forbidden), 'Physical manifest leaked sensitive or connection material.');
    $assert(!str_contains($encodedReport, $forbidden), 'Compatibility report leaked sensitive or connection material.');
}
$assert($validReport->correlationId === 'corr-physical-001', 'Compatibility report correlation ID changed.');

// Explicit scope exclusions and deny-by-default source boundary.
$source = implode("\n", [
    (string) file_get_contents(__DIR__ . '/../src/PhysicalMapping/Foundation.php'),
    (string) file_get_contents(__DIR__ . '/../src/PhysicalMapping/ValueObjects.php'),
    (string) file_get_contents(__DIR__ . '/../src/PhysicalMapping/Contracts.php'),
    (string) file_get_contents(__DIR__ . '/../src/PhysicalMapping/Validation.php'),
]);
$assert(!preg_match('/\b(CREATE\s+TABLE|ALTER\s+TABLE|DROP\s+TABLE|INSERT\s+INTO|UPDATE\s+.+\s+SET|DELETE\s+FROM)\b/i', $source), 'Executable SQL was introduced.');
$assert(!preg_match('/\b(pos|sale|payment|inventory|catalog_schema)\b/i', $source), 'Business schema or POS behavior was introduced.');
$assert(!preg_match('/(?:\/home\/|\/var\/|[A-Z]:\\\\)/', $source), 'Internal path was introduced.');
$assert(!preg_match('/\b(password|credential|api[_-]?key|token)\s*[:=]\s*[\'\"][^\'\"]+/i', $source), 'Credential-like value was introduced.');
$assert(!str_contains($source, 'new PDO('), 'Production database connection was introduced.');
$assert(!str_contains($source, 'MigrationExecutor'), 'Migration execution was introduced.');
$assert(!str_contains($source, 'SqlRenderer') && !str_contains($source, 'SQLRenderer'), 'SQL renderer was introduced.');

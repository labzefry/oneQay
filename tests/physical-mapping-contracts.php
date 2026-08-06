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

// Canonical physical identifiers and reserved namespaces.
$physicalId = new PhysicalIdentifier(' Tenant_Record ');
$assert($physicalId->value === 'tenant_record', 'Physical identifier was not canonicalized.');
$assert(json_encode($physicalId, JSON_THROW_ON_ERROR) === '"tenant_record"', 'Physical identifier JSON changed.');
foreach (['', 'bad-name', 'A B', '/path', '9invalid', str_repeat('a', 65)] as $invalid) {
    $throwsCode(fn () => new PhysicalIdentifier($invalid), PhysicalMappingException::IDENTIFIER_INVALID);
}
foreach (['mysql_user', 'sys_log', 'information_schema_cache', 'performance_schema_data', 'oneqay_internal_map'] as $reserved) {
    $throwsCode(fn () => new PhysicalIdentifier($reserved), PhysicalMappingException::RESERVED_NAMESPACE);
}

// Vendor, charset, collation, and physical vocabulary.
$vendor = new VendorIdentifier('mariadb_11');
$assert($vendor->value === VendorIdentifier::MARIADB_11, 'Vendor identifier was not canonicalized.');
$throwsCode(fn () => new VendorIdentifier('postgresql'), PhysicalMappingException::VENDOR_UNSUPPORTED);
$charset = new CharsetPolicy('utf8mb4');
$assert($charset->value === CharsetPolicy::UTF8MB4 && $charset->bytesPerCharacter() === 4, 'Charset policy changed.');
$throwsCode(fn () => new CharsetPolicy('latin1'), PhysicalMappingException::CHARSET_INVALID);
$unicode = new CollationPolicy('utf8mb4_unicode_ci');
$binary = new CollationPolicy('utf8mb4_binary');
$assert($unicode->value === CollationPolicy::UNICODE_CI, 'Unicode collation was not canonicalized.');
$assert($binary->value === CollationPolicy::BINARY, 'Binary collation was not canonicalized.');
$throwsCode(fn () => new CollationPolicy('utf8_general_ci'), PhysicalMappingException::COLLATION_INVALID);

foreach ([
    PhysicalTypeIdentifier::VARCHAR,
    PhysicalTypeIdentifier::BIGINT_SIGNED,
    PhysicalTypeIdentifier::DECIMAL,
    PhysicalTypeIdentifier::TINYINT_BOOLEAN,
    PhysicalTypeIdentifier::CHAR_UUID,
    PhysicalTypeIdentifier::DATE,
    PhysicalTypeIdentifier::DATETIME,
    PhysicalTypeIdentifier::JSON_DOCUMENT,
] as $type) {
    $assert((new PhysicalTypeIdentifier(strtolower($type)))->value === $type, 'Physical type normalization failed.');
}
$throwsCode(fn () => new PhysicalTypeIdentifier('MONEY'), PhysicalMappingException::SCALAR_UNSUPPORTED);

// Valid logical-to-physical mappings.
$stringMap = new PhysicalScalarMapping(
    new PortableScalarType('STRING'),
    new PhysicalTypeIdentifier('VARCHAR'),
    128,
    null,
    null,
    $charset,
    $unicode,
);
$integerMap = new PhysicalScalarMapping(new PortableScalarType('INTEGER'), new PhysicalTypeIdentifier('BIGINT_SIGNED'));
$decimalMap = new PhysicalScalarMapping(new PortableScalarType('DECIMAL'), new PhysicalTypeIdentifier('DECIMAL'), null, 18, 2);
$booleanMap = new PhysicalScalarMapping(new PortableScalarType('BOOLEAN'), new PhysicalTypeIdentifier('TINYINT_BOOLEAN'));
$uuidMap = new PhysicalScalarMapping(new PortableScalarType('UUID'), new PhysicalTypeIdentifier('CHAR_UUID'), 36, null, null, $charset, $binary);
$dateMap = new PhysicalScalarMapping(new PortableScalarType('DATE'), new PhysicalTypeIdentifier('DATE'));
$datetimeMap = new PhysicalScalarMapping(new PortableScalarType('DATETIME'), new PhysicalTypeIdentifier('DATETIME'));
$jsonMap = new PhysicalScalarMapping(new PortableScalarType('JSON'), new PhysicalTypeIdentifier('JSON_DOCUMENT'));

$assert($stringMap->estimatedIndexBytes() === 512, 'String index-byte estimate changed.');
$assert($uuidMap->estimatedIndexBytes() === 144, 'UUID index-byte estimate changed.');
$assert($integerMap->estimatedIndexBytes() === 8, 'Integer index-byte estimate changed.');
$assert($decimalMap->estimatedIndexBytes() > 0, 'Decimal index-byte estimate failed.');
$assert($booleanMap->estimatedIndexBytes() === 1, 'Boolean index-byte estimate changed.');
$assert($dateMap->estimatedIndexBytes() === 3, 'Date index-byte estimate changed.');
$assert($datetimeMap->estimatedIndexBytes() === 8, 'Datetime index-byte estimate changed.');
$throwsCode(fn () => $jsonMap->estimatedIndexBytes(), PhysicalMappingException::INDEX_INVALID);

// Unsupported mapping, length, precision, and character-option rejection.
$throwsCode(
    fn () => new PhysicalScalarMapping(new PortableScalarType('STRING'), new PhysicalTypeIdentifier('BIGINT_SIGNED'), 20, null, null, $charset, $unicode),
    PhysicalMappingException::SCALAR_UNSUPPORTED,
);
$throwsCode(
    fn () => new PhysicalScalarMapping(new PortableScalarType('STRING'), new PhysicalTypeIdentifier('VARCHAR'), 0, null, null, $charset, $unicode),
    PhysicalMappingException::LENGTH_INVALID,
);
$throwsCode(
    fn () => new PhysicalScalarMapping(new PortableScalarType('STRING'), new PhysicalTypeIdentifier('VARCHAR'), 20, null, null, null, null),
    PhysicalMappingException::COLLATION_INVALID,
);
$throwsCode(
    fn () => new PhysicalScalarMapping(new PortableScalarType('DECIMAL'), new PhysicalTypeIdentifier('DECIMAL'), null, 39, 2),
    PhysicalMappingException::PRECISION_INVALID,
);
$throwsCode(
    fn () => new PhysicalScalarMapping(new PortableScalarType('DECIMAL'), new PhysicalTypeIdentifier('DECIMAL'), null, 8, 9),
    PhysicalMappingException::PRECISION_INVALID,
);
$throwsCode(
    fn () => new PhysicalScalarMapping(new PortableScalarType('UUID'), new PhysicalTypeIdentifier('CHAR_UUID'), 32, null, null, $charset, $binary),
    PhysicalMappingException::LENGTH_INVALID,
);
$throwsCode(
    fn () => new PhysicalScalarMapping(new PortableScalarType('INTEGER'), new PhysicalTypeIdentifier('BIGINT_SIGNED'), null, null, null, $charset, $unicode),
    PhysicalMappingException::COLLATION_INVALID,
);

// Reusable physical attributes.
$globalId = new PhysicalAttributeMapping(new DataDefinitionIdentifier('ID'), new PhysicalIdentifier('id'), $uuidMap);
$tenantId = new PhysicalAttributeMapping(new DataDefinitionIdentifier('TENANT_ID'), new PhysicalIdentifier('tenant_id'), $uuidMap);
$recordId = new PhysicalAttributeMapping(new DataDefinitionIdentifier('RECORD_ID'), new PhysicalIdentifier('record_id'), $uuidMap);
$childId = new PhysicalAttributeMapping(new DataDefinitionIdentifier('CHILD_ID'), new PhysicalIdentifier('child_id'), $uuidMap);
$parentId = new PhysicalAttributeMapping(new DataDefinitionIdentifier('PARENT_ID'), new PhysicalIdentifier('parent_id'), $uuidMap);
$code = new PhysicalAttributeMapping(new DataDefinitionIdentifier('CODE'), new PhysicalIdentifier('code'), $stringMap);
$amount = new PhysicalAttributeMapping(new DataDefinitionIdentifier('AMOUNT'), new PhysicalIdentifier('amount'), $decimalMap);
$active = new PhysicalAttributeMapping(new DataDefinitionIdentifier('ACTIVE'), new PhysicalIdentifier('active'), $booleanMap);
$createdAt = new PhysicalAttributeMapping(new DataDefinitionIdentifier('CREATED_AT'), new PhysicalIdentifier('created_at'), $datetimeMap);
$metadata = new PhysicalAttributeMapping(new DataDefinitionIdentifier('METADATA'), new PhysicalIdentifier('metadata'), $jsonMap);

$globalEntity = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('REFERENCE_CATALOG'),
    new PhysicalIdentifier('reference_catalog'),
    TenantScope::GLOBAL,
    [$globalId, $code],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_reference_catalog'), IndexKind::PRIMARY, ['ID']),
    [new PhysicalIndexMapping(new PhysicalIdentifier('uq_reference_catalog_code'), IndexKind::UNIQUE, ['CODE'])],
);

$tenantEntity = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('TENANT_RECORD'),
    new PhysicalIdentifier('tenant_record'),
    TenantScope::TENANT_SCOPED,
    [$tenantId, $recordId, $code, $amount, $active, $createdAt, $metadata],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_tenant_record'), IndexKind::PRIMARY, ['TENANT_ID', 'RECORD_ID']),
    [new PhysicalIndexMapping(new PhysicalIdentifier('uq_tenant_record_code'), IndexKind::UNIQUE, ['TENANT_ID', 'CODE'])],
    [],
    new DataDefinitionIdentifier('TENANT_ID'),
);

$childEntity = new PhysicalEntityMapping(
    new DataDefinitionIdentifier('TENANT_CHILD'),
    new PhysicalIdentifier('tenant_child'),
    TenantScope::TENANT_SCOPED,
    [$tenantId, $childId, $parentId],
    new PhysicalIndexMapping(new PhysicalIdentifier('pk_tenant_child'), IndexKind::PRIMARY, ['TENANT_ID', 'CHILD_ID']),
    [],
    [new PhysicalReferenceMapping(
        new PhysicalIdentifier('fk_tenant_child_parent'),
        new DataDefinitionIdentifier('TENANT_RECORD'),
        ['TENANT_ID' => 'TENANT_ID', 'PARENT_ID' => 'RECORD_ID'],
    )],
    new DataDefinitionIdentifier('TENANT_ID'),
);

$manifest = new PhysicalMappingManifest($vendor, [$globalEntity, $tenantEntity, $childEntity]);
$validator = new VendorCompatibilityValidator();
$validReport = $validator->validate($manifest, 'corr-physical-001');
$assert($validReport->isCompatible, 'Valid physical mapping manifest was rejected.');
$assert($validReport->errorCodes === [], 'Valid compatibility report contains errors.');
$assert($validReport->entityIdentifiers === ['REFERENCE_CATALOG', 'TENANT_CHILD', 'TENANT_RECORD'], 'Entity identifiers are not deterministic.');
$assert($validReport->vendor === VendorIdentifier::MARIADB_11, 'Report vendor changed.');
$assert(count($manifest->entities()) === 3, 'Physical mapping manifest count changed.');
$assert(count($tenantEntity->attributeIndex()) === 7, 'Physical entity attribute index changed.');
$assert($uuidMap->foreignKeyCompatibilityWith($uuidMap) === ForeignKeyCompatibility::COMPATIBLE, 'Equivalent UUID mappings were incompatible.');
$assert($uuidMap->foreignKeyCompatibilityWith($stringMap) === ForeignKeyCompatibility::INCOMPATIBLE, 'Different mappings were considered compatible.');

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

// Canonical identifiers and reserved namespace policy.
$identifier = new DataDefinitionIdentifier(' tenant_record ');
$assert($identifier->value === 'TENANT_RECORD', 'Identifier was not canonicalized.');
$assert(json_encode($identifier, JSON_THROW_ON_ERROR) === '"TENANT_RECORD"', 'Identifier JSON changed.');
foreach (['', 'bad-name', 'A B', '/path', '9INVALID'] as $invalid) {
    $throwsCode(fn () => new DataDefinitionIdentifier($invalid), DataDefinitionException::IDENTIFIER_INVALID);
}
foreach (['SYS_AUDIT', 'SQL_OBJECT', 'MYSQL_META', 'PG_INTERNAL', 'INFORMATION_SCHEMA', 'ONEQAY_INTERNAL_CACHE'] as $reserved) {
    $throwsCode(fn () => new DataDefinitionIdentifier($reserved), DataDefinitionException::RESERVED_NAMESPACE);
}

// Portable scalar types and local constraints.
foreach ([PortableScalarType::STRING, PortableScalarType::INTEGER, PortableScalarType::DECIMAL, PortableScalarType::BOOLEAN, PortableScalarType::UUID, PortableScalarType::DATE, PortableScalarType::DATETIME, PortableScalarType::JSON] as $type) {
    $assert((new PortableScalarType(strtolower($type)))->value === $type, 'Portable scalar type normalization failed.');
}
$throwsCode(fn () => new PortableScalarType('MONEY'), DataDefinitionException::SCALAR_TYPE_INVALID);
$stringConstraint = new ValueConstraint(new PortableScalarType('STRING'), 128);
$decimalConstraint = new ValueConstraint(new PortableScalarType('DECIMAL'), null, 18, 2);
$uuidConstraint = new ValueConstraint(new PortableScalarType('UUID'));
$integerConstraint = new ValueConstraint(new PortableScalarType('INTEGER'));
$assert($stringConstraint->length === 128, 'String length changed.');
$assert($decimalConstraint->precision === 18 && $decimalConstraint->scale === 2, 'Decimal constraint changed.');
foreach ([
    fn () => new ValueConstraint(new PortableScalarType('STRING')),
    fn () => new ValueConstraint(new PortableScalarType('STRING'), 0),
    fn () => new ValueConstraint(new PortableScalarType('DECIMAL'), null, 39, 2),
    fn () => new ValueConstraint(new PortableScalarType('DECIMAL'), null, 8, 9),
    fn () => new ValueConstraint(new PortableScalarType('INTEGER'), 8),
] as $invalidConstraint) {
    $throwsCode($invalidConstraint, DataDefinitionException::CONSTRAINT_INVALID);
}

// Default policy stores fingerprints rather than literal material.
$sensitiveMarker = 'synthetic-sensitive-' . hash('sha256', 'sprint10-marker');
$syntheticPath = '/' . implode('/', ['private', 'synthetic', 'location']);
$literalDefault = DefaultValueDefinition::literal($sensitiveMarker . $syntheticPath);
$encodedDefault = json_encode($literalDefault, JSON_THROW_ON_ERROR);
$assert($literalDefault->policy === DefaultValuePolicy::LITERAL_FINGERPRINT, 'Literal policy changed.');
$assert(strlen((string) $literalDefault->fingerprint) === 64, 'Literal fingerprint is invalid.');
$assert(!str_contains($encodedDefault, $sensitiveMarker), 'Default JSON leaked literal material.');
$assert(!str_contains(serialize($literalDefault), $syntheticPath), 'Default serialization leaked path material.');
$generatedDefault = DefaultValueDefinition::generated('GENERATED_CLOCK');
$assert($generatedDefault->generatedIdentifier?->value === 'GENERATED_CLOCK', 'Generated default identifier changed.');
$throwsCode(
    fn () => new AttributeDefinition(
        new DataDefinitionIdentifier('INVALID_REQUIRED_DEFAULT'),
        $stringConstraint,
        NullabilityPolicy::REQUIRED,
        DefaultValueDefinition::nullValue(),
    ),
    DataDefinitionException::DEFAULT_INVALID,
);

// Reusable attributes.
$globalId = new AttributeDefinition(new DataDefinitionIdentifier('ID'), $uuidConstraint, NullabilityPolicy::REQUIRED, DefaultValueDefinition::none());
$tenantId = new AttributeDefinition(new DataDefinitionIdentifier('TENANT_ID'), $uuidConstraint, NullabilityPolicy::REQUIRED, DefaultValueDefinition::none());
$recordId = new AttributeDefinition(new DataDefinitionIdentifier('RECORD_ID'), $uuidConstraint, NullabilityPolicy::REQUIRED, DefaultValueDefinition::none());
$code = new AttributeDefinition(new DataDefinitionIdentifier('CODE'), $stringConstraint, NullabilityPolicy::REQUIRED, DefaultValueDefinition::none());
$parentId = new AttributeDefinition(new DataDefinitionIdentifier('PARENT_ID'), $uuidConstraint, NullabilityPolicy::REQUIRED, DefaultValueDefinition::none());
$count = new AttributeDefinition(new DataDefinitionIdentifier('COUNT_VALUE'), $integerConstraint, NullabilityPolicy::NULLABLE, DefaultValueDefinition::nullValue());

$globalEntity = new EntityDefinition(
    new DataDefinitionIdentifier('REFERENCE_CATALOG'),
    TenantScope::GLOBAL,
    [$globalId, $code],
    new PrimaryKeyDefinition(['ID']),
    [new UniqueConstraintDefinition(new DataDefinitionIdentifier('UNIQUE_REFERENCE_CODE'), ['CODE'])],
);

$tenantEntity = new EntityDefinition(
    new DataDefinitionIdentifier('TENANT_RECORD'),
    TenantScope::TENANT_SCOPED,
    [$tenantId, $recordId, $code],
    new PrimaryKeyDefinition(['TENANT_ID', 'RECORD_ID']),
    [new UniqueConstraintDefinition(new DataDefinitionIdentifier('UNIQUE_TENANT_CODE'), ['TENANT_ID', 'CODE'])],
    [],
    new DataDefinitionIdentifier('TENANT_ID'),
);

$childEntity = new EntityDefinition(
    new DataDefinitionIdentifier('TENANT_CHILD'),
    TenantScope::TENANT_SCOPED,
    [$tenantId, $recordId, $parentId, $count],
    new PrimaryKeyDefinition(['TENANT_ID', 'RECORD_ID']),
    [],
    [new ReferenceDefinition(
        new DataDefinitionIdentifier('REFERENCE_TENANT_PARENT'),
        new DataDefinitionIdentifier('TENANT_RECORD'),
        ['TENANT_ID' => 'TENANT_ID', 'PARENT_ID' => 'RECORD_ID'],
    )],
    new DataDefinitionIdentifier('TENANT_ID'),
);

$manifest = new DataDefinitionManifest([$globalEntity, $tenantEntity, $childEntity]);
$validator = new DataDefinitionPolicyValidator();
$validReport = $validator->validate($manifest, 'corr-definition-001');
$assert($validReport->isValid, 'Valid data definition manifest was rejected.');
$assert($validReport->errorCodes === [], 'Valid report contains errors.');
$assert($validReport->entityIdentifiers === ['REFERENCE_CATALOG', 'TENANT_CHILD', 'TENANT_RECORD'], 'Entity identifiers are not deterministic.');
$assert(count($manifest->entities()) === 3, 'Manifest entity count changed.');
$assert(count($tenantEntity->attributeIndex()) === 3, 'Entity attribute index changed.');


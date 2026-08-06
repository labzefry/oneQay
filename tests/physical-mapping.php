<?php

declare(strict_types=1);

require __DIR__ . '/../src/PhysicalMapping/Foundation.php';

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

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$throwsCode = static function (callable $callback, string $code) use ($assert): void {
    try {
        $callback();
        $assert(false, "Expected {$code}.");
    } catch (PhysicalMappingException $exception) {
        $assert($exception->errorCode === $code, "Unexpected {$exception->errorCode}.");
    }
};

require __DIR__ . '/physical-mapping-contracts.php';
require __DIR__ . '/physical-mapping-policy.php';

fwrite(STDOUT, sprintf("Physical Mapping and Vendor Compatibility tests passed: %d assertions.\n", $assertions));

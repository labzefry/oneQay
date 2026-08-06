<?php

declare(strict_types=1);

require __DIR__ . '/../src/DataDefinition/Foundation.php';

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
    } catch (DataDefinitionException $exception) {
        $assert($exception->errorCode === $code, "Unexpected {$exception->errorCode}.");
    }
};


require __DIR__ . '/data-definition-contracts.php';
require __DIR__ . '/data-definition-policy.php';

fwrite(STDOUT, sprintf("Data Definition and Tenant Isolation Policy tests passed: %d assertions.\n", $assertions));

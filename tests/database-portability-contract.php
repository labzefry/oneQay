<?php

declare(strict_types=1);

require __DIR__ . '/../src/Portability/Foundation.php';

use OneQay\Portability\DatabasePortabilityContract;
use OneQay\Portability\EngineProfileDirection;
use OneQay\Portability\PortabilityContractException;
use OneQay\Portability\PortabilityLayer;
use OneQay\Portability\PortabilitySourceUnit;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$throwsCode = static function (callable $callback, string $expected) use ($assert): void {
    try {
        $callback();
        $assert(false, "Expected {$expected}.");
    } catch (PortabilityContractException $exception) {
        $assert($exception->errorCode === $expected, "Unexpected {$exception->errorCode}.");
    }
};

$collectPhpUnits = static function (string $root, PortabilityLayer $layer): array {
    if (!is_dir($root)) {
        throw new RuntimeException("Portability scan root is missing: {$root}");
    }

    $units = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $source = file_get_contents($file->getPathname());
        if (!is_string($source) || $source === '') {
            throw new RuntimeException("Portability source could not be read: {$file->getPathname()}");
        }

        $relative = str_replace(dirname(__DIR__) . '/', '', $file->getPathname());
        $units[] = new PortabilitySourceUnit($layer, $relative, $source);
    }

    return $units;
};

// Approved engine-profile directions are architectural directions only; unknown profiles fail closed.
foreach (['MARIADB', 'MYSQL', 'POSTGRESQL'] as $profile) {
    $assert((new EngineProfileDirection($profile))->value === $profile, "Known profile direction {$profile} was rejected.");
}
$throwsCode(
    fn () => new EngineProfileDirection('SQLITE'),
    PortabilityContractException::PROFILE_DIRECTION_UNSUPPORTED,
);

$repositoryRoot = dirname(__DIR__);
$logicalRoots = [
    $repositoryRoot . '/apps/web/app/Domain',
    $repositoryRoot . '/apps/web/app/Application',
    $repositoryRoot . '/src/Auth',
    $repositoryRoot . '/src/Authorization',
    $repositoryRoot . '/src/Tenant',
    $repositoryRoot . '/src/DataDefinition',
];
$infrastructureRoots = [
    $repositoryRoot . '/src/Persistence',
    $repositoryRoot . '/src/PhysicalMapping',
    $repositoryRoot . '/apps/web/app/Infrastructure/Persistence',
];

$units = [];
foreach ($logicalRoots as $root) {
    $units = array_merge($units, $collectPhpUnits($root, PortabilityLayer::LOGICAL_BUSINESS));
}
foreach ($infrastructureRoots as $root) {
    $units = array_merge($units, $collectPhpUnits($root, PortabilityLayer::INFRASTRUCTURE));
}

$contract = new DatabasePortabilityContract();
$report = $contract->evaluate($units, 'corr-portability-001');
$assert($report->isConformant, 'Canonical logical business code violates the Database Portability Contract.');
$assert($report->errorCodes === [], 'Conformant portability report contains errors.');
$assert($report->logicalBusinessFiles > 0, 'No logical business files were inspected.');
$assert($report->infrastructureFiles > 0, 'No bounded Infrastructure files were inspected.');
$assert($report->correlationId === 'corr-portability-001', 'Portability correlation ID changed.');

// Vendor coupling is permitted only in the bounded Infrastructure classification.
$infrastructureOnly = $contract->evaluate([
    new PortabilitySourceUnit(
        PortabilityLayer::LOGICAL_BUSINESS,
        'synthetic/domain/EngineNeutralRule.php',
        '<?php final class EngineNeutralRule { public function total(int $a, int $b): int { return $a + $b; } }',
    ),
    new PortabilitySourceUnit(
        PortabilityLayer::INFRASTRUCTURE,
        'synthetic/infrastructure/MariaDbAdapter.php',
        "<?php final class MariaDbAdapter { private string \$driver = 'pdo_mysql'; }",
    ),
], 'corr-portability-002');
$assert($infrastructureOnly->isConformant, 'Bounded Infrastructure vendor coupling was rejected.');

$vendorLeak = $contract->evaluate([
    new PortabilitySourceUnit(
        PortabilityLayer::LOGICAL_BUSINESS,
        'synthetic/application/VendorBranch.php',
        "<?php final class VendorBranch { private string \$driver = 'mysql'; }",
    ),
], 'corr-portability-003');
$assert(!$vendorLeak->isConformant, 'Vendor dependency leaked into logical business code.');
$assert(
    in_array(PortabilityContractException::VENDOR_DEPENDENCY_IN_LOGICAL_BUSINESS, $vendorLeak->errorCodes, true),
    'Vendor leakage did not fail closed.',
);

$sqlLeak = $contract->evaluate([
    new PortabilitySourceUnit(
        PortabilityLayer::LOGICAL_BUSINESS,
        'synthetic/domain/RawSqlRule.php',
        "<?php final class RawSqlRule { private string \$query = 'SELECT id FROM sales'; }",
    ),
], 'corr-portability-004');
$assert(!$sqlLeak->isConformant, 'Raw SQL leaked into logical business code.');
$assert(
    in_array(PortabilityContractException::RAW_SQL_IN_LOGICAL_BUSINESS, $sqlLeak->errorCodes, true),
    'Raw SQL leakage did not fail closed.',
);

$noBusinessEvidence = $contract->evaluate([
    new PortabilitySourceUnit(
        PortabilityLayer::INFRASTRUCTURE,
        'synthetic/infrastructure/OnlyAdapter.php',
        "<?php final class OnlyAdapter {}",
    ),
], 'corr-portability-005');
$assert(!$noBusinessEvidence->isConformant, 'Missing logical business evidence was accepted.');
$assert(
    in_array(PortabilityContractException::NO_LOGICAL_BUSINESS_EVIDENCE, $noBusinessEvidence->errorCodes, true),
    'Missing logical business evidence did not fail closed.',
);

// Reports contain paths and classifications only; source text and credential-shaped values are not serialized.
$encoded = json_encode($report, JSON_THROW_ON_ERROR);
foreach (['DB_PASSWORD', 'DB_USER', 'DB_HOST', 'mysql:host=', 'SELECT id FROM sales'] as $forbidden) {
    $assert(!str_contains($encoded, $forbidden), 'Portability report leaked source or configuration material.');
}

fwrite(
    STDOUT,
    sprintf("Database Portability Contract conformance tests passed: %d assertions.\n", $assertions)
);

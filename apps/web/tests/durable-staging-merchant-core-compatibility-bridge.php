<?php

declare(strict_types=1);

// Author by Lab | zefry

require_once __DIR__.'/../app/Application/Runtime/DurableStagingRuntimeBridge.php';

use App\Application\Runtime\DurableStagingRuntimeBridge;

$expect = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "Sprint203 regression failed: {$message}\n");
        exit(1);
    }
};

foreach (['local', 'test', 'ci'] as $runtime) {
    $expect(
        DurableStagingRuntimeBridge::deliveryAllowed($runtime, false),
        "{$runtime} must preserve historical delivery eligibility.",
    );
    $expect(
        DurableStagingRuntimeBridge::repositoryRuntimeClass($runtime, false) === $runtime,
        "{$runtime} must preserve its legacy repository runtime class.",
    );
}

$expect(
    ! DurableStagingRuntimeBridge::deliveryAllowed('staging', false),
    'staging must remain denied when the explicit staging gate is false.',
);
$expect(
    DurableStagingRuntimeBridge::deliveryAllowed(' staging ', true),
    'explicitly armed staging must be delivery-eligible.',
);
$expect(
    DurableStagingRuntimeBridge::repositoryRuntimeClass('staging', false) === 'denied',
    'unarmed staging must project a denied repository runtime.',
);
$expect(
    DurableStagingRuntimeBridge::repositoryRuntimeClass('STAGING', true) === 'ci',
    'armed staging must project the legacy non-production compatibility token.',
);
$expect(
    ! DurableStagingRuntimeBridge::deliveryAllowed('production', true),
    'production must remain source-denied even when the staging gate is armed.',
);
$expect(
    DurableStagingRuntimeBridge::repositoryRuntimeClass('production', true) === 'denied',
    'production must never receive a permitted repository compatibility token.',
);
$expect(
    DurableStagingRuntimeBridge::externalRuntime(' STAGING ') === 'staging',
    'external runtime identity must remain staging.',
);

$root = dirname(__DIR__);
$read = static function (string $relative) use ($root): string {
    $content = file_get_contents($root.'/'.$relative);
    if (! is_string($content) || $content === '') {
        fwrite(STDERR, "Unable to read Sprint203 source: {$relative}\n");
        exit(1);
    }

    return $content;
};

$config = $read('config/oneqay.php');
$appProvider = $read('app/Providers/AppServiceProvider.php');
$routes = $read('routes/web.php');
$layout = $read('resources/views/app.blade.php');
$session = $read('app/Delivery/Http/Identity/FirstPartySessionController.php');
$bootstrap = $read('app/Console/Commands/MerchantContextBootstrapCommand.php');

foreach ([
    "'durable_staging_runtime' => [",
    "env('ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED', false)",
] as $needle) {
    $expect(str_contains($config, $needle), "missing fail-closed staging config: {$needle}");
}

foreach ([
    'DurableStagingRuntimeBridge::repositoryRuntimeClass(',
    "config('oneqay.durable_staging_runtime.enabled', false)",
] as $needle) {
    $expect(str_contains($appProvider, $needle), "AppServiceProvider missing staging bridge: {$needle}");
}

foreach ([
    '$localTestCiRuntime = in_array(',
    'DurableStagingRuntimeBridge::deliveryAllowed(',
    '$localTestCiRuntime && (bool) config(\'oneqay.pos_sale_void.enabled\', false)',
    "if (\$localTestCiRuntime\n    && \$sessionControlEnabled\n    && (bool) config('oneqay.pos_sale_cash_refund.enabled', false))",
    "if (\$localTestCiRuntime\n    && \$sessionControlEnabled\n    && (bool) config('oneqay.pos_shift_closing_cash_evidence.enabled', false))",
] as $needle) {
    $expect(str_contains($routes, $needle), "route staging boundary missing: {$needle}");
}

$expect(
    str_contains($layout, 'DurableStagingRuntimeBridge::deliveryAllowed('),
    'merchant entry must use the explicit staging delivery bridge.',
);
$expect(
    str_contains($session, 'DurableStagingRuntimeBridge::deliveryAllowed('),
    'first-party session establishment must use the explicit staging delivery bridge.',
);

foreach ([
    'DurableStagingRuntimeBridge::deliveryAllowed(',
    'DurableStagingRuntimeBridge::repositoryRuntimeClass(',
    '$repositoryRuntimeClass',
    'Local/Test/CI/Staging runtime',
] as $needle) {
    $expect(str_contains($bootstrap, $needle), "merchant bootstrap staging bridge missing: {$needle}");
}

foreach ([
    'app/Providers/PosCatalogInventorySetupWorkspaceServiceProvider.php',
    'app/Providers/PosShiftStartWorkspaceServiceProvider.php',
    'app/Providers/PosCashierWorkspaceServiceProvider.php',
] as $relative) {
    $source = $read($relative);
    $expect(
        str_contains($source, 'DurableStagingRuntimeBridge::repositoryRuntimeClass('),
        "{$relative} must project staging through the legacy repository compatibility bridge.",
    );
    $expect(
        str_contains($source, 'DurableStagingRuntimeBridge::deliveryAllowed('),
        "{$relative} must independently gate route delivery.",
    );
}

$hub = $read('app/Providers/PosOperationsHubServiceProvider.php');
$expect(
    str_contains($hub, 'DurableStagingRuntimeBridge::deliveryAllowed('),
    'POS Operations Hub must explicitly gate staging delivery.',
);

// Historical repositories remain untouched and continue to reject unknown runtime
// classes directly. This is deliberate: the compatibility bridge is owned by
// composition roots, not by dozens of legacy repository implementations.
foreach ([
    'app/Infrastructure/Persistence/LaravelPersistenceTransaction.php',
    'app/Infrastructure/Access/LaravelDurableOrganizationalAccessRepository.php',
    'app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php',
    'app/Infrastructure/Identity/LaravelFirstPartyIdentityCredentialVerifier.php',
    'app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php',
    'app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php',
    'app/Infrastructure/Pos/LaravelCatalogPreparationRepository.php',
    'app/Infrastructure/Pos/LaravelInventoryBaselineRepository.php',
    'app/Infrastructure/Pos/LaravelShiftOpeningRepository.php',
    'app/Infrastructure/Pos/LaravelShiftOpeningCashRepository.php',
    'app/Infrastructure/Pos/LaravelPosCatalogInventorySetupWorkspaceRepository.php',
    'app/Infrastructure/Pos/LaravelPosShiftStartWorkspaceRepository.php',
    'app/Infrastructure/Pos/LaravelPosCashierWorkspaceRepository.php',
] as $relative) {
    $source = $read($relative);
    $expect(
        str_contains($source, "['local', 'test', 'ci']"),
        "{$relative} historical runtime guard must remain byte-semantically local/test/ci.",
    );
    $expect(
        ! str_contains($source, "['local', 'test', 'ci', 'staging']"),
        "{$relative} must not be widened directly to staging.",
    );
}

$combined = implode("\n", [$config, $appProvider, $routes, $layout, $session, $bootstrap, $hub]);
foreach ([
    'ONEQAY_PRODUCTION_MERCHANT_RUNTIME_ENABLED',
    'ONEQAY_PRODUCTION_POS_DELIVERY_ENABLED',
    "['local', 'test', 'ci', 'production']",
    "['local', 'test', 'ci', 'staging', 'production']",
] as $forbidden) {
    $expect(! str_contains($combined, $forbidden), "forbidden production source expansion detected: {$forbidden}");
}

echo "Sprint203 durable staging merchant core compatibility bridge regression passed.\n";

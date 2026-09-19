<?php

declare(strict_types=1);

// Author by Lab | zefry

$root = dirname(__DIR__);

$read = static function (string $relative) use ($root): string {
    $path = $root.'/'.$relative;
    $content = file_get_contents($path);
    if (! is_string($content) || $content === '') {
        fwrite(STDERR, "Unable to read Sprint203 source: {$relative}\n");
        exit(1);
    }

    return $content;
};

$config = $read('config/oneqay.php');
$routes = $read('routes/web.php');
$layout = $read('resources/views/app.blade.php');
$sessionController = $read('app/Delivery/Http/Identity/FirstPartySessionController.php');
$bootstrapCommand = $read('app/Console/Commands/MerchantContextBootstrapCommand.php');
$appProvider = $read('app/Providers/AppServiceProvider.php');

foreach ([
    "env('ONEQAY_DURABLE_STAGING_RUNTIME_ENABLED', false)",
    "'durable_staging_runtime' => [",
    "Production is intentionally excluded from the source allowlist.",
] as $needle) {
    if (! str_contains($config, $needle)) {
        fwrite(STDERR, "Missing Sprint203 staging config contract: {$needle}\n");
        exit(1);
    }
}

foreach ([
    '$merchantRuntimeAllowed = in_array($firstPartyAuthRuntime',
    "$firstPartyAuthRuntime === 'staging'",
    "config('oneqay.durable_staging_runtime.enabled', false)",
] as $needle) {
    if (! str_contains($routes, $needle)) {
        fwrite(STDERR, "Missing Sprint203 staging route gate: {$needle}\n");
        exit(1);
    }
}

foreach ([
    '$merchantRuntimeClass = strtolower',
    "$merchantRuntimeClass === 'staging'",
    "config('oneqay.durable_staging_runtime.enabled', false)",
] as $needle) {
    if (! str_contains($layout, $needle)) {
        fwrite(STDERR, "Missing Sprint203 staging merchant-entry gate: {$needle}\n");
        exit(1);
    }
}

foreach ([
    "$runtime === 'staging'",
    "config('oneqay.durable_staging_runtime.enabled', false)",
    'abort_unless($allowed, 404);',
] as $needle) {
    if (! str_contains($sessionController, $needle)) {
        fwrite(STDERR, "Missing Sprint203 staging session gate: {$needle}\n");
        exit(1);
    }
}

foreach ([
    "$runtimeClass === 'staging'",
    "config('oneqay.durable_staging_runtime.enabled', false)",
    'if (! $runtimeAllowed',
    'Local/Test/CI/Staging runtime',
] as $needle) {
    if (! str_contains($bootstrapCommand, $needle)) {
        fwrite(STDERR, "Missing Sprint203 staging bootstrap gate: {$needle}\n");
        exit(1);
    }
}

foreach ([
    "$runtime === 'staging'",
    "config('oneqay.durable_staging_runtime.enabled', false)",
    "return 'staging-denied';",
] as $needle) {
    if (! str_contains($appProvider, $needle)) {
        fwrite(STDERR, "Missing Sprint203 provider runtime sanitization: {$needle}\n");
        exit(1);
    }
}

$explicitDeliveryProviders = [
    'app/Providers/PosCatalogInventorySetupWorkspaceServiceProvider.php',
    'app/Providers/PosShiftStartWorkspaceServiceProvider.php',
    'app/Providers/PosCashierWorkspaceServiceProvider.php',
    'app/Providers/PosOperationsHubServiceProvider.php',
];

foreach ($explicitDeliveryProviders as $relative) {
    $source = $read($relative);
    foreach ([
        '$runtimeAllowed = in_array($runtimeClass',
        "$runtimeClass === 'staging'",
        "config('oneqay.durable_staging_runtime.enabled', false)",
        'if (! $runtimeAllowed',
    ] as $needle) {
        if (! str_contains($source, $needle)) {
            fwrite(STDERR, "Missing Sprint203 explicit provider staging gate in {$relative}: {$needle}\n");
            exit(1);
        }
    }
}

$stagingAwareRepositories = [
    'app/Infrastructure/Persistence/LaravelPersistenceTransaction.php',
    'app/Infrastructure/Persistence/LaravelDurableContextGraphRepository.php',
    'app/Infrastructure/Access/LaravelDurableOrganizationalAccessRepository.php',
    'app/Infrastructure/Authorization/LaravelDurableRolePermissionRepository.php',
    'app/Infrastructure/Authorization/LaravelInitialTenantAdministratorProvisioningRepository.php',
    'app/Infrastructure/Authorization/LaravelDurablePolicyAdministrationRepository.php',
    'app/Infrastructure/Bootstrap/LaravelMerchantContextBootstrapStateRepository.php',
    'app/Infrastructure/Identity/LaravelFirstControlPrincipalCredentialBootstrapRepository.php',
    'app/Infrastructure/Identity/LaravelFirstPartyCredentialEpochRepository.php',
    'app/Infrastructure/Identity/LaravelFirstPartyIdentityCredentialVerifier.php',
    'app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityVerifier.php',
    'app/Infrastructure/Identity/LaravelFirstPartySessionAuthorityRepository.php',
    'app/Infrastructure/Identity/LaravelPrivilegedTotpFactorEpochRepository.php',
    'app/Infrastructure/Identity/LaravelPrivilegedTotpMfaRepository.php',
    'app/Infrastructure/Pos/LaravelCatalogPreparationRepository.php',
    'app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php',
    'app/Infrastructure/Pos/LaravelInventoryBaselineRepository.php',
    'app/Infrastructure/Pos/LaravelPosCashierWorkspaceRepository.php',
    'app/Infrastructure/Pos/LaravelPosCatalogInventorySetupWorkspaceRepository.php',
    'app/Infrastructure/Pos/LaravelPosShiftStartWorkspaceRepository.php',
    'app/Infrastructure/Pos/LaravelShiftOpeningCashRepository.php',
    'app/Infrastructure/Pos/LaravelShiftOpeningRepository.php',
];

foreach ($stagingAwareRepositories as $relative) {
    $source = $read($relative);
    if (! str_contains($source, "['local', 'test', 'ci', 'staging']")) {
        fwrite(STDERR, "Sprint203 staging allowlist missing in {$relative}.\n");
        exit(1);
    }
    if (str_contains($source, "['local', 'test', 'ci', 'staging', 'production']")
        || str_contains($source, "['local', 'test', 'ci', 'production']")) {
        fwrite(STDERR, "Production runtime was incorrectly added to {$relative}.\n");
        exit(1);
    }
}

$combined = implode("\n", [
    $config,
    $routes,
    $layout,
    $sessionController,
    $bootstrapCommand,
    $appProvider,
]);

foreach ([
    'ONEQAY_PRODUCTION_MERCHANT_RUNTIME_ENABLED',
    'ONEQAY_PRODUCTION_POS_DELIVERY_ENABLED',
    "'production' => true",
] as $forbidden) {
    if (str_contains($combined, $forbidden)) {
        fwrite(STDERR, "Forbidden Sprint203 production activation source detected: {$forbidden}\n");
        exit(1);
    }
}

echo "Sprint203 durable staging merchant core runtime enablement regression passed.\n";

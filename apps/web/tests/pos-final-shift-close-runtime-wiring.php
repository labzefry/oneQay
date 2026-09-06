<?php

declare(strict_types=1);

// Author by Lab | zefry

$root = dirname(__DIR__);
$provider = file_get_contents($root.'/app/Providers/FinalShiftCloseServiceProvider.php');
$providers = file_get_contents($root.'/bootstrap/providers.php');
$config = file_get_contents($root.'/config/oneqay.php');

if ($provider === false || $providers === false || $config === false) {
    fwrite(STDERR, "Sprint96 runtime wiring source is unreadable.\n");
    exit(1);
}

$requiredProviderFragments = [
    'scoped(LaravelExpectedCashSnapshotReader::class',
    'scoped(DeriveCashVariance::class',
    'scoped(FinalShiftCloseAuthorizationPolicy::class',
    'scoped(CloseShiftRepository::class',
    'new LaravelCloseShiftRepository(',
    "config('oneqay.pos_shift_close.enabled', false)",
    'scoped(CloseShift::class',
    'scoped(ShiftCloseClock::class',
    '$app->make(OrganizationalContextStore::class)',
    '$app->make(DurableScopedAuthorizationPolicy::class)',
    '$app->make(PersistenceTransaction::class)',
];

foreach ($requiredProviderFragments as $fragment) {
    if (! str_contains($provider, $fragment)) {
        fwrite(STDERR, "Missing Sprint96 provider contract: {$fragment}\n");
        exit(1);
    }
}

if (! str_contains($providers, 'App\\Providers\\FinalShiftCloseServiceProvider::class')) {
    fwrite(STDERR, "Final Shift Close provider is not registered.\n");
    exit(1);
}

$appPosition = strpos($providers, 'App\\Providers\\AppServiceProvider::class');
$closePosition = strpos($providers, 'App\\Providers\\FinalShiftCloseServiceProvider::class');
$previewPosition = strpos($providers, 'App\\Providers\\TechnicalPreviewServiceProvider::class');
if ($appPosition === false || $closePosition === false || $previewPosition === false || ! ($appPosition < $closePosition && $closePosition < $previewPosition)) {
    fwrite(STDERR, "Final Shift Close provider registration order is invalid.\n");
    exit(1);
}

if (
    ! str_contains($config, "'pos_shift_close' => [")
    || ! str_contains($config, "env('ONEQAY_POS_SHIFT_CLOSE_ENABLED', false)")
) {
    fwrite(STDERR, "Final Shift Close feature flag is not fail-closed by default.\n");
    exit(1);
}

$routeFiles = glob($root.'/routes/*.php') ?: [];
foreach ($routeFiles as $routeFile) {
    $routeSource = file_get_contents($routeFile);
    if ($routeSource !== false && (str_contains($routeSource, 'CloseShift') || str_contains($routeSource, 'pos.shift.close'))) {
        fwrite(STDERR, "Sprint96 must not expose Final Shift Close through a route.\n");
        exit(1);
    }
}

echo "Sprint96 Final Shift Close runtime wiring regression passed.\n";

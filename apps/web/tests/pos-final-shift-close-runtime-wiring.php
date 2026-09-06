<?php

declare(strict_types=1);

// Author by Lab | zefry

$root = dirname(__DIR__);
$provider = file_get_contents($root.'/app/Providers/FinalShiftCloseServiceProvider.php');
$bootstrap = file_get_contents($root.'/bootstrap/app.php');

if ($provider === false || $bootstrap === false) {
    fwrite(STDERR, "Sprint96 runtime wiring source is unreadable.\n");
    exit(1);
}

$requiredProviderFragments = [
    'scoped(LaravelExpectedCashSnapshotReader::class',
    'scoped(DeriveCashVariance::class',
    'scoped(FinalShiftCloseAuthorizationPolicy::class',
    'scoped(CloseShiftRepository::class',
    'new LaravelCloseShiftRepository(',
    '$this->featureEnabled()',
    'scoped(CloseShift::class',
    'scoped(ShiftCloseClock::class',
    '$app->make(OrganizationalContextStore::class)',
    '$app->make(DurableScopedAuthorizationPolicy::class)',
    '$app->make(PersistenceTransaction::class)',
    "env('ONEQAY_POS_SHIFT_CLOSE_ENABLED', false)",
    'FILTER_VALIDATE_BOOL',
];

foreach ($requiredProviderFragments as $fragment) {
    if (! str_contains($provider, $fragment)) {
        fwrite(STDERR, "Missing Sprint96 provider contract: {$fragment}\n");
        exit(1);
    }
}

if (
    ! str_contains($bootstrap, 'use App\\Providers\\FinalShiftCloseServiceProvider;')
    || ! str_contains($bootstrap, '->withProviders([')
    || ! str_contains($bootstrap, 'FinalShiftCloseServiceProvider::class,')
) {
    fwrite(STDERR, "Final Shift Close provider is not registered at the application bootstrap boundary.\n");
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

$sharedProviders = file_get_contents($root.'/bootstrap/providers.php');
$sharedConfig = file_get_contents($root.'/config/oneqay.php');
if ($sharedProviders === false || $sharedConfig === false) {
    fwrite(STDERR, "Canonical shared runtime files are unreadable.\n");
    exit(1);
}
if (str_contains($sharedProviders, 'FinalShiftCloseServiceProvider') || str_contains($sharedConfig, 'pos_shift_close')) {
    fwrite(STDERR, "Sprint96 must not widen historical shared provider/config horizons.\n");
    exit(1);
}

echo "Sprint96 Final Shift Close runtime wiring regression passed.\n";

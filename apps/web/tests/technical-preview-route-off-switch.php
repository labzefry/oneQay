<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$mode = $argv[1] ?? '';
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        fwrite(STDERR, "Technical Preview route off-switch regression failed: {$case}\n");
        exit(1);
    }
};

$expectedPresent = match ($mode) {
    'preview-enabled', 'qualification-enabled' => true,
    'preview-disabled', 'production-denied' => false,
    default => throw new InvalidArgumentException('Unknown route qualification mode.'),
};

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$bootstrapRequest = Request::create('/health/live', 'GET');
$bootstrapResponse = $kernel->handle($bootstrapRequest);
$assert($bootstrapResponse->getStatusCode() === 200, 'TPROUTE-001 application bootstrap remains healthy');
$kernel->terminate($bootstrapRequest, $bootstrapResponse);

$routes = $app->make('router')->getRoutes();
$previewRouteNames = [
    'preview.index',
    'preview.sign-in',
    'preview.context',
    'preview.context.select',
    'preview.pos',
    'preview.sale',
    'preview.receipt',
    'preview.logout',
    'preview.database-qualification',
    'preview.shift.open',
    'preview.shift.close',
    'preview.reconciliation',
    'preview.sale.void',
    'preview.sale.refund',
    'preview.reconciliation.explanation',
    'preview.reconciliation.review',
];

foreach ($previewRouteNames as $routeName) {
    $registered = $routes->getByName($routeName) !== null;
    $assert(
        $registered === $expectedPresent,
        'TPROUTE-002 '.$mode.' route registration mismatch for '.$routeName,
    );
}

if (! $expectedPresent) {
    $previewRequest = Request::create('/technical-preview', 'GET');
    $previewResponse = $kernel->handle($previewRequest);
    $assert($previewResponse->getStatusCode() === 404, 'TPROUTE-003 disabled/denied Preview surface returns 404');
    $kernel->terminate($previewRequest, $previewResponse);

    $assert(
        config('oneqay.technical_preview.enabled', false) !== true,
        'TPROUTE-004 disabled/denied runtime never arms controller-facing Preview authority',
    );
} else {
    $assert(
        config('oneqay.technical_preview.enabled', false) === true,
        'TPROUTE-005 permitted runtime arms controller-facing Preview authority',
    );

    $runtimeClass = strtolower(trim((string) config('oneqay.runtime_class', '')));
    if ($mode === 'preview-enabled') {
        $assert($runtimeClass === 'preview', 'TPROUTE-006 deployed Preview runtime alias is exact');
        $assert(config('session.driver') === 'file', 'TPROUTE-007 deployed Preview keeps file session driver');
        $assert(config('session.lifetime') === 60, 'TPROUTE-008 deployed Preview keeps 60-minute session lifetime');
        $assert(config('session.encrypt') === true, 'TPROUTE-009 deployed Preview keeps encrypted session payload');
        $assert(config('session.secure') === true, 'TPROUTE-010 deployed Preview keeps Secure cookie');
        $assert(config('session.cookie') === 'oneqay-preview-session', 'TPROUTE-011 deployed Preview keeps dedicated cookie namespace');
    } else {
        $assert($runtimeClass === 'ci', 'TPROUTE-012 CI qualification runtime remains explicitly available');
    }
}

echo "Technical Preview route off-switch regression passed for {$mode}.\n";

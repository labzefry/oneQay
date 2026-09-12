<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;
use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware;
use App\Delivery\Http\Middleware\RequireFinalShiftCloseRuntimeBindingTokenMiddleware;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationController;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeDbBindingAttestationController;
use Illuminate\Contracts\Http\Kernel;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException(
            'Final Shift Close canonical control-plane positive registration metadata regression failed: '.$case,
        );
    }
};

$validToken = str_repeat('A', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);

$assert(
    strlen($validToken) === FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH,
    'PRM-001 fixture remains exactly at the canonical minimum token length',
);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($validToken) === true,
    'PRM-002 canonical Sprint130 token policy accepts the synthetic fixture',
);

$environment = [
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_ENABLED' => 'true',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_TOKEN' => $validToken,
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_ENABLED' => 'true',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_TOKEN' => $validToken,
];

foreach ($environment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

/** @var Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$assert(method_exists($kernel, 'bootstrap'), 'PRM-003 HTTP kernel exposes bootstrap lifecycle');
$kernel->bootstrap();

$routes = $app->make('router')->getRoutes();
$materialization = $routes->getByName('internal.final-shift-close.runtime-binding-manifest.materialize');
$dbAttestation = $routes->getByName('internal.final-shift-close.runtime-db-binding-attestation');

$assert($materialization !== null, 'PRM-004 materialization route is registered for canonical valid token');
$assert($dbAttestation !== null, 'PRM-005 DB attestation route is registered for canonical valid token');

$assert(
    $materialization->uri() === 'internal/final-shift-close/runtime-binding-manifest/materialize',
    'PRM-006 materialization route keeps canonical URI',
);
$assert(
    in_array('POST', $materialization->methods(), true),
    'PRM-007 materialization route retains POST method',
);
$assert(
    ! in_array('GET', $materialization->methods(), true),
    'PRM-008 materialization route exposes no GET method',
);
$assert(
    $materialization->getActionName() === FinalShiftCloseRuntimeBindingManifestMaterializationController::class.'@__invoke',
    'PRM-009 materialization route targets canonical invokable controller',
);
$materializationMiddleware = $materialization->middleware();
$assert(
    in_array('throttle:1,1', $materializationMiddleware, true),
    'PRM-010 materialization route retains one-per-minute throttle',
);
$assert(
    in_array(RequireFinalShiftCloseRuntimeBindingMaterializationTokenMiddleware::class, $materializationMiddleware, true),
    'PRM-011 materialization route retains canonical token middleware',
);

$assert(
    $dbAttestation->uri() === 'internal/final-shift-close/runtime-db-binding-attestation',
    'PRM-012 DB attestation route keeps canonical URI',
);
$assert(
    strtoupper(implode(',', $dbAttestation->methods())) === 'GET,HEAD',
    'PRM-013 DB attestation route retains GET and HEAD methods only',
);
$assert(
    $dbAttestation->getActionName() === FinalShiftCloseRuntimeDbBindingAttestationController::class.'@__invoke',
    'PRM-014 DB attestation route targets canonical invokable controller',
);
$dbAttestationMiddleware = $dbAttestation->middleware();
$assert(
    in_array('throttle:2,1', $dbAttestationMiddleware, true),
    'PRM-015 DB attestation route retains two-per-minute throttle',
);
$assert(
    in_array(RequireFinalShiftCloseRuntimeBindingTokenMiddleware::class, $dbAttestationMiddleware, true),
    'PRM-016 DB attestation route retains canonical token middleware',
);

$unresolvedProductionServices = [
    FinalShiftCloseRuntimeBindingManifestWriter::class,
    FinalShiftCloseRuntimeBindingManifestMaterializer::class,
    FinalShiftCloseRuntimeDatabaseIdentityReader::class,
    FinalShiftCloseRuntimeDbBindingAttestation::class,
    FinalShiftCloseRuntimeBindingManifestMaterializationController::class,
    FinalShiftCloseRuntimeDbBindingAttestationController::class,
];

foreach ($unresolvedProductionServices as $service) {
    $assert(
        $app->resolved($service) === false,
        'PRM-017 positive route registration remains inert for production service: '.$service,
    );
}

fwrite(
    STDOUT,
    "Final Shift Close canonical control-plane positive registration metadata regression passed.\n",
);

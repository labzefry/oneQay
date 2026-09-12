<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestMaterializer;
use App\Application\Pos\FinalShiftCloseRuntimeBindingManifestWriter;
use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeBindingManifestMaterializationController;
use App\Delivery\Http\Pos\FinalShiftCloseRuntimeDbBindingAttestationController;
use Illuminate\Contracts\Http\Kernel;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException(
            'Final Shift Close runtime control-plane delivery-gate registration regression failed: '.$case,
        );
    }
};

$invalidToken = str_repeat('A', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH - 1).'!';

$assert(
    strlen($invalidToken) === FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH,
    'DG-001 fixture remains inside canonical token length boundary',
);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($invalidToken) === false,
    'DG-002 canonical token policy rejects the length-valid disallowed-character fixture',
);

$environment = [
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_ENABLED' => 'true',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_TOKEN' => $invalidToken,
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_ENABLED' => 'true',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_DB_BINDING_ATTESTATION_TOKEN' => $invalidToken,
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

$assert(method_exists($kernel, 'bootstrap'), 'DG-003 HTTP kernel exposes bootstrap lifecycle');
$kernel->bootstrap();

$routes = $app->make('router')->getRoutes();

$assert(
    $routes->getByName('internal.final-shift-close.runtime-binding-manifest.materialize') === null,
    'DG-004 materialization route remains absent for a length-valid disallowed-character token',
);
$assert(
    $routes->getByName('internal.final-shift-close.runtime-db-binding-attestation') === null,
    'DG-005 DB attestation route remains absent for a length-valid disallowed-character token',
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
        'DG-006 production control-plane service remains unresolved: '.$service,
    );
}

$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken(str_repeat('A', 32)) === true,
    'DG-007 canonical policy still distinguishes an otherwise valid token from the disallowed-character fixture',
);

fwrite(
    STDOUT,
    "Final Shift Close canonical control-plane delivery-gate registration regression passed.\n",
);

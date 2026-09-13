<?php

declare(strict_types=1);

use App\Application\Pos\FinalShiftCloseRuntimeBindingManifest;
use App\Application\Pos\FinalShiftCloseRuntimeControlPlaneTokenPolicy;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentity;
use App\Application\Pos\FinalShiftCloseRuntimeDatabaseIdentityReader;
use App\Application\Pos\FinalShiftCloseRuntimeDbBindingAttestation;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $case): void {
    if (! $condition) {
        throw new RuntimeException(
            'Sprint143 Final Shift Close HEAD throttle rejection response hardening regression failed: '.$case,
        );
    }
};

$validToken = str_repeat('H', FinalShiftCloseRuntimeControlPlaneTokenPolicy::MINIMUM_LENGTH);
$assert(
    FinalShiftCloseRuntimeControlPlaneTokenPolicy::isValidToken($validToken),
    'HEAD-THR-001 expected bearer fixture remains canonical-valid',
);

$environment = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'false',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_ENABLED' => 'false',
    'ONEQAY_FINAL_SHIFT_CLOSE_RUNTIME_BINDING_MATERIALIZATION_TOKEN' => '',
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
$kernel->bootstrap();

$routes = $app->make('router')->getRoutes();
$dbAttestationRoute = $routes->getByName('internal.final-shift-close.runtime-db-binding-attestation');
$assert($dbAttestationRoute !== null, 'HEAD-THR-002 DB-attestation route is registered');
$assert(
    strtoupper(implode(',', $dbAttestationRoute->methods())) === 'GET,HEAD',
    'HEAD-THR-003 DB-attestation route retains canonical GET,HEAD method ownership',
);

$canonicalManifestPath = storage_path('app/private/final-shift-close-runtime-binding.json');
$assert(! file_exists($canonicalManifestPath), 'HEAD-THR-004 canonical runtime manifest is absent before dispatch');

$root = sys_get_temp_dir().'/oneqay-sprint143-head-throttle-'.bin2hex(random_bytes(8));
$assert(mkdir($root, 0700, true) || is_dir($root), 'HEAD-THR-005 isolated temporary directory created');
$manifestPath = $root.'/binding.json';

$manifest = new FinalShiftCloseRuntimeBindingManifest([
    'schema_version' => 1,
    'feature' => 'final-shift-close',
    'selection_state' => 'SELECTED_NOT_AUTHORIZED',
    'environment_id' => 'sprint143-durable-stage-01',
    'runtime_class' => 'durable-isolated-stage',
    'exact_running_source_commit' => str_repeat('a', 40),
    'exact_running_artifact_sha256' => str_repeat('b', 64),
    'readiness_attestation_sha256' => str_repeat('c', 64),
    'selection_fingerprint_sha256' => str_repeat('d', 64),
    'trusted_ingestion' => [
        'run_id' => 143001,
        'run_attempt' => 1,
        'ingestion_fingerprint_sha256' => str_repeat('e', 64),
    ],
    'secrets_embedded' => false,
]);

$reader = new class implements FinalShiftCloseRuntimeDatabaseIdentityReader {
    public int $reads = 0;

    public function readPreMigration27Identity(): FinalShiftCloseRuntimeDatabaseIdentity
    {
        $this->reads++;

        return new FinalShiftCloseRuntimeDatabaseIdentity(
            'oneqay_sprint143_synthetic',
            'db-sprint143-synthetic',
            3306,
        );
    }
};

$productionReaderResolutionAttempts = 0;

$makeHeadRequest = static function () use ($validToken): Request {
    return Request::create(
        '/internal/final-shift-close/runtime-db-binding-attestation',
        'HEAD',
        [],
        [],
        [],
        [
            'HTTP_AUTHORIZATION' => 'Bearer '.$validToken,
            'REMOTE_ADDR' => '127.0.0.143',
        ],
    );
};

$dispatch = static function (Request $request) use ($kernel): Response {
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);

    return $response;
};

try {
    $manifestBytes = file_put_contents($manifestPath, $manifest->toCanonicalJson(), LOCK_EX);
    $assert(is_int($manifestBytes) && $manifestBytes > 0, 'HEAD-THR-006 synthetic manifest fixture written');
    $assert(chmod($manifestPath, 0600), 'HEAD-THR-007 synthetic manifest permissions restricted');

    $app->scoped(
        FinalShiftCloseRuntimeDatabaseIdentityReader::class,
        static function () use (&$productionReaderResolutionAttempts): FinalShiftCloseRuntimeDatabaseIdentityReader {
            $productionReaderResolutionAttempts++;
            throw new RuntimeException('Production database identity reader resolution is forbidden in Sprint143.');
        },
    );
    $app->instance(
        FinalShiftCloseRuntimeDbBindingAttestation::class,
        new FinalShiftCloseRuntimeDbBindingAttestation($reader, $manifestPath),
    );

    $responseOne = $dispatch($makeHeadRequest());
    $assert($responseOne->getStatusCode() === 200, 'HEAD-THR-008 first HEAD request returns 200');
    $assert($reader->reads === 1, 'HEAD-THR-009 first HEAD request performs one synthetic DB identity read');

    $responseTwo = $dispatch($makeHeadRequest());
    $assert($responseTwo->getStatusCode() === 200, 'HEAD-THR-010 second HEAD request returns 200');
    $assert($reader->reads === 2, 'HEAD-THR-011 second HEAD request performs exactly two total synthetic reads');

    $responseThree = $dispatch($makeHeadRequest());
    $assert($responseThree->getStatusCode() === 429, 'HEAD-THR-012 third HEAD request remains throttled with 429');
    $assert((string) $responseThree->getContent() === '', 'HEAD-THR-013 throttled HEAD response body is empty');

    $cacheControl = (string) $responseThree->headers->get('Cache-Control');
    $assert(str_contains($cacheControl, 'no-store'), 'HEAD-THR-014 cache-control contains no-store');
    $assert(str_contains($cacheControl, 'private'), 'HEAD-THR-015 cache-control contains private');
    $assert($responseThree->headers->get('Pragma') === 'no-cache', 'HEAD-THR-016 pragma is no-cache');
    $assert(
        $responseThree->headers->get('X-Content-Type-Options') === 'nosniff',
        'HEAD-THR-017 nosniff is present',
    );
    $assert(
        $responseThree->headers->get('X-Robots-Tag') === 'noindex, nofollow, noarchive',
        'HEAD-THR-018 robot indexing is excluded',
    );

    $assert((int) $responseThree->headers->get('X-RateLimit-Limit') === 2, 'HEAD-THR-019 rate-limit ceiling remains two');
    $assert((int) $responseThree->headers->get('X-RateLimit-Remaining') === 0, 'HEAD-THR-020 rate-limit remaining is zero');
    $retryAfter = (string) $responseThree->headers->get('Retry-After');
    $reset = (string) $responseThree->headers->get('X-RateLimit-Reset');
    $assert($retryAfter !== '' && ctype_digit($retryAfter), 'HEAD-THR-021 Retry-After is preserved');
    $assert($reset !== '' && ctype_digit($reset), 'HEAD-THR-022 X-RateLimit-Reset is preserved');

    $assert($reader->reads === 2, 'HEAD-THR-023 throttled HEAD request performs no third DB identity read');
    $assert($productionReaderResolutionAttempts === 0, 'HEAD-THR-024 production DB identity reader remains unresolved');

    $surface = json_encode($responseThree->headers->all(), JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    foreach ([$validToken, $manifestPath, $canonicalManifestPath, 'oneqay_sprint143_synthetic', 'db-sprint143-synthetic'] as $forbidden) {
        $assert(! str_contains($surface, $forbidden), 'HEAD-THR-025 sensitive synthetic fixture is not reflected: '.$forbidden);
    }

    $assert(! file_exists($canonicalManifestPath), 'HEAD-THR-026 canonical runtime manifest remains absent');
} finally {
    if (is_file($manifestPath)) {
        @unlink($manifestPath);
    }
    if (is_dir($root)) {
        @rmdir($root);
    }
}

fwrite(
    STDOUT,
    "Final Shift Close canonical control-plane HEAD throttle rejection response hardening regression passed.\n",
);

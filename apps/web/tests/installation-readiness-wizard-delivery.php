<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('i', 32)),
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_SYSTEM_UPDATE_CONTROL_PLANE_ENABLED' => 'false',
    'ONEQAY_DB_DATABASE' => '',
    'ONEQAY_DB_USERNAME' => '',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint181 installation readiness wizard regression failed: '.$message);
    }
};

$request = Request::create('/system/update', 'GET', server: [
    'HTTP_ACCEPT' => 'text/html',
    'HTTP_X_INERTIA' => 'true',
    'HTTP_X_INERTIA_VERSION' => '',
]);
$response = $kernel->handle($request);
$kernel->terminate($request, $response);

$assert($response->getStatusCode() === 200, 'system update page did not render.');

$payload = json_decode((string) $response->getContent(), true);
$assert(is_array($payload), 'Inertia payload is invalid.');
$assert(($payload['component'] ?? null) === 'System/UpdateDeployment', 'unexpected Inertia component.');

$props = $payload['props'] ?? null;
$assert(is_array($props), 'Inertia props are missing.');

$status = $props['status'] ?? null;
$assert(is_array($status), 'safe updater status is missing.');
$assert(($status['install'] ?? null) === 'DISABLED', 'updater install authority must remain disabled.');
$assert(($status['deployment_authorized'] ?? null) === false, 'deployment authority must remain false.');

$ui = $props['ui'] ?? null;
$assert(is_array($ui), 'read-only UI boundary is missing.');
$assert(($ui['mode'] ?? null) === 'READ_ONLY', 'UI mode must remain read-only.');
$assert(($ui['install_action_exposed'] ?? null) === false, 'install action must not be exposed.');
$assert(($ui['production_ready'] ?? null) === false, 'wizard must not claim Production readiness.');

$preflight = $props['installation_preflight'] ?? null;
$assert(is_array($preflight), 'installation preflight props are missing.');
$assert(($preflight['mode'] ?? null) === 'READ_ONLY', 'installation preflight must remain read-only.');
$assert(($preflight['actions_exposed'] ?? null) === false, 'installation actions must not be exposed.');
$assert(($preflight['ready'] ?? null) === false, 'incomplete governed evidence must fail closed.');

$checks = $preflight['checks'] ?? null;
$assert(is_array($checks), 'readiness checks are missing.');
foreach ([
    'php_version',
    'php_extensions',
    'host_platform',
    'environment',
    'application_key',
    'production_debug',
    'production_https',
    'database_compatibility',
    'filesystem_write',
    'release_manifest',
    'artifact_integrity',
] as $key) {
    $assert(isset($checks[$key]) && is_array($checks[$key]), 'missing readiness check '.$key);
    $assert(array_key_exists('ready', $checks[$key]), 'readiness state missing for '.$key);
    $assert(isset($checks[$key]['reason']) && is_string($checks[$key]['reason']), 'safe reason missing for '.$key);
}

$evidence = $preflight['evidence'] ?? null;
$assert(is_array($evidence), 'evidence summary is missing.');
$assert(($evidence['release_manifest'] ?? null) === 'MISSING', 'source checkout must not fabricate a governed release manifest.');
$assert(($evidence['release_artifact'] ?? null) === 'MISSING', 'source checkout must not fabricate a governed release artifact.');
$assert(($evidence['database'] ?? null) === 'NOT_CONFIGURED', 'unconfigured database must remain explicit.');
$assert(($evidence['host_platform'] ?? null) === 'SERVER_OBSERVED_PARTIAL', 'host evidence must remain explicitly partial.');

$encoded = strtolower(json_encode($props, JSON_THROW_ON_ERROR));
foreach ([
    'oneqay_db_password',
    'db_password',
    'totp_secret',
    'session_token',
    'must-never-appear',
    '.env',
] as $forbidden) {
    $assert(! str_contains($encoded, $forbidden), 'sensitive marker leaked into page props: '.$forbidden);
}

$controller = file_get_contents(__DIR__.'/../app/Delivery/Http/SystemUpdate/SystemUpdatePageController.php');
$page = file_get_contents(__DIR__.'/../resources/js/pages/System/UpdateDeployment.vue');
$assert(is_string($controller) && is_string($page), 'wizard source is missing.');

foreach ([
    'SecureInstallationReadiness',
    'loadGovernedReleaseManifest',
    'observeReleaseArtifact',
    'observeDatabaseState',
    'observeHostPlatformState',
    "'actions_exposed' => false",
] as $needle) {
    $assert(str_contains($controller, $needle), 'controller readiness contract missing '.$needle);
}

foreach ([
    'Artisan::call',
    'migrate',
    'file_put_contents',
    'fwrite(',
    'unlink(',
    'rename(',
    'chmod(',
    'chown(',
    'mkdir(',
    '->insert(',
    '->update(',
    '->delete(',
] as $forbidden) {
    $assert(! str_contains($controller, $forbidden), 'controller contains forbidden mutation primitive '.$forbidden);
}

foreach ([
    'Installation readiness wizard',
    'Preflight instalasi — read only',
    'installation_preflight.ready',
    'No installation authority',
] as $needle) {
    $assert(str_contains(strtolower($page), strtolower($needle)), 'wizard UI contract missing '.$needle);
}

foreach (['fetch(', '@click', '<form', '/system/update/install'] as $forbidden) {
    $assert(! str_contains($page, $forbidden), 'read-only wizard contains forbidden interaction '.$forbidden);
}

echo "Sprint181 installation readiness wizard delivery regression passed.\n";

<?php

declare(strict_types=1);

use App\Delivery\Http\Middleware\SafeRequestObservationMiddleware;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// Author by Lab | zefry
require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('o', 32));
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
    'LOG_CHANNEL' => 'stderr',
    'LOG_LEVEL' => 'warning',
    'ONEQAY_OBSERVABILITY_LOG_LEVEL' => 'info',
    'ONEQAY_OBSERVABILITY_LOG_DAYS' => '14',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$configuredPath = (string) config('logging.channels.oneqay_observation.path');
$assert(config('logging.channels.oneqay_observation.driver') === 'daily', 'observation channel must use bounded daily rotation');
$assert($configuredPath === storage_path('logs/oneqay-observation.log'), 'observation log must live in private application storage');
$assert(! str_starts_with($configuredPath, public_path()), 'observation log must never be under the public document root');
$assert(config('logging.channels.oneqay_observation.level') === 'info', 'observation channel must use safe info level by default');
$assert(config('logging.channels.oneqay_observation.days') === 14, 'observation retention must be bounded to 14 days by default');
$assert(config('logging.channels.stderr.level') === 'warning', 'default stderr logging must avoid debug-level output');

$logPath = sys_get_temp_dir().'/oneqay-observation-'.bin2hex(random_bytes(8)).'.log';
$app['config']->set('logging.channels.oneqay_observation', [
    'driver' => 'single',
    'path' => $logPath,
    'level' => 'info',
    'replace_placeholders' => true,
]);
$app->make('log')->forgetChannel('oneqay_observation');

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);

$querySecret = 'QUERY_SECRET_M75_4f0b';
$bodySecret = 'BODY_SECRET_M75_5a1c';
$cookieSecret = 'COOKIE_SECRET_M75_6b2d';
$authorizationSecret = 'AUTH_SECRET_M75_7c3e';

$request = Request::create(
    '/health/live?token='.$querySecret,
    'GET',
    cookies: ['oneqay-session' => $cookieSecret],
    server: [
        'HTTP_AUTHORIZATION' => 'Bearer '.$authorizationSecret,
        'HTTP_X_CORRELATION_ID' => 'M75-ObsSafe_0001',
        'CONTENT_TYPE' => 'application/json',
    ],
    content: json_encode(['password' => $bodySecret], JSON_THROW_ON_ERROR),
);
$response = $kernel->handle($request);
$assert($response->getStatusCode() === 200, 'synthetic observation request must remain healthy');
$kernel->terminate($request, $response);

$exceptionSecret = 'EXCEPTION_SECRET_M75_8d4f';
$exceptionRequest = Request::create('/synthetic-observation-exception', 'GET');
$exceptionRequest->attributes->set('oneqay.correlation_id', 'M75-ObsSafe_0002');

try {
    (new SafeRequestObservationMiddleware())->handle(
        $exceptionRequest,
        static function () use ($exceptionSecret): never {
            throw new RuntimeException($exceptionSecret);
        },
    );
    $assert(false, 'synthetic exception path must rethrow');
} catch (RuntimeException $exception) {
    $assert($exception->getMessage() === $exceptionSecret, 'middleware must preserve exception semantics');
}

clearstatcache(true, $logPath);
$assert(is_file($logPath), 'safe observation log must be written');
$logContent = (string) file_get_contents($logPath);

$assert(str_contains($logContent, 'oneqay.http.request'), 'safe observation event name must be present');
$assert(str_contains($logContent, 'M75-ObsSafe_0001'), 'request correlation id must be searchable in the log');
$assert(str_contains($logContent, 'health.live'), 'named route must be searchable in the log');
$assert(str_contains($logContent, 'M75-ObsSafe_0002'), 'exception correlation id must be searchable in the log');
$assert(str_contains($logContent, 'RuntimeException'), 'exception class may be recorded as bounded metadata');

foreach ([$querySecret, $bodySecret, $cookieSecret, $authorizationSecret, $exceptionSecret, $testKey] as $secret) {
    $assert(! str_contains($logContent, $secret), 'safe observation log must not leak synthetic sensitive values');
}

foreach (['Authorization', 'oneqay-session', 'password', 'token='] as $sensitiveLabel) {
    $assert(! str_contains($logContent, $sensitiveLabel), 'safe observation log must not copy sensitive request surfaces');
}

@unlink($logPath);

fwrite(STDOUT, "M7.5 observability safe structured logging regression passed.\n");

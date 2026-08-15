<?php

declare(strict_types=1);

use App\Application\Observability\CorrelationId;
use App\Application\Tenancy\MissingTenantContext;
use App\Application\Tenancy\RequireVerifiedTenantContext;
use App\Delivery\Http\Middleware\SafeRequestObservationMiddleware;
use App\Delivery\Http\SafeErrorEnvelope;
use App\Infrastructure\Configuration\CriticalConfiguration;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

$testKey = 'base64:'.base64_encode(str_repeat('t', 32));
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
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

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$request = Request::create('/health/live', 'GET', server: [
    'HTTP_X_CORRELATION_ID' => 'M71-Test_1234',
]);
$response = $kernel->handle($request);
$payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert($response->getStatusCode() === 200, 'liveness must return 200');
$assert(($payload['status'] ?? null) === 'ok', 'liveness status must be ok');
$assert(($payload['correlation_id'] ?? null) === 'M71-Test_1234', 'valid correlation id must propagate');
$assert($response->headers->get('X-Correlation-ID') === 'M71-Test_1234', 'correlation response header must propagate');
$assert(! str_contains((string) $response->getContent(), $testKey), 'health response must not leak APP_KEY');
$assert($response->headers->get('Strict-Transport-Security') === null, 'HSTS must not be emitted for a non-HTTPS request');
$kernel->terminate($request, $response);

$request = Request::create('https://localhost/health/live', 'GET', server: [
    'HTTP_X_CORRELATION_ID' => 'M75-Security_0001',
]);
$response = $kernel->handle($request);
$csp = (string) $response->headers->get('Content-Security-Policy');
$assert($response->getStatusCode() === 200, 'HTTPS liveness must return 200');
$assert($response->headers->get('Strict-Transport-Security') === 'max-age=31536000', 'HTTPS responses must emit bounded HSTS');
$assert(str_contains($csp, "default-src 'self'"), 'CSP must default to same-origin');
$assert(str_contains($csp, "frame-ancestors 'none'"), 'CSP must deny framing');
$assert(str_contains($csp, "object-src 'none'"), 'CSP must deny plugin/object content');
$assert($response->headers->get('X-Content-Type-Options') === 'nosniff', 'responses must disable MIME sniffing');
$assert($response->headers->get('X-Frame-Options') === 'DENY', 'responses must deny framing');
$assert($response->headers->get('Referrer-Policy') === 'strict-origin-when-cross-origin', 'responses must use bounded referrer policy');
$assert($response->headers->get('Permissions-Policy') === 'camera=(self), geolocation=(self), microphone=(), payment=(self), usb=()', 'responses must emit bounded permissions policy');
$assert(! str_contains($csp, $testKey), 'security headers must not leak APP_KEY');
$kernel->terminate($request, $response);

$request = Request::create('/health/ready', 'GET');
$response = $kernel->handle($request);
$payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert($response->getStatusCode() === 200, 'readiness must return 200 for valid CI configuration');
$assert(($payload['status'] ?? null) === 'ready', 'readiness status must be ready');
$kernel->terminate($request, $response);

$previewViolations = CriticalConfiguration::violations([
    'app_key' => $testKey,
    'runtime_class' => 'preview',
    'app_debug' => false,
    'app_env' => 'production',
]);
$assert($previewViolations === [], 'preview runtime class must satisfy readiness with a valid non-debug configuration');

$violations = CriticalConfiguration::violations([
    'app_key' => 'base64:REPLACE_WITH_A_LOCAL_OR_TEST_KEY',
    'runtime_class' => 'production',
    'app_debug' => true,
    'app_env' => 'production',
]);
$assert(in_array('app_key', $violations, true), 'placeholder APP_KEY must fail validation');
$assert(in_array('runtime_class', $violations, true), 'unsupported runtime class must fail validation');
$assert(in_array('app_debug', $violations, true), 'unsafe debug mode must fail validation');

$validId = CorrelationId::resolve('M71-Valid_0001');
$assert($validId === 'M71-Valid_0001', 'valid incoming correlation id must be preserved');
$invalidId = CorrelationId::resolve('bad!');
$assert($invalidId !== 'bad!' && preg_match('/\A[a-f0-9]{32}\z/', $invalidId) === 1, 'invalid correlation id must be regenerated safely');

$envelope = SafeErrorEnvelope::make('ONEQAY_REQUEST_FAILED', $invalidId);
$assert(($envelope['error']['message'] ?? '') === 'The request could not be completed.', 'safe error envelope must remain generic');
$assert(! array_key_exists('exception', $envelope['error']), 'safe error envelope must not expose exception detail');

try {
    (new RequireVerifiedTenantContext())->require(null);
    $assert(false, 'missing tenant context must fail closed');
} catch (MissingTenantContext) {
    // Expected.
}

// M7.5 safe observability regression. Runtime logging is active only for the governed Preview class.
$policy = SafeRequestObservationMiddleware::policy();
$assert($policy['path'] === storage_path('logs/oneqay-observation.log'), 'observation log must live in private application storage');
$assert(! str_starts_with($policy['path'], public_path()), 'observation log must never be under the public document root');
$assert($policy['level'] === 'info', 'observation log must use bounded info level');
$assert($policy['days'] === 14, 'observation retention must be bounded to 14 days');

$logBasePath = sys_get_temp_dir().'/oneqay-observation-'.bin2hex(random_bytes(8)).'.log';
$app->instance(SafeRequestObservationMiddleware::class, new SafeRequestObservationMiddleware($logBasePath));
$app['config']->set('oneqay.runtime_class', 'preview');

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
    (new SafeRequestObservationMiddleware($logBasePath))->handle(
        $exceptionRequest,
        static function () use ($exceptionSecret): never {
            throw new RuntimeException($exceptionSecret);
        },
    );
    $assert(false, 'synthetic exception path must rethrow');
} catch (RuntimeException $exception) {
    $assert($exception->getMessage() === $exceptionSecret, 'middleware must preserve exception semantics');
}

$rotatedPattern = preg_replace('/\.log\z/', '-*.log', $logBasePath);
$logFiles = [];
if (is_file($logBasePath)) {
    $logFiles[] = $logBasePath;
}
if (is_string($rotatedPattern)) {
    foreach (glob($rotatedPattern) ?: [] as $candidate) {
        $logFiles[] = $candidate;
    }
}
$logFiles = array_values(array_unique($logFiles));
$assert($logFiles !== [], 'safe observation log must be written');
$logContent = '';
foreach ($logFiles as $logFile) {
    $logContent .= (string) file_get_contents($logFile)."\n";
}

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

$app['config']->set('oneqay.runtime_class', 'ci');
$app->forgetInstance(SafeRequestObservationMiddleware::class);
foreach ($logFiles as $logFile) {
    @unlink($logFile);
}

$forbidden = [
    'Illuminate\\',
    'Inertia\\',
    'Laravel\\',
    'Vue',
];

foreach ([
    __DIR__.'/../app/Domain',
    __DIR__.'/../app/Application',
] as $directory) {
    if (! is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        foreach ($forbidden as $needle) {
            $assert(! str_contains((string) $content, $needle), "{$file->getPathname()} violates framework-independence boundary: {$needle}");
        }
    }
}

fwrite(STDOUT, "M7.1 application regression passed.\n");

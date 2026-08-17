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
require_once dirname(__DIR__, 3).'/src/SchemaPlanning/Foundation.php';

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

// Sprint 18: actual in-process Laravel migration execution against a disposable SQLite test target only.
// Sprint 19 now publishes guarded database configuration, but keeps persistence disabled by default.
$assert(is_file(__DIR__.'/../config/database.php'), 'Sprint 19 guarded application database configuration is missing.');
$assert(config('database.oneqay_persistence_enabled') === false, 'Sprint 19 persistence must remain disabled during ordinary application regression boot.');
$assert(extension_loaded('pdo_sqlite'), 'Sprint 18 disposable SQLite proof requires pdo_sqlite in CI.');

$removeS18Tree = null;
$removeS18Tree = static function (string $path) use (&$removeS18Tree): void {
    if (is_link($path) || is_file($path)) {
        @unlink($path);
        return;
    }
    if (! is_dir($path)) {
        return;
    }
    $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        $removeS18Tree($item->getPathname());
    }
    @rmdir($path);
};

$s18Parent = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s18-app-'.getmypid();
$removeS18Tree($s18Parent);
$assert(@mkdir($s18Parent, 0700, false), 'Sprint 18 application staging parent could not be created.');

$s18MigrationSource = <<<'PHP'
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('s18_execution_probe', function (Blueprint $table): void {
            $table->char('id', 36);
            $table->string('name', 128);
            $table->primary(['id'], 'pk_s18_execution_probe');
        });
    }

    public function down(): void
    {
        throw new \LogicException('Forward-only generated migration; rollback is not authorized.');
    }
};
PHP;
$s18GenerationCorrelation = new \OneQay\SchemaPlanning\CorrelationId('corr-generation-s18-app');
$s18File = new \OneQay\SchemaPlanning\LaravelMigrationFileArtifact(
    new \OneQay\SchemaPlanning\StableChangeIdentifier(str_repeat('a', 64)),
    new \OneQay\Migration\MigrationIdentifier('MIG_00000000_000001_ENTITY_CREATED_AAAAAAAAAAAA'),
    $s18GenerationCorrelation,
    'database/migrations/0000_00_00_000001_entity_created_aaaaaaaaaaaa.php',
    $s18MigrationSource,
);
$s18Artifact = new \OneQay\SchemaPlanning\LaravelMigrationGenerationArtifact(
    new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('b', 64)),
    new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('c', 64)),
    new \OneQay\SchemaPlanning\ManifestFingerprint(str_repeat('d', 64)),
    $s18GenerationCorrelation,
    [$s18File],
);
$s18Composer = (string) file_get_contents(__DIR__.'/../composer.json');
$s18Materializer = new \OneQay\SchemaPlanning\GovernedLaravelMigrationMaterializer();
$s18Materialized = $s18Materializer->materialize(
    $s18Artifact,
    $s18Composer,
    $s18Parent,
    'corr-materialization-s18-app',
);

$s18DatabasePath = $s18Parent.DIRECTORY_SEPARATOR.'execution.sqlite';
$assert(touch($s18DatabasePath), 'Sprint 18 disposable SQLite file could not be created.');
$app['config']->set('database.default', 's18_sqlite');
$app['config']->set('database.connections.s18_sqlite', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $s18DatabasePath,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);
/** @var \Illuminate\Database\DatabaseManager $s18DatabaseManager */
$s18DatabaseManager = $app->make('db');
$s18DatabaseManager->purge('s18_sqlite');
$s18DatabaseManager->setDefaultConnection('s18_sqlite');
$s18Connection = $s18DatabaseManager->connection('s18_sqlite');
$s18Connection->getPdo();

$s18Target = new class($s18Connection) implements \OneQay\SchemaPlanning\LaravelMigrationExecutionTargetAdapter {
    public int $executeCalls = 0;

    public function __construct(private readonly \Illuminate\Database\Connection $connection) {}

    public function targetKind(): string
    {
        return self::DISPOSABLE_SQLITE_TEST;
    }

    public function preflight(): string
    {
        $schema = $this->connection->getSchemaBuilder();
        if ($schema->hasTable('s18_execution_probe')) {
            throw new RuntimeException('Disposable Sprint 18 baseline is not clean.');
        }
        return hash('sha256', json_encode([
            'driver' => $this->connection->getDriverName(),
            'probe_table' => false,
        ], JSON_THROW_ON_ERROR));
    }

    public function execute(\OneQay\SchemaPlanning\LaravelMigrationFileArtifact $file, string $absoluteStagedPath): void
    {
        if (! is_file($absoluteStagedPath) || hash_file('sha256', $absoluteStagedPath) !== $file->sourceFingerprint) {
            throw new RuntimeException('Sprint 18 staged migration bytes changed before Laravel execution.');
        }
        $migration = require $absoluteStagedPath;
        if (! $migration instanceof \Illuminate\Database\Migrations\Migration) {
            throw new RuntimeException('Sprint 18 staged file did not return a Laravel migration object.');
        }
        $migration->up();
        $this->executeCalls++;
    }

    public function verify(): string
    {
        $schema = $this->connection->getSchemaBuilder();
        $table = $schema->hasTable('s18_execution_probe');
        $id = $table && $schema->hasColumn('s18_execution_probe', 'id');
        $name = $table && $schema->hasColumn('s18_execution_probe', 'name');
        if (! $table || ! $id || ! $name) {
            throw new RuntimeException('Sprint 18 disposable SQLite target shape is incomplete.');
        }
        return hash('sha256', json_encode([
            'driver' => $this->connection->getDriverName(),
            'probe_table' => true,
            'id' => true,
            'name' => true,
        ], JSON_THROW_ON_ERROR));
    }
};

$s18Executor = new \OneQay\SchemaPlanning\GovernedLaravelMigrationExecutor();
$s18Execution = $s18Executor->execute(
    $s18Artifact,
    $s18Materialized,
    $s18Composer,
    $s18Parent,
    $s18Target,
    'corr-execution-s18-app',
);
$assert($s18Execution->finalState === 'COMPLETE', 'Sprint 18 disposable SQLite execution did not complete.');
$assert(! $s18Execution->alreadyComplete, 'Sprint 18 first SQLite execution incorrectly reported idempotent completion.');
$assert($s18Execution->targetKind === \OneQay\SchemaPlanning\LaravelMigrationExecutionTargetAdapter::DISPOSABLE_SQLITE_TEST, 'Sprint 18 execution target kind changed.');
$assert($s18Execution->executedMigrationIdentifiers() === [$s18File->migrationIdentifier->value], 'Sprint 18 executed migration identity changed.');
$assert($s18Target->executeCalls === 1, 'Sprint 18 Laravel migration did not execute exactly once.');
$assert($s18Connection->getSchemaBuilder()->hasTable('s18_execution_probe'), 'Sprint 18 migration did not create the synthetic probe table.');
$assert($s18Connection->getSchemaBuilder()->hasColumn('s18_execution_probe', 'id'), 'Sprint 18 synthetic id column is missing.');
$assert($s18Connection->getSchemaBuilder()->hasColumn('s18_execution_probe', 'name'), 'Sprint 18 synthetic name column is missing.');

$s18Idempotent = $s18Executor->execute(
    $s18Artifact,
    $s18Materialized,
    $s18Composer,
    $s18Parent,
    $s18Target,
    'corr-execution-s18-app-idempotent',
);
$assert($s18Idempotent->alreadyComplete, 'Sprint 18 completed SQLite execution was not idempotently verified.');
$assert($s18Target->executeCalls === 1, 'Sprint 18 idempotent verification re-executed the Laravel migration.');
$assert($s18Idempotent->targetWitness === $s18Execution->targetWitness, 'Sprint 18 idempotent SQLite target witness changed.');

$s18Workspace = $s18Parent.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $s18Materialized->workspaceRelativePath);
$s18StagedPath = $s18Workspace.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $s18File->relativePath);
$assert(file_put_contents($s18StagedPath, $s18File->source."\n// deliberate-s18-app-tamper\n", LOCK_EX) !== false, 'Sprint 18 application tamper fixture could not be created.');
try {
    $s18Executor->execute(
        $s18Artifact,
        $s18Materialized,
        $s18Composer,
        $s18Parent,
        $s18Target,
        'corr-execution-s18-app-tamper',
    );
    $assert(false, 'Sprint 18 accepted a tampered staged migration before execution.');
} catch (\OneQay\SchemaPlanning\LaravelMigrationExecutionException $exception) {
    $assert(
        $exception->errorCode === \OneQay\SchemaPlanning\LaravelMigrationExecutionException::MATERIALIZATION_VALIDATION_FAILED,
        'Sprint 18 staged tamper returned an unexpected bounded error.',
    );
}
$assert(file_put_contents($s18StagedPath, $s18File->source, LOCK_EX) === strlen($s18File->source), 'Sprint 18 application tamper fixture could not be restored.');
$s18Materializer->validate($s18Artifact, $s18Composer, $s18Parent, 'corr-validation-s18-app-restored');

$s18ExecutionWorkspace = $s18Parent.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $s18Execution->executionWorkspaceRelativePath);
$s18JournalPath = $s18ExecutionWorkspace.DIRECTORY_SEPARATOR.'journal.json';
$s18JournalContent = (string) file_get_contents($s18JournalPath);
$s18ReportContent = json_encode($s18Execution, JSON_THROW_ON_ERROR);
foreach ([$s18DatabasePath, $s18Parent, $s18MigrationSource, 'DB_PASSWORD', 'DB_USER', 'DB_HOST', 'mysql:host=', 'jdbc:'] as $forbiddenExecutionMaterial) {
    $assert(! str_contains($s18JournalContent, $forbiddenExecutionMaterial), 'Sprint 18 journal leaked private or sensitive execution material.');
    $assert(! str_contains($s18ReportContent, $forbiddenExecutionMaterial), 'Sprint 18 report leaked private or sensitive execution material.');
}

$s18DatabaseManager->disconnect('s18_sqlite');
$s18DatabaseManager->purge('s18_sqlite');
$app['config']->set('database.connections.s18_sqlite', null);
@unlink($s18DatabasePath);
$removeS18Tree($s18Parent);
$assert(! file_exists($s18Parent), 'Sprint 18 disposable SQLite execution workspace cleanup failed.');

// Sprint 19: durable application persistence remains Local/Test/CI-only and uses its own disposable regression database.
require __DIR__.'/persistence.php';

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

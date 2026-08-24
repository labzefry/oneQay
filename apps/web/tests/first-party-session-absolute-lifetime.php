<?php

declare(strict_types=1);

use App\Application\Identity\FirstPartySessionAuthorityClock;
use App\Application\Identity\FirstPartySessionAuthorityService;
use App\Application\Identity\FirstPartySessionAuthorityViolation;
use App\Application\Identity\PrivilegedTotpFactorEpochRepository;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\LaravelFirstPartyCredentialEpochRepository;
use App\Infrastructure\Identity\LaravelFirstPartySessionAuthorityRepository;
use Illuminate\Contracts\Http\Kernel;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('a', 32));
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED' => 'false',
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
        throw new RuntimeException('Sprint38 first-party session absolute lifetime regression failed: '.$message);
    }
};

$expectViolation = static function (callable $operation, string $errorCode, string $message) use ($assert): void {
    try {
        $operation();
        $assert(false, $message.' did not fail closed');
    } catch (FirstPartySessionAuthorityViolation $exception) {
        $assert($exception->errorCode === $errorCode, $message.' returned unexpected violation');
    }
};

$removeTree = static function (string $path): void {
    if (is_file($path) || is_link($path)) { @unlink($path); return; }
    if (! is_dir($path)) { return; }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($path);
};

$configSource = file_get_contents(__DIR__.'/../config/oneqay.php');
$assert(is_string($configSource), 'config source unreadable');
$assert(str_contains($configSource, "'idle_ttl_seconds' => 7200"), 'idle TTL is no longer fixed at 7200');
$assert(str_contains($configSource, "'absolute_ttl_seconds' => 43200"), 'absolute TTL is no longer fixed at 43200');
$assert(! str_contains($configSource, 'ONEQAY_AUTHENTICATION_SESSION_ABSOLUTE'), 'absolute TTL became environment-configurable');
$assert((int) $app['config']->get('oneqay.session_control.idle_ttl_seconds') === 7200, 'runtime idle TTL mismatch');
$assert((int) $app['config']->get('oneqay.session_control.absolute_ttl_seconds') === 43200, 'runtime absolute TTL mismatch');

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s38-absolute-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'absolute-lifetime.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's38_absolute');
$app['config']->set('database.connections.s38_absolute', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $dbPath,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');
$app['config']->set('oneqay.session_control.enabled', true);
$app['config']->set('oneqay.session_control.idle_ttl_seconds', 7200);
$app['config']->set('oneqay.session_control.absolute_ttl_seconds', 43200);
$app['config']->set('oneqay.privileged_totp_mfa.enabled', false);

/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s38_absolute');
$manager->setDefaultConnection('s38_absolute');
$connection = $manager->connection('s38_absolute');
$connection->getPdo();

$migrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$expectedMigrations = [];
for ($index = 1; $index <= 13; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $matches = array_values(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix)));
    $assert(count($matches) === 1, 'migration #'.$index.' must exist exactly once');
    $expectedMigrations[] = $matches[0];
}
$assert($migrations === $expectedMigrations, 'migration set must remain exactly #1-#13 with no migration #14');
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$connection->table('oneqay_tenants')->insert(['id' => 'tenant-alpha']);
$connection->table('oneqay_identities')->insert(['tenant_id' => 'tenant-alpha', 'id' => 'ordinary-alpha']);
$connection->table('oneqay_organizations')->insert(['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha']);
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'ordinary-alpha',
    'organization_id' => 'organization-alpha',
]);
$passwordHash = password_hash('Sprint38 synthetic password only', PASSWORD_BCRYPT);
$assert(is_string($passwordHash), 'synthetic password hash creation');
$connection->table('oneqay_identity_password_credentials')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'ordinary-alpha',
    'password_hash' => $passwordHash,
    'credential_epoch' => 8,
]);

$clock = new class implements FirstPartySessionAuthorityClock {
    public int $now = 1787500000;
    public function nowUnix(): int { return $this->now; }
};
$repository = new LaravelFirstPartySessionAuthorityRepository($connection, true, 'ci', true);
$credentialEpochs = new LaravelFirstPartyCredentialEpochRepository($connection, true, 'ci');
/** @var PrivilegedTotpFactorEpochRepository $factorEpochs */
$factorEpochs = $app->make(PrivilegedTotpFactorEpochRepository::class);
/** @var PrivilegedTotpMfaService $mfa */
$mfa = $app->make(PrivilegedTotpMfaService::class);
$service = new FirstPartySessionAuthorityService(
    $repository,
    $clock,
    $credentialEpochs,
    $factorEpochs,
    $mfa,
    false,
    7200,
    43200,
);

$tenantId = TenantId::fromString('tenant-alpha');
$identityId = PlatformIdentityId::fromString('ordinary-alpha');
$issuedAt = $clock->now;
$rolling = $service->issue($tenantId, $identityId, 'organization-alpha', null, null, 8, null, 'S38-Issue_Rolling');
$inflated = $service->issue($tenantId, $identityId, 'organization-alpha', null, null, 8, null, 'S38-Issue_Inflated');

$rollingRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $rolling->authorityId())->first();
$assert(is_object($rollingRow), 'rolling authority row missing');
$assert((int) $rollingRow->issued_at_unix === $issuedAt, 'issued_at is not server-owned clock value');
$assert((int) $rollingRow->expires_at_unix === $issuedAt + 7200, 'initial effective expiry is not min(idle, absolute)');

$connection->table('oneqay_identity_first_party_sessions')
    ->where('authority_id', $inflated->authorityId())
    ->update(['expires_at_unix' => $issuedAt + 50000]);

foreach ([7000, 14000, 21000, 28000, 35000, 42000] as $offset) {
    $clock->now = $issuedAt + $offset;
    $service->assertActiveCurrent($tenantId, $identityId, $rolling->authorityId(), 'organization-alpha', null, null, 8, null);
}

$rollingRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $rolling->authorityId())->first();
$assert(is_object($rollingRow), 'rolling authority row missing after touches');
$assert((int) $rollingRow->issued_at_unix === $issuedAt, 'touch mutated immutable issued_at');
$assert((int) $rollingRow->expires_at_unix === $issuedAt + 43200, 'continuous activity crossed or failed to reach absolute cap');

$inventoryNearDeadline = $service->inventory($tenantId, $identityId, $rolling->authorityId(), 'organization-alpha', null, null, 8, null);
$inflatedInventory = array_values(array_filter(
    $inventoryNearDeadline,
    static fn ($item): bool => hash_equals($inflated->publicHandle(), $item->handle),
));
$assert(count($inflatedInventory) === 1, 'pre-Sprint38-style inflated row missing before absolute deadline');
$assert($inflatedInventory[0]->expiresAtUnix === $issuedAt + 43200, 'inventory exposed durable expiry beyond absolute deadline');

$clock->now = $issuedAt + 43200;
$service->assertActiveCurrent($tenantId, $identityId, $rolling->authorityId(), 'organization-alpha', null, null, 8, null);
$deadlineRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $rolling->authorityId())->first();
$assert(is_object($deadlineRow) && (int) $deadlineRow->expires_at_unix === $issuedAt + 43200, 'equality boundary changed or extended absolute deadline');

$clock->now = $issuedAt + 43201;
$expectViolation(
    static fn () => $service->assertActiveCurrent($tenantId, $identityId, $rolling->authorityId(), 'organization-alpha', null, null, 8, null),
    FirstPartySessionAuthorityViolation::AUTHORITY_DENIED,
    'absolute deadline +1 second authority check',
);

$viewer = $service->issue($tenantId, $identityId, 'organization-alpha', null, null, 8, null, 'S38-Issue_Viewer');
$afterDeadlineInventory = $service->inventory($tenantId, $identityId, $viewer->authorityId(), 'organization-alpha', null, null, 8, null);
$handlesAfterDeadline = array_map(static fn ($item): string => $item->handle, $afterDeadlineInventory);
$assert(! in_array($inflated->publicHandle(), $handlesAfterDeadline, true), 'inflated expired row remained an active inventory item');
$assert(! in_array($rolling->publicHandle(), $handlesAfterDeadline, true), 'absolute-expired rolling row remained an active inventory item');

$viewerRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $viewer->authorityId())->first();
$assert(is_object($viewerRow), 'viewer authority row missing');
$viewerIssuedAt = (int) $viewerRow->issued_at_unix;
$clock->now = $viewerIssuedAt - 1;
$expectViolation(
    static fn () => $service->assertActiveCurrent($tenantId, $identityId, $viewer->authorityId(), 'organization-alpha', null, null, 8, null),
    FirstPartySessionAuthorityViolation::AUTHORITY_DENIED,
    'clock rollback before server-owned issued_at',
);
$clock->now = $viewerIssuedAt;
$service->assertActiveCurrent($tenantId, $identityId, $viewer->authorityId(), 'organization-alpha', null, null, 8, null);

$badIdle = new FirstPartySessionAuthorityService($repository, $clock, $credentialEpochs, $factorEpochs, $mfa, false, 7199, 43200);
$expectViolation(
    static fn () => $badIdle->issue($tenantId, $identityId, 'organization-alpha', null, null, 8, null, 'S38-BadIdle'),
    FirstPartySessionAuthorityViolation::FEATURE_DISABLED,
    'idle TTL mismatch',
);
$badAbsolute = new FirstPartySessionAuthorityService($repository, $clock, $credentialEpochs, $factorEpochs, $mfa, false, 7200, 43199);
$expectViolation(
    static fn () => $badAbsolute->issue($tenantId, $identityId, 'organization-alpha', null, null, 8, null, 'S38-BadAbsolute'),
    FirstPartySessionAuthorityViolation::FEATURE_DISABLED,
    'absolute TTL mismatch',
);

$auditEvents = $connection->table('oneqay_identity_first_party_session_audit')->pluck('event_type')->all();
$allowedAuditEvents = ['session_issued', 'session_revoked', 'other_sessions_revoked', 'all_sessions_revoked', 'session_logout'];
foreach ($auditEvents as $event) {
    $assert(is_string($event) && in_array($event, $allowedAuditEvents, true), 'absolute lifetime introduced an unauthorized audit event');
}

$routeNames = array_values(array_filter(array_map(static fn ($route): ?string => $route->getName(), iterator_to_array($app['router']->getRoutes()))));
foreach ($routeNames as $routeName) {
    $assert(! str_contains(strtolower($routeName), 'absolute'), 'absolute lifetime introduced a new HTTP route');
}

$manager->disconnect('s38_absolute');
$manager->purge('s38_absolute');
@unlink($dbPath);
$removeTree($workspace);

echo "Sprint38 first-party session absolute lifetime regression passed.\n";
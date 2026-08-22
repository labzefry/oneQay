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
$testKey = 'base64:'.base64_encode(str_repeat('s', 32));
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED' => 'true',
    'ONEQAY_PRIVILEGED_STEP_UP_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint36 first-party session authority regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s36-session-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'session-authority.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's36_session');
$app['config']->set('database.connections.s36_session', [
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
$app['config']->set('oneqay.privileged_totp_mfa.enabled', true);
$app['config']->set('oneqay.privileged_step_up.enabled', true);
$app['config']->set('oneqay.privileged_step_up.freshness_seconds', 300);

/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s36_session');
$manager->setDefaultConnection('s36_session');
$connection = $manager->connection('s36_session');
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
$assert($migrations === $expectedMigrations, 'migration set must be exactly #1-#13');
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$schema = $connection->getSchemaBuilder();
$assert($schema->hasTable('oneqay_identity_first_party_sessions'), 'session authority table missing');
$assert($schema->hasTable('oneqay_identity_first_party_session_audit'), 'session audit table missing');
foreach (['authority_id', 'public_handle', 'credential_epoch', 'factor_epoch', 'revoked_at_unix'] as $column) {
    $assert($schema->hasColumn('oneqay_identity_first_party_sessions', $column), 'session authority column missing: '.$column);
}

$connection->table('oneqay_tenants')->insert(['id' => 'tenant-alpha']);
$connection->table('oneqay_identities')->insert(['tenant_id' => 'tenant-alpha', 'id' => 'ordinary-alpha']);
$connection->table('oneqay_organizations')->insert(['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha']);
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'ordinary-alpha',
    'organization_id' => 'organization-alpha',
]);
$passwordHash = password_hash('Sprint36 synthetic password only', PASSWORD_BCRYPT);
$assert(is_string($passwordHash), 'synthetic password hash creation');
$connection->table('oneqay_identity_password_credentials')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'ordinary-alpha',
    'password_hash' => $passwordHash,
    'credential_epoch' => 3,
]);

$clock = new class implements FirstPartySessionAuthorityClock {
    public int $now = 1787359800;
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
    true,
    7200,
);

$tenantId = TenantId::fromString('tenant-alpha');
$identityId = PlatformIdentityId::fromString('ordinary-alpha');
$current = $service->issue($tenantId, $identityId, 'organization-alpha', null, null, 3, null, 'S36-Issue_0001');
$assert(preg_match('/\A[0-9a-f]{32}\z/D', $current->authorityId()) === 1, 'authority id format');
$assert(preg_match('/\A[A-Za-z0-9_-]{43}\z/D', $current->publicHandle()) === 1, 'public handle format');
$assert(! hash_equals($current->authorityId(), $current->publicHandle()), 'public handle exposed internal authority id');

$service->assertActiveCurrent($tenantId, $identityId, $current->authorityId(), 'organization-alpha', null, null, 3, null);
$clock->now += 61;
$service->assertActiveCurrent($tenantId, $identityId, $current->authorityId(), 'organization-alpha', null, null, 3, null);
$touched = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $current->authorityId())->first();
$assert(is_object($touched) && (int) $touched->last_seen_at_unix === $clock->now, 'bounded touch did not update after 60 seconds');
$assert((int) $touched->expires_at_unix === $clock->now + 7200, 'touch did not extend exact idle lifetime');

$clock->now += 1;
$remote = $service->issue($tenantId, $identityId, 'organization-alpha', null, null, 3, null, 'S36-Issue_0002');
$inventory = $service->inventory($tenantId, $identityId, $current->authorityId(), 'organization-alpha', null, null, 3, null);
$assert(count($inventory) === 2, 'inventory did not return exact active owner sessions');
$inventoryPayload = array_map(static fn ($item): array => $item->toArray(), $inventory);
$assert(count(array_filter($inventoryPayload, static fn (array $item): bool => $item['current'] === true)) === 1, 'inventory current marker is not exact');
foreach ($inventoryPayload as $item) {
    $assert(! array_key_exists('authority_id', $item), 'inventory leaked internal authority id');
    $assert(! array_key_exists('credential_epoch', $item), 'inventory leaked credential epoch');
    $assert(! array_key_exists('factor_epoch', $item), 'inventory leaked factor epoch');
}

try {
    $service->revokeOne($tenantId, $identityId, $current->authorityId(), $current->publicHandle(), 'organization-alpha', null, null, 3, null, 'S36-RevokeCurrent_0001');
    $assert(false, 'remote revoke accepted current session');
} catch (FirstPartySessionAuthorityViolation $exception) {
    $assert($exception->errorCode === FirstPartySessionAuthorityViolation::CURRENT_SESSION_TARGET, 'current-session revoke failed with wrong semantic');
}

$service->revokeOne($tenantId, $identityId, $current->authorityId(), $remote->publicHandle(), 'organization-alpha', null, null, 3, null, 'S36-RevokeOne_0001');
$remoteRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $remote->authorityId())->first();
$assert(is_object($remoteRow) && (int) $remoteRow->revoked_at_unix === $clock->now, 'remote session was not monotonically revoked');
$revocationAuditCount = $connection->table('oneqay_identity_first_party_session_audit')->where('event_type', 'session_revoked')->count();
$service->revokeOne($tenantId, $identityId, $current->authorityId(), $remote->publicHandle(), 'organization-alpha', null, null, 3, null, 'S36-RevokeOne_Repeat');
$assert($connection->table('oneqay_identity_first_party_session_audit')->where('event_type', 'session_revoked')->count() === $revocationAuditCount, 'repeat revoke created duplicate durable transition audit');

$service->revokeOne($tenantId, $identityId, $current->authorityId(), str_repeat('A', 43), 'organization-alpha', null, null, 3, null, 'S36-Probe_0001');
$clock->now += 1;
$other = $service->issue($tenantId, $identityId, 'organization-alpha', null, null, 3, null, 'S36-Issue_0003');
$revokedOthers = $service->revokeOthers($tenantId, $identityId, $current->authorityId(), 'organization-alpha', null, null, 3, null, 'S36-RevokeOthers_0001');
$assert($revokedOthers === 1, 'revoke-others did not revoke exact active other session');
$currentRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $current->authorityId())->first();
$otherRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $other->authorityId())->first();
$assert(is_object($currentRow) && $currentRow->revoked_at_unix === null, 'revoke-others revoked current authority');
$assert(is_object($otherRow) && $otherRow->revoked_at_unix !== null, 'revoke-others preserved target authority');

$connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'ordinary-alpha')->update(['credential_epoch' => 4]);
try {
    $service->assertActiveCurrent($tenantId, $identityId, $current->authorityId(), 'organization-alpha', null, null, 3, null);
    $assert(false, 'stale credential epoch remained authoritative');
} catch (FirstPartySessionAuthorityViolation $exception) {
    $assert($exception->errorCode === FirstPartySessionAuthorityViolation::AUTHORITY_DENIED, 'stale credential epoch returned unexpected violation');
}

$fresh = $service->issue($tenantId, $identityId, 'organization-alpha', null, null, 4, null, 'S36-Issue_0004');
$service->logoutCurrent($tenantId, $identityId, $fresh->authorityId(), 'S36-Logout_0001');
$freshRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $fresh->authorityId())->first();
$assert(is_object($freshRow) && $freshRow->revoked_at_unix !== null, 'canonical logout did not revoke current durable authority');

$expiring = $service->issue($tenantId, $identityId, 'organization-alpha', null, null, 4, null, 'S36-Issue_Expiry');
$clock->now += 7201;
try {
    $service->assertActiveCurrent($tenantId, $identityId, $expiring->authorityId(), 'organization-alpha', null, null, 4, null);
    $assert(false, 'expired session authority remained active');
} catch (FirstPartySessionAuthorityViolation) {
    // Expected fail-closed expiry.
}

$auditRows = $connection->table('oneqay_identity_first_party_session_audit')->orderBy('occurred_at_unix')->get();
$assert($auditRows->count() >= 6, 'session authority audit evidence is incomplete');
$allowedEvents = ['session_issued', 'session_revoked', 'other_sessions_revoked', 'session_logout'];
foreach ($auditRows as $audit) {
    $assert(is_object($audit) && is_string($audit->event_type ?? null) && in_array($audit->event_type, $allowedEvents, true), 'unexpected session audit event type');
    $encoded = json_encode($audit, JSON_THROW_ON_ERROR);
    foreach ([$current->publicHandle(), $remote->publicHandle(), $testKey, 'Sprint36 synthetic password only'] as $secretMaterial) {
        $assert(! str_contains($encoded, $secretMaterial), 'session audit leaked secret or public selector material');
    }
}

$routeNames = array_filter(array_map(static fn ($route): ?string => $route->getName(), iterator_to_array($app['router']->getRoutes())));
foreach (['auth.sessions.inventory', 'auth.sessions.revoke-one', 'auth.sessions.revoke-others', 'auth.session-control.reauthenticate'] as $requiredRoute) {
    $assert(in_array($requiredRoute, $routeNames, true), 'required Sprint36 route missing: '.$requiredRoute);
}
$assert(! in_array('auth.sessions.revoke-all', $routeNames, true), 'forbidden revoke-all route exists');

$migration13 = require __DIR__.'/../database/migrations/0000_00_00_000013_create_first_party_session_authority.php';
try {
    $migration13->down();
    $assert(false, 'migration #13 rollback unexpectedly succeeded');
} catch (LogicException) {
    // Expected forward-only rollback prohibition.
}

$manager->disconnect('s36_session');
$manager->purge('s36_session');
@unlink($dbPath);
$removeTree($workspace);

echo "Sprint36 first-party session inventory/revocation regression passed.\n";

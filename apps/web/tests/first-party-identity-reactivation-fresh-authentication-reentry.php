<?php

declare(strict_types=1);

use App\Application\Identity\FirstPartyIdentityDisablementSessionTerminationRepository;
use App\Application\Identity\FirstPartySessionAuthorityService;
use App\Application\Identity\FirstPartySessionAuthorityViolation;
use App\Delivery\Http\Identity\FirstPartySessionController;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('w', 32));
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
        throw new RuntimeException('Sprint44 fresh-authentication re-entry regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s44-fresh-reentry-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'fresh-reentry.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's44_fresh_reentry');
$app['config']->set('database.connections.s44_fresh_reentry', [
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
$manager->purge('s44_fresh_reentry');
$manager->setDefaultConnection('s44_fresh_reentry');
$connection = $manager->connection('s44_fresh_reentry');
$connection->getPdo();

$migrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$expectedMigrations = [];
for ($index = 1; $index <= 15; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $matches = array_values(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix)));
    $assert(count($matches) === 1, 'migration #'.$index.' must exist exactly once');
    $expectedMigrations[] = $matches[0];
}
$assert($migrations === $expectedMigrations, 'migration set must remain exactly #1-#15');
$assert(! array_filter($migrations, static fn (string $file): bool => str_contains($file, '000016')), 'migration #16 must not exist');
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
$connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'target-reentry', 'first_party_authentication_enabled' => false],
    ['tenant_id' => 'tenant-alpha', 'id' => 'unrelated-alpha', 'first_party_authentication_enabled' => true],
    ['tenant_id' => 'tenant-beta', 'id' => 'target-reentry', 'first_party_authentication_enabled' => true],
]);
$connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-beta'],
]);
$connection->table('oneqay_identity_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'target-reentry', 'organization_id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'unrelated-alpha', 'organization_id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'target-reentry', 'organization_id' => 'organization-beta'],
]);

$targetPassword = 'Sprint44 target synthetic password';
$betaPassword = 'Sprint44 beta synthetic password';
$unrelatedPassword = 'Sprint44 unrelated synthetic password';
$targetHash = password_hash($targetPassword, PASSWORD_BCRYPT);
$betaHash = password_hash($betaPassword, PASSWORD_BCRYPT);
$unrelatedHash = password_hash($unrelatedPassword, PASSWORD_BCRYPT);
$assert(is_string($targetHash) && is_string($betaHash) && is_string($unrelatedHash), 'password hashes');
$connection->table('oneqay_identity_password_credentials')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'target-reentry', 'password_hash' => $targetHash, 'credential_epoch' => 7],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'unrelated-alpha', 'password_hash' => $unrelatedHash, 'credential_epoch' => 2],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'target-reentry', 'password_hash' => $betaHash, 'credential_epoch' => 3],
]);

$now = time();
$oldAuthority = str_repeat('a', 32);
$oldHandle = str_repeat('A', 43);
$unrelatedAuthority = str_repeat('b', 32);
$connection->table('oneqay_identity_first_party_sessions')->insert([
    [
        'tenant_id' => 'tenant-alpha',
        'authority_id' => $oldAuthority,
        'public_handle' => $oldHandle,
        'identity_id' => 'target-reentry',
        'organization_id' => 'organization-alpha',
        'outlet_id' => null,
        'device_id' => null,
        'credential_epoch' => 7,
        'factor_epoch' => null,
        'issued_at_unix' => $now - 300,
        'last_seen_at_unix' => $now - 200,
        'expires_at_unix' => $now + 6000,
        'revoked_at_unix' => $now - 100,
    ],
    [
        'tenant_id' => 'tenant-alpha',
        'authority_id' => $unrelatedAuthority,
        'public_handle' => str_repeat('B', 43),
        'identity_id' => 'unrelated-alpha',
        'organization_id' => 'organization-alpha',
        'outlet_id' => null,
        'device_id' => null,
        'credential_epoch' => 2,
        'factor_epoch' => null,
        'issued_at_unix' => $now - 60,
        'last_seen_at_unix' => $now - 30,
        'expires_at_unix' => $now + 7000,
        'revoked_at_unix' => null,
    ],
]);

/** @var FirstPartySessionController $controller */
$controller = $app->make(FirstPartySessionController::class);
/** @var FirstPartySessionAuthorityService $sessionAuthorities */
$sessionAuthorities = $app->make(FirstPartySessionAuthorityService::class);
/** @var FirstPartyIdentityDisablementSessionTerminationRepository $termination */
$termination = $app->make(FirstPartyIdentityDisablementSessionTerminationRepository::class);

$makeRequest = static function (array $payload, string $correlation): Request {
    $request = Request::create('/auth/login', 'POST', $payload, server: ['HTTP_ACCEPT' => 'application/json']);
    $request->attributes->set('oneqay.correlation_id', $correlation);
    $session = new Store('oneqay-s44-'.bin2hex(random_bytes(4)), new ArraySessionHandler(120));
    $session->start();
    $request->setLaravelSession($session);
    return $request;
};

$loginPayload = static fn (string $tenant, string $password, string $organization): array => [
    'tenant_id' => $tenant,
    'identity_id' => 'target-reentry',
    'password' => $password,
    'organization_id' => $organization,
];

$initialSessionCount = $connection->table('oneqay_identity_first_party_sessions')->count();
$disabledRequest = $makeRequest($loginPayload('tenant-alpha', $targetPassword, 'organization-alpha'), 'S44-disabled-login');
$disabledResponse = $controller->login($disabledRequest);
$assert($disabledResponse->getStatusCode() === 401, 'disabled identity fresh login must fail closed');
$assert($connection->table('oneqay_identity_first_party_sessions')->count() === $initialSessionCount, 'disabled login created logical authority');
$assert($disabledRequest->session()->get(FirstPartySessionKeys::SESSION_AUTHORITY_ID) === null, 'disabled login created framework authority');

$historicalBefore = $connection->table('oneqay_identity_first_party_sessions')
    ->where('tenant_id', 'tenant-alpha')->where('authority_id', $oldAuthority)->first();
$assert(is_object($historicalBefore) && $historicalBefore->revoked_at_unix !== null, 'historical authority must start revoked');

// Sprint43 has already qualified this exact state transition; Sprint44 proves that the
// committed eligibility transition itself creates no session authority.
$connection->table('oneqay_identities')
    ->where('tenant_id', 'tenant-alpha')->where('id', 'target-reentry')
    ->update(['first_party_authentication_enabled' => true]);
$assert($connection->table('oneqay_identity_first_party_sessions')->count() === $initialSessionCount, 'reactivation alone created logical authority');
$historicalAfterReactivation = $connection->table('oneqay_identity_first_party_sessions')
    ->where('tenant_id', 'tenant-alpha')->where('authority_id', $oldAuthority)->first();
$assert((int) $historicalAfterReactivation->revoked_at_unix === (int) $historicalBefore->revoked_at_unix, 'reactivation rewrote historical revocation evidence');

$badPasswordRequest = $makeRequest($loginPayload('tenant-alpha', 'wrong-password', 'organization-alpha'), 'S44-bad-password');
$assert($controller->login($badPasswordRequest)->getStatusCode() === 401, 'wrong current credential accepted');
$assert($connection->table('oneqay_identity_first_party_sessions')->count() === $initialSessionCount, 'failed credential issued authority');

$crossTenantRequest = $makeRequest($loginPayload('tenant-beta', $targetPassword, 'organization-beta'), 'S44-cross-tenant');
$assert($controller->login($crossTenantRequest)->getStatusCode() === 401, 'cross-tenant credential borrowing accepted');
$assert($connection->table('oneqay_identity_first_party_sessions')->where('tenant_id', 'tenant-beta')->count() === 0, 'cross-tenant login issued authority');

$invalidOrgRequest = $makeRequest($loginPayload('tenant-alpha', $targetPassword, 'organization-missing'), 'S44-invalid-org');
$assert($controller->login($invalidOrgRequest)->getStatusCode() === 401, 'invalid organization accepted');
$assert($connection->table('oneqay_identity_first_party_sessions')->count() === $initialSessionCount, 'invalid organization issued authority');

$freshRequest = $makeRequest($loginPayload('tenant-alpha', $targetPassword, 'organization-alpha'), 'S44-fresh-login');
$freshResponse = $controller->login($freshRequest);
$assert($freshResponse->getStatusCode() === 200, 'fresh login after reactivation failed');
$newAuthority = $freshRequest->session()->get(FirstPartySessionKeys::SESSION_AUTHORITY_ID);
$assert(is_string($newAuthority) && preg_match('/\A[0-9a-f]{32}\z/D', $newAuthority) === 1, 'fresh authority ID missing');
$assert(! hash_equals($oldAuthority, $newAuthority), 'historical authority ID was reused');
$newRow = $connection->table('oneqay_identity_first_party_sessions')
    ->where('tenant_id', 'tenant-alpha')->where('authority_id', $newAuthority)->first();
$assert(is_object($newRow), 'fresh authority row missing');
$assert(is_string($newRow->public_handle) && strlen($newRow->public_handle) === 43, 'fresh public handle malformed');
$assert(! hash_equals($oldHandle, (string) $newRow->public_handle), 'historical public handle was reused');
$assert((int) $newRow->credential_epoch === 7, 'fresh authority did not capture current credential epoch');
$assert($newRow->revoked_at_unix === null, 'fresh authority was born revoked');
$assert($connection->table('oneqay_identity_first_party_session_audit')
    ->where('tenant_id', 'tenant-alpha')->where('target_authority_id', $newAuthority)->where('event_type', 'session_issued')->exists(), 'session_issued audit missing');

try {
    $sessionAuthorities->assertActiveCurrent(
        TenantId::fromString('tenant-alpha'),
        PlatformIdentityId::fromString('target-reentry'),
        $oldAuthority,
        'organization-alpha',
        null,
        null,
        7,
        null,
    );
    $assert(false, 'historical revoked authority resurrected');
} catch (FirstPartySessionAuthorityViolation) {
    // Expected fail-closed historical authority denial.
}

$connection->table('oneqay_identities')
    ->where('tenant_id', 'tenant-alpha')->where('id', 'target-reentry')
    ->update(['first_party_authentication_enabled' => false]);
$revoked = $termination->revokeActiveForIdentityDisablement(
    TenantId::fromString('tenant-alpha'),
    PlatformIdentityId::fromString('target-reentry'),
    time(),
);
$assert($revoked === 1, 'second disablement did not terminate exactly the fresh target authority');
$freshAfterDisable = $connection->table('oneqay_identity_first_party_sessions')
    ->where('tenant_id', 'tenant-alpha')->where('authority_id', $newAuthority)->first();
$assert(is_object($freshAfterDisable) && $freshAfterDisable->revoked_at_unix !== null, 'fresh authority remained active after second disablement');
$historicalFinal = $connection->table('oneqay_identity_first_party_sessions')
    ->where('tenant_id', 'tenant-alpha')->where('authority_id', $oldAuthority)->first();
$assert((int) $historicalFinal->revoked_at_unix === (int) $historicalBefore->revoked_at_unix, 'second disablement cleared or rewrote historical revocation evidence');
$unrelatedFinal = $connection->table('oneqay_identity_first_party_sessions')
    ->where('tenant_id', 'tenant-alpha')->where('authority_id', $unrelatedAuthority)->first();
$assert(is_object($unrelatedFinal) && $unrelatedFinal->revoked_at_unix === null, 'second disablement altered unrelated session');

$source = file_get_contents(__DIR__.'/../app/Delivery/Http/Identity/FirstPartySessionController.php');
$routes = file_get_contents(__DIR__.'/../routes/web.php');
$assert(is_string($source) && str_contains($source, 'FirstPartyIdentityEligibilityVerifier $identityEligibility'), 'controller eligibility dependency missing');
$assert(is_string($source) && str_contains($source, '! $this->identityEligibility->isEligible($tenantId, $identityId)'), 'pre-issuance eligibility check missing');
$assert(is_string($routes) && ! preg_match('/reactivate.*login|restore.*session|resume.*session|login_after_reactivate/i', $routes), 'special reactivation-login or restore route exists');

$removeTree($workspace);
echo "Sprint44 first-party identity reactivation fresh-authentication re-entry regression passed.\n";

<?php

declare(strict_types=1);

use App\Application\Access\DurableOrganizationalAccessGrant;
use App\Application\Identity\FirstPartyIdentityEligibilityVerifier;
use App\Application\Identity\FirstPartySessionAuthorityClock;
use App\Application\Identity\FirstPartySessionAuthorityService;
use App\Application\Identity\PrivilegedTotpFactorEpochRepository;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Delivery\Http\Middleware\EnforceActiveFirstPartySessionAuthorityMiddleware;
use App\Domain\Device\DeviceId;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Outlet\OutletId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Access\LaravelDurableOrganizationalAccessRepository;
use App\Infrastructure\Identity\LaravelFirstPartyCredentialEpochRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityVerifier;
use App\Infrastructure\Identity\LaravelFirstPartySessionAuthorityRepository;
use App\Infrastructure\Organization\LaravelOrganizationalRelationshipVerifier;
use App\Infrastructure\Tenancy\LaravelTenantMembershipVerifier;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Symfony\Component\HttpFoundation\Response;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('o', 32));
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
        throw new RuntimeException('Sprint40 first-party session identity disablement revalidation regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s40-identity-revalidation-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'identity-eligibility.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's40_identity_revalidation');
$app['config']->set('database.connections.s40_identity_revalidation', [
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
$manager->purge('s40_identity_revalidation');
$manager->setDefaultConnection('s40_identity_revalidation');
$connection = $manager->connection('s40_identity_revalidation');
$connection->getPdo();

$migrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$expectedMigrations = [];
for ($index = 1; $index <= 14; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $matches = array_values(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix)));
    $assert(count($matches) === 1, 'migration #'.$index.' must exist exactly once');
    $expectedMigrations[] = $matches[0];
}
$assert($migrations === $expectedMigrations, 'migration set must be exactly #1-#14');
$assert($migrations[13] === '0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php', 'migration #14 exact filename');
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$columns = $connection->select("PRAGMA table_info('oneqay_identities')");
$eligibilityColumns = array_values(array_filter($columns, static fn (object $column): bool => ($column->name ?? null) === 'first_party_authentication_enabled'));
$assert(count($eligibilityColumns) === 1, 'eligibility column must exist exactly once');
$eligibilityColumn = $eligibilityColumns[0];
$assert((int) ($eligibilityColumn->notnull ?? 0) === 1, 'eligibility column must be NOT NULL');
$assert(in_array((string) ($eligibilityColumn->dflt_value ?? ''), ['1', "'1'", 'true', "'true'"], true), 'eligibility column default must be true');

$clock = new class implements FirstPartySessionAuthorityClock {
    public int $now = 1787676000;
    public function nowUnix(): int { return $this->now; }
};
$sessionRepository = new LaravelFirstPartySessionAuthorityRepository($connection, true, 'ci', true);
$credentialEpochs = new LaravelFirstPartyCredentialEpochRepository($connection, true, 'ci');
/** @var PrivilegedTotpFactorEpochRepository $factorEpochs */
$factorEpochs = $app->make(PrivilegedTotpFactorEpochRepository::class);
/** @var PrivilegedTotpMfaService $mfa */
$mfa = $app->make(PrivilegedTotpMfaService::class);
$sessionAuthorities = new FirstPartySessionAuthorityService(
    $sessionRepository,
    $clock,
    $credentialEpochs,
    $factorEpochs,
    $mfa,
    false,
    7200,
    43200,
);
$access = new LaravelDurableOrganizationalAccessRepository($connection, true, 'ci');
$tenantMemberships = new LaravelTenantMembershipVerifier($access);
$organizationalRelationships = new LaravelOrganizationalRelationshipVerifier($access);
$identityEligibility = new LaravelFirstPartyIdentityEligibilityVerifier($connection, true, 'ci', true);
$middleware = new EnforceActiveFirstPartySessionAuthorityMiddleware(
    $sessionAuthorities,
    $identityEligibility,
    $tenantMemberships,
    $organizationalRelationships,
);

/** @var FirstPartyIdentityEligibilityVerifier $containerEligibility */
$containerEligibility = $app->make(FirstPartyIdentityEligibilityVerifier::class);
$assert($containerEligibility instanceof LaravelFirstPartyIdentityEligibilityVerifier, 'service-provider eligibility binding');

$seedContext = static function (
    string $tenant,
    string $identity,
    string $organization,
    ?string $outlet = null,
    ?string $device = null,
) use ($connection, $access, $assert): void {
    $connection->table('oneqay_tenants')->insertOrIgnore(['id' => $tenant]);
    $connection->table('oneqay_identities')->insertOrIgnore(['tenant_id' => $tenant, 'id' => $identity]);
    $connection->table('oneqay_organizations')->insertOrIgnore(['tenant_id' => $tenant, 'id' => $organization]);
    $connection->table('oneqay_identity_organizations')->insertOrIgnore([
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'organization_id' => $organization,
    ]);
    if ($outlet !== null) {
        $connection->table('oneqay_outlets')->insertOrIgnore([
            'tenant_id' => $tenant,
            'id' => $outlet,
            'organization_id' => $organization,
        ]);
    }
    if ($device !== null) {
        $assert($outlet !== null, 'seeded device requires outlet');
        $connection->table('oneqay_devices')->insertOrIgnore([
            'tenant_id' => $tenant,
            'id' => $device,
            'organization_id' => $organization,
            'outlet_id' => $outlet,
        ]);
    }
    $passwordHash = password_hash('Sprint40 synthetic password '.$identity, PASSWORD_BCRYPT);
    $assert(is_string($passwordHash), 'synthetic password hash creation');
    $connection->table('oneqay_identity_password_credentials')->insertOrIgnore([
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'password_hash' => $passwordHash,
        'credential_epoch' => 1,
    ]);

    if ($outlet !== null) {
        $access->record(new DurableOrganizationalAccessGrant(
            TenantId::fromString($tenant),
            PlatformIdentityId::fromString($identity),
            OrganizationId::fromString($organization),
            OutletId::fromString($outlet),
            $device === null ? null : DeviceId::fromString($device),
        ));
    }
};

$issue = static function (
    string $tenant,
    string $identity,
    string $organization,
    ?string $outlet,
    ?string $device,
    string $correlation,
) use ($sessionAuthorities) {
    return $sessionAuthorities->issue(
        TenantId::fromString($tenant),
        PlatformIdentityId::fromString($identity),
        $organization,
        $outlet,
        $device,
        1,
        null,
        $correlation,
    );
};

$makeRequest = static function (
    string $tenant,
    string $identity,
    string $authority,
    string $organization,
    ?string $outlet = null,
    ?string $device = null,
    array $query = [],
    array $extraSession = [],
): Request {
    $session = new Store('oneqay-s40', new ArraySessionHandler(120));
    $session->start();
    $session->put([
        FirstPartySessionKeys::TENANT => $tenant,
        FirstPartySessionKeys::IDENTITY => $identity,
        FirstPartySessionKeys::SESSION_AUTHORITY_ID => $authority,
        FirstPartySessionKeys::ORGANIZATION => $organization,
        FirstPartySessionKeys::CREDENTIAL_EPOCH => 1,
    ]);
    if ($outlet !== null) {
        $session->put(FirstPartySessionKeys::OUTLET, $outlet);
    }
    if ($device !== null) {
        $session->put(FirstPartySessionKeys::DEVICE, $device);
    }
    foreach ($extraSession as $key => $value) {
        $session->put($key, $value);
    }

    $request = Request::create('/sprint40/protected-fixture', 'GET', $query);
    $request->setLaravelSession($session);
    $request->attributes->set('oneqay.correlation_id', 'S40-Request_'.bin2hex(random_bytes(4)));
    return $request;
};

$invoke = static function (Request $request) use ($middleware, $app): Response {
    $app->instance('request', $request);
    return $middleware->handle(
        $request,
        static fn (): Response => response()->json(['authorized' => true], 200, ['Cache-Control' => 'no-store, private']),
    );
};

$assertAllowed = static function (Response $response, string $message) use ($assert): void {
    $assert($response->getStatusCode() === 200, $message.' expected 200, got '.$response->getStatusCode());
};
$assertDenied = static function (Request $request, Response $response, string $message) use ($assert): void {
    $assert($response->getStatusCode() === 401, $message.' expected 401, got '.$response->getStatusCode());
    $payload = json_decode((string) $response->getContent(), true);
    $assert(is_array($payload), $message.' denial payload missing');
    $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
    $assert(str_contains($encoded, 'SESSION_AUTHORITY_DENIED'), $message.' generic denial code missing');
    $assert(! $request->session()->has(FirstPartySessionKeys::IDENTITY), $message.' local session was not invalidated');
};

// Existing rows and newly inserted identities are explicitly enabled by the migration default.
$seedContext('tenant-enabled', 'identity-enabled', 'organization-enabled');
$enabled = $issue('tenant-enabled', 'identity-enabled', 'organization-enabled', null, null, 'S40-Issue_Enabled');
$assert($identityEligibility->isEligible(TenantId::fromString('tenant-enabled'), PlatformIdentityId::fromString('identity-enabled')), 'enabled exact identity must be eligible');
$enabledRequest = $makeRequest('tenant-enabled', 'identity-enabled', $enabled->authorityId(), 'organization-enabled');
$assertAllowed($invoke($enabledRequest), 'enabled exact identity with current organizational access');

// A direct test-only durable transition is observed on the next protected request; no cache grandfathering.
$connection->table('oneqay_identities')
    ->where('tenant_id', 'tenant-enabled')
    ->where('id', 'identity-enabled')
    ->update(['first_party_authentication_enabled' => false]);
$assert(! $identityEligibility->isEligible(TenantId::fromString('tenant-enabled'), PlatformIdentityId::fromString('identity-enabled')), 'disabled exact identity must be ineligible');
$disabledRequest = $makeRequest('tenant-enabled', 'identity-enabled', $enabled->authorityId(), 'organization-enabled');
$assertDenied($disabledRequest, $invoke($disabledRequest), 'disabled identity after prior authorized request');

// Caller-controlled eligibility or identity-state selectors cannot override server-owned disablement.
$callerOverride = $makeRequest(
    'tenant-enabled',
    'identity-enabled',
    $enabled->authorityId(),
    'organization-enabled',
    null,
    null,
    ['first_party_authentication_enabled' => '1', 'identity_id' => 'identity-other'],
    [FirstPartySessionKeys::STEP_UP_SCOPE => 'session_control', FirstPartySessionKeys::STEP_UP_VERIFIED_AT => $clock->now],
);
$assertDenied($callerOverride, $invoke($callerOverride), 'caller selector or privileged step-up override');

// Missing and malformed eligibility evidence fail closed.
$assert(! $identityEligibility->isEligible(TenantId::fromString('tenant-missing'), PlatformIdentityId::fromString('identity-missing')), 'missing identity row must fail closed');
$seedContext('tenant-malformed', 'identity-malformed', 'organization-malformed');
$connection->statement("UPDATE oneqay_identities SET first_party_authentication_enabled = 2 WHERE tenant_id = 'tenant-malformed' AND id = 'identity-malformed'");
$assert(! $identityEligibility->isEligible(TenantId::fromString('tenant-malformed'), PlatformIdentityId::fromString('identity-malformed')), 'malformed eligibility value must fail closed');

// Persistence, runtime, and session-control unavailability each fail closed.
$tenantEnabled = TenantId::fromString('tenant-enabled');
$identityEnabled = PlatformIdentityId::fromString('identity-enabled');
$assert(! (new LaravelFirstPartyIdentityEligibilityVerifier($connection, false, 'ci', true))->isEligible($tenantEnabled, $identityEnabled), 'persistence disabled must fail closed');
$assert(! (new LaravelFirstPartyIdentityEligibilityVerifier($connection, true, 'production', true))->isEligible($tenantEnabled, $identityEnabled), 'production runtime must fail closed');
$assert(! (new LaravelFirstPartyIdentityEligibilityVerifier($connection, true, 'ci', false))->isEligible($tenantEnabled, $identityEnabled), 'session control disabled must fail closed');

// Disabling one identity does not affect another identity in the same tenant or another tenant.
$seedContext('tenant-isolated', 'identity-isolated-a', 'organization-isolated');
$seedContext('tenant-isolated', 'identity-isolated-b', 'organization-isolated');
$seedContext('tenant-other', 'identity-other', 'organization-other');
$isolatedA = $issue('tenant-isolated', 'identity-isolated-a', 'organization-isolated', null, null, 'S40-Issue_IsolatedA');
$isolatedB = $issue('tenant-isolated', 'identity-isolated-b', 'organization-isolated', null, null, 'S40-Issue_IsolatedB');
$otherTenant = $issue('tenant-other', 'identity-other', 'organization-other', null, null, 'S40-Issue_OtherTenant');
$connection->table('oneqay_identities')->where('tenant_id', 'tenant-isolated')->where('id', 'identity-isolated-a')->update(['first_party_authentication_enabled' => false]);
$isolatedARequest = $makeRequest('tenant-isolated', 'identity-isolated-a', $isolatedA->authorityId(), 'organization-isolated');
$assertDenied($isolatedARequest, $invoke($isolatedARequest), 'disabled isolated identity');
$isolatedBRequest = $makeRequest('tenant-isolated', 'identity-isolated-b', $isolatedB->authorityId(), 'organization-isolated');
$assertAllowed($invoke($isolatedBRequest), 'unrelated identity remains eligible');
$otherTenantRequest = $makeRequest('tenant-other', 'identity-other', $otherTenant->authorityId(), 'organization-other');
$assertAllowed($invoke($otherTenantRequest), 'unrelated tenant remains eligible');

// Current eligibility never replaces Sprint39 membership and organizational revalidation.
$seedContext('tenant-membership-loss', 'identity-membership-loss', 'organization-membership-loss');
$membershipLoss = $issue('tenant-membership-loss', 'identity-membership-loss', 'organization-membership-loss', null, null, 'S40-Issue_MembershipLoss');
$connection->table('oneqay_identity_organizations')
    ->where('tenant_id', 'tenant-membership-loss')
    ->where('identity_id', 'identity-membership-loss')
    ->where('organization_id', 'organization-membership-loss')
    ->delete();
$assert($identityEligibility->isEligible(TenantId::fromString('tenant-membership-loss'), PlatformIdentityId::fromString('identity-membership-loss')), 'membership-loss identity should remain eligible');
$membershipLossRequest = $makeRequest('tenant-membership-loss', 'identity-membership-loss', $membershipLoss->authorityId(), 'organization-membership-loss');
$assertDenied($membershipLossRequest, $invoke($membershipLossRequest), 'eligible identity with removed membership');

// Session rotation/retry cannot resurrect disabled access.
$retryRequest = $makeRequest(
    'tenant-isolated',
    'identity-isolated-a',
    $isolatedA->authorityId(),
    'organization-isolated',
    null,
    null,
    ['retry' => '1'],
);
$assertDenied($retryRequest, $invoke($retryRequest), 'rotated/retried disabled identity authority');

// No new audit vocabulary is introduced by request-time eligibility revalidation.
$allowedEvents = ['session_issued', 'session_revoked', 'other_sessions_revoked', 'all_sessions_revoked', 'session_logout'];
foreach ($connection->table('oneqay_identity_first_party_session_audit')->get() as $audit) {
    $assert(is_object($audit) && in_array((string) ($audit->event_type ?? ''), $allowedEvents, true), 'unexpected audit event vocabulary');
    $encoded = json_encode($audit, JSON_THROW_ON_ERROR);
    $assert(! str_contains($encoded, $testKey), 'audit leaked application key');
}

$assert((int) config('oneqay.session_control.idle_ttl_seconds') === 7200, 'idle TTL changed');
$assert((int) config('oneqay.session_control.absolute_ttl_seconds') === 43200, 'absolute TTL changed');

$removeTree($workspace);
echo "Sprint40 first-party session identity disablement revalidation regression passed.\n";

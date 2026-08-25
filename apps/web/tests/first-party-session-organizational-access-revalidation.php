<?php

declare(strict_types=1);

use App\Application\Access\DurableOrganizationalAccessGrant;
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
        throw new RuntimeException('Sprint39 first-party session organizational access revalidation regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s39-org-revalidation-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'organizational-access.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's39_org_revalidation');
$app['config']->set('database.connections.s39_org_revalidation', [
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
$manager->purge('s39_org_revalidation');
$manager->setDefaultConnection('s39_org_revalidation');
$connection = $manager->connection('s39_org_revalidation');
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
$assert($migrations === $expectedMigrations, 'migration set must remain exactly #1-#13');
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}
$assert(! array_filter($migrations, static fn (string $file): bool => str_contains($file, '000014')), 'migration #14 must not exist');

$clock = new class implements FirstPartySessionAuthorityClock {
    public int $now = 1787625000;
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
$middleware = new EnforceActiveFirstPartySessionAuthorityMiddleware(
    $sessionAuthorities,
    $tenantMemberships,
    $organizationalRelationships,
);

$seedContext = static function (
    string $tenant,
    string $identity,
    string $organization,
    ?string $outlet,
    ?string $device,
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
    $passwordHash = password_hash('Sprint39 synthetic password '.$identity, PASSWORD_BCRYPT);
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
    ?string $outlet,
    ?string $device,
    array $query = [],
    array $extraSession = [],
): Request {
    $session = new Store('oneqay-s39', new ArraySessionHandler(120));
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

    $request = Request::create('/sprint39/protected-fixture', 'GET', $query);
    $request->setLaravelSession($session);
    $request->attributes->set('oneqay.correlation_id', 'S39-Request_'.bin2hex(random_bytes(4)));
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

// Exact current durable access permits an otherwise-valid logical authority.
$seedContext('tenant-valid', 'identity-valid', 'organization-valid', 'outlet-valid', 'device-valid');
$valid = $issue('tenant-valid', 'identity-valid', 'organization-valid', 'outlet-valid', 'device-valid', 'S39-Issue_Valid');
$validRequest = $makeRequest('tenant-valid', 'identity-valid', $valid->authorityId(), 'organization-valid', 'outlet-valid', 'device-valid');
$assertAllowed($invoke($validRequest), 'valid exact organizational access');

// Removing the only tenant membership denies a still-active logical authority.
$seedContext('tenant-membership-loss', 'identity-membership-loss', 'organization-membership-loss', null, null);
$membershipLoss = $issue('tenant-membership-loss', 'identity-membership-loss', 'organization-membership-loss', null, null, 'S39-Issue_MembershipLoss');
$connection->table('oneqay_identity_organizations')
    ->where('tenant_id', 'tenant-membership-loss')
    ->where('identity_id', 'identity-membership-loss')
    ->where('organization_id', 'organization-membership-loss')
    ->delete();
$beforeMembershipDenialRows = $connection->table('oneqay_identity_first_party_sessions')->count();
$membershipRequest = $makeRequest('tenant-membership-loss', 'identity-membership-loss', $membershipLoss->authorityId(), 'organization-membership-loss', null, null);
$assertDenied($membershipRequest, $invoke($membershipRequest), 'removed tenant membership');
$assert($connection->table('oneqay_identity_first_party_sessions')->count() === $beforeMembershipDenialRows, 'membership denial minted or removed logical authority');
$assert(! $connection->table('oneqay_identity_organizations')->where('tenant_id', 'tenant-membership-loss')->where('identity_id', 'identity-membership-loss')->exists(), 'membership denial recreated removed grant');

// Removing the selected organization denies it even when another tenant membership remains.
$seedContext('tenant-org-loss', 'identity-org-loss', 'organization-org-loss', null, null);
$connection->table('oneqay_organizations')->insert(['tenant_id' => 'tenant-org-loss', 'id' => 'organization-org-keep']);
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-org-loss',
    'identity_id' => 'identity-org-loss',
    'organization_id' => 'organization-org-keep',
]);
$orgLoss = $issue('tenant-org-loss', 'identity-org-loss', 'organization-org-loss', null, null, 'S39-Issue_OrgLoss');
$connection->table('oneqay_identity_organizations')
    ->where('tenant_id', 'tenant-org-loss')
    ->where('identity_id', 'identity-org-loss')
    ->where('organization_id', 'organization-org-loss')
    ->delete();
$assert($tenantMemberships->verify('identity-org-loss', 'tenant-org-loss') !== null, 'organization-loss fixture accidentally removed tenant membership');
$orgLossRequest = $makeRequest('tenant-org-loss', 'identity-org-loss', $orgLoss->authorityId(), 'organization-org-loss', null, null);
$assertDenied($orgLossRequest, $invoke($orgLossRequest), 'removed selected organization');

// Removing an outlet grant denies outlet-bound authority without broader fallback.
$seedContext('tenant-outlet-loss', 'identity-outlet-loss', 'organization-outlet-loss', 'outlet-outlet-loss', null);
$outletLoss = $issue('tenant-outlet-loss', 'identity-outlet-loss', 'organization-outlet-loss', 'outlet-outlet-loss', null, 'S39-Issue_OutletLoss');
$connection->table('oneqay_outlet_access_grants')
    ->where('tenant_id', 'tenant-outlet-loss')
    ->where('identity_id', 'identity-outlet-loss')
    ->where('organization_id', 'organization-outlet-loss')
    ->where('outlet_id', 'outlet-outlet-loss')
    ->delete();
$outletLossRequest = $makeRequest('tenant-outlet-loss', 'identity-outlet-loss', $outletLoss->authorityId(), 'organization-outlet-loss', 'outlet-outlet-loss', null);
$assertDenied($outletLossRequest, $invoke($outletLossRequest), 'removed outlet grant');
$assert(! $connection->table('oneqay_outlet_access_grants')->where('tenant_id', 'tenant-outlet-loss')->where('identity_id', 'identity-outlet-loss')->exists(), 'outlet denial recreated removed grant');

// Removing only the device grant denies device-bound authority; outlet grant remains valid.
$seedContext('tenant-device-loss', 'identity-device-loss', 'organization-device-loss', 'outlet-device-loss', 'device-device-loss');
$deviceLoss = $issue('tenant-device-loss', 'identity-device-loss', 'organization-device-loss', 'outlet-device-loss', 'device-device-loss', 'S39-Issue_DeviceLoss');
$connection->table('oneqay_device_access_grants')
    ->where('tenant_id', 'tenant-device-loss')
    ->where('identity_id', 'identity-device-loss')
    ->where('organization_id', 'organization-device-loss')
    ->where('outlet_id', 'outlet-device-loss')
    ->where('device_id', 'device-device-loss')
    ->delete();
$assert($connection->table('oneqay_outlet_access_grants')->where('tenant_id', 'tenant-device-loss')->where('identity_id', 'identity-device-loss')->exists(), 'device-loss fixture removed outlet grant');
$deviceLossRequest = $makeRequest('tenant-device-loss', 'identity-device-loss', $deviceLoss->authorityId(), 'organization-device-loss', 'outlet-device-loss', 'device-device-loss');
$assertDenied($deviceLossRequest, $invoke($deviceLossRequest), 'removed device grant');

// A rotated framework session, retry, caller selector, or step-up evidence cannot restore stale access.
$deviceRetryRequest = $makeRequest(
    'tenant-device-loss',
    'identity-device-loss',
    $deviceLoss->authorityId(),
    'organization-device-loss',
    'outlet-device-loss',
    'device-device-loss',
    ['organization_id' => 'organization-valid', 'outlet_id' => 'outlet-valid', 'device_id' => 'device-valid'],
    [FirstPartySessionKeys::STEP_UP_SCOPE => 'session_control', FirstPartySessionKeys::STEP_UP_VERIFIED_AT => $clock->now],
);
$assertDenied($deviceRetryRequest, $invoke($deviceRetryRequest), 'rotated/retried stale device authority');
$assert(! $connection->table('oneqay_device_access_grants')->where('tenant_id', 'tenant-device-loss')->where('identity_id', 'identity-device-loss')->exists(), 'retry or caller selector recreated device grant');

// Device-bound authority without outlet fails closed rather than falling back to organization access.
$seedContext('tenant-structure', 'identity-structure', 'organization-structure', 'outlet-structure', 'device-structure');
$structural = $issue('tenant-structure', 'identity-structure', 'organization-structure', null, 'device-structure', 'S39-Issue_Structure');
$structuralRequest = $makeRequest('tenant-structure', 'identity-structure', $structural->authorityId(), 'organization-structure', null, 'device-structure');
$assertDenied($structuralRequest, $invoke($structuralRequest), 'device without outlet');

// Non-canonical tenant/identity identifiers fail closed instead of being normalized into authority.
$seedContext('tenant-canonical', 'identity-canonical', 'organization-canonical', null, null);
$canonical = $issue('tenant-canonical', 'identity-canonical', 'organization-canonical', null, null, 'S39-Issue_Canonical');
$canonicalRequest = $makeRequest('TENANT-CANONICAL', 'identity-canonical', $canonical->authorityId(), 'organization-canonical', null, null);
$assertDenied($canonicalRequest, $invoke($canonicalRequest), 'non-canonical tenant identifier');

// Another tenant and another identity remain independently authorized after unrelated access loss.
$seedContext('tenant-isolated', 'identity-isolated-a', 'organization-isolated', null, null);
$seedContext('tenant-isolated', 'identity-isolated-b', 'organization-isolated', null, null);
$seedContext('tenant-other', 'identity-other', 'organization-other', null, null);
$isolatedA = $issue('tenant-isolated', 'identity-isolated-a', 'organization-isolated', null, null, 'S39-Issue_IsolatedA');
$isolatedB = $issue('tenant-isolated', 'identity-isolated-b', 'organization-isolated', null, null, 'S39-Issue_IsolatedB');
$otherTenant = $issue('tenant-other', 'identity-other', 'organization-other', null, null, 'S39-Issue_OtherTenant');
$connection->table('oneqay_identity_organizations')
    ->where('tenant_id', 'tenant-isolated')
    ->where('identity_id', 'identity-isolated-a')
    ->where('organization_id', 'organization-isolated')
    ->delete();
$isolatedARequest = $makeRequest('tenant-isolated', 'identity-isolated-a', $isolatedA->authorityId(), 'organization-isolated', null, null);
$assertDenied($isolatedARequest, $invoke($isolatedARequest), 'isolated removed identity');
$isolatedBRequest = $makeRequest('tenant-isolated', 'identity-isolated-b', $isolatedB->authorityId(), 'organization-isolated', null, null);
$assertAllowed($invoke($isolatedBRequest), 'unrelated identity isolation');
$otherTenantRequest = $makeRequest('tenant-other', 'identity-other', $otherTenant->authorityId(), 'organization-other', null, null);
$assertAllowed($invoke($otherTenantRequest), 'unrelated tenant isolation');

// Existing lifetime values and audit vocabulary remain unchanged and secret-free.
$assert((int) config('oneqay.session_control.idle_ttl_seconds') === 7200, 'idle TTL changed');
$assert((int) config('oneqay.session_control.absolute_ttl_seconds') === 43200, 'absolute TTL changed');
$allowedEvents = ['session_issued', 'session_revoked', 'other_sessions_revoked', 'all_sessions_revoked', 'session_logout'];
foreach ($connection->table('oneqay_identity_first_party_session_audit')->get() as $audit) {
    $assert(is_object($audit) && in_array((string) ($audit->event_type ?? ''), $allowedEvents, true), 'unexpected audit event vocabulary');
    $encoded = json_encode($audit, JSON_THROW_ON_ERROR);
    $assert(! str_contains($encoded, $testKey), 'audit leaked application key');
}

$removeTree($workspace);
echo "Sprint39 first-party session organizational access revalidation regression passed.\n";

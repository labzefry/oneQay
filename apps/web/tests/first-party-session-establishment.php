<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Tenancy\TenantContextStore;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('l', 32));
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'local',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint 27 first-party session regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s27-session-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'session.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.connections.s27_session', [
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
/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s27_session');
$manager->setDefaultConnection('s27_session');
$connection = $manager->connection('s27_session');
$connection->getPdo();

$migrationNames = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
];
$actualMigrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($actualMigrations);
$assert($actualMigrations === $migrationNames, 'canonical migration set is not exactly #1-#7');
foreach ($migrationNames as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}

$connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'shared-user'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'no-credential-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'no-authority-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'shared-user'],
]);

$connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-beta'],
]);
$connection->table('oneqay_outlets')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'outlet-alpha', 'organization_id' => 'organization-alpha'],
]);
$connection->table('oneqay_devices')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'device-alpha', 'organization_id' => 'organization-alpha', 'outlet_id' => 'outlet-alpha'],
]);

foreach (['admin-alpha', 'shared-user', 'no-credential-alpha', 'no-authority-alpha'] as $identity) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'organization_id' => 'organization-alpha',
    ]);
}
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-beta',
    'identity_id' => 'shared-user',
    'organization_id' => 'organization-beta',
]);
$connection->table('oneqay_outlet_access_grants')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'shared-user',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
]);
$connection->table('oneqay_device_access_grants')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'shared-user',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
]);

$adminPassword = 'Admin Alpha / synthetic only';
$sharedAlphaPassword = 'Shared Alpha / synthetic only';
$sharedBetaPassword = 'Shared Beta / synthetic only';
$noAuthorityPassword = 'No Authority / synthetic only';
$adminHash = password_hash($adminPassword, PASSWORD_BCRYPT);
$sharedAlphaHash = password_hash($sharedAlphaPassword, PASSWORD_BCRYPT);
$sharedBetaHash = password_hash($sharedBetaPassword, PASSWORD_BCRYPT);
$noAuthorityHash = password_hash($noAuthorityPassword, PASSWORD_BCRYPT);
$assert(is_string($adminHash) && is_string($sharedAlphaHash) && is_string($sharedBetaHash) && is_string($noAuthorityHash), 'synthetic password hashes were not created');

$connection->table('oneqay_identity_password_credentials')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'admin-alpha', 'password_hash' => $adminHash],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'shared-user', 'password_hash' => $sharedAlphaHash],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'no-authority-alpha', 'password_hash' => $noAuthorityHash],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'shared-user', 'password_hash' => $sharedBetaHash],
]);

$connection->table('oneqay_roles')->insert([
    'tenant_id' => 'tenant-alpha',
    'id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
]);
$connection->table('oneqay_role_permissions')->insert([
    'tenant_id' => 'tenant-alpha',
    'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
    'permission_id' => AdministrationPermission::MANAGE,
]);
$connection->table('oneqay_tenant_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'admin-alpha',
    'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
]);

$cookieName = (string) config('session.cookie');
$assert($cookieName !== '', 'session cookie name missing');

$app['router']->get('/__s27/session-inspect', static function (Request $request) {
    $auth = [];
    foreach (FirstPartySessionKeys::all() as $key) {
        $auth[$key] = $request->session()->get($key);
    }

    return response()->json([
        'csrf_token' => $request->session()->token(),
        'auth' => $auth,
    ]);
})->middleware('web');

$refreshCookie = static function (\Symfony\Component\HttpFoundation\Response $response, string $cookieName, ?string &$cookie): void {
    foreach ($response->headers->getCookies() as $responseCookie) {
        if ($responseCookie->getName() === $cookieName) {
            $cookie = $responseCookie->getValue();
        }
    }
};

$inspect = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
) use ($refreshCookie, $assert): array {
    $request = Request::create(
        '/__s27/session-inspect',
        'GET',
        cookies: $cookie === null ? [] : [$cookieName => $cookie],
        server: ['HTTP_ACCEPT' => 'application/json'],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $assert($response->getStatusCode() === 200, 'test-only session inspection failed');
    $refreshCookie($response, $cookieName, $cookie);

    return json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
};

$sendLogin = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    ?string $csrfToken,
    array $payload,
    string $ip,
    bool $includeCsrf = true,
) use ($refreshCookie): \Symfony\Component\HttpFoundation\Response {
    if ($includeCsrf && $csrfToken !== null) {
        $payload['_token'] = $csrfToken;
    }
    $request = Request::create(
        '/auth/login',
        'POST',
        $payload,
        cookies: $cookie === null ? [] : [$cookieName => $cookie],
        server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CORRELATION_ID' => 'S27-Auth_0001',
            'REMOTE_ADDR' => $ip,
        ],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $refreshCookie($response, $cookieName, $cookie);

    return $response;
};

$sendLogout = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    ?string $csrfToken,
    bool $includeCsrf = true,
) use ($refreshCookie): \Symfony\Component\HttpFoundation\Response {
    $payload = [];
    if ($includeCsrf && $csrfToken !== null) {
        $payload['_token'] = $csrfToken;
    }
    $request = Request::create(
        '/auth/logout',
        'POST',
        $payload,
        cookies: $cookie === null ? [] : [$cookieName => $cookie],
        server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CORRELATION_ID' => 'S27-Auth_0001',
            'REMOTE_ADDR' => '10.27.0.250',
        ],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $refreshCookie($response, $cookieName, $cookie);

    return $response;
};

$sendPolicyMutation = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    string $csrfToken,
    string $mutationId,
) use ($refreshCookie): \Symfony\Component\HttpFoundation\Response {
    $request = Request::create(
        '/administration/policy/mutations',
        'POST',
        [
            '_token' => $csrfToken,
            'mutation_id' => $mutationId,
            'operation' => 'role.create',
            'role' => 's27-synthetic-operator',
        ],
        cookies: $cookie === null ? [] : [$cookieName => $cookie],
        server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CORRELATION_ID' => 'S27-Policy_0001',
        ],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $refreshCookie($response, $cookieName, $cookie);

    return $response;
};

$baseLogin = static fn (string $tenant, string $identity, string $password, string $organization): array => [
    'tenant_id' => $tenant,
    'identity_id' => $identity,
    'password' => $password,
    'organization_id' => $organization,
];

$assertNoAuth = static function (array $inspection) use ($assert): void {
    $auth = $inspection['auth'] ?? null;
    $assert(is_array($auth), 'session inspection auth payload missing');
    foreach (FirstPartySessionKeys::all() as $key) {
        $assert(array_key_exists($key, $auth), 'session inspection omitted '.$key);
        $assert($auth[$key] === null, 'failed/cleared session retained '.$key);
    }
};

$assertContextsCleared = static function () use ($app, $assert): void {
    $assert($app->make(TenantContextStore::class)->current() === null, 'tenant request context leaked after authentication request');
    $assert($app->make(OrganizationalContextStore::class)->current() === null, 'organizational request context leaked after authentication request');
};

$cookie = null;
$initial = $inspect($kernel, $cookieName, $cookie);
$csrfInitial = $initial['csrf_token'] ?? null;
$assert(is_string($cookie) && $cookie !== '', 'anonymous framework session cookie missing');
$assert(is_string($csrfInitial) && $csrfInitial !== '', 'anonymous CSRF token missing');
$anonymousCookie = $cookie;
$assertNoAuth($initial);

$adminLogin = $baseLogin('tenant-alpha', 'admin-alpha', $adminPassword, 'organization-alpha');

// CSRF remains framework-mandatory before authentication logic.
$response = $sendLogin($kernel, $cookieName, $cookie, $csrfInitial, $adminLogin, '10.27.0.1', false);
$assert($response->getStatusCode() === 419, 'missing login CSRF token was not rejected');
$inspection = $inspect($kernel, $cookieName, $cookie);
$assertNoAuth($inspection);

// Successful login rotates the session identifier and CSRF token, and writes canonical verified context only.
$response = $sendLogin($kernel, $cookieName, $cookie, $csrfInitial, $adminLogin, '10.27.0.2');
$assert($response->getStatusCode() === 200, 'valid first-party login did not succeed');
$assert($cookie !== null && $cookie !== $anonymousCookie, 'authenticated session cookie was not rotated');
$successBody = (string) $response->getContent();
$assert(! str_contains($successBody, $adminPassword), 'successful response exposed password material');
$assert(! str_contains($successBody, $adminHash), 'successful response exposed password hash material');
$decodedSuccess = json_decode($successBody, true, flags: JSON_THROW_ON_ERROR);
$assert(($decodedSuccess['status'] ?? null) === 'ok', 'login success envelope status');
$assert(($decodedSuccess['correlation_id'] ?? null) === 'S27-Auth_0001', 'login correlation id not preserved');

$inspection = $inspect($kernel, $cookieName, $cookie);
$csrfAuthenticated = $inspection['csrf_token'] ?? null;
$assert(is_string($csrfAuthenticated) && $csrfAuthenticated !== '' && $csrfAuthenticated !== $csrfInitial, 'CSRF token did not rotate on login');
$auth = $inspection['auth'] ?? [];
$assert(($auth[FirstPartySessionKeys::IDENTITY] ?? null) === 'admin-alpha', 'canonical identity session fact missing');
$assert(($auth[FirstPartySessionKeys::TENANT] ?? null) === 'tenant-alpha', 'canonical tenant session fact missing');
$assert(($auth[FirstPartySessionKeys::ORGANIZATION] ?? null) === 'organization-alpha', 'canonical organization session fact missing');
$assert(($auth[FirstPartySessionKeys::OUTLET] ?? null) === null, 'unverified outlet was stored');
$assert(($auth[FirstPartySessionKeys::DEVICE] ?? null) === null, 'unverified device was stored');
$assertContextsCleared();

// The existing Sprint 25 route consumes the real Sprint 27 session but still applies its own durable authorization checks.
$response = $sendPolicyMutation($kernel, $cookieName, $cookie, $csrfAuthenticated, 's27-authenticated-role-create');
$assert($response->getStatusCode() === 200, 'Sprint 25 policy route rejected valid Sprint 27 authenticated admin session');
$assert($connection->table('oneqay_roles')->where('tenant_id', 'tenant-alpha')->where('id', 's27-synthetic-operator')->exists(), 'policy mutation did not use authenticated tenant context');

// Logout itself remains CSRF protected.
$response = $sendLogout($kernel, $cookieName, $cookie, $csrfAuthenticated, false);
$assert($response->getStatusCode() === 419, 'missing logout CSRF token was not rejected');
$inspection = $inspect($kernel, $cookieName, $cookie);
$assert(($inspection['auth'][FirstPartySessionKeys::IDENTITY] ?? null) === 'admin-alpha', 'CSRF-rejected logout cleared authenticated state');

$authenticatedCookie = $cookie;
$response = $sendLogout($kernel, $cookieName, $cookie, $csrfAuthenticated, true);
$assert($response->getStatusCode() === 204, 'valid logout did not return no-content success');
$assert($cookie !== null && $cookie !== $authenticatedCookie, 'logout did not rotate the session cookie');
$inspection = $inspect($kernel, $cookieName, $cookie);
$csrfAfterLogout = $inspection['csrf_token'] ?? null;
$assert(is_string($csrfAfterLogout) && $csrfAfterLogout !== '' && $csrfAfterLogout !== $csrfAuthenticated, 'logout did not rotate CSRF token');
$assertNoAuth($inspection);
$assertContextsCleared();

$response = $sendPolicyMutation($kernel, $cookieName, $cookie, $csrfAfterLogout, 's27-after-logout');
$assert($response->getStatusCode() === 403, 'logged-out session remained authoritative for policy delivery');

// Establish the canonical generic failure envelope and prove all business failures collapse to it.
$response = $sendLogin(
    $kernel,
    $cookieName,
    $cookie,
    $csrfAfterLogout,
    $baseLogin('tenant-alpha', 'admin-alpha', 'wrong synthetic password', 'organization-alpha'),
    '10.27.1.1',
);
$assert($response->getStatusCode() === 401, 'wrong password did not fail generically');
$genericFailure = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($genericFailure['error']['code'] ?? null) === 'AUTHENTICATION_FAILED', 'generic failure code changed');
$assert(! str_contains((string) $response->getContent(), 'wrong synthetic password'), 'failure response exposed submitted password');
$assertNoAuth($inspect($kernel, $cookieName, $cookie));
$assertContextsCleared();

$failureCases = [
    ['absent identity', $baseLogin('tenant-alpha', 'identity-absent', $adminPassword, 'organization-alpha'), '10.27.1.2'],
    ['missing credential', $baseLogin('tenant-alpha', 'no-credential-alpha', $adminPassword, 'organization-alpha'), '10.27.1.3'],
    ['cross-tenant credential', $baseLogin('tenant-beta', 'shared-user', $sharedAlphaPassword, 'organization-beta'), '10.27.1.4'],
    ['foreign organization', $baseLogin('tenant-alpha', 'admin-alpha', $adminPassword, 'organization-beta'), '10.27.1.5'],
    ['unknown field', [...$adminLogin, 'role' => 'authorization-policy-administrator'], '10.27.1.6'],
    ['device without outlet', [...$adminLogin, 'device_id' => 'device-alpha'], '10.27.1.7'],
];

foreach ($failureCases as [$label, $payload, $ip]) {
    $inspection = $inspect($kernel, $cookieName, $cookie);
    $csrf = $inspection['csrf_token'] ?? null;
    $assert(is_string($csrf) && $csrf !== '', $label.' CSRF token missing');
    $response = $sendLogin($kernel, $cookieName, $cookie, $csrf, $payload, $ip);
    $assert($response->getStatusCode() === 401, $label.' status was not generic 401');
    $decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $assert($decoded === $genericFailure, $label.' exposed a distinct business failure envelope');
    $assertNoAuth($inspect($kernel, $cookieName, $cookie));
    $assertContextsCleared();
}

// Persistence disabled remains the same generic business failure and writes no authenticated state.
$app['config']->set('database.oneqay_persistence_enabled', false);
$app->forgetScopedInstances();
$inspection = $inspect($kernel, $cookieName, $cookie);
$csrf = $inspection['csrf_token'] ?? null;
$response = $sendLogin($kernel, $cookieName, $cookie, is_string($csrf) ? $csrf : null, $adminLogin, '10.27.1.8');
$assert($response->getStatusCode() === 401, 'persistence-disabled login did not fail generically');
$decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert($decoded === $genericFailure, 'persistence-disabled login exposed a distinct failure');
$assertNoAuth($inspect($kernel, $cookieName, $cookie));
$app['config']->set('database.oneqay_persistence_enabled', true);
$app->forgetScopedInstances();

// Controller defense remains fail-closed even if a cached/previously registered route is invoked under denied runtime.
foreach (['preview', 'production'] as $runtime) {
    $app['config']->set('oneqay.runtime_class', $runtime);
    $inspection = $inspect($kernel, $cookieName, $cookie);
    $csrf = $inspection['csrf_token'] ?? null;
    $response = $sendLogin($kernel, $cookieName, $cookie, is_string($csrf) ? $csrf : null, $adminLogin, '10.27.2.'.($runtime === 'preview' ? '1' : '2'));
    $assert($response->getStatusCode() === 404, $runtime.' runtime did not fail closed');
    $assertNoAuth($inspect($kernel, $cookieName, $cookie));
}
$app['config']->set('oneqay.runtime_class', 'ci');
$app->forgetScopedInstances();

// A valid full organizational context writes exactly the optional outlet/device facts after durable verification.
$inspection = $inspect($kernel, $cookieName, $cookie);
$csrf = $inspection['csrf_token'] ?? null;
$fullContextLogin = [
    ...$baseLogin('tenant-alpha', 'shared-user', $sharedAlphaPassword, 'organization-alpha'),
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
];
$response = $sendLogin($kernel, $cookieName, $cookie, is_string($csrf) ? $csrf : null, $fullContextLogin, '10.27.3.1');
$assert($response->getStatusCode() === 200, 'valid full organizational context login failed');
$inspection = $inspect($kernel, $cookieName, $cookie);
$auth = $inspection['auth'] ?? [];
$assert(($auth[FirstPartySessionKeys::IDENTITY] ?? null) === 'shared-user', 'full context identity mismatch');
$assert(($auth[FirstPartySessionKeys::TENANT] ?? null) === 'tenant-alpha', 'full context tenant mismatch');
$assert(($auth[FirstPartySessionKeys::ORGANIZATION] ?? null) === 'organization-alpha', 'full context organization mismatch');
$assert(($auth[FirstPartySessionKeys::OUTLET] ?? null) === 'outlet-alpha', 'verified outlet not stored');
$assert(($auth[FirstPartySessionKeys::DEVICE] ?? null) === 'device-alpha', 'verified device not stored');
$fullCsrf = $inspection['csrf_token'] ?? null;
$response = $sendLogout($kernel, $cookieName, $cookie, is_string($fullCsrf) ? $fullCsrf : null);
$assert($response->getStatusCode() === 204, 'full-context logout failed');

// Same textual identity in another tenant succeeds only with that tenant's independent credential.
$inspection = $inspect($kernel, $cookieName, $cookie);
$csrf = $inspection['csrf_token'] ?? null;
$response = $sendLogin(
    $kernel,
    $cookieName,
    $cookie,
    is_string($csrf) ? $csrf : null,
    $baseLogin('tenant-beta', 'shared-user', $sharedBetaPassword, 'organization-beta'),
    '10.27.3.2',
);
$assert($response->getStatusCode() === 200, 'tenant-beta independent credential did not authenticate');
$inspection = $inspect($kernel, $cookieName, $cookie);
$assert(($inspection['auth'][FirstPartySessionKeys::TENANT] ?? null) === 'tenant-beta', 'tenant-beta session ownership mismatch');
$betaCsrf = $inspection['csrf_token'] ?? null;
$response = $sendLogout($kernel, $cookieName, $cookie, is_string($betaCsrf) ? $betaCsrf : null);
$assert($response->getStatusCode() === 204, 'tenant-beta logout failed');

// Correct authentication without policy authority remains denied by the existing Sprint 21/22/25 authorization boundary.
$inspection = $inspect($kernel, $cookieName, $cookie);
$csrf = $inspection['csrf_token'] ?? null;
$response = $sendLogin(
    $kernel,
    $cookieName,
    $cookie,
    is_string($csrf) ? $csrf : null,
    $baseLogin('tenant-alpha', 'no-authority-alpha', $noAuthorityPassword, 'organization-alpha'),
    '10.27.3.3',
);
$assert($response->getStatusCode() === 200, 'valid no-authority identity could not establish a session');
$inspection = $inspect($kernel, $cookieName, $cookie);
$noAuthorityCsrf = $inspection['csrf_token'] ?? null;
$assert(is_string($noAuthorityCsrf) && $noAuthorityCsrf !== '', 'no-authority CSRF token missing');
$response = $sendPolicyMutation($kernel, $cookieName, $cookie, $noAuthorityCsrf, 's27-no-authority');
$assert($response->getStatusCode() === 403, 'authentication incorrectly granted policy authority');
$response = $sendLogout($kernel, $cookieName, $cookie, $noAuthorityCsrf);
$assert($response->getStatusCode() === 204, 'no-authority logout failed');

$storedAdminHash = $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', 'tenant-alpha')
    ->where('identity_id', 'admin-alpha')
    ->value('password_hash');
$assert($storedAdminHash === $adminHash, 'login/logout mutated stored credential hash');
$assertContextsCleared();

$removeTree($workspace);

echo "Sprint 27 first-party session establishment regression passed.\n";

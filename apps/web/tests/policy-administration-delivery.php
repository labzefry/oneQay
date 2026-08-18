<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Tenancy\TenantContextStore;
use App\Delivery\Http\Middleware\RequirePolicyAdministrationSessionContextMiddleware;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

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
        throw new RuntimeException('Sprint 25 policy administration delivery regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s25-policy-delivery-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'policy-delivery.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.connections.s25_policy_delivery', [
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
$manager->purge('s25_policy_delivery');
$manager->setDefaultConnection('s25_policy_delivery');
$connection = $manager->connection('s25_policy_delivery');
$connection->getPdo();

$migrationNames = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
];
$actualMigrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($actualMigrations);
$assert($actualMigrations === $migrationNames, 'canonical six-migration set changed');
foreach ($migrationNames as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
$connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-device-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-target-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-no-authority-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'synthetic-target-beta'],
]);
$connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-beta'],
]);
$connection->table('oneqay_outlets')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'outlet-alpha', 'organization_id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'outlet-beta', 'organization_id' => 'organization-beta'],
]);
$connection->table('oneqay_devices')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'device-alpha', 'organization_id' => 'organization-alpha', 'outlet_id' => 'outlet-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'device-beta', 'organization_id' => 'organization-beta', 'outlet_id' => 'outlet-beta'],
]);
foreach (['synthetic-admin-alpha', 'synthetic-device-admin-alpha', 'synthetic-target-alpha', 'synthetic-no-authority-alpha'] as $identity) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => 'tenant-alpha', 'identity_id' => $identity, 'organization_id' => 'organization-alpha',
    ]);
    $connection->table('oneqay_outlet_access_grants')->insert([
        'tenant_id' => 'tenant-alpha', 'identity_id' => $identity, 'organization_id' => 'organization-alpha', 'outlet_id' => 'outlet-alpha',
    ]);
    $connection->table('oneqay_device_access_grants')->insert([
        'tenant_id' => 'tenant-alpha', 'identity_id' => $identity, 'organization_id' => 'organization-alpha', 'outlet_id' => 'outlet-alpha', 'device_id' => 'device-alpha',
    ]);
}
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-target-beta', 'organization_id' => 'organization-beta',
]);
$connection->table('oneqay_outlet_access_grants')->insert([
    'tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-target-beta', 'organization_id' => 'organization-beta', 'outlet_id' => 'outlet-beta',
]);
$connection->table('oneqay_device_access_grants')->insert([
    'tenant_id' => 'tenant-beta', 'identity_id' => 'synthetic-target-beta', 'organization_id' => 'organization-beta', 'outlet_id' => 'outlet-beta', 'device_id' => 'device-beta',
]);

$connection->table('oneqay_roles')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE],
    ['tenant_id' => 'tenant-alpha', 'id' => 'synthetic-device-control-role'],
]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id' => 'tenant-alpha', 'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE, 'permission_id' => AdministrationPermission::MANAGE],
    ['tenant_id' => 'tenant-alpha', 'role_id' => 'synthetic-device-control-role', 'permission_id' => AdministrationPermission::MANAGE],
]);
$connection->table('oneqay_tenant_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'synthetic-admin-alpha',
    'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
]);
$connection->table('oneqay_device_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'synthetic-device-admin-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
    'role_id' => 'synthetic-device-control-role',
]);

$cookieName = (string) config('session.cookie');
$assert($cookieName !== '', 'session cookie name missing');

// Establish a framework-owned first-party session and capture its encrypted session cookie.
$sessionBootstrapRequest = Request::create('/health/live', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
$sessionBootstrapResponse = $kernel->handle($sessionBootstrapRequest);
$kernel->terminate($sessionBootstrapRequest, $sessionBootstrapResponse);
$sessionCookie = null;
foreach ($sessionBootstrapResponse->headers->getCookies() as $cookie) {
    if ($cookie->getName() === $cookieName) {
        $sessionCookie = $cookie->getValue();
        break;
    }
}
$assert(is_string($sessionCookie) && $sessionCookie !== '', 'framework session cookie was not issued');
/** @var \Illuminate\Session\Store $session */
$session = $app->make('session')->driver();
$assert(is_string($session->token()) && $session->token() !== '', 'framework CSRF token missing');

$setAuthSession = static function (\Illuminate\Session\Store $session, string $identity, string $tenant = 'tenant-alpha', string $organization = 'organization-alpha', ?string $outlet = 'outlet-alpha', ?string $device = 'device-alpha'): void {
    $session->put(RequirePolicyAdministrationSessionContextMiddleware::IDENTITY_SESSION, $identity);
    $session->put(RequirePolicyAdministrationSessionContextMiddleware::TENANT_SESSION, $tenant);
    $session->put(RequirePolicyAdministrationSessionContextMiddleware::ORGANIZATION_SESSION, $organization);
    if ($outlet === null) { $session->forget(RequirePolicyAdministrationSessionContextMiddleware::OUTLET_SESSION); }
    else { $session->put(RequirePolicyAdministrationSessionContextMiddleware::OUTLET_SESSION, $outlet); }
    if ($device === null) { $session->forget(RequirePolicyAdministrationSessionContextMiddleware::DEVICE_SESSION); }
    else { $session->put(RequirePolicyAdministrationSessionContextMiddleware::DEVICE_SESSION, $device); }
    $session->save();
};
$clearAuthSession = static function (\Illuminate\Session\Store $session): void {
    $session->forget([
        RequirePolicyAdministrationSessionContextMiddleware::IDENTITY_SESSION,
        RequirePolicyAdministrationSessionContextMiddleware::TENANT_SESSION,
        RequirePolicyAdministrationSessionContextMiddleware::ORGANIZATION_SESSION,
        RequirePolicyAdministrationSessionContextMiddleware::OUTLET_SESSION,
        RequirePolicyAdministrationSessionContextMiddleware::DEVICE_SESSION,
    ]);
    $session->save();
};

$send = static function (
    Kernel $kernel,
    \Illuminate\Session\Store $session,
    string $cookieName,
    string $sessionCookie,
    array $payload,
    bool $csrf = true,
): \Symfony\Component\HttpFoundation\Response {
    if ($csrf) {
        $payload['_token'] = $session->token();
    }
    $request = Request::create(
        '/administration/policy/mutations',
        'POST',
        $payload,
        cookies: [$cookieName => $sessionCookie],
        server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CORRELATION_ID' => 'S25-Delivery_0001',
        ],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    return $response;
};

$basePayload = static fn (string $id, string $operation, string $role): array => [
    'mutation_id' => $id,
    'operation' => $operation,
    'role' => $role,
];

$setAuthSession($session, 'synthetic-admin-alpha');

// Actual web middleware CSRF proof: authenticated context alone cannot mutate without CSRF.
$beforeCsrfCount = $connection->table('oneqay_policy_mutations')->count();
$response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('csrf-missing', 'role.create', 'synthetic-csrf-role'), false);
$assert($response->getStatusCode() === 419, 'missing CSRF token was not rejected by web middleware');
$assert($connection->table('oneqay_policy_mutations')->count() === $beforeCsrfCount, 'CSRF denial wrote mutation evidence');

// Ordinary positive-control matrix.
$response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('ordinary-role-create', 'role.create', 'synthetic-operator'));
$assert($response->getStatusCode() === 200, 'ordinary role create HTTP status');
$payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($payload['outcome'] ?? null) === 'applied', 'ordinary role create outcome');
$assert(($payload['correlation_id'] ?? null) === 'S25-Delivery_0001', 'correlation id not preserved');
$assert($connection->table('oneqay_roles')->where('tenant_id', 'tenant-alpha')->where('id', 'synthetic-operator')->exists(), 'ordinary role not created');

$response = $send($kernel, $session, $cookieName, $sessionCookie, [
    ...$basePayload('ordinary-permission-grant', 'permission.grant', 'synthetic-operator'),
    'permission' => 'synthetic.resource.execute',
]);
$assert($response->getStatusCode() === 200, 'ordinary permission grant HTTP status');
$assert($connection->table('oneqay_role_permissions')->where('tenant_id', 'tenant-alpha')->where('role_id', 'synthetic-operator')->where('permission_id', 'synthetic.resource.execute')->exists(), 'ordinary permission not granted');

$response = $send($kernel, $session, $cookieName, $sessionCookie, [
    ...$basePayload('ordinary-device-assign', 'role.assign.device', 'synthetic-operator'),
    'target_identity' => 'synthetic-target-alpha',
]);
$assert($response->getStatusCode() === 200, 'ordinary device assignment HTTP status');
$assert($connection->table('oneqay_device_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-target-alpha')->where('role_id', 'synthetic-operator')->exists(), 'ordinary device assignment missing');

$response = $send($kernel, $session, $cookieName, $sessionCookie, [
    ...$basePayload('ordinary-device-revoke', 'role.revoke.device', 'synthetic-operator'),
    'target_identity' => 'synthetic-target-alpha',
]);
$assert($response->getStatusCode() === 200, 'ordinary device revocation HTTP status');
$assert($connection->table('oneqay_device_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-target-alpha')->where('role_id', 'synthetic-operator')->doesntExist(), 'ordinary device revocation left assignment');

// Exact replay remains deterministic; conflicting replay remains rejected.
$response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('ordinary-role-create', 'role.create', 'synthetic-operator'));
$assert($response->getStatusCode() === 200, 'exact replay HTTP status');
$payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($payload['outcome'] ?? null) === 'applied', 'exact replay did not return prior outcome');
$response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('ordinary-role-create', 'role.create', 'synthetic-other-role'));
$assert($response->getStatusCode() === 409, 'conflicting replay not rejected');

// Strict payload vocabulary: actor/tenant/scope authority cannot come from request data.
$response = $send($kernel, $session, $cookieName, $sessionCookie, [
    ...$basePayload('payload-authority-attempt', 'role.create', 'synthetic-payload-role'),
    'tenant_id' => 'tenant-beta',
    'actor_identity' => 'synthetic-target-beta',
]);
$assert($response->getStatusCode() === 422, 'request-supplied authority fields were accepted');
$assert($connection->table('oneqay_roles')->where('tenant_id', 'tenant-beta')->where('id', 'synthetic-payload-role')->doesntExist(), 'request payload crossed tenant authority');

// Protected control remains unreachable through ordinary Sprint 22 delivery.
$response = $send($kernel, $session, $cookieName, $sessionCookie, [
    ...$basePayload('protected-permission-attempt', 'permission.grant', 'synthetic-operator'),
    'permission' => AdministrationPermission::MANAGE,
]);
$assert($response->getStatusCode() === 403, 'protected control permission mutation was accepted');
$response = $send($kernel, $session, $cookieName, $sessionCookie, [
    ...$basePayload('protected-role-attempt', 'role.assign.tenant', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE),
    'target_identity' => 'synthetic-target-alpha',
]);
$assert($response->getStatusCode() === 403, 'protected control role assignment was accepted');

// Sprint 24 and unknown operation strings are outside the closed Sprint 22 vocabulary.
foreach (['control.administrator.delegate', 'synthetic.freeform.operation'] as $operation) {
    $response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('invalid-operation-'.substr(hash('sha256', $operation), 0, 10), $operation, 'synthetic-operator'));
    $assert($response->getStatusCode() === 422, 'non-Sprint22 operation was accepted: '.$operation);
}

// Foreign-tenant targets remain denied by durable target eligibility.
$response = $send($kernel, $session, $cookieName, $sessionCookie, [
    ...$basePayload('foreign-target-attempt', 'role.assign.tenant', 'synthetic-operator'),
    'target_identity' => 'synthetic-target-beta',
]);
$assert($response->getStatusCode() === 403, 'foreign-tenant target was accepted');

// Valid member without policy administration authority is denied.
$setAuthSession($session, 'synthetic-no-authority-alpha');
$response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('no-authority-attempt', 'role.create', 'synthetic-denied-role'));
$assert($response->getStatusCode() === 403, 'actor without policy.manage was accepted');

// A device-scoped control actor cannot mutate broader tenant scope.
$setAuthSession($session, 'synthetic-device-admin-alpha');
$response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('scope-escape-attempt', 'role.create', 'synthetic-scope-escape-role'));
$assert($response->getStatusCode() === 403, 'narrower control actor escaped to tenant scope');

// Valid framework CSRF but missing authentication context is denied by Sprint 25 middleware.
$clearAuthSession($session);
$response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('missing-session-attempt', 'role.create', 'synthetic-missing-session'));
$assert($response->getStatusCode() === 403, 'missing authenticated session context was accepted');

// Syntactically valid session values still require durable membership/relationship proof.
$setAuthSession($session, 'synthetic-admin-alpha', 'tenant-beta', 'organization-beta', 'outlet-beta', 'device-beta');
$response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('invalid-membership-attempt', 'role.create', 'synthetic-invalid-membership'));
$assert($response->getStatusCode() === 403, 'session context without durable tenant membership was accepted');

// Preview and Production runtime classes have no active Sprint 25 delivery surface.
$setAuthSession($session, 'synthetic-admin-alpha');
foreach (['preview', 'production'] as $runtime) {
    $app['config']->set('oneqay.runtime_class', $runtime);
    $response = $send($kernel, $session, $cookieName, $sessionCookie, $basePayload('runtime-denied-'.$runtime, 'role.create', 'synthetic-runtime-'.$runtime));
    $assert($response->getStatusCode() === 404, 'Sprint 25 route active in '.$runtime.' runtime');
}
$app['config']->set('oneqay.runtime_class', 'ci');

// Denied attempts must not create journal evidence.
foreach ([
    'csrf-missing', 'payload-authority-attempt', 'protected-permission-attempt', 'protected-role-attempt',
    'foreign-target-attempt', 'no-authority-attempt', 'scope-escape-attempt', 'missing-session-attempt',
    'invalid-membership-attempt', 'runtime-denied-preview', 'runtime-denied-production',
] as $mutationId) {
    $assert($connection->table('oneqay_policy_mutations')->where('mutation_id', $mutationId)->doesntExist(), 'denied attempt wrote journal evidence: '.$mutationId);
}

// Request-scoped verified contexts must be cleared after the request lifecycle.
$assert($app->make(TenantContextStore::class)->current() === null, 'tenant request context leaked after delivery');
$assert($app->make(OrganizationalContextStore::class)->current() === null, 'organizational request context leaked after delivery');

// Delivery-source preservation: no protected-control/bootstrap dependencies and no direct database mutation mechanics.
$controllerSource = (string) file_get_contents(__DIR__.'/../app/Delivery/Http/Authorization/PolicyAdministrationController.php');
$middlewareSource = (string) file_get_contents(__DIR__.'/../app/Delivery/Http/Middleware/RequirePolicyAdministrationSessionContextMiddleware.php');
$serviceSource = (string) file_get_contents(__DIR__.'/../app/Application/Authorization/PolicyAdministrationDeliveryService.php');
$commandSource = (string) file_get_contents(__DIR__.'/../app/Application/Authorization/PolicyAdministrationDeliveryCommand.php');
$routeSource = (string) file_get_contents(__DIR__.'/../routes/web.php');
foreach ([$controllerSource, $middlewareSource] as $deliverySource) {
    foreach (['InitialTenantAdministratorProvisioningService', 'ProtectedControlAdministratorLifecycleService', 'PreauthorizedInitialTenantAdministratorProvisioningAuthority', 'DB::', 'Schema::', 'new PDO', 'updateOrInsert', 'upsert'] as $forbidden) {
        $assert(! str_contains($deliverySource, $forbidden), 'delivery source contains forbidden dependency/mechanic: '.$forbidden);
    }
}
$assert(str_contains($serviceSource, 'DurablePolicyAdministrationService'), 'delivery service does not delegate to Sprint 22 service');
$assert(str_contains($commandSource, 'PolicyMutationOperation::fromString'), 'closed Sprint 22 operation validator is not used');
$assert(substr_count($routeSource, "Route::post('/administration/policy/mutations'") === 1, 'exactly one Sprint 25 POST route required');
$assert(! str_contains($routeSource, "Route::get('/administration/policy"), 'Sprint 25 GET administration route exists');
$assert(! preg_match("#technical-preview[^\n]*policy#i", $routeSource), 'Sprint 25 route mounted under Technical Preview');

$manager->disconnect('s25_policy_delivery');
$manager->purge('s25_policy_delivery');
$app['config']->set('database.connections.s25_policy_delivery', null);
@unlink($dbPath);
$removeTree($workspace);
$assert(! file_exists($workspace), 'workspace cleanup');

fwrite(STDOUT, "Sprint 25 ordinary policy administration delivery regression passed.\n");

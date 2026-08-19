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
    '0000_00_00_000007_create_identity_password_credentials.php',
    '0000_00_00_000008_create_initial_password_enrollments.php',
    '0000_00_00_000009_create_identity_totp_factors.php',
];
$actualMigrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($actualMigrations);
$assert($actualMigrations === $migrationNames, 'canonical nine-migration set through Sprint 30 changed');
foreach ($migrationNames as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}
$assert($connection->getSchemaBuilder()->hasTable('oneqay_identity_password_credentials'), 'Sprint 26 credential table missing during Sprint 25 delivery preservation');
$assert($connection->getSchemaBuilder()->hasTable('oneqay_initial_password_enrollments'), 'Sprint 28 enrollment table missing during Sprint 25 delivery preservation');

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

// Test-only server-side session establishment emulates an already-authenticated first-party flow.
$seedState = [
    'identity' => null,
    'tenant' => null,
    'organization' => null,
    'outlet' => null,
    'device' => null,
];
$app['router']->get('/__s25/session-seed', function (Request $request) use (&$seedState) {
    $keys = [
        RequirePolicyAdministrationSessionContextMiddleware::IDENTITY_SESSION => $seedState['identity'],
        RequirePolicyAdministrationSessionContextMiddleware::TENANT_SESSION => $seedState['tenant'],
        RequirePolicyAdministrationSessionContextMiddleware::ORGANIZATION_SESSION => $seedState['organization'],
        RequirePolicyAdministrationSessionContextMiddleware::OUTLET_SESSION => $seedState['outlet'],
        RequirePolicyAdministrationSessionContextMiddleware::DEVICE_SESSION => $seedState['device'],
    ];
    foreach ($keys as $key => $value) {
        if ($value === null) {
            $request->session()->forget($key);
        } else {
            $request->session()->put($key, $value);
        }
    }
    return response()->json(['csrf_token' => $request->session()->token()]);
})->middleware('web');

$cookie = null;
$csrfToken = null;
$refreshCookie = static function (\Symfony\Component\HttpFoundation\Response $response, string $cookieName, ?string &$cookie): void {
    foreach ($response->headers->getCookies() as $responseCookie) {
        if ($responseCookie->getName() === $cookieName) {
            $cookie = $responseCookie->getValue();
        }
    }
};
$seedSession = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    ?string &$csrfToken,
    array &$seedState,
    ?string $identity,
    ?string $tenant = 'tenant-alpha',
    ?string $organization = 'organization-alpha',
    ?string $outlet = 'outlet-alpha',
    ?string $device = 'device-alpha',
) use ($refreshCookie, $assert): void {
    $seedState = compact('identity', 'tenant', 'organization', 'outlet', 'device');
    $request = Request::create(
        '/__s25/session-seed',
        'GET',
        cookies: $cookie === null ? [] : [$cookieName => $cookie],
        server: ['HTTP_ACCEPT' => 'application/json'],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $assert($response->getStatusCode() === 200, 'test-only server session establishment failed');
    $refreshCookie($response, $cookieName, $cookie);
    $decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $csrfToken = $decoded['csrf_token'] ?? null;
    $assert(is_string($cookie) && $cookie !== '', 'framework session cookie was not issued');
    $assert(is_string($csrfToken) && $csrfToken !== '', 'framework CSRF token missing');
};
$send = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    ?string $csrfToken,
    array $payload,
    bool $includeCsrf = true,
) use ($refreshCookie): \Symfony\Component\HttpFoundation\Response {
    if ($includeCsrf && $csrfToken !== null) {
        $payload['_token'] = $csrfToken;
    }
    $request = Request::create(
        '/administration/policy/mutations',
        'POST',
        $payload,
        cookies: $cookie === null ? [] : [$cookieName => $cookie],
        server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CORRELATION_ID' => 'S25-Delivery_0001',
        ],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $refreshCookie($response, $cookieName, $cookie);
    return $response;
};
$basePayload = static fn (string $id, string $operation, string $role): array => [
    'mutation_id' => $id,
    'operation' => $operation,
    'role' => $role,
];

$seedSession($kernel, $cookieName, $cookie, $csrfToken, $seedState, 'synthetic-admin-alpha');

// Actual web middleware CSRF proof: authenticated context alone cannot mutate without CSRF.
$beforeCsrfCount = $connection->table('oneqay_policy_mutations')->count();
$response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload('csrf-missing', 'role.create', 'synthetic-csrf-role'), false);
$assert($response->getStatusCode() === 419, 'missing CSRF token was not rejected by web middleware');
$assert($connection->table('oneqay_policy_mutations')->count() === $beforeCsrfCount, 'CSRF denial wrote mutation evidence');

// Ordinary positive-control matrix.
$response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload('ordinary-role-create', 'role.create', 'synthetic-operator'));
$assert($response->getStatusCode() === 200, 'ordinary role create HTTP status');
$decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($decoded['outcome'] ?? null) === 'applied', 'ordinary role create outcome');
$assert(($decoded['correlation_id'] ?? null) === 'S25-Delivery_0001', 'correlation id not preserved');
$assert($connection->table('oneqay_roles')->where('tenant_id', 'tenant-alpha')->where('id', 'synthetic-operator')->exists(), 'ordinary role not created');

$response = $send($kernel, $cookieName, $cookie, $csrfToken, [
    ...$basePayload('ordinary-permission-grant', 'permission.grant', 'synthetic-operator'),
    'permission' => 'synthetic.resource.execute',
]);
$assert($response->getStatusCode() === 200, 'ordinary permission grant HTTP status');
$assert($connection->table('oneqay_role_permissions')->where('tenant_id', 'tenant-alpha')->where('role_id', 'synthetic-operator')->where('permission_id', 'synthetic.resource.execute')->exists(), 'ordinary permission not granted');

$response = $send($kernel, $cookieName, $cookie, $csrfToken, [
    ...$basePayload('ordinary-device-assign', 'role.assign.device', 'synthetic-operator'),
    'target_identity' => 'synthetic-target-alpha',
]);
$assert($response->getStatusCode() === 200, 'ordinary device assignment HTTP status');
$assert($connection->table('oneqay_device_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-target-alpha')->where('role_id', 'synthetic-operator')->exists(), 'ordinary device assignment missing');

$response = $send($kernel, $cookieName, $cookie, $csrfToken, [
    ...$basePayload('ordinary-device-revoke', 'role.revoke.device', 'synthetic-operator'),
    'target_identity' => 'synthetic-target-alpha',
]);
$assert($response->getStatusCode() === 200, 'ordinary device revocation HTTP status');
$assert($connection->table('oneqay_device_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'synthetic-target-alpha')->where('role_id', 'synthetic-operator')->doesntExist(), 'ordinary device revocation left assignment');

// Exact replay remains deterministic; conflicting replay remains rejected.
$response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload('ordinary-role-create', 'role.create', 'synthetic-operator'));
$assert($response->getStatusCode() === 200, 'exact replay HTTP status');
$decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($decoded['outcome'] ?? null) === 'applied', 'exact replay did not return prior outcome');
$response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload('ordinary-role-create', 'role.create', 'synthetic-other-role'));
$assert($response->getStatusCode() === 409, 'conflicting replay not rejected');

// Strict payload vocabulary: actor/tenant/scope authority cannot come from request data.
$response = $send($kernel, $cookieName, $cookie, $csrfToken, [
    ...$basePayload('payload-authority-attempt', 'role.create', 'synthetic-payload-role'),
    'tenant_id' => 'tenant-beta',
    'actor_identity' => 'synthetic-target-beta',
]);
$assert($response->getStatusCode() === 422, 'request-supplied authority fields were accepted');
$assert($connection->table('oneqay_roles')->where('tenant_id', 'tenant-beta')->where('id', 'synthetic-payload-role')->doesntExist(), 'request payload crossed tenant authority');

// Protected control remains unreachable through ordinary Sprint 22 delivery.
$response = $send($kernel, $cookieName, $cookie, $csrfToken, [
    ...$basePayload('protected-permission-attempt', 'permission.grant', 'synthetic-operator'),
    'permission' => AdministrationPermission::MANAGE,
]);
$assert($response->getStatusCode() === 403, 'protected control permission mutation was accepted');
$response = $send($kernel, $cookieName, $cookie, $csrfToken, [
    ...$basePayload('protected-role-attempt', 'role.assign.tenant', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE),
    'target_identity' => 'synthetic-target-alpha',
]);
$assert($response->getStatusCode() === 403, 'protected control role assignment was accepted');

// Sprint 24 and unknown operation strings are outside the closed Sprint 22 vocabulary.
$invalidOperationIds = [];
foreach (['control.administrator.delegate', 'synthetic.freeform.operation'] as $operation) {
    $id = 'invalid-operation-'.substr(hash('sha256', $operation), 0, 10);
    $invalidOperationIds[] = $id;
    $response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload($id, $operation, 'synthetic-operator'));
    $assert($response->getStatusCode() === 422, 'non-Sprint22 operation was accepted: '.$operation);
}

// Foreign-tenant targets remain denied by durable target eligibility.
$response = $send($kernel, $cookieName, $cookie, $csrfToken, [
    ...$basePayload('foreign-target-attempt', 'role.assign.tenant', 'synthetic-operator'),
    'target_identity' => 'synthetic-target-beta',
]);
$assert($response->getStatusCode() === 403, 'foreign-tenant target was accepted');

// Valid member without policy administration authority is denied.
$seedSession($kernel, $cookieName, $cookie, $csrfToken, $seedState, 'synthetic-no-authority-alpha');
$response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload('no-authority-attempt', 'role.create', 'synthetic-denied-role'));
$assert($response->getStatusCode() === 403, 'actor without policy.manage was accepted');

// A device-scoped control actor cannot mutate broader tenant scope.
$seedSession($kernel, $cookieName, $cookie, $csrfToken, $seedState, 'synthetic-device-admin-alpha');
$response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload('scope-escape-attempt', 'role.create', 'synthetic-scope-escape-role'));
$assert($response->getStatusCode() === 403, 'narrower control actor escaped to tenant scope');

// Valid framework CSRF but missing authentication context is denied by Sprint 25 middleware.
$seedSession($kernel, $cookieName, $cookie, $csrfToken, $seedState, null, null, null, null, null);
$response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload('missing-session-attempt', 'role.create', 'synthetic-missing-session'));
$assert($response->getStatusCode() === 403, 'missing authenticated session context was accepted');

// Syntactically valid session values still require durable membership/relationship proof.
$seedSession($kernel, $cookieName, $cookie, $csrfToken, $seedState, 'synthetic-admin-alpha', 'tenant-beta', 'organization-beta', 'outlet-beta', 'device-beta');
$response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload('invalid-membership-attempt', 'role.create', 'synthetic-invalid-membership'));
$assert($response->getStatusCode() === 403, 'session context without durable tenant membership was accepted');

// Preview and Production runtime classes have no active Sprint 25 delivery surface.
$seedSession($kernel, $cookieName, $cookie, $csrfToken, $seedState, 'synthetic-admin-alpha');
foreach (['preview', 'production'] as $runtime) {
    $app['config']->set('oneqay.runtime_class', $runtime);
    $response = $send($kernel, $cookieName, $cookie, $csrfToken, $basePayload('runtime-denied-'.$runtime, 'role.create', 'synthetic-runtime-'.$runtime));
    $assert($response->getStatusCode() === 404, 'Sprint 25 route active in '.$runtime.' runtime');
}
$app['config']->set('oneqay.runtime_class', 'ci');

// Denied attempts must not create journal evidence.
$deniedMutationIds = array_merge([
    'csrf-missing',
    'payload-authority-attempt',
    'protected-permission-attempt',
    'protected-role-attempt',
    'foreign-target-attempt',
    'no-authority-attempt',
    'scope-escape-attempt',
    'missing-session-attempt',
    'invalid-membership-attempt',
    'runtime-denied-preview',
    'runtime-denied-production',
], $invalidOperationIds);
foreach ($deniedMutationIds as $mutationId) {
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

fwrite(STDOUT, "Sprint 25 ordinary policy administration delivery regression passed with Sprint 28 schema preservation.\n");

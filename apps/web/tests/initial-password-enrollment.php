<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\FirstPartyIdentityCredentialVerifier;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('e', 32));
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
        throw new RuntimeException('Sprint 28 initial password enrollment regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s28-enrollment-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'enrollment.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.connections.s28_enrollment', [
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
$manager->purge('s28_enrollment');
$manager->setDefaultConnection('s28_enrollment');
$connection = $manager->connection('s28_enrollment');
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
];
$actualMigrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($actualMigrations);
$assert($actualMigrations === $migrationNames, 'canonical migration set is not exactly #1-#8');
foreach ($migrationNames as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$schema = $connection->getSchemaBuilder();
$assert($schema->hasTable('oneqay_initial_password_enrollments'), 'enrollment table missing');
foreach (['tenant_id', 'enrollment_id', 'actor_identity_id', 'target_identity_id', 'token_digest', 'issued_at_unix', 'expires_at_unix', 'consumed_at_unix', 'active_marker'] as $column) {
    $assert($schema->hasColumn('oneqay_initial_password_enrollments', $column), 'enrollment column missing: '.$column);
}
$indexRows = $connection->select("PRAGMA index_list('oneqay_initial_password_enrollments')");
$assert((bool) array_filter($indexRows, static fn ($row): bool => ($row->name ?? null) === 'uq_initial_password_enrollment_active_target' && (int) ($row->unique ?? 0) === 1), 'active enrollment unique index missing');
$foreignKeys = $connection->select("PRAGMA foreign_key_list('oneqay_initial_password_enrollments')");
$identityForeignKeyRows = array_values(array_filter($foreignKeys, static fn ($row): bool => ($row->table ?? null) === 'oneqay_identities'));
$assert(count($identityForeignKeyRows) >= 4, 'actor/target composite identity foreign keys missing');

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}

$connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'control-admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'ordinary-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'target-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'target-expiring-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'target-existing-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'shared-target'],
    ['tenant_id' => 'tenant-beta', 'id' => 'shared-target'],
    ['tenant_id' => 'tenant-beta', 'id' => 'beta-only-target'],
]);

$connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-beta'],
]);
foreach (['control-admin-alpha', 'ordinary-alpha', 'target-alpha', 'target-expiring-alpha', 'target-existing-alpha', 'shared-target'] as $identity) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'organization_id' => 'organization-alpha',
    ]);
}
$connection->table('oneqay_identity_organizations')->insert([
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'shared-target', 'organization_id' => 'organization-beta'],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'beta-only-target', 'organization_id' => 'organization-beta'],
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
    'identity_id' => 'control-admin-alpha',
    'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
]);

$adminPassword = 'Admin-Alpha-Synthetic-28!';
$ordinaryPassword = 'Ordinary-Alpha-Synthetic-28!';
$existingPassword = 'Existing-Alpha-Synthetic-28!';
foreach ([
    ['control-admin-alpha', $adminPassword],
    ['ordinary-alpha', $ordinaryPassword],
    ['target-existing-alpha', $existingPassword],
] as [$identity, $password]) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $assert(is_string($hash) && $hash !== '', 'fixture hash generation failed');
    $connection->table('oneqay_identity_password_credentials')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'password_hash' => $hash,
    ]);
}

$cookieName = (string) config('session.cookie');
$assert($cookieName !== '', 'session cookie name missing');
$app['router']->get('/__s28/session-state', function (Request $request) {
    return response()->json([
        'csrf_token' => $request->session()->token(),
        'identity' => $request->session()->get(FirstPartySessionKeys::IDENTITY),
        'tenant' => $request->session()->get(FirstPartySessionKeys::TENANT),
        'organization' => $request->session()->get(FirstPartySessionKeys::ORGANIZATION),
        'outlet' => $request->session()->get(FirstPartySessionKeys::OUTLET),
        'device' => $request->session()->get(FirstPartySessionKeys::DEVICE),
    ]);
})->middleware('web');

$refreshCookie = static function (\Symfony\Component\HttpFoundation\Response $response, string $cookieName, ?string &$cookie): void {
    foreach ($response->headers->getCookies() as $responseCookie) {
        if ($responseCookie->getName() === $cookieName) {
            $cookie = $responseCookie->getValue();
        }
    }
};
$state = static function (Kernel $kernel, string $cookieName, ?string &$cookie) use ($refreshCookie, $assert): array {
    $request = Request::create('/__s28/session-state', 'GET', cookies: $cookie === null ? [] : [$cookieName => $cookie], server: ['HTTP_ACCEPT' => 'application/json']);
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $assert($response->getStatusCode() === 200, 'test session-state route failed');
    $refreshCookie($response, $cookieName, $cookie);
    return json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
};
$post = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    string $path,
    array $payload,
    ?string $csrfToken,
    string $ip,
) use ($refreshCookie): \Symfony\Component\HttpFoundation\Response {
    if ($csrfToken !== null) {
        $payload['_token'] = $csrfToken;
    }
    $request = Request::create(
        $path,
        'POST',
        $payload,
        cookies: $cookie === null ? [] : [$cookieName => $cookie],
        server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CORRELATION_ID' => 'S28-Enrollment_0001',
            'REMOTE_ADDR' => $ip,
        ],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $refreshCookie($response, $cookieName, $cookie);
    return $response;
};
$login = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    string $identity,
    string $password,
    string $ip,
) use ($state, $post, $assert): string {
    $before = $state($kernel, $cookieName, $cookie);
    $csrf = $before['csrf_token'] ?? null;
    $assert(is_string($csrf) && $csrf !== '', 'pre-login CSRF missing');
    $response = $post($kernel, $cookieName, $cookie, '/auth/login', [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'password' => $password,
        'organization_id' => 'organization-alpha',
    ], $csrf, $ip);
    $assert($response->getStatusCode() === 200, 'fixture first-party login failed for '.$identity);
    $after = $state($kernel, $cookieName, $cookie);
    $csrf = $after['csrf_token'] ?? null;
    $assert(is_string($csrf) && $csrf !== '', 'post-login CSRF missing');
    return $csrf;
};

$adminCookie = null;
$adminCsrf = $login($kernel, $cookieName, $adminCookie, 'control-admin-alpha', $adminPassword, '10.28.0.1');
$adminState = $state($kernel, $cookieName, $adminCookie);
$assert(($adminState['identity'] ?? null) === 'control-admin-alpha', 'admin authenticated session identity missing');

// CSRF is mandatory before issuance processing.
$beforeIssueCount = $connection->table('oneqay_initial_password_enrollments')->count();
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-target-alpha',
    'target_identity_id' => 'target-alpha',
], null, '10.28.0.2');
$assert($response->getStatusCode() === 419, 'issuance without CSRF was not rejected');
$assert($connection->table('oneqay_initial_password_enrollments')->count() === $beforeIssueCount, 'CSRF denial wrote enrollment');

// Tenant-control administrator issues a token without setting the target password.
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-target-alpha',
    'target_identity_id' => 'target-alpha',
], $adminCsrf, '10.28.0.3');
$assert($response->getStatusCode() === 201, 'authorized issuance did not return 201');
$issued = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$token = $issued['enrollment_token'] ?? null;
$assert(is_string($token) && preg_match('/\A[A-Za-z0-9_-]{43}\z/', $token) === 1, 'issued token shape invalid');
$assert(($issued['target_identity_id'] ?? null) === 'target-alpha', 'issued target mismatch');
$assert(str_contains(strtolower((string) $response->headers->get('Cache-Control')), 'no-store'), 'issuance response is cacheable');
$assert(strtolower((string) $response->headers->get('Pragma')) === 'no-cache', 'issuance pragma no-cache missing');
$row = $connection->table('oneqay_initial_password_enrollments')->where('tenant_id', 'tenant-alpha')->where('enrollment_id', 'enroll-target-alpha')->first();
$assert($row !== null, 'issued enrollment row missing');
$assert(is_string($row->token_digest ?? null) && hash_equals($row->token_digest, hash('sha256', $token)), 'persisted token digest mismatch');
$assert(! hash_equals((string) $row->token_digest, $token), 'plaintext token persisted as digest');
$assert((int) ($row->active_marker ?? 0) === 1, 'issued enrollment is not active');
$serializedRows = json_encode($connection->table('oneqay_initial_password_enrollments')->get(), JSON_THROW_ON_ERROR);
$assert(! str_contains($serializedRows, $token), 'plaintext token leaked into enrollment persistence');

// Duplicate active enrollment and enrollment-id replay are denied without returning token material.
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-target-alpha-two',
    'target_identity_id' => 'target-alpha',
], $adminCsrf, '10.28.0.4');
$assert($response->getStatusCode() === 409, 'second active enrollment was not rejected');
$assert(! str_contains((string) $response->getContent(), $token), 'conflict response leaked prior token');
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-target-alpha',
    'target_identity_id' => 'shared-target',
], $adminCsrf, '10.28.0.5');
$assert($response->getStatusCode() === 409, 'enrollment identifier rebinding was not rejected');

// Self issuance, foreign-tenant targets, and existing credentials are denied.
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-self',
    'target_identity_id' => 'control-admin-alpha',
], $adminCsrf, '10.28.0.6');
$assert($response->getStatusCode() === 403, 'self enrollment issuance was not denied');
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-beta-only',
    'target_identity_id' => 'beta-only-target',
], $adminCsrf, '10.28.0.7');
$assert($response->getStatusCode() === 409, 'foreign-tenant target issuance was not denied');
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-existing',
    'target_identity_id' => 'target-existing-alpha',
], $adminCsrf, '10.28.0.8');
$assert($response->getStatusCode() === 409, 'existing credential target issuance was not denied');

// A logged-in ordinary identity cannot issue enrollments.
$ordinaryCookie = null;
$ordinaryCsrf = $login($kernel, $cookieName, $ordinaryCookie, 'ordinary-alpha', $ordinaryPassword, '10.28.0.9');
$response = $post($kernel, $cookieName, $ordinaryCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-unauthorized',
    'target_identity_id' => 'shared-target',
], $ordinaryCsrf, '10.28.0.10');
$assert($response->getStatusCode() === 403, 'non-control issuer was not denied');

// Redemption uses a separate anonymous web session and remains CSRF protected.
$redeemCookie = null;
$redeemState = $state($kernel, $cookieName, $redeemCookie);
$redeemCsrf = $redeemState['csrf_token'] ?? null;
$assert(is_string($redeemCsrf) && $redeemCsrf !== '', 'redemption CSRF missing');
$redemptionPayload = [
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-alpha',
    'enrollment_id' => 'enroll-target-alpha',
    'enrollment_token' => $token,
    'password' => 'Target Alpha Initial Password 28!',
];
$response = $post($kernel, $cookieName, $redeemCookie, '/auth/password-enrollment', $redemptionPayload, null, '10.28.1.1');
$assert($response->getStatusCode() === 419, 'redemption without CSRF was not rejected');
$assert(! $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'target-alpha')->exists(), 'CSRF-denied redemption created credential');

foreach ([
    ['wrong token', [...$redemptionPayload, 'enrollment_token' => str_repeat('A', 43)]],
    ['foreign tenant', [...$redemptionPayload, 'tenant_id' => 'tenant-beta']],
    ['wrong identity', [...$redemptionPayload, 'identity_id' => 'shared-target']],
    ['short password', [...$redemptionPayload, 'password' => 'short-pass']],
    ['unknown field', [...$redemptionPayload, 'role' => 'platform-superadmin']],
] as $index => [$case, $payload]) {
    $response = $post($kernel, $cookieName, $redeemCookie, '/auth/password-enrollment', $payload, $redeemCsrf, '10.28.1.'.($index + 2));
    $assert($response->getStatusCode() === 401, $case.' did not collapse to generic 401');
    $body = (string) $response->getContent();
    $assert(str_contains($body, 'INITIAL_PASSWORD_ENROLLMENT_FAILED'), $case.' generic error code missing');
    $assert(! str_contains($body, $token), $case.' response leaked enrollment token');
    $assert(! $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'target-alpha')->exists(), $case.' created credential');
}

// Successful redemption creates exactly one credential and consumes the exact enrollment.
$response = $post($kernel, $cookieName, $redeemCookie, '/auth/password-enrollment', $redemptionPayload, $redeemCsrf, '10.28.1.20');
$assert($response->getStatusCode() === 200, 'valid redemption did not succeed');
$redeemed = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($redeemed['outcome'] ?? null) === 'applied', 'valid redemption outcome mismatch');
$credentialHash = $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'target-alpha')->value('password_hash');
$assert(is_string($credentialHash) && $credentialHash !== '', 'redeemed credential hash missing');
$assert(! hash_equals($credentialHash, $redemptionPayload['password']), 'plaintext password persisted');
$assert(password_verify($redemptionPayload['password'], $credentialHash), 'redeemed password does not verify');
$row = $connection->table('oneqay_initial_password_enrollments')->where('tenant_id', 'tenant-alpha')->where('enrollment_id', 'enroll-target-alpha')->first();
$assert($row !== null && is_numeric($row->consumed_at_unix ?? null) && (int) $row->consumed_at_unix > 0, 'enrollment was not consumed');
$assert(($row->active_marker ?? null) === null, 'consumed enrollment remained active');

// Redemption itself does not establish any first-party authenticated session facts.
$afterRedeemState = $state($kernel, $cookieName, $redeemCookie);
foreach (['identity', 'tenant', 'organization', 'outlet', 'device'] as $key) {
    $assert(($afterRedeemState[$key] ?? null) === null, 'redemption established authenticated session fact: '.$key);
}

// Exact redemption replay is deterministic and never replaces the credential, even with a different valid password.
$replayPayload = [...$redemptionPayload, 'password' => 'Different Replay Password 28!'];
$response = $post($kernel, $cookieName, $redeemCookie, '/auth/password-enrollment', $replayPayload, $redeemCsrf, '10.28.1.21');
$assert($response->getStatusCode() === 200, 'exact token replay was not deterministic');
$hashAfterReplay = $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'target-alpha')->value('password_hash');
$assert(is_string($hashAfterReplay) && hash_equals($credentialHash, $hashAfterReplay), 'redemption replay replaced credential hash');
$assert(! password_verify($replayPayload['password'], $hashAfterReplay), 'redemption replay changed password');

// Sprint 26 verifier accepts the newly enrolled password and rejects alternatives.
/** @var FirstPartyIdentityCredentialVerifier $verifier */
$verifier = $app->make(FirstPartyIdentityCredentialVerifier::class);
$tenantAlpha = TenantId::fromString('tenant-alpha');
$targetAlpha = PlatformIdentityId::fromString('target-alpha');
$assert($verifier->verify($tenantAlpha, $targetAlpha, $redemptionPayload['password']), 'Sprint 26 verifier rejected newly enrolled password');
$assert(! $verifier->verify($tenantAlpha, $targetAlpha, 'Wrong Newly Enrolled Password 28!'), 'Sprint 26 verifier accepted wrong password');

// The new credential can establish a Sprint 27 session only through the normal login route.
$targetLoginCookie = null;
$targetLoginCsrf = $state($kernel, $cookieName, $targetLoginCookie)['csrf_token'] ?? null;
$assert(is_string($targetLoginCsrf) && $targetLoginCsrf !== '', 'target login CSRF missing');
$response = $post($kernel, $cookieName, $targetLoginCookie, '/auth/login', [
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-alpha',
    'password' => $redemptionPayload['password'],
    'organization_id' => 'organization-alpha',
], $targetLoginCsrf, '10.28.2.1');
$assert($response->getStatusCode() === 200, 'Sprint 27 login rejected newly enrolled credential');
$targetSession = $state($kernel, $cookieName, $targetLoginCookie);
$assert(($targetSession['identity'] ?? null) === 'target-alpha', 'Sprint 27 login did not establish target identity');
$assert(($targetSession['tenant'] ?? null) === 'tenant-alpha', 'Sprint 27 login did not establish target tenant');

// Expired enrollments cannot redeem, but stale active state is retired during a later authorized issuance.
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-expiring-one',
    'target_identity_id' => 'target-expiring-alpha',
], $adminCsrf, '10.28.3.1');
$assert($response->getStatusCode() === 201, 'expiring enrollment issuance failed');
$expiring = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$expiringToken = $expiring['enrollment_token'] ?? null;
$assert(is_string($expiringToken), 'expiring token missing');
$connection->table('oneqay_initial_password_enrollments')->where('tenant_id', 'tenant-alpha')->where('enrollment_id', 'enroll-expiring-one')->update(['expires_at_unix' => time() - 1]);
$expiredCookie = null;
$expiredCsrf = $state($kernel, $cookieName, $expiredCookie)['csrf_token'] ?? null;
$assert(is_string($expiredCsrf), 'expired redemption CSRF missing');
$response = $post($kernel, $cookieName, $expiredCookie, '/auth/password-enrollment', [
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-expiring-alpha',
    'enrollment_id' => 'enroll-expiring-one',
    'enrollment_token' => $expiringToken,
    'password' => 'Expired Token Password 28!',
], $expiredCsrf, '10.28.3.2');
$assert($response->getStatusCode() === 401, 'expired token was accepted');
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-expiring-two',
    'target_identity_id' => 'target-expiring-alpha',
], $adminCsrf, '10.28.3.3');
$assert($response->getStatusCode() === 201, 'expired active enrollment was not safely superseded');
$oldActive = $connection->table('oneqay_initial_password_enrollments')->where('tenant_id', 'tenant-alpha')->where('enrollment_id', 'enroll-expiring-one')->value('active_marker');
$assert($oldActive === null, 'expired active marker was not retired');

// Password handling remains opaque: spaces are preserved rather than trimmed.
$response = $post($kernel, $cookieName, $adminCookie, '/administration/identity/password-enrollments', [
    'enrollment_id' => 'enroll-shared-alpha',
    'target_identity_id' => 'shared-target',
], $adminCsrf, '10.28.4.1');
$assert($response->getStatusCode() === 201, 'shared-target issuance failed');
$sharedIssue = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$sharedToken = $sharedIssue['enrollment_token'] ?? null;
$assert(is_string($sharedToken), 'shared-target token missing');
$sharedCookie = null;
$sharedCsrf = $state($kernel, $cookieName, $sharedCookie)['csrf_token'] ?? null;
$spacedPassword = '  spaced-password-28  ';
$response = $post($kernel, $cookieName, $sharedCookie, '/auth/password-enrollment', [
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'shared-target',
    'enrollment_id' => 'enroll-shared-alpha',
    'enrollment_token' => $sharedToken,
    'password' => $spacedPassword,
], $sharedCsrf, '10.28.4.2');
$assert($response->getStatusCode() === 200, 'spaced password redemption failed');
$sharedHash = $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'shared-target')->value('password_hash');
$assert(is_string($sharedHash) && password_verify($spacedPassword, $sharedHash), 'spaced password was not preserved exactly');
$assert(! password_verify(trim($spacedPassword), $sharedHash), 'password was trimmed before hashing');
$assert(! $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-beta')->where('identity_id', 'shared-target')->exists(), 'same textual identity cross-tenant credential was created');

// Sensitive material never appears in enrollment persistence.
$allEnrollmentPersistence = json_encode($connection->table('oneqay_initial_password_enrollments')->get(), JSON_THROW_ON_ERROR);
foreach ([$token, $expiringToken, $sharedToken, $redemptionPayload['password'], $spacedPassword] as $sensitive) {
    $assert(! str_contains($allEnrollmentPersistence, $sensitive), 'sensitive material leaked into enrollment persistence');
}

fwrite(STDOUT, "Sprint 28 initial password enrollment regression passed.\n");

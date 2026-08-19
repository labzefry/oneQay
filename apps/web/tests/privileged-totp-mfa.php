<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\PrivilegedTotpClock;
use App\Application\Identity\PrivilegedTotpMfaRepository;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Application\Identity\PrivilegedTotpMfaViolation;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Delivery\Http\Middleware\RequirePolicyAdministrationSessionContextMiddleware;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use OTPHP\TOTP;
use Psr\Clock\ClockInterface;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('m', 32));
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'local',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint 30 privileged TOTP MFA regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s30-totp-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'totp.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.connections.s30_totp', [
    'driver' => 'sqlite',
    'url' => null,
    'database' => $dbPath,
    'prefix' => '',
    'foreign_key_constraints' => true,
    'busy_timeout' => 5000,
    'journal_mode' => 'WAL',
    'synchronous' => 'NORMAL',
]);
$app['config']->set('database.oneqay_persistence_enabled', true);
$app['config']->set('oneqay.runtime_class', 'ci');
$app['config']->set('oneqay.privileged_totp_mfa.enabled', true);

/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s30_totp');
$manager->setDefaultConnection('s30_totp');
$connection = $manager->connection('s30_totp');
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
$assert($actualMigrations === $migrationNames, 'canonical migration set is not exactly #1-#9');
foreach ($migrationNames as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}
$schema = $connection->getSchemaBuilder();
$assert($schema->hasTable('oneqay_identity_totp_factors'), 'Sprint 30 factor table missing');
$assert($schema->getColumnListing('oneqay_identity_totp_factors') === [
    'tenant_id',
    'identity_id',
    'secret_ciphertext',
    'created_at_unix',
    'confirmed_at_unix',
    'last_accepted_time_step',
], 'Sprint 30 factor table columns changed');

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
$connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'admin-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'ordinary-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'admin-beta'],
]);
$connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-beta'],
]);
foreach (['admin-alpha', 'ordinary-alpha'] as $identity) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'organization_id' => 'organization-alpha',
    ]);
}
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-beta',
    'identity_id' => 'admin-beta',
    'organization_id' => 'organization-beta',
]);

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_roles')->insert([
        'tenant_id' => $tenant,
        'id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
    ]);
    $connection->table('oneqay_role_permissions')->insert([
        'tenant_id' => $tenant,
        'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
        'permission_id' => AdministrationPermission::MANAGE,
    ]);
}
$connection->table('oneqay_tenant_role_assignments')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'admin-alpha', 'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'admin-beta', 'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE],
]);

$adminPassword = 'Sprint30 Admin Alpha / synthetic only';
$ordinaryPassword = 'Sprint30 Ordinary Alpha / synthetic only';
$betaPassword = 'Sprint30 Admin Beta / synthetic only';
$connection->table('oneqay_identity_password_credentials')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'admin-alpha', 'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT)],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'ordinary-alpha', 'password_hash' => password_hash($ordinaryPassword, PASSWORD_DEFAULT)],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'admin-beta', 'password_hash' => password_hash($betaPassword, PASSWORD_DEFAULT)],
]);

$fakeClock = new class(1_800_000_000) implements PrivilegedTotpClock {
    public function __construct(public int $now) {}
    public function nowUnix(): int { return $this->now; }
};
$app->instance(PrivilegedTotpClock::class, $fakeClock);

$cookieName = (string) config('session.cookie');
$assert($cookieName !== '', 'session cookie name missing');

$app['router']->get('/__s30/session-inspect', static function (Request $request) {
    $full = [];
    foreach (FirstPartySessionKeys::all() as $key) {
        $full[$key] = $request->session()->get($key);
    }
    $pending = [];
    foreach (FirstPartySessionKeys::pending() as $key) {
        $pending[$key] = $request->session()->get($key);
    }

    return response()->json([
        'csrf_token' => $request->session()->token(),
        'full' => $full,
        'pending' => $pending,
        'mfa_verified_at' => $request->session()->get(FirstPartySessionKeys::MFA_VERIFIED_AT),
    ]);
})->middleware('web');

$app['router']->get('/__s30/policy-probe', static fn () => response()->noContent())
    ->middleware(['web', RequirePolicyAdministrationSessionContextMiddleware::class]);

$app['router']->post('/__s30/drop-mfa-evidence', static function (Request $request) {
    $request->session()->forget(FirstPartySessionKeys::MFA_VERIFIED_AT);
    return response()->noContent();
})->middleware('web');

$refreshCookie = static function (\Symfony\Component\HttpFoundation\Response $response, string $cookieName, ?string &$cookie): void {
    foreach ($response->headers->getCookies() as $responseCookie) {
        if ($responseCookie->getName() === $cookieName) {
            $cookie = $responseCookie->getValue();
        }
    }
};

$send = static function (
    Kernel $kernel,
    string $method,
    string $uri,
    string $cookieName,
    ?string &$cookie,
    array $payload = [],
    ?string $csrfToken = null,
    string $ip = '198.51.100.30',
) use ($refreshCookie): \Symfony\Component\HttpFoundation\Response {
    if ($method === 'POST' && $csrfToken !== null) {
        $payload['_token'] = $csrfToken;
    }
    $request = Request::create(
        $uri,
        $method,
        $payload,
        cookies: $cookie === null ? [] : [$cookieName => $cookie],
        server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CORRELATION_ID' => 'S30-TOTP_0001',
            'REMOTE_ADDR' => $ip,
        ],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $refreshCookie($response, $cookieName, $cookie);

    return $response;
};

$inspect = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
) use ($send, $assert): array {
    $response = $send($kernel, 'GET', '/__s30/session-inspect', $cookieName, $cookie);
    $assert($response->getStatusCode() === 200, 'session inspection failed');
    $decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $assert(is_array($decoded), 'session inspection payload invalid');
    return $decoded;
};

$loginPayload = static fn (string $tenant, string $identity, string $password, string $organization): array => [
    'tenant_id' => $tenant,
    'identity_id' => $identity,
    'password' => $password,
    'organization_id' => $organization,
];

$totpCode = static function (string $secret, int $timestamp): string {
    $clock = new class implements ClockInterface {
        public function now(): DateTimeImmutable { return new DateTimeImmutable('@0'); }
    };
    return TOTP::create(
        secret: $secret,
        period: 30,
        digest: 'sha1',
        digits: 6,
        epoch: 0,
        clock: $clock,
    )->at($timestamp);
};

$cookie = null;
$state = $inspect($kernel, $cookieName, $cookie);
$csrf = $state['csrf_token'] ?? null;
$assert(is_string($csrf) && $csrf !== '', 'initial CSRF token missing');

// Password success for the protected principal must establish pending enrollment only.
$response = $send(
    $kernel,
    'POST',
    '/auth/login',
    $cookieName,
    $cookie,
    $loginPayload('tenant-alpha', 'admin-alpha', $adminPassword, 'organization-alpha'),
    $csrf,
);
$assert($response->getStatusCode() === 202, 'protected password login did not require MFA enrollment');
$body = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($body['code'] ?? null) === 'MFA_ENROLLMENT_REQUIRED', 'protected login enrollment disposition changed');

$state = $inspect($kernel, $cookieName, $cookie);
foreach ($state['full'] as $value) {
    $assert($value === null, 'pending enrollment leaked a full authentication key');
}
$assert(($state['pending'][FirstPartySessionKeys::PENDING_IDENTITY] ?? null) === 'admin-alpha', 'pending enrollment identity mismatch');
$assert(($state['pending'][FirstPartySessionKeys::PENDING_TENANT] ?? null) === 'tenant-alpha', 'pending enrollment tenant mismatch');
$assert(($state['pending'][FirstPartySessionKeys::PENDING_MFA_STATE] ?? null) === FirstPartySessionKeys::MFA_ENROLLMENT_REQUIRED, 'pending enrollment state mismatch');
$assert(($state['mfa_verified_at'] ?? null) === null, 'pending enrollment leaked MFA evidence');
$csrf = $state['csrf_token'];

// Unknown fields and non-exact codes fail closed while keeping the pending factor unconfirmed.
$invalid = $send($kernel, 'POST', '/auth/mfa/totp/enrollment/confirm', $cookieName, $cookie, ['code' => '000000', 'unexpected' => 'x'], $csrf);
$assert($invalid->getStatusCode() === 401, 'enrollment confirmation accepted an unknown field');

$response = $send($kernel, 'POST', '/auth/mfa/totp/enrollment/start', $cookieName, $cookie, [], $csrf);
$assert($response->getStatusCode() === 200, 'enrollment start failed');
$assert(str_contains((string) $response->headers->get('Cache-Control'), 'no-store'), 'enrollment response is cacheable');
$issued = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$secret = $issued['secret'] ?? null;
$uri = $issued['provisioning_uri'] ?? null;
$assert(is_string($secret) && preg_match('/\A[A-Z2-7]{32}\z/D', $secret) === 1, 'provider secret profile changed');
$assert(is_string($uri) && str_starts_with($uri, 'otpauth://totp/'), 'provisioning URI contract changed');
$assert(str_contains($uri, 'issuer=oneQay'), 'provisioning URI issuer missing');

$factor = $connection->table('oneqay_identity_totp_factors')
    ->where('tenant_id', 'tenant-alpha')
    ->where('identity_id', 'admin-alpha')
    ->first();
$assert(is_object($factor), 'pending factor row missing');
$ciphertext = $factor->secret_ciphertext ?? null;
$assert(is_string($ciphertext) && $ciphertext !== '', 'factor ciphertext missing');
$assert(! hash_equals($ciphertext, $secret), 'factor secret stored as plaintext');
$assert(! str_contains($ciphertext, $secret), 'factor ciphertext contains plaintext secret');
$assert(($factor->confirmed_at_unix ?? null) === null, 'pending factor unexpectedly confirmed');
$assert(($factor->last_accepted_time_step ?? null) === null, 'pending factor unexpectedly consumed a TOTP step');

// Repeated start must reuse the same pending secret instead of replacing it.
$response = $send($kernel, 'POST', '/auth/mfa/totp/enrollment/start', $cookieName, $cookie, [], $csrf);
$issuedAgain = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert($response->getStatusCode() === 200, 'repeated enrollment start failed');
$assert(is_string($issuedAgain['secret'] ?? null) && hash_equals($secret, $issuedAgain['secret']), 'pending enrollment secret was replaced');

$code = $totpCode($secret, $fakeClock->now);
$whitespaceCode = ' '.$code;
$invalid = $send($kernel, 'POST', '/auth/mfa/totp/enrollment/confirm', $cookieName, $cookie, ['code' => $whitespaceCode], $csrf);
$assert($invalid->getStatusCode() === 401, 'enrollment confirmation normalized TOTP input');

$response = $send($kernel, 'POST', '/auth/mfa/totp/enrollment/confirm', $cookieName, $cookie, ['code' => $code], $csrf);
$assert($response->getStatusCode() === 204, 'enrollment confirmation failed');
$state = $inspect($kernel, $cookieName, $cookie);
foreach ($state['full'] as $value) {
    $assert($value === null, 'enrollment confirmation silently authenticated the principal');
}
foreach ($state['pending'] as $value) {
    $assert($value === null, 'enrollment confirmation retained pending authentication state');
}
$assert(($state['mfa_verified_at'] ?? null) === null, 'enrollment confirmation silently created MFA evidence');

$factor = $connection->table('oneqay_identity_totp_factors')
    ->where('tenant_id', 'tenant-alpha')
    ->where('identity_id', 'admin-alpha')
    ->first();
$assert(is_object($factor), 'confirmed factor row missing');
$assert((int) ($factor->confirmed_at_unix ?? 0) === $fakeClock->now, 'confirmed timestamp changed');
$assert((int) ($factor->last_accepted_time_step ?? -1) === intdiv($fakeClock->now, 30), 'confirmation did not consume matched time step');

// Fresh password login is mandatory after enrollment and creates challenge-only state.
$csrf = $state['csrf_token'];
$response = $send(
    $kernel,
    'POST',
    '/auth/login',
    $cookieName,
    $cookie,
    $loginPayload('tenant-alpha', 'admin-alpha', $adminPassword, 'organization-alpha'),
    $csrf,
);
$assert($response->getStatusCode() === 202, 'confirmed protected login did not require challenge');
$body = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($body['code'] ?? null) === 'MFA_CHALLENGE_REQUIRED', 'protected challenge disposition changed');
$state = $inspect($kernel, $cookieName, $cookie);
$assert(($state['pending'][FirstPartySessionKeys::PENDING_MFA_STATE] ?? null) === FirstPartySessionKeys::MFA_CHALLENGE_REQUIRED, 'pending challenge state mismatch');
foreach ($state['full'] as $value) {
    $assert($value === null, 'password-only challenge state leaked full authentication');
}
$csrf = $state['csrf_token'];

// Confirmation consumed the current step; advance one period for the first challenge.
$fakeClock->now += 30;
$challengeCode = $totpCode($secret, $fakeClock->now);
$response = $send($kernel, 'POST', '/auth/mfa/totp/challenge', $cookieName, $cookie, ['code' => $challengeCode], $csrf);
$assert($response->getStatusCode() === 200, 'valid privileged TOTP challenge failed');
$state = $inspect($kernel, $cookieName, $cookie);
$assert(($state['full'][FirstPartySessionKeys::IDENTITY] ?? null) === 'admin-alpha', 'verified session identity missing');
$assert(($state['full'][FirstPartySessionKeys::TENANT] ?? null) === 'tenant-alpha', 'verified session tenant missing');
$assert(($state['full'][FirstPartySessionKeys::ORGANIZATION] ?? null) === 'organization-alpha', 'verified session organization missing');
$assert(($state['mfa_verified_at'] ?? null) === $fakeClock->now, 'verified session MFA evidence missing');
foreach ($state['pending'] as $value) {
    $assert($value === null, 'successful challenge retained pending authentication state');
}

$response = $send($kernel, 'GET', '/__s30/policy-probe', $cookieName, $cookie);
$assert($response->getStatusCode() === 204, 'MFA-verified protected session was rejected by policy middleware');

// A fresh pending challenge cannot replay the same or an older accepted step.
$csrf = $state['csrf_token'];
$response = $send(
    $kernel,
    'POST',
    '/auth/login',
    $cookieName,
    $cookie,
    $loginPayload('tenant-alpha', 'admin-alpha', $adminPassword, 'organization-alpha'),
    $csrf,
    '198.51.100.31',
);
$assert($response->getStatusCode() === 202, 'fresh protected login did not return challenge state');
$state = $inspect($kernel, $cookieName, $cookie);
$csrf = $state['csrf_token'];
$replay = $send($kernel, 'POST', '/auth/mfa/totp/challenge', $cookieName, $cookie, ['code' => $challengeCode], $csrf, '198.51.100.32');
$assert($replay->getStatusCode() === 401, 'same-step TOTP replay was accepted');
$olderCode = $totpCode($secret, $fakeClock->now - 30);
$older = $send($kernel, 'POST', '/auth/mfa/totp/challenge', $cookieName, $cookie, ['code' => $olderCode], $csrf, '198.51.100.33');
$assert($older->getStatusCode() === 401, 'older TOTP step was accepted');

$fakeClock->now += 30;
$newCode = $totpCode($secret, $fakeClock->now);
$response = $send($kernel, 'POST', '/auth/mfa/totp/challenge', $cookieName, $cookie, ['code' => $newCode], $csrf, '198.51.100.34');
$assert($response->getStatusCode() === 200, 'newer TOTP step was rejected');
$factor = $connection->table('oneqay_identity_totp_factors')
    ->where('tenant_id', 'tenant-alpha')
    ->where('identity_id', 'admin-alpha')
    ->first();
$assert((int) ($factor->last_accepted_time_step ?? -1) === intdiv($fakeClock->now, 30), 'durable replay marker did not advance monotonically');

// Full protected context without the MFA marker must fail closed while enforcement is armed.
$state = $inspect($kernel, $cookieName, $cookie);
$csrf = $state['csrf_token'];
$response = $send($kernel, 'POST', '/__s30/drop-mfa-evidence', $cookieName, $cookie, [], $csrf);
$assert($response->getStatusCode() === 204, 'test-only MFA evidence removal failed');
$response = $send($kernel, 'GET', '/__s30/policy-probe', $cookieName, $cookie);
$assert($response->getStatusCode() === 403, 'policy middleware accepted a protected session without MFA evidence');

// Non-protected identities preserve Sprint 27 first-party session behavior when MFA is armed.
$ordinaryCookie = null;
$ordinaryState = $inspect($kernel, $cookieName, $ordinaryCookie);
$response = $send(
    $kernel,
    'POST',
    '/auth/login',
    $cookieName,
    $ordinaryCookie,
    $loginPayload('tenant-alpha', 'ordinary-alpha', $ordinaryPassword, 'organization-alpha'),
    $ordinaryState['csrf_token'],
    '198.51.100.35',
);
$assert($response->getStatusCode() === 200, 'non-protected login no longer preserves Sprint 27 behavior');
$ordinaryState = $inspect($kernel, $cookieName, $ordinaryCookie);
$assert(($ordinaryState['full'][FirstPartySessionKeys::IDENTITY] ?? null) === 'ordinary-alpha', 'non-protected full session identity missing');
$assert(($ordinaryState['mfa_verified_at'] ?? null) === null, 'non-protected login fabricated privileged MFA evidence');
$response = $send($kernel, 'GET', '/__s30/policy-probe', $cookieName, $ordinaryCookie);
$assert($response->getStatusCode() === 403, 'non-protected session bypassed privileged MFA policy middleware');

// Ciphertext is bound to exact tenant + identity; copying it cross-tenant must fail closed.
$connection->table('oneqay_identity_totp_factors')->insert([
    'tenant_id' => 'tenant-beta',
    'identity_id' => 'admin-beta',
    'secret_ciphertext' => $ciphertext,
    'created_at_unix' => $fakeClock->now,
    'confirmed_at_unix' => null,
    'last_accepted_time_step' => null,
]);
/** @var PrivilegedTotpMfaService $mfa */
$mfa = $app->make(PrivilegedTotpMfaService::class);
try {
    $mfa->startEnrollment(TenantId::fromString('tenant-beta'), PlatformIdentityId::fromString('admin-beta'));
    $assert(false, 'cross-tenant ciphertext copy was accepted');
} catch (PrivilegedTotpMfaViolation $exception) {
    $assert($exception->errorCode === PrivilegedTotpMfaViolation::FACTOR_STATE_INVALID, 'cross-tenant ciphertext failed with unexpected violation');
}

// Confirmed factors cannot be silently replaced.
try {
    $mfa->startEnrollment(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('admin-alpha'));
    $assert(false, 'confirmed factor replacement was accepted');
} catch (PrivilegedTotpMfaViolation $exception) {
    $assert($exception->errorCode === PrivilegedTotpMfaViolation::ENROLLMENT_DENIED, 'confirmed factor replacement failed with unexpected violation');
}

// Preview and Production runtime classes remain denied even if the feature flag is armed.
foreach (['preview', 'production'] as $deniedRuntime) {
    $app['config']->set('oneqay.runtime_class', $deniedRuntime);
    $deniedCookie = null;
    $deniedState = $inspect($kernel, $cookieName, $deniedCookie);
    $response = $send(
        $kernel,
        'POST',
        '/auth/login',
        $cookieName,
        $deniedCookie,
        $loginPayload('tenant-alpha', 'admin-alpha', $adminPassword, 'organization-alpha'),
        $deniedState['csrf_token'],
        '198.51.100.36',
    );
    $assert($response->getStatusCode() === 404, $deniedRuntime.' runtime unexpectedly exposed first-party authentication');
}
$app['config']->set('oneqay.runtime_class', 'ci');

// Direct repository state remains strict and no test output contains credential material.
/** @var PrivilegedTotpMfaRepository $repository */
$repository = $app->make(PrivilegedTotpMfaRepository::class);
$assert($repository->protectedControlRequired(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('admin-alpha')), 'protected-control requirement was lost');

$removeTree($workspace);
echo "Sprint 30 privileged TOTP MFA regression passed.\n";

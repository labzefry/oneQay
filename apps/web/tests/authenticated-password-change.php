<?php

declare(strict_types=1);

use App\Application\Identity\AuthenticatedPasswordChangeClock;
use App\Application\Identity\AuthenticatedPasswordChangeRepository;
use App\Application\Identity\AuthenticatedPasswordChangeService;
use App\Application\Identity\AuthenticatedPasswordChangeViolation;
use App\Application\Identity\FirstPartyCredentialEpochRepository;
use App\Application\Identity\FirstPartyIdentityCredentialVerifier;
use App\Application\Identity\PrivilegedTotpClock;
use App\Application\Identity\PrivilegedTotpEngine;
use App\Application\Identity\PrivilegedTotpMfaRepository;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Application\Identity\PrivilegedTotpMfaState;
use App\Application\Identity\VerifyFirstPartyCredentialEpoch;
use App\Application\Identity\VerifyFirstPartyIdentityCredential;
use App\Application\Persistence\PersistenceTransaction;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('q', 32));
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'local',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_RECOVERY_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint 34 authenticated password change regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s34-password-change-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'password-change.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's34_password_change');
$app['config']->set('database.connections.s34_password_change', [
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
$app['config']->set('oneqay.authentication_recovery.enabled', true);
$app['config']->set('oneqay.authentication_recovery.restricted_session_ttl_seconds', 600);
$app['config']->set('oneqay.privileged_totp_mfa.enabled', false);

/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s34_password_change');
$manager->setDefaultConnection('s34_password_change');
$connection = $manager->connection('s34_password_change');
$connection->getPdo();
$app->forgetScopedInstances();

$migrationsOneToTen = [
    '0000_00_00_000001_create_foundational_context_graph.php',
    '0000_00_00_000002_create_organizational_access_grants.php',
    '0000_00_00_000003_create_scoped_role_permission_policy.php',
    '0000_00_00_000004_create_policy_mutation_journal.php',
    '0000_00_00_000005_create_initial_tenant_administrator_provisioning_journal.php',
    '0000_00_00_000006_create_protected_control_administrator_mutation_journal.php',
    '0000_00_00_000007_create_identity_password_credentials.php',
    '0000_00_00_000008_create_initial_password_enrollments.php',
    '0000_00_00_000009_create_identity_totp_factors.php',
    '0000_00_00_000010_create_identity_recovery_codes.php',
];
$migrationEleven = '0000_00_00_000011_add_credential_epoch_to_identity_password_credentials.php';
$expectedMigrations = [...$migrationsOneToTen, $migrationEleven];
$actualMigrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($actualMigrations);
$assert($actualMigrations === $expectedMigrations, 'canonical migration set must be exactly #1-#11');
foreach ($migrationsOneToTen as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$connection->table('oneqay_tenants')->insert(['id' => 'tenant-backfill']);
$connection->table('oneqay_organizations')->insert(['tenant_id' => 'tenant-backfill', 'id' => 'organization-backfill']);
$connection->table('oneqay_identities')->insert(['tenant_id' => 'tenant-backfill', 'id' => 'identity-backfill']);
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-backfill',
    'identity_id' => 'identity-backfill',
    'organization_id' => 'organization-backfill',
]);
$connection->table('oneqay_identity_password_credentials')->insert([
    'tenant_id' => 'tenant-backfill',
    'identity_id' => 'identity-backfill',
    'password_hash' => password_hash('Backfill old password only', PASSWORD_DEFAULT),
]);
$connection->table('oneqay_identity_recovery_codes')->insert([
    'tenant_id' => 'tenant-backfill',
    'code_id' => str_repeat('a', 32),
    'identity_id' => 'identity-backfill',
    'code_selector' => str_repeat('A', 22),
    'secret_digest' => str_repeat('b', 64),
    'issued_at_unix' => 1_800_000_000,
    'consumed_at_unix' => 1_800_000_001,
    'revoked_at_unix' => null,
]);
$connection->table('oneqay_identity_recovery_audit')->insert([
    'tenant_id' => 'tenant-backfill',
    'audit_id' => str_repeat('c', 32),
    'identity_id' => 'identity-backfill',
    'event_type' => 'password_reset_completed',
    'code_id' => str_repeat('a', 32),
    'correlation_id' => 'S34-BACKFILL',
    'occurred_at_unix' => 1_800_000_002,
]);

(require __DIR__.'/../database/migrations/'.$migrationEleven)->up();
$backfillEpoch = $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', 'tenant-backfill')
    ->where('identity_id', 'identity-backfill')
    ->value('credential_epoch');
$assert($backfillEpoch === 1, 'migration #11 must preserve historical reset-derived epoch');

$connection->table('oneqay_tenants')->insert(['id' => 'tenant-alpha']);
$connection->table('oneqay_organizations')->insert(['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha']);
$identities = ['ordinary-alpha', 'boundary12-alpha', 'short11-alpha', 'boundary4096-alpha', 'long4097-alpha'];
foreach ($identities as $identity) {
    $connection->table('oneqay_identities')->insert(['tenant_id' => 'tenant-alpha', 'id' => $identity]);
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'organization_id' => 'organization-alpha',
    ]);
    $connection->table('oneqay_identity_password_credentials')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'password_hash' => password_hash('Sprint34 current password '.$identity, PASSWORD_DEFAULT),
        'credential_epoch' => 0,
    ]);
}
$connection->table('oneqay_identity_recovery_codes')->insert([
    'tenant_id' => 'tenant-alpha',
    'code_id' => str_repeat('d', 32),
    'identity_id' => 'ordinary-alpha',
    'code_selector' => str_repeat('D', 22),
    'secret_digest' => str_repeat('e', 64),
    'issued_at_unix' => 1_900_000_000,
    'consumed_at_unix' => null,
    'revoked_at_unix' => null,
]);

$cookieName = (string) config('session.cookie');
$assert($cookieName !== '', 'session cookie name missing');
$app['router']->get('/__s34/session-inspect', static function (Request $request) {
    $full = [];
    foreach (FirstPartySessionKeys::all() as $key) {
        $full[$key] = $request->session()->get($key);
    }
    return response()->json([
        'csrf_token' => $request->session()->token(),
        'full' => $full,
        'credential_epoch' => $request->session()->get(FirstPartySessionKeys::CREDENTIAL_EPOCH),
    ]);
})->middleware('web');

$refreshCookie = static function (\Symfony\Component\HttpFoundation\Response $response, string $cookieName, ?string &$cookie): void {
    foreach ($response->headers->getCookies() as $responseCookie) {
        if ($responseCookie->getName() === $cookieName) {
            $cookie = $responseCookie->getValue();
        }
    }
};
$requestCounter = 0;
$send = static function (
    Kernel $kernel,
    string $method,
    string $uri,
    string $cookieName,
    ?string &$cookie,
    array $payload = [],
    ?string $csrfToken = null,
) use ($refreshCookie, &$requestCounter): \Symfony\Component\HttpFoundation\Response {
    if ($method === 'POST' && $csrfToken !== null) {
        $payload['_token'] = $csrfToken;
    }
    $requestCounter++;
    $request = Request::create(
        $uri,
        $method,
        $payload,
        cookies: $cookie === null ? [] : [$cookieName => $cookie],
        server: [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CORRELATION_ID' => 'S34-PasswordChange_'.str_pad((string) $requestCounter, 4, '0', STR_PAD_LEFT),
            'REMOTE_ADDR' => '198.19.'.intdiv($requestCounter, 250).'.'.(($requestCounter % 249) + 1),
        ],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $refreshCookie($response, $cookieName, $cookie);
    return $response;
};
$inspect = static function (?string &$cookie) use ($send, $kernel, $cookieName, $assert): array {
    $response = $send($kernel, 'GET', '/__s34/session-inspect', $cookieName, $cookie);
    $assert($response->getStatusCode() === 200, 'session inspection failed');
    $decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $assert(is_array($decoded), 'session inspection payload invalid');
    return $decoded;
};
$login = static function (?string &$cookie, string $identity, string $password) use ($send, $inspect, $kernel, $cookieName): \Symfony\Component\HttpFoundation\Response {
    $state = $inspect($cookie);
    return $send($kernel, 'POST', '/auth/login', $cookieName, $cookie, [
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'password' => $password,
        'organization_id' => 'organization-alpha',
    ], (string) $state['csrf_token']);
};
$change = static function (?string &$cookie, string $currentPassword, string $newPassword, ?string $totpCode = null) use ($send, $inspect, $kernel, $cookieName): \Symfony\Component\HttpFoundation\Response {
    $state = $inspect($cookie);
    $payload = ['current_password' => $currentPassword, 'new_password' => $newPassword];
    if ($totpCode !== null) { $payload['totp_code'] = $totpCode; }
    return $send($kernel, 'POST', '/auth/password/change', $cookieName, $cookie, $payload, (string) $state['csrf_token']);
};

$ordinaryCurrent = 'Sprint34 current password ordinary-alpha';
$ordinaryReplacement = 'Sprint34 replacement password ordinary alpha';
$cookieA = null;
$cookieB = null;
$assert($login($cookieA, 'ordinary-alpha', $ordinaryCurrent)->getStatusCode() === 200, 'first ordinary login');
$assert($login($cookieB, 'ordinary-alpha', $ordinaryCurrent)->getStatusCode() === 200, 'second ordinary login');
$stateBefore = $inspect($cookieB);
$assert(($stateBefore['credential_epoch'] ?? null) === 0, 'fresh login must capture epoch zero');
$csrfBefore = $stateBefore['csrf_token'] ?? null;
$changeResponse = $change($cookieB, $ordinaryCurrent, $ordinaryReplacement);
$assert($changeResponse->getStatusCode() === 200, 'ordinary password change success');
$assert($changeResponse->headers->get('Cache-Control') === 'no-store, private', 'success response cache control');
$stateAfter = $inspect($cookieB);
$assert(($stateAfter['credential_epoch'] ?? null) === null, 'successful change must not rewrite epoch into invalidated session');
$assert(($stateAfter['csrf_token'] ?? null) !== $csrfBefore, 'successful change must regenerate CSRF token');
foreach (($stateAfter['full'] ?? []) as $value) {
    $assert($value === null, 'successful change must invalidate full session');
}

$ordinaryRow = $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'ordinary-alpha')->first();
$assert(is_object($ordinaryRow) && ($ordinaryRow->credential_epoch ?? null) === 1, 'successful change increments epoch exactly once');
$assert(is_string($ordinaryRow->password_hash ?? null) && password_verify($ordinaryReplacement, $ordinaryRow->password_hash), 'replacement hash verification');
$assert(! password_verify($ordinaryCurrent, $ordinaryRow->password_hash), 'old password invalid after change');
$revokedAt = $connection->table('oneqay_identity_recovery_codes')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'ordinary-alpha')->value('revoked_at_unix');
$assert(is_int($revokedAt) && $revokedAt > 0, 'successful change revokes unused recovery code');
$ordinaryAuditCount = $connection->table('oneqay_identity_recovery_audit')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'ordinary-alpha')->count();
$assert($ordinaryAuditCount === 0, 'normal password change must not fabricate recovery audit');

$staleResponse = $change($cookieA, $ordinaryCurrent, 'Another valid replacement password');
$assert($staleResponse->getStatusCode() === 401, 'old session must fail stale epoch');
$oldLoginCookie = null;
$assert($login($oldLoginCookie, 'ordinary-alpha', $ordinaryCurrent)->getStatusCode() === 401, 'old password login must fail');
$newLoginCookie = null;
$assert($login($newLoginCookie, 'ordinary-alpha', $ordinaryReplacement)->getStatusCode() === 200, 'replacement password fresh login');
$assert(($inspect($newLoginCookie)['credential_epoch'] ?? null) === 1, 'fresh login captures new epoch');
$samePassword = $change($newLoginCookie, $ordinaryReplacement, $ordinaryReplacement);
$assert($samePassword->getStatusCode() === 401, 'same-password replacement must fail');
$assert($connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'ordinary-alpha')->value('credential_epoch') === 1, 'failed same-password request must not advance epoch');

$boundaries = [
    ['boundary12-alpha', str_repeat('x', 12), 200],
    ['short11-alpha', str_repeat('x', 11), 401],
    ['boundary4096-alpha', str_repeat('y', 4096), 200],
    ['long4097-alpha', str_repeat('y', 4097), 401],
];
foreach ($boundaries as [$identity, $replacement, $expectedStatus]) {
    $cookie = null;
    $current = 'Sprint34 current password '.$identity;
    $assert($login($cookie, $identity, $current)->getStatusCode() === 200, $identity.' login');
    $assert($change($cookie, $current, $replacement)->getStatusCode() === $expectedStatus, $identity.' replacement boundary');
}

$fakeCredentialVerifier = new class implements FirstPartyIdentityCredentialVerifier {
    public function verify(TenantId $tenantId, PlatformIdentityId $identityId, #[\SensitiveParameter] string $password): bool
    { return hash_equals('Current privileged password', $password); }
};
$fakeEpochRepository = new class implements FirstPartyCredentialEpochRepository {
    public function current(TenantId $tenantId, PlatformIdentityId $identityId): int { return 4; }
};
$fakeTransaction = new class implements PersistenceTransaction {
    public function run(callable $operation): mixed { return $operation(); }
};
$fakeTotpRepository = new class implements PrivilegedTotpMfaRepository {
    public int $consumed = 0;
    public bool $protected = true;
    public function protectedControlRequired(TenantId $tenantId, PlatformIdentityId $identityId): bool { return $this->protected; }
    public function factorState(TenantId $tenantId, PlatformIdentityId $identityId): PrivilegedTotpMfaState { return new PrivilegedTotpMfaState(PrivilegedTotpMfaState::CONFIRMED); }
    public function ensurePendingSecret(TenantId $tenantId, PlatformIdentityId $identityId, #[\SensitiveParameter] ?string $freshSecret, int $createdAtUnix): string { return str_repeat('A', 32); }
    public function pendingSecret(TenantId $tenantId, PlatformIdentityId $identityId): string { return str_repeat('A', 32); }
    public function confirmedSecret(TenantId $tenantId, PlatformIdentityId $identityId): string { return str_repeat('A', 32); }
    public function confirmPendingStep(TenantId $tenantId, PlatformIdentityId $identityId, int $matchedTimeStep, int $confirmedAtUnix): void {}
    public function consumeConfirmedStep(TenantId $tenantId, PlatformIdentityId $identityId, int $matchedTimeStep): void { $this->consumed++; }
};
$fakeTotpEngine = new class implements PrivilegedTotpEngine {
    public function generateSecret(): string { return str_repeat('A', 32); }
    public function provisioningUri(TenantId $tenantId, PlatformIdentityId $identityId, #[\SensitiveParameter] string $secret): string { return 'otpauth://totp/oneQay'; }
    public function matchTimeStep(#[\SensitiveParameter] string $secret, #[\SensitiveParameter] string $code, int $nowUnix): ?int { return $code === '123456' ? 777 : null; }
};
$fakeTotpClock = new class implements PrivilegedTotpClock { public function nowUnix(): int { return 2_000_000_000; } };
$fakeMfa = new PrivilegedTotpMfaService($fakeTotpRepository, $fakeTotpEngine, $fakeTransaction, $fakeTotpClock);
$fakeChangeRepository = new class implements AuthenticatedPasswordChangeRepository {
    public int $calls = 0;
    public function change(TenantId $tenantId, PlatformIdentityId $identityId, int $expectedCredentialEpoch, #[\SensitiveParameter] string $currentPassword, #[\SensitiveParameter] string $newPassword, int $occurredAtUnix): void { $this->calls++; }
};
$fakeClock = new class implements AuthenticatedPasswordChangeClock { public function nowUnix(): int { return 2_000_000_001; } };
$privilegedService = new AuthenticatedPasswordChangeService(
    $fakeChangeRepository,
    new VerifyFirstPartyIdentityCredential($fakeCredentialVerifier),
    new VerifyFirstPartyCredentialEpoch($fakeEpochRepository),
    $fakeMfa,
    $fakeTransaction,
    $fakeClock,
    true,
);
$tenantId = TenantId::fromString('tenant-alpha');
$identityId = PlatformIdentityId::fromString('privileged-alpha');
try {
    $privilegedService->change($tenantId, $identityId, 4, 'Current privileged password', 'New privileged password value', null);
    $assert(false, 'protected password change must require TOTP code');
} catch (AuthenticatedPasswordChangeViolation) {
    // expected
}
$privilegedService->change($tenantId, $identityId, 4, 'Current privileged password', 'New privileged password value', '123456');
$assert($fakeTotpRepository->consumed === 1, 'fresh protected TOTP challenge must consume one step');
$assert($fakeChangeRepository->calls === 1, 'protected mutation proceeds only after fresh TOTP challenge');
$fakeTotpRepository->protected = false;
try {
    $privilegedService->change($tenantId, $identityId, 4, 'Current privileged password', 'Another privileged password', '123456');
    $assert(false, 'ordinary identity must reject caller supplied TOTP code');
} catch (AuthenticatedPasswordChangeViolation) {
    // expected
}

$removeTree($workspace);
echo "Sprint 34 authenticated password change regression passed.\n";

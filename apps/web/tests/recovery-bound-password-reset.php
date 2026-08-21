<?php

declare(strict_types=1);

use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\RecoveryCodeClock;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
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
        throw new RuntimeException('Sprint 33 recovery-bound password reset regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s33-reset-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'reset.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.connections.s33_reset', [
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
$manager->purge('s33_reset');
$manager->setDefaultConnection('s33_reset');
$connection = $manager->connection('s33_reset');
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
    '0000_00_00_000010_create_identity_recovery_codes.php',
    '0000_00_00_000011_add_credential_epoch_to_identity_password_credentials.php',
];
$actualMigrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($actualMigrations);
$assert($actualMigrations === $migrationNames, 'canonical migration set must be exactly #1-#11');
foreach ($migrationNames as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
$connection->table('oneqay_organizations')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'organization-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'organization-beta'],
]);
$connection->table('oneqay_identities')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => 'reset-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'protected-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'totp-alpha'],
    ['tenant_id' => 'tenant-alpha', 'id' => 'missing-credential-alpha'],
    ['tenant_id' => 'tenant-beta', 'id' => 'reset-beta'],
]);
foreach (['reset-alpha', 'protected-alpha', 'totp-alpha', 'missing-credential-alpha'] as $identity) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'organization_id' => 'organization-alpha',
    ]);
}
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-beta',
    'identity_id' => 'reset-beta',
    'organization_id' => 'organization-beta',
]);

$oldPassword = 'Sprint33 old password / synthetic only';
$protectedPassword = 'Sprint33 protected password / synthetic only';
$totpPassword = 'Sprint33 totp password / synthetic only';
$betaPassword = 'Sprint33 beta password / synthetic only';
$connection->table('oneqay_identity_password_credentials')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'reset-alpha', 'password_hash' => password_hash($oldPassword, PASSWORD_DEFAULT)],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'protected-alpha', 'password_hash' => password_hash($protectedPassword, PASSWORD_DEFAULT)],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'totp-alpha', 'password_hash' => password_hash($totpPassword, PASSWORD_DEFAULT)],
    ['tenant_id' => 'tenant-beta', 'identity_id' => 'reset-beta', 'password_hash' => password_hash($betaPassword, PASSWORD_DEFAULT)],
]);
$assert($connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')->value('credential_epoch') === 0, 'new credential row must start at generic epoch zero');

$connection->table('oneqay_roles')->insert([
    'tenant_id' => 'tenant-alpha',
    'id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
]);
$connection->table('oneqay_tenant_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'protected-alpha',
    'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
]);
$connection->table('oneqay_identity_totp_factors')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'totp-alpha',
    'secret_ciphertext' => 'synthetic-ciphertext-never-read-by-reset',
    'created_at_unix' => 1_800_000_000,
    'confirmed_at_unix' => 1_800_000_001,
    'last_accepted_time_step' => null,
]);

$fakeClock = new class(1_900_000_000) implements RecoveryCodeClock {
    public function __construct(public int $now) {}
    public function nowUnix(): int { return $this->now; }
};
$app->instance(RecoveryCodeClock::class, $fakeClock);

$cookieName = (string) config('session.cookie');
$assert($cookieName !== '', 'session cookie name missing');

$app['router']->get('/__s33/session-inspect', static function (Request $request) {
    $full = [];
    foreach (FirstPartySessionKeys::all() as $key) {
        $full[$key] = $request->session()->get($key);
    }
    $recovery = [];
    foreach (FirstPartySessionKeys::recovery() as $key) {
        $recovery[$key] = $request->session()->get($key);
    }

    return response()->json([
        'csrf_token' => $request->session()->token(),
        'full' => $full,
        'credential_epoch' => $request->session()->get(FirstPartySessionKeys::CREDENTIAL_EPOCH),
        'recovery' => $recovery,
        'mfa_verified_at' => $request->session()->get(FirstPartySessionKeys::MFA_VERIFIED_AT),
        'step_up_verified_at' => $request->session()->get(FirstPartySessionKeys::STEP_UP_VERIFIED_AT),
        'step_up_scope' => $request->session()->get(FirstPartySessionKeys::STEP_UP_SCOPE),
        'step_up_context' => $request->session()->get(FirstPartySessionKeys::STEP_UP_CONTEXT),
    ]);
})->middleware('web');

$app['router']->post('/__s33/session-mutate', static function (Request $request) {
    $put = $request->input('put', []);
    $forget = $request->input('forget', []);
    if (is_array($put)) {
        foreach ($put as $key => $value) {
            if (is_string($key)) {
                $request->session()->put($key, $value);
            }
        }
    }
    if (is_array($forget)) {
        foreach ($forget as $key) {
            if (is_string($key)) {
                $request->session()->forget($key);
            }
        }
    }
    return response()->noContent();
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
            'HTTP_X_CORRELATION_ID' => 'S33-Reset_'.str_pad((string) $requestCounter, 4, '0', STR_PAD_LEFT),
            'REMOTE_ADDR' => '198.18.'.intdiv($requestCounter, 250).'.'.(($requestCounter % 249) + 1),
        ],
    );
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    $refreshCookie($response, $cookieName, $cookie);
    return $response;
};

$inspect = static function (Kernel $kernel, string $cookieName, ?string &$cookie) use ($send, $assert): array {
    $response = $send($kernel, 'GET', '/__s33/session-inspect', $cookieName, $cookie);
    $assert($response->getStatusCode() === 200, 'session inspection failed');
    $decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $assert(is_array($decoded), 'session inspection payload invalid');
    return $decoded;
};

$mutateSession = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    array $put = [],
    array $forget = [],
) use ($send, $inspect, $assert): array {
    $state = $inspect($kernel, $cookieName, $cookie);
    $csrf = $state['csrf_token'] ?? null;
    $assert(is_string($csrf) && $csrf !== '', 'session mutation CSRF missing');
    $response = $send($kernel, 'POST', '/__s33/session-mutate', $cookieName, $cookie, ['put' => $put, 'forget' => $forget], $csrf);
    $assert($response->getStatusCode() === 204, 'test-only session mutation failed');
    return $inspect($kernel, $cookieName, $cookie);
};

$login = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    string $tenant,
    string $identity,
    string $password,
    string $organization,
) use ($send, $inspect, $assert): array {
    $state = $inspect($kernel, $cookieName, $cookie);
    $csrf = $state['csrf_token'] ?? null;
    $assert(is_string($csrf) && $csrf !== '', 'login CSRF missing');
    $response = $send($kernel, 'POST', '/auth/login', $cookieName, $cookie, [
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'password' => $password,
        'organization_id' => $organization,
    ], $csrf);
    return [$response, $inspect($kernel, $cookieName, $cookie)];
};

$logout = static function (Kernel $kernel, string $cookieName, ?string &$cookie) use ($send, $inspect, $assert): void {
    $state = $inspect($kernel, $cookieName, $cookie);
    $csrf = $state['csrf_token'] ?? null;
    $assert(is_string($csrf) && $csrf !== '', 'logout CSRF missing');
    $response = $send($kernel, 'POST', '/auth/logout', $cookieName, $cookie, [], $csrf);
    $assert($response->getStatusCode() === 204, 'logout failed');
};

$rotate = static function (Kernel $kernel, string $cookieName, ?string &$cookie, string $password) use ($send, $inspect, $assert): \Symfony\Component\HttpFoundation\Response {
    $state = $inspect($kernel, $cookieName, $cookie);
    $csrf = $state['csrf_token'] ?? null;
    $assert(is_string($csrf) && $csrf !== '', 'rotation CSRF missing');
    return $send($kernel, 'POST', '/auth/recovery/codes/rotate', $cookieName, $cookie, ['password' => $password], $csrf);
};

$prove = static function (Kernel $kernel, string $cookieName, ?string &$cookie, string $code) use ($send, $inspect, $assert): \Symfony\Component\HttpFoundation\Response {
    $state = $inspect($kernel, $cookieName, $cookie);
    $csrf = $state['csrf_token'] ?? null;
    $assert(is_string($csrf) && $csrf !== '', 'proof CSRF missing');
    return $send($kernel, 'POST', '/auth/recovery/proof', $cookieName, $cookie, ['recovery_code' => $code], $csrf);
};

$reset = static function (Kernel $kernel, string $cookieName, ?string &$cookie, array $payload) use ($send, $inspect, $assert): \Symfony\Component\HttpFoundation\Response {
    $state = $inspect($kernel, $cookieName, $cookie);
    $csrf = $state['csrf_token'] ?? null;
    $assert(is_string($csrf) && $csrf !== '', 'reset CSRF missing');
    return $send($kernel, 'POST', '/auth/recovery/password-reset', $cookieName, $cookie, $payload, $csrf);
};

$issueRestrictedProof = static function (
    Kernel $kernel,
    string $cookieName,
    string $tenant,
    string $identity,
    string $password,
    string $organization,
) use ($login, $rotate, $logout, $prove, $assert): array {
    $cookie = null;
    [$loginResponse] = $login($kernel, $cookieName, $cookie, $tenant, $identity, $password, $organization);
    $assert($loginResponse->getStatusCode() === 200, 'proof issuer login failed');
    $rotation = $rotate($kernel, $cookieName, $cookie, $password);
    $assert($rotation->getStatusCode() === 200, 'recovery-code rotation failed');
    $rotationBody = json_decode((string) $rotation->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $codes = $rotationBody['recovery_codes'] ?? null;
    $assert(is_array($codes) && count($codes) === 8, 'rotation did not return exactly eight recovery codes');
    $logout($kernel, $cookieName, $cookie);
    $proof = $prove($kernel, $cookieName, $cookie, (string) $codes[0]);
    $assert($proof->getStatusCode() === 200, 'recovery proof failed');
    $proofBody = json_decode((string) $proof->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $assert(! array_key_exists('code_id', $proofBody), 'proof response exposed internal code id');
    return [$cookie, $codes];
};

$seedConsumedProof = static function (string $tenant, string $identity, string $suffix) use ($connection, $fakeClock): string {
    $codeId = str_pad(substr(hash('sha256', $tenant.'|'.$identity.'|'.$suffix), 0, 32), 32, '0');
    $selector = substr(strtr(base64_encode(hash('sha256', 'selector|'.$suffix, true)), '+/', '-_'), 0, 22);
    $connection->table('oneqay_identity_recovery_codes')->insert([
        'tenant_id' => $tenant,
        'code_id' => $codeId,
        'identity_id' => $identity,
        'code_selector' => $selector,
        'secret_digest' => hash('sha256', 'secret|'.$suffix),
        'issued_at_unix' => $fakeClock->now - 10,
        'consumed_at_unix' => $fakeClock->now,
        'revoked_at_unix' => null,
    ]);
    $connection->table('oneqay_identity_recovery_audit')->insert([
        'tenant_id' => $tenant,
        'audit_id' => substr(hash('sha256', 'audit|'.$suffix), 0, 32),
        'identity_id' => $identity,
        'event_type' => 'proof_succeeded',
        'code_id' => $codeId,
        'correlation_id' => 'S33-seed-'.$suffix,
        'occurred_at_unix' => $fakeClock->now,
    ]);
    return $codeId;
};

$installRestrictedSession = static function (
    Kernel $kernel,
    string $cookieName,
    ?string &$cookie,
    string $tenant,
    string $identity,
    string $codeId,
) use ($mutateSession, $fakeClock): array {
    return $mutateSession($kernel, $cookieName, $cookie, [
        FirstPartySessionKeys::RECOVERY_TENANT => $tenant,
        FirstPartySessionKeys::RECOVERY_IDENTITY => $identity,
        FirstPartySessionKeys::RECOVERY_CODE_ID => $codeId,
        FirstPartySessionKeys::RECOVERY_STATE => 'password_reset_required',
        FirstPartySessionKeys::RECOVERY_PROVED_AT => $fakeClock->now,
        FirstPartySessionKeys::RECOVERY_EXPIRES_AT => $fakeClock->now + 600,
    ]);
};

$assert(FirstPartySessionKeys::all() === [
    FirstPartySessionKeys::IDENTITY,
    FirstPartySessionKeys::TENANT,
    FirstPartySessionKeys::ORGANIZATION,
    FirstPartySessionKeys::OUTLET,
    FirstPartySessionKeys::DEVICE,
], 'canonical full-session keys changed');
$assert(! in_array(FirstPartySessionKeys::CREDENTIAL_EPOCH, FirstPartySessionKeys::all(), true), 'credential epoch entered canonical key list');
$assert(in_array(FirstPartySessionKeys::RECOVERY_CODE_ID, FirstPartySessionKeys::recovery(), true), 'recovery code id missing from restricted evidence');

$anonymous = null;
$anonymousReset = $reset($kernel, $cookieName, $anonymous, ['password' => 'abcdefghijkl']);
$assert($anonymousReset->getStatusCode() === 401, 'anonymous reset was accepted');

$fullCookie = null;
[$fullLogin, $fullState] = $login($kernel, $cookieName, $fullCookie, 'tenant-alpha', 'reset-alpha', $oldPassword, 'organization-alpha');
$assert($fullLogin->getStatusCode() === 200, 'full-session collision setup login failed');
$assert(($fullState['credential_epoch'] ?? null) === 0, 'fresh pre-reset login did not capture epoch zero');
$fullCollision = $reset($kernel, $cookieName, $fullCookie, ['password' => 'abcdefghijkl']);
$assert($fullCollision->getStatusCode() === 401, 'full authenticated session was accepted as restricted reset session');

$staleCookie = null;
[$staleLogin, $staleState] = $login($kernel, $cookieName, $staleCookie, 'tenant-alpha', 'reset-alpha', $oldPassword, 'organization-alpha');
$assert($staleLogin->getStatusCode() === 200 && ($staleState['credential_epoch'] ?? null) === 0, 'stale-session fixture setup failed');

$legacyCookie = null;
[$legacyLogin] = $login($kernel, $cookieName, $legacyCookie, 'tenant-alpha', 'reset-alpha', $oldPassword, 'organization-alpha');
$assert($legacyLogin->getStatusCode() === 200, 'legacy-session setup login failed');
$mutateSession($kernel, $cookieName, $legacyCookie, [], [FirstPartySessionKeys::CREDENTIAL_EPOCH]);
$legacyRotation = $rotate($kernel, $cookieName, $legacyCookie, $oldPassword);
$assert($legacyRotation->getStatusCode() === 200, 'missing legacy epoch was rejected while durable epoch was zero');

[$resetCookie, $firstCodes] = $issueRestrictedProof($kernel, $cookieName, 'tenant-alpha', 'reset-alpha', $oldPassword, 'organization-alpha');
$restricted = $inspect($kernel, $cookieName, $resetCookie);
$restrictedCodeId = $restricted['recovery'][FirstPartySessionKeys::RECOVERY_CODE_ID] ?? null;
$assert(is_string($restrictedCodeId) && preg_match('/\A[0-9a-f]{32}\z/D', $restrictedCodeId) === 1, 'restricted session code id invalid');
$assert(($restricted['recovery'][FirstPartySessionKeys::RECOVERY_EXPIRES_AT] ?? 0) === $fakeClock->now + 600, 'restricted TTL is not exact 600 seconds');
$restrictedCsrf = $restricted['csrf_token'];

$unknownField = $reset($kernel, $cookieName, $resetCookie, ['password' => 'abcdefghijkl', 'tenant_id' => 'tenant-beta']);
$assert($unknownField->getStatusCode() === 401, 'reset accepted caller-controlled selector field');
$afterUnknown = $inspect($kernel, $cookieName, $resetCookie);
$assert(($afterUnknown['recovery'][FirstPartySessionKeys::RECOVERY_EXPIRES_AT] ?? null) === $fakeClock->now + 600, 'failed reset extended restricted TTL');

$tooShort = $reset($kernel, $cookieName, $resetCookie, ['password' => str_repeat('x', 11)]);
$assert($tooShort->getStatusCode() === 401, '11-byte password was accepted');

$twelveBytes = ' abcdefghij ';
$assert(strlen($twelveBytes) === 12, '12-byte fixture length changed');
$firstReset = $reset($kernel, $cookieName, $resetCookie, ['password' => $twelveBytes]);
$assert($firstReset->getStatusCode() === 200, '12-byte password reset failed');
$assert(str_contains((string) $firstReset->headers->get('Cache-Control'), 'no-store'), 'reset response is cacheable');
$assert(! str_contains((string) $firstReset->getContent(), $twelveBytes), 'reset response exposed password');
$afterFirstReset = $inspect($kernel, $cookieName, $resetCookie);
$assert(($afterFirstReset['csrf_token'] ?? null) !== $restrictedCsrf, 'successful reset did not regenerate CSRF');
foreach ($afterFirstReset['full'] as $value) {
    $assert($value === null, 'successful reset created canonical full-session evidence');
}
$assert(($afterFirstReset['credential_epoch'] ?? null) === null, 'recovery reset wrote credential epoch into session');
foreach ($afterFirstReset['recovery'] as $value) {
    $assert($value === null, 'successful reset retained restricted recovery evidence');
}
$assert(($afterFirstReset['mfa_verified_at'] ?? null) === null, 'reset fabricated MFA evidence');
$assert(($afterFirstReset['step_up_verified_at'] ?? null) === null, 'reset fabricated step-up evidence');

$storedCredential = $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')->first();
$assert(is_object($storedCredential) && is_string($storedCredential->password_hash ?? null), 'replacement credential missing');
$assert(password_verify($twelveBytes, $storedCredential->password_hash), 'replacement password hash not stored');
$assert(! password_verify($oldPassword, $storedCredential->password_hash), 'old password still verifies after reset');
$assert(! password_verify(trim($twelveBytes), $storedCredential->password_hash), 'password reset trimmed or normalized password input');
$assert(($storedCredential->credential_epoch ?? null) === 1, 'first recovery reset must increment generic credential epoch exactly once');

$firstCompletionCount = $connection->table('oneqay_identity_recovery_audit')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')
    ->where('event_type', 'password_reset_completed')->count();
$assert($firstCompletionCount === 1, 'first reset did not append exactly one completion audit');
$assert($connection->table('oneqay_identity_recovery_audit')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')
    ->where('code_id', $restrictedCodeId)->where('event_type', 'password_reset_completed')->exists(), 'completion audit not bound to consumed code');
$remainingActive = $connection->table('oneqay_identity_recovery_codes')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')
    ->where('code_id', '<>', $restrictedCodeId)->whereNull('consumed_at_unix')->whereNull('revoked_at_unix')->count();
$assert($remainingActive === 0, 'other unused recovery codes were not revoked');

$oldCookie = null;
[$oldLogin] = $login($kernel, $cookieName, $oldCookie, 'tenant-alpha', 'reset-alpha', $oldPassword, 'organization-alpha');
$assert($oldLogin->getStatusCode() === 401, 'old password authenticated after reset');
$freshCookie = null;
[$freshLogin, $freshState] = $login($kernel, $cookieName, $freshCookie, 'tenant-alpha', 'reset-alpha', $twelveBytes, 'organization-alpha');
$assert($freshLogin->getStatusCode() === 200, 'new password did not authenticate through normal login');
$assert(($freshState['credential_epoch'] ?? null) === 1, 'fresh post-reset login did not capture durable epoch one');

$staleRotation = $rotate($kernel, $cookieName, $staleCookie, $twelveBytes);
$assert($staleRotation->getStatusCode() === 401, 'stale pre-reset session epoch remained authoritative');
$mutateSession($kernel, $cookieName, $staleCookie, [], [FirstPartySessionKeys::CREDENTIAL_EPOCH]);
$missingAfterReset = $rotate($kernel, $cookieName, $staleCookie, $twelveBytes);
$assert($missingAfterReset->getStatusCode() === 401, 'missing epoch was accepted after durable epoch advanced');

$negativeCookie = null;
[$negativeLogin] = $login($kernel, $cookieName, $negativeCookie, 'tenant-alpha', 'reset-alpha', $twelveBytes, 'organization-alpha');
$assert($negativeLogin->getStatusCode() === 200, 'negative-epoch setup login failed');
$mutateSession($kernel, $cookieName, $negativeCookie, [FirstPartySessionKeys::CREDENTIAL_EPOCH => -1]);
$assert($rotate($kernel, $cookieName, $negativeCookie, $twelveBytes)->getStatusCode() === 401, 'negative session epoch was accepted');

$futureCookie = null;
[$futureLogin] = $login($kernel, $cookieName, $futureCookie, 'tenant-alpha', 'reset-alpha', $twelveBytes, 'organization-alpha');
$assert($futureLogin->getStatusCode() === 200, 'future-epoch setup login failed');
$mutateSession($kernel, $cookieName, $futureCookie, [FirstPartySessionKeys::CREDENTIAL_EPOCH => 99]);
$assert($rotate($kernel, $cookieName, $futureCookie, $twelveBytes)->getStatusCode() === 401, 'invented future session epoch was accepted');
$assert($rotate($kernel, $cookieName, $freshCookie, $twelveBytes)->getStatusCode() === 200, 'fresh current epoch could not rotate recovery codes');

$replayCookie = null;
$installRestrictedSession($kernel, $cookieName, $replayCookie, 'tenant-alpha', 'reset-alpha', $restrictedCodeId);
$replay = $reset($kernel, $cookieName, $replayCookie, ['password' => 'mnopqrstuvwx']);
$assert($replay->getStatusCode() === 401, 'completed recovery proof replay was accepted');
$assert($connection->table('oneqay_identity_recovery_audit')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')
    ->where('code_id', $restrictedCodeId)->where('event_type', 'password_reset_completed')->count() === 1, 'replay appended a second completion event');

[$collisionCookie] = $issueRestrictedProof($kernel, $cookieName, 'tenant-alpha', 'reset-alpha', $twelveBytes, 'organization-alpha');
$collisionState = $inspect($kernel, $cookieName, $collisionCookie);
$collisionCodeId = $collisionState['recovery'][FirstPartySessionKeys::RECOVERY_CODE_ID];
$collisionExpiry = $collisionState['recovery'][FirstPartySessionKeys::RECOVERY_EXPIRES_AT];
$collisions = [
    FirstPartySessionKeys::IDENTITY => 'reset-alpha',
    FirstPartySessionKeys::PENDING_IDENTITY => 'reset-alpha',
    FirstPartySessionKeys::MFA_VERIFIED_AT => $fakeClock->now,
    FirstPartySessionKeys::STEP_UP_VERIFIED_AT => $fakeClock->now,
    FirstPartySessionKeys::STEP_UP_SCOPE => 'synthetic-scope',
    FirstPartySessionKeys::STEP_UP_CONTEXT => 'synthetic-context',
];
foreach ($collisions as $key => $value) {
    $mutateSession($kernel, $cookieName, $collisionCookie, [$key => $value]);
    $collisionResponse = $reset($kernel, $cookieName, $collisionCookie, ['password' => 'mnopqrstuvwx']);
    $assert($collisionResponse->getStatusCode() === 401, 'restricted-session collision accepted for '.$key);
    $state = $mutateSession($kernel, $cookieName, $collisionCookie, [], [$key]);
    $assert(($state['recovery'][FirstPartySessionKeys::RECOVERY_EXPIRES_AT] ?? null) === $collisionExpiry, 'collision attempt extended restricted TTL');
}
$assert(! $connection->table('oneqay_identity_recovery_audit')->where('code_id', $collisionCodeId)->where('event_type', 'password_reset_completed')->exists(), 'collision attempt consumed recovery proof');

$beforeExpiryHash = $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')->value('password_hash');
$fakeClock->now += 601;
$expired = $reset($kernel, $cookieName, $collisionCookie, ['password' => 'mnopqrstuvwx']);
$assert($expired->getStatusCode() === 401, 'expired restricted session was accepted');
$afterExpiryHash = $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')->value('password_hash');
$assert($beforeExpiryHash === $afterExpiryHash, 'expired reset mutated credential');

foreach ([
    ['protected-alpha', $protectedPassword, 'protected'],
    ['totp-alpha', $totpPassword, 'totp'],
    ['missing-credential-alpha', null, 'missing-credential'],
] as [$identity, $password, $suffix]) {
    $codeId = $seedConsumedProof('tenant-alpha', $identity, $suffix);
    $cookie = null;
    $installRestrictedSession($kernel, $cookieName, $cookie, 'tenant-alpha', $identity, $codeId);
    $response = $reset($kernel, $cookieName, $cookie, ['password' => 'qrstuvwxyz12']);
    $assert($response->getStatusCode() === 401, $suffix.' identity was reset');
    if (is_string($password)) {
        $hash = $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', $identity)->value('password_hash');
        $assert(is_string($hash) && password_verify($password, $hash), $suffix.' credential changed on denied reset');
    }
    $assert(! $connection->table('oneqay_identity_recovery_audit')->where('code_id', $codeId)->where('event_type', 'password_reset_completed')->exists(), $suffix.' denial wrote completion audit');
}

$fakeClock->now += 10;
[$secondResetCookie] = $issueRestrictedProof($kernel, $cookieName, 'tenant-alpha', 'reset-alpha', $twelveBytes, 'organization-alpha');
$tooLong = str_repeat('L', 4097);
$maxPassword = str_repeat('M', 4096);
$assert($reset($kernel, $cookieName, $secondResetCookie, ['password' => $tooLong])->getStatusCode() === 401, '4097-byte password was accepted');
$secondReset = $reset($kernel, $cookieName, $secondResetCookie, ['password' => $maxPassword]);
$assert($secondReset->getStatusCode() === 200, '4096-byte password was rejected');
$assert($connection->table('oneqay_identity_recovery_audit')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')->where('event_type', 'password_reset_completed')->count() === 2, 'second reset did not preserve exactly two completion audit events');
$assert($connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')->value('credential_epoch') === 2, 'second recovery reset must increment generic credential epoch exactly once');

$postFirstCookie = null;
[$postFirstLogin, $postFirstState] = $login($kernel, $cookieName, $postFirstCookie, 'tenant-alpha', 'reset-alpha', $maxPassword, 'organization-alpha');
$assert($postFirstLogin->getStatusCode() === 200 && ($postFirstState['credential_epoch'] ?? null) === 2, 'fresh epoch-two login failed');
$assert($rotate($kernel, $cookieName, $freshCookie, $maxPassword)->getStatusCode() === 401, 'epoch-one session remained authoritative after second reset');
$assert($rotate($kernel, $cookieName, $postFirstCookie, $maxPassword)->getStatusCode() === 200, 'fresh epoch-two session could not rotate recovery codes');

$app['config']->set('oneqay.authentication_recovery.enabled', false);
$app->forgetScopedInstances();
/** @var \App\Application\Identity\FirstPartyCredentialEpochRepository $epochRepository */
$epochRepository = $app->make(\App\Application\Identity\FirstPartyCredentialEpochRepository::class);
$assert($epochRepository->current(
    \App\Domain\Tenancy\TenantId::fromString('tenant-alpha'),
    \App\Domain\Identity\PlatformIdentityId::fromString('reset-alpha'),
) === 2, 'feature disablement changed durable credential epoch authority');
$app['config']->set('oneqay.authentication_recovery.enabled', true);
$app->forgetScopedInstances();

$auditRows = $connection->table('oneqay_identity_recovery_audit')->where('event_type', 'password_reset_completed')->get();
foreach ($auditRows as $row) {
    $serialized = json_encode($row, JSON_THROW_ON_ERROR);
    $assert(! str_contains($serialized, $oldPassword) && ! str_contains($serialized, $twelveBytes), 'audit leaked password material');
}
$assert($connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'reset-alpha')->count() === 1, 'reset recreated credential row instead of update-only mutation');

$removeTree($workspace);
echo "Sprint 33 recovery-bound password reset regression passed.\n";

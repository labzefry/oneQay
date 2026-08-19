<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapService;
use App\Application\Identity\FirstControlPrincipalCredentialBootstrapViolation;
use App\Application\Identity\FirstPartyIdentityCredentialVerifier;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\LaravelFirstControlPrincipalCredentialBootstrapRepository;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Tester\CommandTester;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('f', 32));
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'local',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED' => 'true',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

$app = require __DIR__.'/../bootstrap/app.php';
/** @var ConsoleKernel $consoleKernel */
$consoleKernel = $app->make(ConsoleKernel::class);
$consoleKernel->bootstrap();

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint 29 first control principal credential bootstrap regression failed: '.$message);
    }
};

$expectViolation = static function (callable $operation, string $expectedCode) use ($assert): void {
    try {
        $operation();
    } catch (FirstControlPrincipalCredentialBootstrapViolation $exception) {
        $assert($exception->errorCode === $expectedCode, 'unexpected violation '.$exception->errorCode.'; expected '.$expectedCode);

        return;
    }

    $assert(false, 'expected violation '.$expectedCode.' was not thrown');
};

$removeTree = static function (string $path): void {
    if (is_file($path) || is_link($path)) { @unlink($path); return; }
    if (! is_dir($path)) { return; }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($path);
};

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s29-bootstrap-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'bootstrap.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.connections.s29_bootstrap', [
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
$app['config']->set('oneqay.first_control_principal_credential_bootstrap.enabled', true);
/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s29_bootstrap');
$manager->setDefaultConnection('s29_bootstrap');
$connection = $manager->connection('s29_bootstrap');
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
$assert(! is_file(__DIR__.'/../database/migrations/0000_00_00_000009_create_first_control_principal_bootstrap.php'), 'migration #9 unexpectedly exists');

$seedControlTenant = static function (
    string $tenant,
    string $identity,
    string $journalOutcome = 'applied',
    bool $withJournal = true,
    bool $withAssignment = true,
    bool $alternateControlRole = false,
) use ($connection): void {
    $organization = 'organization-'.$tenant;
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
    $connection->table('oneqay_identities')->insert(['tenant_id' => $tenant, 'id' => $identity]);
    $connection->table('oneqay_organizations')->insert(['tenant_id' => $tenant, 'id' => $organization]);
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'organization_id' => $organization,
    ]);
    $connection->table('oneqay_roles')->insert([
        'tenant_id' => $tenant,
        'id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
    ]);
    $connection->table('oneqay_role_permissions')->insert([
        'tenant_id' => $tenant,
        'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
        'permission_id' => AdministrationPermission::MANAGE,
    ]);
    if ($withAssignment) {
        $connection->table('oneqay_tenant_role_assignments')->insert([
            'tenant_id' => $tenant,
            'identity_id' => $identity,
            'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
        ]);
    }
    if ($withJournal) {
        $connection->table('oneqay_initial_tenant_admin_provisionings')->insert([
            'tenant_id' => $tenant,
            'provisioning_id' => 'provision-'.$tenant,
            'identity_id' => $identity,
            'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
            'permission_id' => AdministrationPermission::MANAGE,
            'payload_fingerprint' => hash('sha256', $tenant.'|'.$identity),
            'outcome' => $journalOutcome,
            'occurred_at_unix' => 1_787_050_000,
        ]);
    }
    if ($alternateControlRole) {
        $connection->table('oneqay_roles')->insert([
            'tenant_id' => $tenant,
            'id' => 'alternate-control-role',
        ]);
        $connection->table('oneqay_role_permissions')->insert([
            'tenant_id' => $tenant,
            'role_id' => 'alternate-control-role',
            'permission_id' => AdministrationPermission::MANAGE,
        ]);
    }
};

$seedControlTenant('tenant-alpha', 'first-control-shared');
$seedControlTenant('tenant-beta', 'first-control-shared');
$seedControlTenant('tenant-missing-journal', 'first-control-missing', withJournal: false);
$seedControlTenant('tenant-bad-journal', 'first-control-bad', journalOutcome: 'rejected');
$seedControlTenant('tenant-no-assignment', 'first-control-no-assignment', withAssignment: false);
$seedControlTenant('tenant-alternate-role', 'first-control-alternate', alternateControlRole: true);
$seedControlTenant('tenant-existing', 'first-control-existing');
$seedControlTenant('tenant-active-enrollment', 'first-control-active');

$existingHash = password_hash('Existing-Credential-S29!', PASSWORD_DEFAULT);
$assert(is_string($existingHash) && $existingHash !== '', 'existing fixture hash generation failed');
$connection->table('oneqay_identity_password_credentials')->insert([
    'tenant_id' => 'tenant-existing',
    'identity_id' => 'first-control-existing',
    'password_hash' => $existingHash,
]);
$connection->table('oneqay_initial_password_enrollments')->insert([
    'tenant_id' => 'tenant-active-enrollment',
    'enrollment_id' => 'active-s29-enrollment',
    'actor_identity_id' => 'first-control-active',
    'target_identity_id' => 'first-control-active',
    'token_digest' => hash('sha256', 'synthetic-active-enrollment-token'),
    'issued_at_unix' => 1_787_050_000,
    'expires_at_unix' => 1_787_050_900,
    'consumed_at_unix' => null,
    'active_marker' => 1,
]);

$app->forgetScopedInstances();
/** @var FirstControlPrincipalCredentialBootstrapService $service */
$service = $app->make(FirstControlPrincipalCredentialBootstrapService::class);

$expectViolation(
    fn () => $service->bootstrap(TenantId::fromString('tenant-alpha'), 'too-short'),
    FirstControlPrincipalCredentialBootstrapViolation::INVALID_PASSWORD,
);
$expectViolation(
    fn () => $service->bootstrap(TenantId::fromString('tenant-alpha'), str_repeat('x', 4097)),
    FirstControlPrincipalCredentialBootstrapViolation::INVALID_PASSWORD,
);
$expectViolation(
    fn () => $service->bootstrap(TenantId::fromString('tenant-missing-journal'), 'Synthetic-Missing-S29!'),
    FirstControlPrincipalCredentialBootstrapViolation::BOOTSTRAP_INELIGIBLE,
);
$expectViolation(
    fn () => $service->bootstrap(TenantId::fromString('tenant-bad-journal'), 'Synthetic-Bad-S29!'),
    FirstControlPrincipalCredentialBootstrapViolation::BOOTSTRAP_INELIGIBLE,
);
$expectViolation(
    fn () => $service->bootstrap(TenantId::fromString('tenant-no-assignment'), 'Synthetic-NoAssign-S29!'),
    FirstControlPrincipalCredentialBootstrapViolation::BOOTSTRAP_INELIGIBLE,
);
$expectViolation(
    fn () => $service->bootstrap(TenantId::fromString('tenant-alternate-role'), 'Synthetic-Alternate-S29!'),
    FirstControlPrincipalCredentialBootstrapViolation::BOOTSTRAP_INELIGIBLE,
);
$expectViolation(
    fn () => $service->bootstrap(TenantId::fromString('tenant-existing'), 'Synthetic-Overwrite-S29!'),
    FirstControlPrincipalCredentialBootstrapViolation::CREDENTIAL_ALREADY_EXISTS,
);
$assert(
    hash_equals($existingHash, (string) $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-existing')->where('identity_id', 'first-control-existing')->value('password_hash')),
    'existing credential changed after overwrite denial',
);
$expectViolation(
    fn () => $service->bootstrap(TenantId::fromString('tenant-active-enrollment'), 'Synthetic-Active-S29!'),
    FirstControlPrincipalCredentialBootstrapViolation::ACTIVE_ENROLLMENT_EXISTS,
);

$disabledRepository = new LaravelFirstControlPrincipalCredentialBootstrapRepository($connection, true, 'ci', false);
$disabledService = new FirstControlPrincipalCredentialBootstrapService(
    $disabledRepository,
    new LaravelPersistenceTransaction($connection, true, 'ci'),
);
$expectViolation(
    fn () => $disabledService->bootstrap(TenantId::fromString('tenant-alpha'), 'Synthetic-Disabled-S29!'),
    FirstControlPrincipalCredentialBootstrapViolation::FEATURE_DISABLED,
);

$persistenceDisabledService = new FirstControlPrincipalCredentialBootstrapService(
    new LaravelFirstControlPrincipalCredentialBootstrapRepository($connection, false, 'ci', true),
    new LaravelPersistenceTransaction($connection, false, 'ci'),
);
$expectViolation(
    fn () => $persistenceDisabledService->bootstrap(TenantId::fromString('tenant-alpha'), 'Synthetic-Persistence-S29!'),
    FirstControlPrincipalCredentialBootstrapViolation::PERSISTENCE_DISABLED,
);

$previewDeniedService = new FirstControlPrincipalCredentialBootstrapService(
    new LaravelFirstControlPrincipalCredentialBootstrapRepository($connection, true, 'preview', true),
    new LaravelPersistenceTransaction($connection, true, 'preview'),
);
$expectViolation(
    fn () => $previewDeniedService->bootstrap(TenantId::fromString('tenant-alpha'), 'Synthetic-Preview-S29!'),
    FirstControlPrincipalCredentialBootstrapViolation::RUNTIME_DENIED,
);

$commands = Artisan::all();
$command = $commands['oneqay:identity:first-control-credential-bootstrap'] ?? null;
$assert($command !== null, 'armed CI bootstrap command is not registered');

// Hidden confirmation mismatch must fail without writing a credential.
$mismatchTester = new CommandTester($command);
$mismatchTester->setInputs(['Synthetic-Mismatch-S29!', 'Synthetic-Different-S29!']);
$mismatchStatus = $mismatchTester->execute(['tenant_id' => 'tenant-alpha'], ['interactive' => true]);
$assert($mismatchStatus !== 0, 'password confirmation mismatch unexpectedly succeeded');
$assert(! $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->exists(), 'confirmation mismatch wrote a credential');

$password = '  Synthetic Bootstrap S29!  ';
$tester = new CommandTester($command);
$tester->setInputs([$password, $password]);
$status = $tester->execute(['tenant_id' => 'tenant-alpha'], ['interactive' => true]);
$output = $tester->getDisplay(true);
$assert($status === 0, 'valid interactive bootstrap did not succeed');
$assert(str_contains($output, 'ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP|STATE=applied'), 'sanitized applied output missing');
$assert(! str_contains($output, $password), 'password leaked in command output');
$assert(! str_contains($output, 'first-control-shared'), 'target identity leaked in command output');

$row = $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', 'tenant-alpha')
    ->where('identity_id', 'first-control-shared')
    ->first();
$assert($row !== null, 'bootstrapped credential row missing');
$assert(is_string($row->password_hash ?? null) && $row->password_hash !== $password, 'plaintext password persisted');
$assert(password_verify($password, (string) $row->password_hash), 'stored password hash does not verify exact password');
$assert(! password_verify(trim($password), (string) $row->password_hash), 'password was trimmed or normalized');
$assert(! $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-beta')->where('identity_id', 'first-control-shared')->exists(), 'same textual identity in foreign tenant received credential');

/** @var FirstPartyIdentityCredentialVerifier $verifier */
$verifier = $app->make(FirstPartyIdentityCredentialVerifier::class);
$assert($verifier->verify(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('first-control-shared'), $password), 'Sprint 26 verifier rejected bootstrapped password');
$assert(! $verifier->verify(TenantId::fromString('tenant-beta'), PlatformIdentityId::fromString('first-control-shared'), $password), 'Sprint 26 verifier crossed tenant boundary');

$repeatTester = new CommandTester($command);
$repeatTester->setInputs(['Synthetic-Replacement-S29!', 'Synthetic-Replacement-S29!']);
$repeatStatus = $repeatTester->execute(['tenant_id' => 'tenant-alpha'], ['interactive' => true]);
$assert($repeatStatus !== 0, 'repeat bootstrap overwrote credential');
$storedAfterRepeat = (string) $connection->table('oneqay_identity_password_credentials')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'first-control-shared')->value('password_hash');
$assert(hash_equals((string) $row->password_hash, $storedAfterRepeat), 'credential hash changed after repeat bootstrap');

// Bootstrap must leave all authority/provisioning/enrollment state untouched.
$assert($connection->table('oneqay_initial_tenant_admin_provisionings')->where('tenant_id', 'tenant-alpha')->count() === 1, 'Sprint 23 provisioning journal changed');
$assert($connection->table('oneqay_protected_control_admin_mutations')->count() === 0, 'Sprint 24 lifecycle journal changed');
$assert($connection->table('oneqay_initial_password_enrollments')->where('tenant_id', 'tenant-alpha')->count() === 0, 'Sprint 28 enrollment state changed for bootstrapped tenant');
$assert($connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'first-control-shared')->where('role_id', InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE)->count() === 1, 'protected assignment changed');

// Prove the bootstrapped credential enters the ordinary Sprint 27 login flow and bootstrap itself creates no session.
/** @var HttpKernel $httpKernel */
$httpKernel = $app->make(HttpKernel::class);
$cookieName = (string) config('session.cookie');
$assert($cookieName !== '', 'session cookie name missing');
$app['router']->get('/__s29/session-state', function (Request $request) {
    return response()->json([
        'csrf_token' => $request->session()->token(),
        'identity' => $request->session()->get(FirstPartySessionKeys::IDENTITY),
        'tenant' => $request->session()->get(FirstPartySessionKeys::TENANT),
        'organization' => $request->session()->get(FirstPartySessionKeys::ORGANIZATION),
    ]);
})->middleware('web');

$cookie = null;
$refreshCookie = static function (\Symfony\Component\HttpFoundation\Response $response, string $cookieName, ?string &$cookie): void {
    foreach ($response->headers->getCookies() as $responseCookie) {
        if ($responseCookie->getName() === $cookieName) {
            $cookie = $responseCookie->getValue();
        }
    }
};
$stateRequest = Request::create('/__s29/session-state', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
$stateResponse = $httpKernel->handle($stateRequest);
$httpKernel->terminate($stateRequest, $stateResponse);
$refreshCookie($stateResponse, $cookieName, $cookie);
$beforeLogin = json_decode((string) $stateResponse->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($beforeLogin['identity'] ?? null) === null, 'bootstrap itself established an identity session');
$csrf = $beforeLogin['csrf_token'] ?? null;
$assert(is_string($csrf) && $csrf !== '', 'pre-login CSRF token missing');

$loginRequest = Request::create(
    '/auth/login',
    'POST',
    [
        '_token' => $csrf,
        'tenant_id' => 'tenant-alpha',
        'identity_id' => 'first-control-shared',
        'password' => $password,
        'organization_id' => 'organization-tenant-alpha',
    ],
    cookies: $cookie === null ? [] : [$cookieName => $cookie],
    server: [
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_CORRELATION_ID' => 'S29-Bootstrap_0001',
        'REMOTE_ADDR' => '10.29.0.1',
    ],
);
$loginResponse = $httpKernel->handle($loginRequest);
$httpKernel->terminate($loginRequest, $loginResponse);
$refreshCookie($loginResponse, $cookieName, $cookie);
$assert($loginResponse->getStatusCode() === 200, 'Sprint 27 login rejected bootstrapped password');

$afterRequest = Request::create('/__s29/session-state', 'GET', cookies: $cookie === null ? [] : [$cookieName => $cookie], server: ['HTTP_ACCEPT' => 'application/json']);
$afterResponse = $httpKernel->handle($afterRequest);
$httpKernel->terminate($afterRequest, $afterResponse);
$afterLogin = json_decode((string) $afterResponse->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($afterLogin['identity'] ?? null) === 'first-control-shared', 'Sprint 27 login did not establish identity session');
$assert(($afterLogin['tenant'] ?? null) === 'tenant-alpha', 'Sprint 27 login did not establish tenant session');
$assert(($afterLogin['organization'] ?? null) === 'organization-tenant-alpha', 'Sprint 27 login did not establish organization session');

$consoleSource = (string) file_get_contents(__DIR__.'/../routes/console.php');
$configSource = (string) file_get_contents(__DIR__.'/../config/oneqay.php');
$assert(str_contains($consoleSource, "oneqay:identity:first-control-credential-bootstrap {tenant_id}"), 'exact console signature missing');
$assert(! preg_match('/first-control-credential-bootstrap[^\n]*(password|identity_id|role_id|permission_id)/i', $consoleSource), 'bootstrap command gained forbidden sensitive selector/argument');
$assert(substr_count($consoleSource, '->secret(') >= 2, 'hidden password and confirmation prompts missing');
$assert(str_contains($configSource, "env('ONEQAY_FIRST_CONTROL_CREDENTIAL_BOOTSTRAP_ENABLED', false)"), 'feature arm is not fail-closed by default');
$assert(! str_contains($consoleSource, 'routes/web.php'), 'console bootstrap unexpectedly references web route registration');

$serializedNonCredentialState = json_encode([
    'provisioning' => $connection->table('oneqay_initial_tenant_admin_provisionings')->get(),
    'protected_control' => $connection->table('oneqay_protected_control_admin_mutations')->get(),
    'enrollment' => $connection->table('oneqay_initial_password_enrollments')->get(),
], JSON_THROW_ON_ERROR);
$assert(! str_contains($serializedNonCredentialState, $password), 'plaintext password leaked into non-credential durable state');
$assert(! str_contains($serializedNonCredentialState, (string) $row->password_hash), 'credential hash leaked into non-credential durable state');

$removeTree($workspace);
echo "Sprint 29 first control principal credential bootstrap regression passed.\n";

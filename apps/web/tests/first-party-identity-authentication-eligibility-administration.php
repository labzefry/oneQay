<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationRepository;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationService;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationViolation;
use App\Application\Identity\IdentityAuthenticationEligibilityMutationId;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Tenancy\TenantId;
use App\Delivery\Http\Identity\FirstPartyIdentityEligibilityAdministrationController;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityAdministrationRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityVerifier;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('q', 32));
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
        throw new RuntimeException('Sprint41 identity authentication eligibility administration regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s41-identity-eligibility-admin-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'identity-eligibility-admin.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's41_identity_eligibility_admin');
$app['config']->set('database.connections.s41_identity_eligibility_admin', [
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
$manager->purge('s41_identity_eligibility_admin');
$manager->setDefaultConnection('s41_identity_eligibility_admin');
$connection = $manager->connection('s41_identity_eligibility_admin');
$connection->getPdo();

$migrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$expectedMigrations = [];
for ($index = 1; $index <= 15; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $matches = array_values(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix)));
    $assert(count($matches) === 1, 'migration #'.$index.' must exist exactly once');
    $expectedMigrations[] = $matches[0];
}
$assert($migrations === $expectedMigrations, 'migration set must be exactly #1-#15');
$assert($migrations[14] === '0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php', 'migration #15 exact filename');
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$journalColumns = $connection->getSchemaBuilder()->getColumnListing('oneqay_identity_authentication_eligibility_mutations');
sort($journalColumns);
$expectedJournalColumns = [
    'actor_identity_id',
    'mutation_id',
    'occurred_at_unix',
    'operation',
    'outcome',
    'payload_fingerprint',
    'target_identity_id',
    'tenant_id',
];
sort($expectedJournalColumns);
$assert($journalColumns === $expectedJournalColumns, 'journal contains unauthorized columns');

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
foreach ([
    ['tenant-alpha', 'admin-alpha'],
    ['tenant-alpha', 'ordinary-alpha-a'],
    ['tenant-alpha', 'ordinary-alpha-b'],
    ['tenant-alpha', 'ordinary-alpha-c'],
    ['tenant-alpha', 'ordinary-alpha-disabled'],
    ['tenant-alpha', 'protected-alpha'],
    ['tenant-alpha', 'no-authority-alpha'],
    ['tenant-beta', 'ordinary-beta'],
] as [$tenant, $identity]) {
    $connection->table('oneqay_identities')->insert([
        'tenant_id' => $tenant,
        'id' => $identity,
    ]);
}
foreach ([
    ['tenant-alpha', 'organization-alpha'],
    ['tenant-beta', 'organization-beta'],
] as [$tenant, $organization]) {
    $connection->table('oneqay_organizations')->insert([
        'tenant_id' => $tenant,
        'id' => $organization,
    ]);
}
foreach (['admin-alpha', 'ordinary-alpha-a', 'ordinary-alpha-b', 'ordinary-alpha-c', 'ordinary-alpha-disabled', 'protected-alpha', 'no-authority-alpha'] as $identity) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'organization_id' => 'organization-alpha',
    ]);
}
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-beta',
    'identity_id' => 'ordinary-beta',
    'organization_id' => 'organization-beta',
]);

$controlRole = InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE;
$connection->table('oneqay_roles')->insert([
    'tenant_id' => 'tenant-alpha',
    'id' => $controlRole,
]);
$connection->table('oneqay_role_permissions')->insert([
    'tenant_id' => 'tenant-alpha',
    'role_id' => $controlRole,
    'permission_id' => AdministrationPermission::MANAGE,
]);
$connection->table('oneqay_tenant_role_assignments')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'admin-alpha', 'role_id' => $controlRole],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'protected-alpha', 'role_id' => $controlRole],
]);

foreach (['ordinary-alpha-a', 'ordinary-alpha-b', 'ordinary-alpha-c', 'ordinary-alpha-disabled'] as $identity) {
    $hash = password_hash('Sprint41 synthetic password '.$identity, PASSWORD_BCRYPT);
    $assert(is_string($hash), 'password hash setup');
    $connection->table('oneqay_identity_password_credentials')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'password_hash' => $hash,
    ]);
}
$connection->table('oneqay_identities')
    ->where('tenant_id', 'tenant-alpha')
    ->where('id', 'ordinary-alpha-disabled')
    ->update(['first_party_authentication_enabled' => false]);

$actor = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('admin-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
);
$noAuthorityActor = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('no-authority-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
);
$clock = new class implements PolicyAdministrationClock {
    public int $now = 1787848800;
    public function nowUnix(): int { return $this->now; }
};

foreach ([
    [false, 'ci', FirstPartyIdentityEligibilityAdministrationViolation::PERSISTENCE_DISABLED],
    [true, 'preview', FirstPartyIdentityEligibilityAdministrationViolation::RUNTIME_DENIED],
    [true, 'production', FirstPartyIdentityEligibilityAdministrationViolation::RUNTIME_DENIED],
] as [$enabled, $runtime, $code]) {
    try {
        (new LaravelFirstPartyIdentityEligibilityAdministrationRepository($connection, $enabled, $runtime))->hasTenantControlAuthority($actor);
        $assert(false, 'unauthorized runtime reached administration storage');
    } catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
        $assert($exception->errorCode === $code, 'runtime guard returned unexpected code');
    }
}

$repository = new LaravelFirstPartyIdentityEligibilityAdministrationRepository($connection, true, 'ci');
$transaction = new LaravelPersistenceTransaction($connection, true, 'ci');
$service = new FirstPartyIdentityEligibilityAdministrationService($repository, $transaction, $clock);
$verifier = new LaravelFirstPartyIdentityEligibilityVerifier($connection, true, 'ci', true);

$assert($repository->hasTenantControlAuthority($actor), 'exact tenant control actor not authorized');
$assert(! $repository->hasTenantControlAuthority($noAuthorityActor), 'ordinary actor received control authority');

$expectViolation = static function (callable $operation, string $code, string $message) use ($assert): void {
    try {
        $operation();
        $assert(false, $message.' was accepted');
    } catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
        $assert($exception->errorCode === $code, $message.' returned wrong violation');
    }
};

$expectViolation(
    fn () => $service->disable(
        $noAuthorityActor,
        PlatformIdentityId::fromString('ordinary-alpha-a'),
        IdentityAuthenticationEligibilityMutationId::fromString('unauthorized-disable'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::AUTHORIZATION_DENIED,
    'unauthorized actor',
);
$expectViolation(
    fn () => $service->disable(
        $actor,
        PlatformIdentityId::fromString('admin-alpha'),
        IdentityAuthenticationEligibilityMutationId::fromString('self-disable'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
    'self target',
);
$expectViolation(
    fn () => $service->disable(
        $actor,
        PlatformIdentityId::fromString('protected-alpha'),
        IdentityAuthenticationEligibilityMutationId::fromString('protected-disable'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::PROTECTED_TARGET,
    'protected-control target',
);
$expectViolation(
    fn () => $service->disable(
        $actor,
        PlatformIdentityId::fromString('ordinary-beta'),
        IdentityAuthenticationEligibilityMutationId::fromString('cross-tenant-disable'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
    'cross-tenant target',
);
$expectViolation(
    fn () => $service->disable(
        $actor,
        PlatformIdentityId::fromString('missing-alpha'),
        IdentityAuthenticationEligibilityMutationId::fromString('missing-disable'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
    'missing target',
);

$credentialBefore = (array) $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', 'tenant-alpha')
    ->where('identity_id', 'ordinary-alpha-a')
    ->first();
$membershipBefore = $connection->table('oneqay_identity_organizations')
    ->where('tenant_id', 'tenant-alpha')
    ->where('identity_id', 'ordinary-alpha-a')
    ->count();
$sessionRowsBefore = $connection->table('oneqay_identity_first_party_sessions')->count();

$outcome = $service->disable(
    $actor,
    PlatformIdentityId::fromString('ordinary-alpha-a'),
    IdentityAuthenticationEligibilityMutationId::fromString('disable-alpha-a'),
);
$assert($outcome === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'enabled target did not return applied');
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('ordinary-alpha-a')), 'disabled target remains eligible');
$assert($verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('ordinary-alpha-b')), 'unrelated identity changed');
$assert($verifier->isEligible(TenantId::fromString('tenant-beta'), PlatformIdentityId::fromString('ordinary-beta')), 'other tenant changed');

$replay = $service->disable(
    $actor,
    PlatformIdentityId::fromString('ordinary-alpha-a'),
    IdentityAuthenticationEligibilityMutationId::fromString('disable-alpha-a'),
);
$assert($replay === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'exact replay did not return prior applied outcome');
$assert($connection->table('oneqay_identity_authentication_eligibility_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 'disable-alpha-a')->count() === 1, 'exact replay duplicated journal');

$expectViolation(
    fn () => $service->disable(
        $actor,
        PlatformIdentityId::fromString('ordinary-alpha-b'),
        IdentityAuthenticationEligibilityMutationId::fromString('disable-alpha-a'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT,
    'mutation identifier reuse with different target',
);

$alreadyDisabled = $service->disable(
    $actor,
    PlatformIdentityId::fromString('ordinary-alpha-disabled'),
    IdentityAuthenticationEligibilityMutationId::fromString('disable-already-disabled'),
);
$assert($alreadyDisabled === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_NO_CHANGE, 'already-disabled target did not return no_change');

$firstConvergence = $service->disable(
    $actor,
    PlatformIdentityId::fromString('ordinary-alpha-b'),
    IdentityAuthenticationEligibilityMutationId::fromString('disable-alpha-b-one'),
);
$secondConvergence = $service->disable(
    $actor,
    PlatformIdentityId::fromString('ordinary-alpha-b'),
    IdentityAuthenticationEligibilityMutationId::fromString('disable-alpha-b-two'),
);
$assert($firstConvergence === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'first convergent mutation not applied');
$assert($secondConvergence === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_NO_CHANGE, 'second convergent mutation did not settle as no_change');
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('ordinary-alpha-b')), 'convergent mutations restored enabled state');

$credentialAfter = (array) $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', 'tenant-alpha')
    ->where('identity_id', 'ordinary-alpha-a')
    ->first();
$assert($credentialAfter === $credentialBefore, 'password credential material changed');
$assert($connection->table('oneqay_identity_organizations')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'ordinary-alpha-a')->count() === $membershipBefore, 'organizational membership changed');
$assert($connection->table('oneqay_identity_first_party_sessions')->count() === $sessionRowsBefore, 'session authority rows changed');
$assert($connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'protected-alpha')->where('role_id', $controlRole)->exists(), 'protected control role changed');
$assert($connection->table('oneqay_tenant_role_assignments')->where('tenant_id', 'tenant-alpha')->where('identity_id', 'admin-alpha')->where('role_id', $controlRole)->exists(), 'actor control role changed');

foreach ($connection->table('oneqay_identity_authentication_eligibility_mutations')->get() as $journal) {
    $row = (array) $journal;
    $assert(array_keys($row) !== [], 'journal row missing');
    $assert(($row['operation'] ?? null) === 'disable', 'journal operation widened beyond disable');
    $assert(in_array($row['outcome'] ?? null, ['applied', 'no_change'], true), 'journal outcome invalid');
    $assert(is_string($row['payload_fingerprint'] ?? null) && preg_match('/\A[a-f0-9]{64}\z/', $row['payload_fingerprint']) === 1, 'journal fingerprint invalid');
    $encoded = json_encode($row, JSON_THROW_ON_ERROR);
    foreach ([$testKey, 'Sprint41 synthetic password', 'session', 'csrf'] as $forbidden) {
        $assert(! str_contains(strtolower($encoded), strtolower($forbidden)), 'journal leaked restricted evidence: '.$forbidden);
    }
}

$contextStore = new class($actor) implements OrganizationalContextStore {
    public function __construct(private ?VerifiedOrganizationalContext $context) {}
    public function current(): ?VerifiedOrganizationalContext { return $this->context; }
    public function setVerified(VerifiedOrganizationalContext $context): void { $this->context = $context; }
    public function clear(): void { $this->context = null; }
};
$controller = new FirstPartyIdentityEligibilityAdministrationController($contextStore, $service);

$request = Request::create(
    '/administration/identities/ordinary-alpha-c/authentication-disablement',
    'POST',
    ['mutation_id' => 'http-disable-alpha-c'],
);
$request->attributes->set('oneqay.correlation_id', 'S41-HTTP-0001');
$response = $controller($request, 'ordinary-alpha-c');
$assert($response->getStatusCode() === 200, 'controller positive status');
$decoded = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($decoded['outcome'] ?? null) === 'applied', 'controller outcome');
$assert(($decoded['correlation_id'] ?? null) === 'S41-HTTP-0001', 'controller correlation id');
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('ordinary-alpha-c')), 'controller did not disable exact target');

$connection->table('oneqay_identities')->where('tenant_id', 'tenant-alpha')->where('id', 'ordinary-alpha-c')->update(['first_party_authentication_enabled' => true]);
$strictPayload = Request::create(
    '/administration/identities/ordinary-alpha-c/authentication-disablement',
    'POST',
    ['mutation_id' => 'http-invalid-extra', 'tenant_id' => 'tenant-beta'],
);
$strictPayload->attributes->set('oneqay.correlation_id', 'S41-HTTP-0002');
$strictResponse = $controller($strictPayload, 'ordinary-alpha-c');
$assert($strictResponse->getStatusCode() === 422, 'extra payload authority field accepted');
$assert($verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('ordinary-alpha-c')), 'invalid payload mutated target');

foreach ([
    ['missing-alpha', 'http-missing-generic'],
    ['protected-alpha', 'http-protected-generic'],
] as [$target, $mutation]) {
    $generic = Request::create(
        '/administration/identities/'.$target.'/authentication-disablement',
        'POST',
        ['mutation_id' => $mutation],
    );
    $generic->attributes->set('oneqay.correlation_id', 'S41-HTTP-GENERIC');
    $genericResponse = $controller($generic, $target);
    $assert($genericResponse->getStatusCode() === 403, 'generic target rejection status changed');
    $payload = json_decode((string) $genericResponse->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $assert(($payload['error']['code'] ?? null) === 'IDENTITY_AUTHENTICATION_ELIGIBILITY_ADMINISTRATION_REJECTED', 'generic target rejection leaked internal reason');
}

$routeSource = (string) file_get_contents(__DIR__.'/../routes/web.php');
$providerSource = (string) file_get_contents(__DIR__.'/../app/Providers/AppServiceProvider.php');
$adapterSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityAdministrationRepository.php');
$serviceSource = (string) file_get_contents(__DIR__.'/../app/Application/Identity/FirstPartyIdentityEligibilityAdministrationService.php');
$migrationSource = (string) file_get_contents(__DIR__.'/../database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php');

$assert(substr_count($routeSource, "Route::post('/administration/identities/{identity_id}/authentication-disablement'") === 1, 'exactly one disable route required');
$assert(str_contains($routeSource, "->name('identity.authentication-eligibility.disable')"), 'disable route name missing');
$assert(str_contains($routeSource, "'session.active', 'throttle:5,1', 'throttle:20,60', RequirePolicyAdministrationSessionContextMiddleware::class"), 'disable route middleware contract missing');
$assert(! preg_match("#Route::(?:post|put|patch).*authentication-(?:enable|reactivat)#i", $routeSource), 'enable/reactivation route exists');
$assert(str_contains($providerSource, 'FirstPartyIdentityEligibilityAdministrationRepository::class'), 'repository binding missing');
$assert(str_contains($providerSource, 'FirstPartyIdentityEligibilityAdministrationService::class'), 'service binding missing');
$assert(str_contains($providerSource, 'PolicyAdministrationClock::class'), 'canonical administration clock not reused');
$assert(str_contains($adapterSource, "private const JOURNAL_TABLE = 'oneqay_identity_authentication_eligibility_mutations';"), 'journal table constant missing');
$assert(str_contains($adapterSource, "->where('first_party_authentication_enabled', 1)"), 'conditional true-to-false update guard missing');
$assert(str_contains($adapterSource, "->update(['first_party_authentication_enabled' => false])"), 'disable-only update missing');
$assert(! str_contains($adapterSource, "->update(['first_party_authentication_enabled' => true])"), 'reactivation update exists');
$assert(! preg_match('/\b(enable|reactivate)\s*\(/i', $serviceSource), 'service exposes reactivation method');
$assert(substr_count($migrationSource, "Schema::create('oneqay_identity_authentication_eligibility_mutations'") === 1, 'journal migration table count');
$assert(str_contains($migrationSource, "throw new LogicException('Forward-only generated migration; rollback is not authorized.');"), 'migration rollback denial missing');

$assert((int) config('oneqay.session_control.idle_ttl_seconds') === 7200, 'idle TTL changed');
$assert((int) config('oneqay.session_control.absolute_ttl_seconds') === 43200, 'absolute TTL changed');

$manager->disconnect('s41_identity_eligibility_admin');
$manager->purge('s41_identity_eligibility_admin');
$app['config']->set('database.connections.s41_identity_eligibility_admin', null);
@unlink($dbPath);
$removeTree($workspace);
$assert(! file_exists($workspace), 'workspace cleanup');

fwrite(STDOUT, "Sprint41 first-party identity authentication eligibility administration regression passed.\n");

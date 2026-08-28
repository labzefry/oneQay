<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationRepository;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationService;
use App\Application\Identity\FirstPartyIdentityEligibilityAdministrationViolation;
use App\Application\Identity\IdentityAuthenticationEligibilityMutationId;
use App\Application\Organization\VerifiedOrganizationalContext;
use App\Application\Persistence\DurablePersistenceViolation;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Organization\OrganizationId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityDisablementSessionTerminationRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityAdministrationRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityVerifier;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('u', 32));
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
$app->instance('request', Request::create('/'));
/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException('Sprint42 identity disablement session termination regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s42-disablement-session-termination-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'disablement-session-termination.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's42_disablement_session_termination');
$app['config']->set('database.connections.s42_disablement_session_termination', [
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
$manager->purge('s42_disablement_session_termination');
$manager->setDefaultConnection('s42_disablement_session_termination');
$connection = $manager->connection('s42_disablement_session_termination');
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
$assert($migrations === $expectedMigrations, 'migration set must remain exactly #1-#15');
$assert(! array_filter($migrations, static fn (string $file): bool => str_contains($file, '000016')), 'migration #16 exists');
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

foreach (['tenant-alpha', 'tenant-beta'] as $tenant) {
    $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
}
foreach ([
    ['tenant-alpha', 'admin-alpha'],
    ['tenant-alpha', 'target-applied'],
    ['tenant-alpha', 'target-disabled'],
    ['tenant-alpha', 'target-conflict'],
    ['tenant-alpha', 'target-rollback'],
    ['tenant-alpha', 'other-alpha'],
    ['tenant-beta', 'target-applied'],
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
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'admin-alpha',
    'role_id' => $controlRole,
]);
$connection->table('oneqay_identities')
    ->where('tenant_id', 'tenant-alpha')
    ->where('id', 'target-disabled')
    ->update(['first_party_authentication_enabled' => false]);

$actor = new VerifiedOrganizationalContext(
    PlatformIdentityId::fromString('admin-alpha'),
    TenantId::fromString('tenant-alpha'),
    OrganizationId::fromString('organization-alpha'),
);

$clock = new class implements PolicyAdministrationClock {
    public int $now = 1787852400;
    public function nowUnix(): int { return $this->now; }
};

$insertSession = static function (
    string $tenant,
    string $identity,
    string $authorityId,
    string $handleCharacter,
    int $issuedAtUnix,
    int $expiresAtUnix,
    ?int $revokedAtUnix = null,
) use ($connection): void {
    $connection->table('oneqay_identity_first_party_sessions')->insert([
        'tenant_id' => $tenant,
        'authority_id' => $authorityId,
        'public_handle' => str_repeat($handleCharacter, 43),
        'identity_id' => $identity,
        'organization_id' => $tenant === 'tenant-alpha' ? 'organization-alpha' : 'organization-beta',
        'outlet_id' => null,
        'device_id' => null,
        'credential_epoch' => 0,
        'factor_epoch' => null,
        'issued_at_unix' => $issuedAtUnix,
        'last_seen_at_unix' => $issuedAtUnix,
        'expires_at_unix' => $expiresAtUnix,
        'revoked_at_unix' => $revokedAtUnix,
    ]);
};

$now = $clock->now;
$insertSession('tenant-alpha', 'admin-alpha', str_repeat('a', 32), 'A', $now - 100, $now + 5000);
$insertSession('tenant-alpha', 'target-applied', str_repeat('b', 32), 'B', $now - 90, $now + 5000);
$insertSession('tenant-alpha', 'target-applied', str_repeat('c', 32), 'C', $now - 80, $now + 4000);
$insertSession('tenant-alpha', 'target-applied', str_repeat('d', 32), 'D', $now - 5000, $now - 1);
$insertSession('tenant-alpha', 'target-applied', str_repeat('e', 32), 'E', $now - 200, $now + 3000, $now - 150);
$insertSession('tenant-alpha', 'other-alpha', str_repeat('f', 32), 'F', $now - 70, $now + 5000);
$insertSession('tenant-beta', 'target-applied', str_repeat('1', 32), 'G', $now - 60, $now + 5000);
$insertSession('tenant-alpha', 'target-disabled', str_repeat('2', 32), 'H', $now - 50, $now + 5000);
$insertSession('tenant-alpha', 'target-conflict', str_repeat('4', 32), 'J', $now - 40, $now + 5000);
$insertSession('tenant-alpha', 'target-rollback', str_repeat('5', 32), 'K', $now - 30, $now + 5000);

$administrationRepository = new LaravelFirstPartyIdentityEligibilityAdministrationRepository($connection, true, 'ci');
$terminationRepository = new LaravelFirstPartyIdentityDisablementSessionTerminationRepository($connection, true, 'ci', true);
$transaction = new LaravelPersistenceTransaction($connection, true, 'ci');
$service = new FirstPartyIdentityEligibilityAdministrationService(
    $administrationRepository,
    $terminationRepository,
    $transaction,
    $clock,
);
$verifier = new LaravelFirstPartyIdentityEligibilityVerifier($connection, true, 'ci', true);

$sessionState = static function (string $tenant, string $authorityId) use ($connection): ?int {
    $value = $connection->table('oneqay_identity_first_party_sessions')
        ->where('tenant_id', $tenant)
        ->where('authority_id', $authorityId)
        ->value('revoked_at_unix');

    return $value === null ? null : (int) $value;
};

$outcome = $service->disable(
    $actor,
    PlatformIdentityId::fromString('target-applied'),
    IdentityAuthenticationEligibilityMutationId::fromString('s42-disable-applied'),
);
$assert($outcome === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'fresh enabled target did not return applied');
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-applied')), 'fresh applied target remains eligible');
$assert($sessionState('tenant-alpha', str_repeat('b', 32)) === $now, 'first active target authority was not revoked at server timestamp');
$assert($sessionState('tenant-alpha', str_repeat('c', 32)) === $now, 'second active target authority was not revoked at server timestamp');
$assert($sessionState('tenant-alpha', str_repeat('d', 32)) === null, 'expired target authority was rewritten');
$assert($sessionState('tenant-alpha', str_repeat('e', 32)) === $now - 150, 'already-revoked target authority was rewritten');
$assert($sessionState('tenant-alpha', str_repeat('a', 32)) === null, 'administrator actor authority was revoked');
$assert($sessionState('tenant-alpha', str_repeat('f', 32)) === null, 'another same-tenant identity authority was revoked');
$assert($sessionState('tenant-beta', str_repeat('1', 32)) === null, 'same-text target identity in another tenant was revoked');
$assert($connection->table('oneqay_identity_first_party_session_audit')->count() === 0, 'Sprint42 inserted a self-service session audit event');

$journal = (array) $connection->table('oneqay_identity_authentication_eligibility_mutations')
    ->where('tenant_id', 'tenant-alpha')
    ->where('mutation_id', 's42-disable-applied')
    ->first();
$assert(($journal['outcome'] ?? null) === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'fresh applied journal outcome changed');
$assert(($journal['target_identity_id'] ?? null) === 'target-applied', 'fresh applied journal target changed');

$noChange = $service->disable(
    $actor,
    PlatformIdentityId::fromString('target-disabled'),
    IdentityAuthenticationEligibilityMutationId::fromString('s42-disable-no-change'),
);
$assert($noChange === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_NO_CHANGE, 'already-disabled target did not return no_change');
$assert($sessionState('tenant-alpha', str_repeat('2', 32)) === $now, 'fresh no_change did not revoke stale active target authority');
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-disabled')), 'no_change restored identity eligibility');
$assert($connection->table('oneqay_identity_authentication_eligibility_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 's42-disable-no-change')->value('outcome') === 'no_change', 'no_change journal outcome changed');

$clock->now += 20;
$replayNow = $clock->now;
$insertSession('tenant-alpha', 'target-applied', str_repeat('3', 32), 'I', $replayNow - 1, $replayNow + 5000);
$replay = $service->disable(
    $actor,
    PlatformIdentityId::fromString('target-applied'),
    IdentityAuthenticationEligibilityMutationId::fromString('s42-disable-applied'),
);
$assert($replay === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'exact replay did not preserve prior applied outcome');
$assert($sessionState('tenant-alpha', str_repeat('3', 32)) === $replayNow, 'exact replay did not revoke newly-stale active target authority');
$assert($connection->table('oneqay_identity_authentication_eligibility_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 's42-disable-applied')->count() === 1, 'exact replay duplicated Sprint41 journal evidence');

try {
    $service->disable(
        $actor,
        PlatformIdentityId::fromString('target-conflict'),
        IdentityAuthenticationEligibilityMutationId::fromString('s42-disable-applied'),
    );
    $assert(false, 'conflicting mutation replay was accepted');
} catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
    $assert($exception->errorCode === FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT, 'conflicting replay returned wrong violation');
}
$assert($sessionState('tenant-alpha', str_repeat('4', 32)) === null, 'conflicting replay performed session termination');
$assert($verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-conflict')), 'conflicting replay mutated target eligibility');

$connection->unprepared(<<<'SQL'
CREATE TRIGGER oneqay_s42_force_termination_failure
BEFORE UPDATE OF revoked_at_unix ON oneqay_identity_first_party_sessions
WHEN OLD.tenant_id = 'tenant-alpha' AND OLD.identity_id = 'target-rollback'
BEGIN
    SELECT RAISE(ABORT, 'forced Sprint42 termination failure');
END
SQL);

$clock->now += 20;
try {
    $service->disable(
        $actor,
        PlatformIdentityId::fromString('target-rollback'),
        IdentityAuthenticationEligibilityMutationId::fromString('s42-disable-rollback'),
    );
    $assert(false, 'termination storage failure was accepted');
} catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
    $assert($exception->errorCode === FirstPartyIdentityEligibilityAdministrationViolation::STORAGE_FAILURE, 'termination storage failure returned wrong violation');
}
$connection->unprepared('DROP TRIGGER oneqay_s42_force_termination_failure');
$assert($verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-rollback')), 'termination failure committed identity disablement');
$assert($connection->table('oneqay_identity_authentication_eligibility_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 's42-disable-rollback')->count() === 0, 'termination failure committed journal evidence');
$assert($sessionState('tenant-alpha', str_repeat('5', 32)) === null, 'termination failure partially revoked target authority');

$expectDurable = static function (
    LaravelFirstPartyIdentityDisablementSessionTerminationRepository $repository,
    int $timestamp,
    string $expectedCode,
    string $label,
) use ($assert): void {
    try {
        $repository->revokeActiveForIdentityDisablement(
            TenantId::fromString('tenant-alpha'),
            PlatformIdentityId::fromString('target-conflict'),
            $timestamp,
        );
        $assert(false, $label.' was accepted');
    } catch (DurablePersistenceViolation $exception) {
        $assert($exception->errorCode === $expectedCode, $label.' returned wrong durable violation');
    }
};

$expectDurable(
    new LaravelFirstPartyIdentityDisablementSessionTerminationRepository($connection, false, 'ci', true),
    $clock->now,
    DurablePersistenceViolation::PERSISTENCE_DISABLED,
    'persistence-disabled termination',
);
$expectDurable(
    new LaravelFirstPartyIdentityDisablementSessionTerminationRepository($connection, true, 'production', true),
    $clock->now,
    DurablePersistenceViolation::RUNTIME_DENIED,
    'production termination runtime',
);
$expectDurable(
    new LaravelFirstPartyIdentityDisablementSessionTerminationRepository($connection, true, 'ci', false),
    $clock->now,
    DurablePersistenceViolation::STORAGE_FAILURE,
    'session-control-disabled termination',
);
$expectDurable(
    new LaravelFirstPartyIdentityDisablementSessionTerminationRepository($connection, true, 'ci', true),
    0,
    DurablePersistenceViolation::RELATIONSHIP_CONFLICT,
    'invalid termination timestamp',
);
$assert($sessionState('tenant-alpha', str_repeat('4', 32)) === null, 'fail-closed boundary tests mutated target authority');

$routeSource = (string) file_get_contents(__DIR__.'/../routes/web.php');
$controllerSource = (string) file_get_contents(__DIR__.'/../app/Delivery/Http/Identity/FirstPartyIdentityEligibilityAdministrationController.php');
$contractSource = (string) file_get_contents(__DIR__.'/../app/Application/Identity/FirstPartyIdentityDisablementSessionTerminationRepository.php');
$adapterSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Identity/LaravelFirstPartyIdentityDisablementSessionTerminationRepository.php');
$serviceSource = (string) file_get_contents(__DIR__.'/../app/Application/Identity/FirstPartyIdentityEligibilityAdministrationService.php');
$providerSource = (string) file_get_contents(__DIR__.'/../app/Providers/AppServiceProvider.php');
$selfServiceContractSource = (string) file_get_contents(__DIR__.'/../app/Application/Identity/FirstPartySessionAuthorityRepository.php');

$assert(substr_count($routeSource, "Route::post('/administration/identities/{identity_id}/authentication-disablement'") === 1, 'public disablement route count changed');
$assert(str_contains($routeSource, "->name('identity.authentication-eligibility.disable')"), 'public disablement route name changed');
$assert(! preg_match("#Route::(?:post|put|patch|delete)\([^\n]*(terminate|disablement-session|session-termination)#i", $routeSource), 'Sprint42 added a public termination route');
$assert(str_contains($controllerSource, 'count($payload) !== 1'), 'Sprint41 exact payload cardinality changed');
$assert(str_contains($controllerSource, "array_key_exists('mutation_id', \$payload)"), 'Sprint41 mutation_id payload requirement changed');
foreach (['session_handle', 'session_id', 'terminate', 'revoke', 'force', 'tenant_id', 'organization_id', 'outlet_id', 'device_id'] as $forbiddenPayload) {
    $assert(! str_contains($controllerSource, "array_key_exists('".$forbiddenPayload."', \$payload)"), 'controller added unauthorized Sprint42 payload field '.$forbiddenPayload);
}

$assert(str_contains($contractSource, 'revokeActiveForIdentityDisablement('), 'dedicated termination contract operation missing');
foreach (['publicHandle', 'authorityId', 'organizationId', 'outletId', 'deviceId', 'reactivate'] as $forbiddenContract) {
    $assert(! str_contains($contractSource, $forbiddenContract), 'termination contract widened with '.$forbiddenContract);
}
$assert(str_contains($adapterSource, "private const SESSION_TABLE = 'oneqay_identity_first_party_sessions';"), 'termination adapter session table missing');
$assert(str_contains($adapterSource, "->where('tenant_id', \$tenantId->value())"), 'termination adapter tenant ownership guard missing');
$assert(str_contains($adapterSource, "->where('identity_id', \$targetIdentityId->value())"), 'termination adapter identity ownership guard missing');
$assert(str_contains($adapterSource, "->whereNull('revoked_at_unix')"), 'termination adapter active-revocation guard missing');
$assert(str_contains($adapterSource, "->where('expires_at_unix', '>=', \$revokedAtUnix)"), 'termination adapter expiry guard missing');
$assert(substr_count($adapterSource, "->update(['revoked_at_unix' => \$revokedAtUnix])") === 1, 'termination adapter update surface widened');
$assert(! str_contains($adapterSource, 'oneqay_identity_first_party_session_audit'), 'termination adapter writes self-service audit table');
$assert(! preg_match('/->(?:insert|insertOrIgnore|updateOrInsert|upsert|delete|truncate)\s*\(/', $adapterSource), 'termination adapter contains unauthorized storage mutation method');

$applyPosition = strpos($serviceSource, '$outcome = $this->repository->applyFresh(');
$terminationPosition = $applyPosition === false ? false : strpos($serviceSource, '$this->sessionTermination->revokeActiveForIdentityDisablement(', $applyPosition);
$assert($applyPosition !== false && $terminationPosition !== false && $applyPosition < $terminationPosition, 'fresh mutation ordering does not apply eligibility before session termination');
$assert(substr_count($serviceSource, 'return $this->transaction->run(function () use (') >= 2, 'fresh/replay termination is not enclosed by canonical transaction');
$assert(str_contains($providerSource, 'FirstPartyIdentityDisablementSessionTerminationRepository::class'), 'termination repository binding missing');
$assert(str_contains($providerSource, 'LaravelFirstPartyIdentityDisablementSessionTerminationRepository('), 'Laravel termination adapter binding missing');
$assert(str_contains($providerSource, '$this->sessionControlEnabled(),'), 'termination adapter did not reuse canonical session-control arm');
$assert(! str_contains($selfServiceContractSource, 'IdentityDisablementSessionTermination'), 'self-service session repository was widened');

$assert((int) config('oneqay.session_control.idle_ttl_seconds') === 7200, 'idle TTL changed');
$assert((int) config('oneqay.session_control.absolute_ttl_seconds') === 43200, 'absolute TTL changed');
$assert($connection->table('oneqay_identity_first_party_session_audit')->count() === 0, 'Sprint42 created session audit evidence');

$manager->disconnect('s42_disablement_session_termination');
$manager->purge('s42_disablement_session_termination');
$app['config']->set('database.connections.s42_disablement_session_termination', null);
@unlink($dbPath);
$removeTree($workspace);
$assert(! file_exists($workspace), 'workspace cleanup');

fwrite(STDOUT, "Sprint42 first-party identity disablement session termination regression passed.\n");

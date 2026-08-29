<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Authorization\PolicyAdministrationClock;
use App\Application\Identity\FirstPartyIdentityDisablementSessionTerminationRepository;
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
use App\Infrastructure\Identity\LaravelFirstPartyIdentityDisablementSessionTerminationRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityAdministrationRepository;
use App\Infrastructure\Identity\LaravelFirstPartyIdentityEligibilityVerifier;
use App\Infrastructure\Persistence\LaravelPersistenceTransaction;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('v', 32));
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
        throw new RuntimeException('Sprint43 identity authentication eligibility reactivation regression failed: '.$message);
    }
};

$expectViolation = static function (callable $operation, string $code, string $message) use ($assert): void {
    try {
        $operation();
        $assert(false, $message.' was accepted');
    } catch (FirstPartyIdentityEligibilityAdministrationViolation $exception) {
        $assert($exception->errorCode === $code, $message.' returned wrong violation');
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s43-identity-reactivation-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'identity-reactivation.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's43_identity_reactivation');
$app['config']->set('database.connections.s43_identity_reactivation', [
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
$manager->purge('s43_identity_reactivation');
$manager->setDefaultConnection('s43_identity_reactivation');
$connection = $manager->connection('s43_identity_reactivation');
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

$alphaIdentities = [
    'admin-alpha',
    'no-authority-alpha',
    'protected-alpha',
    'target-disabled',
    'target-already-enabled',
    'target-converge',
    'target-operation-disable',
    'target-operation-reactivate',
    'target-replay-no-rewrite',
    'target-disable-session',
    'target-http',
    'target-http-invalid',
    'target-malformed',
    'target-rollback',
    'other-alpha',
];
foreach ($alphaIdentities as $identity) {
    $connection->table('oneqay_identities')->insert([
        'tenant_id' => 'tenant-alpha',
        'id' => $identity,
    ]);
}
$connection->table('oneqay_identities')->insert([
    'tenant_id' => 'tenant-beta',
    'id' => 'target-beta',
]);

foreach ([
    ['tenant-alpha', 'organization-alpha'],
    ['tenant-beta', 'organization-beta'],
] as [$tenant, $organization]) {
    $connection->table('oneqay_organizations')->insert([
        'tenant_id' => $tenant,
        'id' => $organization,
    ]);
}

foreach ($alphaIdentities as $identity) {
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => 'tenant-alpha',
        'identity_id' => $identity,
        'organization_id' => 'organization-alpha',
    ]);
}
$connection->table('oneqay_identity_organizations')->insert([
    'tenant_id' => 'tenant-beta',
    'identity_id' => 'target-beta',
    'organization_id' => 'organization-beta',
]);

$connection->table('oneqay_outlets')->insert([
    'tenant_id' => 'tenant-alpha',
    'id' => 'outlet-alpha',
    'organization_id' => 'organization-alpha',
]);
$connection->table('oneqay_devices')->insert([
    'tenant_id' => 'tenant-alpha',
    'id' => 'device-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
]);
$connection->table('oneqay_outlet_access_grants')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-disabled',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
]);
$connection->table('oneqay_device_access_grants')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-disabled',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
]);

$controlRole = InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE;
$ordinaryRole = 'ordinary-role';
$connection->table('oneqay_roles')->insert([
    ['tenant_id' => 'tenant-alpha', 'id' => $controlRole],
    ['tenant_id' => 'tenant-alpha', 'id' => $ordinaryRole],
]);
$connection->table('oneqay_role_permissions')->insert([
    ['tenant_id' => 'tenant-alpha', 'role_id' => $controlRole, 'permission_id' => AdministrationPermission::MANAGE],
    ['tenant_id' => 'tenant-alpha', 'role_id' => $ordinaryRole, 'permission_id' => 'inventory.read'],
]);
$connection->table('oneqay_tenant_role_assignments')->insert([
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'admin-alpha', 'role_id' => $controlRole],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'protected-alpha', 'role_id' => $controlRole],
    ['tenant_id' => 'tenant-alpha', 'identity_id' => 'target-disabled', 'role_id' => $ordinaryRole],
]);
$connection->table('oneqay_organization_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-disabled',
    'organization_id' => 'organization-alpha',
    'role_id' => $ordinaryRole,
]);
$connection->table('oneqay_outlet_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-disabled',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'role_id' => $ordinaryRole,
]);
$connection->table('oneqay_device_role_assignments')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-disabled',
    'organization_id' => 'organization-alpha',
    'outlet_id' => 'outlet-alpha',
    'device_id' => 'device-alpha',
    'role_id' => $ordinaryRole,
]);

$passwordHash = password_hash('Sprint43 preserved target password', PASSWORD_BCRYPT);
$assert(is_string($passwordHash), 'password hash setup');
$connection->table('oneqay_identity_password_credentials')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-disabled',
    'password_hash' => $passwordHash,
    'credential_epoch' => 7,
]);
$connection->table('oneqay_identity_totp_factors')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'target-disabled',
    'secret_ciphertext' => 's43-preserved-totp-ciphertext',
    'created_at_unix' => 1787850000,
    'confirmed_at_unix' => 1787850100,
    'last_accepted_time_step' => 123456,
    'factor_epoch' => 4,
]);
$connection->table('oneqay_identity_recovery_codes')->insert([
    'tenant_id' => 'tenant-alpha',
    'code_id' => str_repeat('c', 32),
    'identity_id' => 'target-disabled',
    'code_selector' => str_repeat('s', 22),
    'secret_digest' => str_repeat('d', 64),
    'issued_at_unix' => 1787850200,
    'consumed_at_unix' => null,
    'revoked_at_unix' => null,
]);
$connection->table('oneqay_identity_totp_recovery_codes')->insert([
    'tenant_id' => 'tenant-alpha',
    'code_id' => str_repeat('t', 32),
    'identity_id' => 'target-disabled',
    'factor_epoch' => 4,
    'code_selector' => str_repeat('r', 22),
    'secret_digest' => str_repeat('e', 64),
    'issued_at_unix' => 1787850300,
    'consumed_at_unix' => null,
    'revoked_at_unix' => null,
]);

foreach ([
    'target-disabled',
    'target-converge',
    'target-operation-reactivate',
    'target-replay-no-rewrite',
    'target-http',
    'target-http-invalid',
    'target-malformed',
    'target-rollback',
] as $disabledIdentity) {
    $connection->table('oneqay_identities')
        ->where('tenant_id', 'tenant-alpha')
        ->where('id', $disabledIdentity)
        ->update(['first_party_authentication_enabled' => false]);
}

$clock = new class implements PolicyAdministrationClock {
    public int $now = 1787856000;
    public function nowUnix(): int { return $this->now; }
};

$insertSession = static function (
    string $identity,
    string $authorityCharacter,
    string $handleCharacter,
    int $issuedAtUnix,
    int $lastSeenAtUnix,
    int $expiresAtUnix,
    ?int $revokedAtUnix = null,
) use ($connection): void {
    $connection->table('oneqay_identity_first_party_sessions')->insert([
        'tenant_id' => 'tenant-alpha',
        'authority_id' => str_repeat($authorityCharacter, 32),
        'public_handle' => str_repeat($handleCharacter, 43),
        'identity_id' => $identity,
        'organization_id' => 'organization-alpha',
        'outlet_id' => null,
        'device_id' => null,
        'credential_epoch' => 7,
        'factor_epoch' => 4,
        'issued_at_unix' => $issuedAtUnix,
        'last_seen_at_unix' => $lastSeenAtUnix,
        'expires_at_unix' => $expiresAtUnix,
        'revoked_at_unix' => $revokedAtUnix,
    ]);
};

$now = $clock->now;
$insertSession('target-disabled', 'a', 'A', $now - 500, $now - 400, $now + 5000, $now - 300);
$insertSession('target-disabled', 'b', 'B', $now - 10000, $now - 9000, $now - 1);
$insertSession('admin-alpha', 'c', 'C', $now - 100, $now - 50, $now + 5000);
$insertSession('target-disable-session', 'd', 'D', $now - 80, $now - 40, $now + 5000);

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

$administrationRepository = new LaravelFirstPartyIdentityEligibilityAdministrationRepository($connection, true, 'ci');
$realTermination = new LaravelFirstPartyIdentityDisablementSessionTerminationRepository($connection, true, 'ci', true);
$termination = new class($realTermination) implements FirstPartyIdentityDisablementSessionTerminationRepository {
    public int $calls = 0;

    public function __construct(
        private readonly LaravelFirstPartyIdentityDisablementSessionTerminationRepository $delegate,
    ) {}

    public function revokeActiveForIdentityDisablement(
        TenantId $tenantId,
        PlatformIdentityId $targetIdentityId,
        int $revokedAtUnix,
    ): int {
        $this->calls++;

        return $this->delegate->revokeActiveForIdentityDisablement(
            $tenantId,
            $targetIdentityId,
            $revokedAtUnix,
        );
    }
};
$transaction = new LaravelPersistenceTransaction($connection, true, 'ci');
$service = new FirstPartyIdentityEligibilityAdministrationService(
    $administrationRepository,
    $termination,
    $transaction,
    $clock,
);
$verifier = new LaravelFirstPartyIdentityEligibilityVerifier($connection, true, 'ci', true);

$snapshotTable = static function (string $table, array $orderBy) use ($connection): string {
    $query = $connection->table($table);
    foreach ($orderBy as $column) {
        $query->orderBy($column);
    }

    return json_encode(
        $query->get()->map(static fn (object $row): array => (array) $row)->all(),
        JSON_THROW_ON_ERROR,
    );
};

$securitySnapshots = [];
foreach ([
    'oneqay_identity_password_credentials' => ['tenant_id', 'identity_id'],
    'oneqay_identity_totp_factors' => ['tenant_id', 'identity_id'],
    'oneqay_identity_recovery_codes' => ['tenant_id', 'code_id'],
    'oneqay_identity_totp_recovery_codes' => ['tenant_id', 'code_id'],
    'oneqay_identity_organizations' => ['tenant_id', 'identity_id', 'organization_id'],
    'oneqay_outlet_access_grants' => ['tenant_id', 'identity_id', 'organization_id', 'outlet_id'],
    'oneqay_device_access_grants' => ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id'],
    'oneqay_role_permissions' => ['tenant_id', 'role_id', 'permission_id'],
    'oneqay_tenant_role_assignments' => ['tenant_id', 'identity_id', 'role_id'],
    'oneqay_organization_role_assignments' => ['tenant_id', 'identity_id', 'organization_id', 'role_id'],
    'oneqay_outlet_role_assignments' => ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'role_id'],
    'oneqay_device_role_assignments' => ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id', 'role_id'],
] as $table => $orderBy) {
    $securitySnapshots[$table] = $snapshotTable($table, $orderBy);
}
$sessionSnapshotBeforeReactivation = $snapshotTable('oneqay_identity_first_party_sessions', ['tenant_id', 'authority_id']);
$sessionAuditBefore = $snapshotTable('oneqay_identity_first_party_session_audit', ['tenant_id', 'audit_id']);

$outcome = $service->reactivate(
    $actor,
    PlatformIdentityId::fromString('target-disabled'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-reactivate-applied'),
);
$assert($outcome === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'disabled target did not return applied');
$assert($verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-disabled')), 'reactivated target is not eligible');
$assert($termination->calls === 0, 'reactivation invoked Sprint42 session termination');

$journal = (array) $connection->table('oneqay_identity_authentication_eligibility_mutations')
    ->where('tenant_id', 'tenant-alpha')
    ->where('mutation_id', 's43-reactivate-applied')
    ->first();
$expectedFingerprint = hash('sha256', implode("\n", [
    'tenant-alpha',
    'admin-alpha',
    'target-disabled',
    'reactivate',
    AdministrationPermission::MANAGE,
    'tenant',
]));
$assert(($journal['operation'] ?? null) === FirstPartyIdentityEligibilityAdministrationRepository::OPERATION_REACTIVATE, 'reactivation journal operation changed');
$assert(($journal['outcome'] ?? null) === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'reactivation journal outcome changed');
$assert(($journal['actor_identity_id'] ?? null) === 'admin-alpha', 'reactivation journal actor changed');
$assert(($journal['target_identity_id'] ?? null) === 'target-disabled', 'reactivation journal target changed');
$assert(($journal['payload_fingerprint'] ?? null) === $expectedFingerprint, 'reactivation fingerprint binding changed');

$replay = $service->reactivate(
    $actor,
    PlatformIdentityId::fromString('target-disabled'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-reactivate-applied'),
);
$assert($replay === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'exact reactivation replay changed prior outcome');
$assert($connection->table('oneqay_identity_authentication_eligibility_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 's43-reactivate-applied')->count() === 1, 'exact replay duplicated journal evidence');
$assert($termination->calls === 0, 'exact reactivation replay invoked Sprint42 termination');

$alreadyEnabled = $service->reactivate(
    $actor,
    PlatformIdentityId::fromString('target-already-enabled'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-reactivate-no-change'),
);
$assert($alreadyEnabled === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_NO_CHANGE, 'already-enabled target did not return no_change');
$assert($verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-already-enabled')), 'no_change target became ineligible');

$convergenceOne = $service->reactivate(
    $actor,
    PlatformIdentityId::fromString('target-converge'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-converge-one'),
);
$convergenceTwo = $service->reactivate(
    $actor,
    PlatformIdentityId::fromString('target-converge'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-converge-two'),
);
$assert(
    count(array_filter([$convergenceOne, $convergenceTwo], static fn (string $value): bool => $value === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED)) === 1,
    'distinct mutation convergence produced more or less than one applied outcome',
);
$assert(
    count(array_filter([$convergenceOne, $convergenceTwo], static fn (string $value): bool => $value === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_NO_CHANGE)) === 1,
    'distinct mutation convergence did not settle remaining mutation as no_change',
);
$assert($verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-converge')), 'convergent reactivation did not end enabled');

$expectViolation(
    fn () => $service->reactivate(
        $actor,
        PlatformIdentityId::fromString('target-already-enabled'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-reactivate-applied'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT,
    'reactivation mutation identifier reuse with different target',
);
$expectViolation(
    fn () => $service->reactivate(
        $noAuthorityActor,
        PlatformIdentityId::fromString('target-already-enabled'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-unauthorized'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::AUTHORIZATION_DENIED,
    'unauthorized actor reactivation',
);
$expectViolation(
    fn () => $service->reactivate(
        $actor,
        PlatformIdentityId::fromString('admin-alpha'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-self'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
    'self reactivation',
);
$expectViolation(
    fn () => $service->reactivate(
        $actor,
        PlatformIdentityId::fromString('protected-alpha'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-protected'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::PROTECTED_TARGET,
    'protected-control reactivation',
);
$expectViolation(
    fn () => $service->reactivate(
        $actor,
        PlatformIdentityId::fromString('target-beta'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-cross-tenant'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
    'cross-tenant reactivation',
);
$expectViolation(
    fn () => $service->reactivate(
        $actor,
        PlatformIdentityId::fromString('missing-alpha'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-missing'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::TARGET_IDENTITY_DENIED,
    'missing target reactivation',
);

$expectViolation(
    fn () => (new FirstPartyIdentityEligibilityAdministrationService(
        new LaravelFirstPartyIdentityEligibilityAdministrationRepository($connection, false, 'ci'),
        $termination,
        $transaction,
        $clock,
    ))->reactivate(
        $actor,
        PlatformIdentityId::fromString('target-already-enabled'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-persistence-disabled'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::PERSISTENCE_DISABLED,
    'persistence-disabled reactivation',
);
$expectViolation(
    fn () => (new FirstPartyIdentityEligibilityAdministrationService(
        new LaravelFirstPartyIdentityEligibilityAdministrationRepository($connection, true, 'production'),
        $termination,
        $transaction,
        $clock,
    ))->reactivate(
        $actor,
        PlatformIdentityId::fromString('target-already-enabled'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-production-denied'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::RUNTIME_DENIED,
    'production reactivation runtime',
);

$malformedFingerprint = hash('sha256', implode("\n", [
    'tenant-alpha',
    'admin-alpha',
    'target-malformed',
    'reactivate',
    AdministrationPermission::MANAGE,
    'tenant',
]));
$connection->table('oneqay_identity_authentication_eligibility_mutations')->insert([
    'tenant_id' => 'tenant-alpha',
    'mutation_id' => 's43-malformed-stored',
    'actor_identity_id' => 'other-alpha',
    'target_identity_id' => 'target-malformed',
    'operation' => 'reactivate',
    'payload_fingerprint' => $malformedFingerprint,
    'outcome' => 'no_change',
    'occurred_at_unix' => $clock->now,
]);
$expectViolation(
    fn () => $service->reactivate(
        $actor,
        PlatformIdentityId::fromString('target-malformed'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-malformed-stored'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT,
    'malformed stored reactivation evidence',
);
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-malformed')), 'malformed replay restored target state');

$connection->unprepared(<<<'SQL'
CREATE TRIGGER oneqay_s43_force_reactivation_failure
BEFORE UPDATE OF first_party_authentication_enabled ON oneqay_identities
WHEN OLD.tenant_id = 'tenant-alpha' AND OLD.id = 'target-rollback'
BEGIN
    SELECT RAISE(ABORT, 'forced Sprint43 reactivation failure');
END
SQL);
$expectViolation(
    fn () => $service->reactivate(
        $actor,
        PlatformIdentityId::fromString('target-rollback'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-rollback'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::STORAGE_FAILURE,
    'reactivation storage failure',
);
$connection->unprepared('DROP TRIGGER oneqay_s43_force_reactivation_failure');
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-rollback')), 'reactivation failure committed eligibility');
$assert($connection->table('oneqay_identity_authentication_eligibility_mutations')->where('tenant_id', 'tenant-alpha')->where('mutation_id', 's43-rollback')->count() === 0, 'reactivation failure committed journal evidence');

$assert($snapshotTable('oneqay_identity_first_party_sessions', ['tenant_id', 'authority_id']) === $sessionSnapshotBeforeReactivation, 'reactivation or denied reactivation mutated logical sessions');
$assert($snapshotTable('oneqay_identity_first_party_session_audit', ['tenant_id', 'audit_id']) === $sessionAuditBefore, 'reactivation wrote session audit evidence');
$assert($termination->calls === 0, 'reactivation paths invoked session termination');
$activeTargetSessions = $connection->table('oneqay_identity_first_party_sessions')
    ->where('tenant_id', 'tenant-alpha')
    ->where('identity_id', 'target-disabled')
    ->whereNull('revoked_at_unix')
    ->where('expires_at_unix', '>=', $clock->now)
    ->count();
$assert($activeTargetSessions === 0, 'reactivation created or revived active logical session authority');

foreach ($securitySnapshots as $table => $snapshot) {
    $orderBy = match ($table) {
        'oneqay_identity_password_credentials', 'oneqay_identity_totp_factors' => ['tenant_id', 'identity_id'],
        'oneqay_identity_recovery_codes', 'oneqay_identity_totp_recovery_codes' => ['tenant_id', 'code_id'],
        'oneqay_identity_organizations' => ['tenant_id', 'identity_id', 'organization_id'],
        'oneqay_outlet_access_grants' => ['tenant_id', 'identity_id', 'organization_id', 'outlet_id'],
        'oneqay_device_access_grants' => ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id'],
        'oneqay_role_permissions' => ['tenant_id', 'role_id', 'permission_id'],
        'oneqay_tenant_role_assignments' => ['tenant_id', 'identity_id', 'role_id'],
        'oneqay_organization_role_assignments' => ['tenant_id', 'identity_id', 'organization_id', 'role_id'],
        'oneqay_outlet_role_assignments' => ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'role_id'],
        'oneqay_device_role_assignments' => ['tenant_id', 'identity_id', 'organization_id', 'outlet_id', 'device_id', 'role_id'],
        default => throw new RuntimeException('Unexpected security snapshot table.'),
    };
    $assert($snapshotTable($table, $orderBy) === $snapshot, 'reactivation mutated preserved security table '.$table);
}

foreach ($connection->table('oneqay_identity_authentication_eligibility_mutations')->get() as $rowObject) {
    $row = (array) $rowObject;
    $assert(in_array($row['operation'] ?? null, ['disable', 'reactivate'], true), 'journal operation vocabulary widened');
    $assert(in_array($row['outcome'] ?? null, ['applied', 'no_change'], true), 'journal outcome invalid');
    $assert(is_string($row['payload_fingerprint'] ?? null) && preg_match('/\A[a-f0-9]{64}\z/', $row['payload_fingerprint']) === 1, 'journal fingerprint invalid');
    $encoded = strtolower(json_encode($row, JSON_THROW_ON_ERROR));
    foreach ([
        strtolower($testKey),
        'sprint43 preserved target password',
        's43-preserved-totp-ciphertext',
        str_repeat('d', 64),
        str_repeat('e', 64),
        'csrf',
    ] as $forbidden) {
        $assert(! str_contains($encoded, $forbidden), 'journal leaked restricted evidence');
    }
}

$disableFirst = $service->disable(
    $actor,
    PlatformIdentityId::fromString('target-operation-disable'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-operation-disable-first'),
);
$assert($disableFirst === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'disable setup for operation conflict did not apply');
$expectViolation(
    fn () => $service->reactivate(
        $actor,
        PlatformIdentityId::fromString('target-operation-disable'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-operation-disable-first'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT,
    'disable mutation identifier reused for reactivation',
);
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-operation-disable')), 'conflicting disable-to-reactivate replay changed target');

$reactivateFirst = $service->reactivate(
    $actor,
    PlatformIdentityId::fromString('target-operation-reactivate'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-operation-reactivate-first'),
);
$assert($reactivateFirst === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'reactivation setup for reverse conflict did not apply');
$terminationCallsBeforeReverseConflict = $termination->calls;
$expectViolation(
    fn () => $service->disable(
        $actor,
        PlatformIdentityId::fromString('target-operation-reactivate'),
        IdentityAuthenticationEligibilityMutationId::fromString('s43-operation-reactivate-first'),
    ),
    FirstPartyIdentityEligibilityAdministrationViolation::MUTATION_CONFLICT,
    'reactivation mutation identifier reused for disablement',
);
$assert($termination->calls === $terminationCallsBeforeReverseConflict, 'conflicting reactivation-to-disable replay invoked session termination');
$assert($verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-operation-reactivate')), 'reverse conflict disabled target');

$replayOriginal = $service->reactivate(
    $actor,
    PlatformIdentityId::fromString('target-replay-no-rewrite'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-replay-no-rewrite'),
);
$assert($replayOriginal === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'replay-no-rewrite setup did not reactivate');
$service->disable(
    $actor,
    PlatformIdentityId::fromString('target-replay-no-rewrite'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-replay-later-disable'),
);
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-replay-no-rewrite')), 'later disable did not make replay target ineligible');
$replayedPrior = $service->reactivate(
    $actor,
    PlatformIdentityId::fromString('target-replay-no-rewrite'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-replay-no-rewrite'),
);
$assert($replayedPrior === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'exact replay did not return prior outcome after later disable');
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-replay-no-rewrite')), 'exact replay rewrote current target state');

$disableSessionOutcome = $service->disable(
    $actor,
    PlatformIdentityId::fromString('target-disable-session'),
    IdentityAuthenticationEligibilityMutationId::fromString('s43-preserve-s42-disable'),
);
$assert($disableSessionOutcome === FirstPartyIdentityEligibilityAdministrationRepository::OUTCOME_APPLIED, 'Sprint41 disablement integration did not apply');
$assert(
    (int) $connection->table('oneqay_identity_first_party_sessions')
        ->where('tenant_id', 'tenant-alpha')
        ->where('authority_id', str_repeat('d', 32))
        ->value('revoked_at_unix') === $clock->now,
    'Sprint41 disablement no longer terminates active target session through Sprint42',
);

$contextStore = new class($actor) implements OrganizationalContextStore {
    public function __construct(private ?VerifiedOrganizationalContext $context) {}
    public function current(): ?VerifiedOrganizationalContext { return $this->context; }
    public function setVerified(VerifiedOrganizationalContext $context): void { $this->context = $context; }
    public function clear(): void { $this->context = null; }
};
$controller = new FirstPartyIdentityEligibilityAdministrationController($contextStore, $service);

$httpRequest = Request::create(
    '/administration/identities/target-http/authentication-reactivation',
    'POST',
    ['mutation_id' => 's43-http-reactivate'],
);
$httpRequest->attributes->set('oneqay.correlation_id', 'S43-HTTP-0001');
$httpResponse = $controller->reactivate($httpRequest, 'target-http');
$assert($httpResponse->getStatusCode() === 200, 'reactivation controller positive status changed');
$httpPayload = json_decode((string) $httpResponse->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(array_keys($httpPayload) === ['status', 'outcome', 'correlation_id'], 'reactivation response surface widened');
$assert(($httpPayload['status'] ?? null) === 'ok', 'reactivation response status changed');
$assert(($httpPayload['outcome'] ?? null) === 'applied', 'reactivation controller outcome changed');
$assert(($httpPayload['correlation_id'] ?? null) === 'S43-HTTP-0001', 'reactivation correlation id changed');
$assert(! $httpResponse->headers->has('Set-Cookie'), 'reactivation response created a framework session cookie');

$invalidExtra = Request::create(
    '/administration/identities/target-http-invalid/authentication-reactivation',
    'POST',
    ['mutation_id' => 's43-http-extra', 'operation' => 'reactivate'],
);
$invalidExtra->attributes->set('oneqay.correlation_id', 'S43-HTTP-0002');
$invalidExtraResponse = $controller->reactivate($invalidExtra, 'target-http-invalid');
$assert($invalidExtraResponse->getStatusCode() === 422, 'caller-selected operation field was accepted');
$assert(! $verifier->isEligible(TenantId::fromString('tenant-alpha'), PlatformIdentityId::fromString('target-http-invalid')), 'invalid extra payload mutated target');

$invalidMutation = Request::create(
    '/administration/identities/target-http-invalid/authentication-reactivation',
    'POST',
    ['mutation_id' => '!!!'],
);
$invalidMutation->attributes->set('oneqay.correlation_id', 'S43-HTTP-0003');
$invalidMutationResponse = $controller->reactivate($invalidMutation, 'target-http-invalid');
$assert($invalidMutationResponse->getStatusCode() === 422, 'malformed mutation identifier was accepted');

$protectedRequest = Request::create(
    '/administration/identities/protected-alpha/authentication-reactivation',
    'POST',
    ['mutation_id' => 's43-http-protected'],
);
$protectedRequest->attributes->set('oneqay.correlation_id', 'S43-HTTP-0004');
$protectedResponse = $controller->reactivate($protectedRequest, 'protected-alpha');
$assert($protectedResponse->getStatusCode() === 403, 'protected target public rejection status changed');
$protectedPayload = json_decode((string) $protectedResponse->getContent(), true, flags: JSON_THROW_ON_ERROR);
$assert(($protectedPayload['error']['code'] ?? null) === 'IDENTITY_AUTHENTICATION_ELIGIBILITY_ADMINISTRATION_REJECTED', 'protected target leaked internal rejection reason');
$assert(! str_contains((string) $protectedResponse->getContent(), 'protected-alpha'), 'protected target identifier leaked in rejection body');

$routeSource = (string) file_get_contents(__DIR__.'/../routes/web.php');
$contractSource = (string) file_get_contents(__DIR__.'/../app/Application/Identity/FirstPartyIdentityEligibilityAdministrationRepository.php');
$serviceSource = (string) file_get_contents(__DIR__.'/../app/Application/Identity/FirstPartyIdentityEligibilityAdministrationService.php');
$controllerSource = (string) file_get_contents(__DIR__.'/../app/Delivery/Http/Identity/FirstPartyIdentityEligibilityAdministrationController.php');
$adapterSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityAdministrationRepository.php');
$terminationContractSource = (string) file_get_contents(__DIR__.'/../app/Application/Identity/FirstPartyIdentityDisablementSessionTerminationRepository.php');
$terminationAdapterSource = (string) file_get_contents(__DIR__.'/../app/Infrastructure/Identity/LaravelFirstPartyIdentityDisablementSessionTerminationRepository.php');

$assert(substr_count($routeSource, "Route::post('/administration/identities/{identity_id}/authentication-disablement'") === 1, 'disable route count changed');
$assert(substr_count($routeSource, "Route::post('/administration/identities/{identity_id}/authentication-reactivation'") === 1, 'reactivation route count changed');
$assert(str_contains($routeSource, "->name('identity.authentication-eligibility.disable')"), 'disable route name changed');
$assert(str_contains($routeSource, "->name('identity.authentication-eligibility.reactivate')"), 'reactivation route name missing');
$assert(substr_count($routeSource, "'session.active', 'throttle:5,1', 'throttle:20,60', RequirePolicyAdministrationSessionContextMiddleware::class") >= 2, 'reactivation did not reuse protected administration middleware');
$assert(! preg_match('#authentication-(?:enablement|toggle)#i', $routeSource), 'generic reactivation route vocabulary exists');

$assert(str_contains($contractSource, "public const OPERATION_DISABLE = 'disable';"), 'disable operation vocabulary changed');
$assert(str_contains($contractSource, "public const OPERATION_REACTIVATE = 'reactivate';"), 'reactivation operation vocabulary missing');
$assert(str_contains($contractSource, 'public function replayReactivationOutcome('), 'dedicated reactivation replay contract missing');
$assert(str_contains($contractSource, 'public function applyFreshReactivation('), 'dedicated fresh reactivation contract missing');
$assert(substr_count($serviceSource, 'public function disable(') === 1, 'disable service operation count changed');
$assert(substr_count($serviceSource, 'public function reactivate(') === 1, 'reactivation service operation count changed');
$assert(substr_count($serviceSource, '$this->sessionTermination->revokeActiveForIdentityDisablement(') === 2, 'reactivation widened session termination composition');
$assert(substr_count($controllerSource, 'count($payload) !== 1') === 2, 'controller payload cardinality enforcement changed');
$assert(substr_count($controllerSource, "array_key_exists('mutation_id', \$payload)") === 2, 'controller mutation_id-only parsing changed');

$assert(str_contains($adapterSource, "->where('first_party_authentication_enabled', 0)"), 'reactivation conditional disabled-state guard missing');
$assert(substr_count($adapterSource, "->update(['first_party_authentication_enabled' => true])") === 1, 'reactivation true update surface widened');
$assert(substr_count($adapterSource, "->update(['first_party_authentication_enabled' => false])") === 1, 'disable false update surface changed');
$assert(! str_contains($adapterSource, 'oneqay_identity_first_party_sessions'), 'reactivation adapter touches session persistence');
$assert(! str_contains($adapterSource, 'revoked_at_unix'), 'reactivation adapter touches session revocation evidence');
$assert(! preg_match('/\b(toggle|setEligibility|setEnabled)\s*\(/i', $contractSource.$serviceSource.$adapterSource), 'generic eligibility mutation method exists');
$assert(! str_contains($terminationContractSource, 'reactivate'), 'Sprint42 termination contract widened for reactivation');
$assert(! str_contains($terminationAdapterSource, 'reactivate'), 'Sprint42 termination adapter widened for reactivation');

$assert((int) config('oneqay.session_control.idle_ttl_seconds') === 7200, 'idle TTL changed');
$assert((int) config('oneqay.session_control.absolute_ttl_seconds') === 43200, 'absolute TTL changed');

$manager->disconnect('s43_identity_reactivation');
$manager->purge('s43_identity_reactivation');
$app['config']->set('database.connections.s43_identity_reactivation', null);
@unlink($dbPath);
$removeTree($workspace);
$assert(! file_exists($workspace), 'workspace cleanup');

fwrite(STDOUT, "Sprint43 first-party identity authentication eligibility reactivation regression passed.\n");

<?php

declare(strict_types=1);

use App\Application\Identity\FirstPartyIdentityCredentialVerifier;
use App\Application\Identity\IssuedRecoveryCodeSet;
use App\Application\Identity\RecoveryCodeClock;
use App\Application\Identity\RecoveryCodeRepository;
use App\Application\Identity\RecoveryCodeService;
use App\Application\Identity\RecoveryCodeViolation;
use App\Application\Identity\VerifyFirstPartyIdentityCredential;
use App\Application\Persistence\PersistenceTransaction;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        throw new RuntimeException($message);
    }
};

$root = dirname(__DIR__);
$read = static function (string $relative) use ($root): string {
    $content = file_get_contents($root.'/'.$relative);
    if (! is_string($content) || $content === '') {
        throw new RuntimeException('Unable to read '.$relative);
    }

    return $content;
};

$code = static function (string $selectorChar, string $secretChar): string {
    return 'rq1.'.str_repeat($selectorChar, 22).'.'.str_repeat($secretChar, 43);
};

$codes = [];
foreach (range('A', 'H') as $letter) {
    $codes[] = $code($letter, strtolower($letter));
}

$issued = new IssuedRecoveryCodeSet($codes);
$assert($issued->codes() === $codes, 'Issued recovery code set must preserve exactly eight codes.');

$duplicateRejected = false;
try {
    new IssuedRecoveryCodeSet(array_fill(0, 8, $codes[0]));
} catch (InvalidArgumentException) {
    $duplicateRejected = true;
}
$assert($duplicateRejected, 'Duplicate recovery codes must be rejected.');

final class Sprint32CredentialVerifier implements FirstPartyIdentityCredentialVerifier
{
    public function __construct(private readonly bool $valid) {}

    public function verify(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        #[\SensitiveParameter] string $password,
    ): bool {
        return $this->valid && $password === 'correct-password';
    }
}

final class Sprint32RecoveryRepository implements RecoveryCodeRepository
{
    public int $rotateCalls = 0;
    public int $consumeCalls = 0;

    /** @param list<string> $codes */
    public function __construct(private readonly array $codes) {}

    public function rotate(
        TenantId $tenantId,
        PlatformIdentityId $identityId,
        int $occurredAtUnix,
        string $correlationId,
    ): array {
        $this->rotateCalls++;
        return $this->codes;
    }

    public function consume(
        #[\SensitiveParameter] string $recoveryCode,
        int $occurredAtUnix,
        string $correlationId,
    ): array {
        $this->consumeCalls++;
        return [
            'tenant_id' => 'tenant-alpha',
            'identity_id' => 'identity-alpha',
            'proved_at_unix' => $occurredAtUnix,
        ];
    }
}

final class Sprint32Transaction implements PersistenceTransaction
{
    public int $calls = 0;

    public function run(callable $operation): mixed
    {
        $this->calls++;
        return $operation();
    }
}

final class Sprint32Clock implements RecoveryCodeClock
{
    public function __construct(private readonly int $now) {}
    public function nowUnix(): int { return $this->now; }
}

$tenant = TenantId::fromString('tenant-alpha');
$identity = PlatformIdentityId::fromString('identity-alpha');
$repository = new Sprint32RecoveryRepository($codes);
$transaction = new Sprint32Transaction();
$service = new RecoveryCodeService(
    new VerifyFirstPartyIdentityCredential(new Sprint32CredentialVerifier(true)),
    $repository,
    $transaction,
    new Sprint32Clock(1_800_000_000),
);

$rotated = $service->rotate($tenant, $identity, 'correct-password', 'corr-s32-rotate');
$assert($rotated->codes() === $codes, 'Successful rotation must return the exact typed eight-code set.');
$assert($repository->rotateCalls === 1 && $transaction->calls === 1, 'Rotation must enter exactly one persistence transaction.');

$proof = $service->prove($codes[0], 'corr-s32-proof');
$assert($proof->tenantId()->equals($tenant), 'Verified proof tenant must be server-derived from repository state.');
$assert($proof->identityId()->equals($identity), 'Verified proof identity must be server-derived from repository state.');
$assert($proof->provedAtUnix() === 1_800_000_000, 'Verified proof timestamp must be server clock time.');
$assert($repository->consumeCalls === 1 && $transaction->calls === 2, 'Proof must enter exactly one persistence transaction.');

$badPasswordRepository = new Sprint32RecoveryRepository($codes);
$badPasswordService = new RecoveryCodeService(
    new VerifyFirstPartyIdentityCredential(new Sprint32CredentialVerifier(false)),
    $badPasswordRepository,
    new Sprint32Transaction(),
    new Sprint32Clock(1_800_000_000),
);
$badPasswordRejected = false;
try {
    $badPasswordService->rotate($tenant, $identity, 'wrong-password', 'corr-s32-bad-password');
} catch (RecoveryCodeViolation $exception) {
    $badPasswordRejected = $exception->errorCode === RecoveryCodeViolation::ROTATION_FAILED;
}
$assert($badPasswordRejected && $badPasswordRepository->rotateCalls === 0, 'Wrong password must fail before recovery-code mutation.');

$badCodeRejected = false;
try {
    $service->prove('not-a-recovery-code', 'corr-s32-bad-code');
} catch (RecoveryCodeViolation $exception) {
    $badCodeRejected = $exception->errorCode === RecoveryCodeViolation::VERIFICATION_FAILED;
}
$assert($badCodeRejected && $repository->consumeCalls === 1, 'Malformed recovery code must fail before repository access.');

$assert(FirstPartySessionKeys::all() === [
    FirstPartySessionKeys::IDENTITY,
    FirstPartySessionKeys::TENANT,
    FirstPartySessionKeys::ORGANIZATION,
    FirstPartySessionKeys::OUTLET,
    FirstPartySessionKeys::DEVICE,
], 'Sprint 27 canonical full-session key list must remain exactly five keys.');
$assert(FirstPartySessionKeys::recovery() === [
    FirstPartySessionKeys::RECOVERY_TENANT,
    FirstPartySessionKeys::RECOVERY_IDENTITY,
    FirstPartySessionKeys::RECOVERY_STATE,
    FirstPartySessionKeys::RECOVERY_PROVED_AT,
    FirstPartySessionKeys::RECOVERY_EXPIRES_AT,
], 'Restricted recovery session must enumerate exactly five recovery keys.');

$config = $read('config/oneqay.php');
$routes = $read('routes/web.php');
$repo = $read('app/Infrastructure/Identity/LaravelRecoveryCodeRepository.php');
$controller = $read('app/Delivery/Http/Identity/RecoveryCodeController.php');
$keys = $read('app/Delivery/Http/Identity/FirstPartySessionKeys.php');
$migration = $read('database/migrations/0000_00_00_000010_create_identity_recovery_codes.php');
$foundation = $read('../../docs/AUTHENTICATION_RECOVERY_PROOF_FOUNDATION.md');

$assert(str_contains($config, "env('ONEQAY_AUTHENTICATION_RECOVERY_ENABLED', false)"), 'Recovery feature arm must default false.');
$assert(str_contains($config, "'restricted_session_ttl_seconds' => 600"), 'Restricted recovery TTL must be fixed at 600 seconds.');
$assert(! str_contains($config, 'ONEQAY_AUTHENTICATION_RECOVERY_TTL'), 'Recovery TTL must not be environment-configurable.');
$assert(str_contains($routes, "Route::post('/auth/recovery/codes/rotate'"), 'Rotation route must exist.');
$assert(str_contains($routes, "Route::post('/auth/recovery/proof'"), 'Proof route must exist.');
$assert(substr_count($routes, "'throttle:5,1', 'throttle:20,60'") >= 3, 'Recovery routes must preserve bounded throttling.');

foreach ([
    "random_bytes(16)",
    "random_bytes(32)",
    "hash('sha256', \$secret)",
    'hash_equals($row->secret_digest, $suppliedDigest)',
    'lockForUpdate()',
    "whereNull('consumed_at_unix')",
    "whereNull('revoked_at_unix')",
    "where('role_id', self::CONTROL_ROLE)",
    "whereNotNull('confirmed_at_unix')",
    "'codes_rotated'",
    "'proof_succeeded'",
] as $needle) {
    $assert(str_contains($repo, $needle), 'Recovery repository missing invariant: '.$needle);
}
foreach (['decryptString', 'encryptString', 'secret_ciphertext', 'provisioningUri', 'password_hash(', 'password_verify('] as $forbidden) {
    $assert(! str_contains($repo, $forbidden), 'Recovery repository must not introduce or access forbidden secret primitive: '.$forbidden);
}
$assert(str_contains($repo, "->update(['revoked_at_unix' => \$occurredAtUnix])"), 'Rotation must atomically revoke prior unused codes.');
$assert(str_contains($repo, "->update(['consumed_at_unix' => \$occurredAtUnix])"), 'Proof must atomically consume one code.');
$assert(str_contains($repo, 'if ($updated !== 1)'), 'Same-code replay/concurrency must have at most one winner.');

foreach ([
    'oneqay_identity_recovery_codes',
    'oneqay_identity_recovery_audit',
    "char('code_selector', 22)",
    "char('secret_digest', 64)",
    "unsignedBigInteger('consumed_at_unix')->nullable()",
    "unsignedBigInteger('revoked_at_unix')->nullable()",
    "throw new LogicException('Forward-only generated migration; rollback is not authorized.')",
] as $needle) {
    $assert(str_contains($migration, $needle), 'Migration #10 missing invariant: '.$needle);
}
foreach (['plaintext', "string('password'", "string('totp'", "string('email'", "string('phone'"] as $forbidden) {
    $assert(! str_contains(strtolower($migration), strtolower($forbidden)), 'Migration #10 contains forbidden recovery storage: '.$forbidden);
}

$migrations = array_map('basename', glob($root.'/database/migrations/*.php') ?: []);
sort($migrations);
$expectedMigrations = [
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
$assert($migrations === $expectedMigrations, 'Source migration set must be exactly #1 through #10.');

foreach ([
    "private const RECOVERY_STATE = 'password_reset_required'",
    'private const RESTRICTED_SESSION_TTL_SECONDS = 600',
    '$session->invalidate();',
    '$session->regenerateToken();',
    'FirstPartySessionKeys::recovery()',
    "SafeErrorEnvelope::make('AUTHENTICATION_RECOVERY_FAILED'",
] as $needle) {
    $assert(str_contains($controller, $needle), 'Recovery controller missing invariant: '.$needle);
}
foreach ([
    'oneqay.auth.recovery.tenant_id',
    'oneqay.auth.recovery.identity_id',
    'oneqay.auth.recovery.state',
    'oneqay.auth.recovery.proved_at',
    'oneqay.auth.recovery.expires_at',
] as $needle) {
    $assert(str_contains($keys, $needle), 'Recovery session key missing: '.$needle);
}

$assert(! str_contains($controller, 'password_hash'), 'Sprint 32 controller must not change passwords.');
$assert(! str_contains($controller, 'PrivilegedTotpMfaService'), 'Sprint 32 controller must not recover or replace TOTP.');
$assert(str_contains($foundation, 'does not implement password reset'), 'Foundation must preserve no-password-reset boundary.');
$assert(str_contains($foundation, 'Technical Preview remains NO_SCHEMA_CHANGE'), 'Foundation must preserve Technical Preview boundary.');
$assert(str_contains($foundation, 'Production remains NO-GO / NOT AUTHORIZED'), 'Foundation must preserve Production boundary.');

fwrite(STDOUT, "Sprint 32 authentication recovery proof regression passed.\n");

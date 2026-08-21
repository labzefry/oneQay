<?php

declare(strict_types=1);

// Author by Lab | zefry
$root = dirname(__DIR__);
$repo = dirname($root, 2);

$assert = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$read = static function (string $path) use ($assert): string {
    $assert(is_file($path), 'required Sprint35 source is missing: '.$path);
    $content = file_get_contents($path);
    $assert(is_string($content), 'required Sprint35 source is unreadable: '.$path);
    return $content;
};

$migration = $read($root.'/database/migrations/0000_00_00_000012_add_totp_factor_epoch_and_recovery_authority.php');
$service = $read($root.'/app/Application/Identity/PrivilegedTotpRecoveryService.php');
$repository = $read($root.'/app/Infrastructure/Identity/LaravelPrivilegedTotpRecoveryRepository.php');
$controller = $read($root.'/app/Delivery/Http/Identity/PrivilegedTotpRecoveryController.php');
$sessionKeys = $read($root.'/app/Delivery/Http/Identity/FirstPartySessionKeys.php');
$routes = $read($root.'/routes/web.php');
$passwordRecovery = $read($root.'/app/Application/Identity/RecoveryCodeService.php');
$gate = $read($repo.'/docs/SPRINT_35_PRIVILEGED_TOTP_RECOVERY_SCHEMA_SOURCE_ENVELOPE_GATE.md');

$assert(str_contains($migration, "unsignedBigInteger('factor_epoch')->default(0)"), 'migration #12 must add monotonic factor_epoch default 0');
$assert(str_contains($migration, 'oneqay_identity_totp_recovery_codes'), 'migration #12 must create dedicated TOTP recovery codes');
$assert(str_contains($migration, 'oneqay_identity_totp_recovery_audit'), 'migration #12 must create dedicated secret-free audit');
$assert(str_contains($migration, "throw new LogicException('Forward-only generated migration; rollback is not authorized.')"), 'migration #12 rollback must be prohibited');
$assert(! str_contains($migration, 'credential_epoch'), 'migration #12 must not overload password credential epoch');

$migrations = glob($root.'/database/migrations/*.php') ?: [];
sort($migrations, SORT_STRING);
$assert(count($migrations) === 12, 'canonical migration chain must contain exactly #1-#12');
for ($number = 1; $number <= 11; $number++) {
    $needle = sprintf('_%06d_', $number);
    $matches = array_values(array_filter($migrations, static fn (string $path): bool => str_contains(basename($path), $needle)));
    $assert(count($matches) === 1, 'migration #'.$number.' must remain present exactly once');
}

$assert(str_contains($service, "'/\\Amq1\\."), 'TOTP recovery must use dedicated mq1 code namespace');
$assert(str_contains($passwordRecovery, "'/\\Arq1\\."), 'password recovery must retain rq1 namespace');
$assert(! str_contains($service, 'rq1.'), 'TOTP recovery service must not accept password recovery namespace');
$assert(str_contains($service, 'PrivilegedTotpMfaService $totp'), 'rotation must reuse canonical privileged TOTP service');
$assert(str_contains($service, 'PrivilegedTotpEngine $engine'), 'replacement must reuse canonical TOTP engine');
$assert(str_contains($service, 'matchTimeStep($secret, $newTotp, $now)'), 'new factor must be challenged before replacement');
$assert(str_contains($service, '$newEpoch !== $proof->factorEpoch() + 1'), 'replacement must advance factor epoch exactly once');

$assert(str_contains($repository, 'hash(\'sha256\', $secret)'), 'recovery secrets must be stored only as one-way digests');
$assert(str_contains($repository, 'lockForUpdate()'), 'recovery proof/replacement must use durable row locks');
$assert(str_contains($repository, "where('factor_epoch', \$oldEpoch)"), 'replacement must use optimistic epoch guard');
$assert(str_contains($repository, "'factor_epoch' => \$newEpoch"), 'replacement must persist the next factor epoch');
$assert(str_contains($repository, "'factor_replaced'"), 'replacement must append factor_replaced audit evidence');
$assert(str_contains($repository, 'revokeUnused($tenant, $identity, $occurredAtUnix)'), 'replacement must revoke remaining dedicated recovery codes');
$assert(! str_contains($repository, "'secret' => \$newSecret"), 'audit/storage rows must not persist plaintext replacement secret');

$assert(str_contains($controller, 'private const TTL = 600;'), 'restricted TOTP recovery session must be exactly 600 seconds');
$assert(str_contains($controller, '$session->invalidate();'), 'successful proof/replacement must rotate session authority');
$assert(str_contains($controller, '$session->regenerateToken();'), 'successful proof/replacement must regenerate CSRF');
$assert(str_contains($controller, "'requires_fresh_login' => true"), 'replacement must require a fresh normal login');
$assert(! str_contains($controller, 'MFA_VERIFIED_AT, time()'), 'replacement must not synthesize MFA verification');
$assert(! str_contains($controller, 'STEP_UP_VERIFIED_AT, time()'), 'replacement must not synthesize step-up authority');

foreach ([
    'TOTP_RECOVERY_TENANT',
    'TOTP_RECOVERY_IDENTITY',
    'TOTP_RECOVERY_CODE_ID',
    'TOTP_RECOVERY_FACTOR_EPOCH',
    'TOTP_RECOVERY_STATE',
    'TOTP_RECOVERY_PROVED_AT',
    'TOTP_RECOVERY_EXPIRES_AT',
    'TOTP_RECOVERY_REPLACEMENT',
] as $constant) {
    $assert(str_contains($sessionKeys, 'public const '.$constant), 'missing dedicated session key '.$constant);
}
$assert(str_contains($sessionKeys, 'MFA_FACTOR_EPOCH'), 'privileged MFA session namespace must expose factor epoch binding key');

foreach ([
    '/auth/mfa/recovery/codes/rotate',
    '/auth/mfa/recovery/proof',
    '/auth/mfa/recovery/totp/replace/start',
    '/auth/mfa/recovery/totp/replace/confirm',
] as $route) {
    $assert(str_contains($routes, $route), 'missing Sprint35 route '.$route);
}
$assert(substr_count($routes, "['throttle:5,1', 'throttle:20,60']") >= 4, 'Sprint35 recovery routes must preserve bounded throttling');

$assert(str_contains($gate, 'aaf7fb11490250d29c68dc7b46b62d2ee2239707ca53e004f9c0652878928e3f'), 'published Sprint35 source-envelope fingerprint must remain canonical');
$assert(str_contains($gate, 'Production remains `NO-GO / NOT AUTHORIZED`'), 'Production must remain NO-GO');

fwrite(STDOUT, "PASS: Sprint35 privileged TOTP recovery and factor replacement regression\n");

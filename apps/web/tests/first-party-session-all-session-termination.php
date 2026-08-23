<?php

declare(strict_types=1);

use App\Application\Authorization\AdministrationPermission;
use App\Application\Authorization\InitialTenantAdministratorProvisioningRepository;
use App\Application\Identity\FirstPartySessionAuthorityClock;
use App\Application\Identity\FirstPartySessionAuthorityService;
use App\Application\Identity\FirstPartySessionAuthorityViolation;
use App\Application\Identity\PrivilegedTotpFactorEpochRepository;
use App\Application\Identity\PrivilegedTotpMfaService;
use App\Delivery\Http\Identity\FirstPartySessionControlController;
use App\Delivery\Http\Identity\FirstPartySessionKeys;
use App\Delivery\Http\Middleware\RequireFirstPartySessionControlMutationContextMiddleware;
use App\Domain\Identity\PlatformIdentityId;
use App\Domain\Tenancy\TenantId;
use App\Infrastructure\Identity\LaravelFirstPartyCredentialEpochRepository;
use App\Infrastructure\Identity\LaravelFirstPartySessionAuthorityRepository;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require __DIR__.'/../vendor/autoload.php';

// Author by Lab | zefry
$testKey = 'base64:'.base64_encode(str_repeat('r', 32));
foreach ([
    'APP_NAME' => 'oneQay',
    'APP_ENV' => 'testing',
    'APP_KEY' => $testKey,
    'APP_DEBUG' => 'false',
    'APP_URL' => 'http://localhost',
    'ONEQAY_RUNTIME_CLASS' => 'ci',
    'ONEQAY_PERSISTENCE_ENABLED' => 'true',
    'ONEQAY_AUTHENTICATION_SESSION_CONTROL_ENABLED' => 'true',
    'ONEQAY_PRIVILEGED_TOTP_MFA_ENABLED' => 'true',
    'ONEQAY_PRIVILEGED_STEP_UP_ENABLED' => 'true',
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
        throw new RuntimeException('Sprint37 all-session termination regression failed: '.$message);
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

$workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'oneqay-s37-session-'.getmypid();
$removeTree($workspace);
$assert(@mkdir($workspace, 0700, false), 'workspace create');
$dbPath = $workspace.DIRECTORY_SEPARATOR.'session-authority.sqlite';
$assert(touch($dbPath), 'SQLite create');

$app['config']->set('database.default', 's37_session');
$app['config']->set('database.connections.s37_session', [
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
$app['config']->set('oneqay.privileged_totp_mfa.enabled', true);
$app['config']->set('oneqay.privileged_step_up.enabled', true);
$app['config']->set('oneqay.privileged_step_up.freshness_seconds', 300);

/** @var \Illuminate\Database\DatabaseManager $manager */
$manager = $app->make('db');
$manager->purge('s37_session');
$manager->setDefaultConnection('s37_session');
$connection = $manager->connection('s37_session');
$connection->getPdo();

$migrations = array_values(array_filter(scandir(__DIR__.'/../database/migrations') ?: [], static fn (string $file): bool => str_ends_with($file, '.php')));
sort($migrations);
$expectedMigrations = [];
for ($index = 1; $index <= 13; $index++) {
    $prefix = sprintf('0000_00_00_%06d_', $index);
    $matches = array_values(array_filter($migrations, static fn (string $file): bool => str_starts_with($file, $prefix)));
    $assert(count($matches) === 1, 'migration #'.$index.' must exist exactly once');
    $expectedMigrations[] = $matches[0];
}
$assert($migrations === $expectedMigrations, 'migration set must remain exactly #1-#13 with no migration #14');
foreach ($migrations as $migration) {
    (require __DIR__.'/../database/migrations/'.$migration)->up();
}

$seedOwner = static function (string $tenant, string $identity, string $organization, int $credentialEpoch) use ($connection, $assert): void {
    if (! $connection->table('oneqay_tenants')->where('id', $tenant)->exists()) {
        $connection->table('oneqay_tenants')->insert(['id' => $tenant]);
    }
    $connection->table('oneqay_identities')->insert(['tenant_id' => $tenant, 'id' => $identity]);
    if (! $connection->table('oneqay_organizations')->where('tenant_id', $tenant)->where('id', $organization)->exists()) {
        $connection->table('oneqay_organizations')->insert(['tenant_id' => $tenant, 'id' => $organization]);
    }
    $connection->table('oneqay_identity_organizations')->insert([
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'organization_id' => $organization,
    ]);
    $hash = password_hash('Sprint37 synthetic password '.$tenant.' '.$identity, PASSWORD_BCRYPT);
    $assert(is_string($hash), 'synthetic password hash creation');
    $connection->table('oneqay_identity_password_credentials')->insert([
        'tenant_id' => $tenant,
        'identity_id' => $identity,
        'password_hash' => $hash,
        'credential_epoch' => $credentialEpoch,
    ]);
};

$seedOwner('tenant-alpha', 'ordinary-alpha', 'organization-alpha', 3);
$seedOwner('tenant-alpha', 'other-alpha', 'organization-alpha', 4);
$seedOwner('tenant-alpha', 'privileged-alpha', 'organization-alpha', 5);
$seedOwner('tenant-beta', 'ordinary-alpha', 'organization-beta', 7);

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
    'identity_id' => 'privileged-alpha',
    'role_id' => InitialTenantAdministratorProvisioningRepository::CONTROL_ROLE,
]);

$totpSecret = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';
/** @var Encrypter $encrypter */
$encrypter = $app->make(Encrypter::class);
$factorPayload = json_encode([
    'v' => 1,
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'privileged-alpha',
    'secret' => $totpSecret,
], JSON_THROW_ON_ERROR);
$connection->table('oneqay_identity_totp_factors')->insert([
    'tenant_id' => 'tenant-alpha',
    'identity_id' => 'privileged-alpha',
    'secret_ciphertext' => $encrypter->encryptString($factorPayload),
    'created_at_unix' => 1787420000,
    'confirmed_at_unix' => 1787420060,
    'last_accepted_time_step' => 59580668,
    'factor_epoch' => 2,
]);

$clock = new class implements FirstPartySessionAuthorityClock {
    public int $now = 1787421000;
    public function nowUnix(): int { return $this->now; }
};
$repository = new LaravelFirstPartySessionAuthorityRepository($connection, true, 'ci', true);
$credentialEpochs = new LaravelFirstPartyCredentialEpochRepository($connection, true, 'ci');
/** @var PrivilegedTotpFactorEpochRepository $factorEpochs */
$factorEpochs = $app->make(PrivilegedTotpFactorEpochRepository::class);
/** @var PrivilegedTotpMfaService $mfa */
$mfa = $app->make(PrivilegedTotpMfaService::class);
$service = new FirstPartySessionAuthorityService(
    $repository,
    $clock,
    $credentialEpochs,
    $factorEpochs,
    $mfa,
    true,
    7200,
);

$tenantAlpha = TenantId::fromString('tenant-alpha');
$ordinaryAlpha = PlatformIdentityId::fromString('ordinary-alpha');
$otherAlpha = PlatformIdentityId::fromString('other-alpha');
$tenantBeta = TenantId::fromString('tenant-beta');
$privilegedAlpha = PlatformIdentityId::fromString('privileged-alpha');

$current = $service->issue($tenantAlpha, $ordinaryAlpha, 'organization-alpha', null, null, 3, null, 'S37-Issue_Current');
$clock->now++;
$remoteOne = $service->issue($tenantAlpha, $ordinaryAlpha, 'organization-alpha', null, null, 3, null, 'S37-Issue_Remote1');
$clock->now++;
$remoteTwo = $service->issue($tenantAlpha, $ordinaryAlpha, 'organization-alpha', null, null, 3, null, 'S37-Issue_Remote2');
$otherIdentity = $service->issue($tenantAlpha, $otherAlpha, 'organization-alpha', null, null, 4, null, 'S37-Issue_OtherIdentity');
$otherTenant = $service->issue($tenantBeta, $ordinaryAlpha, 'organization-beta', null, null, 7, null, 'S37-Issue_OtherTenant');

$credentialEpochBefore = (int) $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'ordinary-alpha')->value('credential_epoch');
$revoked = $service->revokeAll(
    $tenantAlpha,
    $ordinaryAlpha,
    $current->authorityId(),
    'organization-alpha',
    null,
    null,
    3,
    null,
    'S37-RevokeAll_0001',
);
$assert($revoked === 3, 'revoke-all did not revoke current plus every active exact-owner authority');
foreach ([$current, $remoteOne, $remoteTwo] as $owned) {
    $row = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $owned->authorityId())->first();
    $assert(is_object($row) && (int) $row->revoked_at_unix === $clock->now, 'exact-owner authority was not revoked monotonically');
}
$otherIdentityRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $otherIdentity->authorityId())->first();
$otherTenantRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $otherTenant->authorityId())->first();
$assert(is_object($otherIdentityRow) && $otherIdentityRow->revoked_at_unix === null, 'another identity was affected');
$assert(is_object($otherTenantRow) && $otherTenantRow->revoked_at_unix === null, 'another tenant with the same identity string was affected');
$assert((int) $connection->table('oneqay_identity_password_credentials')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'ordinary-alpha')->value('credential_epoch') === $credentialEpochBefore, 'credential epoch mutated');
$assert($connection->table('oneqay_identity_first_party_session_audit')->where('event_type', 'all_sessions_revoked')->count() === 1, 'revoke-all transition audit was not exactly one');

try {
    $service->revokeAll($tenantAlpha, $ordinaryAlpha, $current->authorityId(), 'organization-alpha', null, null, 3, null, 'S37-Replay_Service');
    $assert(false, 'revoked current authority was accepted on service replay');
} catch (FirstPartySessionAuthorityViolation $exception) {
    $assert($exception->errorCode === FirstPartySessionAuthorityViolation::AUTHORITY_DENIED, 'service replay returned unexpected denial');
}
$assert($connection->table('oneqay_identity_first_party_session_audit')->where('event_type', 'all_sessions_revoked')->count() === 1, 'service replay created duplicate audit');

$clock->now++;
$replayCurrent = $service->issue($tenantAlpha, $otherAlpha, 'organization-alpha', null, null, 4, null, 'S37-Replay_Current');
$replayRemote = $service->issue($tenantAlpha, $otherAlpha, 'organization-alpha', null, null, 4, null, 'S37-Replay_Remote');
$firstTransition = $repository->revokeAll($tenantAlpha, $otherAlpha, $replayCurrent->authorityId(), $clock->now, 'S37-Repository_First');
$secondTransition = $repository->revokeAll($tenantAlpha, $otherAlpha, $replayCurrent->authorityId(), $clock->now, 'S37-Repository_Replay');
$assert($firstTransition === 2 && $secondTransition === 0, 'repository replay did not converge monotonically');
$replayCurrentRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $replayCurrent->authorityId())->first();
$replayRemoteRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $replayRemote->authorityId())->first();
$assert(is_object($replayCurrentRow) && $replayCurrentRow->revoked_at_unix !== null, 'repository replay resurrected current authority');
$assert(is_object($replayRemoteRow) && $replayRemoteRow->revoked_at_unix !== null, 'repository replay resurrected remote authority');

$controller = new FirstPartySessionControlController($service);
$clock->now++;
$httpCurrent = $service->issue($tenantBeta, $ordinaryAlpha, 'organization-beta', null, null, 7, null, 'S37-Http_Current');
$httpRemote = $service->issue($tenantBeta, $ordinaryAlpha, 'organization-beta', null, null, 7, null, 'S37-Http_Remote');
$session = $app->make('session')->driver();
$session->flush();
$session->put([
    FirstPartySessionKeys::TENANT => 'tenant-beta',
    FirstPartySessionKeys::IDENTITY => 'ordinary-alpha',
    FirstPartySessionKeys::SESSION_AUTHORITY_ID => $httpCurrent->authorityId(),
    FirstPartySessionKeys::ORGANIZATION => 'organization-beta',
    FirstPartySessionKeys::CREDENTIAL_EPOCH => 7,
    FirstPartySessionKeys::MFA_FACTOR_EPOCH => null,
]);
$session->regenerateToken();
$oldToken = $session->token();
$request = Request::create('/auth/sessions/revoke-all', 'POST', ['_token' => $oldToken]);
$request->setLaravelSession($session);
$request->attributes->set('oneqay.correlation_id', 'S37-Http_RevokeAll');
$response = $controller->revokeAll($request);
$assert($response->getStatusCode() === 204, 'controller revoke-all did not return 204');
foreach ([$httpCurrent, $httpRemote] as $owned) {
    $row = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $owned->authorityId())->first();
    $assert(is_object($row) && $row->revoked_at_unix !== null, 'controller returned before durable revoke-all evidence existed');
}
$assert(! $session->has(FirstPartySessionKeys::IDENTITY) && ! $session->has(FirstPartySessionKeys::SESSION_AUTHORITY_ID), 'current Laravel session was not invalidated after durable success');
$newToken = $session->token();
$assert(is_string($newToken) && $newToken !== '' && is_string($oldToken) && ! hash_equals($oldToken, $newToken), 'CSRF token was not regenerated after durable success');

$clock->now++;
$payloadCurrent = $service->issue($tenantBeta, $ordinaryAlpha, 'organization-beta', null, null, 7, null, 'S37-Payload_Current');
$session->flush();
$session->put([
    FirstPartySessionKeys::TENANT => 'tenant-beta',
    FirstPartySessionKeys::IDENTITY => 'ordinary-alpha',
    FirstPartySessionKeys::SESSION_AUTHORITY_ID => $payloadCurrent->authorityId(),
    FirstPartySessionKeys::ORGANIZATION => 'organization-beta',
    FirstPartySessionKeys::CREDENTIAL_EPOCH => 7,
    FirstPartySessionKeys::MFA_FACTOR_EPOCH => null,
]);
$payloadRequest = Request::create('/auth/sessions/revoke-all', 'POST', ['tenant_id' => 'tenant-alpha']);
$payloadRequest->setLaravelSession($session);
$payloadRequest->attributes->set('oneqay.correlation_id', 'S37-Payload_Denied');
$payloadResponse = $controller->revokeAll($payloadRequest);
$assert($payloadResponse->getStatusCode() === 401, 'caller-supplied owner selector was not denied generically');
$payloadRow = $connection->table('oneqay_identity_first_party_sessions')->where('authority_id', $payloadCurrent->authorityId())->first();
$assert(is_object($payloadRow) && $payloadRow->revoked_at_unix === null, 'denied selector influenced durable owner state');

/** @var RequireFirstPartySessionControlMutationContextMiddleware $mutationMiddleware */
$mutationMiddleware = $app->make(RequireFirstPartySessionControlMutationContextMiddleware::class);
$ordinaryRequest = Request::create('/auth/sessions/revoke-all', 'POST');
$session->flush();
$session->put([
    FirstPartySessionKeys::TENANT => 'tenant-beta',
    FirstPartySessionKeys::IDENTITY => 'ordinary-alpha',
    FirstPartySessionKeys::ORGANIZATION => 'organization-beta',
]);
$ordinaryRequest->setLaravelSession($session);
$ordinaryResponse = $mutationMiddleware->handle($ordinaryRequest, static fn (): \Symfony\Component\HttpFoundation\Response => response('ok', 200));
$assert($ordinaryResponse->getStatusCode() === 200, 'ordinary identity received invented privileged step-up requirement');

$clock->now++;
$privilegedCurrent = $service->issue($tenantAlpha, $privilegedAlpha, 'organization-alpha', null, null, 5, 2, 'S37-Privileged_Current');
$factorEpochBefore = (int) $connection->table('oneqay_identity_totp_factors')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'privileged-alpha')->value('factor_epoch');

$privilegedRequest = Request::create('/auth/sessions/revoke-all', 'POST');
$session->flush();
$session->put([
    FirstPartySessionKeys::TENANT => 'tenant-alpha',
    FirstPartySessionKeys::IDENTITY => 'privileged-alpha',
    FirstPartySessionKeys::SESSION_AUTHORITY_ID => $privilegedCurrent->authorityId(),
    FirstPartySessionKeys::ORGANIZATION => 'organization-alpha',
    FirstPartySessionKeys::CREDENTIAL_EPOCH => 5,
    FirstPartySessionKeys::MFA_FACTOR_EPOCH => 2,
]);
$privilegedRequest->setLaravelSession($session);
try {
    $mutationMiddleware->handle($privilegedRequest, static fn (): \Symfony\Component\HttpFoundation\Response => response('unexpected', 200));
    $assert(false, 'protected privileged identity mutated without session_control step-up');
} catch (HttpExceptionInterface $exception) {
    $assert($exception->getStatusCode() === 403, 'missing privileged step-up returned unexpected status');
}

$stepUpContext = [
    'identity_id' => 'privileged-alpha',
    'tenant_id' => 'tenant-alpha',
    'organization_id' => 'organization-alpha',
    'outlet_id' => null,
    'device_id' => null,
];
$session->put([
    FirstPartySessionKeys::STEP_UP_SCOPE => 'session_control',
    FirstPartySessionKeys::STEP_UP_CONTEXT => $stepUpContext,
    FirstPartySessionKeys::STEP_UP_VERIFIED_AT => time(),
]);
$freshResponse = $mutationMiddleware->handle($privilegedRequest, static fn (): \Symfony\Component\HttpFoundation\Response => response('ok', 200));
$assert($freshResponse->getStatusCode() === 200, 'fresh privileged session_control step-up was rejected');

$session->put(FirstPartySessionKeys::STEP_UP_VERIFIED_AT, time() - 301);
try {
    $mutationMiddleware->handle($privilegedRequest, static fn (): \Symfony\Component\HttpFoundation\Response => response('unexpected', 200));
    $assert(false, 'stale privileged session_control step-up was accepted');
} catch (HttpExceptionInterface $exception) {
    $assert($exception->getStatusCode() === 403, 'stale privileged step-up returned unexpected status');
}
$assert((int) $connection->table('oneqay_identity_totp_factors')
    ->where('tenant_id', 'tenant-alpha')->where('identity_id', 'privileged-alpha')->value('factor_epoch') === $factorEpochBefore, 'factor epoch mutated during session-control checks');

$disabledRepository = new LaravelFirstPartySessionAuthorityRepository($connection, true, 'ci', false);
try {
    $disabledRepository->revokeAll($tenantAlpha, $privilegedAlpha, $privilegedCurrent->authorityId(), $clock->now, 'S37-Disabled');
    $assert(false, 'disabled feature allowed revoke-all');
} catch (FirstPartySessionAuthorityViolation $exception) {
    $assert($exception->errorCode === FirstPartySessionAuthorityViolation::FEATURE_DISABLED, 'disabled feature returned unexpected violation');
}
$productionRepository = new LaravelFirstPartySessionAuthorityRepository($connection, true, 'production', true);
try {
    $productionRepository->revokeAll($tenantAlpha, $privilegedAlpha, $privilegedCurrent->authorityId(), $clock->now, 'S37-Production');
    $assert(false, 'production runtime allowed revoke-all');
} catch (FirstPartySessionAuthorityViolation $exception) {
    $assert($exception->errorCode === FirstPartySessionAuthorityViolation::STORAGE_FAILURE, 'production runtime did not fail closed');
}

$auditRows = $connection->table('oneqay_identity_first_party_session_audit')->get();
foreach ($auditRows as $audit) {
    $encoded = json_encode($audit, JSON_THROW_ON_ERROR);
    foreach ([$testKey, $totpSecret, $current->publicHandle(), $remoteOne->publicHandle(), $httpCurrent->publicHandle()] as $secretMaterial) {
        $assert(! str_contains($encoded, $secretMaterial), 'audit leaked secret or public selector material');
    }
}

$route = collect($app['router']->getRoutes())->first(static fn ($candidate): bool => $candidate->getName() === 'auth.sessions.revoke-all');
$assert($route !== null, 'Sprint37 revoke-all route missing');
$assert($route->uri() === 'auth/sessions/revoke-all', 'Sprint37 revoke-all route URI changed');
$assert($route->methods() === ['POST'], 'Sprint37 revoke-all route method changed');
$routeMiddleware = $route->gatherMiddleware();
foreach (['session.active', 'session.control-mutation', 'throttle:5,1', 'throttle:20,60'] as $requiredMiddleware) {
    $assert(in_array($requiredMiddleware, $routeMiddleware, true), 'Sprint37 route middleware missing: '.$requiredMiddleware);
}

$manager->disconnect('s37_session');
$manager->purge('s37_session');
@unlink($dbPath);
$removeTree($workspace);

echo "Sprint37 first-party all-session termination regression passed.\n";

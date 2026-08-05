<?php

declare(strict_types=1);

require __DIR__ . '/../src/Auth/Foundation.php';
require __DIR__ . '/../src/Tenant/Foundation.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'OneQay\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use OneQay\Auth\ArraySessionStore;
use OneQay\Auth\AuthenticationService;
use OneQay\Auth\InMemoryUserProvider;
use OneQay\Auth\NativePasswordHasher;
use OneQay\Auth\SessionGuard;
use OneQay\Auth\User;
use OneQay\Http\ErrorEnvelope;
use OneQay\Tenant\SessionTenantContextResolver;
use OneQay\Tenant\TenantContext;
use OneQay\Tenant\TenantContextException;
use OneQay\Tenant\TenantIdentifier;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$hasher = new NativePasswordHasher();
$passwordHash = $hasher->hash('Correct-Horse-2026');
$assert($hasher->verify('Correct-Horse-2026', $passwordHash), 'Password hash verification failed.');
$assert(!$hasher->verify('wrong-password', $passwordHash), 'Wrong password was accepted.');

$user = new User('user-001', 'owner@example.test', $passwordHash);
$users = new InMemoryUserProvider([$user]);
$session = new ArraySessionStore();
$guard = new SessionGuard($session, $users);
$service = new AuthenticationService($users, $hasher, $guard);
$resolver = new SessionTenantContextResolver($session, $guard);
$fingerprint = 'ua|127.0.0.1';

$initialSessionId = $session->id();
$result = $service->login('OWNER@example.test', 'Correct-Horse-2026', $fingerprint);
$assert($result->isAuthenticated, 'Valid credentials did not authenticate.');
$assert($session->id() !== $initialSessionId, 'Session ID was not regenerated on login.');
$assert($guard->user($fingerprint)?->id === 'user-001', 'Authenticated user was not restored.');
$assert($guard->csrfToken() !== null, 'CSRF token was not issued.');
$assert($guard->verifyCsrfToken((string) $guard->csrfToken()), 'CSRF token validation failed.');
$assert(!$guard->verifyCsrfToken('invalid-token'), 'Invalid CSRF token was accepted.');

$invalid = $service->login('owner@example.test', 'wrong-password', $fingerprint);
$assert(!$invalid->isAuthenticated, 'Invalid credentials authenticated.');
$assert($invalid->errorCode === AuthenticationService::INVALID_CREDENTIALS, 'Invalid credential error code changed.');

$tenantId = new TenantIdentifier('  TENANT_ALPHA  ');
$assert($tenantId->value === 'tenant_alpha', 'Tenant identifier was not normalized.');

foreach (['', 'tenant.example.test', 'tenant/alpha', '-tenant', 'tenant-'] as $invalidTenantId) {
    try {
        new TenantIdentifier($invalidTenantId);
        $assert(false, 'Invalid tenant identifier was accepted.');
    } catch (InvalidArgumentException) {
        $assert(true, 'Invalid tenant identifier was rejected.');
    }
}

$beforeSelect = $session->id();
$context = $resolver->select('tenant_alpha', $fingerprint);
$assert($context instanceof TenantContext, 'Authenticated session could not select tenant context.');
$assert($context->tenantId->value === 'tenant_alpha', 'Selected tenant context is incorrect.');
$assert($session->id() !== $beforeSelect, 'Session ID was not regenerated on tenant selection.');
$assert($resolver->resolve($fingerprint)?->tenantId->value === 'tenant_alpha', 'Tenant context was not restored from session.');
$assert($resolver->requireContext($fingerprint)->tenantId->value === 'tenant_alpha', 'Required tenant context could not be resolved.');

$sameTenantSessionId = $session->id();
$resolver->select('tenant_alpha', $fingerprint);
$assert($session->id() === $sameTenantSessionId, 'Session regenerated when tenant context did not change.');

$beforeSwitch = $session->id();
$resolver->select('tenant_beta', $fingerprint);
$assert($session->id() !== $beforeSwitch, 'Tenant switch did not regenerate session ID.');
$assert($resolver->resolve($fingerprint)?->tenantId->value === 'tenant_beta', 'Tenant switch was not persisted.');

$resolver->clear();
try {
    $resolver->requireContext($fingerprint);
    $assert(false, 'Missing tenant context was accepted.');
} catch (TenantContextException $exception) {
    $assert($exception->errorCode === SessionTenantContextResolver::REQUIRED, 'Missing tenant context error code changed.');
}

try {
    $resolver->select('tenant.example.test', $fingerprint);
    $assert(false, 'Invalid tenant context was accepted.');
} catch (TenantContextException $exception) {
    $assert($exception->errorCode === SessionTenantContextResolver::INVALID, 'Invalid tenant context error code changed.');
}

$resolver->select('tenant_alpha', $fingerprint);
$beforeLogout = $session->id();
$service->logout();
$assert($session->id() !== $beforeLogout, 'Session ID was not regenerated on logout.');
$assert($guard->user($fingerprint) === null, 'User remained authenticated after logout.');
$assert($resolver->resolve($fingerprint) === null, 'Tenant context remained after logout.');

try {
    $resolver->select('tenant_alpha', $fingerprint);
    $assert(false, 'Unauthenticated session selected tenant context.');
} catch (TenantContextException $exception) {
    $assert($exception->errorCode === SessionTenantContextResolver::UNAVAILABLE, 'Unauthenticated tenant error code changed.');
}

$contextProperties = array_keys(get_object_vars(new TenantContext(new TenantIdentifier('tenant_alpha'))));
$assert($contextProperties === ['tenantId'], 'Tenant context contains role, permission, or unexpected state.');

$envelope = new ErrorEnvelope(SessionTenantContextResolver::REQUIRED, 'Tenant context diperlukan.', 'corr-tenant-123');
$payload = $envelope->toArray();
$assert($payload['error']['code'] === 'TENANT_CONTEXT_REQUIRED', 'Tenant error envelope code is invalid.');
$assert($payload['error']['correlation_id'] === 'corr-tenant-123', 'Tenant correlation ID is missing.');

$source = file_get_contents(__DIR__ . '/../src/Tenant/Foundation.php');
$assert(is_string($source) && !preg_match('/\b(role|permission|rbac|abac|pos|sale|payment|inventory)\b/i', $source), 'Authorization or POS behavior was introduced.');

fwrite(STDOUT, sprintf("Authentication and Tenant Context Foundation tests passed: %d assertions.\n", $assertions));

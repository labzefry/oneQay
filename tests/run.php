<?php

declare(strict_types=1);

require __DIR__ . '/../src/Auth/Foundation.php';
require __DIR__ . '/../src/Tenant/Foundation.php';
require __DIR__ . '/../src/Authorization/Foundation.php';
require __DIR__ . '/../src/Http/ErrorEnvelope.php';

use OneQay\Auth\ArraySessionStore;
use OneQay\Auth\AuthenticationService;
use OneQay\Auth\InMemoryUserProvider;
use OneQay\Auth\NativePasswordHasher;
use OneQay\Auth\SessionGuard;
use OneQay\Auth\User;
use OneQay\Authorization\AuthorizationContext;
use OneQay\Authorization\AuthorizationException;
use OneQay\Authorization\AuthorizationService;
use OneQay\Authorization\AuthorizationSubject;
use OneQay\Authorization\DenyByDefaultPolicy;
use OneQay\Authorization\ExplicitGrantPolicy;
use OneQay\Authorization\PermissionIdentifier;
use OneQay\Http\ErrorEnvelope;
use OneQay\Tenant\SessionTenantContextResolver;
use OneQay\Tenant\TenantIdentifier;

$assertions = 0;
$assert = static function (bool $condition, string $message) use (&$assertions): void {
    $assertions++;
    if (!$condition) { throw new RuntimeException($message); }
};
$throwsCode = static function (callable $callback, string $code) use ($assert): void {
    try { $callback(); $assert(false, "Expected {$code}."); }
    catch (AuthorizationException $e) { $assert($e->errorCode === $code, "Unexpected {$e->errorCode}."); }
};

$hasher = new NativePasswordHasher();
$hash = $hasher->hash('Correct-Horse-2026');
$assert($hasher->verify('Correct-Horse-2026', $hash), 'Authentication password regression failed.');
$users = new InMemoryUserProvider([new User('user-001', 'owner@example.test', $hash)]);
$session = new ArraySessionStore();
$guard = new SessionGuard($session, $users);
$auth = new AuthenticationService($users, $hasher, $guard);
$tenant = new SessionTenantContextResolver($session, $guard);
$fingerprint = 'ua|127.0.0.1';

$unauthz = new AuthorizationService($guard, $tenant, new DenyByDefaultPolicy());
$throwsCode(fn () => $unauthz->subject($fingerprint), AuthorizationService::AUTHENTICATION_REQUIRED);

$login = $auth->login('owner@example.test', 'Correct-Horse-2026', $fingerprint);
$assert($login->isAuthenticated, 'Authentication regression failed.');
$throwsCode(fn () => $unauthz->subject($fingerprint), AuthorizationService::TENANT_REQUIRED);
$tenant->select('tenant_alpha', $fingerprint);
$assert($tenant->requireContext($fingerprint)->tenantId->value === 'tenant_alpha', 'Tenant regression failed.');

$permission = new PermissionIdentifier(' FOUNDATION.READ ');
$assert($permission->value === 'foundation.read', 'Permission normalization failed.');
foreach (['', 'read', '/foundation/read', 'tenant_alpha.foundation.read'] as $invalid) {
    try { new PermissionIdentifier($invalid); $assert(false, 'Invalid permission accepted.'); }
    catch (InvalidArgumentException) { $assert(true, 'Invalid permission rejected.'); }
}

$subject = $unauthz->subject($fingerprint);
$assert(array_keys(get_object_vars($subject)) === ['userId', 'tenantId'], 'Subject boundary expanded.');
$context = new AuthorizationContext($subject, $permission, 'corr-authz-001');
$assert(array_keys(get_object_vars($context)) === ['subject', 'permission', 'correlationId'], 'Context boundary expanded.');
$denied = $unauthz->evaluate($context, $fingerprint);
$assert(!$denied->isAllowed && $denied->reasonCode === AuthorizationService::PERMISSION_DENIED, 'Default policy did not deny.');
$throwsCode(fn () => $unauthz->requireAllowed($context, $fingerprint), AuthorizationService::PERMISSION_DENIED);

$grants = new ExplicitGrantPolicy();
$grants->grant('user-001', new TenantIdentifier('tenant_alpha'), $permission);
$service = new AuthorizationService($guard, $tenant, $grants);
$allowed = $service->evaluate($context, $fingerprint);
$assert($allowed->isAllowed, 'Explicit synthetic grant did not allow.');
$service->requireAllowed($context, $fingerprint);
$assert(true, 'Allowed decision accepted.');

$crossTenant = new AuthorizationContext(
    new AuthorizationSubject('user-001', new TenantIdentifier('tenant_beta')),
    $permission,
    'corr-authz-002'
);
$throwsCode(fn () => $service->evaluate($crossTenant, $fingerprint), AuthorizationService::CROSS_TENANT_DENIED);

$tenant->select('tenant_beta', $fingerprint);
$newSubject = $service->subject($fingerprint);
$newContext = new AuthorizationContext($newSubject, $permission, 'corr-authz-003');
$assert(!$service->evaluate($newContext, $fingerprint)->isAllowed, 'Grant leaked across tenants.');

$wrongUser = new AuthorizationContext(
    new AuthorizationSubject('user-999', new TenantIdentifier('tenant_beta')),
    $permission,
    'corr-authz-004'
);
$throwsCode(fn () => $service->evaluate($wrongUser, $fingerprint), AuthorizationService::CONTEXT_INVALID);

$envelope = new ErrorEnvelope(AuthorizationService::PERMISSION_DENIED, 'Akses ditolak.', 'corr-authz-005');
$payload = $envelope->toArray();
$assert($payload['error']['code'] === 'AUTHORIZATION_PERMISSION_DENIED', 'Authorization error code changed.');
$assert($payload['error']['correlation_id'] === 'corr-authz-005', 'Correlation ID missing.');

$source = file_get_contents(__DIR__ . '/../src/Authorization/Foundation.php');
$assert(is_string($source) && !preg_match('/\b(pos|sale|payment|inventory|catalog)\b/i', $source), 'POS or business behavior introduced.');

fwrite(STDOUT, sprintf("Authentication, Tenant Context, and Authorization Boundary tests passed: %d assertions.\n", $assertions));

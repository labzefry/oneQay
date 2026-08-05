<?php

declare(strict_types=1);

require __DIR__ . '/../src/Auth/Foundation.php';

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

$initialSessionId = $session->id();
$result = $service->login('OWNER@example.test', 'Correct-Horse-2026', 'ua|127.0.0.1');
$assert($result->isAuthenticated, 'Valid credentials did not authenticate.');
$assert($session->id() !== $initialSessionId, 'Session ID was not regenerated on login.');
$assert($guard->user('ua|127.0.0.1')?->id === 'user-001', 'Authenticated user was not restored.');
$assert($guard->csrfToken() !== null, 'CSRF token was not issued.');
$assert($guard->verifyCsrfToken((string) $guard->csrfToken()), 'CSRF token validation failed.');
$assert(!$guard->verifyCsrfToken('invalid-token'), 'Invalid CSRF token was accepted.');

$invalid = $service->login('owner@example.test', 'wrong-password', 'ua|127.0.0.1');
$assert(!$invalid->isAuthenticated, 'Invalid credentials authenticated.');
$assert($invalid->errorCode === AuthenticationService::INVALID_CREDENTIALS, 'Invalid credential error code changed.');

$assert($guard->user('different-fingerprint') === null, 'Fingerprint mismatch did not invalidate session.');

$service->login('owner@example.test', 'Correct-Horse-2026', 'ua|127.0.0.1');
$beforeLogout = $session->id();
$service->logout();
$assert($session->id() !== $beforeLogout, 'Session ID was not regenerated on logout.');
$assert($guard->user('ua|127.0.0.1') === null, 'User remained authenticated after logout.');

$envelope = new ErrorEnvelope('AUTH_INVALID_CREDENTIALS', 'Email atau kata sandi tidak valid.', 'corr-123');
$payload = $envelope->toArray();
$assert($payload['error']['code'] === 'AUTH_INVALID_CREDENTIALS', 'Error envelope code is invalid.');
$assert($payload['error']['correlation_id'] === 'corr-123', 'Correlation ID is missing.');

fwrite(STDOUT, sprintf("Authentication Foundation tests passed: %d assertions.\n", $assertions));

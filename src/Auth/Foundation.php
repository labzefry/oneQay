<?php

declare(strict_types=1);

namespace OneQay\Auth;

final readonly class User
{
    public function __construct(
        public string $id,
        public string $email,
        public string $passwordHash,
        public bool $isActive = true,
    ) {
        if ($this->id === '' || filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('User identity is invalid.');
        }
    }
}

interface UserProvider
{
    public function findByEmail(string $email): ?User;

    public function findById(string $id): ?User;
}

final class InMemoryUserProvider implements UserProvider
{
    /** @var array<string, User> */
    private array $usersById = [];

    /** @var array<string, string> */
    private array $idByEmail = [];

    /** @param iterable<User> $users */
    public function __construct(iterable $users = [])
    {
        foreach ($users as $user) {
            $this->add($user);
        }
    }

    public function add(User $user): void
    {
        $email = self::normalizeEmail($user->email);
        $this->usersById[$user->id] = $user;
        $this->idByEmail[$email] = $user->id;
    }

    public function findByEmail(string $email): ?User
    {
        $id = $this->idByEmail[self::normalizeEmail($email)] ?? null;

        return $id === null ? null : ($this->usersById[$id] ?? null);
    }

    public function findById(string $id): ?User
    {
        return $this->usersById[$id] ?? null;
    }

    private static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}

interface PasswordHasher
{
    public function hash(string $plainPassword): string;

    public function verify(string $plainPassword, string $passwordHash): bool;

    public function needsRehash(string $passwordHash): bool;
}

final class NativePasswordHasher implements PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        self::assertPassword($plainPassword);
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);

        if ($hash === false) {
            throw new \RuntimeException('Password hashing failed.');
        }

        return $hash;
    }

    public function verify(string $plainPassword, string $passwordHash): bool
    {
        return $plainPassword !== '' && $passwordHash !== '' && password_verify($plainPassword, $passwordHash);
    }

    public function needsRehash(string $passwordHash): bool
    {
        return password_needs_rehash($passwordHash, PASSWORD_DEFAULT);
    }

    private static function assertPassword(string $plainPassword): void
    {
        if (strlen($plainPassword) < 12) {
            throw new \InvalidArgumentException('Password must contain at least 12 characters.');
        }
    }
}

interface SessionStore
{
    public function id(): string;

    public function regenerate(): void;

    public function put(string $key, mixed $value): void;

    public function get(string $key): mixed;

    public function remove(string $key): void;

    public function invalidate(): void;
}

final class ArraySessionStore implements SessionStore
{
    /** @var array<string, mixed> */
    private array $values = [];

    private string $sessionId;

    public function __construct()
    {
        $this->sessionId = self::newId();
    }

    public function id(): string
    {
        return $this->sessionId;
    }

    public function regenerate(): void
    {
        $this->sessionId = self::newId();
    }

    public function put(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    public function remove(string $key): void
    {
        unset($this->values[$key]);
    }

    public function invalidate(): void
    {
        $this->values = [];
        $this->regenerate();
    }

    private static function newId(): string
    {
        return bin2hex(random_bytes(32));
    }
}

final readonly class AuthenticationResult
{
    private function __construct(
        public bool $isAuthenticated,
        public ?User $user,
        public ?string $errorCode,
    ) {
    }

    public static function success(User $user): self
    {
        return new self(true, $user, null);
    }

    public static function failure(string $errorCode): self
    {
        return new self(false, null, $errorCode);
    }
}

final class SessionGuard
{
    private const USER_ID = 'auth.user_id';
    private const AUTHENTICATED_AT = 'auth.authenticated_at';
    private const FINGERPRINT = 'auth.fingerprint';
    private const CSRF_TOKEN = 'auth.csrf_token';

    public function __construct(
        private readonly SessionStore $session,
        private readonly UserProvider $users,
    ) {
    }

    public function login(User $user, string $fingerprint): void
    {
        if (!$user->isActive) {
            throw new \DomainException('Inactive users cannot authenticate.');
        }

        $this->session->regenerate();
        $this->session->put(self::USER_ID, $user->id);
        $this->session->put(self::AUTHENTICATED_AT, time());
        $this->session->put(self::FINGERPRINT, self::fingerprintHash($fingerprint));
        $this->session->put(self::CSRF_TOKEN, bin2hex(random_bytes(32)));
    }

    public function user(string $fingerprint): ?User
    {
        $userId = $this->session->get(self::USER_ID);
        $storedFingerprint = $this->session->get(self::FINGERPRINT);

        if (!is_string($userId) || !is_string($storedFingerprint)) {
            return null;
        }

        if (!hash_equals($storedFingerprint, self::fingerprintHash($fingerprint))) {
            $this->logout();
            return null;
        }

        $user = $this->users->findById($userId);

        if ($user === null || !$user->isActive) {
            $this->logout();
            return null;
        }

        return $user;
    }

    public function csrfToken(): ?string
    {
        $token = $this->session->get(self::CSRF_TOKEN);
        return is_string($token) ? $token : null;
    }

    public function verifyCsrfToken(string $token): bool
    {
        $stored = $this->csrfToken();
        return $stored !== null && $token !== '' && hash_equals($stored, $token);
    }

    public function logout(): void
    {
        $this->session->invalidate();
    }

    private static function fingerprintHash(string $fingerprint): string
    {
        if ($fingerprint === '') {
            throw new \InvalidArgumentException('Session fingerprint is required.');
        }

        return hash('sha256', $fingerprint);
    }
}

final class AuthenticationService
{
    public const INVALID_CREDENTIALS = 'AUTH_INVALID_CREDENTIALS';
    public const INACTIVE_USER = 'AUTH_INACTIVE_USER';

    public function __construct(
        private readonly UserProvider $users,
        private readonly PasswordHasher $hasher,
        private readonly SessionGuard $guard,
    ) {
    }

    public function login(string $email, string $password, string $fingerprint): AuthenticationResult
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || !$this->hasher->verify($password, $user->passwordHash)) {
            return AuthenticationResult::failure(self::INVALID_CREDENTIALS);
        }

        if (!$user->isActive) {
            return AuthenticationResult::failure(self::INACTIVE_USER);
        }

        $this->guard->login($user, $fingerprint);
        return AuthenticationResult::success($user);
    }

    public function logout(): void
    {
        $this->guard->logout();
    }
}
